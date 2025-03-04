<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get the requested page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$validPages = ['dashboard', 'leads', 'opportunities', 'contacts', 'tasks', 'reports', 'settings'];
$page = in_array($page, $validPages) ? $page : 'dashboard';

// Get user data
$userData = getUserDetails($_SESSION['user_id']);

// Include header
include 'includes/header.php';
?>

<!-- Main Layout -->
<div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Left side -->
                    <div class="flex">
                        <button class="lg:hidden px-4 text-gray-500 focus:outline-none" id="sidebarToggle">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-semibold text-gray-800 capitalize">
                                <?php echo $page; ?>
                            </h1>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center">
                        <!-- Search -->
                        <div class="hidden md:block">
                            <div class="relative">
                                <input type="text" 
                                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-primary" 
                                       placeholder="Search...">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <button class="ml-4 p-2 text-gray-400 hover:text-gray-500 relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                        </button>

                        <!-- Profile dropdown -->
                        <div class="ml-4 relative flex items-center">
                            <div class="flex items-center">
                                <img class="h-8 w-8 rounded-full" 
                                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($userData['name']); ?>&background=0061F2&color=fff" 
                                     alt="Profile">
                                <span class="ml-2 text-sm font-medium text-gray-700">
                                    <?php echo htmlspecialchars($userData['name']); ?>
                                </span>
                            </div>
                            <div class="ml-2">
                                <form method="post" action="includes/logout.php" class="inline">
                                    <button type="submit" class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <?php
                    $pagePath = "pages/{$page}.php";
                    if (file_exists($pagePath)) {
                        include $pagePath;
                    } else {
                        echo '<div class="bg-white rounded-lg shadow-sm p-6">
                                <h2 class="text-2xl font-semibold text-gray-800">404 - Page Not Found</h2>
                                <p class="mt-2 text-gray-600">The requested page could not be found.</p>
                              </div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Quick Actions -->
<?php include 'includes/quick-actions.php'; ?>

<!-- Toast Container -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-50"></div>

<!-- Scripts -->
<script src="js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize page-specific functionality
    if (typeof initializePage === 'function') {
        initializePage('<?php echo $page; ?>');
    }

    // Handle sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar && overlay) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }
});
</script>
</body>
</html>
