<?php

declare(strict_types=1);

function send_store_mail(string $to, string $subject, string $html): bool
{
    $config = require __DIR__ . '/config.php';
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $config['mail']['from_name'] . ' <' . $config['mail']['from_address'] . '>',
    ];

    return mail($to, $subject, $html, implode("\r\n", $headers));
}
