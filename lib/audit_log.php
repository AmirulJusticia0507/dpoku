<?php
// lib/audit_log.php  — Helper pencatatan audit log
include 'Koneksi.php';

/**
 * Mencatat aktivitas pengguna ke tabel audit_log.
 *
 * @param string      $action Nama aksi (create/update/delete/login/export)
 * @param string      $table  Nama tabel terdampak
 * @param int|null    $recordId  ID baris yang terlibat
 * @param string|null $detailDeskripsi  Deskripsi tambahan
 * @return bool
 */
function log_audit($action, $table, $recordId = null, $detailDeskripsi = null)
{
    global $koneksidpogendeng;

    $userId   = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? null;
    $ip       = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? ($_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''));

    if ($ip !== '' && strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    $action    = (string) $action;
    $table     = (string) $table;
    $recordId  = $recordId  === null ? null : (int) $recordId;
    $detail    = $detailDeskripsi === null ? null : (string) $detailDeskripsi;
    $ip        = $ip !== '' ? substr((string) $ip, 0, 45) : null;

    $stmt = $koneksidpogendeng->prepare(
        "INSERT INTO audit_log (user_id, username, action, table_name, record_id, detail, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $ok = $stmt->execute([$userId, $username, $action, $table, $recordId, $detail, $ip]);
    return $ok;
}

/**
 * Membungkus operasi DB sehingga otomatis tercatat audit-log-nya.
 * Contoh: audit_db_op($koneksidpogendeng, 'insert', 'dpo', $id, 'Tambah DPO NIK 123');
 */
function audit_db_op($conn, $type, $table, $recordId, $description)
{
    $map = ['create' => 'create', 'update' => 'update', 'delete' => 'delete', 'read' => 'read'];
    $action = $map[$type] ?? $type;
    log_audit($action, $table, $recordId, $description);
}
