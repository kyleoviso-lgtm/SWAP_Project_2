<?php

require_once 'db.php';

// --------------------
// FILTER: ORDER TYPE
// --------------------
$type = $_GET['type'] ?? 'all';
$status_ids = ($type === 'active') ? "1,2,3" : (($type === 'completed') ? "4,5" : "1,2,3,4,5");

// --------------------
// SQL: Get item sales per day
// --------------------
$sql = "
SELECT 
    DATE(order_time) AS order_date,
    item.name AS item_name,
    SUM(item_qty) AS total_qty
FROM order_table
JOIN item ON order_table.item_id = item.IID
WHERE order_status_id IN ($status_ids)
GROUP BY DATE(order_time), item.IID
ORDER BY order_date ASC
";

$result = $conn->query($sql);

// --------------------
// Transform data for Chart.js
// --------------------
$all_dates = [];
$all_items = [];

// Step 1: Collect all dates and items
while ($row = $result->fetch_assoc()) {
    $date = $row['order_date'];
    $item = $row['item_name'];
    $qty  = (int)$row['total_qty'];

    if (!in_array($date, $all_dates)) $all_dates[] = $date;
    if (!isset($all_items[$item])) $all_items[$item] = [];
    $all_items[$item][$date] = $qty;
}

// Step 2: Fill missing dates with 0 and ensure correct order
foreach ($all_items as $item_name => &$dates) {
    foreach ($all_dates as $date) {
        if (!isset($dates[$date])) $dates[$date] = 0;
    }
    ksort($dates);
}
unset($dates);

// Step 3: Prepare datasets for Chart.js
$colors = ['#5865F2','#F04747','#43B581','#FAA61A','#7289DA','#AA00FF','#FF6EC7','#00BFFF'];
$datasets = [];
$i = 0;
foreach ($all_items as $item_name => $date_data) {
    $datasets[] = [
        'label' => $item_name,
        'data' => array_values($date_data),
        'borderColor' => $colors[$i % count($colors)],
        'backgroundColor' => 'rgba(0,0,0,0)',
        'fill' => false,
        'tension' => 0.3
    ];
    $i++;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Items Over Time</title>
<link rel="stylesheet" href="css/visualization.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <h1>Items Ordered Over Time</h1>

    <form method="GET">
        <select name="type" onchange="this.form.submit()">
            <option value="all" <?= $type=='all'?'selected':'' ?>>All Orders</option>
            <option value="active" <?= $type=='active'?'selected':'' ?>>Active Orders</option>
            <option value="completed" <?= $type=='completed'?'selected':'' ?>>Completed Orders</option>
        </select>
    </form>

    <div class="card">
        <canvas id="ordersChart"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('ordersChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($all_dates); ?>,
        datasets: <?php echo json_encode(array_map(function($dataset) {
            $dataset['pointStyle'] = 'circle';
            $dataset['pointRadius'] = 5;          // size of the dot
            $dataset['pointBackgroundColor'] = $dataset['borderColor']; // fill dot with line color
            return $dataset;
        }, $datasets)); ?>
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { ticks: { color: '#ffffff' }, grid: { color: '#333' } },
            y: { beginAtZero: true, ticks: { color: '#ffffff' }, grid: { color: '#333' } }
        },
        plugins: {
            legend: {
                labels: { 
                    color: '#ffffff',
                    usePointStyle: true,   // legend uses dot style
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        }
    }
});
</script>

</body>
</html>
