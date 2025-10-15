<?php include 'Includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PadelUp Marketplace</title>
    <link rel="stylesheet" href="styling/styles.css">
    <link rel="stylesheet" href="styling/marketplace.css">
</head>
<body>
    <div class="container">
    <main class="marketpage">
        <section class="hero">
            <div class="container hero-inner">
                <div class="hero-copy">
                    <h1>Find Your Perfect Padel Gear</h1>
                    <p class="subtitle">Buy and sell pre-loved padel rackets, balls, and accessories</p>
                    <div class="search-row">
                        <label class="search">
                            <input id="searchInput" type="search" placeholder="Search for rackets, balls, etc." />
                            <button class="icon-btn search-btn" aria-label="Search">
                            </button>
                        </label>

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

        <section class="market-grid">
            <section class="catalog">
                <div class="catalog-header">
                    <button id="openFiltersBtn" class="btn btn-outline filter-toggle-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        <span>Filters</span>
                    </button>
                    <div class="sort-controls">
                        <label>Sort:
                            <select id="sortSelect">
                                <option value="relevance">Most relevant</option>
                                <option value="price-asc">Price: Low to high</option>
                                <option value="price-desc">Price: High to low</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div id="productGrid" class="product-grid">
                    <!-- Product cards (12 sample cards) -->
                    <!-- We'll use data attributes for quick view -->
                    
                    <article class="product-card" data-title="Bullpadel Hack 02" data-brand="Bullpadel" data-price="279" data-condition="Like New" data-location="Madrid, ES" data-rating="4.5" data-seller="PadelPro">
                        <div class="product-badge featured">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                            <span>Featured</span>
                        </div>
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/racket_hack02.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body">
                            <div class="card-top">
                                <h3 class="product-title">Bullpadel Hack 02</h3>
                                <span class="brand">Bullpadel</span>
                            </div>
                            <div class="price">€279</div>
                            <div class="meta">
                                <span class="badge condition">Like New</span>
                                <span class="location">Madrid</span>
                            </div>
                            <div class="rating">★★★★★</div>
                            <div class="seller">Seller: <strong>PadelPro</strong></div>
                        </div>
                        <div class="card-actions">
                            <button class="btn btn-sm btn-outline view-details">View Details</button>
                            <button class="btn btn-sm btn-primary quick-view">Quick View</button>
                        </div>
                    </article>

                    <!-- Duplicate similar cards with variations -->
                    <article class="product-card" data-title="Adidas Carbon Comp" data-brand="Adidas" data-price="149" data-condition="Used" data-location="Barcelona, ES" data-rating="4.0" data-seller="RacketWorld">
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/adidas metal.png" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body">
                            <div class="card-top"><h3 class="product-title">Adidas Carbon Comp</h3><span class="brand">Adidas</span></div>
                            <div class="price">€149</div>
                            <div class="meta"><span class="badge condition">Used</span><span class="location">Barcelona</span></div>
                            <div class="rating">★★★★☆</div>
                            <div class="seller">Seller: <strong>RacketWorld</strong></div>
                        </div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Head Graphene 360" data-brand="Head" data-price="199" data-condition="New" data-location="Valencia, ES" data-rating="4.8" data-seller="PlayerOne">
                        <div class="product-badge verified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>Verified</span>
                        </div>
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/head.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Head Graphene 360</h3><span class="brand">Head</span></div><div class="price">€199</div><div class="meta"><span class="badge condition">New</span><span class="location">Valencia</span></div><div class="rating">★★★★★</div><div class="seller">Seller: <strong>PlayerOne</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Nox ML10 Pro" data-brand="Nox" data-price="239" data-condition="Like New" data-location="Seville, ES" data-rating="4.6" data-seller="NoxShop">
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/nox_ml.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Nox ML10 Pro</h3><span class="brand">Nox</span></div><div class="price">€239</div><div class="meta"><span class="badge condition">Like New</span><span class="location">Seville</span></div><div class="rating">★★★★☆</div><div class="seller">Seller: <strong>NoxShop</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Wilson Pro Staff" data-brand="Wilson" data-price="129" data-condition="Used" data-location="Bilbao, ES" data-rating="3.9" data-seller="SecondServe">
                        <div class="product-badge verified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>Verified</span>
                        </div>
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/wilson.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Wilson Pro Staff</h3><span class="brand">Wilson</span></div><div class="price">€129</div><div class="meta"><span class="badge condition">Used</span><span class="location">Bilbao</span></div><div class="rating">★★★☆☆</div><div class="seller">Seller: <strong>SecondServe</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Hybrid Padel Pack" data-brand="Multi" data-price="59" data-condition="New" data-location="Madrid, ES" data-rating="4.2" data-seller="PadelOutlet">
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/padel_pack.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Hybrid Padel Pack</h3><span class="brand">Multi</span></div><div class="price">€59</div><div class="meta"><span class="badge condition">New</span><span class="location">Madrid</span></div><div class="rating">★★★★☆</div><div class="seller">Seller: <strong>PadelOutlet</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Pro Player Shoes" data-brand="Adidas" data-price="89" data-condition="Like New" data-location="Granada, ES" data-rating="4.1" data-seller="Shoes4Padel">
                        <div class="product-badge verified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>Verified</span>
                        </div>
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/shoes.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Pro Player Shoes</h3><span class="brand">Adidas</span></div><div class="price">€89</div><div class="meta"><span class="badge condition">Like New</span><span class="location">Granada</span></div><div class="rating">★★★★☆</div><div class="seller">Seller: <strong>Shoes4Padel</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Padel Travel Bag" data-brand="Head" data-price="69" data-condition="New" data-location="Alicante, ES" data-rating="4.4" data-seller="BagHouse">
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/padel_bag.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Padel Travel Bag</h3><span class="brand">Head</span></div><div class="price">€69</div><div class="meta"><span class="badge condition">New</span><span class="location">Alicante</span></div><div class="rating">★★★★☆</div><div class="seller">Seller: <strong>BagHouse</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Grip Tape Bundle" data-brand="Wilson" data-price="9" data-condition="New" data-location="Malaga, ES" data-rating="4.7" data-seller="GripPro">
                        <div class="product-badge verified">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>Verified</span>
                        </div>
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/tape.jpg" alt="Padel Racket" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Grip Tape Bundle</h3><span class="brand">Wilson</span></div><div class="price">€9</div><div class="meta"><span class="badge condition">New</span><span class="location">Malaga</span></div><div class="rating">★★★★★</div><div class="seller">Seller: <strong>GripPro</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                    <article class="product-card" data-title="Wilson Balls Pack" data-brand="Wilson" data-price="16" data-condition="New" data-location="Cordoba, ES" data-rating="3.8" data-seller="BundleDeals">
                        <button class="fav icon-btn" aria-label="Favorite"><span class="heart">♡</span></button>
                        <figure class="img-wrap"><img src="Assets/Photos/balls.jpg" alt="Padel Balls" class="product-img" /></figure>
                        <div class="card-body"><div class="card-top"><h3 class="product-title">Wilson Padel Balls</h3><span class="brand">Wilson</span></div><div class="price">€39</div><div class="meta"><span class="badge condition">New</span><span class="location">Cordoba</span></div><div class="rating">★★★☆☆</div><div class="seller">Seller: <strong>BundleDeals</strong></div></div>
                        <div class="card-actions"><button class="btn btn-sm btn-outline view-details">View Details</button><button class="btn btn-sm btn-primary quick-view">Quick View</button></div>
                    </article>

                </div>
            </section>
        </section>

        <!-- Dynamic Filter Panel -->
        <div id="filterOverlay" class="filter-overlay" aria-hidden="true"></div>
        <aside id="filterPanel" class="filter-panel" aria-hidden="true">
            <div class="filter-panel-header">
                <h3>Filters</h3>
                <button id="closeFiltersBtn" class="icon-btn modal-close" aria-label="Close Filters">×</button>
            </div>
            <div class="filter-panel-body">
                <div class="filter-block">
                    <h4>Categories</h4>
                    <ul class="categories">
                        <li><label><input type="checkbox" checked> Rackets</label></li>
                        <li><label><input type="checkbox" checked> Balls</label></li>
                        <li><label><input type="checkbox"> Bags</label></li>
                        <li><label><input type="checkbox"> Shoes</label></li>
                        <li><label><input type="checkbox"> Apparel</label></li>
                        <li><label><input type="checkbox"> Accessories</label></li>
                    </ul>
                </div>

                <div class="filter-block">
                    <h4>Price</h4>
                    <div class="price-range">
                        <input id="priceRange" type="range" min="0" max="500" value="150">
                        <div class="range-labels"><span>0€</span><span id="priceVal">150€</span><span>500€</span></div>
                    </div>
                </div>

                <div class="filter-block">
                    <h4>Condition</h4>
                    <label><input type="checkbox" checked> New</label>
                    <label><input type="checkbox" checked> Like New</label>
                    <label><input type="checkbox"> Used</label>
                </div>

                <div class="filter-block">
                    <h4>Brand</h4>
                    <label><input type="checkbox"> Bullpadel</label>
                    <label><input type="checkbox"> Adidas</label>
                    <label><input type="checkbox"> Head</label>
                    <label><input type="checkbox"> Nox</label>
                    <label><input type="checkbox"> Wilson</label>
                </div>
            </div>
            <div class="filter-panel-footer">
                <button class="btn btn-primary">Apply Filters</button>
            </div>
        </aside>

        <!-- Quick View Modal -->
        <div id="quickView" class="modal" aria-hidden="true">
            <div class="modal-panel">
                <button class="modal-close" aria-label="Close">×</button>
                <div class="modal-grid">
                    <div class="modal-image">
                        <svg viewBox="0 0 200 140" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" rx="8" fill="#eef6ff"/></svg>
                    </div>
                    <div class="modal-content">
                        <h3 id="mv-title">Product Title</h3>
                        <p class="mv-brand">Brand: <span id="mv-brand">Brand</span></p>
                        <p class="mv-price">€<span id="mv-price">0</span></p>
                        <p class="mv-condition">Condition: <span id="mv-condition">-</span></p>
                        <p class="mv-location">Location: <span id="mv-location">-</span></p>
                        <p class="mv-seller">Seller: <strong id="mv-seller">-</strong></p>
                        <div class="mv-actions">
                            <button class="btn btn-primary">Contact Seller</button>
                            <button class="btn btn-outline" id="mv-add-fav">Add to favorites</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sell Item Modal -->
        <div id="sellItemModal" class="modal" aria-hidden="true">
            <div class="modal-panel">
                <button class="modal-close" aria-label="Close">×</button>
                <h3>List Your Padel Gear</h3>
                <form id="sellItemForm" class="sell-form">
                    <div class="form-group">
                        <label for="itemTitle">Product Title</label>
                        <input type="text" id="itemTitle" placeholder="e.g., Bullpadel Hack 03" required>
                    </div>
                    <div class="form-group">
                        <label for="itemBrand">Brand</label>
                        <input type="text" id="itemBrand" placeholder="e.g., Bullpadel" required>
                    </div>
                    <div class="two-col">
                        <div class="form-group">
                            <label for="itemPrice">Price (€)</label>
                            <input type="number" id="itemPrice" placeholder="199" required>
                        </div>
                        <div class="form-group">
                            <label for="itemCondition">Condition</label>
                            <select id="itemCondition" required>
                                <option value="New">New</option>
                                <option value="Like New">Like New</option>
                                <option value="Used">Used</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="itemImage" class="file-upload-label">
                            Click to Upload Image
                        </label>
                        <input type="file" id="itemImage" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;">List Item</button>
                </form>
            </div>
        </div>
    </main>

    <footer class="minimal-footer">
        <div class="footer-content">
            <div class="footer-brand">PadelUp</div>
            <div class="footer-links">
                <a href="#">About-Us</a>
                
            </div>
        </div>
    </footer>

    <script>
        // Quick View modal and simple interactions
        const quickBtns = document.querySelectorAll('.quick-view');
        const modal = document.getElementById('quickView');
        const mv = {
            title: document.getElementById('mv-title'),
            brand: document.getElementById('mv-brand'),
            price: document.getElementById('mv-price'),
            condition: document.getElementById('mv-condition'),
            location: document.getElementById('mv-location'),
            seller: document.getElementById('mv-seller')
        };

        quickBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const card = e.target.closest('.product-card');
                if (!card) return;
                modal.setAttribute('aria-hidden', 'false');
                mv.title.textContent = card.dataset.title;
                mv.brand.textContent = card.dataset.brand;
                mv.price.textContent = card.dataset.price;
                mv.condition.textContent = card.dataset.condition;
                mv.location.textContent = card.dataset.location;
                mv.seller.textContent = card.dataset.seller;
                document.body.style.overflow = 'hidden';
            });
        });

        document.querySelectorAll('.modal-close, #quickView').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target === el || e.target.classList.contains('modal-close')) {
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }
            });
        });

        // Prevent modal clicks from closing when clicking inside any modal panel
        document.querySelectorAll('.modal-panel').forEach(panel => {
            panel.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Favorite toggle
        document.querySelectorAll('.product-card .fav').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const heart = btn.querySelector('.heart');
                btn.parentElement.classList.toggle('favorited');
                if (btn.parentElement.classList.contains('favorited')) heart.textContent = '♥'; else heart.textContent = '♡';
            });
        });

        // Simple search filter
        const searchInput = document.getElementById('searchInput');
        const productGrid = document.getElementById('productGrid');
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const title = card.dataset.title.toLowerCase();
                const brand = card.dataset.brand.toLowerCase();
                const match = title.includes(q) || brand.includes(q);
                card.style.display = match || q === '' ? '' : 'none';
            });
        });

        // Price range display
        const priceRange = document.getElementById('priceRange');
        const priceVal = document.getElementById('priceVal');
        priceRange.addEventListener('input', () => {
            priceVal.textContent = priceRange.value + '€';
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


    </script>
</body>
</html>