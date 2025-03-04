<?php
// Get opportunities with related data
$opportunities = fetchAll("
    SELECT 
        o.*,
        l.company_name,
        u.name as assigned_to_name,
        (SELECT COUNT(*) FROM tasks WHERE related_to_type = 'opportunity' AND related_to_id = o.id) as task_count
    FROM opportunities o
    LEFT JOIN leads l ON o.lead_id = l.id
    LEFT JOIN users u ON o.assigned_to = u.id
    ORDER BY o.expected_close_date ASC
");

// Get pipeline stages for filter
$stages = ['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost'];

// Calculate pipeline totals
$pipelineTotals = [
    'total_value' => array_sum(array_column($opportunities, 'amount')),
    'total_deals' => count($opportunities),
    'avg_deal_size' => count($opportunities) > 0 ? array_sum(array_column($opportunities, 'amount')) / count($opportunities) : 0
];
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Opportunities</h1>
        <button onclick="openNewOpportunityModal()" 
                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Opportunity
        </button>
    </div>
</div>

<!-- Pipeline Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Pipeline Value</p>
                <h3 class="text-2xl font-bold text-gray-900">$<?php echo number_format($pipelineTotals['total_value']); ?></h3>
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
                <h3 class="text-2xl font-bold text-gray-900"><?php echo $pipelineTotals['total_deals']; ?></h3>
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
                <h3 class="text-2xl font-bold text-gray-900">$<?php echo number_format($pipelineTotals['avg_deal_size']); ?></h3>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-chart-line text-warning text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-lg shadow-sm mb-6">
    <div class="flex flex-wrap gap-4">
        <!-- Search -->
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <input type="text" 
                       id="searchOpportunities" 
                       class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" 
                       placeholder="Search opportunities...">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- Stage Filter -->
        <div class="w-48">
            <select id="stageFilter" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Stages</option>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?php echo $stage; ?>"><?php echo ucfirst(str_replace('_', ' ', $stage)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Owner Filter -->
        <div class="w-48">
            <select id="ownerFilter" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Owners</option>
                <?php
                $users = fetchAll("SELECT id, name FROM users ORDER BY name");
                foreach ($users as $user):
                ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- Pipeline View -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="grid grid-cols-6 gap-4">
        <?php
        $stageColors = [
            'prospecting' => 'bg-gray-100',
            'qualification' => 'bg-blue-100',
            'proposal' => 'bg-yellow-100',
            'negotiation' => 'bg-purple-100',
            'closed_won' => 'bg-green-100',
            'closed_lost' => 'bg-red-100'
        ];
        
        foreach ($stages as $stage):
            $stageOpportunities = array_filter($opportunities, function($opp) use ($stage) {
                return $opp['stage'] === $stage;
            });
            $stageValue = array_sum(array_column($stageOpportunities, 'amount'));
        ?>
            <div class="<?php echo $stageColors[$stage]; ?> p-4 rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-900"><?php echo ucfirst(str_replace('_', ' ', $stage)); ?></h3>
                    <span class="text-sm text-gray-500"><?php echo count($stageOpportunities); ?></span>
                </div>
                <p class="text-sm text-gray-500 mb-4">$<?php echo number_format($stageValue); ?></p>
                
                <div class="space-y-2">
                    <?php foreach ($stageOpportunities as $opp): ?>
                        <div class="bg-white p-3 rounded shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                             onclick="editOpportunity(<?php echo $opp['id']; ?>)">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($opp['name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($opp['company_name']); ?></div>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-sm font-medium text-primary">$<?php echo number_format($opp['amount']); ?></span>
                                <span class="text-xs text-gray-500"><?php echo date('M j', strtotime($opp['expected_close_date'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- New/Edit Opportunity Modal -->
<div id="opportunityModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <form id="opportunityForm" class="p-6">
            <input type="hidden" id="opportunityId" name="id">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">New Opportunity</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeOpportunityModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Opportunity Name</label>
                    <input type="text" id="name" name="name" required
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="leadId" class="block text-sm font-medium text-gray-700">Associated Lead</label>
                    <select id="leadId" name="lead_id" required
                            class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select Lead</option>
                        <?php
                        $leads = fetchAll("SELECT id, company_name FROM leads ORDER BY company_name");
                        foreach ($leads as $lead):
                        ?>
                            <option value="<?php echo $lead['id']; ?>"><?php echo htmlspecialchars($lead['company_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                    <div class="mt-1 relative rounded-lg">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">$</span>
                        </div>
                        <input type="number" id="amount" name="amount" required min="0" step="0.01"
                               class="block w-full pl-7 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label for="stage" class="block text-sm font-medium text-gray-700">Stage</label>
                    <select id="stage" name="stage" required
                            class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <?php foreach ($stages as $stage): ?>
                            <option value="<?php echo $stage; ?>"><?php echo ucfirst(str_replace('_', ' ', $stage)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="probability" class="block text-sm font-medium text-gray-700">Probability (%)</label>
                    <input type="number" id="probability" name="probability" required min="0" max="100"
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="expectedCloseDate" class="block text-sm font-medium text-gray-700">Expected Close Date</label>
                    <input type="date" id="expectedCloseDate" name="expected_close_date" required
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="assignedTo" class="block text-sm font-medium text-gray-700">Assigned To</label>
                    <select id="assignedTo" name="assigned_to" required
                            class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
                        onclick="closeOpportunityModal()">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                    Save Opportunity
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    initializeFilters();
    
    // Initialize form submission
    initializeOpportunityForm();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchOpportunities');
    const stageFilter = document.getElementById('stageFilter');
    const ownerFilter = document.getElementById('ownerFilter');
    
    // Add event listeners for filters
    [searchInput, stageFilter, ownerFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    searchInput.addEventListener('keyup', applyFilters);
}

function applyFilters() {
    const search = document.getElementById('searchOpportunities').value.toLowerCase();
    const stage = document.getElementById('stageFilter').value;
    const owner = document.getElementById('ownerFilter').value;
    
    document.querySelectorAll('.pipeline-card').forEach(card => {
        const name = card.querySelector('.opportunity-name').textContent.toLowerCase();
        const company = card.querySelector('.company-name').textContent.toLowerCase();
        const cardStage = card.dataset.stage;
        const cardOwner = card.dataset.owner;
        
        const matchesSearch = name.includes(search) || company.includes(search);
        const matchesStage = !stage || cardStage === stage;
        const matchesOwner = !owner || cardOwner === owner;
        
        card.style.display = matchesSearch && matchesStage && matchesOwner ? '' : 'none';
    });
}

function openNewOpportunityModal() {
    document.getElementById('modalTitle').textContent = 'New Opportunity';
    document.getElementById('opportunityForm').reset();
    document.getElementById('opportunityId').value = '';
    document.getElementById('opportunityModal').classList.remove('hidden');
}

function editOpportunity(id) {
    document.getElementById('modalTitle').textContent = 'Edit Opportunity';
    document.getElementById('opportunityId').value = id;
    
    // Fetch opportunity data
    fetch(`api/opportunities/get/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const opportunity = data.data;
                Object.keys(opportunity).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.value = opportunity[key];
                    }
                });
                document.getElementById('opportunityModal').classList.remove('hidden');
            } else {
                showToast('Failed to load opportunity data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load opportunity data', 'error');
        });
}

function closeOpportunityModal() {
    document.getElementById('opportunityModal').classList.add('hidden');
}

function initializeOpportunityForm() {
    document.getElementById('opportunityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = formData.get('id');
        const method = id ? 'PUT' : 'POST';
        const endpoint = id ? `api/opportunities/update/${id}` : 'api/opportunities/create';
        
        fetch(endpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(id ? 'Opportunity updated successfully' : 'Opportunity created successfully', 'success');
                closeOpportunityModal();
                // Reload the page to show updated data
                window.location.reload();
            } else {
                throw new Error(data.error || 'Operation failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message, 'error');
        });
    });
}

// Helper function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Helper function to format dates
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
}
</script>
