<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CountryRiskScore;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
     * Export all risk scores to a CSV file.
     */
    public function exportCsv()
    {
        $scores = CountryRiskScore::with(['country', 'details.riskCategory'])
            ->orderByDesc('composite_score')
            ->get();

        $filename = "sourcing_risk_report_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($scores) {
            $file = fopen('php://output', 'w');
            
            // CSV Header Row
            fputcsv($file, [
                'Country Code (ISO3)', 
                'Country Name', 
                'Composite Score', 
                'Risk Level', 
                'Economic Score', 
                'Weather Score', 
                'Currency Score', 
                'Geopolitical Score', 
                'Logistics Score', 
                'Last Calculated'
            ]);

            foreach ($scores as $item) {
                $details = $item->details->keyBy('riskCategory.slug');
                
                fputcsv($file, [
                    $item->country->iso3,
                    $item->country->name,
                    number_format($item->composite_score, 2),
                    ucfirst($item->risk_level),
                    $details->has('economic-risk') ? number_format($details->get('economic-risk')->category_score, 2) : 'N/A',
                    $details->has('weather-risk') ? number_format($details->get('weather-risk')->category_score, 2) : 'N/A',
                    $details->has('currency-stability-risk') ? number_format($details->get('currency-stability-risk')->category_score, 2) : 'N/A',
                    $details->has('geopolitical-risk') ? number_format($details->get('geopolitical-risk')->category_score, 2) : 'N/A',
                    $details->has('logistics-risk') ? number_format($details->get('logistics-risk')->category_score, 2) : 'N/A',
                    $item->calculated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
