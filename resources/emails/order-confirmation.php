<?php
/** @var array $order */
/** @var array $settings */
?>
<!doctype html><html><body style="font-family:Arial,sans-serif"><h1>Thank you for your order</h1><p>Your order <?= htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?> has been received.</p></body></html>
