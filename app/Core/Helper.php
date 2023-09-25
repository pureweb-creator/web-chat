<?php

namespace App\Core;

abstract class Helper
{
    public static function response($message=false, $success=true, $returnResponseToSession=false): void
    {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if (!$returnResponseToSession) {
            echo json_encode($response);
            die;
        }

        $_SESSION['response'] = $response;
    }

    public static function darken_color($rgb, $darker=2): string
    {
        $hash = (str_contains($rgb, '#')) ? '#' : '';
        $rgb = (strlen($rgb) == 7) ? str_replace('#', '', $rgb) : ((strlen($rgb) == 6) ? $rgb : false);
        if(strlen($rgb) != 6) return $hash.'000000';
        $darker = ($darker > 1) ? $darker : 1;

        list($R16,$G16,$B16) = str_split($rgb,2);

        $R = sprintf("%02X", floor(hexdec($R16)/$darker));
        $G = sprintf("%02X", floor(hexdec($G16)/$darker));
        $B = sprintf("%02X", floor(hexdec($B16)/$darker));

        return $hash.$R.$G.$B;
    }
}