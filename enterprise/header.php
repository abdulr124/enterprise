<?php
// Safety: prevent the infinite loop that caused your 120s timeout
if (basename($_SERVER['PHP_SELF']) == 'header.php') {
    die('Direct access not permitted');
}
// Define your page links here for easy management
$links = [
    'dashboard' => 'dashboard.php',
    'inventory' => 'inventory.php',
    'sales'     => 'sales.php',
    'reports'   => 'reports.php'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS | Enterprise Resource Planning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:italic,wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-white text-slate-900">
    <nav class="sticky top-0 z-50 w-full border-b border-slate-100 bg-white/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="index.php" class="text-xl font-bold tracking-tighter text-slate-900">
                Enterprise Resource Planning<span class="text-blue-600">.</span>
            </a>

            <div class="hidden md:flex items-center gap-8 text-xs font-semibold uppercase tracking-widest text-slate-500">
                <?php if (!empty($_SESSION['user'])): ?>
                    <span class="normal-case tracking-normal text-slate-700 font-semibold">
                        <?= htmlspecialchars($_SESSION['user']['username']) ?>
                        <span class="text-slate-400">(<?= htmlspecialchars($_SESSION['user']['role'] ?? '') ?>)</span>
                    </span>
                    <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-sm hover:bg-red-700 transition">Logout</a>
                <?php else: ?>
                    <a href="index.php" class="hover:text-blue-600 transition">Portal Access</a>
                    <a href="register.php" class="px-4 py-2 bg-slate-900 text-white rounded-sm hover:bg-slate-800 transition">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>