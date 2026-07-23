<?php

include 'includes/config.php';

$conn = getDB();

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    $user_id = 0;
}

if (isset($_POST['add_to_cart'])) {
    if ($user_id == 0) {
        header('Location: login.php');
        exit;
    }

    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_author = $_POST['product_author'];
    $product_quantity = $_POST['quantity'] ?? 1;

    $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed: ' . mysqli_error($conn));

    if (mysqli_num_rows($check_cart_numbers) > 0) {
        $existing_cart_item = mysqli_fetch_assoc($check_cart_numbers);
        $new_quantity = $existing_cart_item['quantity'] + $product_quantity;
        mysqli_query($conn, "UPDATE `cart` SET quantity = '$new_quantity' WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed: ' . mysqli_error($conn));
    } else {
        $product_author = mysqli_real_escape_string($conn, $product_author);
        mysqli_query($conn, "INSERT INTO `cart`(user_id, name, author, price, image, quantity) VALUES('$user_id', '$product_name', '$product_author', '$product_price', '$product_image', '$product_quantity')") or die('query failed: ' . mysqli_error($conn));
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<section class="home">
    <div class="content">
        <h3>Profită acum de reducere la toate cărțile de 20%</h3>
        <p>În perioada 01-05-2024 - 07-05-2024 cu ajutorul codului t&m20 veți dispune de o reducere de 20% la totalul din coșul de cumpărături</p>
        <a href="products.php" class="white-btn">Caută acum!</a>
    </div>
</section>

<div class="row caracteristici">
    <div class="col-sm-4 col-xs-4">
        <i class="fa-solid fa-truck"></i>
        <div>
            <h4>Livrare în 48H</h4>
            <p>Produsele sunt livrate în maximum 48 de la finalizarea comenzii!
            <p>
        </div>
    </div>
    <div class="col-sm-4 col-xs-4">
        <i class="fa-solid fa-gift"></i>
        <div>
            <h4>Împachetare gratuită</h4>
            <p>Dacă doriți să faceți un cadou îl putem împacheta gratuit!</p>
        </div>
    </div>
    <div class="col-sm-4 col-xs-4">
        <i class="fa-solid fa-wallet"></i>
        <div>
            <h4>Siguranță la plată</h4>
            <p>Folosim tehnologia SSL pentru a asigura confidențialitate</p>
        </div>
    </div>
</div>

<div class="carousel">
    <>
        <li><a href="products.php?author[]=Frank%20Herbert"></href><img src="images/dune_banner.jpg" style="width:100%" alt="Dune"></a></li>
        <li><a href="products.php?author[]=Stephen%20King"><img src="images/stephen-king-books-in-order (1).jpg" alt="Carti stephen king"></a></li>
        <li><a href="products.php?author[]=George+R.R.+Martin"><img src="images/got.jpg" alt="Game of thornes"></a></li>
        </ul>


        <div class="carousel-btn prev" onclick="prevSlide()">&#10094;</div>
        <div class="carousel-btn next" onclick="nextSlide()">&#10095;</div>
</div>
<section class="products">

    <h1 class="title">Ultimele produse adăugate</h1>

    <div class="box-container">

        <?php
        $select_products = mysqli_query($conn, "SELECT * FROM `products` ORDER BY id DESC LIMIT 4") or die('query failed');
        if (mysqli_num_rows($select_products) > 0) {
            while ($fetch_products = mysqli_fetch_assoc($select_products)) {
        ?>
                <div class="box">
                    <a href="product_details.php?product_id=<?php echo $fetch_products['id']; ?>">
                        <img class="image" src="images/<?php echo $fetch_products['image']; ?>" alt="">
                    </a>
                    <div class="name"><?php echo $fetch_products['name']; ?></div>
                    <div class="author"><?php echo $fetch_products['author']; ?></div>
                    <div class="price"><?php echo $fetch_products['price']; ?> lei</div>
                    <form action="" method="post">
                        <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
                        <input type="hidden" name="product_author" value="<?php echo $fetch_products['author']; ?>">
                        <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                        <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
                        <input type="submit" value="Adauga in cos" name="add_to_cart" class="btn">
                    </form>
                </div>
        <?php
            }
        } else {
            echo '<p class="empty">Niciun produs adaugat in cos!</p>';
        }
        ?>
    </div>

</section>


<section class="weekly-recommendation">
    <div class="container">
        <h1>Recomandarea săptămânii</h1>
        <div class="recommendation">
            <img src="images/doctorsleep.jpg" alt="Cartea săptămânii">
            <div class="description">
                <h3>Doctor Sleep, autor Stephen King!</h3>
                <p class="full-description">Bântuit de stafiile din Hotelul Overlook, unde a petrecut un an de coșmar în copilărie,
                    Dan Torrance se chinuie de decenii întregi să scape de moștenirea tatălui său: o viață dominată de disperare,
                    alcoolism și violență. Stabilit în New Hampshire, el se angajează la un cămin de bătrâni, unde „strălucirea“
                    îl ajută să-i îndrume pe rezidenți cât mai lin spre ultimul drum.
                    Dar, când o întâlnește pe Abra Stone, a cărei „strălucire“ e cea mai puternică dintre toate, demonii lui Dan
                    revin la viață ca să îl ajute să se lupte pentru sufletul și supraviețuirea fetei.
                    Urmează o confruntare dramatică între forțele răului și ale binelui, cu tensiunile caracteristice lumilor
                    create de maestrul Stephen King.</p>
                <p class="mobile-description">Dan Torrance, bântuit de stafiile din Hotelul Overlook unde a petrecut un an de coșmar în copilărie,
                    stabilit în New Hampshire, el se angajează la un cămin de bătrâni, unde „strălucirea“ îl ajută să-i îndrume pe rezidenți cât mai
                    lin spre ultimul drum. Până când...</p>
                <a href="product_details.php?product_id=27" class="btn">Vezi detalii</a>
            </div>
        </div>
    </div>
</section>
<script src="script.js"></script>
<?php require "includes/footer.php" ?>
</body>

</html>