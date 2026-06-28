-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for spk_bingxue
CREATE DATABASE IF NOT EXISTS `spk_bingxue` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `spk_bingxue`;

-- Dumping structure for table spk_bingxue.applicants
CREATE TABLE IF NOT EXISTS `applicants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_communication` decimal(5,2) DEFAULT 0.00,
  `skill_cooperation` decimal(5,2) DEFAULT 0.00,
  `skill_ethics` decimal(5,2) DEFAULT 0.00,
  `skill_technical` decimal(5,2) DEFAULT 0.00,
  `c1_score` decimal(5,2) DEFAULT 0.00,
  `experience_years` varchar(50) DEFAULT NULL,
  `c2_score` decimal(5,2) DEFAULT 0.00,
  `pretest_score` decimal(5,2) DEFAULT 0.00,
  `c3_score` decimal(5,2) DEFAULT 0.00,
  `pretest_taken` tinyint(1) DEFAULT 0,
  `education` varchar(50) DEFAULT NULL,
  `c4_score` decimal(5,2) DEFAULT 0.00,
  `age` int(11) DEFAULT 0,
  `c5_score` decimal(5,2) DEFAULT 0.00,
  `saw_value` decimal(10,6) DEFAULT 0.000000,
  `rank` int(11) DEFAULT 0,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `personal_data_filled` tinyint(1) DEFAULT 0,
  `self_assessment_filled` tinyint(1) DEFAULT 0,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `skill_operating_equipment` decimal(5,2) DEFAULT 0.00,
  `skill_sop` decimal(5,2) DEFAULT 0.00,
  `skill_speed_accuracy` decimal(5,2) DEFAULT 0.00,
  `skill_customer_service` decimal(5,2) DEFAULT 0.00,
  `skill_teamwork` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `applicants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table spk_bingxue.applicants: ~5 rows (approximately)
REPLACE INTO `applicants` (`id`, `user_id`, `skill_communication`, `skill_cooperation`, `skill_ethics`, `skill_technical`, `c1_score`, `experience_years`, `c2_score`, `pretest_score`, `c3_score`, `pretest_taken`, `education`, `c4_score`, `age`, `c5_score`, `saw_value`, `rank`, `status`, `personal_data_filled`, `self_assessment_filled`, `submitted_at`, `updated_at`, `skill_operating_equipment`, `skill_sop`, `skill_speed_accuracy`, `skill_customer_service`, `skill_teamwork`) VALUES
	(1, 2, 4.00, 5.00, 5.00, 4.00, 40.00, '3 Tahun', 25.00, 90.00, 70.00, 1, 'SMA/SMK', 20.00, 25, 40.00, 0.892900, 1, 'pending', 1, 1, '2026-06-26 19:18:39', '2026-06-28 18:20:17', 4.00, 5.00, 5.00, 4.00, 5.00),
	(2, 3, 5.00, 3.00, 4.00, 3.00, 20.00, '1 Tahun', 15.00, 80.00, 70.00, 1, 'SMA/SMK', 20.00, 19, 10.00, 0.567900, 4, 'pending', 1, 1, '2026-06-26 19:28:52', '2026-06-28 18:20:02', 5.00, 3.00, 4.00, 3.00, 3.00),
	(3, 4, 4.00, 4.00, 3.00, 4.00, 20.00, '3 Tahun', 25.00, 70.00, 20.00, 1, 'SMA/SMK', 20.00, 23, 30.00, 0.575000, 3, 'pending', 1, 1, '2026-06-26 19:32:15', '2026-06-28 18:20:02', 4.00, 4.00, 3.00, 4.00, 3.00),
	(4, 5, 4.00, 3.00, 4.00, 2.00, 10.00, 'Tidak Ada Pengalaman', 10.00, 80.00, 70.00, 1, 'D3/S1/S2', 70.00, 24, 40.00, 0.625000, 2, 'pending', 1, 1, '2026-06-26 19:34:38', '2026-06-28 18:20:02', 4.00, 3.00, 4.00, 2.00, 3.00),
	(5, 6, 4.00, 5.00, 4.00, 3.00, 30.00, '2 Tahun', 20.00, 40.00, 10.00, 1, 'SMP', 10.00, 21, 20.00, 0.525000, 5, 'pending', 1, 1, '2026-06-26 19:36:25', '2026-06-28 18:20:02', 4.00, 5.00, 4.00, 3.00, 4.00);

-- Dumping structure for table spk_bingxue.criteria
CREATE TABLE IF NOT EXISTS `criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `type` enum('benefit','cost') NOT NULL DEFAULT 'benefit',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table spk_bingxue.criteria: ~5 rows (approximately)
REPLACE INTO `criteria` (`id`, `code`, `name`, `weight`, `type`, `description`) VALUES
	(1, 'C1', 'Skill', 30.00, 'benefit', 'Penilaian keterampilan: Komunikasi, Kerjasama, Etika, Teknis'),
	(2, 'C2', 'Pengalaman Kerja', 25.00, 'benefit', 'Lama pengalaman kerja pelamar'),
	(3, 'C3', 'Nilai Pre-Test', 20.00, 'benefit', 'Hasil tes pengetahuan umum dan potensi'),
	(4, 'C4', 'Pendidikan', 15.00, 'benefit', 'Tingkat pendidikan terakhir pelamar'),
	(5, 'C5', 'Umur', 10.00, 'benefit', 'Usia pelamar dalam tahun');

