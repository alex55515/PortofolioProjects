<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin</title>
   <link rel="stylesheet" href="style_admin.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>

   <?php

   if (isset($message)) {
      foreach ($message as $message) {
         echo '<div class="message>
           <span>' . $message . '</span>
           <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
   </div>
       ';
      }
   }

   ?>

   <header class="header">

      <div class="flex">

         <a href="admin_main.php" class="logo">Admin</a>

         <nav class="navbar">
            <a href="admin_main.php">Acasa</a>
            <a href="admin_products.php">Produse</a>
            <a href="admin_orders.php">Comenzi</a>
            <a href="admin_users.php">Utilizatori</a>
         </nav>

         <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
         </div>

         <div class="account-box">
            <p>Nume de utilizator : <span><?php echo $_SESSION['admin_name']; ?></span></p>
            <p>Email : <span><?php echo $_SESSION['admin_email']; ?></span></p>
            <a href="logout.php" class="delete-btn">logout</a>
         </div>

      </div>

   </header>