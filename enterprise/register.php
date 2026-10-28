<?php
require_once __DIR__ . '/config/db.php';
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'staff';

    // --- BACKEND VALIDATION ---
    if (empty($username)) $errors[] = "Username is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($pass) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($pass !== $confirm) $errors[] = "Passwords do not match.";
    if (!in_array($role, ['admin', 'staff'], true)) $errors[] = "Invalid role selected.";

    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or Email already exists in the system.";
        } else {
            // Hashing the password for security
            $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword, $role])) {
                $success = true;
            } else {
                $errors[] = "System Error: Could not create account.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Access // Create Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'IBM Plex Mono', monospace; }
    </style>
</head>
<body class="bg-[#0f172a] min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h1 class="text-white text-2xl font-bold tracking-tighter uppercase">Inventory System</h1>
            <p class="text-[10px] font-mono text-slate-500 uppercase tracking-[0.3em] mt-2">Identity_Provisioning_Module</p>
        </div>

        <div class="bg-white rounded-sm shadow-2xl overflow-hidden">
            <div class="p-8">
                <h2 class="text-lg font-bold text-slate-900 mb-6">Create Operator Account</h2>

                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold uppercase tracking-widest">
                        Account Created. <a href="login.php" class="underline">Proceed to Login &rarr;</a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 space-y-2">
                        <?php foreach($errors as $err): ?>
                            <div class="p-3 bg-red-50 border-l-2 border-red-500 text-red-700 text-[10px] font-mono uppercase">
                                ERROR: <?= htmlspecialchars($err) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Username</label>
                        <input type="text" name="username" required
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Email Address</label>
                        <input type="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Account Type</label>
                        <select name="role" required
                                class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                            <option value="staff" <?= (($_POST['role'] ?? '') === 'staff') ? 'selected' : '' ?>>Cashier (Staff)</option>
                            <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Access Password</label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Confirm Identity</label>
                        <input type="password" name="confirm_password" required minlength="6"
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-900 transition-all">
                    </div>

                    <button type="submit" 
                            class="w-full bg-slate-900 text-white font-bold py-4 text-[10px] uppercase tracking-[0.2em] rounded-sm hover:bg-slate-800 transition-all shadow-lg">
                        Register Account
                    </button>
                </form>
            </div>
            
            <div class="bg-slate-50 p-4 border-t border-slate-100 text-center">
                <p class="text-[10px] text-slate-400 uppercase tracking-widest">
                    Existing User? <a href="login.php" class="text-slate-900 font-bold hover:underline">Sign In</a>
                </p>
            </div>
        </div>

        <div class="mt-8 text-center text-slate-600 text-[9px] font-mono uppercase tracking-widest">
            Authorized Personnel Only // Security Level 01
        </div>
    </div>

</body>
</html>