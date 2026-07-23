<?php include 'includes/config.php';

$conn = getDB();

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

include 'includes/header.php';

?>

<div class="footer-container">
    <h1 class="footer-h1">Termeni și condiții</h1>
    <section>
        <h2 class="footer-description">Bun venit pe website-ul nostru! Accesarea și utilizarea acestui website
            sunt supuse următoarelor termeni și condiții. Prin utilizarea acestui website, sunteți de
            acord cu acești termeni și condiții. Vă rugăm să citiți cu atenție toate informațiile de mai
            jos înainte de a utiliza acest website.</h2>

        <h2 class="footer-h2">Drepturi de autor</h2>
        <p class="footer-p">Conținutul acestui website, inclusiv, dar fără a se limita la, texte, imagini,
            design, grafică și alte materiale, sunt protejate de drepturile de autor și alte drepturi de proprietate
            intelectuală. Utilizarea neautorizată a acestor materiale este strict interzisă și poate încălca legile
            privind drepturile de autor.</p>

        <h2 class="footer-h2">Utilizarea website-ului</h2>
        <p class="footer-p">Utilizarea acestui website este permisă în scopuri legale și conforme cu acești termeni
            și condiții. Nu sunteți autorizat să utilizați acest website în niciun mod care ar putea afecta
            funcționarea sau securitatea acestuia.</p>

        <h2 class="footer-h2">Informații personale</h2>
        <p class="footer-p">Colectăm și stocăm informațiile dvs. personale în conformitate cu politica noastră de
            confidențialitate. Prin utilizarea acestui website, sunteți de acord cu colectarea, stocarea și utilizarea
            informațiilor dvs. personale în conformitate cu această politică.</p>

        <h2 class="footer-h2">Modificări ale termenilor și condițiilor</h2>
        <p class="footer-p">Ne rezervăm dreptul de a modifica acești termeni și condiții în orice moment. Orice modificare
            va intra în vigoare imediat după publicarea pe acest website. Continuarea utilizării acestui website după modificarea
            termenilor și condițiilor reprezintă acceptarea dvs. a acestor modificări.</p>

        <h2 class="footer-h2">Politica de returnare</h2>
        <p class="footer-p">Politica noastră de returnare permite returnarea cărților achiziționate în termen de 30 de zile de la
            data achiziției. Pentru detalii suplimentare și instrucțiuni privind returnarea, vă rugăm să consultați pagina noastră
            de returnare.</p>
    </section>
</div>

<script src="script.js"></script>
<?php include 'includes/footer.php' ?>
</body>

</html>