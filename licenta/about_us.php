<?php include 'includes/config.php';

$conn = getDB();

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

include 'includes/header.php';

?>
<div class="footer-container">
    <h1 class="footer-h1">Despre noi</h1>
    <section>
        <h2 class="footer-description">Bine ai venit la T&M Carti, destinația ta online pentru cărți de calitate și
            servicii excepționale! La T&M Carti, ne mândrim cu pasiunea noastră pentru literatură și cu angajamentul
            nostru față de clienți.</h2>
        <img id="logo" src="images/logo.jpg">

        <h2 class="footer-h2">Misiunea Noastră</h2>
        <p class="footer-p">Misiunea noastră este să aducem bucurie și inspirație prin intermediul cărților.
            Ne străduim să oferim o gamă diversificată de cărți, de la cele clasice până la cele mai recente apariții,
            acoperind o varietate de genuri și subiecte. Ne dorim să fim o sursă de cunoaștere, relaxare și divertisment
            pentru toți pasionații de lectură.</p>

        <h2 class="footer-h2">Colecții de Cărți</h2>
        <p class="footer-p">La T&M Carti, găsești o selecție vastă de cărți pentru toate gusturile și interesele.
            De la cărți clasice ale literaturii universale până la cele mai recente bestseller-uri, suntem aici pentru
            a-ți satisface setea de cunoaștere și aventură literară.</p>

        <h2 class="footer-h2">Experiența Noastră</h2>
        <p class="footer-p">Cu o experiență vastă în industria cărților și o echipă dedicată, ne asigurăm că fiecare
            client primește servicii de cea mai înaltă calitate. De la navigarea simplă pe website-ul nostru până la
            procesul rapid și sigur de comandă și livrare promptă, ne străduim să facem experiența de cumpărare la
            T&M Carti cât mai plăcută și lipsită de griji posibil.</p>
    </section>
</div>

<script src="script.js"></script>

<?php include 'includes/footer.php' ?>
</body>

</html>