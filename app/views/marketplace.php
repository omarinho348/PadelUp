<?php
require_once __DIR__ . '/../controllers/MarketplaceController.php';

if (isset($_POST['action']) && $_POST['action'] === 'update_listing') {
    $message = MarketplaceController::updateListing();
} else {
    $message = MarketplaceController::createListing();
}

$products = MarketplaceController::listProducts();

// Separate user's listings from others
$my_listings = [];
$other_listings = [];
if (isset($_SESSION['user_id'])) {
    $current_user_id = $_SESSION['user_id'];
    foreach ($products as $product) {
        if ($product['seller_id'] == $current_user_id) {
            $my_listings[] = $product;
        } else {
            $other_listings[] = $product;
        }
    }
} else {
    $other_listings = $products;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - PadelUp</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/marketplace.css">
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="marketpage">
        <section class="hero">
            <div class="container hero-inner">
                <div class="hero-copy">
                    <h1>Find Your Perfect Padel Gear</h1>
                    <p class="subtitle">Buy and sell pre-loved padel rackets, balls, and accessories</p>
                    <div class="search-row"> 
                        <form method="GET" action="marketplace.php" class="search">
                            <input id="searchInput" name="search" type="search" placeholder="Search for rackets, balls, etc." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" />
                            <!-- Hidden fields to preserve other filters when searching -->
                            <?php foreach ($_GET as $key => $value) {
                                if ($key !== 'search' && is_scalar($value)) {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                                }
                            } ?>
                        </form>
                        <button id="openSellModalBtn" class="btn btn-primary sell-btn-plus" aria-label="Sell Your Gear">+</button>
                    </div>
                </div>

                <div class="hero-art">
                    <!-- decorative sports-oriented illustration placeholder -->
                    <div class="art-plate" aria-hidden>
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                          <defs>
                            <linearGradient id="g" x1="0" x2="1"><stop offset="0%" stop-color="#1976d2"/><stop offset="100%" stop-color="#20bfa9"/></linearGradient>
                          </defs>
                          <rect width="100%" height="100%" rx="12" fill="url(#g)" opacity="0.12"/>
                          <g transform="translate(20,30)">
                            <circle cx="40" cy="40" r="36" fill="#fff" opacity="0.06"/>
                            <rect x="90" y="20" width="60" height="80" rx="8" fill="#fff" opacity="0.06"/>
                          </g>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="market-grid container"> <!-- Added container class here -->
            <?php if ($message) echo $message; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'listed'): ?>
                <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: var(--radius); margin-bottom: 20px; text-align: center;">
                    Your item has been listed successfully!
                </div>
                <script>
                    // Clean up the URL
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.pathname);
                    }
                </script>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
                <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: var(--radius); margin-bottom: 20px; text-align: center;">
                    Your listing has been updated successfully!
                </div>
                <script>
                    if (window.history.replaceState) { window.history.replaceState(null, null, window.location.pathname); }
                </script>
            <?php endif; ?>
            <form method="GET" action="marketplace.php" class="filter-form">
                <section class="catalog">
                    <div class="catalog-header">
                        <button id="openFiltersBtn" type="button" class="btn btn-outline filter-toggle-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                            <span>Filters</span>
                        </button>
                        <div class="sort-controls">
                            <label>Sort:
                                <select id="sortSelect" name="sort">
                                    <option value="relevance" <?php if (($_GET['sort'] ?? 'relevance') == 'relevance') echo 'selected'; ?>>Most relevant</option>
                                    <option value="price-asc" <?php if (($_GET['sort'] ?? '') == 'price-asc') echo 'selected'; ?>>Price: Low to high</option>
                                    <option value="price-desc" <?php if (($_GET['sort'] ?? '') == 'price-desc') echo 'selected'; ?>>Price: High to low</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div id="productGrid">
                        <?php if (empty($my_listings) && empty($other_listings)): ?>
                            <div class="product-grid">
                                <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--muted);">
                                    No products listed yet. Be the first to sell something!
                                </p>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($my_listings)): ?>
                                <section class="product-section">
                                    <h2 class="grid-section-title">Your Listings</h2>
                                    <div class="product-grid">
                                        <?php foreach ($my_listings as $product): ?>
                                            <article class="product-card <?php if ($product['status'] === 'sold') echo 'is-sold'; ?>" 
                                                     data-title="<?php echo htmlspecialchars($product['title']); ?>" 
                                                     data-brand="<?php echo htmlspecialchars($product['category']); ?>" 
                                                     data-price="<?php echo htmlspecialchars($product['price']); ?>" 
                                                     data-condition="<?php echo htmlspecialchars($product['product_condition']); ?>" 
                                                     data-location="N/A" 
                                                     data-rating="0"
                                                     data-seller="You">
                                                <figure class="img-wrap">
                                                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? '../../public/Photos/racket_hack02.jpg'); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-img" />
                                                </figure>
                                                <div class="card-body">
                                                    <div class="card-top">
                                                        <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                                        <span class="brand"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></span>
                                                    </div>
                                                    <div class="price">EGP <?php echo htmlspecialchars(number_format($product['price'], 2)); ?></div>
                                                    <div class="meta">
                                                        <span class="badge status-badge status-<?php echo htmlspecialchars($product['status']); ?>"><?php echo htmlspecialchars(ucfirst($product['status'])); ?></span>
                                                        <span class="badge condition"><?php echo htmlspecialchars(str_replace('_', ' ', $product['product_condition'])); ?></span>
                                                    </div>
                                                    <div class="seller">Seller: <strong>You</strong></div>
                                                </div>
                                                <div class="card-actions seller-actions">
                                                    <button type="button" class="btn btn-sm btn-outline edit-listing-btn"
                                                            data-product-id="<?php echo (int)$product['product_id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($product['title']); ?>"
                                                            data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                                            data-price="<?php echo htmlspecialchars($product['price']); ?>"
                                                            data-category="<?php echo htmlspecialchars($product['category']); ?>"
                                                            data-condition="<?php echo htmlspecialchars($product['product_condition']); ?>"
                                                            data-status="<?php echo htmlspecialchars($product['status']); ?>">
                                                        Edit Listing
                                                    </button>
                                                    <div class="view-count">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                        <span><?php echo rand(1, 20); ?></span>
                                                    </div>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <?php if (!empty($other_listings)): ?>
                                <section class="product-section">
                                    <?php if (!empty($my_listings)): // Add a title for other listings only if user has listings ?>
                                        <h2 class="grid-section-title">Other Listings</h2>
                                    <?php endif; ?>
                                    <div class="product-grid">
                                        <?php foreach ($other_listings as $product): ?>
                                        <article class="product-card <?php if ($product['status'] === 'sold') echo 'is-sold'; ?>" 
                                                 data-title="<?php echo htmlspecialchars($product['title']); ?>" 
                                                 data-brand="<?php echo htmlspecialchars($product['category']); ?>" 
                                                 data-price="<?php echo htmlspecialchars($product['price']); ?>" 
                                                 data-condition="<?php echo htmlspecialchars($product['product_condition']); ?>" 
                                                 data-location="N/A" 
                                                 data-rating="0"
                                                 data-seller="<?php echo htmlspecialchars($product['seller_name']); ?>">
                                            <figure class="img-wrap">
                                                <img src="<?php echo htmlspecialchars($product['image_url'] ?? '../../public/Photos/racket_hack02.jpg'); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-img" />
                                            </figure>
                                            <div class="card-body">
                                                <div class="card-top">
                                                    <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                                    <span class="brand"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></span>
                                                </div>
                                                <div class="price">EGP <?php echo htmlspecialchars(number_format($product['price'], 2)); ?></div>
                                                <div class="meta">
                                                    <span class="badge status-badge status-<?php echo htmlspecialchars($product['status']); ?>"><?php echo htmlspecialchars(ucfirst($product['status'])); ?></span>
                                                    <span class="badge condition"><?php echo htmlspecialchars(str_replace('_', ' ', $product['product_condition'])); ?></span>
                                                </div>
                                                <div class="seller">Seller: <strong><?php echo htmlspecialchars($product['seller_name']); ?></strong></div>
                                            </div>
                                            <div class="card-actions" style="justify-content: center;">
                                                <button type="button" class="btn btn-sm btn-primary contact-seller-btn" <?php if ($product['status'] === 'sold') echo 'disabled'; ?>
                                                        data-seller-name="<?php echo htmlspecialchars($product['seller_name']); ?>"
                                                        data-seller-email="<?php echo htmlspecialchars($product['seller_email']); ?>"
                                                        data-seller-phone="<?php echo htmlspecialchars($product['seller_phone'] ?? 'Not provided'); ?>">
                                                    <?php if ($product['status'] === 'sold'): ?>Sold
                                                    <?php else: ?>Contact <?php echo htmlspecialchars(strtok($product['seller_name'], ' ')); ?>
                                                    <?php endif; ?>
                                                </button>
                                            </div>
                                        </article>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </form>
        </section>

        <!-- Dynamic Filter Panel -->
        <div id="filterOverlay" class="filter-overlay" aria-hidden="true"></div>
        <aside id="filterPanel" class="filter-panel" aria-hidden="true">
            <div class="filter-panel-header">
                <h3>Filters</h3>
                <button id="closeFiltersBtn" class="icon-btn modal-close" aria-label="Close Filters">×</button>
            </div>
            <form method="GET" action="marketplace.php" class="filter-form-wrapper">
                <div class="filter-panel-body">
                    <div class="filter-block">
                        <h4>Categories</h4>
                        <label><input type="checkbox" name="categories[]" value="rackets" <?php if (in_array('rackets', $_GET['categories'] ?? [])) echo 'checked'; ?>> Rackets</label>
                        <label><input type="checkbox" name="categories[]" value="shoes" <?php if (in_array('shoes', $_GET['categories'] ?? [])) echo 'checked'; ?>> Shoes</label>
                        <label><input type="checkbox" name="categories[]" value="apparel" <?php if (in_array('apparel', $_GET['categories'] ?? [])) echo 'checked'; ?>> Apparel</label>
                        <label><input type="checkbox" name="categories[]" value="accessories" <?php if (in_array('accessories', $_GET['categories'] ?? [])) echo 'checked'; ?>> Accessories</label>
                    </div>

                    <div class="filter-block">
                        <h4>Max Price</h4>
                        <div class="price-range">
                            <?php $maxPrice = $_GET['max_price'] ?? 30000; ?>
                            <input id="priceRange" name="max_price" type="range" min="0" max="30000" step="500" value="<?php echo htmlspecialchars($maxPrice); ?>">
                            <div class="range-labels"><span>EGP 0</span><span id="priceVal">EGP <?php echo htmlspecialchars($maxPrice); ?></span></div>
                        </div>
                    </div>

                    <div class="filter-block">
                        <h4>Condition</h4>
                        <label><input type="checkbox" name="conditions[]" value="new" <?php if (in_array('new', $_GET['conditions'] ?? [])) echo 'checked'; ?>> New</label>
                        <label><input type="checkbox" name="conditions[]" value="used_like_new" <?php if (in_array('used_like_new', $_GET['conditions'] ?? [])) echo 'checked'; ?>> Used - Like New</label>
                        <label><input type="checkbox" name="conditions[]" value="used_good" <?php if (in_array('used_good', $_GET['conditions'] ?? [])) echo 'checked'; ?>> Used - Good</label>
                        <label><input type="checkbox" name="conditions[]" value="used_fair" <?php if (in_array('used_fair', $_GET['conditions'] ?? [])) echo 'checked'; ?>> Used - Fair</label>
                    </div>
                </div>
                <div class="filter-panel-footer">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="marketplace.php" class="btn btn-outline">Clear All</a>
                </div>
                <!-- Pass along search and sort parameters -->
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'relevance'); ?>">
            </form>
        </aside>

        <!-- Contact Seller Modal -->
        <div id="contactSellerModal" class="modal" aria-hidden="true">
            <div class="modal-panel" style="max-width: 450px;">
                <button class="modal-close" aria-label="Close">×</button>
                <h3 id="contact-seller-title">Contact Seller</h3>
                <div class="seller-contact-info">
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <a href="#" id="contact-seller-email" class="info-value"></a>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span id="contact-seller-phone" class="info-value"></span>
                    </div>
                </div>
                <p style="font-size: 0.9rem; color: var(--muted); text-align: center; margin-top: 20px;">
                    Please be respectful when contacting sellers.
                </p>
            </div>
        </div>

        <!-- Sell Item Modal -->
        <div id="sellItemModal" class="modal" aria-hidden="true">
            <div class="modal-panel">
                <button class="modal-close" aria-label="Close">×</button>
                <h3>List Your Padel Gear</h3>
                <form id="sellItemForm" class="sell-form" method="POST" action="marketplace.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Product Title</label>
                        <input type="text" id="title" name="title" placeholder="e.g., Bullpadel Hack 03" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Describe the item, its usage, and any marks or defects."></textarea>
                    </div>
                    <div class="two-col">
                        <div class="form-group">
                            <label for="price">Price (EGP)</label>
                            <input type="number" id="price" name="price" min="0" step="100" placeholder="1500.00" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="rackets">Rackets</option>
                                <option value="shoes">Shoes</option>
                                <option value="apparel">Apparel</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="product_condition">Condition</label>
                        <select id="product_condition" name="product_condition" required>
                            <option value="new">New</option>
                            <option value="used_like_new">Used - Like New</option>
                            <option value="used_good">Used - Good</option>
                            <option value="used_fair">Used - Fair</option>
                            </select>
                    </div>
                    <div class="form-group">
                        <label for="itemImage" class="file-upload-label">
                            Click to Upload Image
                        </label>
                        <input type="file" id="itemImage" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;">List Item</button>
                </form>
            </div>
        </div>

        <!-- Edit Item Modal -->
        <div id="editItemModal" class="modal" aria-hidden="true">
            <div class="modal-panel">
                <button class="modal-close" aria-label="Close">×</button>
                <h3>Edit Your Listing</h3>
                <form id="editItemForm" class="sell-form" method="POST" action="marketplace.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_listing">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    <div class="form-group">
                        <label for="edit_title">Product Title</label>
                        <input type="text" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="two-col">
                        <div class="form-group">
                            <label for="edit_price">Price (EGP)</label>
                            <input type="number" id="edit_price" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_category">Category</label>
                            <select id="edit_category" name="category" required>
                                <option value="rackets">Rackets</option>
                                <option value="shoes">Shoes</option>
                                <option value="apparel">Apparel</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_condition">Condition</label>
                        <select id="edit_product_condition" name="product_condition" required>
                            <option value="new">New</option>
                            <option value="used_like_new">Used - Like New</option>
                            <option value="used_good">Used - Good</option>
                            <option value="used_fair">Used - Fair</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Availability</label>
                        <select id="edit_status" name="status" required>
                            <option value="available">Available</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_itemImage" class="file-upload-label">
                            Change Product Image (Optional)
                        </label>
                        <input type="file" id="edit_itemImage" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;">Save Changes</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Prevent modal clicks from closing when clicking inside any modal panel
        document.querySelectorAll('.modal-panel').forEach(panel => {
            panel.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Dynamic Filter Panel Logic
        const openBtn = document.getElementById('openFiltersBtn');
        const closeBtn = document.getElementById('closeFiltersBtn');
        const filterPanel = document.getElementById('filterPanel');
        const filterOverlay = document.getElementById('filterOverlay');

        const openFilters = () => {
            filterPanel.setAttribute('aria-hidden', 'false');
            filterOverlay.setAttribute('aria-hidden', 'false');
        };

        const closeFilters = () => {
            filterPanel.setAttribute('aria-hidden', 'true');
            filterOverlay.setAttribute('aria-hidden', 'true');
        };

        openBtn.addEventListener('click', openFilters);
        closeBtn.addEventListener('click', closeFilters);
        filterOverlay.addEventListener('click', closeFilters);

        // Sell Item Modal Logic
        const openSellModalBtn = document.getElementById('openSellModalBtn');
        const sellModal = document.getElementById('sellItemModal');
        const sellModalCloseBtns = sellModal.querySelectorAll('.modal-close');

        openSellModalBtn.addEventListener('click', () => {
            sellModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });

        const closeSellModal = () => {
            sellModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        sellModalCloseBtns.forEach(btn => btn.addEventListener('click', closeSellModal));
        sellModal.addEventListener('click', (e) => {
            if (e.target === sellModal) {
                closeSellModal();
            }
        });

        // Contact Seller Modal Logic
        const contactSellerModal = document.getElementById('contactSellerModal');
        const contactSellerCloseBtn = contactSellerModal.querySelector('.modal-close');

        document.querySelectorAll('.contact-seller-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const sellerName = this.dataset.sellerName;
                const sellerEmail = this.dataset.sellerEmail;
                const sellerPhone = this.dataset.sellerPhone;

                document.getElementById('contact-seller-title').textContent = `Contact ${sellerName}`;
                const emailLink = document.getElementById('contact-seller-email');
                emailLink.textContent = sellerEmail;
                emailLink.href = `mailto:${sellerEmail}`;
                document.getElementById('contact-seller-phone').textContent = sellerPhone;

                contactSellerModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });
        });

        const closeContactSellerModal = () => {
            contactSellerModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };
        contactSellerCloseBtn.addEventListener('click', closeContactSellerModal);
        contactSellerModal.addEventListener('click', (e) => { if (e.target === contactSellerModal) closeContactSellerModal(); });

        // Edit Listing Modal Logic
        const editItemModal = document.getElementById('editItemModal');
        const editModalCloseBtns = editItemModal.querySelectorAll('.modal-close');

        document.querySelectorAll('.edit-listing-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Populate the form
                document.getElementById('edit_product_id').value = this.dataset.productId;
                document.getElementById('edit_title').value = this.dataset.title;
                document.getElementById('edit_description').value = this.dataset.description;
                document.getElementById('edit_price').value = this.dataset.price;
                document.getElementById('edit_category').value = this.dataset.category;
                document.getElementById('edit_product_condition').value = this.dataset.condition;
                document.getElementById('edit_status').value = this.dataset.status;

                // Show the modal
                editItemModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            });
        });

        const closeEditModal = () => {
            editItemModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        editModalCloseBtns.forEach(btn => btn.addEventListener('click', closeEditModal));
        editItemModal.addEventListener('click', (e) => { if (e.target === editItemModal) closeEditModal(); });

        // Price range display update
        const priceRange = document.getElementById('priceRange');
        const priceVal = document.getElementById('priceVal');
        if (priceRange) {
            priceRange.addEventListener('input', () => {
                priceVal.textContent = 'EGP ' + priceRange.value;
            });
        }
        // Link sort dropdown to the main filter form and submit on change
        const sortSelect = document.getElementById('sortSelect');
        const filterForm = document.querySelector('.filter-form');
        if (sortSelect && filterForm) {
            sortSelect.addEventListener('change', () => {
                filterForm.submit();
            });
        }

        // Live search filtering
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();
                document.querySelectorAll('.product-card').forEach(card => {
                    const title = (card.dataset.title || '').toLowerCase();
                    const seller = (card.dataset.seller || '').toLowerCase();
                    const isVisible = title.includes(query) || seller.includes(query);
                    card.style.display = isVisible ? '' : 'none';
                });
            });
        }
    </script>
<?php include __DIR__ . '/partials/footer.php'; ?>