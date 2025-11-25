/**
 * Multimedia Equipment Watcher - JavaScript
 * 
 * This file contains optional JavaScript enhancements
 */

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#e74c3c';
            isValid = false;
        } else {
            field.style.borderColor = '#dee2e6';
        }
    });
    
    return isValid;
}

// Confirm delete action
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Date validation - ensure due date is not before borrow date
document.addEventListener('DOMContentLoaded', function() {
    const dateBorrowed = document.getElementById('date_borrowed');
    const dueDate = document.getElementById('due_date');
    
    if (dateBorrowed && dueDate) {
        dueDate.addEventListener('change', function() {
            if (this.value && dateBorrowed.value) {
                if (this.value < dateBorrowed.value) {
                    alert('Due date cannot be earlier than the borrow date!');
                    this.value = '';
                }
            }
        });
    }
});
