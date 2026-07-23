<?php

include 'includes/config.php';

$conn = getDB();

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
};

$message = array();

if (isset($_POST['add_product'])) {
   if (isset(
      $_POST['name'],
      $_POST['author'],
      $_POST['price'],
      $_POST['type'],
      $_POST['description'],
      $_POST['pages'],
      $_FILES['image']
   )) {
      $name = mysqli_real_escape_string($conn, $_POST['name']);
      $author = mysqli_real_escape_string($conn, $_POST['author']);
      $price = $_POST['price'];
      $type = $_POST['type'];
      $description = mysqli_real_escape_string($conn, $_POST['description']);
      $pages = $_POST['pages'];
      $image = $_FILES['image']['name'];
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'images/' . $image;

      $select_product_name = mysqli_query($conn, "SELECT name 
      FROM `products` WHERE name = '$name'") or die('query failed');

      if (mysqli_num_rows($select_product_name) > 0) {
         $message[] = 'Numele produsului este deja folosit.';
      } else {
         if ($image_size > 2000000) {
            $message[] = 'Imaginea este prea mare.';
         } else {
            move_uploaded_file($image_tmp_name, $image_folder);

            $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image, author, type, description, pages) 
            VALUES('$name', '$price', '$image', '$author', '$type', '$description', '$pages')") or die('query failed');

            if ($add_product_query) {
               $message[] = 'Produsul a fost adaugat cu succes!';
            } else {
               $message[] = 'Produsul nu a putut fi adaugat!';
            }
         }
      }
   } else {
      $message[] = 'Toate câmpurile sunt obligatorii!';
   }
}


if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
   $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
   $image_path = 'images/' . $fetch_delete_image['image'];
   if (file_exists($image_path)) {
      unlink($image_path);
   }
   mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_products.php');
}

if (isset($_POST['update_product'])) {
   if (isset($_POST['update_p_id'], $_POST['update_name'], $_POST['update_author'], $_POST['update_price'], $_POST['update_type'], $_POST['update_description'], $_POST['update_pages'])) {
      $update_p_id = $_POST['update_p_id'];
      $update_name = $_POST['update_name'];
      $update_author = $_POST['update_author'];
      $update_price = $_POST['update_price'];
      $update_type = $_POST['update_type'];
      $update_description = $_POST['update_description'];
      $update_pages = $_POST['update_pages'];

      mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price', author ='$update_author', type = '$update_type', description = '$update_description', pages = '$update_pages' WHERE id = '$update_p_id'") or die('query failed');

      $update_image = $_FILES['update_image']['name'];
      $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
      $update_image_size = $_FILES['update_image']['size'];
      $update_folder = 'images/' . $update_image;
      $update_old_image = $_POST['update_old_image'];

      if (!empty($update_image)) {
         if ($update_image_size > 2000000) {
            $message[] = 'image file size is too large';
         } else {
            mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('query failed');
            move_uploaded_file($update_image_tmp_name, $update_folder);
            unlink('images/' . $update_old_image);
         }
      }

      header('location:admin_products.php');
   } else {
      $message[] = 'Toate câmpurile sunt obligatorii!';
   }
}

?>

<?php include 'admin_header.php'; ?>

<section class="add-products">
   <?php
   if (isset($message) && is_array($message) && !empty($message)) {
      foreach ($message as $msg) {
         echo '<p>' . $msg . '</p>';
      }
   }
   ?>

   <form action="" method="post" enctype="multipart/form-data">
      <h3>Adauga produs</h3>
      <input type="text" name="name" class="box" placeholder="Introdu numele produsului" required>
      <input type="text" name="author" class="box" placeholder="Introdu numele autorului" required>
      <input type="number" min="0" name="price" class="box" placeholder="Introdu pretul produsului" required>
      <input type="number" min="0" name="pages" class="box" placeholder="Introdu numărul de pagini" required>
      <textarea name="description" class="box" placeholder="Introdu descrierea produsului"></textarea>
      <select name="type" class="box" required>
         <option value="">Selecteaza tipul produsului</option>
         <option value="Dezvoltare Personala">Dezvoltare Personală</option>
         <option value="Fantasy">Fantasy</option>
         <option value="Aventură">Aventură</option>
         <option value="Science Fiction">Science Fiction</option>
      </select>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" placeholder="Niciun fișier selectat" class="box" required>
      <input type="submit" value="Adauga produs" name="add_product" class="btn">
   </form>


