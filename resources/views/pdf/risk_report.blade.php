<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Risiko Rantai Pasok</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #1a252f;
            padding-bottom: 10px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #1a252f;
        }
        .subtitle {
            font-size: 11px;
            color: #7f8c8d;
        }
        .meta-text {
            text-align: right;
            font-size: 10px;
            color: #7f8c8d;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #7f8c8d;
            font-weight: bold;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #2c3e50;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #34495e;
            font-size: 10px;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-weight: bold;
            border-radius: 3px;
            font-size: 9px;
            text-align: center;
        }
        .badge-low {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge-medium {
            background-color: #fff3cd;
            color: #664d03;
        }
        .badge-high {
            background-color: #f8d7da;
            color: #842029;
        }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            text-align: center;
            font-size: 9px;
            color: #bdc3c7;
            border-top: 1px solid #ecf0f1;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">LAPORAN RISIKO RANTAI PASOK GLOBAL</div>
                    <div class="subtitle">Platform Sourcing & Intelijen Risiko (GSCRIP)</div>
                </td>
                <td class="meta-text">
                    Tanggal Cetak: {{ $generatedAt }}<br>
                    Format: Ringkasan Eksekutif PDF
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td>
                    <div class="summary-title">Total Negara Dievaluasi</div>
                    <div class="summary-value">{{ $scores->count() }}</div>
                </td>
                <td>
                    <div class="summary-title">Rata-rata Indeks Risiko Sistem</div>
                    <div class="summary-value">{{ number_format($scores->avg('composite_score'), 2) }}</div>
                </td>
                <td>
                    <div class="summary-title">Negara Risiko Tinggi</div>
                    <div class="summary-value">{{ $scores->where('risk_level', 'high')->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%">ISO3</th>
                <th style="width: 22%">Negara</th>
                <th style="width: 12%">Skor Risiko</th>
                <th style="width: 12%">Tingkat Risiko</th>
                <th style="width: 9%">Ekonomi</th>
                <th style="width: 9%">Cuaca</th>
                <th style="width: 9%">Mata Uang</th>
                <th style="width: 9%">Geopolitik</th>
                <th style="width: 10%">Logistik</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scores as $item)
            @php
                $details = $item->details->keyBy('riskCategory.slug');
                $level = $item->risk_level;
                
                $badgeClass = 'badge-low';
                $levelText = 'Risiko Rendah';
                
                if ($level === 'high' || $level === 'critical') {
                    $badgeClass = 'badge-high';
                    $levelText = 'Risiko Tinggi';
                } elseif ($level === 'medium') {
                    $badgeClass = 'badge-medium';
                    $levelText = 'Risiko Sedang';
                }
            @endphp
            <tr>
                <td><strong>{{ $item->country->iso3 }}</strong></td>
                <td>{{ $item->country->name }}</td>
                <td><strong>{{ number_format($item->composite_score, 2) }}</strong></td>
                <td>
                    <span class="badge {{ $badgeClass }}">
                        {{ strtoupper($levelText) }}
                    </span>
                </td>
                <td>{{ $details->has('economic-risk') ? number_format($details->get('economic-risk')->category_score, 1) : 'N/A' }}</td>
                <td>{{ $details->has('weather-risk') ? number_format($details->get('weather-risk')->category_score, 1) : 'N/A' }}</td>
                <td>{{ $details->has('currency-stability-risk') ? number_format($details->get('currency-stability-risk')->category_score, 1) : 'N/A' }}</td>
                <td>{{ $details->has('geopolitical-risk') ? number_format($details->get('geopolitical-risk')->category_score, 1) : 'N/A' }}</td>
                <td>{{ $details->has('logistics-risk') ? number_format($details->get('logistics-risk')->category_score, 1) : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        GSCRIP Platform Intelijen Rantai Pasok &bull; Halaman 1 dari 1 &bull; Rahasia
    </div>

</body>
</html>
