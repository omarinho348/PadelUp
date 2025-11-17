<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/Product.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class MarketplaceController
{
    /**
     * Get all products for the main marketplace view.
     */
    public static function listProducts(): array
    {
        $filters = [
            'search' => $_GET['search'] ?? null,
            'categories' => $_GET['categories'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'conditions' => $_GET['conditions'] ?? null,
            'sort' => $_GET['sort'] ?? 'relevance',
        ];

        return Product::findAllAvailable($GLOBALS['conn'], $filters);
    }

    /**
     * Handle the creation of a new product listing.
     */
    public static function createListing(): string
    {
        // Only handle POST requests that are NOT for updating a listing
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (isset($_POST['action']) && $_POST['action'] === 'update_listing')) {
            return "";
        }

        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("You must be logged in to sell an item.");
            }

            // --- 1. Validate form data ---
            $required = ['title', 'description', 'price', 'category', 'product_condition'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields.");
                }
            }

            // --- 2. Handle Image Upload ---
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("An image is required to list a product.");
            } else {
                $uploadDir = __DIR__ . '/../../public/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                    throw new Exception("Invalid file type. Please upload a JPG, PNG, GIF, or WEBP.");
                }

                if ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                    throw new Exception("File is too large. Maximum size is 5MB.");
                }

                $fileName = uniqid('product_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $uploadPath = $uploadDir . $fileName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    throw new Exception("Failed to upload image.");
                }
                // Store the relative path for web access
                $imageUrl = '../../public/uploads/products/' . $fileName;
            }

            // --- 3. Prepare data for model ---
            $data = [
                'seller_id' => (int)$_SESSION['user_id'],
                'title' => htmlspecialchars($_POST['title']),
                'description' => htmlspecialchars($_POST['description'] ?? ''),
                'price' => (float)$_POST['price'],
                'category' => htmlspecialchars($_POST['category']),
                'product_condition' => htmlspecialchars($_POST['product_condition']),
                'image_url' => $imageUrl
            ];

            // --- 4. Call Model to create product ---
            if (Product::create($GLOBALS['conn'], $data)) {
                // --- 5. Redirect on success ---
                header('Location: marketplace.php?status=listed');
                exit();
            } else {
                throw new Exception("An error occurred while creating the listing.");
            }

        } catch (Exception $e) {
            return '<div class="error-message">' . $e->getMessage() . '</div>';
        }
    }

    /**
     * Handle updating an existing product listing.
     */
    public static function updateListing(): string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'update_listing') {
            return "";
        }

        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("You must be logged in to edit an item.");
            }

            $productId = (int)($_POST['product_id'] ?? 0);
            if ($productId <= 0) {
                throw new Exception("Invalid product ID.");
            }

            // Verify ownership
            $product = Product::findById($GLOBALS['conn'], $productId);
            if (!$product || $product['seller_id'] !== $_SESSION['user_id']) {
                throw new Exception("You do not have permission to edit this item.");
            }

            // --- Validate form data ---
            $required = ['title', 'price', 'category', 'product_condition', 'status'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields.");
                }
            }

            // --- Handle Image Upload (if a new one is provided) ---
            $imageUrl = $product['image_url']; // Keep old image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Same upload logic as createListing, but we could also delete the old image
                 $uploadDir = __DIR__ . '/../../public/uploads/products/';
                 $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                 if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                     throw new Exception("Invalid file type. Please upload a JPG, PNG, GIF, or WEBP.");
                 }
                 if ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                     throw new Exception("File is too large. Maximum size is 5MB.");
                 }
 
                 $fileName = uniqid('product_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                 $uploadPath = $uploadDir . $fileName;
 
                 if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                     throw new Exception("Failed to upload new image.");
                 }
 
                 // TODO: Optionally delete the old image file.
                 $imageUrl = '../../public/uploads/products/' . $fileName;
            }

            $data = [
                'title' => htmlspecialchars($_POST['title']),
                'description' => htmlspecialchars($_POST['description'] ?? ''),
                'price' => (float)$_POST['price'],
                'category' => htmlspecialchars($_POST['category']),
                'product_condition' => htmlspecialchars($_POST['product_condition']),
                'image_url' => $imageUrl,
                'status' => htmlspecialchars($_POST['status'])
            ];

            if (Product::update($GLOBALS['conn'], $productId, $data)) {
                header('Location: marketplace.php?status=updated');
                exit();
            } else {
                throw new Exception("An error occurred while updating the listing.");
            }
        } catch (Exception $e) {
            return '<div class="error-message">' . $e->getMessage() . '</div>';
        }
    }
}
?>