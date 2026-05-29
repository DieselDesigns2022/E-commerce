<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
customer_logout();
redirect('/account/login.php');
