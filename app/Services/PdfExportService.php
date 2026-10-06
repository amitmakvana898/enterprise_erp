<?php

namespace App\Services;

class PdfExportService {
    public static function streamHtmlPrint(string $title, string $htmlContent): void {
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>" . htmlspecialchars($title) . "</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; color: #222; }
                @media print {
                    .no-print { display: none !important; }
                    body { padding: 0; }
                }
            </style>
        </head>
        <body onload='window.print()'>
            <div class='no-print mb-4 d-flex justify-content-between align-items-center'>
                <h4>" . htmlspecialchars($title) . "</h4>
                <button onclick='window.print()' class='btn btn-primary btn-sm'>Print / Save PDF</button>
            </div>
            {$htmlContent}
        </body>
        </html>";
        exit;
    }
}
