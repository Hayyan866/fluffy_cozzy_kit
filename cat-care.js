// Elements for navigation and cart
const openCartLink = document.getElementById('openCart');
const cartCountElement = document.getElementById('cartCount');
const cartItemsContainer = document.getElementById('cartItems');
const cartTotalElement = document.getElementById('cartTotal');
const cartNotification = document.getElementById('cartNotification');
const accountLink = document.getElementById('accountLink');
const accountDropdown = document.getElementById('accountDropdown');
const myProfileLink = document.getElementById('myProfileLink');
const editProfileLink = document.getElementById('editProfileLink');
const logoutBtn = document.getElementById('logoutBtn');
const adminLink = document.getElementById('adminLink');

// Simulated login state
let isLoggedIn = false;

// Function to check if user is admin
function checkAdminAccess() {
    const loggedInUserEmail = localStorage.getItem('loggedInUserEmail');
    const adminEmails = ['admin@example.com']; // Hardcoded for simplicity
    return loggedInUserEmail && adminEmails.includes(loggedInUserEmail);
}

// Function to show notification
function showNotification(message) {
    if (cartNotification) {
        cartNotification.textContent = message;
        cartNotification.classList.add('active');
        setTimeout(() => {
            cartNotification.classList.remove('active');
        }, 3000); // Hide after 3 seconds
    }
}

// Function to update cart count
function updateCartCount() {
    try {
        const cart = getCart(); // Assumes getCart() is defined in cart.js
        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        if (cartCountElement) {
            cartCountElement.textContent = totalItems;
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Function to render cart items in the side panel
function renderCart() {
    try {
        if (!cartItemsContainer || !cartTotalElement) return;

        const cart = getCart();

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p class="cart-empty">Your cart is empty.</p>';
            cartTotalElement.innerHTML = '';
            return;
        }

        cartItemsContainer.innerHTML = '';
        let totalPrice = 0;

        cart.forEach(item => {
            const itemTotal = item.price * (item.quantity || 1);
            totalPrice += itemTotal;

            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <img src="${item.image || 'placeholder.png'}" alt="${item.name}">
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <p>Price: ${item.price} Rs</p>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateItemQuantity('${item.id}', ${item.quantity - 1}, true)">–</button>
                        <span>Qty: ${item.quantity || 1}</span>
                        <button class="quantity-btn" onclick="updateItemQuantity('${item.id}', ${item.quantity + 1}, true)">+</button>
                    </div>
                    <p>Total: ${itemTotal} Rs</p>
                </div>
                <button class="remove-btn" onclick="removeItem('${item.id}', true)">Remove</button>
            `;
            cartItemsContainer.appendChild(cartItem);
        });

        cartTotalElement.innerHTML = `Subtotal: ${totalPrice} Rs`;
    } catch (error) {
        console.error('Error rendering cart:', error);
    }
}

// Function to toggle cart panel
function toggleCartPanel(open = true) {
    const cartPanel = document.getElementById('cartPanel');
    const overlay = document.getElementById('cartOverlay');
    if (open) {
        cartPanel.classList.add('open');
        overlay.classList.add('active');
        renderCart();
    } else {
        cartPanel.classList.remove('open');
        overlay.classList.remove('active');
    }
}

// Function to handle checkout
function checkout() {
    if (!isLoggedIn) {
        alert('Please login to proceed to checkout.');
        window.location.href = 'account.html';
        return;
    }
    window.location.href = 'checkout.html';
}

// Function to view cart
function viewCart() {
    window.location.href = 'cart.html';
}

// Override updateItemQuantity to include notification
window.updateItemQuantity = function(id, quantity, showNotif = false) {
    try {
        const cart = getCart();
        const item = cart.find(item => item.id === id);
        if (item) {
            if (quantity <= 0) {
                removeItem(id, showNotif);
                return;
            }
            item.quantity = quantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            document.dispatchEvent(new Event('cartUpdated'));
            if (showNotif) {
                showNotification(`Updated quantity for ${item.name}`);
            }
        }
    } catch (error) {
        console.error('Error updating item quantity:', error);
    }
};

// Override removeItem to include notification
window.removeItem = function(id, showNotif = false) {
    try {
        const cart = getCart();
        const item = cart.find(item => item.id === id);
        const updatedCart = cart.filter(item => item.id !== id);
        localStorage.setItem('cart', JSON.stringify(updatedCart));
        document.dispatchEvent(new Event('cartUpdated'));
        if (showNotif && item) {
            showNotification(`${item.name} removed from cart`);
        }
    } catch (error) {
        console.error('Error removing item:', error);
    }
};

// Toggle dropdown menu
accountLink.addEventListener('click', (e) => {
    e.preventDefault();
    if (isLoggedIn) {
        accountDropdown.classList.toggle('active');
    } else {
        window.location.href = 'account.html';
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!accountLink.contains(e.target) && !accountDropdown.contains(e.target)) {
        accountDropdown.classList.remove('active');
    }
});

// Event listener for "My Profile" link
myProfileLink.addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = 'account.html';
    accountDropdown.classList.remove('active');
});

// Event listener for "Edit Profile" link
editProfileLink.addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = 'account.html';
    accountDropdown.classList.remove('active');
});

// Event listener for logout button
logoutBtn.addEventListener('click', () => {
    localStorage.removeItem('loggedInUserEmail'); // Clear logged-in user
    isLoggedIn = false;
    accountDropdown.classList.remove('active');
    window.location.href = 'account.html';
});

// Event listener for admin link
adminLink.addEventListener('click', (e) => {
    if (!checkAdminAccess()) {
        e.preventDefault();
        alert('Access denied. You must be an admin to view this page.');
        window.location.href = 'account.html';
    }
});

// Event listeners for cart panel
openCartLink.addEventListener('click', (e) => {
    e.preventDefault();
    toggleCartPanel(true);
});

document.getElementById('closeCart').addEventListener('click', () => toggleCartPanel(false));
document.getElementById('cartOverlay').addEventListener('click', () => toggleCartPanel(false));

// Event listener for cart updates
document.addEventListener('cartUpdated', () => {
    updateCartCount();
    renderCart();
});

// Initial setup
document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const loggedInUserEmail = localStorage.getItem('loggedInUserEmail');
    if (loggedInUserEmail) {
        isLoggedIn = true;
    }

    // Update cart count and render cart
    updateCartCount();
    renderCart();
});