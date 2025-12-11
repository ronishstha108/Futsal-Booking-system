// Simple JavaScript for basic interactions
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today and prevent past dates
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(input => {
        if (!input.min) input.min = today;
        if (!input.value) input.value = today;
        
        // Add change event to prevent past date selection
        input.addEventListener('change', function() {
            if (this.value < today) {
                alert('Cannot select past dates. Please choose today or a future date.');
                this.value = today;
            }
        });
    });

    // Mobile menu toggle
    const navToggle = document.querySelector('.toggle');
    const navMenu = document.querySelector('.menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const required = this.querySelectorAll('[required]');
            let valid = true;
            
            // Check required fields
            required.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Additional date validation for booking forms
            const dateInput = this.querySelector('input[type="date"]');
            if (dateInput && dateInput.value < today) {
                valid = false;
                alert('Cannot book for past dates. Please select today or a future date.');
                dateInput.style.borderColor = 'red';
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    });
});

function showMessage(message, type = 'info') {
    alert(message); // Simple alert for demo
}

// Slot selection function for booking page
function selectSlot(slotId) {
    document.querySelectorAll('.slot-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.target.closest('.slot-card').classList.add('selected');
    document.getElementById('selectedSlot').value = slotId;
    document.getElementById('bookBtn').disabled = false;
}

// Date change handler for booking page
function setupDateChangeListener() {
    const dateInput = document.getElementById('bookingDate');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const date = this.value;
            const today = new Date().toISOString().split('T')[0];
            
            if (date < today) {
                alert('Cannot select past dates. Please choose today or a future date.');
                this.value = today;
                return;
            }
            
            window.location.href = `dashboard.php?date=${date}`;
        });
    }
}

// Initialize date change listener when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupDateChangeListener();
});