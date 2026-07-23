<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T&M</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
            <div class="message">
                <span>' . $message . '</span>
                <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
            </div>
            ';
        }
    }
    ?>

    <header class="header">
        <div class="header-2">
            <div class="flex">
                <a href="index.php" class="logo">
                    <img src="./images/logo.jpg" alt="Logo" class="logo-img">
                </a>

                <nav class="navbar">
                    <a href="index.php">Acasă</a>
                    <a href="about_us.php">Despre</a>
                    <a href="products.php">Produse</a>
                </nav>

                <div class="icons">
                    <div id="menu-btn" class="fas fa-bars"></div>
                    <a id="search-icon" class="fas fa-search"></a>
                    <div id="search-bar" class="search-bar hidden">
                        <input type="text" id="search-input" placeholder="Caută o carte sau autor...">
                        <button id="search-button">Caută</button>
                    </div>
                    <div id="user-btn" class="fas fa-user"></div>
                    <?php
                    $cart_rows_number = 0;
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                        $cart_rows_number = mysqli_num_rows($select_cart_number);
                    }
                    ?>
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'cart.php' : 'login.php'; ?>"> <i class="fas fa-shopping-cart"></i> <span>(<?php echo $cart_rows_number; ?>)</span> </a>
                </div>

                <div class="user-box">
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <p>Nume de utilizator : <span><?php echo $_SESSION['user_name']; ?></span></p>
                        <p>Email : <span><?php echo $_SESSION['user_email']; ?></span></p>
                        <a href="logout.php" class="delete-btn">logout</a>
                    <?php else : ?>
                        <p class="client"><a href="login.php">Conectează-te</a> | <a href="register.php">Înregistrează-te</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.getElementById("search-icon").addEventListener("click", function() {
            const searchBar = document.getElementById("search-bar");
            searchBar.classList.toggle("hidden");
        });

        document
            .getElementById("search-button")
            .addEventListener("click", function(event) {
                event.preventDefault();
                performSearch();
            });

        document
            .getElementById("search-input")
            .addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    performSearch();
                }
            });

        function performSearch() {
            const query = document.getElementById("search-input").value.trim();
            if (query) {
                window.location.href = `products.php?search=${encodeURIComponent(query)}`;
            }
        }
    </script>