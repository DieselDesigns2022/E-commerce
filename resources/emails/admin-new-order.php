<?php
/** @var array $order */
?>
<!doctype html><html><body style="font-family:Arial,sans-serif"><h1>New order</h1><p>Order <?= htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?> was paid.</p></body></html>
