<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/auth.php';
require_role(['admin']);

$msg = '';
$editing = null;

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Prevent deleting a supplier that already has purchase history (protects your data integrity)
    $check = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE supplier_id = ?");
    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {
        $msg = "FAIL: Cannot delete - this supplier has existing purchase records.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "OK: Supplier deleted.";
    }
}

// --- ADD / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $id      = intval($_POST['id'] ?? 0);

    if (empty($name)) {
        $msg = "FAIL: Supplier name is required.";
    } else {
        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, contact = ?, email = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $contact, $email, $address, $id]);
            $msg = "OK: Supplier updated.";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact, email, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $contact, $email, $address]);
            $msg = "OK: Supplier added.";
        }
    }
}

// --- EDIT (load existing record into the form) ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- LIST ---
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$active = 'suppliers';
include __DIR__ . '/header.php';
?>

<div class="flex min-h-screen bg-gray-100">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-800">Suppliers</h1>
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

            <div class="bg-white p-6 rounded-xl shadow mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <?= $editing ? 'Edit Supplier' : 'Add New Supplier' ?>
                </h2>
                <form method="post" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input type="hidden" name="id" value="<?= $editing['id'] ?? '' ?>">

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Name *</label>
                        <input type="text" name="name" required
                               value="<?= htmlspecialchars($editing['name'] ?? '') ?>"
                               class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contact / Phone</label>
                        <input type="text" name="contact"
                               value="<?= htmlspecialchars($editing['contact'] ?? '') ?>"
                               class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($editing['email'] ?? '') ?>"
                               class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Address</label>
                        <input type="text" name="address"
                               value="<?= htmlspecialchars($editing['address'] ?? '') ?>"
                               class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="sm:col-span-2 flex gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm font-bold uppercase tracking-widest">
                            <?= $editing ? 'Update Supplier' : 'Add Supplier' ?>
                        </button>
                        <?php if ($editing): ?>
                            <a href="suppliers.php" class="px-6 py-2 rounded text-sm font-bold uppercase tracking-widest text-gray-500 hover:text-gray-800">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">All Suppliers</h2>
                <table class="min-w-full text-left border-collapse table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Name</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Contact</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Email</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b">Address</th>
                            <th class="p-3 text-gray-600 text-xs uppercase font-bold tracking-widest border-b text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="5" class="p-6 border-b text-center text-gray-400 text-sm italic font-mono">No suppliers yet - add one above.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $s): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 border-b text-sm font-semibold text-gray-800"><?= htmlspecialchars($s['name']) ?></td>
                                    <td class="p-3 border-b text-sm text-gray-600"><?= htmlspecialchars($s['contact'] ?: '-') ?></td>
                                    <td class="p-3 border-b text-sm text-gray-600"><?= htmlspecialchars($s['email'] ?: '-') ?></td>
                                    <td class="p-3 border-b text-sm text-gray-600"><?= htmlspecialchars($s['address'] ?: '-') ?></td>
                                    <td class="p-3 border-b text-sm text-center space-x-3">
                                        <a href="suppliers.php?edit=<?= $s['id'] ?>" class="text-blue-600 hover:underline text-xs font-bold uppercase">Edit</a>
                                        <a href="suppliers.php?delete=<?= $s['id'] ?>"
                                           onclick="return confirm('Delete this supplier?');"
                                           class="text-red-600 hover:underline text-xs font-bold uppercase">Delete</a>
                                    </td>
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