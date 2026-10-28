<?php
// config/db.php

// --- 1. Database Configuration ---
$host = 'localhost';
$db   = 'inventory_db';
$user = 'root';
$pass = ''; // set your MySQL password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE             => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('DB Connection failed: ' . $e->getMessage());
}

// --- 2. Dynamic Store Configuration ---
// Fetch settings from the database and define them as constants
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings_data = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Define constants using the database values
if (!empty($settings_data)) {
    // We use the null coalescing operator (??) as a safety fallback
    define('STORE_NAME', $settings_data['store_name'] ?? 'Default Store Name');
    define('STORE_ADDRESS', $settings_data['store_address'] ?? 'Default Address');
    define('STORE_PHONE', $settings_data['store_phone'] ?? 'Default Phone');
} else {
    // Fallback if settings table is empty
    define('STORE_NAME', 'Settings Missing');
    define('STORE_ADDRESS', 'N/A');
    define('STORE_PHONE', 'N/A');
}