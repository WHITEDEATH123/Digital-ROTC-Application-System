/**
 * Main JavaScript file for Digital ROTC System
 * Handles common frontend interactions
 */

// Utility functions
function showMessage(message, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type}`;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 1000;
        max-width: 300px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;
    
    switch(type) {
        case 'success':
            messageDiv.style.backgroundColor = '#d4edda';
            messageDiv.style.color = '#155724';
            messageDiv.style.border = '1px solid #c3e6cb';
            break;
        case 'error':
            messageDiv.style.backgroundColor = '#f8d7da';
            messageDiv.style.color = '#721c24';
            messageDiv.style.border = '1px solid #f5c6cb';
            break;
        case 'warning':
            messageDiv.style.backgroundColor = '#fff3cd';
            messageDiv.style.color = '#856404';
            messageDiv.style.border = '1px solid #ffeaa7';
            break;
        default:
            messageDiv.style.backgroundColor = '#d1ecf1';
            messageDiv.style.color = '#0c5460';
            messageDiv.style.border = '1px solid #bee5eb';
    }
    
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
        }
    }, 5000);
    
    // Click to dismiss
    messageDiv.addEventListener('click', () => {
        if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
        }
    });
}

// Form validation helpers
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^09\d{9}$/;
    return re.test(phone);
}

function validateRequired(value) {
    return value && value.trim().length > 0;
}

// File upload helpers
function validateImageFile(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!allowedTypes.includes(file.type)) {
        throw new Error('Please upload a valid image file (JPG, PNG, or GIF)');
    }
    
    if (file.size > maxSize) {
        throw new Error('File size must be less than 5MB');
    }
    
    return true;
}

function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        try {
            validateImageFile(input.files[0]);
            
            const reader = new FileReader();
            reader.onload = function(e) {
                if (previewElement) {
                    previewElement.src = e.target.result;
                    previewElement.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        } catch (error) {
            showMessage(error.message, 'error');
            input.value = '';
        }
    }
}

// AJAX helper
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return fetch(url, finalOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Request failed:', error);
            showMessage('Network error occurred. Please try again.', 'error');
            throw error;
        });
}

// Confirmation dialogs
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Loading indicator
function showLoading(element) {
    if (element) {
        element.disabled = true;
        element.textContent = 'Loading...';
    }
}

function hideLoading(element, originalText) {
    if (element) {
        element.disabled = false;
        element.textContent = originalText;
    }
}

// Print functionality
function printPage() {
    window.print();
}

// Navigation helpers
function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = 'dashboard.php';
    }
}

// Initialize common functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for common buttons
    const backButtons = document.querySelectorAll('[data-action="back"]');
    backButtons.forEach(button => {
        button.addEventListener('click', goBack);
    });
    
    const printButtons = document.querySelectorAll('[data-action="print"]');
    printButtons.forEach(button => {
        button.addEventListener('click', printPage);
    });
    
    // Add form validation classes
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!validateRequired(field.value)) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
                
                // Special validation for email and phone
                if (field.type === 'email' && field.value && !validateEmail(field.value)) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                }
                
                if (field.type === 'tel' && field.value && !validatePhone(field.value)) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showMessage('Please fill in all required fields correctly.', 'error');
            }
        });
    });
    
    // Add image preview functionality
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            const previewElement = previewId ? document.getElementById(previewId) : null;
            previewImage(this, previewElement);
        });
    });
    
    // Check for URL parameters and show messages
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        showMessage('Operation completed successfully!', 'success');
    }
    if (urlParams.get('error')) {
        showMessage(urlParams.get('error'), 'error');
    }
    if (urlParams.get('confirmed')) {
        showMessage('Enrollment confirmed successfully!', 'success');
    }
});

// Export functions for global use
window.ROTC = {
    showMessage,
    validateEmail,
    validatePhone,
    validateRequired,
    validateImageFile,
    previewImage,
    makeRequest,
    confirmAction,
    showLoading,
    hideLoading,
    printPage,
    goBack
};
