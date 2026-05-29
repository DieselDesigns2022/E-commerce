<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/helpers.php';

session_start();
$message = 'Customer accounts are scaffolded. Full login/register/password reset logic is a TODO module after admin/order flows are stabilized.';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Customer Login</title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body><main class="section"><div class="container"><form class="card form-grid"><h1 class="full">Customer Login</h1><p class="full"><?= e($message) ?></p><label>Email <input type="email"></label><label>Password <input type="password"></label><div class="full actions"><button class="button" type="button">TODO: Login</button><a href="/account/register.php">Register</a></div></form></div></main></body></html>
