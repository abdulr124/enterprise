<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';
require_role(['admin']);

// --- 1. Fetch Live Dashboard Data ---

// a. Total Products (Catalog)
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// b. Available Stock (Items with more than 0 quantity)
$available_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity > 0")->fetchColumn();

// c. Low Stock Logic - uses each product's own reorder_level
$low_stock_count = $pdo->query("
    SELECT COUNT(*) FROM products 
    WHERE quantity <= reorder_level AND quantity > 0
")->fetchColumn();

// d. Out of Stock (Items at 0)
$out_of_stock_count = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= 0")->fetchColumn();

// e. Revenue vs. Amount Actually Collected
$total_revenue   = $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn() ?: 0;
$total_collected = $pdo->query("SELECT SUM(paid_amount) FROM sales")->fetchColumn() ?: 0;
$outstanding      = $total_revenue - $total_collected;

// f. Total Users
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// g. Total Suppliers
$total_suppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();

// h. Total Invoices/Records
$total_reports = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();

// --- 2. Fetch Recent Sales (Limit 5) ---
$recent_sales = $pdo->query("
    SELECT 
        s.sale_date,
        s.total_amount,
        s.payment_method 
    FROM sales s
    ORDER BY s.sale_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// --- 3. Top Products by Sales Value ---
$top_products = $pdo->query("
    SELECT p.name, SUM(si.subtotal) AS total_value
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY p.id
    ORDER BY total_value DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$top_products_labels = array_column($top_products, 'name');
$top_products_values = array_column($top_products, 'total_value');

// --- 4. Low stock product list (for the alert banner detail) ---
$low_stock_items = $pdo->query("
    SELECT name, quantity, reorder_level 
    FROM products 
    WHERE quantity <= reorder_level AND quantity > 0
    ORDER BY quantity ASC
")->fetchAll(PDO::FETCH_ASSOC);

// --- 5. Recent Purchases (restocks) ---
$recent_purchases = $pdo->query("
    SELECT pu.received_at, pu.quantity, pu.total_cost, p.name AS product_name, s.name AS supplier_name
    FROM purchases pu
    JOIN products p ON pu.product_id = p.id
    JOIN suppliers s ON pu.supplier_id = s.id
    ORDER BY pu.received_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$active = 'dashboard';
include __DIR__ . '/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex min-h-screen bg-gray-100">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['username']) ?>" class="w-10 h-10 rounded-full" alt="User Avatar">
            </div>
        </header>

        <main class="p-4 sm:p-6 w-full">

            <?php if ($out_of_stock_count > 0): ?>
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 flex justify-between items-center rounded shadow-sm">
                    <span class="flex items-center gap-2">
                        <strong>CRITICAL:</strong> <?= $out_of_stock_count ?> items are completely out of stock!
                    </span>
                    <a href="products/index.php" class="text-xs font-bold uppercase underline">Check Inventory</a>
                </div>
            <?php endif; ?>

            <?php if ($low_stock_count > 0): ?>
                <div class="mb-4 p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-700 rounded shadow-sm flex justify-between items-center">
                    <span>
                        <strong>Low Stock:</strong> <?= $low_stock_count ?> item(s) at or below their reorder level -
                        <?= htmlspecialchars(implode(', ', array_map(
                            fn($p) => $p['name'] . " ({$p['quantity']} left)",
                            array_slice($low_stock_items, 0, 5)
                        ))) ?><?= count($low_stock_items) > 5 ? '...' : '' ?>
                    </span>
                    <a href="restock.php" class="text-xs font-bold uppercase underline whitespace-nowrap ml-4">Restock Now</a>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"> 
                
                <div class="bg-white p-4 rounded-xl shadow hover:shadow-lg transition border-l-4 border-blue-500">                
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Total Catalog</h2>
                    <p class="text-2xl font-bold text-gray-800"><?= (int)$total_products ?></p>
                </div>

                <div class="bg-white p-4 rounded-xl shadow hover:shadow-lg transition border-l-4 border-green-500">
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Available Stock</h2>
                    <p class="text-2xl font-bold text-green-600"><?= (int)$available_stock ?></p>
                </div>

                <div class="bg-white p-4 rounded-xl shadow hover:shadow-lg transition border-l-4 <?= ($low_stock_count > 0) ? 'border-amber-500 bg-amber-50' : 'border-gray-200' ?>">
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Low Stock Warning</h2>
                    <p class="text-2xl font-bold <?= ($low_stock_count > 0) ? 'text-amber-600' : 'text-gray-800' ?>">
                        <?= (int)$low_stock_count ?>
                    </p>
                    <span class="text-[10px] text-gray-400 italic font-mono">Per-product reorder level</span>
                </div>

                <div class="bg-white p-4 rounded-xl shadow hover:shadow-lg transition border-l-4 border-indigo-500">
                   <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Suppliers</h2>
                   <p class="text-2xl font-bold text-gray-800"><?= (int)$total_suppliers ?></p>
                   <a href="suppliers.php" class="text-[10px] text-indigo-500 font-bold uppercase hover:underline">Manage &rarr;</a>
                </div>

            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

                <div class="bg-white p-4 rounded-xl shadow border-l-4 border-slate-700">
                   <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Total Revenue</h2>
                   <p class="text-2xl font-bold text-gray-800">&#8358;<?= number_format($total_revenue, 2) ?></p>
                </div>

                <div class="bg-white p-4 rounded-xl shadow border-l-4 border-emerald-500">
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Amount Collected</h2>
                    <p class="text-2xl font-bold text-emerald-600">&#8358;<?= number_format($total_collected, 2) ?></p>
                </div>

                <div class="bg-white p-4 rounded-xl shadow border-l-4 border-rose-500">
                    <h2 class="text-gray-600 text-xs font-bold uppercase tracking-wider">Outstanding Balance</h2>
                    <p class="text-2xl font-bold text-rose-600">&#8358;<?= number_format($outstanding, 2) ?></p>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <div class="bg-white p-4 sm:p-6 rounded-xl shadow overflow-x-auto">             
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Recent Sales</h2>
                    <table class="min-w-full text-left border-collapse table-auto">                 
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Date</th>
                                <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-right">Amount</th>
                                <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-center">Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_sales)): ?>
                                <tr>
                                    <td colspan="3" class="p-6 border-b text-center text-gray-400 text-sm italic font-mono">No recent sales records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_sales as $sale): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="p-3 border-b text-sm font-mono"><?= date('Y-m-d H:i', strtotime($sale['sale_date'])) ?></td>
                                        <td class="p-3 border-b text-sm font-bold text-gray-800 text-right">&#8358;<?= number_format($sale['total_amount'], 2) ?></td>
                                        <td class="p-3 border-b text-sm text-center">
                                            <span class="px-2 py-1 bg-gray-100 rounded text-[10px] font-bold uppercase text-gray-600"><?= htmlspecialchars($sale['payment_method']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-xl shadow">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Top Products by Sales Value</h2>
                    <?php if (empty($top_products)): ?>
                        <p class="text-center text-gray-400 text-sm italic font-mono py-6">No sales data yet.</p>
                    <?php else: ?>
                        <canvas id="topProductsChart" height="200"></canvas>
                        <script>
                            new Chart(document.getElementById('topProductsChart'), {
                                type: 'bar',
                                data: {
                                    labels: <?= json_encode($top_products_labels) ?>,
                                    datasets: [{
                                        label: 'Sales Value',
                                        data: <?= json_encode($top_products_values) ?>,
                                        backgroundColor: '#3b82f6'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { display: false } },
                                    scales: { y: { beginAtZero: true } }
                                }
                            });
                        </script>
                    <?php endif; ?>
                </div>

            </div>

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Recent Restocks (Purchases)</h2>
                <table class="min-w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Date</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Product</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Supplier</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-center">Qty</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_purchases)): ?>
                            <tr>
                                <td colspan="5" class="p-6 border-b text-center text-gray-400 text-sm italic font-mono">No restocks recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_purchases as $pu): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 border-b text-sm font-mono"><?= date('Y-m-d H:i', strtotime($pu['received_at'])) ?></td>
                                    <td class="p-3 border-b text-sm"><?= htmlspecialchars($pu['product_name']) ?></td>
                                    <td class="p-3 border-b text-sm"><?= htmlspecialchars($pu['supplier_name']) ?></td>
                                    <td class="p-3 border-b text-sm text-center"><?= (int)$pu['quantity'] ?></td>
                                    <td class="p-3 border-b text-sm text-right">&#8358;<?= number_format($pu['total_cost'], 2) ?></td>
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