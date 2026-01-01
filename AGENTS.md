# AGENTS.md

This file documents **how to work in this repository** (for humans and coding agents) and serves as the **source of truth for core business rules** for the music school system.

## Repository overview

- **Workspace root**: `/workspace`
- **Target stack**: PHP, Laravel, MySQL
- **Current state**: Minimal skeleton repo (no Laravel app scaffold committed yet).

## Product summary

The system has 3 roles:

- **Student**: view class schedule, request schedule changes / report absence, view fees, pay via bank transfer and upload bank-in slip.
- **Teacher**: view assigned schedule, mark classes completed, view earnings statement (based on profit-sharing), see management payment status.
- **Management**: create/allocate students/teachers, set fee plans, create recurring schedules, approve payments (student → school), compute teacher earnings and mark teacher payouts.

## Core domain rules (must not drift)

### Fee plans (monthly/cycle)

All amounts are in **RM**.

- **Premiere**: RM220 for **4 hours** per cycle (1 hour weekly × 4)
- **Debut**: RM240 for **4 hours** per cycle (1 hour weekly × 4)
- **Grade 1–3**: RM260 for **4 hours** per cycle (1 hour weekly × 4)
- **Grade 4**: RM270 for **4 hours** per cycle (1 hour weekly × 4)
- **Grade 5**: RM290 for **4 hours** per cycle (1 hour weekly × 4)
- **Grade 6**: RM300 for **4 hours** per cycle (1 hour weekly × 4)
- **Grade 7**: RM330 for **3 hours** per cycle (45 mins weekly × 4)
- **Grade 8**: RM360 for **3 hours** per cycle (45 mins weekly × 4)

Duration options:

- For plans that are **1 hour weekly**, a student can choose **30 mins weekly** and the cycle fee becomes **half**.
- For **Grade 7–8**, the weekly class is **45 mins**; cycle total is **3 hours** across 4 lessons.

Implementation notes:

- Do **not** hardcode “4 hours” as a universal cycle total.
- Store fee plans as data and compute:
  - `lessons_per_cycle` (usually 4)
  - `minutes_per_lesson` (60 / 30 / 45)
  - `cycle_minutes_total` = `lessons_per_cycle * minutes_per_lesson`
  - `cycle_fee_amount`

### Scheduling & recurrence

Management schedules a student by selecting:

- **Start datetime** (e.g., Monday 2pm)
- **Lesson duration** (minutes, per plan rules)
- **Recurring count**: **4 lessons**

System auto-creates 4 lessons on the following weeks at the same weekday/time.

For Grade 7–8: 4 recurring lessons × 45 mins = 180 mins (= 3 hours) total per cycle.

### Attendance / cancellation rule

- If the student informs **≥ 2 hours before** class time that they cannot attend:
  - The class is **postponed to the next week** (no penalty).
- If the student informs **< 2 hours before** (or no-show):
  - The class is considered **still on-going** (i.e., counts as taken / not refundable).

Implementation notes:

- Record `absence_notified_at` and compare to `scheduled_start_at` with a 2-hour threshold.
- Prefer explicit status transitions for lessons (see below).

### Cycle progress (1/4, 2/4, 3/4, 4/4) and repeats

Lessons are grouped into a **cycle** of 4.

- Each lesson in a cycle is labeled **N/4** (progress index).
- After **4/4** is completed:
  - system expects **fee collection for the next cycle**
  - upon payment approval, the **next cycle** starts again at **1/4**

Implementation notes:

- Model **Cycle** explicitly (recommended) rather than inferring from dates.
- A cycle should have:
  - `lesson_count` (default 4)
  - `minutes_per_lesson`
  - `cycle_fee_amount`
  - `status` (e.g., `draft`, `active`, `awaiting_student_payment`, `paid`, `completed`)

### Teacher profit-sharing and payout

Management sets a **profit-sharing percentage** per teacher (e.g., Teacher A = 60%).

Earnings example:

- Plan: RM300 per 4 hours → RM75 per hour
- Teacher share: 60% → RM45 per hour equivalent

Implementation notes:

- Compute teacher earnings per completed lesson:
  - `lesson_fee_amount` = `cycle_fee_amount / cycle_minutes_total * minutes_per_lesson`
  - `teacher_earning_amount` = `lesson_fee_amount * teacher_share_percent`
- Persist earnings in a **teacher statement/ledger**.
- Management marks payouts to teachers; do not infer payout from bank-in slips (those are student→school).

## Suggested data model (Laravel/MySQL)

Names are suggestions; keep them consistent once chosen.

- **users**
  - login identity (email/phone), password, status
- **roles** or `user_roles`
  - `student`, `teacher`, `management` (RBAC)
- **students** / **teachers**
  - profile data linked to `users`
- **fee_plans**
  - `name`, `cycle_fee_amount`, `lessons_per_cycle`, `minutes_per_lesson_default`, constraints by grade
- **student_enrollments**
  - student + plan + chosen `minutes_per_lesson` (30/45/60) + start date + status
- **cycles**
  - enrollment + cycle index + `cycle_fee_amount` snapshot + status
- **lessons**
  - cycle + `scheduled_start_at`, `scheduled_end_at`, `minutes`, `sequence_in_cycle` (1..4),
    `status` (`scheduled`, `completed`, `postponed`, `missed`, `cancelled`), `absence_notified_at`
- **lesson_reschedules**
  - audit trail of changes (old/new datetime, who requested, reason)
- **payments (student→school)**
  - cycle + amount + method `bank_transfer` + `status` (`pending_review`, `approved`, `rejected`)
- **payment_attachments**
  - payment + uploaded slip (storage path, uploaded_by, metadata)
- **teacher_shares**
  - teacher + percent (effective dating if needed)
- **teacher_earnings**
  - lesson + teacher + computed amount + status (`unpaid`, `paid`) + paid_at
- **teacher_payouts**
  - management-created payouts (covers multiple earnings rows), status + reference

## Access control (non-negotiable)

- **Student**: can only see their own schedule, cycles, invoices/payments, and upload slips.
- **Teacher**: can only see lessons assigned to them, mark completion, and view their earnings/payout status.
- **Management**: full access, including assignment, approvals, configuration, and financial operations.

## Development workflow (when Laravel is added)

When scaffolding Laravel, also add:

- `.env.example` (no secrets)
- database migrations + seeders for fee plans
- basic tests for:
  - recurrence generation
  - 2-hour cancellation rule
  - cycle progression and earnings computation

## Development workflow (current)

- **Build**: Not configured.
- **Tests**: Not configured.
- **Lint/format**: Not configured.

If you add a language/runtime (Node/Python/Go/etc.), also add:

- A dependency/lock file appropriate for the ecosystem
- A minimal “how to run” section in the README
- At least one fast CI-friendly check (lint or unit tests)

## Conventions

- **Keep changes small and scoped**: Prefer incremental commits/PRs that do one thing well.
- **Prefer explicit structure**: If introducing code, create a conventional layout (e.g. `src/`, `tests/`, `docs/`) and document it.
- **Documentation**: Use Markdown. Keep instructions copy/paste friendly.

## Security and hygiene

- **Never commit secrets**: API keys, tokens, credentials, private certificates, `.env` files with real values.
- **Be deterministic**: Prefer pinned dependencies/lockfiles once a package manager is introduced.
- **Avoid repo-wide churn**: Don’t reformat or rename large swaths of files unless that’s the goal of the change.

## When adding automation

If you add tooling (lint, formatting, CI), ensure it is:

- **Fast** (runs locally in seconds where possible)
- **Reproducible** (document exact commands)
- **Minimal** (avoid heavy frameworks unless needed)

