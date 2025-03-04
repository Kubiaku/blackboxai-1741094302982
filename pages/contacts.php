<?php
// Get contacts with related data
$contacts = fetchAll("
    SELECT 
        c.*,
        l.company_name as lead_company,
        u.name as created_by_name,
        (SELECT COUNT(*) FROM tasks WHERE related_to_type = 'contact' AND related_to_id = c.id) as task_count
    FROM contacts c
    LEFT JOIN leads l ON c.lead_id = l.id
    LEFT JOIN users u ON c.created_by = u.id
    ORDER BY c.last_name, c.first_name
");

// Get companies for filter
$companies = fetchAll("SELECT DISTINCT company FROM contacts WHERE company IS NOT NULL ORDER BY company");
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Contacts</h1>
        <button onclick="openNewContactModal()" 
                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Contact
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
                       id="searchContacts" 
                       class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" 
                       placeholder="Search contacts...">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- Company Filter -->
        <div class="w-48">
            <select id="companyFilter" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Companies</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?php echo htmlspecialchars($company['company']); ?>">
                        <?php echo htmlspecialchars($company['company']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- View Toggle -->
        <div class="flex rounded-lg border">
            <button id="gridViewBtn" 
                    class="px-4 py-2 rounded-l-lg bg-primary text-white hover:bg-blue-600 transition-colors">
                <i class="fas fa-th-large"></i>
            </button>
            <button id="listViewBtn" 
                    class="px-4 py-2 rounded-r-lg bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>
</div>

<!-- Contacts Grid View -->
<div id="gridView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php foreach ($contacts as $contact): ?>
        <div class="bg-white rounded-lg shadow-sm overflow-hidden contact-card"
             data-company="<?php echo htmlspecialchars($contact['company']); ?>">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <img class="h-16 w-16 rounded-full" 
                         src="https://ui-avatars.com/api/?name=<?php echo urlencode($contact['first_name'] . ' ' . $contact['last_name']); ?>&background=0061F2&color=fff" 
                         alt="<?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?>">
                    <div class="flex space-x-2">
                        <button onclick="editContact(<?php echo $contact['id']; ?>)"
                                class="text-gray-400 hover:text-primary">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteContact(<?php echo $contact['id']; ?>)"
                                class="text-gray-400 hover:text-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900">
                    <?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?>
                </h3>
                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($contact['job_title']); ?></p>
                
                <div class="mt-4 space-y-2">
                    <?php if ($contact['company']): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-building text-gray-400 w-5"></i>
                            <span class="text-gray-600"><?php echo htmlspecialchars($contact['company']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['email']): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-envelope text-gray-400 w-5"></i>
                            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" 
                               class="text-primary hover:text-blue-600">
                                <?php echo htmlspecialchars($contact['email']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['phone']): ?>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-phone text-gray-400 w-5"></i>
                            <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" 
                               class="text-gray-600 hover:text-gray-900">
                                <?php echo htmlspecialchars($contact['phone']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($contact['task_count'] > 0): ?>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <?php echo $contact['task_count']; ?> tasks
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Contacts List View (Hidden by default) -->
<div id="listView" class="hidden bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($contacts as $contact): ?>
                <tr class="contact-row" data-company="<?php echo htmlspecialchars($contact['company']); ?>">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="h-10 w-10 rounded-full" 
                                 src="https://ui-avatars.com/api/?name=<?php echo urlencode($contact['first_name'] . ' ' . $contact['last_name']); ?>&background=0061F2&color=fff" 
                                 alt="">
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($contact['job_title']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($contact['company']); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($contact['email']); ?></div>
                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($contact['phone']); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($contact['task_count'] > 0): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <?php echo $contact['task_count']; ?> tasks
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="editContact(<?php echo $contact['id']; ?>)" 
                                class="text-primary hover:text-blue-700 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteContact(<?php echo $contact['id']; ?>)" 
                                class="text-danger hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- New/Edit Contact Modal -->
<div id="contactModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <form id="contactForm" class="p-6">
            <input type="hidden" id="contactId" name="id">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">New Contact</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeContactModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="firstName" name="first_name" required
                               class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="lastName" name="last_name" required
                               class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
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
                    <label for="jobTitle" class="block text-sm font-medium text-gray-700">Job Title</label>
                    <input type="text" id="jobTitle" name="job_title"
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
                    <input type="text" id="company" name="company"
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="leadId" class="block text-sm font-medium text-gray-700">Associated Lead</label>
                    <select id="leadId" name="lead_id"
                            class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">None</option>
                        <?php
                        $leads = fetchAll("SELECT id, company_name FROM leads ORDER BY company_name");
                        foreach ($leads as $lead):
                        ?>
                            <option value="<?php echo $lead['id']; ?>"><?php echo htmlspecialchars($lead['company_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
                        onclick="closeContactModal()">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                    Save Contact
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters and view toggles
    initializeFilters();
    initializeViewToggles();
    
    // Initialize form submission
    initializeContactForm();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchContacts');
    const companyFilter = document.getElementById('companyFilter');
    
    // Add event listeners for filters
    [searchInput, companyFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    searchInput.addEventListener('keyup', applyFilters);
}

function applyFilters() {
    const search = document.getElementById('searchContacts').value.toLowerCase();
    const company = document.getElementById('companyFilter').value;
    
    // Filter grid view
    document.querySelectorAll('.contact-card').forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const cardCompany = card.dataset.company;
        
        const matchesSearch = name.includes(search);
        const matchesCompany = !company || cardCompany === company;
        
        card.style.display = matchesSearch && matchesCompany ? '' : 'none';
    });
    
    // Filter list view
    document.querySelectorAll('.contact-row').forEach(row => {
        const name = row.querySelector('.text-gray-900').textContent.toLowerCase();
        const rowCompany = row.dataset.company;
        
        const matchesSearch = name.includes(search);
        const matchesCompany = !company || rowCompany === company;
        
        row.style.display = matchesSearch && matchesCompany ? '' : 'none';
    });
}

function initializeViewToggles() {
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    
    gridViewBtn.addEventListener('click', () => {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        gridViewBtn.classList.add('bg-primary', 'text-white');
        gridViewBtn.classList.remove('bg-white', 'text-gray-700');
        listViewBtn.classList.add('bg-white', 'text-gray-700');
        listViewBtn.classList.remove('bg-primary', 'text-white');
    });
    
    listViewBtn.addEventListener('click', () => {
        gridView.classList.add('hidden');
        listView.classList.remove('hidden');
        listViewBtn.classList.add('bg-primary', 'text-white');
        listViewBtn.classList.remove('bg-white', 'text-gray-700');
        gridViewBtn.classList.add('bg-white', 'text-gray-700');
        gridViewBtn.classList.remove('bg-primary', 'text-white');
    });
}

function openNewContactModal() {
    document.getElementById('modalTitle').textContent = 'New Contact';
    document.getElementById('contactForm').reset();
    document.getElementById('contactId').value = '';
    document.getElementById('contactModal').classList.remove('hidden');
}

function editContact(id) {
    document.getElementById('modalTitle').textContent = 'Edit Contact';
    document.getElementById('contactId').value = id;
    
    // Fetch contact data
    fetch(`api/contacts/get/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.data;
                Object.keys(contact).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.value = contact[key];
                    }
                });
                document.getElementById('contactModal').classList.remove('hidden');
            } else {
                showToast('Failed to load contact data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load contact data', 'error');
        });
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
}

function initializeContactForm() {
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = formData.get('id');
        const method = id ? 'PUT' : 'POST';
        const endpoint = id ? `api/contacts/update/${id}` : 'api/contacts/create';
        
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
                showToast(id ? 'Contact updated successfully' : 'Contact created successfully', 'success');
                closeContactModal();
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

function deleteContact(id) {
    if (confirm('Are you sure you want to delete this contact?')) {
        fetch(`api/contacts/delete/${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Contact deleted successfully', 'success');
                // Remove the contact from both views
                document.querySelector(`.contact-card[data-id="${id}"]`)?.remove();
                document.querySelector(`.contact-row[data-id="${id}"]`)?.remove();
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
