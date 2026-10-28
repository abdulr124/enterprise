<?php
// includes/auth.php
// Include this at the very top of any protected page, BEFORE any HTML output.

session_start();

/**
 * Blocks access if no one is logged in.
 * Redirects to login.php.
 */
function require_login() {
    if (empty($_SESSION['user'])) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Blocks access unless the logged-in user's role is in $allowed_roles.
 * Call require_login() first (or this calls it for you).
 *
 * Usage:
 *   require_role(['admin']);            // admin only
 *   require_role(['admin', 'staff']);   // both roles allowed
 */
function require_role(array $allowed_roles) {
    require_login();

    $role = $_SESSION['user']['role'] ?? null;

    if (!$role || !in_array($role, $allowed_roles, true)) {
        http_response_code(403);
        echo '<div style="font-family:sans-serif;text-align:center;margin-top:80px;">
                <h1>403 - Access Denied</h1>
                <p>You do not have permission to view this page.</p>
                <a href="/dashboard.php">Back to Dashboard</a>
              </div>';
        exit;
    }
}

/**
 * Convenience helper - returns the logged-in user's id, or null.
 */
function current_user_id() {
    return $_SESSION['user']['id'] ?? null;
}