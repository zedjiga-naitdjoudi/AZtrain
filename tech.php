<?php
$title = "Page tech";
include 'includebis/header.inc.php'; ?>
<h1>NASA APOD</h1>
<section class="content2">
    <h2>Image du jour APOD (NASA)</h2>
    <section class="section">

        <?php
        $api_key = "3mFHy7rrkUuwn5Xxz7Jk5MkQNZ9lbWa19VZ4kukO"; // Remplacez ceci par votre clé API de la NASA.
        echo getNasaApod($api_key);
        ?>
    </section>
    <section class="localisation">
        <h2>Localisation</h2>
        <section class="localisation-xml">
            <h2>Localisation de l'utilisateur (XML)</h2>
            <?php
            $ip = $_SERVER['REMOTE_ADDR'];
            echo getGeoLocationInfo($ip);
            ?>
        </section>
        <section class="localisation-json">
            <h2>Localisation de l'utilisateur (JSON)</h2>
            <?php
            $ip = $_SERVER['REMOTE_ADDR'];
            echo getIPGeoLocation($ip);
            ?>
        </section>
    </section>
</section>
<?php include 'includebis/footer.inc.php'; ?>