</section>

<section class="show-products">
   <hr>
   </hr>
   <div class="title">
      <h6>Actualizare sau ștergere produse</h6>
      <div class="search-container">
         <input type="text" id="searchInput" onkeyup="searchProducts()" placeholder="Caută carte">
         <button type="button" id="searchButton" onclick="searchProducts()">Caută</button>
      </div>
   </div>
   <hr>
   <div class="box-container">
      <?php
      $select_products = mysqli_query($conn, "SELECT id, name, author, price, type, image FROM `products`") or die('query failed');
      if (mysqli_num_rows($select_products) > 0) {
         while ($fetch_products = mysqli_fetch_assoc($select_products)) {
      ?>
            <div class="box">
               <img src="images/<?php echo $fetch_products['image']; ?>" alt="">
               <div class="name"><?php echo $fetch_products['name']; ?></div>
               <div class="author"><?php echo $fetch_products['author']; ?></div>
               <div class="price"><?php echo $fetch_products['price']; ?> lei</div>
               <div class="type"><?php echo $fetch_products['type']; ?></div>
               <a href="admin_products.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">Editează</a>
               <a href="admin_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Stergi acest produs?');">Șterge</a>
            </div>
      <?php
         }
      } else {
         echo '<p class="empty">Niciun produs adăugat încă!</p>';
      }
      ?>
   </div>

   <section class="edit-product-form">
      <?php
      if (isset($_GET['update'])) {
         $update_id = $_GET['update'];
         $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'") or die('query failed');
         if (mysqli_num_rows($update_query) > 0) {
            while ($fetch_update = mysqli_fetch_assoc($update_query)) {
      ?>
               <form action="" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
                  <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
                  <img src="images/<?php echo $fetch_update['image']; ?>" alt="">
                  <input type="text" name="update_name" value="<?php echo $fetch_update['name']; ?>" class="box" required placeholder="Introdu numele produsului">
                  <input type="text" name="update_author" value="<?php echo $fetch_update['author']; ?>" class="box" required placeholder="Introdu numele autorului">
                  <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" class="box" required placeholder="Introdu pretul produsului">
                  <input type="number" name="update_pages" value="<?php echo $fetch_update['pages']; ?>" min="0" class="box" required placeholder="Introdu numărul de pagini">
                  <textarea name="update_description" class="box" placeholder="Introdu descrierea produsului"><?php echo $fetch_update['description']; ?></textarea>
                  <select name="update_type" class="box" required>
                     <option value="Dezvoltare Personala" <?php if ($fetch_update['type'] == 'Dezvoltare Personala') echo 'selected'; ?>>Dezvoltare Personala</option>
                     <option value="Fantasy" <?php if ($fetch_update['type'] == 'Fantasy') echo 'selected'; ?>>Fantasy</option>
                     <option value="Aventură" <?php if ($fetch_update['type'] == 'Aventură') echo 'selected'; ?>>Aventură</option>
                     <option value="Science Fiction" <?php if ($fetch_update['type'] == 'Science Fiction') echo 'selected'; ?>>Science Fiction</option>
                  </select>
                  <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
                  <input type="submit" value="Actualizeaza" name="update_product" class="btn">
                  <input type="reset" value="Anuleaza" id="close-update" class="option-btn">
               </form>
      <?php
            }
         }
      } else {
         echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
      }
      ?>

   </section>

   <script src="script_admin.js"></script>

   </body>

   </html>