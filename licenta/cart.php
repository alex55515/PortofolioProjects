<?php

include 'includes/config.php';

$conn = getDB();

session_start();

if (!isset($_SESSION['user_id'])) {
   header('Location: login.php');
   exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['cart'])) {
   $_SESSION['cart'] = [];
}

if (isset($_POST['add_to_cart'])) {
   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_author = $_POST['product_author'];
   $product_quantity = $_POST['quantity'] ?? 1;

   $cart_item = [
      'name' => $product_name,
      'author' => $product_author,
      'price' => $product_price,
      'image' => $product_image,
      'quantity' => $product_quantity
   ];

   if ($user_id != 0) {
      $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` 
        WHERE name = '$product_name' AND user_id = '$user_id'") or
         die('query failed: ' . mysqli_error($conn));

      if (mysqli_num_rows($check_cart_numbers) > 0) {
         $existing_cart_item = mysqli_fetch_assoc($check_cart_numbers);
         $new_quantity = $existing_cart_item['quantity'] + $product_quantity;
         mysqli_query($conn, "UPDATE `cart` SET quantity = '$new_quantity' WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed: ' . mysqli_error($conn));
      } else {
         $product_author = mysqli_real_escape_string($conn, $product_author);
         mysqli_query($conn, "INSERT INTO `cart`(user_id, name, author, price, image, quantity) VALUES('$user_id', '$product_name', '$product_author', '$product_price', '$product_image', '$product_quantity')") or die('query failed: ' . mysqli_error($conn));
      }
   } else {
      $_SESSION['cart'][] = $cart_item;
      var_dump($_SESSION['cart']);
   }
   header('Location: ' . $_SERVER['PHP_SELF']);
   exit;
}

if (isset($_POST['update_cart'])) {
   $cart_id = $_POST['cart_id'];
   $cart_quantity = $_POST['cart_quantity'];
   mysqli_query($conn, "UPDATE `cart` SET quantity = '$cart_quantity' WHERE id = '$cart_id' AND user_id = '$user_id'") or die('query failed');
   header('Location: cart.php');
   exit;
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$delete_id' AND user_id = '$user_id'") or die('query failed');
   header('Location: cart.php');
   exit;
}

if (isset($_GET['delete_all'])) {
   mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   header('Location: cart.php');
   exit;
}
$total_products_in_cart = count($_SESSION['cart']);

?>

<?php include 'includes/header.php'; ?>


<h5 class="cart-heading">Coșul tău de cumpărături:</h5>

<div style="display:flex; justify-content: center;">

   <section class="shopping-cart">
      <table class="cart-table">
         <thead>
            <tr>
               <th>Nume produs</th>
               <th>Cantitate</th>
               <th>Șterge</th>
               <th>Preț pe buc.</th>
               <th>Preț total</th>
            </tr>
         </thead>
         <tbody>
            <?php
            $grand_total = 0;
            $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
            if (mysqli_num_rows($select_cart) > 0) {
               while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                  $sub_total = $fetch_cart['quantity'] * $fetch_cart['price'];
                  $grand_total += $sub_total;
            ?>
                  <tr>
                     <td>
                        <img src="images/<?php echo $fetch_cart['image']; ?>" alt="">
                        <div class="name"><?php echo $fetch_cart['name']; ?><br><span class="author"><?php echo $fetch_cart['author']; ?></span></div>
                     </td>
                     <td class="quantity">
                        <form action="" method="post">
                           <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                           <input type="number" min="1" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>">
                           <button type="submit" name="update_cart"><i class="fas fa-check"></i></button>
                        </form>
                     </td>
                     <td class="delete-product-cart"><a href="cart.php?delete=<?php echo $fetch_cart['id']; ?>" i class="fa-solid fa-x" onclick="return confirm('Steri din cos?');"></a></td>
                     <td class="price-buc"><?php echo $fetch_cart['price']; ?> lei</td>
                     <td class="subtotal"><?php echo $sub_total; ?> lei </td>
                  </tr>
            <?php
               }
            } else {
               echo '<tr><td colspan="5" class="empty">Cosul tau este gol!</td></tr>';
            }
            ?>
            <tr class="total-cart">
               <td colspan="3"></td>
               <td>Total:</td>
               <td><?php echo $grand_total; ?> lei</td>
            </tr>
         </tbody>
      </table>

      <div class="delete-all-container" style="text-align: center; margin-top: 2rem;">
         <a href="cart.php?delete_all" class="delete-btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('Șterge toate produsele din coș?');">Șterge toate produsele</a>
      </div>

      <div class="cart-total">
         <div class="flex">
            <a href="products.php" class="option-btn">Continuă cumpărăturile</a>
            <a href="checkout.php" class="btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>">Finalizare comandă</a>
         </div>
      </div>

   </section>

</div>

<?php include 'includes/footer.php'; ?>

<script src="script.js"></script>


</body>

</html>