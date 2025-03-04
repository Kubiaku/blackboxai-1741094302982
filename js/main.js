// API Helper Functions
const API = {
    async fetch(endpoint, options = {}) {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`/sales-app/api/${endpoint}`, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Error:', error);
            showToast(error.message, 'error');
            throw error;
        }
    },

    get(endpoint) {
        return this.fetch(endpoint);
    },

    post(endpoint, data) {
        return this.fetch(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    put(endpoint, data) {
        return this.fetch(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    delete(endpoint) {
        return this.fetch(endpoint, {
            method: 'DELETE'
        });
    }
};

// UI Helper Functions
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

function showLoadingSpinner(container) {
    container.innerHTML = `
        <div class="flex items-center justify-center p-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
        </div>
    `;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
}

// Form Helper Functions
function serializeForm(form) {
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    return data;
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('border-danger');
            
            const errorMessage = field.dataset.error || 'This field is required';
            let errorDiv = field.nextElementSibling;
            
            if (!errorDiv || !errorDiv.classList.contains('error-message')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-danger text-sm mt-1';
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
            
            errorDiv.textContent = errorMessage;
        } else {
            field.classList.remove('border-danger');
            const errorDiv = field.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.remove();
            }
        }
    });
    
    return isValid;
}

// Chart Helper Functions
function createChart(ctx, config) {
    if (window.chart) {
        window.chart.destroy();
    }
    window.chart = new Chart(ctx, config);
}

// Initialize page-specific functionality
document.addEventListener('DOMContentLoaded', () => {
    const currentPage = document.body.dataset.page;
    
    // Initialize page-specific functions
    switch (currentPage) {
        case 'dashboard':
            initializeDashboard();
            break;
        case 'leads':
            initializeLeads();
            break;
        case 'opportunities':
            initializeOpportunities();
            break;
        case 'contacts':
            initializeContacts();
            break;
        case 'tasks':
            initializeTasks();
            break;
        case 'reports':
            initializeReports();
            break;
    }
    
    // Initialize global components
    initializeSidebar();
    initializeQuickActions();
    initializeNotifications();
});

// Sidebar functionality
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar-transition');
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
}

// Quick Actions functionality
function initializeQuickActions() {
    const quickActionBtn = document.getElementById('quickActionBtn');
    const quickActionModal = document.getElementById('quickActionModal');
    
    if (quickActionBtn && quickActionModal) {
        quickActionBtn.addEventListener('click', () => {
            quickActionModal.classList.remove('hidden');
        });
        
        quickActionModal.addEventListener('click', (e) => {
            if (e.target === quickActionModal) {
                quickActionModal.classList.add('hidden');
            }
        });
    }
}

// Notifications functionality
function initializeNotifications() {
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', async () => {
            try {
                const response = await API.get('notifications/list');
                // Handle notifications display
            } catch (error) {
                console.error('Failed to fetch notifications:', error);
            }
        });
    }
}

// Export functions for use in other scripts
window.SalesHub = {
    API,
    showToast,
    showLoadingSpinner,
    formatCurrency,
    formatDate,
    openModal,
    closeModal,
    serializeForm,
    validateForm,
    createChart
};
