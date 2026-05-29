<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/auth.php';
require_once dirname(__DIR__, 3) . '/app/db.php';

$admin = require_admin();
if (($admin['role'] ?? '') !== 'owner') { http_response_code(403); exit('Owner access required.'); }
$migrations = db_select('SELECT * FROM migrations ORDER BY executed_at DESC');
$title = 'Migrations';
$nav = 'super';
require dirname(__DIR__, 3) . '/resources/views/admin/header.php';
?>
<section class="card"><h2>Migration Status</h2><p>TODO: Add safe web-triggered migration runner with backups in Phase 4. Current installs import <code>database/schema.sql</code>.</p><table class="admin-table"><thead><tr><th>Migration</th><th>Batch</th><th>Executed</th></tr></thead><tbody><?php foreach ($migrations as $migration): ?><tr><td><?= e($migration['migration']) ?></td><td><?= (int) $migration['batch'] ?></td><td><?= e($migration['executed_at']) ?></td></tr><?php endforeach; ?><?php if (!$migrations): ?><tr><td colspan="3">No migrations recorded.</td></tr><?php endif; ?></tbody></table></section>
<?php require dirname(__DIR__, 3) . '/resources/views/admin/footer.php'; ?>
