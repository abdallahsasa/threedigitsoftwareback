<?php

namespace App\Services;

class ArabicPdfHelper
{
    /**
     * Arabic letters and their connected forms:
     * [Isolated, Final, Initial, Medial]
     */
    protected static array $glyphs = [
        0x0621 => [0xFE80, 0xFE80, 0xFE80, 0xFE80], // Hamza
        0x0622 => [0xFE81, 0xFE82, 0xFE81, 0xFE82], // Alef with Madda
        0x0623 => [0xFE83, 0xFE84, 0xFE83, 0xFE84], // Alef with Hamza above
        0x0624 => [0xFE85, 0xFE86, 0xFE85, 0xFE86], // Waw with Hamza
        0x0625 => [0xFE87, 0xFE88, 0xFE87, 0xFE88], // Alef with Hamza below
        0x0626 => [0xFE89, 0xFE8A, 0xFE8B, 0xFE8C], // Yeh with Hamza
        0x0627 => [0xFE8D, 0xFE8E, 0xFE8D, 0xFE8E], // Alef
        0x0628 => [0xFE8F, 0xFE90, 0xFE91, 0xFE92], // Beh
        0x0629 => [0xFE93, 0xFE94, 0xFE93, 0xFE94], // Teh Marbuta
        0x062A => [0xFE95, 0xFE96, 0xFE97, 0xFE98], // Teh
        0x062B => [0xFE99, 0xFE9A, 0xFE9B, 0xFE9C], // Theh
        0x062C => [0xFE9D, 0xFE9E, 0xFE9F, 0xFEA0], // Jeem
        0x062D => [0xFEA1, 0xFEA2, 0xFEA3, 0xFEA4], // Hah
        0x062E => [0xFEA5, 0xFEA6, 0xFEA7, 0xFEA8], // Khah
        0x062F => [0xFEA9, 0xFEAA, 0xFEA9, 0xFEAA], // Dal
        0x0630 => [0xFEAB, 0xFEAC, 0xFEAB, 0xFEAC], // Thal
        0x0631 => [0xFEAD, 0xFEAE, 0xFEAD, 0xFEAE], // Reh
        0x0632 => [0xFEAF, 0xFEB0, 0xFEAF, 0xFEB0], // Zain
        0x0633 => [0xFEB1, 0xFEB2, 0xFEB3, 0xFEB4], // Seen
        0x0634 => [0xFEB5, 0xFEB6, 0xFEB7, 0xFEB8], // Sheen
        0x0635 => [0xFEB9, 0xFEBA, 0xFEBB, 0xFEBC], // Sad
        0x0636 => [0xFEBD, 0xFEBE, 0xFEBF, 0xFEC0], // Dad
        0x0637 => [0xFEC1, 0xFEC2, 0xFEC3, 0xFEC4], // Tah
        0x0638 => [0xFEC5, 0xFEC6, 0xFEC7, 0xFEC8], // Zah
        0x0639 => [0xFEC9, 0xFECA, 0xFECB, 0xFECC], // Ain
        0x063A => [0xFECD, 0xFECE, 0xFECF, 0xFED0], // Ghain
        0x0641 => [0xFED1, 0xFED2, 0xFED3, 0xFED4], // Feh
        0x0642 => [0xFED5, 0xFED6, 0xFED7, 0xFED8], // Qaf
        0x0643 => [0xFED9, 0xFEDA, 0xFEDB, 0xFEDC], // Kaf
        0x0644 => [0xFEDD, 0xFEDE, 0xFEDF, 0xFEE0], // Lam
        0x0645 => [0xFEE1, 0xFEE2, 0xFEE3, 0xFEE4], // Meem
        0x0646 => [0xFEE5, 0xFEE6, 0xFEE7, 0xFEE8], // Noon
        0x0647 => [0xFEE9, 0xFEEA, 0xFEEB, 0xFEEC], // Heh
        0x0648 => [0xFEED, 0xFEEE, 0xFEED, 0xFEEE], // Waw
        0x0649 => [0xFEEF, 0xFEF0, 0xFBE8, 0xFBE9], // Alef Maksura
        0x064A => [0xFEF1, 0xFEF2, 0xFEF3, 0xFEF4], // Yeh
        0x067E => [0xFB56, 0xFB57, 0xFB58, 0xFB59], // Peh
        0x0686 => [0xFB7A, 0xFB7B, 0xFB7C, 0xFB7D], // Tcheh
        0x0698 => [0xFB8A, 0xFB8B, 0xFB8A, 0xFB8B], // Jeh
        0x06AF => [0xFB92, 0xFB93, 0xFB94, 0xFB95], // Gaf
    ];

