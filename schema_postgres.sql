-- ============================================================
-- db_dpoku - PostgreSQL schema
-- Migrasi dari MySQL (AdminLTE) ke PostgreSQL (Tailwind UI)
-- ============================================================

CREATE TABLE IF NOT EXISTS instansi (
    id SERIAL PRIMARY KEY,
    nama_instansi VARCHAR(255) NOT NULL,
    keterangan_instansi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jenis_kasus (
    id SERIAL PRIMARY KEY,
    jenis_kasus VARCHAR(255) NOT NULL,
    deskripsi_kasus TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jenis_hukuman (
    id SERIAL PRIMARY KEY,
    jenis_hukuman VARCHAR(255) NOT NULL,
    lama_hukuman VARCHAR(100),
    vonis_putusan TEXT,
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- "user" di-quote karena reserved keyword di PostgreSQL
CREATE TABLE IF NOT EXISTS "user" (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(255),
    jumlah_saldo_bounty BIGINT DEFAULT 0,
    amount_saldo BIGINT DEFAULT 0,
    email VARCHAR(255),
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bounty (
    id SERIAL PRIMARY KEY,
    jumlah_bounty BIGINT NOT NULL,
    id_kasus INT,
    id_hukuman INT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dpo (
    id SERIAL PRIMARY KEY,
    nik VARCHAR(50),
    nama_lengkap VARCHAR(255),
    tanggal_lahir DATE,
    jenis_kelamin VARCHAR(20),
    nama_instansi VARCHAR(255),
    jenis_kasus VARCHAR(255),
    jenis_hukuman VARCHAR(255),
    no_hp VARCHAR(50),
    email VARCHAR(255),
    media_sosial VARCHAR(255),
    alamat TEXT,
    status_dpo VARCHAR(50),
    foto VARCHAR(500),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_log (
    id SERIAL PRIMARY KEY,
    user_id INT,
    username VARCHAR(100),
    action VARCHAR(50),
    table_name VARCHAR(100),
    record_id INT,
    detail TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index untuk pencarian umum
CREATE INDEX IF NOT EXISTS idx_dpo_nik ON dpo (nik);
CREATE INDEX IF NOT EXISTS idx_dpo_nama ON dpo (nama_lengkap);
CREATE INDEX IF NOT EXISTS idx_dpo_instansi ON dpo (nama_instansi);
CREATE INDEX IF NOT EXISTS idx_audit_action ON audit_log (action);

-- User default (password: admin123 -> password_hash)
-- Ganti setelah login pertama.
-- Password hash di-buat lewat aplikasi agar konsisten (bcrypt).
