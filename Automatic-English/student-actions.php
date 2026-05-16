<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$action = $_GET['action'] ?? '';
$id = (int) ($_GET['id'] ?? 0);

if ($action === 'delete') {
    // Only Admin can delete
    if (!is_admin()) {
        redirect('students.php?error=unauthorized');
    }

    if ($id > 0) {
        try {
            // Get student info for photo deletion
            $stmt = $pdo->prepare("SELECT photo FROM students WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $student = $stmt->fetch();

            if ($student && $student->photo) {
                $photo_path = 'public/uploads/students/' . $student->photo;
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            }

            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
            $stmt->execute(['id' => $id]);

            redirect('students.php?success=deleted');
        } catch (PDOException $e) {
            redirect('students.php?error=delete_failed');
        }
    }
}

redirect('students.php');
