<?php

require 'includes/config.php';

$conn = getDB();

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location: login.php');
    exit;
}
?>
<?php require 'admin_header.php'; ?>

<section class="dashboard">

    <h1 class="title">DASHBOARD</h1>

    <div class="box-container">

        <div class="box">
            <?php
            $total_pendings = 0;
            $select_pending = mysqli_query($conn, "SELECT total_price FROM `orders` 
                                  WHERE status_payment = 'pending'") or die('query failed');
            if (mysqli_num_rows($select_pending) > 0) {
                while ($fetch_pendings = mysqli_fetch_assoc($select_pending)) {
                    $total_price = $fetch_pendings['total_price'];
                    $total_pendings += $total_price;
                };
            };
            ?>
            <h3><?php echo $total_pendings; ?> lei</h3>
            <p>Valoare totala in asteptare</p>
        </div>

        <div class="box">
            <?php
            $total_completed = 0;
            $select_completed = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE status_payment = 'completed'") or die('query failed');
            if (mysqli_num_rows($select_completed) > 0) {
                while ($fetch_completed = mysqli_fetch_assoc($select_completed)) {
                    $total_price = $fetch_completed['total_price'];
                    $total_completed += $total_price;
                };
            };
            ?>
            <h3><?php echo $total_completed; ?> lei</h3>
            <p>Valoare totala finalizata</p>
        </div>

        <div class="box">
            <?php
            $select_orders = mysqli_query($conn, "SELECT * FROM orders")
                or die("Query failed");
            $number_of_orders = mysqli_num_rows($select_orders);
            ?>
            <h3><?php echo $number_of_orders ?></h3>
            <p>Comenzi</p>
        </div>

        <div class="box">
            <?php
            $select_products = mysqli_query($conn, "SELECT * FROM products")
                or die("Query failed");
            $number_of_products = mysqli_num_rows($select_products);
            ?>
            <h3><?php echo $number_of_products ?></h3>
            <p>Produse adaugate</p>
        </div>

        <div class="box">
            <?php
            $select_users = mysqli_query($conn, "SELECT * FROM users WHERE user_type='user'")
                or die("Query failed");
            $number_of_users = mysqli_num_rows($select_users);
            ?>
            <h3><?php echo $number_of_users ?></h3>
            <p>Utilizatori</p>
        </div>

        <div class="box">
            <?php
            $select_admins = mysqli_query($conn, "SELECT * FROM users WHERE user_type ='admin'")
                or die("Query failed");
            $number_of_admins = mysqli_num_rows($select_admins);
            ?>
            <h3><?php echo $number_of_admins ?></h3>
            <p>Admini</p>
        </div>
        <script src="script_admin.js"></script>
        </body>

        </html>