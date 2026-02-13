// Data for products (aligned with accessories.html)
const productsData = [
    // Cats
    {
        id: "adult1",
        type: "cat",
        name: "Scottish Adult",
        breed: "Scottish",
        age: 2,
        price: 18000,
        image: "https://media.istockphoto.com/id/1795356989/photo/british-shorthair-cat-lies-on-a-white-background-and-looks-into-the-camera.jpg?s=612x612&w=0&k=20&c=BJyuLFRZCkrtJoJ0uc9cBHVau4Gx4Va04N8XSzyAmIs=",
        description: "A friendly Scottish cat with a calm temperament, perfect for families."
    },
    {
        id: "adult2",
        type: "cat",
        name: "Persian Adult(White)",
        breed: "Persian",
        age: 3,
        price: 8000,
        image: "https://media.istockphoto.com/id/1131383991/photo/white-persian-cat-three-months-ago.jpg?s=612x612&w=0&k=20&c=6uVX39ln3HukbekLFpsnsRUjhSNHe4SlPEcZ9TMalZY=",
        description: "Elegant white Persian with a luxurious coat, loves to cuddle."
    },
    {
        id: "adult3",
        type: "cat",
        name: "Persian Adult(Black)",
        breed: "Persian",
        age: 2.5,
        price: 8500,
        image: "https://media.istockphoto.com/id/1250692028/photo/black-persian-domestic-cat-adult-sitting-against-white-background.jpg?s=612x612&w=0&k=20&c=wD1ClGBU_Hrgy2SIw62L3r5LJPrsNS905Kk7akIJDeE=",
        description: "Sleek black Persian, affectionate and playful."
    },
    {
        id: "adult4",
        type: "cat",
        name: "Persian Adult(Brown)",
        breed: "Persian",
        age: 4,
        price: 9000,
        image: "https://media.istockphoto.com/id/685891408/photo/fluffy-british-longhair-cat-isolated-on-a-white-background.jpg?s=612x612&w=0&k=20&c=AuQxJ8wK4Z0-1Q5j9sWR4CrwGsAGe-SxMMOGqakMlc4=",
        description: "Warm brown Persian, enjoys quiet environments."
    },
    {
        id: "adult5",
        type: "cat",
        name: "Persian Adult(Fawn)",
        breed: "Persian",
        age: 3.5,
        price: 9500,
        image: "https://media.istockphoto.com/id/1137939659/photo/persian-cat-lying-in-front-of-white-background.jpg?s=612x612&w=0&k=20&c=Cs7afJADV4FFgVsrU7AiSJbjsSb1GqIK65k_2GKoOqk=",
        description: "Fawn-colored Persian, gentle and loving."
    },
    {
        id: "adult6",
        type: "cat",
        name: "Siamese Adult",
        breed: "Siamese",
        age: 4,
        price: 12000,
        image: "https://media.istockphoto.com/id/146960014/photo/siamese-kitten-sitting-on-a-white-background.jpg?s=612x612&w=0&k=20&c=qEBxswCvRMjAiQ0lZMtd1V1x1xgggeWRj55ZbsUlb0c=",
        description: "Vocal and active Siamese, loves attention."
    },
    {
        id: "adult7",
        type: "cat",
        name: "Persian Adult",
        breed: "Persian",
        age: 3,
        price: 15000,
        image: "https://media.istockphoto.com/id/467299620/photo/cute-3-month-old-persian-seal-colourpoint-kitten-is-lying.jpg?s=612x612&w=0&k=20&c=GRrtpCiL4TvNxGV7o9GZWutGsvBsE3A_YuN-PnWrRIU=",
        description: "Classic Persian with a sweet personality."
    },
    {
        id: "adult8",
        type: "cat",
        name: "Persian Adult(Ginger)",
        breed: "Persian",
        age: 2.5,
        price: 11000,
        image: "https://media.istockphoto.com/id/123176193/photo/persian-cat.jpg?s=612x612&w=0&k=20&c=YCTtIpP29p77S2KXRGM7nCxAKtNtz2zSQTCtwWTJbLU=",
        description: "Vibrant ginger Persian, full of charm."
    },
    {
        id: "adult9",
        type: "cat",
        name: "Persian Adult(Calico)",
        breed: "Persian",
        age: 3.5,
        price: 13000,
        image: "https://media.istockphoto.com/id/510066770/photo/persian-kitten-3-months-old-standing.jpg?s=612x612&w=0&k=20&c=EckxCEF-gOUpLIBgqHaX2RcMxF2X0a3HHho9pBlka8I=",
        description: "Unique calico Persian, loves to play."
    },
    {
        id: "adult10",
        type: "cat",
        name: "Persian Adult(Bi)",
        breed: "Persian",
        age: 4,
        price: 16000,
        image: "https://encrypted-tbn0.gstatic.com/images?q=cat-tree.jpg",
        description: "Bi-colored Persian, elegant and poised."
    },
    {
        id: "adult11",
        type: "cat",
        name: "Persian Adult(Grey)",
        breed: "Persian",
        age: 2,
        price: 9000,
        image: "https://www.omlet.co.uk/images/cache/512/341/persian-smoke-cat-against-white-background.jpg",
        description: "Grey Persian with a soft, fluffy coat."
    },
    {
        id: "adult12",
        type: "cat",
        name: "Persian Adult(Tabby)",
        breed: "Persian",
        age: 3,
        price: 17000,
        image: "https://st2.depositphotos.com/1004199/7997/i/450/depositphotos_79979698-stock-photo-persian-kitten-in-front-of.jpg",
        description: "Tabby-patterned Persian, energetic and curious."
    },
    // Kittens
    {
        id: "kitten1",
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
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
        type: "kitten",
        name: "Persian Kitten",
        breed: "Persian",
        age: 0.25,
        price: 10000,
        gender: "Female",
        image: "https://encrypted-tbn0.gstatic.com/images?q=cat-tree.jpg",
        description: "Relaxed Persian kitten, loves to be held."
    },
    {
        id: "kitten11",
        type: "kitten",
        name: "Persian Kitten(Grey)",
        breed: "Persian",
        age: 0.33,
        price: 11000,
        gender: "Male",
        image: "https://media.istockphoto.com/id/1179913641/photo/cute-little-kitten.jpg?s=612x612&w=0&k=20&c=_3JhzRoz3SwlJAr3vQxUxmQQ9er7pCaJlO9S3A_NgxI=",
        description: "Energetic Persian kitten with a soft grey coat."
    },
    {
        id: "kitten12",
        type: "kitten",
        name: "Persian Kitten(Himalayan)",
        breed: "Persian",
        age: 0.42,
        price: 15000,
        gender: "Female",
        image: "https://media.istockphoto.com/id/173696537/photo/blue-eyes.jpg?s=612x612&w=0&k=20&c=w_I0PAqrfRQJ-mFHuXeMbs85UmScooUeoaMp2QoY6oQ=",
        description: "Unique Himalayan Persian kitten, affectionate and curious."
    },
    // Food
    {
        id: "food1",
        type: "food",
        name: "Reflex",
        brand: "Reflex",
        ageGroup: "Adult",
        price: 1500,
        image: "https://www.petline.com.hk/wp-content/uploads/8698995028868.jpg",
        description: "Nutritious dry food for adult cats, rich in protein."
    },
    {
        id: "food2",
        type: "food",
        name: "Moggy",
        brand: "Moggy",
        ageGroup: "Kitten",
        price: 1200,
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzHuQATy8sqD3pWQvDTvjIcc6T2inbF75coQ&s",
        description: "Chicken and rice dry food, perfect for adult cats."
    },
    {
        id: "food3",
        type: "food",
        name: "Pawfect",
        brand: "Pawfect",
        ageGroup: "Adult",
        price: 1800,
        image: "https://petsone.pk/wp-content/uploads/2024/01/Pawfect-Adult-Cat-Food-1.2-KG.jpg",
        description: "Balanced dry food for active adult cats."
    },
    {
        id: "food4",
        type: "food",
        name: "Fluffy Food",
        brand: "Fluffy Food",
        ageGroup: "Adult",
        price: 1600,
        image: "https://petcity.pt/19566-large_default/fluffy-cat-adult.jpg",
        description: "Premium dry food with essential nutrients for adults."
    },
    {
        id: "food5",
        type: "food",
        name: "Royal Canin",
        brand: "Royal Canin",
        ageGroup: "Adult",
        price: 2000,
        image: "https://encrypted-tbn0.gstatic.com/images?q=tbn&usqp=CAU",
        description: "High-quality dry food for adult cat health."
    },
    {
        id: "food6",
        type: "food",
        name: "Reflex Plus",
        brand: "Reflex",
        ageGroup: "Adult",
        price: 1700,
        image: "https://petcollectives.com/cdn/shop/products/Untitled-1-01_da2c4d3b-5352-4b30-bb22-938ca5bfc3e2.jpg?v=1694682082",
        description: "Enhanced dry food formula for adult cats."
    },
    {
        id: "food7",
        type: "food",
        name: "Moggy Adult",
        brand: "Moggy",
        ageGroup: "Adult",
        price: 1300,
        image: "https://encrypted-tbn0.gstatic.com/images?q=cat-food.jpg",
        description: "Affordable dry food for adult cats, great taste."
    },
    {
        id: "food8",
        type: "food",
        name: "Pawi Kitten",
        brand: "Pawfect",
        ageGroup: "Kitten",
        price: 1900,
        image: "https://happytailspk.com/wp-content/uploads/2024/02/pawfectkitten_1a405db4-85e9-48ca-8462-90a692f8353b.webp",
        description: "Special dry food for growing kittens."
    },
    {
        id: "food9",
        type: "food",
        name: "Fluffy Senior",
        brand: "Fluffy Food",
        ageGroup: "Senior",
        price: 1650,
        image: "https://petcity.pt/19566-large_default/fluffy-cat-adult.jpg",
        description: "Gentle dry food for senior cats, easy to digest."
    },
    {
        id: "food10",
        type: "food",
        name: "Royal Canin Wet",
        brand: "Royal Canin",
        ageGroup: "Adult",
        price: 2100,
        image: "https://encrypted-tbn0.gstatic.com/images?q=wet-cat-food.jpg",
        description: "Delicious wet food for adult cats, high moisture."
    },
    {
        id: "food11",
        type: "food",
        name: "Reflex Treats",
        brand: "Reflex",
        ageGroup: "All",
        price: 1400,
        image: "https://encrypted-tbn0.gstatic.com/images?q=cat-treats.jpg",
        description: "Tasty treats for cats of all ages."
    },
    {
        id: "food12",
        type: "food",
        name: "Moggy Treats",
        brand: "Moggy",
        ageGroup: "All",
        price: 1250,
        image: "https://happytailspk.com/wp-content/uploads/2024/02/pawfectkitten_1a405db4-85e9-48ca-8462-90a692f8353b.webp",
        description: "Crunchy treats to reward your cat."
    },
    // Accessories
    {
        id: "accessory1",
        type: "accessory",
        name: "Cat Toy",
        breed: null,
        age: null,
        price: 100,
        image: "https://img.freepik.com/premium-photo/cat-toys-isolated-white-background-studio-shot_93675-160818.jpg",
        description: "Colorful fabric toy to keep your cat entertained."
    },
    {
        id: "accessory2",
        type: "accessory",
        name: "Cat Food Bowl",
        breed: null,
        age: null,
        price: 1500,
        image: "https://media.istockphoto.com/id/174824044/photo/empty-dog-dish-on-white.jpg?s=612x612&w=0&k=20&c=UoW9IoVj9B6bcXurBZhdjltY1yCf5efVhNpn85YAdvc=",
        description: "Durable metal food bowl for daily use."
    },
    {
        id: "accessory3",
        type: "accessory",
        name: "Cat Litter Box",
        breed: null,
        age: null,
        price: 2000,
        image: "https://media.istockphoto.com/id/1345625430/photo/cats-litter-box-with-wooden-filler-and-scoop-isolated-on-white.jpg?s=612x612&w=0&k=20&c=NEQcmqzaPTEtd9jpzqZ7Zod8cSC4b9Z_JghL-jnoCzA=",
        description: "Spacious plastic litter box for easy cleaning."
    },
    {
        id: "accessory4",
        type: "accessory",
        name: "Cat Carrier",
        breed: null,
        age: null,
        price: 2500,
        image: "https://media.istockphoto.com/id/2149626457/photo/pet-travel-plastic-carrier-isolated-on-white.jpg?s=612x612&w=0&k=20&c=mvUBOJ0jujIZ_bUj4AcCVknkLYILg_EGUX6E8hAu_ek=",
        description: "Sturdy plastic carrier for safe travel."
    },
    {
        id: "accessory5",
        type: "accessory",
        name: "Cat Bed",
        breed: null,
        age: null,
        price: 2500,
        image: "https://media.istockphoto.com/id/488640434/photo/pet-bed.jpg?s=612x612&w=0&k=20&c=uNd3EBKVNMdzt_OMGQ6VjaAV0YfCiC0PJ2s6ml-DW4k=",
        description: "Cozy fabric bed for your cat's comfort."
    },
    {
        id: "accessory6",
        type: "accessory",
        name: "Scratching Post",
        breed: null,
        age: null,
        price: 1800,
        image: "https://thumbs.dreamstime.com/b/isolated-cat-scratching-post-rope-sisal-pink-ball-toy-white-background-182639000.jpg",
        description: "Wooden scratching post with a playful ball."
    },
    {
        id: "accessory7",
        type: "accessory",
        name: "Cat Collar",
        breed: null,
        age: null,
        price: 300,
        image: "https://media.istockphoto.com/id/1199865571/photo/pink-cat-collar-on-white.jpg?s=612x612&w=0&k=20&c=ADocm091lcs-u4RL30r8jdiC_EQMdx61mjhaWsJHff4=",
        description: "Adjustable fabric collar for cats."
    },
    {
        id: "accessory8",
        type: "accessory",
        name: "Water Fountain",
        breed: null,
        age: null,
        price: 3500,
        image: "https://media.istockphoto.com/id/1850538469/photo/electric-water-dispenser-for-pets-fountain-drinker-for-cats-isolated-on-white-background.jpg?s=612x612&w=0&k=20&c=qlKCgbXG5LwKe6rPVQAtR0xW9fn3m8WzU9tn7VVJhLI=",
        description: "Electric water fountain for fresh drinking water."
    },
    {
        id: "accessory9",
        type: "accessory",
        name: "Grooming Brush",
        breed: null,
        age: null,
        price: 500,
        image: "https://media.istockphoto.com/id/1454418688/photo/plastic-brush-and-combing-out-wool-in-animals-and-a-tuft-of-gray-cat-hair-on-a-white.jpg?s=612x612&w=0&k=20&c=rFrLP_3Q7bBlh2T7RygZIc3fFlVcLnzYC20Xe_cmI7s=",
        description: "Gentle plastic brush for cat grooming."
    },
    {
        id: "accessory10",
        type: "accessory",
        name: "Cat Tree",
        breed: null,
        age: null,
        price: 4500,
        image: "https://encrypted-tbn0.gstatic.com/images?q=cat-tree.jpg",
        description: "Multi-level wooden cat tree for climbing and play."
    },
    {
        id: "accessory11",
        type: "accessory",
        name: "Cat Leash",
        breed: null,
        age: null,
        price: 400,
        image: "https://www.doghouse.co.uk/cdn/shop/files/bluemoonruffwearleash.png?v=1738581253",
        description: "Comfortable fabric leash for outdoor walks."
    },
    {
        id: "accessory12",
        type: "accessory",
        name: "Cat Tunnel",
        breed: null,
        age: null,
        price: 1200,
        image: "https://bestfriend.com/cdn/shop/products/25b2967732ebd6f65b5cc70d88edf89e_600x.jpg?v=1632393357",
        description: "Fun fabric tunnel for cats to explore."
    }
];

