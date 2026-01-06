<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeInvoice($request, $invoice);

        return view('invoices.show', [
            'invoice' => $invoice->loadMissing(['student', 'cycle.enrollment.branch', 'cycle.enrollment.feePlan', 'cycle.additionalCharges']),
        ]);
    }

    public function download(Request $request, Invoice $invoice): Response
    {
        $this->authorizeInvoice($request, $invoice);

        $invoice->loadMissing(['student', 'cycle.enrollment.branch', 'cycle.enrollment.feePlan', 'cycle.additionalCharges']);
        $html = view('invoices.show', ['invoice' => $invoice])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.html"',
        ]);
    }

    private function authorizeInvoice(Request $request, Invoice $invoice): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->role === 'management') {
            return;
        }

        if ($user->role === 'student' && (int) $invoice->student_id === (int) $user->id) {
            return;
        }

        abort(403);
    }
}

