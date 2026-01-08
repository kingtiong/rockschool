<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rooms:backfill {--branch_id=} {--dry-run} {--force}', function () {
    /** @var \Illuminate\Console\Command $this */
    $branchId = $this->option('branch_id') ? (int) $this->option('branch_id') : null;
    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');

    $branchesQuery = \App\Models\Branch::query()->orderBy('name');
    if ($branchId) {
        $branchesQuery->whereKey($branchId);
    }

    $branches = $branchesQuery->get();
    if ($branches->count() === 0) {
        $this->error('No branches found.');
        return 1;
    }

    $this->info('Backfilling classroom numbers for existing lessons...');
    if ($dryRun) {
        $this->warn('Dry-run mode: no database updates will be made.');
    }

    foreach ($branches as $branch) {
        /** @var \App\Models\Branch $branch */
        $roomNumbers = \App\Models\Room::query()
            ->where('branch_id', $branch->id)
            ->where('active', true)
            ->orderBy('number')
            ->pluck('number')
            ->map(fn ($n) => (int) $n)
            ->values()
            ->all();

        if (count($roomNumbers) === 0) {
            $roomNumbers = range(1, max(1, (int) ($branch->classrooms_count ?? 1)));
        }

        $lessons = \App\Models\Lesson::query()
            ->whereNotNull('scheduled_start_at')
            ->whereNotNull('scheduled_end_at')
            ->whereHas('cycle.enrollment', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })
            ->orderBy('scheduled_start_at')
            ->orderBy('id')
            ->get();

        $updated = 0;
        $skipped = 0;
        $conflicts = 0;

        // Track last end time per room in chronological order.
        $lastEndByRoom = [];
        foreach ($roomNumbers as $r) {
            $lastEndByRoom[$r] = null;
        }

        foreach ($lessons as $lesson) {
            /** @var \App\Models\Lesson $lesson */
            $start = $lesson->scheduled_start_at;
            $end = $lesson->scheduled_end_at;
            if (! $start || ! $end) {
                $skipped++;
                continue;
            }

            $currentRoom = $lesson->classroom_number ? (int) $lesson->classroom_number : null;

            // Keep existing room if it doesn't overlap in that room.
            if ($currentRoom && isset($lastEndByRoom[$currentRoom]) && ($lastEndByRoom[$currentRoom] === null || $lastEndByRoom[$currentRoom]->lte($start))) {
                $lastEndByRoom[$currentRoom] = $end;
                continue;
            }

            // Otherwise find first available room.
            $assigned = null;
            foreach ($roomNumbers as $r) {
                $lastEnd = $lastEndByRoom[$r];
                if ($lastEnd === null || $lastEnd->lte($start)) {
                    $assigned = $r;
                    break;
                }
            }

            if (! $assigned) {
                $conflicts++;
                if (! $force) {
                    $skipped++;
                    continue;
                }
                $assigned = $roomNumbers[0] ?? 1;
            }

            if (! $dryRun) {
                $lesson->update(['classroom_number' => $assigned]);
            }
            $lastEndByRoom[$assigned] = $end;
            $updated++;
        }

        $this->line("Branch: {$branch->name} | Rooms: ".implode(',', $roomNumbers)." | Updated: {$updated} | Skipped: {$skipped} | Conflicts: {$conflicts}");
    }

    $this->info('Done.');
    return 0;
})->purpose('Auto-assign rooms for existing lessons');

Artisan::command('earnings:release {--dry-run} {--limit=200}', function () {
    /** @var \Illuminate\Console\Command $this */
    $dryRun = (bool) $this->option('dry-run');
    $limit = (int) ($this->option('limit') ?? 200);
    $limit = max(1, min(2000, $limit));

    $this->info('Releasing teacher earnings for ended lessons...');
    if ($dryRun) {
        $this->warn('Dry-run mode: no database updates will be made.');
    }

    $service = new \App\Services\LessonCompletionService();
    $stats = $service->releaseDueEarnings($limit, $dryRun);

    $this->line('Processed: '.$stats['processed']);
    $this->line('Auto-completed: '.$stats['completed']);
    $this->line('Earnings created: '.$stats['earnings_created']);
    $this->line('Cycles completed: '.$stats['cycles_completed']);
    $this->line('Next cycles created: '.$stats['next_cycles_created']);

    $this->info('Done.');
    return 0;
})->purpose('Auto-complete ended lessons and create teacher earnings');
