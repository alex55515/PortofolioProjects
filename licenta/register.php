<?php

require 'includes/config.php';

$conn = getDB();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tomescu's Store</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-image: url('images/login.jpg');
            background-size: cover;
        }
    </style>
</head>

<body>

    <?php

    if (isset($_POST['submit'])) {

        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
        $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));

        $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'") or die('query failed');

        if (mysqli_num_rows($select_users) > 0) {
            $message[] = 'Utilizatorul deja exista!';
        } else {
            if ($pass != $cpass) {

                $message[] = 'Parolele nu se potrivesc!';
            } else {

                mysqli_query($conn, "INSERT INTO `users`(name, email, password) VALUES('$name', '$email', '$cpass')") or die('query failed');
                $message[] = 'Înregistrarea a avut succes!';
                header('location:login.php');
            }
        }
    }
    ?>

    <?php

    if (isset($message)) {
        foreach ($message as $msg) {
            echo '
      <div class="message">
         <span>' . $msg . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
        }
    }
    ?>

    <div class="form-container">

        <form action="" method="post">
            <h3>Intregistrează-te!</h3>
            <input type="text" name="name" placeholder="Introdu numele" required class="box">
            <input type="email" name="email" placeholder="Introdu emailul" required class="box">
            <input type="password" name="password" placeholder="Introdu parola" required class="box">
            <input type="password" name="cpassword" placeholder="Reintrodu parola" required class="box">
            <input type="submit" name="submit" value="Register" class="btn">
            <p>Ai deja cont creat? <a href="login.php">Conectează-te!</a></p>
        </form>

    </div>