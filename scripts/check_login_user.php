<?php

declare(strict_types=1);

$central = new PDO('mysql:host=127.0.0.1;dbname=itcity2', 'root', '');
$central->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$email = 'superadmin@demo.test';

$centralStmt = $central->prepare('SELECT id, email FROM users WHERE email = :email LIMIT 1');
$centralStmt->execute(['email' => $email]);
$centralUser = $centralStmt->fetch(PDO::FETCH_ASSOC);

echo "Central DB user: " . ($centralUser ? json_encode($centralUser) : 'NOT FOUND') . PHP_EOL;

$tenantStmt = $central->query('SELECT t.id, d.domain FROM tenants t LEFT JOIN domains d ON d.tenant_id = t.id');
$tenants = $tenantStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($tenants as $tenant) {
    $tenantId = (string) $tenant['id'];
    $dbName = 'tenant' . preg_replace('/[^A-Za-z0-9]/', '', $tenantId);

    try {
        $tenantPdo = new PDO('mysql:host=127.0.0.1;dbname=' . $dbName, 'root', '');
        $tenantPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $tenantPdo->prepare('SELECT id, email, is_active, auth_source FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $tenantUser = $stmt->fetch(PDO::FETCH_ASSOC);

        echo sprintf("Tenant %s (%s) DB=%s user: %s\n", $tenantId, $tenant['domain'] ?? '-', $dbName, $tenantUser ? json_encode($tenantUser) : 'NOT FOUND');
    } catch (Throwable $e) {
        echo sprintf("Tenant %s (%s) DB=%s error: %s\n", $tenantId, $tenant['domain'] ?? '-', $dbName, $e->getMessage());
    }
}
