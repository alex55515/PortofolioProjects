<?php
include 'includes/config.php';

$conn = getDB();

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (isset($_POST['add_to_cart'])) {
    if (!$user_id) {
        header('Location: login.php');
        exit;
    }
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

    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

include 'includes/header.php';
?>

<section class="product-details">
    <div class="product-container">
        <?php
        if (isset($_GET['product_id'])) {
            $product_id = $_GET['product_id'];
            $select_product = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$product_id'") or die('query failed');
            if (mysqli_num_rows($select_product) > 0) {
                $product = mysqli_fetch_assoc($select_product);
        ?>
                <div class="product-details-container">
                    <div class="product-image">
                        <img class="product-image" src="images/<?php echo $product['image']; ?>" alt="">
                    </div>
                    <div class="product-details-right">
                        <h2><?php echo $product['name']; ?></h2>
                        <h3>de <?php echo $product['author']; ?></h3>
                        <p><i class="fa-solid fa-wallet"></i> Preț valabil exclusiv online!</p>
                        <p><i class="fa-solid fa-gift"></i> Împachetare cadou gratuită!</p>
                        <p><i class="fa-solid fa-backward"></i> Retur gratuit în 14 zile.</p>
                        <p><i class="fa-solid fa-truck"></i> Transport gratuit peste 150 de lei.</p>
                        <p><i class="fa-solid fa-book-open"></i> Număr de pagini: <?php echo $product['pages']; ?></p>
                        <div class="add-to-cart">
                            <p class="price"><?php echo $product['price']; ?> lei</p>
                            <form action="" method="post">
                                <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                                <input type="hidden" name="product_author" value="<?php echo $product['author']; ?>">
                                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                                <input type="hidden" name="product_image" value="<?php echo $product['image']; ?>">
                                <label for="product_quantity">Cantitate:</label>
                                <input type="number" name="product_quantity" id="product_quantity" value="1" min="1">
                                <input type="submit" value="Adaugă în coș" name="add_to_cart" class="btn">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="product-description">
                    <h3>Descriere:</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                <div class="related-books">
                    <h3>Te-ar putea interesa:</h3>
                </div>
                <div class="related-books-container">
                    <?php
                    $related_books_query = mysqli_query($conn, "SELECT * FROM `products` 
                    WHERE type = '{$product['type']}' AND id != '$product_id' LIMIT 4") or die('related books query failed');

                    if (mysqli_num_rows($related_books_query) > 0) {
                        while ($related_book = mysqli_fetch_assoc($related_books_query)) {
                    ?>
                            <div class="related-book">
                                <a href="product_details.php?product_id=<?php echo $related_book['id']; ?>">
                                    <img src="images/<?php echo $related_book['image']; ?>" alt="<?php echo $related_book['name']; ?>">
                                </a>
                                <div class="price"><?php echo $related_book['price']; ?> lei</div>
                                <h4>
                                    <a href="product_details.php?product_id=<?php echo $related_book['id']; ?>">
                                        <?php echo $related_book['name']; ?>
                                    </a>
                                </h4>
                                <p><?php echo $related_book['author']; ?></p>
                                <form action="" method="post">
                                    <input type="hidden" name="product_name" value="<?php echo $related_book['name']; ?>">
                                    <input type="hidden" name="product_author" value="<?php echo $related_book['author']; ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $related_book['price']; ?>">
                                    <input type="hidden" name="product_image" value="<?php echo $related_book['image']; ?>">
                                    <input type="hidden" name="product_quantity" value="1">
                                    <input type="submit" value="Adaugă la comandă" name="add_to_cart" class="btn">
                                </form>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<p class="empty">Nu există cărți asemănătoare.</p>';
                    }
                    ?>
                </div>
        <?php
            } else {
                echo '<p class="empty">Produsul nu există!</p>';
            }
        } else {
            echo '<p class="empty">Nu s-a specificat niciun produs!</p>';
        }
        ?>
    </div>

</section>

<?php include 'includes/footer.php'; ?>

<script src="script.js"></script>
</body>

</html>