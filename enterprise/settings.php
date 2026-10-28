<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/header.php';

if(!isLoggedIn()) { header('Location: /inventory/login.php'); exit; }

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_update = [
        'store_name'    => $_POST['store_name'] ?? '',
        'store_address' => $_POST['store_address'] ?? '',
        'store_phone'   => $_POST['store_phone'] ?? '',
    ];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            UPDATE settings
            SET setting_value = :value
            WHERE setting_key = :key
        ");

        foreach ($settings_to_update as $key => $value) {
            $stmt->execute([':key' => $key, ':value' => $value]);
        }

        $pdo->commit();
        $msg = '✅ Company settings updated successfully!';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $msg = "❌ Error updating settings: " . $e->getMessage();
    }
}

// Fetch current settings for form display
$settings_data = [];
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings_data_raw = $settings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert array to key => value for easy access
foreach ($settings_data_raw as $row) {
    $settings_data[$row['setting_key']] = $row['setting_value'];
}
?>

<main class="bg-gray-100 min-h-screen p-8">
  <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg p-6">
    <h2 class="text-3xl font-bold mb-6">🏢 Update Company Details</h2>

    <?php if($msg): ?>
      <div class="p-3 rounded mb-4 <?= (strpos($msg, '❌') !== false) ? 'bg-red-500 text-white' : 'bg-green-500 text-white' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post">
      
      <div class="mb-4">
        <label for="store_name" class="block text-sm font-medium text-gray-700">Store Name</label>
        <input type="text" id="store_name" name="store_name" 
               value="<?= htmlspecialchars($settings_data['store_name'] ?? '') ?>" 
               required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
      </div>
      
      <div class="mb-4">
        <label for="store_address" class="block text-sm font-medium text-gray-700">Address</label>
        <input type="text" id="store_address" name="store_address" 
               value="<?= htmlspecialchars($settings_data['store_address'] ?? '') ?>" 
               class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
      </div>
      
      <div class="mb-6">
        <label for="store_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
        <input type="text" id="store_phone" name="store_phone" 
               value="<?= htmlspecialchars($settings_data['store_phone'] ?? '') ?>" 
               class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md font-bold">
        💾 Save Company Updates
      </button>
    </form>
  </div>
</main>

<?php require_once __DIR__ . '/../footer.php'; ?>