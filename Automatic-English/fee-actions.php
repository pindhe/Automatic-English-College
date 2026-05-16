<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$action = $_GET['action'] ?? '';
$id = (int) ($_GET['id'] ?? 0);

if ($action === 'delete') {
    if ($id > 0) {
        try {
            // Get fee info for notification message
            $stmt = $pdo->prepare("SELECT f.*, s.full_name FROM fees f JOIN students s ON f.student_id = s.id WHERE f.id = :id");
            $stmt->execute(['id' => $id]);
            $fee = $stmt->fetch();

            if ($fee) {
                // Delete the record
                $stmt = $pdo->prepare("DELETE FROM fees WHERE id = :id");
                $stmt->execute(['id' => $id]);

                // If an employee deleted the record, notify admin
                if (is_employee()) {
                    $employee_name = $_SESSION['full_name'];
                    $amount = format_currency($fee->amount);
                    $message = "Employee <strong>{$employee_name}</strong> deleted a fee record of <strong>{$amount}</strong> for student <strong>{$fee->full_name}</strong> ({$fee->month}).";

                    $stmt = $pdo->prepare("INSERT INTO notifications (type, message) VALUES ('fee_deletion', :message)");
                    $stmt->execute(['message' => $message]);
                }

                redirect('fees.php?success=deleted');
            }
        } catch (PDOException $e) {
            redirect('fees.php?error=delete_failed');
        }
    }
}

redirect('fees.php');
