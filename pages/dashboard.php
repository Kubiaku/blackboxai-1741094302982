<?php
// Get dashboard statistics
$stats = [
    'total_revenue' => fetchOne("SELECT SUM(amount) as total FROM opportunities WHERE stage = 'closed_won'")['total'] ?? 0,
    'open_deals' => fetchOne("SELECT COUNT(*) as count FROM opportunities WHERE stage NOT IN ('closed_won', 'closed_lost')")['count'] ?? 0,
    'new_leads' => fetchOne("SELECT COUNT(*) as count FROM leads WHERE status = 'new'")['count'] ?? 0,
    'win_rate' => fetchOne("
        SELECT 
            ROUND(
                (COUNT(CASE WHEN stage = 'closed_won' THEN 1 END) * 100.0) / 
                COUNT(CASE WHEN stage IN ('closed_won', 'closed_lost') THEN 1 END)
            ) as rate 
        FROM opportunities
    ")['rate'] ?? 0
];

// Get recent activities
$activities = fetchAll("
    SELECT 
        a.*, 
        u.name as performed_by_name 
    FROM activities a 
    LEFT JOIN users u ON a.performed_by = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

// Get upcoming tasks
$tasks = fetchAll("
    SELECT * FROM tasks 
    WHERE status = 'pending' 
    AND due_date >= CURRENT_DATE 
    ORDER BY due_date ASC 
    LIMIT 5
");
?>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Revenue -->
    <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-primary text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                    <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($stats['total_revenue']); ?></p>
                </div>
            </div>
            <span class="text-success text-sm font-medium">
                <i class="fas fa-arrow-up mr-1"></i>12.5%
            </span>
        </div>
        <div class="h-2 bg-gray-100 rounded">
            <div class="h-2 bg-primary rounded" style="width: 75%"></div>
        </div>
    </div>

    <!-- Open Deals -->
    <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-briefcase text-success text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Open Deals</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $stats['open_deals']; ?></p>
                </div>
            </div>
            <span class="text-success text-sm font-medium">
                <i class="fas fa-arrow-up mr-1"></i>8.2%
            </span>
        </div>
        <div class="h-2 bg-gray-100 rounded">
            <div class="h-2 bg-success rounded" style="width: 65%"></div>
        </div>
    </div>

    <!-- New Leads -->
    <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-warning text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">New Leads</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $stats['new_leads']; ?></p>
                </div>
            </div>
            <span class="text-warning text-sm font-medium">
                <i class="fas fa-arrow-right mr-1"></i>0%
            </span>
        </div>
        <div class="h-2 bg-gray-100 rounded">
            <div class="h-2 bg-warning rounded" style="width: 45%"></div>
        </div>
    </div>

    <!-- Win Rate -->
    <div class="bg-white p-6 rounded-lg shadow-sm card-hover">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-danger text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Win Rate</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $stats['win_rate']; ?>%</p>
                </div>
            </div>
            <span class="text-success text-sm font-medium">
                <i class="fas fa-arrow-up mr-1"></i>5.3%
            </span>
        </div>
        <div class="h-2 bg-gray-100 rounded">
            <div class="h-2 bg-danger rounded" style="width: <?php echo $stats['win_rate']; ?>%"></div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Sales Trend Chart -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Sales Trend</h3>
            <select class="text-sm text-gray-500 border rounded-lg px-2 py-1" id="salesTrendPeriod">
                <option value="7">Last 7 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
            </select>
        </div>
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <!-- Pipeline Chart -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Pipeline Overview</h3>
            <button class="text-sm text-primary hover:text-blue-700" onclick="window.location.href='index.php?page=opportunities'">
                View Details
            </button>
        </div>
        <div class="chart-container">
            <canvas id="pipelineChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity & Tasks -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Activity -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
                    <button class="text-sm text-primary hover:text-blue-700" onclick="loadMoreActivities()">View All</button>
                </div>
            </div>
            <div class="p-6 space-y-6">
                <?php foreach ($activities as $activity): ?>
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 <?php echo getActivityIconClass($activity['activity_type']); ?> rounded-full flex items-center justify-center">
                            <i class="<?php echo getActivityIcon($activity['activity_type']); ?>"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($activity['description']); ?></p>
                            <p class="text-sm text-gray-500">By <?php echo htmlspecialchars($activity['performed_by_name']); ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?php echo timeAgo($activity['created_at']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Tasks -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Tasks</h3>
                <button class="text-sm text-primary hover:text-blue-700" onclick="openQuickAction('task')">Add Task</button>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <?php foreach ($tasks as $task): ?>
                    <div class="flex items-center">
                        <input type="checkbox" 
                               class="h-4 w-4 text-primary rounded border-gray-300 focus:ring-primary"
                               onchange="updateTaskStatus(<?php echo $task['id']; ?>, this.checked)">
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($task['title']); ?></p>
                            <p class="text-xs text-gray-500">Due <?php echo formatDate($task['due_date']); ?></p>
                        </div>
                        <span class="status-badge status-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize dashboard charts when the page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardCharts();
});

function initializeDashboardCharts() {
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    const salesTrendChart = new Chart(salesTrendCtx, {
        type: 'line',
        data: getSalesTrendData(),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Pipeline Chart
    const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
    const pipelineChart = new Chart(pipelineCtx, {
        type: 'doughnut',
        data: getPipelineData(),
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Update charts when period changes
    document.getElementById('salesTrendPeriod').addEventListener('change', function() {
        updateSalesTrendChart(salesTrendChart, this.value);
    });
}

// Helper functions for chart data
function getSalesTrendData() {
    return {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Sales',
            data: [12, 19, 15, 25, 22, 30, 28],
            borderColor: '#0061F2',
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(0, 97, 242, 0.1)'
        }]
    };
}

function getPipelineData() {
    return {
        labels: ['Qualified', 'Proposal', 'Negotiation', 'Closed Won'],
        datasets: [{
            data: [30, 25, 20, 25],
            backgroundColor: ['#0061F2', '#00B74A', '#F59E0B', '#DC2626'],
            borderWidth: 0
        }]
    };
}

// Function to update task status
async function updateTaskStatus(taskId, completed) {
    try {
        const response = await fetch(`api/tasks/update/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: completed ? 'completed' : 'pending'
            })
        });

        if (!response.ok) {
            throw new Error('Failed to update task');
        }

        showToast(completed ? 'Task completed!' : 'Task reopened', 'success');
    } catch (error) {
        console.error('Error:', error);
        showToast('Failed to update task', 'error');
    }
}

// Helper functions
function getActivityIconClass(type) {
    const classes = {
        'note': 'bg-blue-100 text-primary',
        'call': 'bg-green-100 text-success',
        'email': 'bg-yellow-100 text-warning',
        'meeting': 'bg-purple-100 text-purple-500',
        'task': 'bg-red-100 text-danger'
    };
    return classes[type] || 'bg-gray-100 text-gray-500';
}

function getActivityIcon(type) {
    const icons = {
        'note': 'fas fa-sticky-note',
        'call': 'fas fa-phone',
        'email': 'fas fa-envelope',
        'meeting': 'fas fa-users',
        'task': 'fas fa-tasks'
    };
    return icons[type] || 'fas fa-dot-circle';
}

function timeAgo(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval > 1) return interval + ' years ago';
    
    interval = Math.floor(seconds / 2592000);
    if (interval > 1) return interval + ' months ago';
    
    interval = Math.floor(seconds / 86400);
    if (interval > 1) return interval + ' days ago';
    
    interval = Math.floor(seconds / 3600);
    if (interval > 1) return interval + ' hours ago';
    
    interval = Math.floor(seconds / 60);
    if (interval > 1) return interval + ' minutes ago';
    
    return 'just now';
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}
</script>
