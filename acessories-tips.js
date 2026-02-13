// Elements for navigation and cart functionality
const openCartLink = document.getElementById('openCart');
const cartCountElement = document.getElementById('cartCount');
const accountLink = document.getElementById('accountLink');
const accountDropdown = document.getElementById('accountDropdown');
const myProfileLink = document.getElementById('myProfileLink');
const editProfileLink = document.getElementById('editProfileLink');
const logoutBtn = document.getElementById('logoutBtn');

// Simulated login state (aligned with account.js)
let isLoggedIn = false;

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
        const cartItemsContainer = document.getElementById('cartItems');
        const cartTotalElement = document.getElementById('cartTotal');
        const cart = getCart();

        if (!cartItemsContainer || !cartTotalElement) return;

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
                        <button class="quantity-btn" onclick="updateItemQuantity('${item.id}', ${item.quantity - 1})">–</button>
                        <span>Qty: ${item.quantity || 1}</span>
                        <button class="quantity-btn" onclick="updateItemQuantity('${item.id}', ${item.quantity + 1})">+</button>
                    </div>
                    <p>Total: ${itemTotal} Rs</p>
                </div>
                <button class="remove-btn" onclick="removeItem('${item.id}')">Remove</button>
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
    // Update cart count
    updateCartCount();
});