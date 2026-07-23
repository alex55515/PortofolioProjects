<?php

include 'includes/config.php';

$conn = getDB();

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
}

if (isset($_POST['update_order'])) {
   $order_update_id = $_POST['order_id'];
   $status_payment = $_POST['update_payment'];
   mysqli_query($conn, "UPDATE `orders` SET status_payment = '$status_payment' WHERE id = '$order_update_id'") or die('query failed');
   $message[] = 'Metoda de plata s-a actualizat!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_orders.php');
}

?>

<?php include 'admin_header.php'; ?>

<section class="orders">

   <h1 class="title">Comenzi plasate</h1>

   <div class="box-container">
      <?php
      $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
      if (mysqli_num_rows($select_orders) > 0) {
      ?>
         <table style="overflow-x:auto;">
            <thead>
               <tr>
                  <th>Id</th>
                  <th>User ID</th>
                  <th>Data</th>
                  <th>Nume</th>
                  <th>Prenume</th>
                  <th>Numar de telefon</th>
                  <th>Email</th>
                  <th>Adresa</th>
                  <th>Produse</th>
                  <th>Pretul</th>
                  <th>Tipul comenzii</th>
                  <th>Actiuni</th>
               </tr>
            </thead>
            <tbody>
               <?php
               while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
               ?>
                  <tr>
                     <td><?php echo $fetch_orders['id']; ?></td>
                     <td><?php echo $fetch_orders['user_id']; ?></td>
                     <td><?php echo $fetch_orders['date']; ?></td>
                     <td><?php echo $fetch_orders['last_name']; ?></td>
                     <td><?php echo $fetch_orders['first_name']; ?></td>
                     <td><?php echo $fetch_orders['number']; ?></td>
                     <td><?php echo $fetch_orders['email']; ?></td>
                     <td><?php echo $fetch_orders['address']; ?></td>
                     <td><?php echo $fetch_orders['total_products']; ?></td>
                     <td><?php echo $fetch_orders['total_price']; ?> lei</td>
                     <td>
                        <form action="" method="post">
                           <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                           <select name="update_payment">
                              <?php if ($fetch_orders['status_payment'] == 'pending') : ?>
                                 <option value="pending" selected>In asteptare</option>
                                 <option value="completed">Finalizata</option>
                              <?php else : ?>
                                 <option value="pending">In asteptare</option>
                                 <option value="completed" selected>Finalizata</option>
                              <?php endif; ?>
                           </select>
                     </td>
                     <td>
                        <input type="submit" value="Update" name="update_order" class="option-btn">
                        <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" onclick="return confirm('Stergi aceasta comanda?');" class="delete-btn">Sterge</a>
                        </form>
                     </td>
                  </tr>
               <?php
               }
               ?>
            </tbody>
         </table>
      <?php
      } else {
         echo '<p class="empty">Nicio comanda plasata!</p>';
      }
      ?>
   </div>

</section>

<script src="script_admin.js"></script>

</body>

</html>