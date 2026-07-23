<?php

include 'includes/config.php';

$conn = getDB();

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_users.php');
}

if(isset($_POST['update_user'])){
   $user_id = $_POST['user_id'];
   $user_type = $_POST['user_type'];
   mysqli_query($conn, "UPDATE `users` SET user_type = '$user_type' WHERE id = '$user_id'") or die('query failed');
   header('location:admin_users.php');
}

?>

<?php include 'admin_header.php'; ?>

<section class="users">

   <h1 class="title">Conturile utilizatorilor</h1>

   <div class="box-container">
      <?php
         $select_users = mysqli_query($conn, "SELECT * FROM `users`") or die('query failed');
      ?>
      <table>
         <thead>
            <tr>
               <th>Id</th>
               <th>Nume de utilizator</th>
               <th>Email</th>
               <th>Rol</th>
               <th>Acțiuni</th>
            </tr>
         </thead>
         <tbody>
            <?php
               while($fetch_users = mysqli_fetch_assoc($select_users)){
            ?>
            <tr>
               <td><?php echo $fetch_users['id']; ?></td>
               <td><?php echo $fetch_users['name']; ?></td>
               <td><?php echo $fetch_users['email']; ?></td>
               <td>
                  <form action="" method="post">
                     <input type="hidden" name="user_id" value="<?php echo $fetch_users['id']; ?>">
                     <select name="user_type">
                        <option value="user" <?php if($fetch_users['user_type'] == 'user') echo 'selected'; ?>>Utilizator</option>
                        <option value="admin" <?php if($fetch_users['user_type'] == 'admin') echo 'selected'; ?>>Admin</option>
                     </select>
               </td>
               <td>
                     <input type="submit" value="Update" name="update_user" class="option-btn">
                  </form>
                  <a href="admin_users.php?delete=<?php echo $fetch_users['id']; ?>" onclick="return confirm('Sterge acest utilizator?');" class="delete-btn">Șterge</a>
               </td>
            </tr>
            <?php
               }
            ?>
         </tbody>
      </table>
   </div>

</section>


<script src="script_admin.js"></script>

</body>
</html>
