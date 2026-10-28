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

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sku = trim($_POST['sku']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $cost = floatval($_POST['cost_price']);
    $qty = intval($_POST['quantity']);
    $desc = trim($_POST['description']);

    if ($name === '') $errors[] = "Name required";
    if ($sku === '') $errors[] = "SKU required";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, sku, category, price, cost_price, quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $sku, $category, $price, $cost, $qty, $desc]);
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            // Error code 23000 is for Integrity constraint violation (Duplicate Entry)
            if ($e->getCode() == 23000) {
                $errors[] = "The SKU '$sku' already exists. Please use a unique ID.";
            } else {
                $errors[] = "Database Error: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Inter', sans-serif; }
    .font-mono { font-family: 'IBM Plex Mono', monospace; }
</style>

<main class="p-8 bg-[#f8fafc] min-h-screen">
    <div class="max-w-3xl mx-auto">
        
        <div class="mb-8 border-b border-slate-200 pb-4">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Add New Product</h2>
            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] mt-1">Inventory Management // Asset_Creation</p>
        </div>

        <?php if($errors): ?>
            <div class="mb-6 space-y-2">
                <?php foreach($errors as $e): ?>
                    <div class="flex items-center gap-3 bg-red-50 border-l-4 border-red-500 p-4 text-red-700 text-xs font-bold uppercase tracking-tight shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <?=htmlspecialchars($e)?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
            <form method="post" class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Product Name</label>
                        <input name="name" value="<?=isset($name)?htmlspecialchars($name):''?>" class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-950 focus:bg-white transition-all" placeholder="e.g. Industrial Processor" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">SKU / ID</label>
                        <input name="sku" value="<?=isset($sku)?htmlspecialchars($sku):''?>" class="w-full bg-slate-50 border border-slate-200 p-3 text-sm font-mono rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-950 focus:bg-white transition-all" placeholder="SKU-0000" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Category</label>
                        <input name="category" value="<?=isset($category)?htmlspecialchars($category):''?>" class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-950 focus:bg-white transition-all" placeholder="Electronics">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Selling Price (₦)</label>
                        <input name="price" type="number" step="0.01" value="<?=isset($price)?$price:''?>" class="w-full bg-slate-50 border border-slate-200 p-3 text-sm font-mono rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-950 focus:bg-white transition-all" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Cost Price (₦)</label>
                        <input name="cost_price" type="number" step="0.01" value="<?=isset($cost)?$cost:''?>" class="w-full bg-slate-50 border border-slate-200 p-3 text-sm font-mono rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-950 focus:bg-white transition-all" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Initial Quantity</label>
                        <input name="quantity" type="number" value="<?=isset($qty)?$qty:''?>" class="w-full bg-slate-50 border border-slate-200 p-3 text-sm font-mono rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-950 focus:bg-white transition-all" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Product Description</label>
                        <textarea name="description" rows="4" class="w-full bg-slate-50 border border-slate-200 p-3 text-sm rounded-sm focus:outline-none focus:ring-1 focus:ring-slate-950 focus:bg-white transition-all" placeholder="Enter technical specifications..."><?=isset($desc)?htmlspecialchars($desc):''?></textarea>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <a href="index.php" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors">
                        &larr; Cancel Entry
                    </a>
                    <button class="bg-slate-900 text-white px-10 py-3 text-xs font-bold uppercase tracking-[0.2em] rounded-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
                        Commit Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../footer.php'; ?>