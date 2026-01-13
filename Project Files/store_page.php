<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manufacturing Products Store</title>
    <link rel="stylesheet" href="css/store_page.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L2 7V17L12 22L22 17V7L12 2Z" fill="#5865f2"/>
                </svg>
                <span>MFG Store</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item active">
                <span class="icon">🏪</span>
                <span>Store</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">🛒</span>
                <span>Cart</span>
                <span class="badge">3</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">📦</span>
                <span>Orders</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">⚙️</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar">JD</div>
                <div class="user-info">
                    <div class="user-name">John Doe</div>
                    <div class="user-role">Buyer</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="search-bar">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search products, categories, or suppliers...">
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                    <span>🔔</span>
                    <span class="badge-small">5</span>
                </button>
                <button class="icon-btn">
                    <span>🛒</span>
                </button>
            </div>
        </header>

        <div class="store-content">
            <div class="filters-section">
                <div class="section-header">
                    <h2>Filters</h2>
                    <button class="text-btn">Clear All</button>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title">Categories</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" checked>
                        <span>Industrial Equipment</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>Power Tools</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>Safety Gear</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>Raw Materials</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>Machinery Parts</span>
                    </label>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title">Price Range</h3>
                    <div class="price-inputs">
                        <input type="number" placeholder="Min" class="price-input">
                        <span>—</span>
                        <input type="number" placeholder="Max" class="price-input">
                    </div>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title">Availability</h3>
                    <label class="checkbox-label">
                        <input type="checkbox" checked>
                        <span>In Stock</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>Pre-Order</span>
                    </label>
                </div>

                <div class="filter-group">
                    <h3 class="filter-title">Rating</h3>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>⭐⭐⭐⭐⭐ 5 Stars</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>⭐⭐⭐⭐ 4+ Stars</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>⭐⭐⭐ 3+ Stars</span>
                    </label>
                </div>
            </div>

            <div class="products-section">
                <div class="products-header">
                    <div class="results-info">
                        <h2>Industrial Equipment</h2>
                        <span class="results-count">127 products found</span>
                    </div>
                    <div class="sort-controls">
                        <label>Sort by:</label>
                        <select class="sort-select">
                            <option>Best Match</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Newest First</option>
                            <option>Top Rated</option>
                        </select>
                    </div>
                </div>

                <div class="products-grid">
                    <div class="product-card">
                        <div class="product-image">
                            <div class="image-placeholder">🏭</div>
                            <button class="wishlist-btn">❤️</button>
                            <span class="stock-badge in-stock">In Stock</span>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Industrial Equipment</div>
                            <h3 class="product-name">Heavy-Duty CNC Milling Machine</h3>
                            <div class="product-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-count">(142)</span>
                            </div>
                            <div class="product-specs">
                                <span>• 5-Axis Control</span>
                                <span>• 3000 RPM</span>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">$24,999</div>
                                <button class="btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <div class="image-placeholder">⚙️</div>
                            <button class="wishlist-btn">❤️</button>
                            <span class="stock-badge in-stock">In Stock</span>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Industrial Equipment</div>
                            <h3 class="product-name">Hydraulic Press 200 Ton</h3>
                            <div class="product-rating">
                                <span class="stars">⭐⭐⭐⭐</span>
                                <span class="rating-count">(89)</span>
                            </div>
                            <div class="product-specs">
                                <span>• 200 Ton Capacity</span>
                                <span>• Electric Control</span>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">$18,500</div>
                                <button class="btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <div class="image-placeholder">🔧</div>
                            <button class="wishlist-btn">❤️</button>
                            <span class="stock-badge low-stock">Low Stock</span>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Power Tools</div>
                            <h3 class="product-name">Industrial Welding Station</h3>
                            <div class="product-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-count">(203)</span>
                            </div>
                            <div class="product-specs">
                                <span>• MIG/TIG/Stick</span>
                                <span>• 400A Output</span>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">$3,299</div>
                                <button class="btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <div class="image-placeholder">🛠️</div>
                            <button class="wishlist-btn">❤️</button>
                            <span class="stock-badge in-stock">In Stock</span>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Industrial Equipment</div>
                            <h3 class="product-name">Laser Cutting System Pro</h3>
                            <div class="product-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-count">(156)</span>
                            </div>
                            <div class="product-specs">
                                <span>• 6kW Fiber Laser</span>
                                <span>• Auto Focus</span>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">$45,000</div>
                                <button class="btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <div class="image-placeholder">⚡</div>
                            <button class="wishlist-btn">❤️</button>
                            <span class="stock-badge in-stock">In Stock</span>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Power Tools</div>
                            <h3 class="product-name">Industrial Air Compressor</h3>
                            <div class="product-rating">
                                <span class="stars">⭐⭐⭐⭐</span>
                                <span class="rating-count">(78)</span>
                            </div>
                            <div class="product-specs">
                                <span>• 200L Tank</span>
                                <span>• 15 HP Motor</span>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">$2,850</div>
                                <button class="btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <div class="image-placeholder">🔩</div>
                            <button class="wishlist-btn">❤️</button>
                            <span class="stock-badge pre-order">Pre-Order</span>
                        </div>
                        <div class="product-info">
                            <div class="product-category">Machinery Parts</div>
                            <h3 class="product-name">Precision Bearing Set Pro</h3>
                            <div class="product-rating">
                                <span class="stars">⭐⭐⭐⭐⭐</span>
                                <span class="rating-count">(234)</span>
                            </div>
                            <div class="product-specs">
                                <span>• High Carbon Steel</span>
                                <span>• 500 Pieces</span>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">$599</div>
                                <button class="btn-add-cart">Pre-Order</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pagination">
                    <button class="page-btn">Previous</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">4</button>
                    <button class="page-btn">Next</button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>