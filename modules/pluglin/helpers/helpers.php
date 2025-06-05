<?php

if (!function_exists('d')) {
    function d()
    {
        array_map(function ($x) {
            dump($x);
        }, func_get_args());
    }
}

if (!function_exists('dd')) {
    function dd()
    {
        d(func_get_args());
        die;
    }
}

if (!(function_exists('jsonResponse'))) {
    function jsonResponse(array $data = [], int $statusCode = 200, array $headers = []): bool
    {
        header('Content-Type: application/json');

        // if content type is defined in this array
        // it will override the default one.
        foreach ($headers as $header => $content) {
            header("$header: $content");
        }

        if (!empty($data)) {
            echo json_encode($data);
        }

        http_response_code($statusCode);
        exit;
    }
}
