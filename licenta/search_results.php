<?php
include 'includes/config.php';

$query = $_GET['query'] ?? '';

if ($query) {
    $conn = getDB();

    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR author LIKE ?");
    $searchTerm = "%" . $query . "%";
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode($products);
} else {
    echo json_encode([]);
}
