<?php
include 'includes/config.php';

$conn = getDB();

$query = $_GET['query'] ?? '';

if (!empty($query)) {
    $stmt = $conn->prepare("SELECT id, name, author, image FROM products WHERE name LIKE ? OR author LIKE ? LIMIT 5");
    $search_query = "%{$query}%";
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="search-suggestion-item" data-product-id="' . $row['id'] . '">';
            echo '<img src="images/' . $row['image'] . '" class="search-suggestion-image">';
            echo '<div class="search-suggestion-info">';
            echo '<div class="search-suggestion-name">' . $row['name'] . '</div>';
            echo '<div class="search-suggestion-author">' . $row['author'] . '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<div class="search-suggestion-item">Nu s-au găsit rezultate</div>';
    }
} else {
    echo '<div class="search-suggestion-item">Introduceți un termen de căutare</div>';
}
