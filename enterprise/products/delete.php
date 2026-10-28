<?php
require_once __DIR__ . '/../config/db.php';
// We don't include header.php here because we are redirecting immediately.
// We only need the session check if header.php isn't doing it.
session_start();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // Start a transaction so both deletes happen, or neither happens
        $pdo->beginTransaction();

        // 1. Remove the product from any previous sales records first
        // This clears the "Foreign Key" restriction
        $stmt_items = $pdo->prepare("DELETE FROM sale_items WHERE product_id = ?");
        $stmt_items->execute([$id]);

        // 2. Now it is safe to delete the actual product
        $stmt_product = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt_product->execute([$id]);

        // If we got here, commit the changes to the database
        $pdo->commit();
        
        header("Location: index.php?status=deleted");
        exit;

    } catch (Exception $e) {
        // If anything goes wrong, cancel the whole operation
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Redirect with an error flag
        header("Location: index.php?status=error&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}