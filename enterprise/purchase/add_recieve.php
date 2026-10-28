<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../header.php';
if(!isLoggedIn()) { header('Location: /inventory/login.php'); exit; }

$errors = [];
// Fetch products and suppliers for dropdowns
$products = $pdo->query("SELECT id, name, quantity FROM products")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = intval($_POST['supplier_id']);
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['quantity']);
    $unit_cost = floatval($_POST['unit_cost']);
    $received_at = $_POST['received_at'];

    if ($qty <= 0) $errors[] = 'Quantity must be > 0';

    if (empty($errors)) {
        $total = $qty * $unit_cost;
        $stmt = $pdo->prepare("INSERT INTO purchases (supplier_id, product_id, quantity, unit_cost, total_cost, received_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$supplier_id, $product_id, $qty, $unit_cost, $total, $received_at]);

        // update product quantity
        $stmt2 = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $stmt2->execute([$qty, $product_id]);

        header('Location: index.php');
        exit;
    }
}
?>

<div class="max-w-2xl bg-white p-6 rounded shadow">
  <h2 class="text-xl mb-4">Receive Goods</h2>
  <?php foreach($errors as $e): ?><div class="text-red-600 mb-2"><?=htmlspecialchars($e)?></div><?php endforeach; ?>
  <form method="post">
    <label class="block mb-2">Supplier
      <select name="supplier_id" class="border p-2 w-full">
        <?php foreach($suppliers as $s): ?><option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option><?php endforeach; ?>
      </select>
    </label>
    <label class="block mb-2">Product
      <select name="product_id" class="border p-2 w-full">
        <?php foreach($products as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?> (current: <?=$p['quantity']?>)</option><?php endforeach; ?>
      </select>
    </label>
    <label class="block mb-2">Quantity <input name="quantity" type="number" class="border p-2 w-full"></label>
    <label class="block mb-2">Unit Cost <input name="unit_cost" type="number" step="0.01" class="border p-2 w-full"></label>
    <label class="block mb-4">Received Date <input name="received_at" type="date" class="border p-2 w-full" value="<?=date('Y-m-d')?>"></label>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
  </form>
</div>
<?php require_once __DIR__ . '/../footer.php'; ?>
