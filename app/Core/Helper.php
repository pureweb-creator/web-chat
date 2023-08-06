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
}