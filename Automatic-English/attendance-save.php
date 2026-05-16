<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $date = $_POST['date'];

    foreach ($_POST['attendance'] as $student_id => $status) {
        // Check if already exists for this date
        $check = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
        $check->execute([$student_id, $date]);

        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND attendance_date = ?");
            $stmt->execute([$status, $student_id, $date]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO attendance (student_id, attendance_date, status) VALUES (?, ?, ?)");
            $stmt->execute([$student_id, $date, $status]);
        }
    }

    header("Location: attendance.php?date=" . $date . "&success=1");
    exit();
}
?>