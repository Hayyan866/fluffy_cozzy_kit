// cart.js - Cart functionality for Fluffy Cozzy Kit
document.addEventListener('DOMContentLoaded', function() {
    // Cart panel elements
    const openCartBtn = document.getElementById('openCart');
    const closeCartBtn = document.getElementById('closeCart');
    const cartPanel = document.getElementById('cartPanel');
    const cartOverlay = document.getElementById('cartOverlay');
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    
    // Toggle cart panel visibility
    openCartBtn.addEventListener('click', function(e) {
        e.preventDefault();
        loadCartItems(); // Load cart items when opening the panel
        cartPanel.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
    
    closeCartBtn.addEventListener('click', function() {
        cartPanel.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    cartOverlay.addEventListener('click', function() {
        cartPanel.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Function to load cart items via AJAX
    function loadCartItems() {
        // Show loading indicator
        cartItems.innerHTML = '<div class="loading">Loading your cart...</div>';
        
        // Fetch cart data
        fetch('get_cart.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCartItems(data.items);
                    updateCartTotal(data.total);
                } else {
                    cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
                    cartTotal.innerHTML = 'Total: Rs. 0.00';
                }
            })
            .catch(error => {
                console.error('Error loading cart:', error);
                cartItems.innerHTML = '<div class="error">Could not load cart. Please try again.</div>';
            });
    }
    
    // Function to display cart items in panel
    function displayCartItems(items) {
        if (items.length === 0) {
            cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
            return;
        }
        
        let html = '';
        items.forEach(item => {
            html += `
                <div class="cart-item" data-id="${item.product_id}">
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <h3>${item.name}</h3>
                        <p class="cart-item-price">Rs. ${parseFloat(item.price).toFixed(2)}</p>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn minus" data-id="${item.product_id}">-</button>
                            <span class="quantity">${item.quantity}</span>
                            <button class="quantity-btn plus" data-id="${item.product_id}">+</button>
                        </div>
                    </div>
                    <button class="remove-item" data-id="${item.product_id}">×</button>
                </div>
            `;
        });
        
        cartItems.innerHTML = html;
        
        // Add event listeners for quantity buttons and remove buttons
        document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
            btn.addEventListener('click', function() {
                updateCartItemQuantity(this.dataset.id, -1);
            });
        });
        
        document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
            btn.addEventListener('click', function() {
                updateCartItemQuantity(this.dataset.id, 1);
            });
        });
        
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                removeCartItem(this.dataset.id);
            });
        });
    }
    
    // Update cart total
    function updateCartTotal(total) {
        cartTotal.innerHTML = `<strong>Total: Rs. ${parseFloat(total).toFixed(2)}</strong>`;
    }
    
    // Function to update cart item quantity
    function updateCartItemQuantity(productId, change) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `product_id=${productId}&quantity_change=${change}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update quantity display
                const quantityElement = document.querySelector(`.cart-item[data-id="${productId}"] .quantity`);
                if (quantityElement) {
                    quantityElement.textContent = data.new_quantity;
                }
                
                // If quantity is 0, remove the item
                if (data.new_quantity <= 0) {
                    const itemElement = document.querySelector(`.cart-item[data-id="${productId}"]`);
                    if (itemElement) {
                        itemElement.remove();
                    }
                }
                
                // Update cart count in header
                document.getElementById('cartCount').textContent = data.cart_count;
                
                // Update total
                updateCartTotal(data.total);
                
                // If cart is empty now
                if (data.cart_count === 0) {
                    cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
        });
    }
    
    // Function to remove cart item
    function removeCartItem(productId) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `product_id=${productId}&remove=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from the panel
                const itemElement = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                // Update cart count in header
                document.getElementById('cartCount').textContent = data.cart_count;
                
                // Update total
                updateCartTotal(data.total);
                
                // If cart is empty now
                if (data.cart_count === 0) {
                    cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error removing cart item:', error);
        });
    }
});