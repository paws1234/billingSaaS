<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function __construct(protected ReceiptService $receiptService)
    {
    }

    public function index(Request $request)
    {
        return $request->user()->invoices()->latest()->get();
    }

    public function show(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);

        return $invoice->load(['subscription.plan']);
    }

    public function download(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);

        // Generate receipt if not exists
        if (!$invoice->receipt_path) {
            $this->receiptService->generateAndUpload($invoice);
            $invoice->refresh();
        }

        // Get file from storage
        if (Storage::disk('s3')->exists($invoice->receipt_path)) {
            return response()->json([
                'download_url' => Storage::disk('s3')->temporaryUrl(
                    $invoice->receipt_path,
                    now()->addMinutes(5)
                ),
            ]);
        }

        if (Storage::disk('local')->exists($invoice->receipt_path)) {
            return response()->download(
                Storage::disk('local')->path($invoice->receipt_path),
                'invoice-' . $invoice->id . '.pdf'
            );
        }

        abort(404, 'Receipt not found');
    }
}
