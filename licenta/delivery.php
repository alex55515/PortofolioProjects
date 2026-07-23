<?php include 'includes/config.php';

$conn = getDB();

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

include 'includes/header.php';

?>
<div class="footer-container">
    <h1 class="footer-h1">Livrare</h1>
    <p>
    <h2 class="footer-description">Expedierea pachetului dumneavoastră este o parte crucială a experienței de cumpărare
        la T&M Carti. Ne străduim să vă oferim cele mai bune servicii de expediere, pentru ca pachetele să ajungă la destinație
        în siguranță și în cel mai scurt timp posibil.</h2>

    <h2 class="footer-h2">Despre livrare</h2>
    <p class="footer-p">Pachetele sunt în general pregătite pentru expediere în termen de 2 zile lucrătoare de la primirea plății
        dumneavoastră. Ne asigurăm că toate articolele sunt ambalate cu grijă și atenție pentru a fi protejate în timpul transportului.
        Folosim servicii de expediere de încredere, precum UPS, care oferă opțiuni de urmărire și livrare fără semnătură.</p>

    <h2 class="footer-h2">Despre costul livrării</h2>
    <p class="footer-p">Costul livrării include atât costurile de procesare și ambalare, cât și costurile poștale. Procesarea și
        ambalarea sunt taxate la un tarif fix, în timp ce costul transportului poate varia în funcție de greutatea totală a pachetului.
        Pentru a optimiza costurile, vă recomandăm să grupați toate articolele într-o singură comandă. Acest lucru vă va ajuta să economisiți
        și să beneficiați de o livrare mai eficientă.</p>

    <h2 class="footer-h2">Riscurile privind livrările</h2>
    <p class="footer-p">Deși facem tot posibilul să protejăm obiectele fragile, riscurile aferente livrării cad în responsabilitatea dumneavoastră.
        Cu toate acestea, ne luăm angajamentul de a folosi ambalaje generos dimensionate și de a asigura că articolele dumneavoastră sunt protejate
        în cel mai bun mod posibil pe parcursul întregului proces de livrare.</p> <br>
    <p class="footer-p">La T&M Carti, ne pasă de fiecare detaliu al experienței dumneavoastră de cumpărare, inclusiv de modul în care pachetele dumneavoastră sunt
        expediate și livrate. Vă mulțumim că ați ales să faceți parte din comunitatea noastră și vă asigurăm că vom continua să depunem eforturi pentru
        a vă oferi cele mai bune servicii posibile.</p>
    </section>
</div>

<script src="script.js"></script>

<?php include 'includes/footer.php' ?>
</body>

</html>