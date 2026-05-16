<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_secure_session();
require_admin();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Prevent deleting yourself
    if ($id == $_SESSION['user_id']) {
        header("Location: users.php?error=self_delete");
        exit();
    }

    if ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            header("Location: users.php?success=deleted");
        } else {
            header("Location: users.php?error=failed");
        }
        exit();
    }
}

header("Location: users.php");
exit();
