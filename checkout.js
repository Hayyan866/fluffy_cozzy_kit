// Elements
const cardPaymentOption = document.getElementById('card');
const codPaymentOption = document.getElementById('cod');
const cardDetailsSection = document.getElementById('cardDetails');
const shippingForm = document.getElementById('shippingForm');
const cartItemsContainer = document.getElementById('cartItems');
const subtotalElement = document.getElementById('subtotal');
const shippingElement = document.getElementById('shipping');
const discountRowElement = document.getElementById('discountRow');
const discountElement = document.getElementById('discount');
const totalElement = document.getElementById('total');
const promoCodeInput = document.getElementById('promoCode');
const applyPromoButton = document.getElementById('applyPromo');
const promoMessageElement = document.getElementById('promoMessage');
const cartCountElement = document.getElementById('cartCount');

// Constants
const SHIPPING_FEE = 250; // 250 Rs shipping fee

// Variables
let appliedDiscount = 0;
let promoCodeApplied = false;

// DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const loggedInUserEmail = localStorage.getItem('loggedInUserEmail');
    if (!loggedInUserEmail) {
        // Redirect to login page if not logged in
        window.location.href = 'account.html?redirect=checkout';
        return;
    }

    // Load user data
    loadUserData(loggedInUserEmail);
    
    // Load cart items
    loadCartItems();
    
    // Update cart count in header
    updateCartCount();
    
    // Initialize payment method toggle
    initPaymentMethodToggle();
    
    // Initialize form submission
    initFormSubmission();
    
    // Initialize promo code functionality
    initPromoCode();
});

// Function to load user data
function loadUserData(email) {
    fetch(`api/get_user_data.php?email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Fill form fields with user data
                document.getElementById('firstName').value = data.user.first_name || '';
                document.getElementById('lastName').value = data.user.last_name || '';
                document.getElementById('email').value = data.user.email || '';
                // Don't pre-fill sensitive information
            } else {
                console.error('Error loading user data:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading user data:', error);
        });
}

// Function to load cart items
function loadCartItems() {
    const cart = getCart();
    
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p class="empty-cart">Your cart is empty.</p>';
        updateOrderSummary(0);
        return;
    }
    
    cartItemsContainer.innerHTML = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * (item.quantity || 1);
        subtotal += itemTotal;
        
        const cartItem = document.createElement('div');
        cartItem.className = 'order-item';
        cartItem.innerHTML = `
            <img src="${item.image || 'placeholder.png'}" alt="${item.name}">
            <div class="item-details">
                <h3>${item.name}</h3>
                <p>Qty: ${item.quantity || 1}</p>
                <p>Price: ${item.price} Rs</p>
            </div>
            <div class="item-price">${itemTotal} Rs</div>
        `;
        cartItemsContainer.appendChild(cartItem);
    });
    
    updateOrderSummary(subtotal);
}

// Function to update order summary
function updateOrderSummary(subtotal) {
    subtotalElement.textContent = `${subtotal} Rs`;
    shippingElement.textContent = `${SHIPPING_FEE} Rs`;
    
    if (promoCodeApplied) {
        discountRowElement.style.display = 'flex';
        discountElement.textContent = `-${appliedDiscount} Rs`;
    } else {
        discountRowElement.style.display = 'none';
    }
    
    const total = subtotal + SHIPPING_FEE - appliedDiscount;
    totalElement.textContent = `${total} Rs`;
}

// Function to initialize payment method toggle
function initPaymentMethodToggle() {
    cardPaymentOption.addEventListener('change', () => {
        cardDetailsSection.style.display = 'block';
    });
    
    codPaymentOption.addEventListener('change', () => {
        cardDetailsSection.style.display = 'none';
    });
}

// Function to initialize form submission
function initFormSubmission() {
    shippingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Process order
        processOrder();
    });
}

// Function to validate form
function validateForm() {
    const requiredFields = ['firstName', 'lastName', 'email', 'phone', 'address', 'city', 'postalCode'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    // Validate card details if card payment is selected
    if (cardPaymentOption.checked) {
        const cardFields = ['cardNumber', 'expiry', 'cvv', 'nameOnCard'];
        cardFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
    }
    
    if (!isValid) {
        alert('Please fill all required fields.');
    }
    
    return isValid;
}

// Function to process order
function processOrder() {
    const cart = getCart();
    
    if (cart.length === 0) {
        alert('Your cart is empty.');
        return;
    }
    
    // Create order data
    const orderData = {
        email: document.getElementById('email').value,
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        address: document.getElementById('address').value,
        city: document.getElementById('city').value,
        postal_code: document.getElementById('postalCode').value,
        phone: document.getElementById('phone').value,
        payment_method: cardPaymentOption.checked ? 'card' : 'cod',
        shipping_fee: SHIPPING_FEE,
        discount_amount: appliedDiscount,
        items: cart,
        promo_code: promoCodeApplied ? promoCodeInput.value : null
    };
    
    // Send order to server
    fetch('api/place_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            clearCart();
            
            // Show success message
            alert('Order placed successfully!');
            
            // Redirect to confirmation page
            window.location.href = `order_confirmation.html?order_id=${data.order_id}`;
        } else {
            alert(`Error placing order: ${data.error}`);
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        alert('An error occurred while placing your order. Please try again.');
    });
}

// Function to initialize promo code functionality
function initPromoCode() {
    applyPromoButton.addEventListener('click', () => {
        const promoCode = promoCodeInput.value.trim();
        
        if (!promoCode) {
            promoMessageElement.textContent = 'Please enter a promo code.';
            promoMessageElement.className = 'error';
            return;
        }
        
        // Check promo code with server
        fetch(`api/check_promo.php?code=${encodeURIComponent(promoCode)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    promoCodeApplied = true;
                    appliedDiscount = data.discount_amount;
                    
                    promoMessageElement.textContent = `Promo code applied! You saved ${data.discount_amount} Rs.`;
                    promoMessageElement.className = 'success';
                    
                    // Update order summary
                    const subtotal = parseFloat(subtotalElement.textContent.replace(' Rs', ''));
                    updateOrderSummary(subtotal);
                } else {
                    promoCodeApplied = false;
                    appliedDiscount = 0;
                    
                    promoMessageElement.textContent = data.error || 'Invalid promo code.';
                    promoMessageElement.className = 'error';
                    
                    // Update order summary
                    const subtotal = parseFloat(subtotalElement.textContent.replace(' Rs', ''));
                    updateOrderSummary(subtotal);
                }
            })
            .catch(error => {
                console.error('Error checking promo code:', error);
                promoMessageElement.textContent = 'Error checking promo code. Please try again.';
                promoMessageElement.className = 'error';
            });
    });
}

// Function to update cart count
function updateCartCount() {
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
    }
}