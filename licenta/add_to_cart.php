<?php
include 'includes/config.php';

$conn = getDB();

session_start();

$response = array('success' => false);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    $response['error'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_author = $_POST['product_author'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['product_quantity'];

    $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

    if (mysqli_num_rows($check_cart_numbers) > 0) {
        $fetch_cart = mysqli_fetch_assoc($check_cart_numbers);
        $new_quantity = $fetch_cart['quantity'] + $product_quantity;
        mysqli_query($conn, "UPDATE `cart` SET quantity = '$new_quantity' WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');
    } else {
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image, author) VALUES('$user_id', '$product_name', 
        '$product_price', '$product_quantity', '$product_image', '$product_author')") or die('query failed');
    }

    $response['success'] = true;
} else {
    $response['error'] = 'Invalid request';
}

echo json_encode($response);
