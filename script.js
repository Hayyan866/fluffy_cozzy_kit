// Function to update cart count
function updateCartCount() {
    try {
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        const cartCountElement = document.getElementById('cartCount');
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
    window.location.href = 'checkout.html';
}

// Function to view cart
function viewCart() {
    window.location.href = 'cart.html';
}

// Function to scroll to video section
function scrollToVideo() {
    const videoSection = document.getElementById('videoSection');
    if (videoSection) {
        videoSection.scrollIntoView({ behavior: 'smooth' });
    }
}

// Event listeners for cart panel
document.getElementById('openCart').addEventListener('click', (e) => {
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

// From account.js
const accountLink = document.getElementById('accountLink');
const accountDropdown = document.getElementById('accountDropdown');
const myProfileLink = document.getElementById('myProfileLink');
const editProfileLink = document.getElementById('editProfileLink');
const logoutBtn = document.getElementById('logoutBtn');

// Toggle dropdown on account link click
accountLink.addEventListener('click', (e) => {
    e.preventDefault();
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    if (isLoggedIn) {
        accountDropdown.classList.toggle('active');
    } else {
        window.location.href = 'account.php'; // Redirect if not logged in
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!accountLink.contains(e.target) && !accountDropdown.contains(e.target)) {
        accountDropdown.classList.remove('active');
    }
});

// Event listener for logout button
logoutBtn.addEventListener('click', () => {
    resetToLogin();
});

// Reset to login state (simulate logout)
function resetToLogin() {
    localStorage.setItem('isLoggedIn', 'false');
    window.location.href = 'account.php';
}

// Update cart count on page load
window.addEventListener('load', () => {
    updateCartCount();
});