<?php

/**
 * Start session if not already started
 */
function start_secure_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Sanitize user input
 */
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific page
 */
function redirect($page)
{
    header("Location: " . $page);
    exit();
}

/**
 * Check if the user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if the user is an admin
 */
function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if the user is an employee
 */
function is_employee()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employee';
}

/**
 * Centralized permission check
 */
function can_do($action)
{
    $role = $_SESSION['role'] ?? '';

    if ($role === 'admin')
        return true;

    $permissions = [
        'employee' => [
            'student_add',
            'student_edit',
            'attendance_mark',
            'fee_view',
            'fee_collect'
        ]
    ];

    return in_array($action, $permissions[$role] ?? []);
}

/**
 * Require login to access a page
 */
function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/**
 * Get unread notifications count
 */
function get_unread_notifications_count($pdo)
{
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0");
    return $stmt->fetchColumn();
}

/**
 * Get latest notifications
 */
function get_latest_notifications($pdo, $limit = 5)
{
    $stmt = $pdo->prepare("SELECT * FROM notifications ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Require admin role to access a page
 */
function require_admin()
{
    require_login();
    if (!is_admin()) {
        // You can redirect to a "Permission Denied" page or home
        redirect('index.php?error=unauthorized');
    }
}

/**
 * Get college settings
 */
function get_settings($pdo)
{
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    return $stmt->fetch();
}

/**
 * Format currency
 */
function format_currency($amount)
{
    return '$' . number_format($amount, 2);
}

/**
 * Generate a random student/teacher ID
 */
function generate_id($prefix = 'STU')
{
    return $prefix . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Generate a relative time string (e.g., "2 hours ago")
 */
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>