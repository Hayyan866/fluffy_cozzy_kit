// Data for kittens
const kittensData = [
    {
        id: "kitten1",
        name: "Scottish Kitten",
        breed: "Scottish",
        age: 0.25,
        price: 7500,
        gender: "Male",
        image: "https://media.istockphoto.com/id/1754761653/photo/british-shorthair-cat-sits-on-a-white-background-and-looks-at-the-camera.jpg?s=612x612&w=0&k=20&c=-Xh51MIAPG5bXm9oW9BnaFcAu5gQWkO97wM6dEWJx-c=",
        description: "Playful Scottish kitten with a round face, loves to explore."
    },
    {
        id: "kitten2",
        name: "Persian Kitten (Ginger)",
        breed: "Persian",
        age: 0.33,
        price: 9000,
        gender: "Female",
        image: "https://media.istockphoto.com/id/172762572/photo/little-cat-and-iguana.jpg?s=612x612&w=0&k=20&c=y6U2_Fow3gk_TgUe6X64PsIyXbsw27TeeqGVRNU8XwE=",
        description: "Fluffy ginger Persian kitten, loves cuddles."
    },
    {
        id: "kitten3",
        name: "Persian Kitten (Black)",
        breed: "Persian",
        age: 0.33,
        price: 9000,
        gender: "Male",
        image: "https://media.istockphoto.com/id/153718480/photo/persian-kitten.jpg?s=612x612&w=0&k=20&c=B9CaCd1FgcHqlboDq4nHicvNNgRDtJPvtwT_TKwR62c=",
        description: "Sleek black Persian kitten, curious and playful."
    },
    {
        id: "kitten4",
        name: "Maine Coon Kitten",
        breed: "Maine Coon",
        age: 0.33,
        price: 9000,
        gender: "Female",
        image: "https://img.freepik.com/premium-photo/maine-coon-kitten_87557-6063.jpg?ga=GA1.1.814272209.1745042540&semt=ais_hybrid&w=740",
        description: "Majestic Maine Coon kitten, friendly and adventurous."
    },
    {
        id: "kitten5",
        name: "Siamese Kitten",
        breed: "Siamese",
        age: 0.42,
        price: 6000,
        gender: "Male",
        image: "https://media.istockphoto.com/id/173849569/photo/cute-kittens-series.jpg?s=612x612&w=0&k=20&c=aCC9CpHQnn-Z-C6-DSWH8gLBsrDGHmXvEufeAxIJI8U=",
        description: "Vocal Siamese kitten, loves attention and playtime."
    },
    {
        id: "kitten6",
        name: "Persian Kitten (Bi)",
        breed: "Persian",
        age: 0.25,
        price: 8500,
        gender: "Female",
        image: "https://img.freepik.com/premium-photo/persian-kitten_87557-11231.jpg?ga=GA1.1.814272209.1745042540&semt=ais_hybrid&w=740",
        description: "Bi-colored Persian kitten, gentle and affectionate."
    },
    {
        id: "kitten7",
        name: "Persian Kitten (White)",
        breed: "Persian",
        age: 0.17,
        price: 7000,
        gender: "Male",
        image: "https://img.freepik.com/premium-photo/persian-cat-sitting-front-white-background_191971-3637.jpg?ga=GA1.1.814272209.1745042540&semt=ais_hybrid&w=740",
        description: "Snow-white Persian kitten, calm and loving."
    },
    {
        id: "kitten8",
        name: "Persian Kitten (Brown)",
        breed: "Persian",
        age: 0.25,
        price: 9500,
        gender: "Female",
        image: "https://img.freepik.com/premium-photo/persian-kitten_87557-8810.jpg?ga=GA1.1.814272209.1745042540&semt=ais_hybrid&w=740",
        description: "Warm brown Persian kitten, enjoys quiet naps."
    },
    {
        id: "kitten9",
        name: "Persian Kitten (Fawn)",
        breed: "Persian",
        age: 0.33,
        price: 12000,
        gender: "Male",
        image: "https://media.istockphoto.com/id/172365735/photo/sweeter-than-candy.jpg?s=612x612&w=0&k=20&c=1Ve93wESzFc6z7L_W3EoWL5Xtk8qKSxI-SFhj9KRVmY=",
        description: "Fawn-colored Persian kitten, playful and charming."
    },
    {
        id: "kitten10",
        name: "Persian Kitten",
        breed: "Persian Kitten(Colico)",
        age: 0.25,
        price: 10000,
        gender: "Female",
        image: "https://encrypted-tbn0.gstatic.com/images?q=cat-tree.jpg",
        description: "Relaxed Ragdoll kitten, loves to be held."
    },
    {
        id: "kitten11",
        name: "Persian Kitten(Grey)",
        breed: "Persian",
        age: 0.33,
        price: 11000,
        gender: "Male",
        image: "https://media.istockphoto.com/id/1179913641/photo/cute-little-kitten.jpg?s=612x612&w=0&k=20&c=_3JhzRoz3SwlJAr3vQxUxmQQ9er7pCaJlO9S3A_NgxI=",
        description: "Energetic Bengal kitten with a spotted coat."
    },
    {
        id: "kitten12",
        name: "Persian Kitten(Himalian)",
        breed: "Persian",
        age: 0.42,
        price: 15000,
        gender: "Female",
        image: "https://media.istockphoto.com/id/173696537/photo/blue-eyes.jpg?s=612x612&w=0&k=20&c=w_I0PAqrfRQJ-mFHuXeMbs85UmScooUeoaMp2QoY6oQ=",
        description: "Unique hairless Sphynx kitten, affectionate and curious."
    }
];

