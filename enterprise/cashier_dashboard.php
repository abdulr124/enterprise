<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';
require_role(['staff', 'admin']); // staff use this; admin can view too for testing

$user_id = current_user_id();

// --- This cashier's sales today ---
$today_sales_count = $pdo->prepare("
    SELECT COUNT(*) FROM sales WHERE user_id = ? AND DATE(sale_date) = CURDATE()
");
$today_sales_count->execute([$user_id]);
$today_sales_count = $today_sales_count->fetchColumn();

$today_sales_total = $pdo->prepare("
    SELECT SUM(total_amount) FROM sales WHERE user_id = ? AND DATE(sale_date) = CURDATE()
");
$today_sales_total->execute([$user_id]);
$today_sales_total = $today_sales_total->fetchColumn() ?: 0;

$today_collected = $pdo->prepare("
    SELECT SUM(paid_amount) FROM sales WHERE user_id = ? AND DATE(sale_date) = CURDATE()
");
$today_collected->execute([$user_id]);
$today_collected = $today_collected->fetchColumn() ?: 0;

// --- This cashier's recent transactions (any date, last 10) ---
$recent = $pdo->prepare("
    SELECT id, total_amount, paid_amount, payment_method, customer_name, sale_date
    FROM sales
    WHERE user_id = ?
    ORDER BY sale_date DESC
    LIMIT 10
");
$recent->execute([$user_id]);
$recent_sales = $recent->fetchAll(PDO::FETCH_ASSOC);

$active = 'dashboard';
include __DIR__ . '/header.php';
?>

<div class="flex min-h-screen bg-gray-100">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Cashier Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['username']) ?>" class="w-10 h-10 rounded-full" alt="User Avatar">
            </div>
        </header>

        <main class="p-4 sm:p-6 w-full">

            <a href="sales/pos.php" class="block mb-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow p-6 text-center transition">
                <span class="text-lg font-bold uppercase tracking-widest">Open POS Terminal</span>
                <p class="text-xs text-blue-100 mt-1">Start a new sale</p>
            </a>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

                <div class="bg-white p-4 rounded-xl shadow border-l-4 border-blue-500">
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">My Sales Today</h2>
                    <p class="text-2xl font-bold text-gray-800"><?= (int)$today_sales_count ?></p>
                </div>

                <div class="bg-white p-4 rounded-xl shadow border-l-4 border-slate-700">
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Total Sold Today</h2>
                    <p class="text-2xl font-bold text-gray-800">₦<?= number_format($today_sales_total, 2) ?></p>
                </div>

                <div class="bg-white p-4 rounded-xl shadow border-l-4 border-emerald-500">
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Collected Today</h2>
                    <p class="text-2xl font-bold text-emerald-600">₦<?= number_format($today_collected, 2) ?></p>
                </div>

            </div>

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">My Recent Transactions</h2>
                <table class="min-w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Date</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Customer</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-right">Amount</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-center">Payment</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-center">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_sales)): ?>
                            <tr>
                                <td colspan="5" class="p-6 border-b text-center text-gray-400 text-sm italic font-mono">No transactions yet — head to the POS to make your first sale.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 border-b text-sm font-mono"><?= date('Y-m-d H:i', strtotime($sale['sale_date'])) ?></td>
                                    <td class="p-3 border-b text-sm"><?= htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer') ?></td>
                                    <td class="p-3 border-b text-sm font-bold text-gray-800 text-right">₦<?= number_format($sale['total_amount'], 2) ?></td>
                                    <td class="p-3 border-b text-sm text-center">
                                        <span class="px-2 py-1 bg-gray-100 rounded text-[10px] font-bold uppercase text-gray-600"><?= htmlspecialchars($sale['payment_method']) ?></span>
                                    </td>
                                    <td class="p-3 border-b text-sm text-center">
                                        <a href="sales/invoice.php?id=<?= $sale['id'] ?>" target="_blank" class="text-blue-600 hover:underline text-xs font-bold uppercase">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>