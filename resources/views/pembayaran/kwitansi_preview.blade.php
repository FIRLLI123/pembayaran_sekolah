<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Kwitansi</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #0f172a;
            font-family: Arial, sans-serif;
        }
        .topbar {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            background: #111827;
            color: #e5e7eb;
            box-sizing: border-box;
        }
        .filename {
            font-size: 13px;
            opacity: 0.9;
        }
        .download-link {
            color: #93c5fd;
            text-decoration: none;
            font-size: 13px;
        }
        .viewer {
            height: calc(100% - 48px);
            width: 100%;
            border: 0;
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="filename">{{ $filename }}</div>
        <a class="download-link"
           href="data:application/pdf;base64,{{ $pdfBase64 }}"
           download="{{ $filename }}">
            Download PDF
        </a>
    </div>

    <iframe class="viewer" src="data:application/pdf;base64,{{ $pdfBase64 }}"></iframe>
</body>
</html>
