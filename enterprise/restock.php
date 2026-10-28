<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';
require_role(['admin']);

$msg = '';

// --- RECORD A RESTOCK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = intval($_POST['supplier_id'] ?? 0);
    $product_id  = intval($_POST['product_id'] ?? 0);
    $quantity    = intval($_POST['quantity'] ?? 0);
    $unit_cost   = floatval($_POST['unit_cost'] ?? 0);

    if ($supplier_id <= 0 || $product_id <= 0) {
        $msg = "FAIL: Please select both a supplier and a product.";
    } elseif ($quantity <= 0) {
        $msg = "FAIL: Quantity must be greater than 0.";
    } elseif ($unit_cost < 0) {
        $msg = "FAIL: Cost cannot be negative.";
    } else {
        $total_cost = $quantity * $unit_cost;

        try {
            $pdo->beginTransaction();

            // 1. Record the purchase
            $stmt = $pdo->prepare("
                INSERT INTO purchases (supplier_id, product_id, quantity, unit_cost, total_cost, received_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$supplier_id, $product_id, $quantity, $unit_cost, $total_cost]);

            // 2. Increase the product's stock (no DB trigger installed, so we do it here)
            $stmt2 = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $stmt2->execute([$quantity, $product_id]);

            $pdo->commit();
            $msg = "OK: Restock recorded and stock updated.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "FAIL: " . $e->getMessage();
        }
    }
}

// --- Data for the form ---
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$products  = $pdo->query("SELECT id, name, sku, quantity FROM products ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- Restock history ---
$history = $pdo->query("
    SELECT pu.received_at, pu.quantity, pu.unit_cost, pu.total_cost,
           p.name AS product_name, s.name AS supplier_name
    FROM purchases pu
    JOIN products p ON pu.product_id = p.id
    JOIN suppliers s ON pu.supplier_id = s.id
    ORDER BY pu.received_at DESC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);

$active = 'suppliers';
include __DIR__ . '/header.php';
?>

<div class="flex min-h-screen bg-gray-100">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Restock Inventory</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></span>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['username']) ?>" class="w-10 h-10 rounded-full" alt="User Avatar">
            </div>
        </header>

        <main class="p-4 sm:p-6 w-full">

            <?php if ($msg): ?>
                <div class="mb-4 p-4 rounded text-sm font-semibold <?= strpos($msg, 'FAIL') !== false ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($suppliers)): ?>
                <div class="mb-4 p-4 rounded text-sm font-semibold bg-amber-100 text-amber-700">
                    No suppliers yet. <a href="suppliers.php" class="underline font-bold">Add a supplier first</a> before restocking.
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-xl shadow mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Record a Restock</h2>
                <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier *</label>
                        <select name="supplier_id" required class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                            <option value="">-- Select Supplier --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product *</label>
                        <select name="product_id" required class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>) - currently <?= $p['quantity'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity Received *</label>
                        <input type="number" name="quantity" min="1" required
                               class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cost per Unit (&#8358;) *</label>
                        <input type="number" name="unit_cost" step="0.01" min="0" required
                               class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded text-sm font-bold uppercase tracking-widest">
                            Confirm Restock
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Restock History</h2>
                <table class="min-w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Date</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Product</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Supplier</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-center">Qty</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-right">Unit Cost</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="6" class="p-6 border-b text-center text-gray-400 text-sm italic font-mono">No restocks recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 border-b text-sm font-mono"><?= date('Y-m-d H:i', strtotime($h['received_at'])) ?></td>
                                    <td class="p-3 border-b text-sm"><?= htmlspecialchars($h['product_name']) ?></td>
                                    <td class="p-3 border-b text-sm"><?= htmlspecialchars($h['supplier_name']) ?></td>
                                    <td class="p-3 border-b text-sm text-center"><?= (int)$h['quantity'] ?></td>
                                    <td class="p-3 border-b text-sm text-right">&#8358;<?= number_format($h['unit_cost'], 2) ?></td>
                                    <td class="p-3 border-b text-sm text-right font-bold">&#8358;<?= number_format($h['total_cost'], 2) ?></td>
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