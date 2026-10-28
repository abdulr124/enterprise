<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth.php';
require_role(['admin']);

// --- 1. Handle Date Filtering ---
$date_filter = '1 = 1';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

if (!empty($start_date) && !empty($end_date)) {
    $start_dt = date('Y-m-d 00:00:00', strtotime($start_date));
    $end_dt = date('Y-m-d 23:59:59', strtotime($end_date));
    $date_filter = "sale_date BETWEEN :start_date AND :end_date";
}

// --- 2. Fetch Summary Statistics (KPIs) ---
$summary_sql = "SELECT COUNT(id) AS total_sales_count, SUM(total_amount) AS total_revenue FROM sales WHERE {$date_filter}";
$stmt_summary = $pdo->prepare($summary_sql);

if ($date_filter != '1 = 1') {
    $stmt_summary->bindParam(':start_date', $start_dt);
    $stmt_summary->bindParam(':end_date', $end_dt);
}
$stmt_summary->execute();
$summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

$total_sales_count = $summary['total_sales_count'] ?? 0;
$total_revenue = $summary['total_revenue'] ?? 0;

// --- 3. Fetch Detailed Sales List ---
$details_sql = "SELECT id, customer_name, total_amount, payment_method, sale_date FROM sales WHERE {$date_filter} ORDER BY sale_date DESC";
$stmt_details = $pdo->prepare($details_sql);

if ($date_filter != '1 = 1') {
    $stmt_details->bindParam(':start_date', $start_dt);
    $stmt_details->bindParam(':end_date', $end_dt);
}
$stmt_details->execute();
$sales_list = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

$active = 'reports';
include __DIR__ . '/../header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">
<style>
    .font-mono { font-family: 'IBM Plex Mono', monospace; }
</style>

<div class="flex min-h-screen bg-gray-100">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Financial Intelligence</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['username']) ?>" class="w-10 h-10 rounded-full" alt="User Avatar">
            </div>
        </header>

        <main class="p-4 sm:p-8">
            <div class="max-w-7xl mx-auto">

                <div class="mb-8 flex justify-between items-end">
                    <p class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em]">Analytics // Performance_Audit_Report</p>
                    <button onclick="window.print()" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest hover:text-slate-900 transition-colors">
                        Generate PDF/Print
                    </button>
                </div>

                <div class="bg-white border border-slate-200 p-6 rounded-sm mb-8 shadow-sm">
                    <form method="GET" class="flex flex-wrap items-end gap-6">
                        <div class="flex-1 max-w-xs">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-2">Start Date</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"
                                   class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs font-mono rounded-sm outline-none focus:ring-1 focus:ring-slate-900">
                        </div>
                        <div class="flex-1 max-w-xs">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-2">End Date</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"
                                   class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs font-mono rounded-sm outline-none focus:ring-1 focus:ring-slate-900">
                        </div>
                        <button class="bg-slate-900 text-white px-8 py-2.5 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-slate-800 transition-all">
                            Update Data
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white p-6 border border-slate-200 rounded-sm shadow-sm">
                        <h3 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Total Sales</h3>
                        <p class="text-3xl font-bold text-slate-900 font-mono"><?= $total_sales_count ?></p>
                    </div>
                    <div class="bg-white p-6 border border-slate-200 rounded-sm shadow-sm">
                        <h3 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Gross Revenue</h3>
                        <p class="text-3xl font-bold text-slate-900 font-mono">&#8358;<?= number_format($total_revenue, 2) ?></p>
                    </div>
                    <div class="bg-slate-900 p-6 rounded-sm shadow-lg text-white">
                        <h3 class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-2">Mean Transaction Value</h3>
                        <?php $average_sale = ($total_sales_count > 0) ? ($total_revenue / $total_sales_count) : 0; ?>
                        <p class="text-3xl font-bold font-mono">&#8358;<?= number_format($average_sale, 2) ?></p>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Transaction Ledger</h3>
                    </div>
                    <table class="min-w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Invoice ID</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Date</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Customer</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-right">Amount</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-center">Payment</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-right">Link</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (empty($sales_list)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-mono text-[10px] uppercase italic">No sales found in the selected date range.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($sales_list as $sale): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-mono text-[11px] text-slate-400">#<?= $sale['id'] ?></td>
                                    <td class="px-6 py-4 text-[11px] font-mono text-slate-500"><?= date('Y-m-d H:i', strtotime($sale['sale_date'])) ?></td>
                                    <td class="px-6 py-4 font-semibold text-slate-800 uppercase text-[11px] tracking-tight"><?= htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer') ?></td>
                                    <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">&#8358;<?= number_format($sale['total_amount'], 2) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[9px] font-bold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full border border-slate-200 uppercase">
                                            <?= htmlspecialchars($sale['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="../sales/invoice.php?id=<?= $sale['id'] ?>" target="_blank" class="text-[10px] font-bold text-blue-600 hover:underline uppercase">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>