-- Dumping structure for table spk_bingxue.employees
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `position` varchar(100) DEFAULT 'Staff',
  `join_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','resigned','terminated') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `applicant_id` (`applicant_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table spk_bingxue.employees: ~0 rows (approximately)

-- Dumping structure for table spk_bingxue.questions
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table spk_bingxue.questions: ~10 rows (approximately)
REPLACE INTO `questions` (`id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `created_at`) VALUES
	(1, 'Apa kepanjangan dari HRD?', 'Human Resource Development', 'Human Responsibility Division', 'Human Relation Department', 'Human Recruitment Division', 'A', '2026-06-26 19:11:36'),
	(2, 'Dalam bekerja tim, sikap yang paling tepat adalah?', 'Menyelesaikan pekerjaan sendiri tanpa peduli tim', 'Menunggu arahan atasan terus menerus', 'Berkomunikasi aktif dan saling membantu anggota tim', 'Bersaing dengan anggota tim lainnya', 'C', '2026-06-26 19:11:36'),
	(3, 'Etika profesi berarti?', 'Aturan berpakaian di kantor', 'Standar perilaku dan nilai moral dalam lingkungan kerja', 'Jam kerja yang harus dipenuhi', 'Gaji yang sesuai jabatan', 'B', '2026-06-26 19:11:36'),
	(4, 'Berapakah hasil dari 15% dari 200?', '20', '25', '30', '15', 'C', '2026-06-26 19:11:36'),
	(5, 'Apa yang dimaksud dengan SOP?', 'Standard Operating Procedure', 'System of Operations Plan', 'Strategic Organizational Purpose', 'Staff Operation Protocol', 'A', '2026-06-26 19:11:36'),
	(6, 'Dalam menghadapi konflik di tempat kerja, langkah terbaik adalah?', 'Mengabaikan masalah hingga selesai sendiri', 'Memihak salah satu pihak yang berselisih', 'Berdiskusi secara terbuka dan mencari solusi bersama', 'Melaporkan langsung ke pihak luar perusahaan', 'C', '2026-06-26 19:11:36'),
	(7, 'Dokumen yang berisi ringkasan kualifikasi dan pengalaman kerja seseorang disebut?', 'Surat Lamaran', 'Curriculum Vitae (CV)', 'Portofolio', 'Sertifikat', 'B', '2026-06-26 19:11:36'),
	(8, 'Apa arti dari istilah "deadline" dalam pekerjaan?', 'Waktu mulai pekerjaan', 'Batas waktu penyelesaian pekerjaan', 'Waktu istirahat kerja', 'Pertemuan tim', 'B', '2026-06-26 19:11:36'),
	(9, 'Manakah yang termasuk keterampilan komunikasi yang baik?', 'Berbicara terus tanpa mendengarkan orang lain', 'Mendengarkan aktif dan menyampaikan pesan dengan jelas', 'Hanya berkomunikasi melalui pesan tertulis', 'Menggunakan bahasa yang sulit dipahami orang lain', 'B', '2026-06-26 19:11:36'),
	(10, 'Apa yang sebaiknya dilakukan ketika mendapat tugas yang tidak dipahami?', 'Menunda pekerjaan tersebut', 'Langsung mengerjakan tanpa bertanya', 'Bertanya kepada atasan atau rekan yang lebih berpengalaman', 'Menyerahkan tugas tersebut kepada orang lain', 'C', '2026-06-26 19:11:36');

-- Dumping structure for table spk_bingxue.sub_criteria
CREATE TABLE IF NOT EXISTS `sub_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `criteria_id` int(11) NOT NULL,
  `label` varchar(150) NOT NULL,
  `value` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `criteria_id` (`criteria_id`),
  CONSTRAINT `sub_criteria_ibfk_1` FOREIGN KEY (`criteria_id`) REFERENCES `criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table spk_bingxue.sub_criteria: ~19 rows (approximately)
REPLACE INTO `sub_criteria` (`id`, `criteria_id`, `label`, `value`) VALUES
	(1, 1, '86-100 Sangat Baik', 40.00),
	(2, 1, '76-85 Baik', 30.00),
	(3, 1, '66-75 Cukup', 20.00),
	(4, 1, '<65 Kurang', 10.00),
	(5, 2, '>3 Tahun', 30.00),
	(6, 2, '3 Tahun', 25.00),
	(7, 2, '2 Tahun', 20.00),
	(8, 2, '1 Tahun', 15.00),
	(9, 2, 'Tidak Ada Pengalaman', 10.00),
	(10, 3, 'Nilai 80-100', 70.00),
	(11, 3, 'Nilai 50-79', 20.00),
	(12, 3, 'Nilai 0-49', 10.00),
	(13, 4, 'D3/S1/S2', 70.00),
	(14, 4, 'SMA/SMK', 20.00),
	(15, 4, 'SMP', 10.00),
	(16, 5, '24-25 Tahun', 40.00),
	(17, 5, '22-23 Tahun', 30.00),
	(18, 5, '20-21 Tahun', 20.00),
	(19, 5, '18-19 Tahun', 10.00);

-- Dumping structure for table spk_bingxue.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table spk_bingxue.users: ~6 rows (approximately)
REPLACE INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
	(1, 'Administrator', 'admin@bingxue.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-26 19:11:36'),
	(2, 'Devia', 'devia@gmail.com', '$2y$10$4/bN69ygcqUfF3eSta/fTehBTX/h04oqGycmFW4ZLzAUvAEoKtmlS', 'user', '2026-06-26 19:17:06'),
	(3, 'Ridho', 'ridho@gmail.com', '$2y$10$/J/38j/U05F5RR5/ORjTQusQ7OkuwaCIclepnkNfokyz6FY9bZNI2', 'user', '2026-06-26 19:20:28'),
	(4, 'Kiki', 'kiki@gmail.com', '$2y$10$lkp6xFyRU.STGaEStmAEievMzIbvd.Oa4I2Q.JAr8p4bDF2586tHC', 'user', '2026-06-26 19:30:11'),
	(5, 'Rahma', 'rahma@gmail.com', '$2y$10$fWCsl8jynE9hyJT5mNCJy.vfb2qmzdh9Z38/Qsrw2qJrAq6ImRNjK', 'user', '2026-06-26 19:32:49'),
	(6, 'Devi', 'devi@gmail.com', '$2y$10$Z5b4t3CS51R1ieqoJ3uEZ.QwG1scHihDLMUQTdk2EbWuor/NsL9I6', 'user', '2026-06-26 19:35:05');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
