<?php

declare(strict_types=1);

http_response_code(501);
?>
Secure controlled downloads are scaffolded. TODO: validate signed token, enforce limits/expiration, then stream files from storage/digital-files.
