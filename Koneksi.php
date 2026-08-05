<?php
// Koneksi.php - koneksi PostgreSQL (PDO)
// Kredensial diambil dari config.local.php (tidak di-commit ke git).

$db_host = getenv('DPOKU_DB_HOST') ?: 'localhost';
$db_name = getenv('DPOKU_DB_NAME') ?: 'db_dpoku';
$db_user = getenv('DPOKU_DB_USER') ?: 'dpoku';
$db_pass = getenv('DPOKU_DB_PASS') ?: '';
$db_port = getenv('DPOKU_DB_PORT') ?: '5432';

$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
}

// Pastikan password selalu berupa string (jika null/bool, PDO pgsql error
// "SASL: SCRAM-SERVER-FIRST-MESSAGE: client password must be a string")
$db_pass = is_string($db_pass) ? $db_pass : (string) ($db_pass ?? '');

if (!isset($koneksidpogendeng)) {
    try {
        $koneksidpogendeng = new PDO(
            "pgsql:host=$db_host;port=$db_port;dbname=$db_name;user=$db_user",
            $db_user,
            $db_pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}
?>
