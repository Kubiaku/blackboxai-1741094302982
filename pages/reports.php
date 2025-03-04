<?php
// Get sales summary
$salesSummary = fetchOne("
    SELECT 
        COUNT(DISTINCT o.id) as total_opportunities,
        SUM(o.amount) as total_amount,
        AVG(o.amount) as avg_amount,
        COUNT(DISTINCT CASE WHEN o.stage = 'closed_won' THEN o.id END) as won_opportunities,
        COUNT(DISTINCT CASE WHEN o.stage = 'closed_lost' THEN o.id END) as lost_opportunities,
        ROUND(
            (COUNT(DISTINCT CASE WHEN o.stage = 'closed_won' THEN o.id END) * 100.0) /
            NULLIF(COUNT(DISTINCT CASE WHEN o.stage IN ('closed_won', 'closed_lost') THEN o.id END), 0)
        ) as win_rate
    FROM opportunities o
");

// Get pipeline by stage
$pipeline = fetchAll("
    SELECT 
        stage,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM opportunities
    WHERE stage NOT IN ('closed_won', 'closed_lost')
    GROUP BY stage
    ORDER BY FIELD(stage, 'prospecting', 'qualification', 'proposal', 'negotiation')
");

// Get leads by status
$leadsByStatus = fetchAll("
    SELECT 
        status,
        COUNT(*) as count
    FROM leads
    GROUP BY status
");

// Get monthly sales trend
$salesTrend = fetchAll("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total_deals,
        SUM(amount) as total_amount,
        COUNT(CASE WHEN stage = 'closed_won' THEN 1 END) as won_deals,
        SUM(CASE WHEN stage = 'closed_won' THEN amount ELSE 0 END) as won_amount
    FROM opportunities
    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");

// Get top performing sales reps
$topSalesReps = fetchAll("
    SELECT 
        u.name,
        COUNT(DISTINCT o.id) as total_deals,
        SUM(o.amount) as total_amount,
        COUNT(DISTINCT CASE WHEN o.stage = 'closed_won' THEN o.id END) as won_deals,
        ROUND(
            (COUNT(DISTINCT CASE WHEN o.stage = 'closed_won' THEN o.id END) * 100.0) /
            NULLIF(COUNT(DISTINCT CASE WHEN o.stage IN ('closed_won', 'closed_lost') THEN o.id END), 0)
        ) as win_rate
    FROM users u
    LEFT JOIN opportunities o ON u.id = o.assigned_to
    WHERE u.role = 'sales_rep'
    GROUP BY u.id, u.name
    ORDER BY total_amount DESC
    LIMIT 5
");
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Reports & Analytics</h1>
        <div class="flex space-x-4">
            <button onclick="exportReport('pdf')" 
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </button>
            <button onclick="exportReport('csv')" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition-colors">
                <i class="fas fa-file-csv mr-2"></i>Export CSV
            </button>
        </div>
    </div>
</div>

<!-- Sales Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                <h3 class="text-2xl font-bold text-gray-900">$<?php echo number_format($salesSummary['total_amount']); ?></h3>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-dollar-sign text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Deals</p>
                <h3 class="text-2xl font-bold text-gray-900"><?php echo $salesSummary['total_opportunities']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-briefcase text-success text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Avg Deal Size</p>
                <h3 class="text-2xl font-bold text-gray-900">$<?php echo number_format($salesSummary['avg_amount']); ?></h3>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-chart-line text-warning text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Won Deals</p>
                <h3 class="text-2xl font-bold text-success"><?php echo $salesSummary['won_opportunities']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-success text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Lost Deals</p>
                <h3 class="text-2xl font-bold text-danger"><?php echo $salesSummary['lost_opportunities']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-times text-danger text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Win Rate</p>
                <h3 class="text-2xl font-bold text-primary"><?php echo $salesSummary['win_rate']; ?>%</h3>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-percentage text-primary text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Sales Trend Chart -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Sales Trend</h3>
            <select class="text-sm text-gray-500 border rounded-lg px-2 py-1" id="salesTrendPeriod">
                <option value="6">Last 6 Months</option>
                <option value="12" selected>Last 12 Months</option>
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
            <div class="text-sm text-gray-500">
                Total Value: $<?php echo number_format(array_sum(array_column($pipeline, 'total_amount'))); ?>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="pipelineChart"></canvas>
        </div>
    </div>
</div>

<!-- Additional Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Leads by Status -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Leads by Status</h3>
        <div class="chart-container">
            <canvas id="leadsChart"></canvas>
        </div>
    </div>

    <!-- Win Rate by Sales Rep -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Win Rate by Sales Rep</h3>
        <div class="chart-container">
            <canvas id="winRateChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Sales Reps Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Top Performing Sales Reps</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Rep</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Deals</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Won Deals</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Win Rate</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($topSalesReps as $rep): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-8 w-8 rounded-full" 
                                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($rep['name']); ?>&background=0061F2&color=fff" 
                                     alt="">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($rep['name']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $rep['total_deals']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $rep['won_deals']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            $<?php echo number_format($rep['total_amount']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900"><?php echo $rep['win_rate']; ?>%</span>
                                <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary rounded-full h-2" style="width: <?php echo $rep['win_rate']; ?>%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    const salesTrendChart = new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($salesTrend, 'month')); ?>,
            datasets: [{
                label: 'Total Revenue',
                data: <?php echo json_encode(array_column($salesTrend, 'total_amount')); ?>,
                borderColor: '#0061F2',
                backgroundColor: 'rgba(0, 97, 242, 0.1)',
                fill: true
            }]
        },
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
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Pipeline Chart
    const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
    const pipelineChart = new Chart(pipelineCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($pipeline, 'stage')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($pipeline, 'total_amount')); ?>,
                backgroundColor: ['#0061F2', '#00B74A', '#F59E0B', '#DC2626']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '70%'
        }
    });

    // Leads Chart
    const leadsCtx = document.getElementById('leadsChart').getContext('2d');
    const leadsChart = new Chart(leadsCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($leadsByStatus, 'status')); ?>,
            datasets: [{
                label: 'Number of Leads',
                data: <?php echo json_encode(array_column($leadsByStatus, 'count')); ?>,
                backgroundColor: '#0061F2'
            }]
        },
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
                    beginAtZero: true
                }
            }
        }
    });

    // Win Rate Chart
    const winRateCtx = document.getElementById('winRateChart').getContext('2d');
    const winRateChart = new Chart(winRateCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($topSalesReps, 'name')); ?>,
            datasets: [{
                label: 'Win Rate',
                data: <?php echo json_encode(array_column($topSalesReps, 'win_rate')); ?>,
                backgroundColor: '#00B74A'
            }]
        },
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Update charts when period changes
    document.getElementById('salesTrendPeriod').addEventListener('change', function() {
        updateSalesTrendChart(salesTrendChart, this.value);
    });
}

function updateSalesTrendChart(chart, months) {
    fetch(`api/reports/sales-trend?months=${months}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chart.data.labels = data.data.map(d => d.month);
                chart.data.datasets[0].data = data.data.map(d => d.total_amount);
                chart.update();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to update chart', 'error');
        });
}

function exportReport(format) {
    const endpoint = `api/reports/export?format=${format}`;
    
    fetch(endpoint)
        .then(response => {
            if (format === 'csv') {
                return response.blob();
            }
            return response.json();
        })
        .then(data => {
            if (format === 'csv') {
                // Download CSV file
                const url = window.URL.createObjectURL(data);
                const a = document.createElement('a');
                a.href = url;
                a.download = `sales_report_${new Date().toISOString().split('T')[0]}.csv`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                a.remove();
            } else {
                // Handle PDF response
                if (data.success) {
                    window.open(data.url, '_blank');
                } else {
                    throw new Error(data.error || 'Export failed');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Export failed', 'error');
        });
}
</script>
