-- ============================================================
-- SPK Bingxue Rancaekek - Database Schema
-- Simple Additive Weighting (SAW) Method
-- ============================================================
CREATE DATABASE IF NOT EXISTS spk_bingxue CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spk_bingxue;

-- Users (Applicants & Admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Criteria (C1-C5)
CREATE TABLE IF NOT EXISTS criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    type ENUM('benefit','cost') NOT NULL DEFAULT 'benefit',
    description TEXT
) ENGINE=InnoDB;

-- Sub-criteria (values for each criterion)
CREATE TABLE IF NOT EXISTS sub_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    criteria_id INT NOT NULL,
    label VARCHAR(150) NOT NULL,
    value DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (criteria_id) REFERENCES criteria(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Pre-Test Questions
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Applicants (pelamar) - stores all assessment data
CREATE TABLE IF NOT EXISTS applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    -- C1: Skill (Self-Assessment)
    skill_communication DECIMAL(5,2) DEFAULT 0,
    skill_cooperation DECIMAL(5,2) DEFAULT 0,
    skill_ethics DECIMAL(5,2) DEFAULT 0,
    skill_technical DECIMAL(5,2) DEFAULT 0,
    c1_score DECIMAL(5,2) DEFAULT 0,
    -- C2: Pengalaman
    experience_years VARCHAR(50) DEFAULT NULL,
    c2_score DECIMAL(5,2) DEFAULT 0,
    -- C3: Pre-Test
    pretest_score DECIMAL(5,2) DEFAULT 0,
    c3_score DECIMAL(5,2) DEFAULT 0,
    pretest_taken TINYINT(1) DEFAULT 0,
    -- C4: Pendidikan
    education VARCHAR(50) DEFAULT NULL,
    c4_score DECIMAL(5,2) DEFAULT 0,
    -- C5: Umur
    age INT DEFAULT 0,
    c5_score DECIMAL(5,2) DEFAULT 0,
    -- SAW Result
    saw_value DECIMAL(10,6) DEFAULT 0,
    `rank` INT DEFAULT 0,
    -- Status
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    personal_data_filled TINYINT(1) DEFAULT 0,
    self_assessment_filled TINYINT(1) DEFAULT 0,
    submitted_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE applicants ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Employee records
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    applicant_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    position VARCHAR(100) DEFAULT 'Staff',
    join_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    status ENUM('active','resigned','terminated') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE employees ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE employees ADD FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE;

-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Admin account (password: admin123)
INSERT IGNORE INTO users (name, email, password, role) VALUES
('Administrator', 'admin@bingxue.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Criteria
INSERT INTO criteria (code, name, weight, type, description) VALUES
('C1', 'Skill', 30.00, 'benefit', 'Penilaian keterampilan: Komunikasi, Kerjasama, Etika, Teknis'),
('C2', 'Pengalaman Kerja', 25.00, 'benefit', 'Lama pengalaman kerja pelamar'),
('C3', 'Nilai Pre-Test', 20.00, 'benefit', 'Hasil tes pengetahuan umum dan potensi'),
('C4', 'Pendidikan', 15.00, 'benefit', 'Tingkat pendidikan terakhir pelamar'),
('C5', 'Umur', 10.00, 'benefit', 'Usia pelamar dalam tahun');

-- Sub-criteria C1: Skill (Communication sub-weight 40, Cooperation 30, Ethics 20, Technical 10)
INSERT INTO sub_criteria (criteria_id, label, value) VALUES
((SELECT id FROM criteria WHERE code='C1'), 'Komunikasi (bobot 40%)', 40.00),
((SELECT id FROM criteria WHERE code='C1'), 'Kerjasama (bobot 30%)', 30.00),
((SELECT id FROM criteria WHERE code='C1'), 'Etika (bobot 20%)', 20.00),
((SELECT id FROM criteria WHERE code='C1'), 'Teknis (bobot 10%)', 10.00);

-- Sub-criteria C2: Pengalaman
INSERT INTO sub_criteria (criteria_id, label, value) VALUES
((SELECT id FROM criteria WHERE code='C2'), '>3 Tahun', 30.00),
((SELECT id FROM criteria WHERE code='C2'), '3 Tahun', 25.00),
((SELECT id FROM criteria WHERE code='C2'), '2 Tahun', 20.00),
((SELECT id FROM criteria WHERE code='C2'), '1 Tahun', 15.00),
((SELECT id FROM criteria WHERE code='C2'), 'Tidak Ada Pengalaman', 10.00);

-- Sub-criteria C3: Pre-Test
INSERT INTO sub_criteria (criteria_id, label, value) VALUES
((SELECT id FROM criteria WHERE code='C3'), 'Nilai 80-100', 70.00),
((SELECT id FROM criteria WHERE code='C3'), 'Nilai 50-79', 20.00),
((SELECT id FROM criteria WHERE code='C3'), 'Nilai 0-49', 10.00);

-- Sub-criteria C4: Pendidikan
INSERT INTO sub_criteria (criteria_id, label, value) VALUES
((SELECT id FROM criteria WHERE code='C4'), 'D3/S1/S2', 70.00),
((SELECT id FROM criteria WHERE code='C4'), 'SMA/SMK', 20.00),
((SELECT id FROM criteria WHERE code='C4'), 'SMP', 10.00);

-- Sub-criteria C5: Umur
INSERT INTO sub_criteria (criteria_id, label, value) VALUES
((SELECT id FROM criteria WHERE code='C5'), '24-25 Tahun', 40.00),
((SELECT id FROM criteria WHERE code='C5'), '22-23 Tahun', 30.00),
((SELECT id FROM criteria WHERE code='C5'), '20-21 Tahun', 20.00),
((SELECT id FROM criteria WHERE code='C5'), '18-19 Tahun', 10.00);

-- Sample Pre-Test Questions (10 questions)
INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES
('Apa kepanjangan dari HRD?', 'Human Resource Development', 'Human Responsibility Division', 'Human Relation Department', 'Human Recruitment Division', 'A'),
('Dalam bekerja tim, sikap yang paling tepat adalah?', 'Menyelesaikan pekerjaan sendiri tanpa peduli tim', 'Menunggu arahan atasan terus menerus', 'Berkomunikasi aktif dan saling membantu anggota tim', 'Bersaing dengan anggota tim lainnya', 'C'),
('Etika profesi berarti?', 'Aturan berpakaian di kantor', 'Standar perilaku dan nilai moral dalam lingkungan kerja', 'Jam kerja yang harus dipenuhi', 'Gaji yang sesuai jabatan', 'B'),
('Berapakah hasil dari 15% dari 200?', '20', '25', '30', '15', 'C'),
('Apa yang dimaksud dengan SOP?', 'Standard Operating Procedure', 'System of Operations Plan', 'Strategic Organizational Purpose', 'Staff Operation Protocol', 'A'),
('Dalam menghadapi konflik di tempat kerja, langkah terbaik adalah?', 'Mengabaikan masalah hingga selesai sendiri', 'Memihak salah satu pihak yang berselisih', 'Berdiskusi secara terbuka dan mencari solusi bersama', 'Melaporkan langsung ke pihak luar perusahaan', 'C'),
('Dokumen yang berisi ringkasan kualifikasi dan pengalaman kerja seseorang disebut?', 'Surat Lamaran', 'Curriculum Vitae (CV)', 'Portofolio', 'Sertifikat', 'B'),
('Apa arti dari istilah "deadline" dalam pekerjaan?', 'Waktu mulai pekerjaan', 'Batas waktu penyelesaian pekerjaan', 'Waktu istirahat kerja', 'Pertemuan tim', 'B'),
('Manakah yang termasuk keterampilan komunikasi yang baik?', 'Berbicara terus tanpa mendengarkan orang lain', 'Mendengarkan aktif dan menyampaikan pesan dengan jelas', 'Hanya berkomunikasi melalui pesan tertulis', 'Menggunakan bahasa yang sulit dipahami orang lain', 'B'),
('Apa yang sebaiknya dilakukan ketika mendapat tugas yang tidak dipahami?', 'Menunda pekerjaan tersebut', 'Langsung mengerjakan tanpa bertanya', 'Bertanya kepada atasan atau rekan yang lebih berpengalaman', 'Menyerahkan tugas tersebut kepada orang lain', 'C');
