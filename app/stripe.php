<?php

declare(strict_types=1);

function verify_stripe_signature(string $payload, string $signatureHeader, string $secret, int $tolerance = 300): bool
{
    if ($secret === '' || $signatureHeader === '') {
        return false;
    }

    $timestamp = null;
    $signatures = [];
    foreach (explode(',', $signatureHeader) as $part) {
        [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($key === 't') {
            $timestamp = (int) $value;
        }
        if ($key === 'v1') {
            $signatures[] = $value;
        }
    }

    if (!$timestamp || abs(time() - $timestamp) > $tolerance) {
        return false;
    }

    $signedPayload = $timestamp . '.' . $payload;
    $expected = hash_hmac('sha256', $signedPayload, $secret);

    foreach ($signatures as $signature) {
        if (hash_equals($expected, $signature)) {
            return true;
        }
    }

    return false;
}

function stripe_create_checkout_session(int $orderId, array $lineItems, string $successUrl, string $cancelUrl): array
{
    $config = require __DIR__ . '/config.php';
    if ($config['stripe']['secret_key'] === '') {
        return ['id' => 'stripe_not_configured_' . $orderId, 'url' => $successUrl . '?order_id=' . $orderId . '&manual=1'];
    }

    $payload = [
        'mode' => 'payment',
        'success_url' => $successUrl . '?order_id=' . $orderId . '&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $cancelUrl,
        'metadata[order_id]' => (string) $orderId,
    ];
    $index = 0;
    foreach ($lineItems as $item) {
        $payload["line_items[$index][price_data][currency]"] = strtolower($item['currency'] ?? 'usd');
        $payload["line_items[$index][price_data][product_data][name]"] = $item['name'];
        $payload["line_items[$index][price_data][unit_amount]"] = (string) $item['unit_amount'];
        $payload["line_items[$index][quantity]"] = (string) $item['quantity'];
        $index++;
    }

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_USERPWD => $config['stripe']['secret_key'] . ':',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode((string) $response, true);
    if ($status < 200 || $status >= 300 || !is_array($decoded)) {
        throw new RuntimeException('Unable to create Stripe Checkout session.');
    }

    return $decoded;
}