// Function to add item to cart
function addKittenToCart(itemId) {
    try {
        const item = kittensData.find(kitten => kitten.id === itemId);
        if (!item) {
            console.error(`Kitten with ID ${itemId} not found`);
            return;
        }
        addToCart(item); // Use cart.js's addToCart
        document.dispatchEvent(new Event('cartUpdated')); // Trigger cart update
    } catch (error) {
        console.error('Error adding kitten to cart:', error);
    }
}

// Function to render kittens
function renderKittens(kittens) {
    const kittensGrid = document.getElementById('kittensGrid');
    kittensGrid.innerHTML = '';
    kittens.forEach(kitten => {
        const kittenDiv = document.createElement('div');
        kittenDiv.className = 'kitten';
        kittenDiv.innerHTML = `
            <a href="product-details.html?id=${kitten.id}">
                <img src="${kitten.image}" alt="${kitten.name}">
            </a>
            <h3>${kitten.name}</h3>
            <p>Age: ${(kitten.age * 12).toFixed(0)} months | Gender: ${kitten.gender} | Adoption Fee: ${kitten.price} Rs</p>
            <button class="shop-btn" onclick="addKittenToCart('${kitten.id}')">Add to Cart</button>
        `;
        kittensGrid.appendChild(kittenDiv);
    });
}

// Function to filter kittens
function filterKittens() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const breedFilter = document.getElementById('breedFilter').value;
    const ageFilter = document.getElementById('ageFilter').value;
    const priceFilter = document.getElementById('priceFilter').value;
    const genderFilter = document.getElementById('genderFilter').value;

    let filteredKittens = kittensData;

    // Apply search filter
    if (searchInput) {
        filteredKittens = filteredKittens.filter(kitten =>
            kitten.name.toLowerCase().includes(searchInput) ||
            kitten.breed.toLowerCase().includes(searchInput)
        );
    }

    // Apply breed filter
    if (breedFilter !== 'All') {
        filteredKittens = filteredKittens.filter(kitten => kitten.breed === breedFilter);
    }

    // Apply age filter
    if (ageFilter !== 'All') {
        const [minAge, maxAge] = ageFilter.split('-').map(Number);
        filteredKittens = filteredKittens.filter(kitten => kitten.age >= minAge && kitten.age <= maxAge);
    }

    // Apply price filter
    if (priceFilter !== 'All') {
        const [minPrice, maxPrice] = priceFilter.split('-').map(Number);
        filteredKittens = filteredKittens.filter(kitten => kitten.price >= minPrice && kitten.price <= maxPrice);
    }

    // Apply gender filter
    if (genderFilter !== 'All') {
        filteredKittens = filteredKittens.filter(kitten => kitten.gender === genderFilter);
    }

    renderKittens(filteredKittens);
}

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

// Event listeners for filters and search
document.getElementById('searchInput').addEventListener('input', filterKittens);
document.getElementById('breedFilter').addEventListener('change', filterKittens);
document.getElementById('ageFilter').addEventListener('change', filterKittens);
document.getElementById('priceFilter').addEventListener('change', filterKittens);
document.getElementById('genderFilter').addEventListener('change', filterKittens);

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

// Initial render and cart count update
window.addEventListener('load', () => {
    renderKittens(kittensData);
    updateCartCount();
});