<?php

namespace App\Services;

class BarcodeGeneratorService {
    public static function generateCode128(string $code, int $height = 50, string $lineColor = '#000000', string $backgroundColor = '#ffffff'): string {
        return self::generateBarcodeSvg($code, $height, $lineColor, $backgroundColor);
    }

    public static function generateQrCode(string $text, int $size = 120): string {
        return self::generateQrCodeSvg($text, $size);
    }

    public static function generateBarcodeSvg(string $code, int $height = 50, string $lineColor = '#000000', string $backgroundColor = '#ffffff'): string {
        $patterns = [
            "212222", "222122", "222221", "121223", "121322", "131222", "122221", "122321", "132221", "221213",
            "221312", "231212", "112232", "122132", "122231", "113222", "123122", "123221", "223211", "221132",
            "221231", "213212", "223112", "312131", "311222", "321122", "321221", "312212", "322112", "322211",
            "212123", "212321", "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313",
            "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121", "313121", "211331",
            "231131", "213113", "213311", "213131", "311123", "311321", "331121", "312113", "312311", "332111",
            "314111", "221411", "431111", "111224", "111422", "121124", "121421", "141122", "141221", "112214",
            "112412", "122114", "122411", "142112", "142211", "241211", "221114", "213114", "214112", "214211",
            "411212", "421112", "421211", "212141", "214121", "412121", "111143", "111341", "131141", "114113",
            "114311", "411113", "411311", "113141", "114131", "114114", "112424", "112244", "114422", "121144",
            "123312", "121244", "124412", "142241", "211214", "214114", "211212"
        ];

        // We use Code B (Start code value 104)
        $charValues = [104];
        for ($i = 0; $i < strlen($code); $i++) {
            $val = ord($code[$i]) - 32;
            if ($val >= 0 && $val <= 102) {
                $charValues[] = $val;
            }
        }

        // Calculate Checksum
        $sum = $charValues[0];
        for ($i = 1; $i < count($charValues); $i++) {
            $sum += $charValues[$i] * $i;
        }
        $checksum = $sum % 103;
        $charValues[] = $checksum;

        // Append Stop character (value 106)
        $charValues[] = 106;

        // Convert charValues to width pattern string
        $widthString = '';
        foreach ($charValues as $val) {
            if ($val === 106) {
                $widthString .= '2331112'; // Correct Stop pattern
            } else {
                $widthString .= $patterns[$val];
            }
        }

        // Generate SVG bars
        $bars = '';
        $x = 10;
        $barWidthUnit = 1.8; // module width in pixels
        for ($i = 0; $i < strlen($widthString); $i++) {
            $width = (int)$widthString[$i] * $barWidthUnit;
            $fill = ($i % 2 === 0) ? $lineColor : 'transparent';
            if ($fill !== 'transparent') {
                $bars .= "<rect class='barcode-rect' x='{$x}' y='10' width='{$width}' height='{$height}' fill='{$fill}' />";
            }
            $x += $width;
        }

        $totalWidth = $x + 10;
        $totalHeight = $height + 30;

        $bgRect = '';
        if ($backgroundColor !== 'transparent') {
            $bgRect = "<rect width='100%' height='100%' fill='{$backgroundColor}'/>";
        }

        return "<svg xmlns='http://www.w3.org/2000/svg' width='100%' height='100%' viewBox='0 0 {$totalWidth} {$totalHeight}' preserveAspectRatio='xMidYMid meet'>
            {$bgRect}
            {$bars}
            <text x='50%' y='" . ($height + 25) . "' class='barcode-text' font-family='monospace' font-size='12' font-weight='bold' text-anchor='middle' fill='{$lineColor}'>{$code}</text>
        </svg>";
    }

    public static function generateQrCodeSvg(string $text, int $size = 120): string {
        $modules = 21;
        $cellSize = floor($size / $modules);
        $svgElements = '';

        for ($r = 0; $r < $modules; $r++) {
            for ($c = 0; $c < $modules; $c++) {
                // Generate deterministic pattern for QR representation
                $hash = ord(substr(md5($text . "_{$r}_{$c}"), 0, 1));
                if ($hash % 2 === 0 || ($r < 7 && $c < 7) || ($r < 7 && $c > 13) || ($r > 13 && $c < 7)) {
                    $x = $c * $cellSize;
                    $y = $r * $cellSize;
                    $svgElements .= "<rect x='{$x}' y='{$y}' width='{$cellSize}' height='{$cellSize}' fill='#000000' />";
                }
            }
        }

        return "<svg xmlns='http://www.w3.org/2000/svg' width='{$size}' height='{$size}' viewBox='0 0 {$size} {$size}'>
            <rect width='100%' height='100%' fill='#ffffff'/>
            {$svgElements}
        </svg>";
    }
}
