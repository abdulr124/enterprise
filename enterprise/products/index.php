<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user']);
    }
}

if(!isLoggedIn()) { 
    header('Location: /inventory/login.php'); 
    exit; 
}

// --- 1. FETCH OUT OF STOCK ITEMS ---
$oos_stmt = $pdo->query("SELECT name FROM products WHERE quantity <= 0");
$out_of_stock_items = $oos_stmt->fetchAll(PDO::FETCH_COLUMN);
$oos_count = count($out_of_stock_items);

// --- 2. NEW: FETCH LOW STOCK ITEMS (Between 1 and 5) ---
$low_threshold = 5;
$low_stmt = $pdo->prepare("SELECT name FROM products WHERE quantity > 0 AND quantity <= ?");
$low_stmt->execute([$low_threshold]);
$low_stock_items = $low_stmt->fetchAll(PDO::FETCH_COLUMN);
$low_count = count($low_stock_items);

// Existing Search Logic
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR sku LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$q%","%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}
$products = $stmt->fetchAll();

require_once __DIR__ . '/../header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">

<div class="p-8 bg-[#f8fafc] min-h-screen">
    
    <?php if ($oos_count > 0): ?>
    <div class="mb-4 flex items-center p-4 bg-red-50 border-l-4 border-red-500 rounded-sm shadow-sm">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-bold text-red-800 uppercase tracking-tight">Critical: Out of Stock</h3>
            <div class="text-xs text-red-700 mt-1">
                <span class="font-bold"><?= $oos_count ?></span> item(s) are empty: 
                <span class="italic font-medium text-red-900"><?= implode(', ', array_slice($out_of_stock_items, 0, 3)) ?><?= $oos_count > 3 ? '...' : '' ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($low_count > 0): ?>
    <div class="mb-6 flex items-center p-4 bg-amber-50 border-l-4 border-amber-500 rounded-sm shadow-sm">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-bold text-amber-800 uppercase tracking-tight">Warning: Low Stock</h3>
            <div class="text-xs text-amber-700 mt-1">
                <span class="font-bold"><?= $low_count ?></span> item(s) are running low: 
                <span class="italic font-medium text-amber-900"><?= implode(', ', array_slice($low_stock_items, 0, 3)) ?><?= $low_count > 3 ? '...' : '' ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Products</h1>
            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] mt-1">Inventory // Master_Catalog</p>
        </div>
        <a href="add.php" class="bg-slate-900 text-white px-6 py-2.5 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition-all shadow-sm">
            + Add Product
        </a>
    </div>

    <div class="bg-white p-4 border border-slate-200 rounded-sm mb-6 shadow-sm">
        <form method="get" class="flex gap-2">
            <input name="q" value="<?=htmlspecialchars($q)?>" 
                placeholder="Search by product name or SKU..." 
                class="flex-1 bg-slate-50 border border-slate-200 p-2.5 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-400 transition-all">
            <button class="px-6 py-2 bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold uppercase tracking-widest rounded-sm hover:bg-slate-200 transition-all">
                Search
            </button>
            <?php if ($q !== ''): ?>
                <a href="index.php" class="px-4 py-2 text-xs text-slate-400 hover:text-slate-600 flex items-center italic">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
        <table class="min-w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 w-16">#</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Name</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">SKU</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Category</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-right">Price</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-center">Qty</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-mono text-xs italic">No matching products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($products as $p): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-mono text-[11px] text-slate-400">#<?=$p['id']?></td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-800"><?=htmlspecialchars($p['name'])?></td>
                        <td class="px-6 py-4 text-[11px] font-mono text-slate-500 uppercase"><?=htmlspecialchars($p['sku'])?></td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full uppercase border border-slate-200">
                                <?=htmlspecialchars($p['category'])?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-slate-900 text-right">₦<?=number_format($p['price'],2)?></td>
                        <td class="px-6 py-4 text-sm text-center font-mono">
                            <?php if ($p['quantity'] <= 0): ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-sm font-bold uppercase text-[10px]">Out of Stock</span>
                            <?php elseif ($p['quantity'] <= $low_threshold): ?>
                                <span class="bg-amber-100 text-amber-700 px-2 py-1 rounded-sm font-bold text-[11px] italic"><?=$p['quantity']?> (Low)</span>
                            <?php else: ?>
                                <span class="text-slate-600"><?=$p['quantity']?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="edit.php?id=<?=$p['id']?>" class="text-[10px] font-bold text-blue-600 uppercase hover:underline">Edit</a>
                            <a href="delete.php?id=<?=$p['id']?>" 
                               onclick="return confirm('Are you sure you want to delete this product? This will also remove its sale history.')" 
                               class="text-[10px] font-bold text-red-500 uppercase hover:underline">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>