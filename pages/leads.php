<?php
// Get leads with related data
$leads = fetchAll("
    SELECT 
        l.*,
        u.name as assigned_to_name,
        (SELECT COUNT(*) FROM opportunities WHERE lead_id = l.id) as opportunity_count,
        (SELECT COUNT(*) FROM tasks WHERE related_to_type = 'lead' AND related_to_id = l.id) as task_count
    FROM leads l
    LEFT JOIN users u ON l.assigned_to = u.id
    ORDER BY l.created_at DESC
");

// Get lead statuses for filter
$statuses = ['new', 'contacted', 'qualified', 'lost'];
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Leads</h1>
        <button onclick="openNewLeadModal()" 
                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Lead
        </button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-lg shadow-sm mb-6">
    <div class="flex flex-wrap gap-4">
        <!-- Search -->
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <input type="text" 
                       id="searchLeads" 
                       class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" 
                       placeholder="Search leads...">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="w-48">
            <select id="statusFilter" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo $status; ?>"><?php echo ucfirst($status); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Assigned To Filter -->
        <div class="w-48">
            <select id="assignedToFilter" 
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

<!-- Leads Table -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Company
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Contact
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Owner
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Related
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Created
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="leadsTableBody">
                <?php foreach ($leads as $lead): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($lead['company_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($lead['contact_name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($lead['email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="status-badge status-<?php echo $lead['status']; ?>">
                                <?php echo ucfirst($lead['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-8 w-8 rounded-full" 
                                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($lead['assigned_to_name']); ?>&background=0061F2&color=fff" 
                                     alt="<?php echo htmlspecialchars($lead['assigned_to_name']); ?>">
                                <div class="ml-2 text-sm text-gray-900">
                                    <?php echo htmlspecialchars($lead['assigned_to_name']); ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <?php if ($lead['opportunity_count'] > 0): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $lead['opportunity_count']; ?> opportunities
                                    </span>
                                <?php endif; ?>
                                <?php if ($lead['task_count'] > 0): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <?php echo $lead['task_count']; ?> tasks
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($lead['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="editLead(<?php echo $lead['id']; ?>)" 
                                    class="text-primary hover:text-blue-700 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteLead(<?php echo $lead['id']; ?>)" 
                                    class="text-danger hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New/Edit Lead Modal -->
<div id="leadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <form id="leadForm" class="p-6">
            <input type="hidden" id="leadId" name="id">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">New Lead</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeleadModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="companyName" class="block text-sm font-medium text-gray-700">Company Name</label>
                    <input type="text" id="companyName" name="company_name" required
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="contactName" class="block text-sm font-medium text-gray-700">Contact Name</label>
                    <input type="text" id="contactName" name="contact_name" required
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email"
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" id="phone" name="phone"
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" required
                            class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>"><?php echo ucfirst($status); ?></option>
                        <?php endforeach; ?>
                    </select>
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

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
                        onclick="closeleadModal()">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                    Save Lead
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
    initializeLeadForm();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchLeads');
    const statusFilter = document.getElementById('statusFilter');
    const assignedToFilter = document.getElementById('assignedToFilter');
    
    // Add event listeners for filters
    [searchInput, statusFilter, assignedToFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    searchInput.addEventListener('keyup', applyFilters);
}

function applyFilters() {
    const search = document.getElementById('searchLeads').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const assignedTo = document.getElementById('assignedToFilter').value;
    
    const rows = document.getElementById('leadsTableBody').getElementsByTagName('tr');
    
    Array.from(rows).forEach(row => {
        const companyName = row.cells[0].textContent.toLowerCase();
        const contactInfo = row.cells[1].textContent.toLowerCase();
        const rowStatus = row.cells[2].textContent.toLowerCase();
        const rowAssignedTo = row.cells[3].querySelector('img').alt;
        
        const matchesSearch = companyName.includes(search) || contactInfo.includes(search);
        const matchesStatus = !status || rowStatus.includes(status.toLowerCase());
        const matchesAssignedTo = !assignedTo || rowAssignedTo === assignedTo;
        
        row.style.display = matchesSearch && matchesStatus && matchesAssignedTo ? '' : 'none';
    });
}

function openNewLeadModal() {
    document.getElementById('modalTitle').textContent = 'New Lead';
    document.getElementById('leadForm').reset();
    document.getElementById('leadId').value = '';
    document.getElementById('leadModal').classList.remove('hidden');
}

function editLead(id) {
    document.getElementById('modalTitle').textContent = 'Edit Lead';
    document.getElementById('leadId').value = id;
    
    // Fetch lead data
    fetch(`api/leads/get/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const lead = data.data;
                Object.keys(lead).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.value = lead[key];
                    }
                });
                document.getElementById('leadModal').classList.remove('hidden');
            } else {
                showToast('Failed to load lead data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load lead data', 'error');
        });
}

function closeleadModal() {
    document.getElementById('leadModal').classList.add('hidden');
}

function initializeLeadForm() {
    document.getElementById('leadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = formData.get('id');
        const method = id ? 'PUT' : 'POST';
        const endpoint = id ? `api/leads/update/${id}` : 'api/leads/create';
        
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
                showToast(id ? 'Lead updated successfully' : 'Lead created successfully', 'success');
                closeleadModal();
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

function deleteLead(id) {
    if (confirm('Are you sure you want to delete this lead?')) {
        fetch(`api/leads/delete/${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Lead deleted successfully', 'success');
                // Remove the row from the table
                document.querySelector(`tr[data-id="${id}"]`).remove();
            } else {
                throw new Error(data.error || 'Delete failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message, 'error');
        });
    }
}
</script>
