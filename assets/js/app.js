/**
 * TrafficLens AI — Client-Side Application Logic
 * Handles: toasts, modals, search, form validation, sidebar toggle, AJAX utilities
 */

// ============================================================
// TOAST NOTIFICATION SYSTEM
// ============================================================
const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'success', duration = 4000) {
        this.init();

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.success} toast-icon"></i>
            <span>${message}</span>
        `;

        // Click to dismiss
        toast.addEventListener('click', () => this.dismiss(toast));

        this.container.appendChild(toast);

        // Auto dismiss
        setTimeout(() => this.dismiss(toast), duration);
    },

    dismiss(toast) {
        toast.classList.add('toast-hide');
        setTimeout(() => toast.remove(), 200);
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error'); },
    warning(message) { this.show(message, 'warning'); }
};

// ============================================================
// CONFIRMATION MODAL
// ============================================================
const Modal = {
    show(title, message, onConfirm, confirmText = 'DELETE', confirmClass = 'btn-danger') {
        // Remove existing modal
        const existing = document.querySelector('.modal-overlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal">
                <h3 class="modal-title">${title}</h3>
                <p class="modal-body">${message}</p>
                <div class="modal-actions">
                    <button class="btn-ghost btn-sm modal-cancel">CANCEL</button>
                    <button class="${confirmClass} btn-sm modal-confirm">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Animate in
        requestAnimationFrame(() => overlay.classList.add('active'));

        // Cancel
        overlay.querySelector('.modal-cancel').addEventListener('click', () => {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 250);
        });

        // Click outside
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                setTimeout(() => overlay.remove(), 250);
            }
        });

        // Confirm
        overlay.querySelector('.modal-confirm').addEventListener('click', () => {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 250);
            if (typeof onConfirm === 'function') onConfirm();
        });

        // Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                overlay.classList.remove('active');
                setTimeout(() => overlay.remove(), 250);
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }
};

// ============================================================
// DELETE CONFIRMATION
// ============================================================
function confirmDelete(url, itemName = 'this item') {
    Modal.show(
        'Confirm Deletion',
        `Are you sure you want to delete <strong>${itemName}</strong>? This action cannot be undone.`,
        () => { window.location.href = url; }
    );
    return false;
}

// ============================================================
// SIDEBAR TOGGLE (MOBILE)
// ============================================================
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('.mobile-menu-toggle');
    if (sidebar && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// ============================================================
// FORM VALIDATION
// ============================================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;

    // Clear previous errors
    form.querySelectorAll('.form-error').forEach(el => el.remove());
    form.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(el => {
        el.style.borderColor = '';
    });

    // Check required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = 'var(--color-danger)';
            const error = document.createElement('span');
            error.className = 'form-error';
            error.textContent = 'This field is required';
            field.parentElement.appendChild(error);
        }
    });

    // Email validation
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            isValid = false;
            field.style.borderColor = 'var(--color-danger)';
            const error = document.createElement('span');
            error.className = 'form-error';
            error.textContent = 'Please enter a valid email';
            field.parentElement.appendChild(error);
        }
    });

    // Phone validation
    form.querySelectorAll('input[type="tel"]').forEach(field => {
        if (field.value && !/^[\d\s\+\-\(\)]{7,15}$/.test(field.value)) {
            isValid = false;
            field.style.borderColor = 'var(--color-danger)';
            const error = document.createElement('span');
            error.className = 'form-error';
            error.textContent = 'Please enter a valid phone number';
            field.parentElement.appendChild(error);
        }
    });

    if (!isValid) {
        Toast.error('Please fix the errors in the form.');
    }

    return isValid;
}

// ============================================================
// SEARCH FILTERING
// ============================================================
function initSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', function () {
        const query = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });

        // Show empty state if no results
        const visibleRows = table.querySelectorAll('tbody tr[style=""]').length +
            table.querySelectorAll('tbody tr:not([style])').length -
            table.querySelectorAll('tbody tr[style="display: none;"]').length;

        const existingEmpty = table.parentElement.querySelector('.search-empty');
        if (existingEmpty) existingEmpty.remove();

        const allHidden = Array.from(rows).every(r => r.style.display === 'none');
        if (allHidden && rows.length > 0) {
            const emptyMsg = document.createElement('div');
            emptyMsg.className = 'search-empty text-center mt-24';
            emptyMsg.innerHTML = `<p class="text-muted">No results found for "<strong>${this.value}</strong>"</p>`;
            table.parentElement.appendChild(emptyMsg);
        }
    });
}

// ============================================================
// AJAX HELPER — Fetch Vehicles by Driver
// ============================================================
function loadVehiclesByDriver(driverSelectId, vehicleSelectId) {
    const driverSelect = document.getElementById(driverSelectId);
    const vehicleSelect = document.getElementById(vehicleSelectId);
    if (!driverSelect || !vehicleSelect) return;

    driverSelect.addEventListener('change', function () {
        const driverId = this.value;
        vehicleSelect.innerHTML = '<option value="">Loading...</option>';

        if (!driverId) {
            vehicleSelect.innerHTML = '<option value="">Select vehicle</option>';
            return;
        }

        fetch(`../api/get_vehicles.php?driver_id=${driverId}`)
            .then(response => response.json())
            .then(data => {
                vehicleSelect.innerHTML = '<option value="">Select vehicle</option>';
                data.forEach(v => {
                    vehicleSelect.innerHTML += `<option value="${v.vehicle_id}">${v.vehicle_number} — ${v.vehicle_type} ${v.manufacturer} ${v.model}</option>`;
                });
            })
            .catch(() => {
                vehicleSelect.innerHTML = '<option value="">Error loading vehicles</option>';
                Toast.error('Failed to load vehicles.');
            });
    });
}

// ============================================================
// AJAX HELPER — Fetch Fine Amount by Rule
// ============================================================
function loadFineByRule(ruleSelectId, fineDisplayId) {
    const ruleSelect = document.getElementById(ruleSelectId);
    const fineDisplay = document.getElementById(fineDisplayId);
    if (!ruleSelect || !fineDisplay) return;

    ruleSelect.addEventListener('change', function () {
        const ruleId = this.value;

        if (!ruleId) {
            fineDisplay.textContent = '₹0.00';
            return;
        }

        fetch(`../api/get_fine.php?rule_id=${ruleId}`)
            .then(response => response.json())
            .then(data => {
                fineDisplay.textContent = `₹${parseFloat(data.fine_amount).toFixed(2)}`;
            })
            .catch(() => {
                fineDisplay.textContent = '₹0.00';
                Toast.error('Failed to load fine amount.');
            });
    });
}

// ============================================================
// AUTO-DISMISS PHP FLASH MESSAGES
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    // Show toast from URL params
    const params = new URLSearchParams(window.location.search);
    if (params.has('success')) {
        Toast.success(decodeURIComponent(params.get('success')));
        // Clean URL
        window.history.replaceState({}, '', window.location.pathname);
    }
    if (params.has('error')) {
        Toast.error(decodeURIComponent(params.get('error')));
        window.history.replaceState({}, '', window.location.pathname);
    }
});

// ============================================================
// CURRENCY FORMATTING
// ============================================================
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// ============================================================
// DATE FORMATTING
// ============================================================
function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}
