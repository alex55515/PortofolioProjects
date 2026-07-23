<?php include 'includes/config.php';

$conn = getDB();

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

include 'includes/header.php';

?>
<div class="footer-container">
    <h1 class="footer-h1">Date de contact</h1>
    <div class="row">
        <div class="col-md-4 contact" style="margin-top: 16px">
            <p class="footer-p">Pagina: <a href="index.php" target="_blank">T&M</a></p>
            <p class="footer-p">
                Adresa de email:
                <a href="mailto:alexmariustomescuk@gmail.com" target="_blank">alexmariustomescuk@gmail.com</a>
            </p>
            <p class="footer-p">Numar de telefon: 0722109460</p>
            <p class="footer-p">Adresa: Strada Cosmesti, nr. 17, Ploiești</p>
            <p class="footer-p">Program:</p>
            <p class="footer-p">Luni-Vineri: 10:00-22:00</p>
            <p class="footer-p">Sambata-Duminica: 10:00-21:00</p>
        </div>
        <div class="col-md-8 harta" style="margin:10px 10px 0px 10px; padding-bottom: 10px;">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2825.1659198408706!2d26.035715676111064!3d44.919961271070164!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40b249de231422a1%3A0x14c9149399946614!2sStrada%20Cosme%C8%99ti%2017%2C%20Ploie%C8%99ti!5e0!3m2!1sen!2sro!4v1705232718595!5m2!1sen!2sro" width="100%" height="400px" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</div>

<script src="script.js"></script>

<?php include 'includes/footer.php' ?>
</body>

</html>