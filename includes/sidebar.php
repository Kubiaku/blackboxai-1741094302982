<?php
// Get current counts from database (these would normally come from your database)
$leadCount = 12;
$opportunityCount = 8;
$taskCount = 5;
?>
<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg sidebar-transition lg:translate-x-0 z-30 custom-scrollbar">
    <div class="flex items-center justify-center h-16 border-b">
        <a href="index.php" class="text-xl font-bold text-primary">SalesHub</a>
    </div>
    <nav class="mt-6">
        <div class="px-4 space-y-2">
            <a href="index.php?page=dashboard" 
               class="flex items-center px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors
                      <?php echo $page === 'dashboard' ? 'bg-gray-100 text-gray-700' : ''; ?>">
                <i class="fas fa-chart-pie mr-3 <?php echo $page === 'dashboard' ? 'text-primary' : ''; ?>"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="index.php?page=leads" 
               class="flex items-center px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors
                      <?php echo $page === 'leads' ? 'bg-gray-100 text-gray-700' : ''; ?>">
                <i class="fas fa-user-plus mr-3"></i>
                <span>Leads</span>
                <?php if ($leadCount > 0): ?>
                    <span class="ml-auto bg-blue-100 text-primary text-xs px-2 py-1 rounded-full"><?php echo $leadCount; ?></span>
                <?php endif; ?>
            </a>
            
            <a href="index.php?page=opportunities" 
               class="flex items-center px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors
                      <?php echo $page === 'opportunities' ? 'bg-gray-100 text-gray-700' : ''; ?>">
                <i class="fas fa-briefcase mr-3"></i>
                <span>Opportunities</span>
                <?php if ($opportunityCount > 0): ?>
                    <span class="ml-auto bg-green-100 text-success text-xs px-2 py-1 rounded-full"><?php echo $opportunityCount; ?></span>
                <?php endif; ?>
            </a>
            
            <a href="index.php?page=contacts" 
               class="flex items-center px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors
                      <?php echo $page === 'contacts' ? 'bg-gray-100 text-gray-700' : ''; ?>">
                <i class="fas fa-address-book mr-3"></i>
                <span>Contacts</span>
            </a>
            
            <a href="index.php?page=tasks" 
               class="flex items-center px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors
                      <?php echo $page === 'tasks' ? 'bg-gray-100 text-gray-700' : ''; ?>">
                <i class="fas fa-tasks mr-3"></i>
                <span>Tasks</span>
                <?php if ($taskCount > 0): ?>
                    <span class="ml-auto bg-yellow-100 text-warning text-xs px-2 py-1 rounded-full"><?php echo $taskCount; ?></span>
                <?php endif; ?>
            </a>
            
            <a href="index.php?page=reports" 
               class="flex items-center px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors
                      <?php echo $page === 'reports' ? 'bg-gray-100 text-gray-700' : ''; ?>">
                <i class="fas fa-chart-line mr-3"></i>
                <span>Reports</span>
            </a>
            
            <a href="index.php?page=settings" 
               class="flex items-center px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors
                      <?php echo $page === 'settings' ? 'bg-gray-100 text-gray-700' : ''; ?>">
                <i class="fas fa-cog mr-3"></i>
                <span>Settings</span>
            </a>
        </div>
    </nav>
</div>

<!-- Mobile Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden" onclick="toggleSidebar()"></div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar-transition');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// Add event listener to sidebar toggle button
document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);
</script>
