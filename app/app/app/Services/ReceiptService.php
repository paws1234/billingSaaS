<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{
    public function generatePDF(Invoice $invoice): string
    {
        $invoice->load(['user', 'subscription.plan']);

        $pdf = Pdf::loadView('receipts.invoice', [
            'invoice' => $invoice,
            'user' => $invoice->user,
            'subscription' => $invoice->subscription,
            'plan' => $invoice->subscription?->plan,
        ]);

        $filename = 'receipts/'.$invoice->id.'/invoice-'.$invoice->id.'.pdf';

        // Save locally first
        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }

    public function uploadToS3(Invoice $invoice, string $localPath): string
    {
        if (! Storage::disk('local')->exists($localPath)) {
            throw new \Exception('Local receipt file not found');
        }

        $content = Storage::disk('local')->get($localPath);
        $s3Path = 'receipts/'.date('Y/m').'/invoice-'.$invoice->id.'.pdf';

        // Upload to S3
        Storage::disk('s3')->put($s3Path, $content, 'public');

        // Clean up local file
        Storage::disk('local')->delete($localPath);

        return $s3Path;
    }

    public function generateAndUpload(Invoice $invoice): string
    {
        // Generate PDF
        $localPath = $this->generatePDF($invoice);

        // Upload to S3 if configured
        if (config('filesystems.default') === 's3' || config('app.env') === 'production') {
            $s3Path = $this->uploadToS3($invoice, $localPath);

            // Update invoice with S3 path
            $invoice->update(['receipt_path' => $s3Path]);

            return Storage::disk('s3')->url($s3Path);
        }

        // Update invoice with local path
        $invoice->update(['receipt_path' => $localPath]);

        return Storage::disk('local')->url($localPath);
    }
}
