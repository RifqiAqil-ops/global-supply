<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CountryRiskScore;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Show the Sourcing Risk Reports console.
     */
    public function index()
    {
        // Fetch all active risk scores for preview
        $scores = CountryRiskScore::with(['country', 'details.riskCategory'])
            ->orderByDesc('composite_score')
            ->get();

        return view('user.reports_index', compact('scores'));
    }

    /**
     * Export all risk scores to a native Excel (.xlsx) file.
     */
    public function exportExcel()
    {
        $filename = "Laporan_Risiko_Rantai_Pasok_" . Carbon::now()->format('Y-m-d') . ".xlsx";
        return Excel::download(new ReportsExport, $filename);
    }

    /**
     * Export all risk scores to a PDF file.
     */
    public function exportPdf()
    {
        $scores = CountryRiskScore::with(['country', 'details.riskCategory'])
            ->orderByDesc('composite_score')
            ->get();

        $generatedAt = Carbon::now()->format('F d, Y h:i A');

        $pdf = Pdf::loadView('pdf.risk_report', compact('scores', 'generatedAt'));
        
        // Optional layout options
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("sourcing_risk_report_" . Carbon::now()->format('Ymd_His') . ".pdf");
    }
}
