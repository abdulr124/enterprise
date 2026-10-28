<?php
// includes/sidebar.php
// Include this ONCE from each protected page after includes/auth.php.
// Pass $active = 'dashboard' | 'products' | 'sales' | 'reports' before including.
$active = $active ?? '';
$role   = $_SESSION['user']['role'] ?? '';
?>
<aside class="w-64 bg-gray-900 text-white flex flex-col" x-data="{ openProducts: false, openSales: false }">
   <div class="px-6 py-4 border-b border-gray-700 h-16"></div>
   <nav class="flex-1 px-4 py-6 space-y-2">

        <a href="dashboard.php" class="block px-4 py-2 rounded-lg <?= $active === 'dashboard' ? 'bg-blue-600' : 'hover:bg-gray-800' ?>">Dashboard</a>

        <?php if ($role === 'admin'): ?>
        <div>
            <button @click="openProducts = !openProducts"
                    class="w-full flex justify-between items-center px-4 py-2 rounded-lg hover:bg-gray-800">
                <span>Products</span>
                <svg :class="{ 'rotate-90': openProducts }" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <div x-show="openProducts" class="ml-6 mt-2 space-y-1" x-cloak>
                <a href="products/index.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">All Products</a>
                <a href="products/add.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">Add Product</a>
            </div>
        </div>

        <a href="suppliers.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">Suppliers</a>
        <a href="restock.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">Restock</a>
        <?php endif; ?>

        <div>
            <button @click="openSales = !openSales"
                    class="w-full flex justify-between items-center px-4 py-2 rounded-lg hover:bg-gray-800">
                <span>Sales</span>
                <svg :class="{ 'rotate-90': openSales }" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <div x-show="openSales" class="ml-6 mt-2 space-y-1" x-cloak>
                <a href="sales/invoice.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">Invoices</a>
                <a href="sales/pos.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">POS</a>
                <?php if ($role === 'admin'): ?>
                <a href="sales/sale_items.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">sale-items</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($role === 'admin'): ?>
        <a href="reports/index.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800">Reports</a>
        <?php endif; ?>

   </nav>

   <div class="px-6 py-4 border-t border-gray-700">
      <a href="login.php" class="block px-4 py-2 bg-red-600 rounded-lg text-center hover:bg-red-700">Logout</a>
   </div>
</aside>

<script src="https://unpkg.com/alpinejs" defer></script>