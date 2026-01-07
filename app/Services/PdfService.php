<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * PDF Service
 * Handles PDF generation for invoices and reports
 */
class PdfService
{
    /**
     * Generate invoice PDF
     */
    public function generateInvoicePdf(Invoice $invoice): string
    {
        $invoice->load(['organization', 'invoiceItems', 'subscription']);

        $pdf = Pdf::loadView('pdfs.invoice', [
            'invoice' => $invoice,
        ]);

        return $pdf->output();
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoicePdf(Invoice $invoice): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('pdfs.invoice', [
            'invoice' => $invoice->load(['organization', 'invoiceItems', 'subscription']),
        ]);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Generate report PDF
     */
    public function generateReportPdf(array $reportData): string
    {
        $pdf = Pdf::loadView('pdfs.agency-report', [
            'report' => $reportData,
        ]);

        return $pdf->output();
    }

    /**
     * Download report PDF
     */
    public function downloadReportPdf(array $reportData, string $filename): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView('pdfs.agency-report', [
            'report' => $reportData,
        ]);

        return $pdf->download($filename);
    }
}

