// Data for adult cats
const catsData = [
    {
        id: "adult1",
        name: "Scottish Adult",
        breed: "Scottish",
        age: 2,
        price: 18000,
        image: "https://media.istockphoto.com/id/1795356989/photo/british-shorthair-cat-lies-on-a-white-background-and-looks-into-the-camera.jpg?s=612x612&w=0&k=20&c=BJyuLFRZCkrtJoJ0uc9cBHVau4Gx4Va04N8XSzyAmIs=",
        description: "A friendly Scottish cat with a calm temperament, perfect for families."
    },
    {
        id: "adult2",
        name: "Persian Adult(White)",
        breed: "Persian",
        age: 3,
        price: 8000,
        image: "https://media.istockphoto.com/id/1131383991/photo/white-persian-cat-three-months-ago.jpg?s=612x612&w=0&k=20&c=6uVX39ln3HukbekLFpsnsRUjhSNHe4SlPEcZ9TMalZY=",
        description: "Elegant white Persian with a luxurious coat, loves to cuddle."
    },
    {
        id: "adult3",
        name: "Persian Adult(Black)",
        breed: "Persian",
        age: 2.5,
        price: 8500,
        image: "https://media.istockphoto.com/id/1250692028/photo/black-persian-domestic-cat-adult-sitting-against-white-background.jpg?s=612x612&w=0&k=20&c=wD1ClGBU_Hrgy2SIw62L3r5LJPrsNS905Kk7akIJDeE=",
        description: "Sleek black Persian, affectionate and playful."
    },
    {
        id: "adult4",
        name: "Persian Adult(Brown)",
        breed: "Persian",
        age: 4,
        price: 9000,
        image: "https://media.istockphoto.com/id/685891408/photo/fluffy-british-longhair-cat-isolated-on-a-white-background.jpg?s=612x612&w=0&k=20&c=AuQxJ8wK4Z0-1Q5j9sWR4CrwGsAGe-SxMMOGqakMlc4=",
        description: "Warm brown Persian, enjoys quiet environments."
    },
    {
        id: "adult5",
        name: "Persian Adult(Fawn)",
        breed: "Persian",
        age: 3.5,
        price: 9500,
        image: "https://media.istockphoto.com/id/1137939659/photo/persian-cat-lying-in-front-of-white-background.jpg?s=612x612&w=0&k=20&c=Cs7afJADV4FFgVsrU7AiSJbjsSb1GqIK65k_2GKoOqk=",
        description: "Fawn-colored Persian, gentle and loving."
    },
    {
        id: "adult6",
        name: "Siamese Adult",
        breed: "Siamese",
        age: 4,
        price: 12000,
        image: "https://media.istockphoto.com/id/146960014/photo/siamese-kitten-sitting-on-a-white-background.jpg?s=612x612&w=0&k=20&c=qEBxswCvRMjAiQ0lZMtd1V1x1xgggeWRj55ZbsUlb0c=",
        description: "Vocal and active Siamese, loves attention."
    },
    {
        id: "adult7",
        name: "Persian Adult",
        breed: "Persian",
        age: 3,
        price: 15000,
        image: "https://media.istockphoto.com/id/467299620/photo/cute-3-month-old-persian-seal-colourpoint-kitten-is-lying.jpg?s=612x612&w=0&k=20&c=GRrtpCiL4TvNxGV7o9GZWutGsvBsE3A_YuN-PnWrRIU=",
        description: "Classic Persian with a sweet personality."
    },
    {
        id: "adult8",
        name: "Persian Adult(Ginger)",
        breed: "Persian",
        age: 2.5,
        price: 11000,
        image: "https://media.istockphoto.com/id/123176193/photo/persian-cat.jpg?s=612x612&w=0&k=20&c=YCTtIpP29p77S2KXRGM7nCxAKtNtz2zSQTCtwWTJbLU=",
        description: "Vibrant ginger Persian, full of charm."
    },
    {
        id: "adult9",
        name: "Persian Adult(Calico)",
        breed: "Persian",
        age: 3.5,
        price: 13000,
        image: "https://media.istockphoto.com/id/510066770/photo/persian-kitten-3-months-old-standing.jpg?s=612x612&w=0&k=20&c=EckxCEF-gOUpLIBgqHaX2RcMxF2X0a3HHho9pBlka8I=",
        description: "Unique calico Persian, loves to play."
    },
    {
        id: "adult10",
        name: "Persian Adult(Bi)",
        breed: "Persian",
        age: 4,
        price: 16000,
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTe6hMUCeimsx7O2Zmo5cIL2UcQS0a9-nIRw&s",
        description: "Bi-colored Persian, elegant and poised."
    },
    {
        id: "adult11",
        name: "Persian Adult(Grey)",
        breed: "Persian",
        age: 2,
        price: 9000,
        image: "https://www.omlet.co.uk/images/cache/512/341/persian-smoke-cat-against-white-background.jpg",
        description: "Grey Persian with a soft, fluffy coat."
    },
    {
        id: "adult12",
        name: "Persian Adult(Tabby)",
        breed: "Persian",
        age: 3,
        price: 17000,
        image: "https://st2.depositphotos.com/1004199/7997/i/450/depositphotos_79979698-stock-photo-persian-kitten-in-front-of.jpg",
        description: "Tabby-patterned Persian, energetic and curious."
    }
];

