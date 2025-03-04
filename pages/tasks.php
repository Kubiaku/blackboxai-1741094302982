<?php
// Get tasks with related data
$tasks = fetchAll("
    SELECT 
        t.*,
        u.name as assigned_to_name,
        CASE 
            WHEN t.related_to_type = 'lead' THEN (SELECT company_name FROM leads WHERE id = t.related_to_id)
            WHEN t.related_to_type = 'opportunity' THEN (SELECT name FROM opportunities WHERE id = t.related_to_id)
            WHEN t.related_to_type = 'contact' THEN (
                SELECT CONCAT(first_name, ' ', last_name) 
                FROM contacts 
                WHERE id = t.related_to_id
            )
        END as related_to_name
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.id
    ORDER BY 
        CASE 
            WHEN t.status = 'pending' AND t.due_date < CURRENT_DATE THEN 1
            WHEN t.status = 'pending' AND t.due_date = CURRENT_DATE THEN 2
            WHEN t.status = 'pending' THEN 3
            WHEN t.status = 'in_progress' THEN 4
            WHEN t.status = 'completed' THEN 5
            ELSE 6
        END,
        t.due_date ASC
");

// Group tasks by status
$tasksByStatus = [
    'pending' => array_filter($tasks, fn($t) => $t['status'] === 'pending'),
    'in_progress' => array_filter($tasks, fn($t) => $t['status'] === 'in_progress'),
    'completed' => array_filter($tasks, fn($t) => $t['status'] === 'completed')
];

// Calculate statistics
$stats = [
    'total' => count($tasks),
    'pending' => count($tasksByStatus['pending']),
    'in_progress' => count($tasksByStatus['in_progress']),
    'completed' => count($tasksByStatus['completed']),
    'overdue' => count(array_filter($tasks, fn($t) => 
        $t['status'] === 'pending' && strtotime($t['due_date']) < strtotime('today')
    ))
];
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Tasks</h1>
        <button onclick="openNewTaskModal()" 
                class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Task
        </button>
    </div>
</div>

<!-- Task Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                <h3 class="text-2xl font-bold text-gray-900"><?php echo $stats['total']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-tasks text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Pending</p>
                <h3 class="text-2xl font-bold text-gray-900"><?php echo $stats['pending']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-warning text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">In Progress</p>
                <h3 class="text-2xl font-bold text-gray-900"><?php echo $stats['in_progress']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-spinner text-purple-500 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Completed</p>
                <h3 class="text-2xl font-bold text-gray-900"><?php echo $stats['completed']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-success text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Overdue</p>
                <h3 class="text-2xl font-bold text-danger"><?php echo $stats['overdue']; ?></h3>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-danger text-xl"></i>
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
                       id="searchTasks" 
                       class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" 
                       placeholder="Search tasks...">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="w-48">
            <select id="statusFilter" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <!-- Priority Filter -->
        <div class="w-48">
            <select id="priorityFilter" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>

        <!-- Assigned To Filter -->
        <div class="w-48">
            <select id="assignedToFilter" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="">All Assignees</option>
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

<!-- Tasks Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Pending Tasks -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Pending</h3>
        </div>
        <div class="p-4 space-y-4">
            <?php foreach ($tasksByStatus['pending'] as $task): ?>
                <div class="bg-white border rounded-lg p-4 task-card hover:shadow-md transition-shadow"
                     data-id="<?php echo $task['id']; ?>"
                     data-status="pending"
                     data-priority="<?php echo $task['priority']; ?>"
                     data-assigned="<?php echo $task['assigned_to']; ?>">
                    <div class="flex items-center justify-between mb-2">
                        <span class="status-badge status-<?php echo $task['priority']; ?>">
                            <?php echo ucfirst($task['priority']); ?>
                        </span>
                        <div class="flex items-center space-x-2">
                            <button onclick="editTask(<?php echo $task['id']; ?>)"
                                    class="text-gray-400 hover:text-primary">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTask(<?php echo $task['id']; ?>)"
                                    class="text-gray-400 hover:text-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <h4 class="font-medium text-gray-900 mb-2"><?php echo htmlspecialchars($task['title']); ?></h4>
                    
                    <?php if ($task['description']): ?>
                        <p class="text-sm text-gray-500 mb-4"><?php echo htmlspecialchars($task['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <img class="h-6 w-6 rounded-full" 
                                 src="https://ui-avatars.com/api/?name=<?php echo urlencode($task['assigned_to_name']); ?>&background=0061F2&color=fff" 
                                 alt="">
                            <span class="ml-2 text-gray-600"><?php echo htmlspecialchars($task['assigned_to_name']); ?></span>
                        </div>
                        <span class="text-gray-500">
                            <?php 
                            $dueDate = new DateTime($task['due_date']);
                            $today = new DateTime('today');
                            $interval = $today->diff($dueDate);
                            
                            if ($dueDate < $today) {
                                echo '<span class="text-danger">Overdue by ' . $interval->days . ' days</span>';
                            } elseif ($dueDate == $today) {
                                echo '<span class="text-warning">Due today</span>';
                            } else {
                                echo 'Due in ' . $interval->days . ' days';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <?php if ($task['related_to_type']): ?>
                        <div class="mt-4 text-sm">
                            <span class="text-gray-500">
                                Related to: 
                                <a href="index.php?page=<?php echo $task['related_to_type']; ?>s&id=<?php echo $task['related_to_id']; ?>" 
                                   class="text-primary hover:text-blue-600">
                                    <?php echo htmlspecialchars($task['related_to_name']); ?>
                                </a>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- In Progress Tasks -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">In Progress</h3>
        </div>
        <div class="p-4 space-y-4">
            <?php foreach ($tasksByStatus['in_progress'] as $task): ?>
                <!-- Similar task card structure as above -->
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Completed Tasks -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Completed</h3>
        </div>
        <div class="p-4 space-y-4">
            <?php foreach ($tasksByStatus['completed'] as $task): ?>
                <!-- Similar task card structure as above -->
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- New/Edit Task Modal -->
<div id="taskModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <form id="taskForm" class="p-6">
            <input type="hidden" id="taskId" name="id">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">New Task</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeTaskModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" name="title" required
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>

                <div>
                    <label for="dueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" id="dueDate" name="due_date" required
                           class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select id="priority" name="priority" required
                            class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" required
                            class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
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
                    <label for="relatedToType" class="block text-sm font-medium text-gray-700">Related To</label>
                    <div class="grid grid-cols-2 gap-4">
                        <select id="relatedToType" name="related_to_type"
                                class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">None</option>
                            <option value="lead">Lead</option>
                            <option value="opportunity">Opportunity</option>
                            <option value="contact">Contact</option>
                        </select>
                        <select id="relatedToId" name="related_to_id"
                                class="mt-1 block w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
                        onclick="closeTaskModal()">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                    Save Task
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
    initializeTaskForm();
    
    // Initialize related to type change handler
    initializeRelatedToHandler();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchTasks');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const assignedToFilter = document.getElementById('assignedToFilter');
    
    // Add event listeners for filters
    [searchInput, statusFilter, priorityFilter, assignedToFilter].forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    searchInput.addEventListener('keyup', applyFilters);
}

function applyFilters() {
    const search = document.getElementById('searchTasks').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    const assignedTo = document.getElementById('assignedToFilter').value;
    
    document.querySelectorAll('.task-card').forEach(card => {
        const title = card.querySelector('h4').textContent.toLowerCase();
        const cardStatus = card.dataset.status;
        const cardPriority = card.dataset.priority;
        const cardAssignedTo = card.dataset.assigned;
        
        const matchesSearch = title.includes(search);
        const matchesStatus = !status || cardStatus === status;
        const matchesPriority = !priority || cardPriority === priority;
        const matchesAssignedTo = !assignedTo || cardAssignedTo === assignedTo;
        
        card.style.display = matchesSearch && matchesStatus && matchesPriority && matchesAssignedTo ? '' : 'none';
    });
}

function openNewTaskModal() {
    document.getElementById('modalTitle').textContent = 'New Task';
    document.getElementById('taskForm').reset();
    document.getElementById('taskId').value = '';
    document.getElementById('taskModal').classList.remove('hidden');
}

function editTask(id) {
    document.getElementById('modalTitle').textContent = 'Edit Task';
    document.getElementById('taskId').value = id;
    
    // Fetch task data
    fetch(`api/tasks/get/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const task = data.data;
                Object.keys(task).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.value = task[key];
                    }
                });
                // Update related to options
                if (task.related_to_type) {
                    updateRelatedToOptions(task.related_to_type, task.related_to_id);
                }
                document.getElementById('taskModal').classList.remove('hidden');
            } else {
                showToast('Failed to load task data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load task data', 'error');
        });
}

function closeTaskModal() {
    document.getElementById('taskModal').classList.add('hidden');
}

function initializeTaskForm() {
    document.getElementById('taskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = formData.get('id');
        const method = id ? 'PUT' : 'POST';
        const endpoint = id ? `api/tasks/update/${id}` : 'api/tasks/create';
        
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
                showToast(id ? 'Task updated successfully' : 'Task created successfully', 'success');
                closeTaskModal();
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

function initializeRelatedToHandler() {
    const typeSelect = document.getElementById('relatedToType');
    typeSelect.addEventListener('change', function() {
        updateRelatedToOptions(this.value);
    });
}

function updateRelatedToOptions(type, selectedId = null) {
    const idSelect = document.getElementById('relatedToId');
    idSelect.innerHTML = '<option value="">Select...</option>';
    
    if (!type) return;
    
    // Fetch related items based on type
    fetch(`api/${type}s/list`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = type === 'lead' ? item.company_name :
                                       type === 'opportunity' ? item.name :
                                       `${item.first_name} ${item.last_name}`;
                    if (selectedId && item.id === selectedId) {
                        option.selected = true;
                    }
                    idSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load related items', 'error');
        });
}

function deleteTask(id) {
    if (confirm('Are you sure you want to delete this task?')) {
        fetch(`api/tasks/delete/${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Task deleted successfully', 'success');
                // Remove the task card
                document.querySelector(`.task-card[data-id="${id}"]`).remove();
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
