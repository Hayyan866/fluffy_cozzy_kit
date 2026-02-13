// Data for accessories
const accessoriesData = [
    {
        id: "accessory1",
        name: "Cat Toy",
        type: "Toy",
        price: 100,
        image: "https://img.freepik.com/premium-photo/cat-toys-isolated-white-background-studio-shot_93675-160818.jpg",
        description: "Colorful fabric toy to keep your cat entertained."
    },
    {
        id: "accessory2",
        name: "Cat Food Bowl",
        type: "Food Bowl",
        price: 1500,
        image: "https://media.istockphoto.com/id/174824044/photo/empty-dog-dish-on-white.jpg?s=612x612&w=0&k=20&c=UoW9IoVj9B6bcXurBZhdjltY1yCf5efVhNpn85YAdvc=",
        description: "Durable metal food bowl for daily use."
    },
    {
        id: "accessory3",
        name: "Cat Litter Box",
        type: "Litter Box",
        price: 2000,
        image: "https://media.istockphoto.com/id/1345625430/photo/cats-litter-box-with-wooden-filler-and-scoop-isolated-on-white.jpg?s=612x612&w=0&k=20&c=NEQcmqzaPTEtd9jpzqZ7Zod8cSC4b9Z_JghL-jnoCzA=",
        description: "Spacious plastic litter box for easy cleaning."
    },
    {
        id: "accessory4",
        name: "Cat Carrier",
        type: "Carrier",
        price: 2500,
        image: "https://media.istockphoto.com/id/2149626457/photo/pet-travel-plastic-carrier-isolated-on-white.jpg?s=612x612&w=0&k=20&c=mvUBOJ0jujIZ_bUj4AcCVknkLYILg_EGUX6E8hAu_ek=",
        description: "Sturdy plastic carrier for safe travel."
    },
    {
        id: "accessory5",
        name: "Cat Bed",
        type: "Bed",
        price: 2500,
        image: "https://media.istockphoto.com/id/488640434/photo/pet-bed.jpg?s=612x612&w=0&k=20&c=uNd3EBKVNMdzt_OMGQ6VjaAV0YfCiC0PJ2s6ml-DW4k=",
        description: "Cozy fabric bed for your cat's comfort."
    },
    {
        id: "accessory6",
        name: "Scratching Post",
        type: "Scratching Post",
        price: 1800,
        image: "https://thumbs.dreamstime.com/b/isolated-cat-scratching-post-rope-sisal-pink-ball-toy-white-background-182639000.jpg",
        description: "Wooden scratching post with a playful ball."
    },
    {
        id: "accessory7",
        name: "Cat Collar",
        type: "Collar",
        price: 300,
        image: "https://media.istockphoto.com/id/1199865571/photo/pink-cat-collar-on-white.jpg?s=612x612&w=0&k=20&c=ADocm091lcs-u4RL30r8jdiC_EQMdx61mjhaWsJHff4=",
        description: "Adjustable fabric collar for cats."
    },
    {
        id: "accessory8",
        name: "Water Fountain",
        type: "Water Fountain",
        price: 3500,
        image: "https://media.istockphoto.com/id/1850538469/photo/electric-water-dispenser-for-pets-fountain-drinker-for-cats-isolated-on-white-background.jpg?s=612x612&w=0&k=20&c=qlKCgbXG5LwKe6rPVQAtR0xW9fn3m8WzU9tn7VVJhLI=",
        description: "Electric water fountain for fresh drinking water."
    },
    {
        id: "accessory9",
        name: "Grooming Brush",
        type: "Grooming Brush",
        price: 500,
        image: "https://media.istockphoto.com/id/1454418688/photo/plastic-brush-and-combing-out-wool-in-animals-and-a-tuft-of-gray-cat-hair-on-a-white.jpg?s=612x612&w=0&k=20&c=rFrLP_3Q7bBlh2T7RygZIc3fFlVcLnzYC20Xe_cmI7s=",
        description: "Gentle plastic brush for cat grooming."
    },
    {
        id: "accessory10",
        name: "Cat Tree",
        type: "Cat Tree",
        price: 4500,
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRpaAf1prxlu9O24bz1c23Xj3CCauEGUqkkJA&s",
        description: "Multi-level wooden cat tree for climbing and play."
    },
    {
        id: "accessory11",
        name: "Cat Leash",
        type: "Leash",
        price: 400,
        image: "https://www.doghouse.co.uk/cdn/shop/files/bluemoonruffwearleash.png?v=1738581253",
        description: "Comfortable fabric leash for outdoor walks."
    },
    {
        id: "accessory12",
        name: "Cat Tunnel",
        type: "Tunnel",
        price: 1200,
        image: "https://bestfriend.com/cdn/shop/products/25b2967732ebd6f65b5cc70d88edf89e_600x.jpg?v=1632393357",
        description: "Fun fabric tunnel for cats to explore."
    }
];

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

// Function to add item to cart
function addAccessoryToCart(itemId) {
    try {
        const item = accessoriesData.find(accessory => accessory.id === itemId);
        if (!item) {
            console.error(`Accessory with ID ${itemId} not found`);
            return;
        }
        addToCart(item); // Use cart.js's addToCart
    } catch (error) {
        console.error('Error adding item to cart:', error);
    }
}

// Function to render accessories
function renderAccessories(accessories) {
    const accessoriesGrid = document.getElementById('accessoriesGrid');
    accessoriesGrid.innerHTML = '';
    accessories.forEach(item => {
        const accessoryDiv = document.createElement('div');
        accessoryDiv.className = 'accessory';
        accessoryDiv.innerHTML = `
            <a href="product-details.html?id=${item.id}">
                <img src="${item.image}" alt="${item.name}">
            </a>
            <h3>${item.name}</h3>
            <p>Price: ${item.price} Rs</p>
            <button class="shop-btn" onclick="addAccessoryToCart('${item.id}')">Add to Cart</button>
        `;
        accessoriesGrid.appendChild(accessoryDiv);
    });
}

// Function to filter accessories
function filterAccessories() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const priceFilter = document.getElementById('priceFilter').value;

    let filteredAccessories = accessoriesData;

    // Apply search filter
    if (searchInput) {
        filteredAccessories = filteredAccessories.filter(item =>
            item.name.toLowerCase().includes(searchInput) ||
            item.type.toLowerCase().includes(searchInput)
        );
    }

    // Apply type filter
    if (typeFilter !== 'All') {
        filteredAccessories = filteredAccessories.filter(item => item.type === typeFilter);
    }

    // Apply price filter
    if (priceFilter !== 'All') {
        const [minPrice, maxPrice] = priceFilter.split('-').map(Number);
        if (maxPrice) {
            filteredAccessories = filteredAccessories.filter(item => item.price >= minPrice && item.price <= maxPrice);
        } else {
            filteredAccessories = filteredAccessories.filter(item => item.price >= minPrice);
        }
    }

    renderAccessories(filteredAccessories);
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

// Event listeners for filters and search
document.getElementById('searchInput').addEventListener('input', filterAccessories);
document.getElementById('typeFilter').addEventListener('change', filterAccessories);
document.getElementById('priceFilter').addEventListener('change', filterAccessories);

// Initial render and cart count update
window.addEventListener('load', () => {
    renderAccessories(accessoriesData);
    updateCartCount();
});