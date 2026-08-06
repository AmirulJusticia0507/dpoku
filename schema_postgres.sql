-- ============================================================
-- db_dpoku - PostgreSQL schema
-- Migrasi dari MySQL (AdminLTE) ke PostgreSQL (Tailwind UI)
-- ============================================================

CREATE TABLE IF NOT EXISTS instansi (
    id SERIAL PRIMARY KEY,
    nama_instansi VARCHAR(255) NOT NULL UNIQUE,
    keterangan_instansi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jenis_kasus (
    id SERIAL PRIMARY KEY,
    jenis_kasus VARCHAR(255) NOT NULL UNIQUE,
    deskripsi_kasus TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jenis_hukuman (
    id SERIAL PRIMARY KEY,
    jenis_hukuman VARCHAR(255) NOT NULL,
    lama_hukuman VARCHAR(100),
    vonis_putusan TEXT,
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (jenis_hukuman, lama_hukuman)
);

-- "user" di-quote karena reserved keyword di PostgreSQL
CREATE TABLE IF NOT EXISTS "user" (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(255),
    role VARCHAR(20) NOT NULL DEFAULT 'operator',
    jumlah_saldo_bounty BIGINT DEFAULT 0,
    amount_saldo BIGINT DEFAULT 0,
    email VARCHAR(255),
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bounty (
    id SERIAL PRIMARY KEY,
    jumlah_bounty BIGINT NOT NULL,
    id_kasus INT REFERENCES jenis_kasus(id) ON DELETE SET NULL,
    id_hukuman INT REFERENCES jenis_hukuman(id) ON DELETE SET NULL,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dpo (
    id SERIAL PRIMARY KEY,
    nik VARCHAR(50) UNIQUE,
    nama_lengkap VARCHAR(255) NOT NULL,
    tanggal_lahir DATE,
    jenis_kelamin VARCHAR(20),
    instansi_id INT REFERENCES instansi(id) ON DELETE SET NULL,
    jenis_kasus_id INT REFERENCES jenis_kasus(id) ON DELETE SET NULL,
    jenis_hukuman_id INT REFERENCES jenis_hukuman(id) ON DELETE SET NULL,
    no_hp VARCHAR(50),
    email VARCHAR(255),
    media_sosial VARCHAR(255),
    alamat TEXT,
    status_dpo VARCHAR(50) DEFAULT 'BURON',
    foto VARCHAR(500),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS barang_bukti (
    id SERIAL PRIMARY KEY,
    dpo_id INT REFERENCES dpo(id) ON DELETE CASCADE,
    nama_file VARCHAR(500),
    tipe_file VARCHAR(100),
    ukuran INT,
    keterangan TEXT,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dpo_status_log (
    id SERIAL PRIMARY KEY,
    dpo_id INT REFERENCES dpo(id) ON DELETE CASCADE,
    status_lama VARCHAR(50),
    status_baru VARCHAR(50),
    changed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

CREATE TABLE IF NOT EXISTS daftar_pejabat (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL DEFAULT '',
    jabatan VARCHAR(255) NOT NULL DEFAULT '',
    instansi VARCHAR(255) NOT NULL DEFAULT '',
    keterangan VARCHAR(500) NOT NULL DEFAULT '',
    sumber VARCHAR(20) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index untuk pencarian umum
CREATE INDEX IF NOT EXISTS idx_dpo_nik ON dpo (nik);
CREATE INDEX IF NOT EXISTS idx_dpo_nama ON dpo (nama_lengkap);
CREATE INDEX IF NOT EXISTS idx_dpo_instansi ON dpo (instansi_id);
CREATE INDEX IF NOT EXISTS idx_dpo_status ON dpo (status_dpo);
CREATE INDEX IF NOT EXISTS idx_bukti_dpo ON barang_bukti (dpo_id);
CREATE INDEX IF NOT EXISTS idx_audit_action ON audit_log (action);
CREATE INDEX IF NOT EXISTS idx_pejabat_nama ON daftar_pejabat (nama);
CREATE INDEX IF NOT EXISTS idx_pejabat_instansi ON daftar_pejabat (instansi);
CREATE INDEX IF NOT EXISTS idx_pejabat_sumber ON daftar_pejabat (sumber);

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT INTO instansi (nama_instansi, keterangan_instansi) VALUES
('Kepolisian RI', 'Instansi penegak hukum'),
('Kejaksaan Agung', 'Instansi penuntut umum')
ON CONFLICT DO NOTHING;

INSERT INTO jenis_kasus (jenis_kasus, deskripsi_kasus) VALUES
('Pembunuhan', 'Kasus pembunuhan berencana'),
('Penipuan', 'Kasus penipuan / penggelapan'),
('Pembunuhan Berat', 'Pembunuhan dengan pemberatan'),
('Penganiayaan', 'Penganiayaan berat atau ringan'),
('Penculikan', 'Penculikan anak atau orang dewasa'),
('Perampokan', 'Perampokan dengan kekerasan'),
('Pencurian', 'Pencurian biasa atau dengan pemberatan (curat)'),
('Pencurian Kendaraan', 'Pencurian kendaraan bermotor (curanmor)'),
('Penggelapan', 'Penggelapan barang atau uang'),
('Korupsi', 'Tindak pidana korupsi'),
('Pencucian Uang', 'Tindak pidana pencucian uang (TPPU)'),
('Narkotika', 'Penyalahgunaan dan peredaran narkotika'),
('Perjudian', 'Perjudian online atau konvensional'),
('Pemerkosaan', 'Tindak pidana pemerkosaan'),
('Pencabulan', 'Tindak pidana pencabulan'),
('KDRT', 'Kekerasan dalam rumah tangga'),
('Perdagangan Orang', 'Tindak pidana perdagangan orang (TPPO)'),
('Penyelundupan', 'Penyelundupan barang atau manusia'),
('Pengancaman', 'Pengancaman atau teror'),
('Pemalsuan', 'Pemalsuan dokumen, surat, atau uang'),
('Penadahan', 'Tindak pidana penadahan barang curian'),
('Terorisme', 'Tindak pidana terorisme'),
('Cyber Crime', 'Kejahatan siber / ITE'),
('Penculikan Anak', 'Penculikan anak di bawah umur')
ON CONFLICT DO NOTHING;

INSERT INTO jenis_hukuman (jenis_hukuman, lama_hukuman, vonis_putusan, status) VALUES
('Pidana Penjara', '1 tahun', 'Vonis 1 tahun penjara', 'Aktif'),
('Pidana Penjara', '2 tahun', 'Vonis 2 tahun penjara', 'Aktif'),
('Pidana Penjara', '3 tahun', 'Vonis 3 tahun penjara', 'Aktif'),
('Pidana Penjara', '5 tahun', 'Vonis 5 tahun penjara', 'Aktif'),
('Pidana Penjara', '7 tahun', 'Vonis 7 tahun penjara', 'Aktif'),
('Pidana Penjara', '10 tahun', 'Vonis 10 tahun penjara', 'Aktif'),
('Pidana Penjara', '15 tahun', 'Vonis 15 tahun penjara', 'Aktif'),
('Pidana Penjara', '20 tahun', 'Vonis 20 tahun penjara', 'Aktif'),
('Seumur Hidup', 'Seumur hidup', 'Vonis penjara seumur hidup', 'Aktif'),
('Hukuman Mati', 'Mati', 'Vonis hukuman mati', 'Aktif'),
('Denda', 'Rp100.000.000', 'Denda 100 juta rupiah', 'Aktif'),
('Denda', 'Rp500.000.000', 'Denda 500 juta rupiah', 'Aktif'),
('Denda', 'Rp1.000.000.000', 'Denda 1 miliar rupiah', 'Aktif'),
('Pidana Ringan', '3 bulan', 'Vonis pidana ringan 3 bulan', 'Aktif'),
('Pidana Ringan', '6 bulan', 'Vonis pidana ringan 6 bulan', 'Aktif'),
('Percobaan', '2 tahun', 'Hukuman percobaan 2 tahun', 'Aktif'),
('Restitusi', 'Rp250.000.000', 'Kewajiban membayar ganti rugi 250 juta', 'Aktif')
ON CONFLICT DO NOTHING;

-- ============================================================
-- SEED BOUNTY per jenis kasus (idempotent: hanya jika belum ada)
-- ============================================================
INSERT INTO bounty (jumlah_bounty, id_kasus)
SELECT v.jumlah, k.id
FROM (VALUES
    ('Korupsi',              100000000),
    ('Pembunuhan',           150000000),
    ('Narkotika',             75000000),
    ('Pencucian Uang',       100000000),
    ('Penipuan',              25000000),
    ('Perampokan',            50000000),
    ('Terorisme',            200000000),
    ('Penculikan Anak',       60000000),
    ('Pembunuhan Berat',     175000000),
    ('Penggelapan',           30000000),
    ('Perdagangan Orang',     80000000),
    ('Cyber Crime',           40000000)
) AS v(jenis, jumlah)
JOIN jenis_kasus k ON k.jenis_kasus = v.jenis
WHERE NOT EXISTS (SELECT 1 FROM bounty b WHERE b.id_kasus = k.id);

-- ============================================================
-- SEED DATA DPO contoh (fiktif) - ON CONFLICT (nik) DO NOTHING
-- ============================================================
INSERT INTO dpo
(nik, nama_lengkap, tanggal_lahir, jenis_kelamin, instansi_id, jenis_kasus_id, jenis_hukuman_id,
 no_hp, email, media_sosial, alamat, status_dpo)
SELECT
    '3201011508910001', 'Bagus Pratama', '1991-08-15', 'LAKI-LAKI',
    (SELECT id FROM instansi WHERE nama_instansi = 'Kejaksaan Agung' LIMIT 1),
    (SELECT id FROM jenis_kasus WHERE jenis_kasus = 'Korupsi' LIMIT 1),
    (SELECT id FROM jenis_hukuman WHERE jenis_hukuman = 'Pidana Penjara' AND lama_hukuman = '10 tahun' LIMIT 1),
    '081234567801', 'bagus.pratama@example.com', 'fb.com/bagus', 'Jl. Melati No. 12, Bogor', 'BURON'
ON CONFLICT (nik) DO NOTHING;

INSERT INTO dpo
(nik, nama_lengkap, tanggal_lahir, jenis_kelamin, instansi_id, jenis_kasus_id, jenis_hukuman_id,
 no_hp, email, media_sosial, alamat, status_dpo)
SELECT
    '3173027109850002', 'Siti Rahayu', '1985-11-01', 'PEREMPUAN',
    (SELECT id FROM instansi WHERE nama_instansi = 'Kepolisian RI' LIMIT 1),
    (SELECT id FROM jenis_kasus WHERE jenis_kasus = 'Penipuan' LIMIT 1),
    (SELECT id FROM jenis_hukuman WHERE jenis_hukuman = 'Pidana Penjara' AND lama_hukuman = '5 tahun' LIMIT 1),
    '081234567802', 'siti.rahayu@example.com', 'ig.com/sitirahayu', 'Jl. Kenanga No. 5, Jakarta Timur', 'BURON'
ON CONFLICT (nik) DO NOTHING;

INSERT INTO dpo
(nik, nama_lengkap, tanggal_lahir, jenis_kelamin, instansi_id, jenis_kasus_id, jenis_hukuman_id,
 no_hp, email, media_sosial, alamat, status_dpo)
SELECT
    '3501021207930003', 'Andri Firmansyah', '1993-07-12', 'LAKI-LAKI',
    (SELECT id FROM instansi WHERE nama_instansi = 'Kepolisian RI' LIMIT 1),
    (SELECT id FROM jenis_kasus WHERE jenis_kasus = 'Narkotika' LIMIT 1),
    (SELECT id FROM jenis_hukuman WHERE jenis_hukuman = 'Pidana Penjara' AND lama_hukuman = '15 tahun' LIMIT 1),
    '081234567803', 'andri.f@example.com', 'ig.com/andrif', 'Jl. Mawar No. 8, Malang', 'TERTANGKAP'
ON CONFLICT (nik) DO NOTHING;

INSERT INTO dpo
(nik, nama_lengkap, tanggal_lahir, jenis_kelamin, instansi_id, jenis_kasus_id, jenis_hukuman_id,
 no_hp, email, media_sosial, alamat, status_dpo)
SELECT
    '3674032507880004', 'Rudi Hartono', '1988-03-25', 'LAKI-LAKI',
    (SELECT id FROM instansi WHERE nama_instansi = 'Kepolisian RI' LIMIT 1),
    (SELECT id FROM jenis_kasus WHERE jenis_kasus = 'Pembunuhan' LIMIT 1),
    (SELECT id FROM jenis_hukuman WHERE jenis_hukuman = 'Seumur Hidup' LIMIT 1),
    '081234567804', 'rudi.h@example.com', 'fb.com/rudi', 'Jl. Flamboyan No. 3, Depok', 'BURON'
ON CONFLICT (nik) DO NOTHING;

INSERT INTO dpo
(nik, nama_lengkap, tanggal_lahir, jenis_kelamin, instansi_id, jenis_kasus_id, jenis_hukuman_id,
 no_hp, email, media_sosial, alamat, status_dpo)
SELECT
    '3301045209900005', 'Dewi Lestari', '1990-10-12', 'PEREMPUAN',
    (SELECT id FROM instansi WHERE nama_instansi = 'Kejaksaan Agung' LIMIT 1),
    (SELECT id FROM jenis_kasus WHERE jenis_kasus = 'Pencucian Uang' LIMIT 1),
    (SELECT id FROM jenis_hukuman WHERE jenis_hukuman = 'Pidana Penjara' AND lama_hukuman = '7 tahun' LIMIT 1),
    '081234567805', 'dewi.lestari@example.com', 'ig.com/dewi', 'Jl. Anggrek No. 21, Semarang', 'BURON'
ON CONFLICT (nik) DO NOTHING;

INSERT INTO dpo
(nik, nama_lengkap, tanggal_lahir, jenis_kelamin, instansi_id, jenis_kasus_id, jenis_hukuman_id,
 no_hp, email, media_sosial, alamat, status_dpo)
SELECT
    '3515071005900006', 'Joko Santoso', '1990-05-10', 'LAKI-LAKI',
    (SELECT id FROM instansi WHERE nama_instansi = 'Kepolisian RI' LIMIT 1),
    (SELECT id FROM jenis_kasus WHERE jenis_kasus = 'Penculikan Anak' LIMIT 1),
    (SELECT id FROM jenis_hukuman WHERE jenis_hukuman = 'Pidana Penjara' AND lama_hukuman = '20 tahun' LIMIT 1),
    '081234567806', 'joko.s@example.com', 'fb.com/joko', 'Jl. Cemara No. 9, Surabaya', 'MENINGGAL DUNIA'
ON CONFLICT (nik) DO NOTHING;
