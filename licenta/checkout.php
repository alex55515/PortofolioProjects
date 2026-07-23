<?php
include 'includes/config.php';

$conn = getDB();

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

if (isset($_POST['order_btn'])) {

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $pin_code = mysqli_real_escape_string($conn, $_POST['pin_code']);
    $address = "$state, $street, $city, $country - $pin_code";

    $cart_total = 0;
    $cart_products = [];

    $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
    if (mysqli_num_rows($cart_query) > 0) {
        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
            $sub_total = ($cart_item['price'] * $cart_item['quantity']);
            $cart_total += $sub_total;
        }
    }

    $total_products = implode(', ', $cart_products);

    $order_query = mysqli_query($conn, "SELECT * FROM `orders` 
    WHERE first_name = '$first_name' AND last_name = '$last_name' 
    AND number = '$number' AND email = '$email' AND method = '$method' 
    AND address = '$address' AND total_products = '$total_products' 
    AND total_price = '$cart_total'") or die('query failed');

    if ($cart_total == 0) {
        $message[] = 'Coșul este gol';
    } else {
        if (mysqli_num_rows($order_query) > 0) {
            $message[] = 'Comanda a fost deja plasată!';
        } else {
            mysqli_query($conn, "INSERT INTO `orders`(user_id, first_name, last_name, number, email, method, address, total_products, total_price, status_payment) 
            VALUES('$user_id', '$first_name', '$last_name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', 'pending')")
                or die('query failed');
            $message[] = 'Comanda a fost plasată!';
            mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<h3 class="checkout-heading">Finalizare Comandă</h3>

<section class="checkout-body">

    <section class="checkout">
        <form action="" method="post">
            <h3>Introdu datele de livrare: </h3>
            <div class="flex">
                <div class="inputBox">
                    <input type="text" name="first_name" required placeholder="Prenume">
                </div>
                <div class="inputBox">
                    <input type="text" name="last_name" required placeholder="Nume">
                </div>
                <div class="inputBox">
                    <input type="email" name="email" required placeholder="Introdu adresa de email">
                </div>
                <div class="inputBox">
                    <input type="number" name="number" required placeholder="Număr de telefon">
                </div>
                <div class="inputBox">
                    <input type="text" name="state" required placeholder="Județ">
                </div>
                <div class="inputBox">
                    <input type="text" name="street" required placeholder="Stradă">
                </div>
                <div class="inputBox">
                    <input type="text" name="city" required placeholder="Oraș">
                </div>
                <div class="inputBox">
                    <input type="text" name="country" required placeholder="Țară">
                </div>
                <div class="inputBox">
                    <input type="number" name="pin_code" required placeholder="Cod poștal">
                </div>
            </div>
            <h3 class="payment-heading">Modalitate de plată:</h3>
            <div class="payment-methods">
                <label>
                    <input type="radio" name="method" value="cash on delivery" required>
                    <span>Ramburs <br>Plătiți la primirea coletului.</span>
                </label>
                <label>
                    <input type="radio" name="method" value="credit card">
                    <span>Card Online <br>(bancar sau cultural)<br>
                        <img src="images/visa.jpg" alt="Visa">
                        <img src="images/mastercard.png" alt="MasterCard">
                        <br>Redirect to secure page <a href="https://www.payu.ro">PayU</a>.
                    </span>
                </label>
                <label>
                    <input type="radio" name="method" value="bank transfer">
                    <span>PayPal</span>
                </label>
            </div>
            <input type="submit" value="Comandă acum" class="btn" name="order_btn">
        </form>
    </section>

    <section class="display-order">
        <h3>Comanda ta:</h3>
        <?php
        $grand_total = 0;
        $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
                $grand_total += $total_price;
        ?>
                <div class="product">
                    <div class="product-info">
                        <img src="images/<?php echo $fetch_cart['image']; ?>" alt="<?php echo $fetch_cart['name']; ?>">
                        <div class="product-info-right">
                            <p class="cart-product-name"><?php echo $fetch_cart['name']; ?></p>
                            <p class="cart-product-author"><?php echo $fetch_cart['author']; ?></p>
                            <p><?php echo $fetch_cart['quantity'] . ' buc.'; ?> </p>
                            <p class="cart-product-price"><?php echo $fetch_cart['price'] . ' lei'; ?></p>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<p class="empty">Cosul tau este gol</p>';
        }
        ?>
        <div class="grand-total"> Total de plată : <span><?php echo $grand_total; ?> lei</span> </div>
    </section>


</section>

<script src="script.js"></script>
</body>

</html>