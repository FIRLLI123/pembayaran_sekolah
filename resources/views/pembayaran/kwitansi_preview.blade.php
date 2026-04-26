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
            margin-left: 14px;
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
        <div>
            <a class="download-link" id="openDirectLink" href="#" target="_blank" rel="noopener">Buka PDF</a>
            <a class="download-link" id="downloadLink" href="#" download="{{ $filename }}">Download PDF</a>
        </div>
    </div>

    <iframe class="viewer" id="pdfViewer"></iframe>

    <script>
        (function () {
            var base64 = @json($pdfBase64);
            var byteChars = atob(base64);
            var byteNumbers = new Array(byteChars.length);
            for (var i = 0; i < byteChars.length; i++) {
                byteNumbers[i] = byteChars.charCodeAt(i);
            }
            var byteArray = new Uint8Array(byteNumbers);
            var blob = new Blob([byteArray], { type: 'application/pdf' });
            var blobUrl = URL.createObjectURL(blob);

            document.getElementById('pdfViewer').src = blobUrl;
            document.getElementById('openDirectLink').href = blobUrl;
            document.getElementById('downloadLink').href = blobUrl;
        })();
    </script>
</body>
</html>
