<?php
// Include database connection
$db_host = 'localhost';
$db_user = 'root'; // Change to your database username
$db_password = ''; // Change to your database password
$db_name = 'cats_db';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
// session_start();
// if (!isset($_SESSION['admin_email'])) {
//     header("Location: admin_login.php");
//     exit;
// }

// Handle form submission for adding/editing products
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_product') {
        $category_id = $_POST['category_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $image_path = ''; // Will be set after file upload
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $upload_error = "Failed to upload image.";
            }
        }
        
        // Insert product into database
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, price, description, image_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $category_id, $name, $price, $description, $image_path);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            
            // Add product details
            if ($category_id == 1 || $category_id == 2) { // Adult Cats or Kittens
                $breed = $_POST['breed'];
                $age = $_POST['age'];
                $gender = $_POST['gender'];
                $weight = $_POST['weight'];
                $vaccination = $_POST['vaccination'];
                
                $keys = ['breed', 'age', 'gender', 'weight', 'vaccination'];
                $values = [$breed, $age, $gender, $weight, $vaccination];
                
                // Insert product details
                for ($i = 0; $i < count($keys); $i++) {
                    $stmt = $conn->prepare("INSERT INTO product_details (product_id, detail_key, detail_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $product_id, $keys[$i], $values[$i]);
                    $stmt->execute();
                }
            } elseif ($category_id == 3) { // Cat Food
                $brand = $_POST['brand'];
                $type = $_POST['type'];
                $weight = $_POST['weight'];
                $age_group = $_POST['age_group'];
                $ingredients = $_POST['ingredients'];
                
                $keys = ['brand', 'type', 'weight', 'age_group', 'ingredients'];
                $values = [$brand, $type, $weight, $age_group, $ingredients];
                
                // Insert product details
                for ($i = 0; $i < count($keys); $i++) {
                    $stmt = $conn->prepare("INSERT INTO product_details (product_id, detail_key, detail_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $product_id, $keys[$i], $values[$i]);
                    $stmt->execute();
                }
            } elseif ($category_id == 4) { // Accessories
                $type = $_POST['type'];
                $material = $_POST['material'];
                $size = $_POST['size'];
                $color = $_POST['color'];
                $brand = $_POST['brand'];
                
                $keys = ['type', 'material', 'size', 'color', 'brand'];
                $values = [$type, $material, $size, $color, $brand];
                
                // Insert product details
                for ($i = 0; $i < count($keys); $i++) {
                    $stmt = $conn->prepare("INSERT INTO product_details (product_id, detail_key, detail_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $product_id, $keys[$i], $values[$i]);
                    $stmt->execute();
                }
            }
            
            $success_message = "Product added successfully!";
        } else {
            $error_message = "Error adding product: " . $conn->error;
        }
    } elseif ($action == 'update_product') {
        $product_id = $_POST['product_id'];
        $category_id = $_POST['category_id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        
        // Handle image upload if there's a new image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Update image path in database
                $stmt = $conn->prepare("UPDATE products SET image_path = ? WHERE product_id = ?");
                $stmt->bind_param("si", $target_file, $product_id);
                $stmt->execute();
            } else {
                $upload_error = "Failed to upload image.";
            }
        }
        
        // Update product in database
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, description = ? WHERE product_id = ?");
        $stmt->bind_param("isdsi", $category_id, $name, $price, $description, $product_id);
        
        if ($stmt->execute()) {
            // Delete existing product details
            $delete_stmt = $conn->prepare("DELETE FROM product_details WHERE product_id = ?");
            $delete_stmt->bind_param("i", $product_id);
            $delete_stmt->execute();
            
            // Add updated product details
            if ($category_id == 1 || $category_id == 2) { // Adult Cats or Kittens
                $breed = $_POST['breed'];
                $age = $_POST['age'];
                $gender = $_POST['gender'];
                $weight = $_POST['weight'];
                $vaccination = $_POST['vaccination'];
                
                $keys = ['breed', 'age', 'gender', 'weight', 'vaccination'];
                $values = [$breed, $age, $gender, $weight, $vaccination];
                
                // Insert product details
                for ($i = 0; $i < count($keys); $i++) {
                    $stmt = $conn->prepare("INSERT INTO product_details (product_id, detail_key, detail_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $product_id, $keys[$i], $values[$i]);
                    $stmt->execute();
                }
            } elseif ($category_id == 3) { // Cat Food
                $brand = $_POST['brand'];
                $type = $_POST['type'];
                $weight = $_POST['weight'];
                $age_group = $_POST['age_group'];
                $ingredients = $_POST['ingredients'];
                
                $keys = ['brand', 'type', 'weight', 'age_group', 'ingredients'];
                $values = [$brand, $type, $weight, $age_group, $ingredients];
                
                // Insert product details
                for ($i = 0; $i < count($keys); $i++) {
                    $stmt = $conn->prepare("INSERT INTO product_details (product_id, detail_key, detail_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $product_id, $keys[$i], $values[$i]);
                    $stmt->execute();
                }
            } elseif ($category_id == 4) { // Accessories
                $type = $_POST['type'];
                $material = $_POST['material'];
                $size = $_POST['size'];
                $color = $_POST['color'];
                $brand = $_POST['brand'];
                
                $keys = ['type', 'material', 'size', 'color', 'brand'];
                $values = [$type, $material, $size, $color, $brand];
                
                // Insert product details
                for ($i = 0; $i < count($keys); $i++) {
                    $stmt = $conn->prepare("INSERT INTO product_details (product_id, detail_key, detail_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $product_id, $keys[$i], $values[$i]);
                    $stmt->execute();
                }
            }
            
            $success_message = "Product updated successfully!";
        } else {
            $error_message = "Error updating product: " . $conn->error;
        }
    } elseif ($action == 'delete_product') {
        $product_id = $_POST['product_id'];
        
        // Delete product details first
        $delete_details_stmt = $conn->prepare("DELETE FROM product_details WHERE product_id = ?");
        $delete_details_stmt->bind_param("i", $product_id);
        $delete_details_stmt->execute();
        
        // Delete product
        $delete_stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $delete_stmt->bind_param("i", $product_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Product deleted successfully!";
        } else {
            $error_message = "Error deleting product: " . $conn->error;
        }
    }
}

// Get all categories
$categories_query = "SELECT * FROM categories";
$categories_result = $conn->query($categories_query);
$categories = [];

if ($categories_result->num_rows > 0) {
    while ($category = $categories_result->fetch_assoc()) {
        $categories[] = $category;
    }
}

// Get all products with their details
$products_query = "SELECT p.*, c.name as category_name FROM products p 
                  JOIN categories c ON p.category_id = c.category_id 
                  ORDER BY p.product_id DESC";
$products_result = $conn->query($products_query);
$products = [];

if ($products_result && $products_result->num_rows > 0) {
    while ($product = $products_result->fetch_assoc()) {
        // Get product details
        $details_query = "SELECT detail_key, detail_value FROM product_details WHERE product_id = " . $product['product_id'];
        $details_result = $conn->query($details_query);
        $details = [];
        
        if ($details_result && $details_result->num_rows > 0) {
            while ($detail = $details_result->fetch_assoc()) {
                $details[$detail['detail_key']] = $detail['detail_value'];
            }
        }
        
        $product['details'] = $details;
        $products[] = $product;
    }
}

// Get product details for edit
$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = "SELECT p.*, c.name as category_name FROM products p 
                  JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.product_id = ?";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    
    if ($edit_result && $edit_result->num_rows > 0) {
        $edit_product = $edit_result->fetch_assoc();
        
        // Get product details
        $details_query = "SELECT detail_key, detail_value FROM product_details WHERE product_id = " . $edit_id;
        $details_result = $conn->query($details_query);
        $details = [];
        
        if ($details_result && $details_result->num_rows > 0) {
            while ($detail = $details_result->fetch_assoc()) {
                $details[$detail['detail_key']] = $detail['detail_value'];
            }
        }
        
        $edit_product['details'] = $details;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">Admin Dashboard</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="logout.php">Sign out</a>
            </div>
        </div>
    </header>
    
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_orders.php">
                                <i class="bi bi-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_customers.php">
                                <i class="bi bi-people"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_products.php">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                        
                    </ul>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Products Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-circle"></i> Add New Product
                        </button>
                    </div>
                </div>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Products Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No products found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['product_id']; ?></td>
                                        <td>
                                            <?php if ($product['image_path']): ?>
                                                <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $product['name']; ?></td>
                                        <td><?php echo $product['category_name']; ?></td>
                                        <td>PKR <?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <a href="admin_products.php?edit=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProductModal<?php echo $product['product_id']; ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                            
                                            <!-- Delete Confirmation Modal -->
                                            <div class="modal fade" id="deleteProductModal<?php echo $product['product_id']; ?>" tabindex="-1" aria-labelledby="deleteProductModalLabel<?php echo $product['product_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteProductModalLabel<?php echo $product['product_id']; ?>">Confirm Delete</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete <strong><?php echo $product['name']; ?></strong>? This action cannot be undone.
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form method="post" action="">
                                                                <input type="hidden" name="action" value="delete_product">
                                                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="" enctype="multipart/form-data" id="addProductForm">
                        <input type="hidden" name="action" value="add_product">
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id" required onchange="showFormFields()">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (PKR )</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        
                        <!-- Adult Cats & Kittens Form Fields -->
                        <div id="cat-fields" class="form-section">
                            <h4 class="mt-4">Cat Details</h4>
                            
                            <div class="mb-3">
                                <label for="breed" class="form-label">Breed</label>
                                <select class="form-select" id="breed" name="breed">
                                    <option value="Scottish">Scottish</option>
                                    <option value="Persian">Persian</option>
                                    <option value="Siamese">Siamese</option>
                                    <option value="Maine Coon">Maine Coon</option>
                                    <option value="Ragdoll">Ragdoll</option>
                                    <option value="Bengal">Bengal</option>
                                    <option value="Sphynx">Sphynx</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="text" class="form-control" id="age" name="age">
                            </div>
                            
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="weight" class="form-label">Weight</label>
                                <input type="text" class="form-control" id="weight" name="weight">
                            </div>
                            
                            <div class="mb-3">
                                <label for="vaccination" class="form-label">Vaccination Status</label>
                                <select class="form-select" id="vaccination" name="vaccination">
                                    <option value="Fully Vaccinated">Fully Vaccinated</option>
                                    <option value="Partially Vaccinated">Partially Vaccinated</option>
                                    <option value="Not Vaccinated">Not Vaccinated</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Cat Food Form Fields -->
                        <div id="food-fields" class="form-section">
                            <h4 class="mt-4">Food Details</h4>
                            
                            <div class="mb-3">
                                <label for="brand" class="form-label">Brand</label>
                                <select class="form-select" id="brand" name="brand">
                                    <option value="Reflex">Reflex</option>
                                    <option value="Moggy">Moggy</option>
                                    <option value="Pawfect">Pawfect</option>
                                    <option value="Fluffy Food">Fluffy Food</option>
                                    <option value="Royal Canin">Royal Canin</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="Dry">Dry</option>
                                    <option value="Wet">Wet</option>
                                    <option value="Treats">Treats</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="weight" class="form-label">Weight/Size</label>
                                <input type="text" class="form-control" id="weight" name="weight">
                            </div>
                            
                            <div class="mb-3">
                                <label for="age_group" class="form-label">Age Group</label>
                                <select class="form-select" id="age_group" name="age_group">
                                    <option value="Kitten">Kitten</option>
                                    <option value="Adult">Adult</option>
                                    <option value="Senior">Senior</option>
                                    <option value="All Ages">All Ages</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ingredients" class="form-label">Main Ingredients</label>
                                <textarea class="form-control" id="ingredients" name="ingredients" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <!-- Accessories Form Fields -->
                        <div id="accessory-fields" class="form-section">
                            <h4 class="mt-4">Accessory Details</h4>
                            
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="Toy">Toy</option>
                                    <option value="Bed">Bed</option>
                                    <option value="Carrier">Carrier</option>
                                    <option value="Litter Box">Litter Box</option>
                                    <option value="Food Bowl">Food Bowl</option>
                                    <option value="Scratching Post">Scratching Post</option>
                                    <option value="Collar">Collar</option>
                                    <option value="Water Fountain">Water Fountain</option>
                                    <option value="Grooming Brush">Grooming Brush</option>
                                    <option value="Cat Tree">Cat Tree</option>
                                    <option value="Leash">Leash</option>
                                    <option value="Tunnel">Tunnel</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="material" class="form-label">Material</label>
                                <input type="text" class="form-control" id="material" name="material">
                            </div>
                            <div class="mb-3">
                                <label for="size" class="form-label">Size</label>
                                <input type="text" class="form-control" id="size" name="size">
                            </div>
                            
                            <div class="mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color">
                            </div>
                            
                            <div class="mb-3">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand">
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <?php if ($edit_product): ?>
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="" enctype="multipart/form-data" id="editProductForm">
                        <input type="hidden" name="action" value="update_product">
                        <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
                        
                        <div class="mb-3">
                            <label for="edit_category_id" class="form-label">Category</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required onchange="showEditFormFields()">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ($edit_product['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo $edit_product['name']; ?>" required>
                        </div>
                        

                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Price (PKR )</label>
                            <input type="number" class="form-control" id="edit_price" name="price" step="0.01" value="<?php echo $edit_product['price']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"><?php echo $edit_product['description']; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Product Image</label>
                            <?php if ($edit_product['image_path']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo $edit_product['image_path']; ?>" alt="<?php echo $edit_product['name']; ?>" style="max-width: 150px; max-height: 150px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <small class="form-text text-muted">Leave empty to keep current image</small>
                        </div>
                        
                        <!-- Edit Adult Cats & Kittens Form Fields -->
                        <div id="edit-cat-fields" class="form-section <?php echo ($edit_product['category_id'] == 1 || $edit_product['category_id'] == 2) ? 'active' : ''; ?>">
                            <h4 class="mt-4">Cat Details</h4>
                            
                            <div class="mb-3">
                                <label for="edit_breed" class="form-label">Breed</label>
                                <select class="form-select" id="edit_breed" name="breed">
                                    <option value="Scottish" <?php echo (isset($edit_product['details']['breed']) && $edit_product['details']['breed'] == 'Scottish') ? 'selected' : ''; ?>>Scottish</option>
                                    <option value="Persian" <?php echo (isset($edit_product['details']['breed']) && $edit_product['details']['breed'] == 'Persian') ? 'selected' : ''; ?>>Persian</option>
                                    <option value="Siamese" <?php echo (isset($edit_product['details']['breed']) && $edit_product['details']['breed'] == 'Siamese') ? 'selected' : ''; ?>>Siamese</option>
                                    <option value="Maine Coon" <?php echo (isset($edit_product['details']['breed']) && $edit_product['details']['breed'] == 'Maine Coon') ? 'selected' : ''; ?>>Maine Coon</option>
                                    <option value="Ragdoll" <?php echo (isset($edit_product['details']['breed']) && $edit_product['details']['breed'] == 'Ragdoll') ? 'selected' : ''; ?>>Ragdoll</option>
                                    <option value="Bengal" <?php echo (isset($edit_product['details']['breed']) && $edit_product['details']['breed'] == 'Bengal') ? 'selected' : ''; ?>>Bengal</option>
                                    <option value="Sphynx" <?php echo (isset($edit_product['details']['breed']) && $edit_product['details']['breed'] == 'Sphynx') ? 'selected' : ''; ?>>Sphynx</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_age" class="form-label">Age</label>
                                <input type="text" class="form-control" id="edit_age" name="age" value="<?php echo isset($edit_product['details']['age']) ? $edit_product['details']['age'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_gender" class="form-label">Gender</label>
                                <select class="form-select" id="edit_gender" name="gender">
                                    <option value="Male" <?php echo (isset($edit_product['details']['gender']) && $edit_product['details']['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($edit_product['details']['gender']) && $edit_product['details']['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_weight" class="form-label">Weight</label>
                                <input type="text" class="form-control" id="edit_weight" name="weight" value="<?php echo isset($edit_product['details']['weight']) ? $edit_product['details']['weight'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_vaccination" class="form-label">Vaccination Status</label>
                                <select class="form-select" id="edit_vaccination" name="vaccination">
                                    <option value="Fully Vaccinated" <?php echo (isset($edit_product['details']['vaccination']) && $edit_product['details']['vaccination'] == 'Fully Vaccinated') ? 'selected' : ''; ?>>Fully Vaccinated</option>
                                    <option value="Partially Vaccinated" <?php echo (isset($edit_product['details']['vaccination']) && $edit_product['details']['vaccination'] == 'Partially Vaccinated') ? 'selected' : ''; ?>>Partially Vaccinated</option>
                                    <option value="Not Vaccinated" <?php echo (isset($edit_product['details']['vaccination']) && $edit_product['details']['vaccination'] == 'Not Vaccinated') ? 'selected' : ''; ?>>Not Vaccinated</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Edit Cat Food Form Fields -->
                        <div id="edit-food-fields" class="form-section <?php echo ($edit_product['category_id'] == 3) ? 'active' : ''; ?>">
                            <h4 class="mt-4">Food Details</h4>
                            
                            <div class="mb-3">
                                <label for="edit_brand" class="form-label">Brand</label>
                                <select class="form-select" id="edit_brand" name="brand">
                                    <option value="Reflex" <?php echo (isset($edit_product['details']['brand']) && $edit_product['details']['brand'] == 'Reflex') ? 'selected' : ''; ?>>Reflex</option>
                                    <option value="Moggy" <?php echo (isset($edit_product['details']['brand']) && $edit_product['details']['brand'] == 'Moggy') ? 'selected' : ''; ?>>Moggy</option>
                                    <option value="Pawfect" <?php echo (isset($edit_product['details']['brand']) && $edit_product['details']['brand'] == 'Pawfect') ? 'selected' : ''; ?>>Pawfect</option>
                                    <option value="Fluffy Food" <?php echo (isset($edit_product['details']['brand']) && $edit_product['details']['brand'] == 'Fluffy Food') ? 'selected' : ''; ?>>Fluffy Food</option>
                                    <option value="Royal Canin" <?php echo (isset($edit_product['details']['brand']) && $edit_product['details']['brand'] == 'Royal Canin') ? 'selected' : ''; ?>>Royal Canin</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_type" class="form-label">Type</label>
                                <select class="form-select" id="edit_type" name="type">
                                    <option value="Dry" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Dry') ? 'selected' : ''; ?>>Dry</option>
                                    <option value="Wet" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Wet') ? 'selected' : ''; ?>>Wet</option>
                                    <option value="Treats" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Treats') ? 'selected' : ''; ?>>Treats</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_food_weight" class="form-label">Weight/Size</label>
                                <input type="text" class="form-control" id="edit_food_weight" name="weight" value="<?php echo isset($edit_product['details']['weight']) ? $edit_product['details']['weight'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_age_group" class="form-label">Age Group</label>
                                <select class="form-select" id="edit_age_group" name="age_group">
                                    <option value="Kitten" <?php echo (isset($edit_product['details']['age_group']) && $edit_product['details']['age_group'] == 'Kitten') ? 'selected' : ''; ?>>Kitten</option>
                                    <option value="Adult" <?php echo (isset($edit_product['details']['age_group']) && $edit_product['details']['age_group'] == 'Adult') ? 'selected' : ''; ?>>Adult</option>
                                    <option value="Senior" <?php echo (isset($edit_product['details']['age_group']) && $edit_product['details']['age_group'] == 'Senior') ? 'selected' : ''; ?>>Senior</option>
                                    <option value="All Ages" <?php echo (isset($edit_product['details']['age_group']) && $edit_product['details']['age_group'] == 'All Ages') ? 'selected' : ''; ?>>All Ages</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_ingredients" class="form-label">Main Ingredients</label>
                                <textarea class="form-control" id="edit_ingredients" name="ingredients" rows="2"><?php echo isset($edit_product['details']['ingredients']) ? $edit_product['details']['ingredients'] : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Edit Accessories Form Fields -->
                        <div id="edit-accessory-fields" class="form-section <?php echo ($edit_product['category_id'] == 4) ? 'active' : ''; ?>">
                            <h4 class="mt-4">Accessory Details</h4>
                            
                            <div class="mb-3">
                                <label for="edit_accessory_type" class="form-label">Type</label>
                                <select class="form-select" id="edit_accessory_type" name="type">
                                    <option value="Toy" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Toy') ? 'selected' : ''; ?>>Toy</option>
                                    <option value="Bed" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Bed') ? 'selected' : ''; ?>>Bed</option>
                                    <option value="Carrier" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Carrier') ? 'selected' : ''; ?>>Carrier</option>
                                    <option value="Litter Box" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Litter Box') ? 'selected' : ''; ?>>Litter Box</option>
                                    <option value="Food Bowl" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Food Bowl') ? 'selected' : ''; ?>>Food Bowl</option>
                                    <option value="Scratching Post" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Scratching Post') ? 'selected' : ''; ?>>Scratching Post</option>
                                    <option value="Collar" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Collar') ? 'selected' : ''; ?>>Collar</option>
                                    <option value="Water Fountain" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Water Fountain') ? 'selected' : ''; ?>>Water Fountain</option>
                                    <option value="Grooming Brush" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Grooming Brush') ? 'selected' : ''; ?>>Grooming Brush</option>
                                    <option value="Cat Tree" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Cat Tree') ? 'selected' : ''; ?>>Cat Tree</option>
                                    <option value="Leash" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Leash') ? 'selected' : ''; ?>>Leash</option>
                                    <option value="Tunnel" <?php echo (isset($edit_product['details']['type']) && $edit_product['details']['type'] == 'Tunnel') ? 'selected' : ''; ?>>Tunnel</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_material" class="form-label">Material</label>
                                <input type="text" class="form-control" id="edit_material" name="material" value="<?php echo isset($edit_product['details']['material']) ? $edit_product['details']['material'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_size" class="form-label">Size</label>
                                <input type="text" class="form-control" id="edit_size" name="size" value="<?php echo isset($edit_product['details']['size']) ? $edit_product['details']['size'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="edit_color" name="color" value="<?php echo isset($edit_product['details']['color']) ? $edit_product['details']['color'] : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_accessory_brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="edit_accessory_brand" name="brand" value="<?php echo isset($edit_product['details']['brand']) ? $edit_product['details']['brand'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Automatically open the edit modal when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            var editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
            editModal.show();
        });
    </script>
    <?php endif; ?>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showFormFields() {
            var categoryId = document.getElementById('category_id').value;
            
            // Hide all form sections
            var formSections = document.querySelectorAll('#addProductForm .form-section');
            formSections.forEach(function(section) {
                section.classList.remove('active');
            });
            
            // Show relevant form section based on category
            if (categoryId == '1' || categoryId == '2') { // Adult Cats or Kittens
                document.getElementById('cat-fields').classList.add('active');
            } else if (categoryId == '3') { // Cat Food
                document.getElementById('food-fields').classList.add('active');
            } else if (categoryId == '4') { // Accessories
                document.getElementById('accessory-fields').classList.add('active');
            }
        }
        
        function showEditFormFields() {
            var categoryId = document.getElementById('edit_category_id').value;
            
            // Hide all form sections
            var formSections = document.querySelectorAll('#editProductForm .form-section');
            formSections.forEach(function(section) {
                section.classList.remove('active');
            });
            
            // Show relevant form section based on category
            if (categoryId == '1' || categoryId == '2') { // Adult Cats or Kittens
                document.getElementById('edit-cat-fields').classList.add('active');
            } else if (categoryId == '3') { // Cat Food
                document.getElementById('edit-food-fields').classList.add('active');
            } else if (categoryId == '4') { // Accessories
                document.getElementById('edit-accessory-fields').classList.add('active');
            }
        }
    </script>
</body>
</html>