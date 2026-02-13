// Data for cat food
const foodData = [
    {
        id: "food1",
        name: "Reflex",
        type: "Dry",
        price: 1500,
        brand: "Reflex",
        ageGroup: "Adult",
        image: "https://www.petline.com.hk/wp-content/uploads/8698995028868.jpg",
        description: "Nutritious dry food for adult cats, rich in protein."
    },
    {
        id: "food2",
        name: "Moggy",
        type: "Dry",
        price: 1200,
        brand: "Moggy",
        ageGroup: "Kitten",
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzHuQATy8sqD3pWQvDTvjIcc6T2inbF75coQ&s",
        description: "Chicken and rice dry food, perfect for adult cats."
    },
    {
        id: "food3",
        name: "Pawfect",
        type: "Dry",
        price: 1800,
        brand: "Pawfect",
        ageGroup: "Adult",
        image: "https://catsandtails.com.pk/wp-content/uploads/2023/04/1682454792_Pawfect-Adult-cat-food.jpeg",
        description: "Balanced dry food for active adult cats."
    },
    {
        id: "food4",
        name: "Fluffy Food",
        type: "Dry",
        price: 1600,
        brand: "Fluffy Food",
        ageGroup: "Adult",
        image: "https://petcity.pt/19566-large_default/fluffy-cat-adult.jpg",
        description: "Premium dry food with essential nutrients for adults."
    },
    {
        id: "food5",
        name: "Royal Canin",
        type: "Dry",
        price: 2000,
        brand: "Royal Canin",
        ageGroup: "Kitten",
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTJ8Eml12hzDBRA0FLxG16O8-cz8f2p-qqXPw&s",
        description: "High-quality dry food for adult cat health."
    },
    {
        id: "food6",
        name: "Reflex Plus",
        type: "Dry",
        price: 1700,
        brand: "Reflex",
        ageGroup: "Adult",
        image: "https://petcollectives.com/cdn/shop/products/Untitled-1-01_da2c4d3b-5352-4b30-bb22-938ca5bfc3e2.jpg?v=1694682082",
        description: "Enhanced dry food formula for adult cats."
    },
    {
        id: "food7",
        name: "Moggy Adult",
        type: "Dry",
        price: 1300,
        brand: "Moggy",
        ageGroup: "Adult",
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQbfx8G6YW1lMohB_RxrziBtjgIz8oDAOpqUQ&s",
        description: "Affordable dry food for adult cats, great taste."
    },
    {
        id: "food8",
        name: "Pawfect Kitten",
        type: "Dry",
        price: 1900,
        brand: "Pawfect",
        ageGroup: "Kitten",
        image: "https://happytailspk.com/wp-content/uploads/2024/02/pawfectkitten_1a405db4-85e9-48ca-8462-90a692f8353b.webp",
        description: "Special dry food for growing kittens."
    },
    {
        id: "food9",
        name: "Fluffy Senior",
        type: "Dry",
        price: 1650,
        brand: "Fluffy Food",
        ageGroup: "Senior",
        image: "https://petcity.pt/19566-large_default/fluffy-cat-adult.jpg",
        description: "Gentle dry food for senior cats, easy to digest."
    },
    {
        id: "food10",
        name: "Royal Canin Wet",
        type: "Wet",
        price: 2100,
        brand: "Royal Canin",
        ageGroup: "Adult",
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTaCAzuCNXPF-3djrOfENl4T69_8cEd4J33aA&s",
        description: "Delicious wet food for adult cats, high moisture."
    },
    {
        id: "food11",
        name: "Reflex Treats",
        type: "Treats",
        price: 1400,
        brand: "Reflex",
        ageGroup: "All",
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRqvL72VmHRJfrE7uq7LRbolXZN0fRuGLomGg&s",
        description: "Tasty treats for cats of all ages."
    },
    {
        id: "food12",
        name: "Moggy Treats",
        type: "Treats",
        price: 1250,
        brand: "Moggy",
        ageGroup: "All",
        image: "https://happytailspk.com/wp-content/uploads/2024/02/pawfectkitten_1a405db4-85e9-48ca-8462-90a692f8353b.webp",
        description: "Crunchy treats to reward your cat."
    }
];

// Elements for navigation, cart, and food
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

// Function to add item to cart
function addFoodToCart(itemId) {
    try {
        const item = foodData.find(food => food.id === itemId);
        if (!item) {
            console.error(`Food item with ID ${itemId} not found`);
            return;
        }
        addToCart(item); // Use cart.js's addToCart
        document.dispatchEvent(new Event('cartUpdated')); // Trigger cart update
        showNotification(`${item.name} added to cart`);
    } catch (error) {
        console.error('Error adding food item to cart:', error);
    }
}

// Function to render food items
function renderFoodItems(foodItems) {
    const foodGrid = document.getElementById('foodGrid');
    if (!foodGrid) return;
    foodGrid.innerHTML = '';
    foodItems.forEach(item => {
        const foodDiv = document.createElement('div');
        foodDiv.className = 'food-item';
        foodDiv.innerHTML = `
            <a href="product-details.html?id=${item.id}">
                <img src="${item.image}" alt="${item.name}">
            </a>
            <h3>${item.name}</h3>
            <p>Age Group: ${item.ageGroup} | Price: ${item.price} Rs</p>
            <button class="shop-btn" onclick="addFoodToCart('${item.id}')">Add to Cart</button>
        `;
        foodGrid.appendChild(foodDiv);
    });
}

// Function to filter food items
function filterFoodItems() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const priceFilter = document.getElementById('priceFilter').value;
    const brandFilter = document.getElementById('brandFilter').value;
    const ageGroupFilter = document.getElementById('ageGroupFilter').value;

    let filteredFood = foodData;

    // Apply search filter
    if (searchInput) {
        filteredFood = filteredFood.filter(item =>
            item.name.toLowerCase().includes(searchInput) ||
            item.brand.toLowerCase().includes(searchInput)
        );
    }

    // Apply type filter
    if (typeFilter !== 'All') {
        filteredFood = filteredFood.filter(item => item.type === typeFilter);
    }

    // Apply price filter
    if (priceFilter !== 'All') {
        const [minPrice, maxPrice] = priceFilter.split('-').map(Number);
        if (maxPrice) {
            filteredFood = filteredFood.filter(item => item.price >= minPrice && item.price <= maxPrice);
        } else {
            filteredFood = filteredFood.filter(item => item.price >= minPrice);
        }
    }

    // Apply brand filter
    if (brandFilter !== 'All') {
        filteredFood = filteredFood.filter(item => item.brand === brandFilter);
    }

    // Apply age group filter
    if (ageGroupFilter !== 'All') {
        filteredFood = filteredFood.filter(item => item.ageGroup === ageGroupFilter || item.ageGroup === 'All');
    }

    renderFoodItems(filteredFood);
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

// Event listeners for filters and search
document.getElementById('searchInput').addEventListener('input', filterFoodItems);
document.getElementById('typeFilter').addEventListener('change', filterFoodItems);
document.getElementById('priceFilter').addEventListener('change', filterFoodItems);
document.getElementById('brandFilter').addEventListener('change', filterFoodItems);
document.getElementById('ageGroupFilter').addEventListener('change', filterFoodItems);

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

    // Render food items and update cart
    renderFoodItems(foodData);
    updateCartCount();
    renderCart();
});