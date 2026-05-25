<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=itcity2', 'root', '');
$stmt = $pdo->prepare('DESCRIBE tenants');
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== SCHEMA DE TENANTS ===\n";
foreach ($result as $row) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== DATOS DE TENANTS ===\n";
$stmt2 = $pdo->prepare('SELECT * FROM tenants LIMIT 2');
$stmt2->execute();
$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($result2 as $row) {
    echo json_encode($row) . "\n";
}
?>
