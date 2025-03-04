<?php
// Get user permissions
$userRole = $_SESSION['user_role'] ?? 'user';

// Define available actions based on user role
$actions = [
    'lead' => [
        'icon' => 'fas fa-user-plus',
        'color' => 'text-primary',
        'bg' => 'bg-blue-100',
        'title' => 'Add Lead',
        'description' => 'Create a new lead',
        'url' => 'index.php?page=leads&action=new'
    ],
    'opportunity' => [
        'icon' => 'fas fa-briefcase',
        'color' => 'text-success',
        'bg' => 'bg-green-100',
        'title' => 'Add Opportunity',
        'description' => 'Create new opportunity',
        'url' => 'index.php?page=opportunities&action=new'
    ],
    'task' => [
        'icon' => 'fas fa-tasks',
        'color' => 'text-warning',
        'bg' => 'bg-yellow-100',
        'title' => 'Add Task',
        'description' => 'Create a new task',
        'url' => 'index.php?page=tasks&action=new'
    ],
    'meeting' => [
        'icon' => 'fas fa-calendar',
        'color' => 'text-danger',
        'bg' => 'bg-red-100',
        'title' => 'Schedule Meeting',
        'description' => 'Set up a meeting',
        'url' => 'index.php?page=calendar&action=new'
    ]
];

// Filter actions based on user role
if ($userRole !== 'admin') {
    // Remove certain actions for non-admin users if needed
    // unset($actions['some_restricted_action']);
}
?>

<!-- Quick Action Button -->
<div class="fixed bottom-8 right-8">
    <div class="relative">
        <button id="quickActionBtn" 
                class="bg-primary text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 transition-colors pulse">
            <i class="fas fa-plus text-xl"></i>
        </button>
    </div>
</div>

<!-- Quick Action Modal -->
<div id="quickActionModal" 
     class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 modal-animation"
         @click.away="closeQuickActionModal()">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modal-title" class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                <button type="button" 
                        class="text-gray-400 hover:text-gray-600" 
                        onclick="closeQuickActionModal()"
                        aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($actions as $key => $action): ?>
                <a href="<?php echo $action['url']; ?>" 
                   class="p-4 border rounded-lg hover:bg-gray-50 transition-colors text-left group">
                    <i class="<?php echo $action['icon'] . ' ' . $action['color']; ?> text-xl mb-2 group-hover:scale-110 transform transition-transform"></i>
                    <h4 class="font-medium"><?php echo $action['title']; ?></h4>
                    <p class="text-sm text-gray-500"><?php echo $action['description']; ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Action Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const quickActionBtn = document.getElementById('quickActionBtn');
    const quickActionModal = document.getElementById('quickActionModal');

    if (quickActionBtn && quickActionModal) {
        // Open modal
        quickActionBtn.addEventListener('click', () => {
            quickActionModal.classList.remove('hidden');
            // Add animation class
            const modalContent = quickActionModal.querySelector('.modal-animation');
            modalContent.classList.add('animate-modal-in');
        });

        // Close modal when clicking outside
        quickActionModal.addEventListener('click', (e) => {
            if (e.target === quickActionModal) {
                closeQuickActionModal();
            }
        });

        // Close modal when pressing Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !quickActionModal.classList.contains('hidden')) {
                closeQuickActionModal();
            }
        });
    }
});

function closeQuickActionModal() {
    const modal = document.getElementById('quickActionModal');
    const modalContent = modal.querySelector('.modal-animation');
    
    // Add closing animation
    modalContent.classList.add('animate-modal-out');
    
    // Wait for animation to complete before hiding
    setTimeout(() => {
        modal.classList.add('hidden');
        modalContent.classList.remove('animate-modal-out', 'animate-modal-in');
    }, 200);
}

// Function to open quick action with specific type
function openQuickAction(type) {
    const modal = document.getElementById('quickActionModal');
    if (modal) {
        modal.classList.remove('hidden');
        // Optionally pre-select or highlight the specified action type
        const actionButton = modal.querySelector(`[data-action="${type}"]`);
        if (actionButton) {
            actionButton.focus();
        }
    }
}
</script>

<style>
/* Modal Animations */
@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes modalOut {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.95);
    }
}

.animate-modal-in {
    animation: modalIn 0.2s ease-out;
}

.animate-modal-out {
    animation: modalOut 0.2s ease-in;
}

/* Pulse Animation for Quick Action Button */
.pulse::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background-color: currentColor;
    opacity: 0.3;
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.3;
        transform: scale(1);
    }
    50% {
        opacity: 0;
        transform: scale(1.2);
    }
}
</style>
