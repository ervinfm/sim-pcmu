<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Aset - {{ $asset->inventory_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #e5e7eb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        /* Container Label: Ukuran Kartu Nama/Stiker Standar */
        .label-card {
            background: white;
            width: 90mm;
            height: 55mm;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header Bar */
        .header {
            background: #111827; /* Hitam Modern */
            color: white;
            padding: 4px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 7mm;
        }
        .org-name {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .sys-name {
            font-size: 8px;
            opacity: 0.8;
            font-weight: 500;
        }

        /* Body Content */
        .body {
            flex: 1;
            display: flex;
            padding: 4mm;
            gap: 4mm;
            align-items: center;
        }

        .qr-box {
            width: 32mm;
            height: 32mm;
            border: 2px solid #111827;
            border-radius: 6px;
            padding: 2px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .qr-box svg { width: 100%; height: 100%; }

        .details {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .asset-name {
            font-size: 13px;
            font-weight: 800;
            color: #000;
            line-height: 1.2;
            margin-bottom: 6px;
            text-transform: uppercase;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .meta-table {
            width: 100%;
            font-size: 9px;
            color: #374151;
        }
        .meta-table td { padding: 1px 0; vertical-align: top; }
        .meta-label { width: 35px; font-weight: 600; color: #6b7280; }
        .meta-val { font-weight: 600; }

        /* Footer Code */
        .footer {
            text-align: center;
            border-top: 1px dashed #d1d5db;
            padding: 3px 0;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 11px;
            color: #111827;
            background: #f9fafb;
        }

        /* Print Button */
        .btn-print {
            position: fixed;
            bottom: 30px;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            transition: transform 0.2s;
        }
        .btn-print:hover { transform: scale(1.05); }

        /* PRINT MODE */
        @media print {
            body { background: none; display: block; margin: 0; }
            .btn-print { display: none; }
            .label-card {
                box-shadow: none;
                margin: 0;
                page-break-inside: avoid;
                border: 1px solid #ddd; /* Helper border saat print */
            }
            @page {
                size: auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="label-card">
        <div class="header">
            <span class="org-name">{{ $orgName }}</span>
            <span class="sys-name">INVENTARIS</span>
        </div>

        <div class="body">
            <div class="qr-box">
                {!! $qrCode !!}
            </div>

            <div class="details">
                <div class="asset-name">{{ $asset->name }}</div>
                
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Lokasi</td>
                        <td class="meta-val">: {{ Str::limit($asset->location->name ?? '-', 15) }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tanggal</td>
                        <td class="meta-val">: {{ \Carbon\Carbon::parse($asset->acquisition_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Kat</td>
                        <td class="meta-val">: {{ $asset->category }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            {{ $asset->inventory_code }}
        </div>
    </div>

    <a href="javascript:window.print()" class="btn-print">
        🖨️ Cetak Label
    </a>

</body>
</html>