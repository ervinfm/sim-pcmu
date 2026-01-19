<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Batch</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page { 
                size: A4; 
                margin: 0.5cm; 
            }
            body { 
                -webkit-print-color-adjust: exact; 
                margin: 0;
            }
            .no-print { display: none; }
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
        }

        .page-container {
            background: white;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            margin: 0 auto;
            padding: 10mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: grid;
            /* Grid Layout: 3 Kolom */
            grid-template-columns: repeat(3, 1fr); 
            grid-auto-rows: min-content;
            grid-gap: 5mm; /* Jarak antar stiker */
            align-content: start;
            box-sizing: border-box;
        }

        .sticker-card {
            border: 1px solid #e5e7eb;
            background: #fff;
            height: 40mm; /* Tinggi fix stiker */
            display: flex;
            align-items: center;
            padding: 8px;
            position: relative;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Garis potong halus (opsional) */
        .sticker-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border: 1px dashed #d1d5db; /* Garis potong */
            border-radius: 6px;
            pointer-events: none;
        }

        .qr-wrapper {
            width: 32mm;
            height: 32mm;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
            flex-shrink: 0;
        }
        
        .qr-wrapper svg {
            width: 100%;
            height: 100%;
        }

        .info-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            overflow: hidden;
        }

        .org-header {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            color: #4b5563;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 2px;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .asset-name {
            font-size: 10px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .meta-info {
            font-size: 8px;
            color: #6b7280;
            margin-bottom: 1px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .inventory-badge {
            display: inline-block;
            background: #111827;
            color: #fff;
            font-family: 'Courier New', monospace;
            font-size: 8px;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 3px;
            margin-top: auto; /* Push ke bawah */
            align-self: flex-start;
        }

        .btn-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-float:hover { background: #1d4ed8; transform: translateY(-2px); }

    </style>
</head>
<body>

    <div class="page-container">
        @foreach($assets as $asset)
            <div class="sticker-card">
                <div class="qr-wrapper">
                    {!! $asset->qr_code !!}
                </div>
                <div class="info-wrapper">
                    <div class="org-header">{{ $orgName }}</div>
                    <div class="asset-name">{{ $asset->name }}</div>
                    
                    <div class="meta-info">
                        <span>📍</span> {{ Str::limit($asset->location->name ?? '-', 15) }}
                    </div>
                    
                    <div class="inventory-badge">
                        {{ $asset->inventory_code }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="javascript:window.print()" class="btn-float no-print">🖨️ Cetak Halaman</a>

</body>
</html>