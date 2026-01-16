<!DOCTYPE html>
<html lang="en">

<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb"; // your schema name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from item table
$sql = "SELECT IID, name, price, description, availability FROM item";
$result = $conn->query($sql);
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">    
</head>

<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L2 7V17L12 22L22 17V7L12 2Z" fill="#5865f2"/>
                </svg>
                <span>Store Dashboard</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <span class="icon">📊</span>
                <span>Overview</span>
            </a>
            <a href="dashboard_product_management.php" class="nav-item active">
                <span class="icon">📦</span>
                <span>Product Management</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">🛒</span>
                <span>Orders</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">👥</span>
                <span>Customers</span>
            </a>
            <a href="#" class="nav-item">
                <span class="icon">💰</span>
                <span>Revenue</span>
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
                    <div class="user-role">Admin</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1>Product Management</h1>
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                    <span>🔔</span>
                    <span class="badge">3</span>
                </button>
                <button class="icon-btn">
                    <span>⚙️</span>
                </button>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            
            <!-- Product management section -->
            <div class="product-management-CRUD-section">
                <div class="product-management-CRUD-header">
                    <h3>Product Management</h3>

                    <div class="header-actions">
                        <div class="search-bar-container">
                            <input type="text" name="Search Bar" class="search-bar" placeholder="Search Name" id="searchBar" >
                            <button type="button" class="clear-btn" id="clearBtn" style="display: none;">X</button>
                        </div>
                        <button class="btn-secondary add-item-btn">Add Item</button>
                    </div>
                </div>
                
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Name</th>
                                <th>Price ($)</th>
                                <th>Description</th>
                                <th class="available-clmn">Availability</th>
                                <th class="actions-clmn">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['IID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>$" . htmlspecialchars(number_format($row['price'], 2)) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";

                                    // Check availability
                                    if ($row['availability'] == 0) {
                                        echo "<td class='availability-cell'><span class='status-badge pending'>Unavailable</span></td>";
                                    } else {
                                        echo "<td class='availability-cell'><span class='status-badge completed'>Available</span></td>";
                                    }

                                    // Edit button and delete button
                                    echo "<td class='action-btn-cell'>
                                                <a class='edit-btn' href='edit_item.php?IID=" . urlencode($row['IID']) . "'>Edit</a>
                                                <form method='post' action='delete_item.php' style='display:inline; margin:0;'>
                                                    <input type='hidden' name='IID' value='" . htmlspecialchars($row['IID']) . "'>
                                                    <button type='submit' class='delete-btn'>Delete</button>
                                                </form>
                                            </td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No items found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- JavaScript for Search Filter -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
    const searchBar = document.getElementById('searchBar');
    const tableBody = document.querySelector('.data-table tbody');
    const clearBtn = document.getElementById('clearBtn');

    // Search filter function
    const filterRows = () => {
        const filter = searchBar.value.toLowerCase();
        const rows = tableBody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(row => {
            const nameCell = row.cells[1];
                        if (nameCell) {
                            const nameText = nameCell.textContent.toLowerCase();
                            if (nameText.includes(filter)) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });

                    // Remove any previous "no items" row
                    const noItemsRow = tableBody.querySelector('.no-items-row');
                    if (noItemsRow) noItemsRow.remove();

                    // If no rows are visible, add a "No items matched" row
                    if (visibleCount === 0) {
                        const colCount = tableBody.parentElement.querySelectorAll('thead th').length;
                        const row = document.createElement('tr');
                        row.classList.add('no-items-row');
                        row.innerHTML = `<td colspan="${colCount}" style="text-align: center; color: #dcddde; font-size: 14px;">No items matched</td>`;
                        tableBody.appendChild(row);
                    }

                    // Show/hide clear button
                    clearBtn.style.display = searchBar.value ? 'block' : 'none';
                };

                searchBar.addEventListener('input', filterRows);

                // Clear button functionality
                clearBtn.addEventListener('click', () => {
                    searchBar.value = '';
                    filterRows();
                    searchBar.focus();
                });
            });
            </script>

            
            <div class="section-separator"></div>

            <!-- Charts Section -->
            <div class="charts-row">
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Sales Overview</h3>
                        <select class="chart-select">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                        </select>
                    </div>
                    <div class="chart-placeholder">
                        <svg viewBox="0 0 400 200" class="line-chart">
                            <polyline
                                fill="none"
                                stroke="#5865f2"
                                stroke-width="3"
                                points="0,150 50,120 100,140 150,80 200,100 250,60 300,90 350,40 400,70"
                            />
                            <polyline
                                fill="url(#gradient)"
                                stroke="none"
                                points="0,150 50,120 100,140 150,80 200,100 250,60 300,90 350,40 400,70 400,200 0,200"
                            />
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#5865f2;stop-opacity:0.3" />
                                    <stop offset="100%" style="stop-color:#5865f2;stop-opacity:0" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>

                <div class="chart-card small">
                    <div class="card-header">
                        <h3>Top Categories</h3>
                    </div>
                    <div class="category-list">
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Electronics</span>
                                <span class="category-value">$8,240</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Clothing</span>
                                <span class="category-value">$6,120</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 55%"></div>
                            </div>
                        </div>
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Home & Garden</span>
                                <span class="category-value">$4,890</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 45%"></div>
                            </div>
                        </div>
                        <div class="category-item">
                            <div class="category-info">
                                <span class="category-name">Sports</span>
                                <span class="category-value">$3,210</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </main>
</body>
</html>