<!-- Quick Actions -->
<div class="fixed bottom-8 right-8">
        <div class="relative">
            <button id="quickActionBtn" class="bg-primary text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 transition-colors pulse">
                <i class="fas fa-plus text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Quick Action Modal -->
    <div id="quickActionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 modal-animation">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    <button class="text-gray-400 hover:text-gray-600" onclick="closeQuickActionModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <a href="index.php?page=leads&action=new" class="p-4 border rounded-lg hover:bg-gray-50 transition-colors text-left">
                        <i class="fas fa-user-plus text-primary text-xl mb-2"></i>
                        <h4 class="font-medium">Add Lead</h4>
                        <p class="text-sm text-gray-500">Create a new lead</p>
                    </a>
                    <a href="index.php?page=opportunities&action=new" class="p-4 border rounded-lg hover:bg-gray-50 transition-colors text-left">
                        <i class="fas fa-briefcase text-success text-xl mb-2"></i>
                        <h4 class="font-medium">Add Opportunity</h4>
                        <p class="text-sm text-gray-500">Create new opportunity</p>
                    </a>
                    <a href="index.php?page=tasks&action=new" class="p-4 border rounded-lg hover:bg-gray-50 transition-colors text-left">
                        <i class="fas fa-tasks text-warning text-xl mb-2"></i>
                        <h4 class="font-medium">Add Task</h4>
                        <p class="text-sm text-gray-500">Create a new task</p>
                    </a>
                    <a href="index.php?page=calendar&action=new" class="p-4 border rounded-lg hover:bg-gray-50 transition-colors text-left">
                        <i class="fas fa-calendar text-danger text-xl mb-2"></i>
                        <h4 class="font-medium">Schedule Meeting</h4>
                        <p class="text-sm text-gray-500">Set up a meeting</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Messages Container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50"></div>

    <!-- Include main.js -->
    <script src="js/main.js"></script>
    <script>
        // Quick Action Modal
        const quickActionModal = document.getElementById('quickActionModal');
        const quickActionBtn = document.getElementById('quickActionBtn');

        quickActionBtn.addEventListener('click', () => {
            quickActionModal.classList.remove('hidden');
        });

        function closeQuickActionModal() {
            quickActionModal.classList.add('hidden');
        }

        // Close modal when clicking outside
        quickActionModal.addEventListener('click', (e) => {
            if (e.target === quickActionModal) {
                closeQuickActionModal();
            }
        });

        // Toast message function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `mb-4 p-4 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-y-full ${
                type === 'success' ? 'bg-success' : 
                type === 'error' ? 'bg-danger' :
                type === 'warning' ? 'bg-warning' : 'bg-primary'
            }`;
            toast.textContent = message;
            
            const container = document.getElementById('toastContainer');
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => toast.classList.remove('translate-y-full'), 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-y-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // AJAX request helper
        async function fetchData(url, options = {}) {
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message, 'error');
                throw error;
            }
        }
    </script>
</body>
</html>
