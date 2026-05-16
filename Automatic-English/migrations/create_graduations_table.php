<?php
/**
 * Migration: Create graduations table
 * Run this file once: http://localhost/Automatic-English-College/migrations/create_graduations_table.php
 */
require_once '../config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `graduations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `certificate_type` varchar(100) NOT NULL,
  `graduation_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_grad_student` (`student_id`),
  CONSTRAINT `fk_grad_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $pdo->exec($sql);
    echo '<div style="font-family:sans-serif;padding:30px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;color:#166534;">
        <h2>✅ Migration Successful</h2>
        <p>The <strong>graduations</strong> table has been created (or already exists). You can now use the Graduation page.</p>
        <a href="../graduation.php" style="display:inline-block;margin-top:16px;background:#16a34a;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;">Go to Graduation Page</a>
    </div>';
} catch (PDOException $e) {
    echo '<div style="font-family:sans-serif;padding:30px;background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;color:#9f1239;">
        <h2>❌ Migration Failed</h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
    </div>';
}
?>