<section class="footer">

   <section class="newsletter">
      <h2>Abonează-te la newsletter-ul nostru</h2>
      <form action="" method="post">
         <input type="email" name="email" placeholder="Introdu adresa ta de email" required>
         <input type="submit" name="subscribe" value="Abonează-te">
      </form>
      <?php
      if (isset($_POST['subscribe'])) {
         $email = $_POST['email'];
         if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message[] = 'Mulțumim pentru abonare!';
         } else {
            $message[] = 'Adresa de email nu este validă!';
         }
      }
      ?>
   </section>


   <div class="box-container">

      <div class="box">
         <h3>Informații utile</h3>
         <a href="delivery.php">Livrare</a>
         <a href="terms.php">Termeni și condiții</a>
         <a href="about_us.php">Despre noi</a>
         <a href="contact_us.php">Contactează-ne</a>
      </div>

      <div class="box">
         <h3>contact info</h3>
         <p> <i class="fas fa-phone"></i> 0722109460 </p>
         <p> <i class="fas fa-phone"></i> 0722108460 </p>
         <p> <i class="fas fa-envelope"></i> alexmariustomescuk@gmail.com </p>
         <p> <i class="fas fa-map-marker-alt"></i> Bd. Petrolului, nr. 101 </p>
      </div>

      <div class="box">
         <h3>follow us</h3>
         <a href="#"> <i class="fab fa-facebook-f"></i> facebook </a>
         <a href="#"> <i class="fab fa-instagram"></i> instagram </a>
      </div>

   </div>

   <p class="credit"> &copy; copyright @ <?php echo date('Y'); ?> by <span>T&M</span> </p>

</section>