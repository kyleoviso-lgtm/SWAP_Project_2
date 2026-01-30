<?php
// visualization_element.php
// Assumes: $conn exists (from bootstrap.php)

// --------------------
// FILTER: ORDER TYPE (local scope, safe)
// --------------------
$type = $_GET['type'] ?? 'all';

switch ($type) {
    case 'active':
        $status_ids = [1, 2, 3];
        break;
    case 'completed':
        $status_ids = [4, 5];
        break;
    default:
        $status_ids = [1, 2, 3, 4, 5];
}

// --------------------
// PREPARED SQL (safe)
// --------------------
$placeholders = implode(',', array_fill(0, count($status_ids), '?'));

$sql = "
SELECT 
    DATE(order_time) AS order_date,
    item.name AS item_name,
    SUM(item_qty) AS total_qty
FROM order_table
JOIN item ON order_table.item_id = item.IID
WHERE order_status_id IN ($placeholders)
GROUP BY DATE(order_time), item.IID
ORDER BY order_date ASC
";

$stmt = $conn->prepare($sql);
$types = str_repeat('i', count($status_ids));
$stmt->bind_param($types, ...$status_ids);
$stmt->execute();
$result = $stmt->get_result();

// --------------------
// Transform data
// --------------------
$all_dates = [];
$all_items = [];

while ($row = $result->fetch_assoc()) {
    $date = $row['order_date'];
    $item = $row['item_name'];
    $qty  = (int)$row['total_qty'];

    if (!in_array($date, $all_dates)) $all_dates[] = $date;
    if (!isset($all_items[$item])) $all_items[$item] = [];
    $all_items[$item][$date] = $qty;
}

foreach ($all_items as &$dates) {
    foreach ($all_dates as $date) {
        if (!isset($dates[$date])) $dates[$date] = 0;
    }
    ksort($dates);
}
unset($dates);

$datasets = [];
$colors = ['#5865F2','#F04747','#43B581','#FAA61A','#7289DA','#AA00FF'];
$i = 0;

foreach ($all_items as $item_name => $date_data) {
    $datasets[] = [
        'label' => $item_name,
        'data' => array_values($date_data),
        'borderColor' => $colors[$i % count($colors)],
        'fill' => false,
        'tension' => 0.3,
        'pointRadius' => 5,
        'pointBackgroundColor' => $colors[$i % count($colors)]
    ];
    $i++;
}
?>

<!-- Chart Filter -->
<form method="GET" class="chart-filter">
    <select name="type" onchange="this.form.submit()">
        <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Orders</option>
        <option value="active" <?= $type === 'active' ? 'selected' : '' ?>>Active Orders</option>
        <option value="completed" <?= $type === 'completed' ? 'selected' : '' ?>>Completed Orders</option>
    </select>
</form>

<div class="chart-container">
    <canvas id="ordersChart"></canvas>
</div>

<script>
if (window.ordersChartInstance) {
    window.ordersChartInstance.destroy();
}

const ctx = document.getElementById('ordersChart').getContext('2d');

window.ordersChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($all_dates) ?>,
        datasets: <?= json_encode($datasets) ?>
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#fff' } }
        },
        scales: {
            x: { ticks: { color: '#fff' } },
            y: { beginAtZero: true, ticks: { color: '#fff' } }
        }
    }
});
</script>
