<?php 
require_once __DIR__ . '/../config/db.php'; 
include_once __DIR__ . '/../header.php';

// --- 1. DATA FOR TABLE (Recent Items) ---
$query = "SELECT si.*, p.name as product_name FROM sale_items si 
          JOIN products p ON si.product_id = p.id 
          ORDER BY si.id DESC LIMIT 15";
$sale_items = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// --- 2. DATA FOR BAR CHART (Top Products) ---
$barQuery = "SELECT p.name, SUM(si.quantity) as total_sold 
             FROM sale_items si 
             JOIN products p ON si.product_id = p.id 
             GROUP BY si.product_id ORDER BY total_sold DESC LIMIT 8";
$barData = $pdo->query($barQuery)->fetchAll(PDO::FETCH_ASSOC);

// --- 3. DATA FOR HISTOGRAM (Price Range Frequency) ---
$histQuery = "SELECT 
                CASE 
                    WHEN (quantity * unit_price) <= 5000 THEN '0-5k'
                    WHEN (quantity * unit_price) <= 10000 THEN '5k-10k'
                    WHEN (quantity * unit_price) <= 20000 THEN '10k-20k'
                    WHEN (quantity * unit_price) <= 50000 THEN '20k-50k'
                    ELSE '50k+' 
                END AS price_range,
                COUNT(*) as frequency
              FROM sale_items
              GROUP BY price_range";
$histData = $pdo->query($histQuery)->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex min-h-screen bg-[#f8fafc]">
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Sales Analytics Dashboard</h1>
                <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Inventory // Performance_Audit</p>
            </div>
            <a href="../dashboard.php" class="text-xs font-bold text-blue-600 hover:underline uppercase tracking-tighter">&larr; Dashboard</a>
        </header>

        <main class="p-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
                    <h2 class="text-xs font-bold text-slate-500 uppercase mb-4 tracking-widest">Top Products by Volume</h2>
                    <div class="h-64"><canvas id="barChart"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
                    <h2 class="text-xs font-bold text-slate-500 uppercase mb-4 tracking-widest">Transaction Value Distribution</h2>
                    <div class="h-64"><canvas id="histChart"></canvas></div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Recent Itemized Ledger</h2>
                    <span class="text-[10px] font-mono text-slate-400">Showing Last 15 Entries</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 text-xs font-bold text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 border-b">Product</th>
                                <th class="px-6 py-3 border-b text-center">Qty Sold</th>
                                <th class="px-6 py-3 border-b text-right">Total (₦)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($sale_items as $item): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800"><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td class="px-6 py-4 text-sm text-center font-mono"><?= $item['quantity'] ?></td>
                                    <td class="px-6 py-4 text-sm font-bold text-right text-slate-900">₦<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Global configurations for Chart.js
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
    };

    // 1. BAR CHART: Logic to handle empty data safely
    const barLabels = <?= json_encode(array_column($barData, 'name')) ?>;
    const barValues = <?= json_encode(array_column($barData, 'total_sold')) ?>;

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: barLabels,
            datasets: [{
                label: 'Units Sold',
                data: barValues,
                backgroundColor: '#3b82f6',
                borderRadius: 4
            }]
        },
        options: commonOptions
    });

    // 2. HISTOGRAM: Logic to handle price distribution
    const histLabels = <?= json_encode(array_column($histData, 'price_range')) ?>;
    const histValues = <?= json_encode(array_column($histData, 'frequency')) ?>;

    new Chart(document.getElementById('histChart'), {
        type: 'bar',
        data: {
            labels: histLabels,
            datasets: [{
                label: 'Frequency',
                data: histValues,
                backgroundColor: '#10b981',
                barPercentage: 1.0,
                categoryPercentage: 0.9
            }]
        },
        options: commonOptions
    });
</script>

<?php include_once __DIR__ . '/../footer.php'; ?>