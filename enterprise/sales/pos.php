<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth.php';
require_role(['admin', 'staff']);

$user_id = current_user_id();

// Fetch available products for the terminal
$products = $pdo->query("SELECT id, name, price, quantity FROM products WHERE quantity > 0 ORDER BY name ASC")->fetchAll();
$msg = '';
$lastSaleId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_ids = $_POST['product_id'] ?? [];
    $qtys        = $_POST['qty'] ?? [];
    $customer    = trim($_POST['customer_name'] ?? 'Walk-in Customer');
    $payment     = $_POST['payment_method'] ?? 'Cash';
    $paid_amount = floatval($_POST['paid_amount'] ?? 0);

    $items = [];
    $total = 0;
    $valid_sale = true;

    try {
        $pdo->beginTransaction();

        for ($i=0; $i < count($product_ids); $i++) {
            $pid = intval($product_ids[$i]);
            $q   = intval($qtys[$i]);

            if ($q <= 0) continue;

            $stmt = $pdo->prepare("SELECT name, price, quantity FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$pid]);
            $prod = $stmt->fetch();

            if (!$prod) {
                $msg = "❌ Error: Product ID $pid not found.";
                $valid_sale = false;
                break;
            }

            if ($q > $prod['quantity']) {
                $msg = "❌ Stock Error: Only {$prod['quantity']} units of " . htmlspecialchars($prod['name']) . " available.";
                $valid_sale = false;
                break;
            }

            $subtotal = $q * $prod['price'];
            $items[] = [
                'product_id' => $pid,
                'qty'        => $q,
                'unit_price' => $prod['price'],
                'subtotal'   => $subtotal
            ];
            $total += $subtotal;
        }

        if ($valid_sale && count($items) > 0) {
            $stmt = $pdo->prepare("INSERT INTO sales (user_id, total_amount, paid_amount, payment_method, customer_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $total, $paid_amount, $payment, $customer]);
            $sale_id = $pdo->lastInsertId();
            $lastSaleId = $sale_id;

            $stmtItem   = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmtUpdate = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");

            foreach($items as $it) {
                $stmtItem->execute([$sale_id, $it['product_id'], $it['qty'], $it['unit_price'], $it['subtotal']]);
                $stmtUpdate->execute([$it['qty'], $it['product_id']]);
            }

            $pdo->commit();
            $msg = '✅ Transaction Finalized. Stock levels updated.';
        } elseif ($valid_sale && count($items) === 0) {
            $msg = "⚠️ Input Required: Select at least one product quantity.";
            $pdo->rollBack();
        } else {
            $pdo->rollBack();
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $msg = "❌ System Fault: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    .font-mono { font-family: 'IBM Plex Mono', monospace; }
</style>

<main class="p-8">
  <div class="max-w-6xl mx-auto">
    <div class="mb-8 border-b border-slate-200 pb-4 flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Terminal POS</h2>
            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-[0.2em] mt-1">Terminal_ID // 01_Live_Sales</p>
        </div>
        <a href="../cashier_dashboard.php" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900">← Back to Dashboard</a>
    </div>

    <?php if($msg): ?>
      <div class="p-4 mb-6 text-xs font-bold uppercase tracking-widest rounded-sm border <?= (strpos($msg, '❌') !== false) ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
        <?=htmlspecialchars($msg)?>
      </div>
    <?php endif; ?>

    <form method="post" id="posForm" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <div class="bg-white border border-slate-200 rounded-sm shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-slate-50/50">
                    <th class="px-4 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100">Product Asset</th>
                    <th class="px-4 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-right">Price</th>
                    <th class="px-4 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-center">In-Stock</th>
                    <th class="px-4 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-center">Sale Qty</th>
                    <th class="px-4 py-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-100 text-right">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <?php foreach($products as $p): ?>
                  <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-4">
                      <input type="hidden" name="product_id[]" value="<?=$p['id']?>">
                      <span class="text-sm font-semibold text-slate-800 uppercase"><?=htmlspecialchars($p['name'])?></span>
                    </td>
                    <td class="px-4 py-4 text-sm font-mono text-slate-500 text-right">₦<?=number_format($p['price'],2)?></td>
                    <td class="px-4 py-4 text-center">
                        <span class="stock-badge text-[10px] font-mono bg-slate-50 px-2 py-1 rounded border border-slate-200"><?=$p['quantity']?></span>
                    </td>
                    <td class="px-4 py-4 text-center">
                      <input type="number" name="qty[]" min="0" max="<?=$p['quantity']?>" 
                             class="qty-input w-20 bg-slate-50 border border-slate-200 p-1.5 text-center font-mono text-sm focus:ring-1 focus:ring-slate-900 outline-none"
                             data-price="<?=$p['price']?>" data-max="<?=$p['quantity']?>" value="0">
                    </td>
                    <td class="px-4 py-4 text-right font-mono text-sm text-slate-900 font-bold subtotal">0.00</td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
            </table>
        </div>
      </div>

      <div class="space-y-6">
        <div class="bg-slate-900 p-6 rounded-sm shadow-xl text-white">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-800 pb-4">Checkout Summary</h3>
            
            <div class="space-y-5 mb-8">
                <div>
                    <label class="block text-[9px] uppercase tracking-widest text-slate-500 mb-1">Customer Identifier</label>
                    <input name="customer_name" class="w-full bg-slate-800 border-none p-3 text-sm rounded-sm focus:ring-1 focus:ring-blue-500 outline-none" placeholder="Walk-in Customer">
                </div>
                <div>
                    <label class="block text-[9px] uppercase tracking-widest text-slate-500 mb-1">Payment Protocol</label>
                    <select name="payment_method" class="w-full bg-slate-800 border-none p-3 text-sm rounded-sm focus:ring-1 focus:ring-blue-500 outline-none">
                        <option>Cash</option>
                        <option>Card</option>
                        <option>Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] uppercase tracking-widest text-slate-500 mb-1">Received Amount (₦)</label>
                    <input name="paid_amount" id="paidAmount" type="number" step="0.01" class="w-full bg-slate-800 border-none p-3 text-sm font-mono rounded-sm focus:ring-1 focus:ring-blue-500 outline-none" value="0.00">
                </div>
            </div>

            <div class="border-t border-slate-800 pt-6 space-y-3">
                <div class="flex justify-between items-center text-slate-400 text-xs">
                    <span class="uppercase tracking-widest">Total Valuation</span>
                    <span class="font-mono text-xl text-white font-bold">₦<span id="totalDisplay">0.00</span></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[9px] uppercase tracking-widest text-slate-500">Balance Owed</span>
                    <span class="font-mono text-emerald-400 font-bold" id="changeDisplay">0.00</span>
                </div>
            </div>

            <button type="submit" class="w-full mt-10 bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 text-xs uppercase tracking-[0.2em] rounded-sm transition-all shadow-lg">
                Execute Transaction
            </button>
        </div>

        <?php if($lastSaleId): ?>
          <div class="bg-white border border-slate-200 p-6 rounded-sm text-center shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 italic">Ledger Entry Created</p>
            <a href="invoice.php?id=<?=$lastSaleId?>" target="_blank" class="block w-full bg-slate-100 text-slate-900 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all">
                Print Official Receipt
            </a>
          </div>
        <?php endif; ?>
      </div>
    </form>
  </div>
</main>

<script>
const qtyInputs = document.querySelectorAll('.qty-input');
const totalDisplay = document.getElementById('totalDisplay');
const changeDisplay = document.getElementById('changeDisplay');
const paidAmount = document.getElementById('paidAmount');

function calculateTotals() {
  let total = 0;
  document.querySelectorAll('tbody tr').forEach(row => {
    const qtyInput = row.querySelector('.qty-input');
    const subtotalCell = row.querySelector('.subtotal');
    const stockBadge = row.querySelector('.stock-badge');
    const price = parseFloat(qtyInput.dataset.price) || 0;
    
    let qty = parseInt(qtyInput.value) || 0;
    const maxQty = parseInt(qtyInput.dataset.max);
    
    if (qty > maxQty) { 
        qty = maxQty; 
        qtyInput.value = maxQty; 
        stockBadge.classList.add('bg-red-50', 'text-red-600', 'border-red-200');
    } else {
        stockBadge.classList.remove('bg-red-50', 'text-red-600', 'border-red-200');
    }
    
    if (qty < 0) { qty = 0; qtyInput.value = 0; }

    const subtotal = qty * price;
    subtotalCell.textContent = subtotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    total += subtotal;
  });
  
  totalDisplay.textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2});
  
  let paid = parseFloat(paidAmount.value) || 0;
  
  if (paid === 0 && total > 0) {
      paidAmount.value = total.toFixed(2);
      paid = total;
  }

  let change = paid - total;
  changeDisplay.textContent = change.toLocaleString('en-US', {minimumFractionDigits: 2});
  changeDisplay.className = change < 0 ? 'font-mono text-red-400 font-bold' : 'font-mono text-emerald-400 font-bold';
}

qtyInputs.forEach(input => input.addEventListener('input', calculateTotals));
paidAmount.addEventListener('input', calculateTotals);
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>