// Function to load product details
function loadProductDetails() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        const product = productsData.find(p => p.id === productId);

        if (product) {
            document.getElementById('productImage').src = product.image;
            document.getElementById('productImage').alt = product.name;
            document.getElementById('productName').textContent = product.name;
            document.getElementById('productPrice').textContent = `Rs ${product.price.toLocaleString()}`;
            document.getElementById('productDescription').textContent = product.description;
            document.getElementById('productAge').textContent = product.age ? `Age: ${product.age < 1 ? (product.age * 12).toFixed(0) + ' months' : product.age + ' years'}` : '';
            document.getElementById('productBreed').textContent = product.breed ? `Breed: ${product.breed}` : '';
            document.getElementById('productType').textContent = `Category: ${product.type.charAt(0).toUpperCase() + product.type.slice(1)}`;
        } else {
            document.querySelector('.product-details').innerHTML = '<p>Product not found.</p>';
        }
    } catch (error) {
        console.error('Error loading product details:', error);
    }
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

// Handle add to cart button
document.querySelector('.add-to-cart-btn').addEventListener('click', function() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        const product = productsData.find(p => p.id === productId);
        if (!product) {
            console.error(`Product with ID ${productId} not found`);
            return;
        }
        addToCart(product);
        document.dispatchEvent(new Event('cartUpdated'));
        toggleCartPanel(true); // Open the cart panel after adding
    } catch (error) {
        console.error('Error adding product to cart:', error);
    }
});

// Handle review submission
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    try {
        const reviewText = this.querySelector('textarea').value;
        const rating = this.querySelector('input[type="number"]').value;
        const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
        const reviewDiv = document.createElement('div');
        reviewDiv.className = 'review';
        reviewDiv.innerHTML = `<span class="stars">${stars}</span> "${reviewText}" - Anonymous`;
        document.getElementById('reviewsList').appendChild(reviewDiv);
        this.reset();
    } catch (error) {
        console.error('Error submitting review:', error);
    }
});

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

// Load product details and update cart count on page load
window.addEventListener('load', () => {
    loadProductDetails();
    updateCartCount();
});