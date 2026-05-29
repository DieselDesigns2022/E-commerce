<?php

declare(strict_types=1);

?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Installer</title><link rel="stylesheet" href="/assets/admin.css"></head><body class="login-page"><section class="card login-card"><h1>Store Installer</h1><p>This install-ready foundation currently uses a manual installer:</p><ol><li>Copy <code>.env.example</code> to <code>.env</code>.</li><li>Create a MySQL database.</li><li>Import <code>database/schema.sql</code>.</li><li>Create your first owner admin with a bcrypt password hash.</li></ol><p>TODO: turn this into a guarded web installer that creates .env, imports schema, creates owner admin, then locks itself.</p></section></body></html>
