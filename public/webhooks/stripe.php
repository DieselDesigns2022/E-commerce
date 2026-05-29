<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/stripe.php';
require_once dirname(__DIR__, 2) . '/app/models/Order.php';
require_once dirname(__DIR__, 2) . '/app/mail.php';
require_once dirname(__DIR__, 2) . '/app/models/Setting.php';

$config = require dirname(__DIR__, 2) . '/app/config.php';
$payload = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (!verify_stripe_signature($payload, $signature, $config['stripe']['webhook_secret'])) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

$event = json_decode($payload, true);
if (!is_array($event)) {
    http_response_code(400);
    echo 'Invalid payload';
    exit;
}

if (($event['type'] ?? '') === 'checkout.session.completed') {
    $session = $event['data']['object'] ?? [];
    $sessionId = (string) ($session['id'] ?? '');
    $paymentIntent = isset($session['payment_intent']) ? (string) $session['payment_intent'] : null;
    if ($sessionId !== '') {
        order_mark_paid_by_stripe($sessionId, $paymentIntent);
        $order = db_first('SELECT * FROM orders WHERE stripe_checkout_session_id = ?', [$sessionId]);
        if ($order) {
            $settings = settings_all();
            send_store_mail($order['customer_email'], 'Order confirmation ' . $order['order_number'], '<p>Thank you for your order ' . htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') . '.</p>');
            if (!empty($settings['store_email'])) {
                send_store_mail($settings['store_email'], 'New order ' . $order['order_number'], '<p>A new order has been paid.</p>');
            }
        }
    }
}

http_response_code(200);
echo 'ok';
