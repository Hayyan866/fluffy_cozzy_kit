// Elements for admin panel
const productForm = document.getElementById('product-form');
const ordersList = document.getElementById('orders-list');
const inquiriesList = document.getElementById('inquiries-list');
const usersList = document.getElementById('users-list');

// Simulated data
let products = JSON.parse(localStorage.getItem('products')) || [];
let orders = JSON.parse(localStorage.getItem('orders')) || [
    { id: '1234', status: 'Pending', date: '2025-04-20' },
    { id: '5678', status: 'Shipped', date: '2025-04-18' }
];
let inquiries = [
    { id: 'User123', text: 'When will my order ship?', date: '2025-04-20' },
    { id: 'User456', text: 'Is this cat food grain-free?', date: '2025-04-19' }
];
let users = JSON.parse(localStorage.getItem('users')) || [
    { name: 'John Doe', email: 'john.doe@example.com' },
    { name: 'Jane Smith', email: 'jane.smith@example.com' }
];

// Function to save data to localStorage
function saveData() {
    localStorage.setItem('products', JSON.stringify(products));
    localStorage.setItem('orders', JSON.stringify(orders));
    localStorage.setItem('users', JSON.stringify(users));
}

// Function to check if user is admin
function checkAdminAccess() {
    const loggedInUserEmail = localStorage.getItem('loggedInUserEmail');
    // Hardcoded admin email for simplicity (aligned with account.js users array)
    const adminEmails = ['admin@example.com']; // Replace with actual admin emails
    if (!loggedInUserEmail || !adminEmails.includes(loggedInUserEmail)) {
        alert('Access denied. You must be an admin to view this page.');
        window.location.href = 'account.html';
        return false;
    }
    return true;
}

// Function to render orders
function renderOrders() {
    ordersList.innerHTML = orders.map(order => `
        <p>Order #${order.id} - ${order.status} (Placed on ${order.date}) 
            <button onclick="updateOrderStatus('${order.id}')">Update Status</button>
        </p>
    `).join('');
}

// Function to render inquiries
function renderInquiries() {
    inquiriesList.innerHTML = inquiries.map(inquiry => `
        <p>"${inquiry.text}" - ${inquiry.id} 
            <button onclick="replyInquiry('${inquiry.id}')">Reply</button>
        </p>
    `).join('');
}

// Function to render users
function renderUsers() {
    usersList.innerHTML = users.map(user => `
        <p>${user.name} - ${user.email}</p>
    `).join('');
}

// Function to handle product form submission
productForm.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!checkAdminAccess()) return;

    const name = document.getElementById('product-name').value;
    const category = document.getElementById('category').value;
    const price = parseFloat(document.getElementById('price').value);
    const description = document.getElementById('description').value;
    const imageInput = document.getElementById('product-image');
    const image = imageInput.files[0] ? URL.createObjectURL(imageInput.files[0]) : 'placeholder.png';

    const product = {
        id: Date.now().toString(), // Simple unique ID
        name,
        category,
        price,
        description,
        image
    };

    products.push(product);
    saveData();
    alert('Product added successfully!');
    productForm.reset();
});

// Function to update order status
window.updateOrderStatus = function(orderId) {
    if (!checkAdminAccess()) return;

    const order = orders.find(o => o.id === orderId);
    if (order) {
        const newStatus = prompt('Enter new status (Pending/Shipped/Delivered):', order.status);
        if (newStatus && ['Pending', 'Shipped', 'Delivered'].includes(newStatus)) {
            order.status = newStatus;
            if (newStatus === 'Delivered') {
                order.deliveredDate = new Date().toISOString().split('T')[0];
            }
            saveData();
            renderOrders();
            alert(`Order #${orderId} status updated to ${newStatus}`);
        } else {
            alert('Invalid status. Please use Pending, Shipped, or Delivered.');
        }
    }
};

// Function to reply to inquiries
window.replyInquiry = function(userId) {
    if (!checkAdminAccess()) return;

    const inquiry = inquiries.find(i => i.id === userId);
    if (inquiry) {
        const reply = prompt(`Replying to ${userId}: "${inquiry.text}"\nEnter your reply:`);
        if (reply) {
            inquiries = inquiries.filter(i => i.id !== userId); // Remove inquiry after replying
            saveData();
            renderInquiries();
            alert(`Reply sent to ${userId}: "${reply}"`);
        }
    }
};

// Initial setup
document.addEventListener('DOMContentLoaded', () => {
    // Check admin access
    if (!checkAdminAccess()) return;

    // Render admin data
    renderOrders();
    renderInquiries();
    renderUsers();
});