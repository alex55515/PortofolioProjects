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

$filter_category = $_GET['category'] ?? [];
$filter_author = $_GET['author'] ?? [];
$filter_price = $_GET['price'] ?? [];
$search_query = $_GET['search'] ?? '';

$page_no = $_GET['page_no'] ?? 1;
$sort_option = $_GET['sort'] ?? 'id_desc';

$filter_condition = [];
$params = [];

if (!empty($filter_category)) {
   $filter_condition[] = "type IN (" . str_repeat('?,', count($filter_category) - 1) . "?)";
   $params = array_merge($params, $filter_category);
}
if (!empty($filter_author)) {
   $filter_condition[] = "author IN (" . str_repeat('?,', count($filter_author) - 1) . "?)";
   $params = array_merge($params, $filter_author);
}
if (!empty($filter_price)) {
   $price_conditions = [];
   foreach ($filter_price as $price_range) {
      if ($price_range === '100+') {
         $price_conditions[] = "price >= ?";
         $params[] = 100;
      } else {
         $range = explode('-', $price_range);
         $price_conditions[] = "price BETWEEN ? AND ?";
         $params[] = $range[0];
         $params[] = $range[1];
      }
   }
   $filter_condition[] = '(' . implode(' OR ', $price_conditions) . ')';
}
if (!empty($search_query)) {
   $filter_condition[] = "(name LIKE ? OR author LIKE ?)";
   $params[] = "%" . $search_query . "%";
   $params[] = "%" . $search_query . "%";
}

$filter_sql = !empty($filter_condition) ? "WHERE " . implode(" AND ", $filter_condition) : "";

$stmt = $conn->prepare("SELECT COUNT(*) as total_records FROM products $filter_sql");
$stmt->execute();
$stmt->bind_result($total_records);
$stmt->store_result();
$stmt->fetch();

$total_records_per_page = 10;
$offset = ($page_no - 1) * $total_records_per_page;
$total_no_of_pages = ceil($total_records / $total_records_per_page);

$order_sql = "ORDER BY ";
if ($sort_option == 'id_desc') {
   $order_sql .= "id DESC";
} elseif ($sort_option == 'price_desc') {
   $order_sql .= "price DESC";
} elseif ($sort_option == 'name_asc') {
   $order_sql .= "name ASC";
} elseif ($sort_option == 'name_desc') {
   $order_sql .= "name DESC";
} else {
   $order_sql .= "price ASC";
}

$sql = "SELECT * FROM products $filter_sql $order_sql LIMIT ?, ?";
$params[] = $offset;
$params[] = $total_records_per_page;

$stmt2 = $conn->prepare($sql);

