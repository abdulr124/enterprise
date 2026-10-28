<?php
require_once __DIR__ . "/config/db.php";
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Supports both new hashed passwords (from register.php) and
    // legacy plain-text passwords (your existing admin/abdul accounts).
    // Legacy accounts are auto-upgraded to a hash the moment they log in.
    $passwordOk = false;

    if ($user) {
        $storedPassword = $user['password'];
        $looksHashed = password_get_info($storedPassword)['algo'] !== null;

        if ($looksHashed) {
            $passwordOk = password_verify($password, $storedPassword);
        } else {
            // Legacy plain-text account
            $passwordOk = ($password === $storedPassword);
            if ($passwordOk) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->execute([$newHash, $user['id']]);
            }
        }
    }

    if ($passwordOk) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        // Route by role: admin -> full dashboard, staff -> cashier dashboard
        if ($user['role'] === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: cashier_dashboard.php");
        }
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

require_once __DIR__ . '/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<style>
    .font-technical { font-family: 'IBM Plex Mono', monospace; }
    body { font-family: 'Inter', sans-serif; }
</style>

<main class="relative min-h-[calc(100vh-64px)] flex items-center justify-center bg-white overflow-hidden px-6">

    <div class="absolute inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f1f5f9_1px,transparent_1px),linear-gradient(to_bottom,#f1f5f9_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] [mask-image:radial-gradient(white,transparent)] opacity-40"></div>
    </div>

    <div class="relative z-10 w-full max-w-sm">

        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-slate-900 text-white rounded-sm mb-6 shadow-2xl shadow-slate-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-950">System Entry</h2>
            <p class="font-technical text-[10px] uppercase tracking-[0.25em] text-slate-400 mt-2">Node-01 Access Protocol</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-sm shadow-sm">

            <?php if($error): ?>
                <div class="flex items-center gap-3 bg-red-50 border-l-2 border-red-500 p-3 mb-6">
                    <p class="font-technical text-[11px] text-red-700 leading-none lowercase italic"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <div>
                    <label class="font-technical text-[10px] font-bold uppercase tracking-widest text-slate-500 block mb-2">Username</label>
                    <input name="username" type="text"
                        class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-sm text-sm focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition-all"
                        required>
                </div>

                <div>
                    <label class="font-technical text-[10px] font-bold uppercase tracking-widest text-slate-500 block mb-2">Password</label>
                    <input name="password" type="password"
                        class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-sm text-sm focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition-all"
                        required>
                </div>

                <button class="w-full bg-slate-950 text-white font-technical text-[11px] font-bold uppercase tracking-[0.2em] py-4 rounded-sm hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
                    Authorize Access
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="register.php" class="font-technical text-[9px] uppercase tracking-[0.2em] text-slate-400 hover:text-slate-900 transition-all border-b border-transparent hover:border-slate-900">
                Register New Institution Account
            </a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>