    /**
     * Letters that only connect from the right (preceding letter)
     */
    protected static array $rightConnectingOnly = [
        0x0622, 0x0623, 0x0624, 0x0625, 0x0627, 0x062F, 0x0630, 0x0631, 0x0632, 0x0648, 0x0698
    ];

    /**
     * Lam-Alef ligatures
     */
    protected static array $lamAlef = [
        0x0622 => [0xFEF5, 0xFEF6], // Lam + Alef with Madda
        0x0623 => [0xFEF7, 0xFEF8], // Lam + Alef with Hamza above
        0x0625 => [0xFEF9, 0xFEFA], // Lam + Alef with Hamza below
        0x0627 => [0xFEFB, 0xFEFC], // Lam + Alef
    ];

    /**
     * Reshape Arabic text into connected Unicode Presentation Forms
     */
    public static function reshape(string $text): string
    {
        if (empty($text) || !preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text;
        }

        $chars = mb_str_split($text, 1, 'UTF-8');
        $len = count($chars);
        $codes = [];

        foreach ($chars as $c) {
            $codes[] = self::ordUtf8($c);
        }

        $reshaped = [];
        $i = 0;

        while ($i < $len) {
            $current = $codes[$i];

            // Handle Lam-Alef ligatures
            if ($current === 0x0644 && isset($codes[$i + 1]) && isset(self::$lamAlef[$codes[$i + 1]])) {
                $alefCode = $codes[$i + 1];
                $prevCode = ($i > 0) ? $codes[$i - 1] : null;
                $prevConnects = $prevCode && isset(self::$glyphs[$prevCode]) && !in_array($prevCode, self::$rightConnectingOnly);

                $ligature = $prevConnects ? self::$lamAlef[$alefCode][1] : self::$lamAlef[$alefCode][0];
                $reshaped[] = self::chrUtf8($ligature);
                $i += 2;
                continue;
            }

            if (!isset(self::$glyphs[$current])) {
                $reshaped[] = $chars[$i];
                $i++;
                continue;
            }

            $prevCode = ($i > 0) ? $codes[$i - 1] : null;
            $nextCode = ($i + 1 < $len) ? $codes[$i + 1] : null;

            $prevConnects = $prevCode && isset(self::$glyphs[$prevCode]) && !in_array($prevCode, self::$rightConnectingOnly);
            $nextConnects = $nextCode && isset(self::$glyphs[$nextCode]) && !in_array($current, self::$rightConnectingOnly);

            if ($prevConnects && $nextConnects) {
                $form = self::$glyphs[$current][3]; // Medial
            } elseif ($prevConnects) {
                $form = self::$glyphs[$current][1]; // Final
            } elseif ($nextConnects) {
                $form = self::$glyphs[$current][2]; // Initial
            } else {
                $form = self::$glyphs[$current][0]; // Isolated
            }

            $reshaped[] = self::chrUtf8($form);
            $i++;
        }

        return implode('', $reshaped);
    }

    /**
     * Bidi helper for DomPDF: reshapes Arabic and properly handles RTL display
     */
    public static function formatForPdf(string $text, bool $isRtl = true): string
    {
        if (empty($text)) {
            return '';
        }

        if (!$isRtl) {
            return $text;
        }

        // Reshape Arabic glyphs
        $reshaped = self::reshape($text);

        // In DomPDF with RTL CSS direction, reshaped glyphs render in native cursive font
        return $reshaped;
    }

    protected static function ordUtf8(string $char): int
    {
        $code = unpack('N', mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'));
        return $code[1] ?? 0;
    }

    protected static function chrUtf8(int $code): string
    {
        return mb_convert_encoding(pack('N', $code), 'UTF-8', 'UCS-4BE');
    }
}