// Elements for navigation and cart
const openCartLink = document.getElementById('openCart');
const cartCountElement = document.getElementById('cartCount');
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

// Function to add item to cart
function addCatToCart(catId) {
    try {
        const cat = catsData.find(c => c.id === catId);
        if (!cat) {
            console.error(`Cat with ID ${catId} not found`);
            return;
        }
        addToCart(cat); // Assumes addToCart is defined in cart.js
        updateCartCount();
        renderCart();
    } catch (error) {
        console.error('Error adding item to cart:', error);
    }
}

// Function to render cats
function renderCats(cats) {
    const catsGrid = document.getElementById('catsGrid');
    catsGrid.innerHTML = '';
    cats.forEach(cat => {
        const catDiv = document.createElement('div');
        catDiv.className = 'cat';
        catDiv.innerHTML = `
            <a href="product-details.html?id=${cat.id}">
                <img src="${cat.image}" alt="${cat.name}">
            </a>
            <h3>${cat.name}</h3>
            <p>Age: ${cat.age} years | Adoption Fee: ${cat.price} Rs</p>
            <button class="shop-btn" onclick="addCatToCart('${cat.id}')">Add to Cart</button>
        `;
        catsGrid.appendChild(catDiv);
    });
}

// Function to filter cats
function filterCats() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const breedFilter = document.getElementById('breedFilter').value;
    const ageFilter = document.getElementById('ageFilter').value;
    const priceFilter = document.getElementById('priceFilter').value;

    let filteredCats = catsData;

    // Apply search filter
    if (searchInput) {
        filteredCats = filteredCats.filter(cat =>
            cat.name.toLowerCase().includes(searchInput) ||
            cat.breed.toLowerCase().includes(searchInput)
        );
    }

    // Apply breed filter
    if (breedFilter !== 'All') {
        filteredCats = filteredCats.filter(cat => cat.breed === breedFilter);
    }

    // Apply age filter
    if (ageFilter !== 'All') {
        const [minAge, maxAge] = ageFilter.split('-').map(Number);
        filteredCats = filteredCats.filter(cat => cat.age >= minAge && cat.age <= maxAge);
    }

    // Apply price filter
    if (priceFilter !== 'All') {
        const [minPrice, maxPrice] = priceFilter.split('-').map(Number);
        filteredCats = filteredCats.filter(cat => cat.price >= minPrice && cat.price <= maxPrice);
    }

    renderCats(filteredCats);
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

// Event listeners for filters and search
document.getElementById('searchInput').addEventListener('input', filterCats);
document.getElementById('breedFilter').addEventListener('change', filterCats);
document.getElementById('ageFilter').addEventListener('change', filterCats);
document.getElementById('priceFilter').addEventListener('change', filterCats);

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

    // Update cart count and render cats
    updateCartCount();
    renderCats(catsData);
});