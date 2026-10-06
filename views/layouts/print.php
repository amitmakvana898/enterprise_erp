<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($title ?? 'Print Label') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; color: #000; font-family: monospace; padding: 20px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-dark text-light p-3 rounded">
        <h5 class="mb-0"><?= e($title) ?></h5>
        <button onclick="window.print()" class="btn btn-primary btn-sm">Print Barcode Tag / Save PDF</button>
    </div>
    {{content}}
</body>
</html>
