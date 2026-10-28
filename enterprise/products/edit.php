<?php
// 1. Load the database first
require_once __DIR__ . '/../config/db.php';

// 2. Load the header (which starts session and checks login)
require_once __DIR__ . '/../header.php';

// 3. Ensure $pdo is available globally
global $pdo;

// Error Check: If for some reason db.php failed, stop here
if (!$pdo) {
    die("❌ SYSTEM_CRITICAL: Database connection (PDO) is null. Check your config/db.php file.");
}

$id = intval($_GET['id'] ?? 0);
$msg = '';

// --- DATABASE LOGIC ---
try {
    // Check if we are saving data (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name']);
        $sku      = trim($_POST['sku']);
        $category = trim($_POST['category']);
        $price    = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);

        $update = $pdo->prepare("UPDATE products SET name = ?, sku = ?, category = ?, price = ?, quantity = ? WHERE id = ?");
        $update->execute([$name, $sku, $category, $price, $quantity, $id]);
        $msg = "✅ RECORD_UPDATED_SUCCESSFULLY";
    }

    // Fetch the product to display in the form
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        die("❌ ERROR: Product #$id not found in the master catalog.");
    }

} catch (PDOException $e) {
    $msg = "❌ DATABASE_ERROR: " . $e->getMessage();
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">

<div class="p-8 bg-[#f8fafc] min-h-screen">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8 border-b border-slate-200 pb-4 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight text-uppercase">Edit Product</h1>
                <p class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] mt-1">Registry_ID // #<?= $id ?></p>
            </div>
            <a href="index.php" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest hover:text-slate-900 transition-colors">
                &larr; Return to Catalog
            </a>
        </div>

        <?php if($msg): ?>
            <div class="mb-6 p-4 text-[10px] font-mono font-bold uppercase tracking-widest border <?= strpos($msg, '✅') !== false ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-200 rounded-sm shadow-sm">
            <form method="POST" class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Product Descriptor</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:ring-1 focus:ring-slate-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Inventory SKU</label>
                        <input type="text" name="sku" value="<?= htmlspecialchars($product['sku']) ?>" required
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm font-mono rounded-sm focus:ring-1 focus:ring-slate-900 outline-none uppercase">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Asset Category</label>
                        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:ring-1 focus:ring-slate-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Unit Price (₦)</label>
                        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm font-mono rounded-sm focus:ring-1 focus:ring-slate-900 outline-none">
                    </div>

                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">Current Stock</label>
                        <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required
                               class="w-full bg-slate-50 border border-slate-200 p-3 text-sm font-mono rounded-sm focus:ring-1 focus:ring-slate-900 outline-none">
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 text-[10px] uppercase tracking-[0.2em] rounded-sm hover:bg-slate-800 transition-all shadow-lg">
                        Commit Changes to Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>