if ($stmt2 === false) {
   die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$types = str_repeat('s', count($params) - 2) . 'ii';
$stmt2->bind_param($types, ...$params);

$stmt2->execute();
$products = $stmt2->get_result();

function paginate($current_page, $total_pages, $sort_option)
{
   $pagination = '';

   if ($total_pages <= 1) return '';

   $pagination .= '<ul>';

   if ($current_page > 1) {
      $pagination .= '<li><a href="?page_no=1&sort=' . $sort_option . '">«</a></li>';
      $pagination .= '<li><a href="?page_no=' . ($current_page - 1) . '&sort=' . $sort_option . '">‹</a></li>';
   }

   if ($current_page > 3) {
      $pagination .= '<li><a href="?page_no=1&sort=' . $sort_option . '">1</a></li>';
      $pagination .= '<li><span class="ellipsis">...</span></li>';
   }

   for ($i = max(1, $current_page - 1); $i <= min($total_pages, $current_page + 1); $i++) {
      if ($i == $current_page) {
         $pagination .= '<li><span class="current">' . $i . '</span></li>';
      } else {
         $pagination .= '<li><a href="?page_no=' . $i . '&sort=' . $sort_option . '">' . $i . '</a></li>';
      }
   }

   if ($current_page < $total_pages - 2) {
      $pagination .= '<li><span class="ellipsis">...</span></li>';
      $pagination .= '<li><a href="?page_no=' . $total_pages . '&sort=' . $sort_option . '">' . $total_pages . '</a></li>';
   }

   if ($current_page < $total_pages) {
      $pagination .= '<li><a href="?page_no=' . ($current_page + 1) . '&sort=' . $sort_option . '">›</a></li>';
      $pagination .= '<li><a href="?page_no=' . $total_pages . '&sort=' . $sort_option . '">»</a></li>';
   }

   $pagination .= '</ul>';

   return $pagination;
}


?>

<?php include 'includes/header.php'; ?>

<section class="products-page">
   <div class="product-filters">
      <div class="filter-title">
         <h3>Filtrează după:</h3>
      </div>
      <div class="filters">
         <form action="" method="get">
            <div class="filter-category">
               <h4>Categorie:</h4>
               <label><input type="checkbox" name="category[]" value="Science Fiction" <?php if (in_array('Science Fiction', $filter_category)) echo 'checked'; ?>> Science Fiction</label><br>
               <label><input type="checkbox" name="category[]" value="Dezvoltare personală" <?php if (in_array('Dezvoltare personală', $filter_category)) echo 'checked'; ?>> Dezvoltare personală</label><br>
               <label><input type="checkbox" name="category[]" value="Fantasy" <?php if (in_array('Fantasy', $filter_category)) echo 'checked'; ?>> Fantasy</label><br>
               <label><input type="checkbox" name="category[]" value="Aventura" <?php if (in_array('Aventura', $filter_category)) echo 'checked'; ?>> Aventura</label>
            </div>
            <div class="filter-author">
               <h4>Autor:</h4>
               <?php
               $select_authors = mysqli_query($conn, "SELECT DISTINCT author FROM `products`") or die('query failed');
               while ($fetch_author = mysqli_fetch_assoc($select_authors)) {
                  $author = $fetch_author['author'];
                  echo "<label><input type='checkbox' name='author[]' value='$author'";
                  if (in_array($author, $filter_author)) echo 'checked';
                  echo ">$author</label><br>";
               }
               ?>
            </div>
            <div class="filter-price">
               <h4>Preț:</h4>
               <label><input type="checkbox" name="price[]" value="20-40" <?php if (in_array('20-40', $filter_price)) echo 'checked'; ?>> 20-40 lei</label><br>
               <label><input type="checkbox" name="price[]" value="40-60" <?php if (in_array('40-60', $filter_price)) echo 'checked'; ?>> 40-60 lei</label><br>
               <label><input type="checkbox" name="price[]" value="60-80" <?php if (in_array('60-80', $filter_price)) echo 'checked'; ?>> 60-80 lei</label><br>
               <label><input type="checkbox" name="price[]" value="80-100" <?php if (in_array('80-100', $filter_price)) echo 'checked'; ?>> 80-100 lei</label><br>
               <label><input type="checkbox" name="price[]" value="100+" <?php if (in_array('100+', $filter_price)) echo 'checked'; ?>> Peste 100 lei</label><br>
            </div>
            <input type="submit" value="Filtrează" class="btn">
            <input type="submit" value="Șterge" onclick="clearFilters()" class="btn">
         </form>
      </div>
   </div>

   <div class="products-container-with-pagination">
      <div class="filter-sort-pagination-container">
         <button class="toggle-filters-btn" onclick="toggleFilters()">Filtrează</button>
         <div class="sorting">
            <form method="get" action="">
               <label for="sort">Sortează după:</label>
               <select name="sort" id="sort" onchange="this.form.submit()">
                  <option value="price_asc" <?php if ($sort_option == 'price_asc') echo 'selected'; ?>>Preț crescător</option>
                  <option value="id_desc" <?php if ($sort_option == 'id_desc') echo 'selected'; ?>>Noutate</option>
                  <option value="price_desc" <?php if ($sort_option == 'price_desc') echo 'selected'; ?>>Preț descrescător</option>
                  <option value="name_asc" <?php if ($sort_option == 'name_asc') echo 'selected'; ?>>Alfabetic (ascendent)</option>
                  <option value="name_desc" <?php if ($sort_option == 'name_desc') echo 'selected'; ?>>Alfabetic (descendent)</option>
               </select>
            </form>
         </div>
         <div class="pagination">
            <?php echo paginate($page_no, $total_no_of_pages, $sort_option); ?>
         </div>
      </div>

      <div class="products-container">
         <?php
         if ($products->num_rows > 0) {
            while ($fetch_products = $products->fetch_assoc()) {
         ?>
               <div class="box">
                  <a href="product_details.php?product_id=<?php echo $fetch_products['id']; ?>">
                     <img class="image" src="images/<?php echo $fetch_products['image']; ?>" alt="">
                     <div class="name"><?php echo $fetch_products['name']; ?></div>
                  </a>
                  <a href="products.php?author[]=<?php echo urlencode($fetch_products['author']); ?>">
                     <div class="author"><?php echo $fetch_products['author']; ?></div>
                  </a>
                  <div class="price"><?php echo $fetch_products['price']; ?> lei</div>
                  <form action="" method="post">
                     <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
                     <input type="hidden" name="product_author" value="<?php echo $fetch_products['author']; ?>">
                     <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                     <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
                     <input type="hidden" name="product_quantity" value="1">
                     <input type="submit" value="Adaugă în coș" name="add_to_cart" class="btn">
                  </form>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">Nu au fost adăugate produse!</p>';
         }
         ?>
      </div>
      <div class="pagination">
         <?php echo paginate($page_no, $total_no_of_pages, $sort_option); ?>
      </div>
   </div>
</section>

<?php include 'includes/footer.php'; ?>

<script src="script.js"></script>

</body>

</html>