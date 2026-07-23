<?php

include 'includes/config.php';

$conn = getDB();

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `message` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_message.php');
}

?>

<?php include 'admin_header.php'; ?>

<section class="messages">

   <h1 class="title"> Mesaje </h1>

   <div class="box-container">
   <?php
      $select_message = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
      if(mysqli_num_rows($select_message) > 0){
         while($fetch_message = mysqli_fetch_assoc($select_message)){
      
   ?>
   <div class="box">
      <p> Id : <span><?php echo $fetch_message['user_id']; ?></span> </p>
      <p> Nume : <span><?php echo $fetch_message['name']; ?></span> </p>
      <p> Numar de telefon : <span><?php echo $fetch_message['number']; ?></span> </p>
      <p> Email: <span><?php echo $fetch_message['email']; ?></span> </p>
      <p> Mesaj : <span><?php echo $fetch_message['message']; ?></span> </p>
      <a href="admin_message.php?delete=<?php echo $fetch_message['id']; ?>" onclick="return confirm('Stergi acest mesaj?');" class="delete-btn">Sterge mesajul</a>
   </div>
   <?php
      };
   }else{
      echo '<p class="empty">Nu ai niciun mesaj!</p>';
   }
   ?>
   </div>

</section>


<script src="script_admin.js"></script>

</body>
</html>