<?php
require_once __DIR__ . '/../config/db.php';
session_start(); // Fixed: Required for isLoggedIn() to work

// Safety check: Define isLoggedIn if it hasn't been defined in header.php
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user']);
    }
}

if(!isLoggedIn()) { 
    header('Location: /inventory/login.php'); 
    exit; 
}

$sale_id = intval($_GET['id'] ?? 0);

if ($sale_id === 0) {
    // ----------------------------------------------------------------
    // SECTION 1: DISPLAY SALES HISTORY LIST (Since no ID is provided)
    // ----------------------------------------------------------------
    $sales = $pdo->query("SELECT id, total_amount, customer_name, sale_date, payment_method FROM sales ORDER BY sale_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    require_once __DIR__ . '/../header.php';
    ?>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">

    <main class="bg-[#f8fafc] min-h-screen p-8">
      <div class="max-w-5xl mx-auto">
        
        <div class="mb-8 flex justify-between items-end border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Sales Records</h2>
                <p class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] mt-1">Archive // Invoice_Ledger_History</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
            <table class="min-w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/50">
                  <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Sale ID</th>
                  <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Timestamp</th>
                  <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Customer Entity</th>
                  <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-right">Settlement (₦)</th>
                  <th class="px-6 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-center">Protocol</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <?php foreach($sales as $sale): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4 font-mono text-[11px] text-slate-400">#<?= $sale['id'] ?></td>
                  <td class="px-6 py-4 text-xs font-mono text-slate-500"><?= date('Y-m-d // H:i', strtotime($sale['sale_date'])) ?></td>
                  <td class="px-6 py-4 text-sm font-semibold text-slate-800"><?= htmlspecialchars($sale['customer_name']) ?></td>
                  <td class="px-6 py-4 text-sm font-mono font-bold text-right text-slate-900"><?= number_format($sale['total_amount'], 2) ?></td>
                  <td class="px-6 py-4 text-center">
                    <a href="invoice.php?id=<?= $sale['id'] ?>" target="_blank" class="inline-block border border-slate-900 text-slate-900 text-[10px] font-bold uppercase tracking-widest px-4 py-1.5 rounded-sm hover:bg-slate-900 hover:text-white transition-all">
                        View Receipt
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            
            <?php if(empty($sales)): ?>
                <div class="p-12 text-center">
                    <p class="font-mono text-xs text-slate-400 italic">Null set: No transaction records found in database.</p>
                </div>
            <?php endif; ?>
        </div>
      </div>
    </main>

    <?php 
    require_once __DIR__ . '/../footer.php';
    exit; 
} 

// ----------------------------------------------------------------
// SECTION 2: DISPLAY SINGLE INVOICE (Receipt Design)
// ----------------------------------------------------------------

$stmt = $pdo->prepare("SELECT id, total_amount, paid_amount, payment_method, customer_name, sale_date FROM sales WHERE id = ?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    die("<p class='font-mono text-center mt-20 text-red-500'>CRITICAL_ERROR: TRANSACTION_ID_NOT_FOUND</p>");
}

$stmt_items = $pdo->prepare("
    SELECT si.quantity, si.unit_price, si.subtotal, p.name AS product_name
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$stmt_items->execute([$sale_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

$change = $sale['paid_amount'] - $sale['total_amount'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $sale_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'IBM Plex Mono', monospace; }
        @media print {
            body { width: 80mm; margin: 0; padding: 5mm; background: white; }
            .no-print { display: none; }
            .invoice-box { box-shadow: none; border: none; width: 100%; margin: 0; }
        }
        .invoice-box {
            max-width: 400px; margin: 40px auto; padding: 25px; border: 1px solid #e2e8f0; background: white;
        }
    </style>
</head>
<body class="bg-slate-50">

<div class="invoice-box shadow-sm">
    <div class="text-center mb-6 border-b border-slate-200 pb-4">
        <h1 class="text-xl font-bold text-slate-900 uppercase tracking-tighter"><?= htmlspecialchars(STORE_NAME) ?></h1>
        <p class="text-[10px] text-slate-500 mt-1 uppercase"><?= htmlspecialchars(STORE_ADDRESS) ?></p>
        <p class="text-[10px] text-slate-500 uppercase">TEL: <?= htmlspecialchars(STORE_PHONE) ?></p>
        <div class="mt-4 pt-2 border-t border-slate-100 font-bold text-xs uppercase tracking-widest">
            Transaction Receipt #<?= $sale['id'] ?>
        </div>
    </div>

    <div class="flex justify-between text-[10px] mb-4 text-slate-600 uppercase">
        <span>Client: <?= htmlspecialchars($sale['customer_name']) ?></span>
        <span><?= date('Y-m-d H:i', strtotime($sale['sale_date'])) ?></span>
    </div>

    <table class="w-full text-[11px] mb-4 border-t border-b border-slate-900 py-2">
        <thead>
            <tr class="font-bold border-b border-slate-100">
                <th class="text-left py-2">ITEM</th>
                <th class="text-center py-2">QTY</th>
                <th class="text-right py-2">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td class="py-1 uppercase"><?= htmlspecialchars($item['product_name']) ?></td>
                <td class="text-center"><?= $item['quantity'] ?></td>
                <td class="text-right"><?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="space-y-1 text-[11px]">
        <div class="flex justify-between font-bold text-sm pt-2 border-t border-slate-100">
            <span>GRAND TOTAL:</span>
            <span>₦<?= number_format($sale['total_amount'], 2) ?></span>
        </div>
        <div class="flex justify-between text-slate-500 uppercase pt-2">
            <span>Method:</span>
            <span><?= htmlspecialchars($sale['payment_method']) ?></span>
        </div>
        <div class="flex justify-between text-slate-500 uppercase">
            <span>Paid:</span>
            <span>₦<?= number_format($sale['paid_amount'], 2) ?></span>
        </div>
        <div class="flex justify-between font-bold pt-1 border-t border-slate-950 mt-2">
            <span>CHANGE DUE:</span>
            <span>₦<?= number_format($change, 2) ?></span>
        </div>
    </div>

    <div class="text-center mt-10 text-[10px] text-slate-400 uppercase tracking-widest">
        *** Thank You for your business ***<br>
        Processed by System Admin
    </div>
</div>

<div class="flex justify-center gap-4 mt-8 no-print pb-10">
    <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 px-8 py-3 rounded-sm text-white text-[10px] font-bold uppercase tracking-widest transition-all">
        Execute Print
    </button>
    <a href="invoice.php" class="bg-white border border-slate-200 px-8 py-3 rounded-sm text-slate-600 text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">
        Back to Ledger
    </a>
</div>

</body>
</html>