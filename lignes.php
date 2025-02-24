<?php
declare(strict_types=1);
$apiKey = "a70070ce-be75-4d06-8990-60b61b005edc";
if (isset($_GET['q'])) {
    $where = "\"" . $_GET['q'] . "\"";
    $url0 = "https://ressources.data.sncf.com/api/explore/v2.1/catalog/datasets/liste-des-gares/records?where=$where&?limit=1";
    // Initialiser cURL
    $ch1 = curl_init($url0);

    // options cURL
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);

    // requête
    $gareResponse = curl_exec($ch1);

    // erreurs 
    if (curl_errno($ch1)) {
        echo "Erreur cURL: " . curl_error($ch1);
        die();
    }

    // Décoder le JSON
    $liste_des_gares = json_decode($gareResponse, true);

    $gare = $liste_des_gares["results"][0]["code_uic"]; //code uic gare
    $valGare = $liste_des_gares["results"][0]["libelle"];
} else {
    $gare = "87271007"; // Gare du Nord
    $valGare = "Paris-Nord";
}
$fromDateTime = date('Ymd') . "T000000"; // date d'aujourd'hui minuit
$untilDateTime = date("Ymd") . "T235959"; // date d'aujourd'hui 23h59 (59")

//URL de l'API
$url = "https://$apiKey@api.sncf.com/v1/coverage/sncf/stop_areas/stop_area:SNCF:$gare/departures";

// Initialiser cURL
$ch = curl_init($url);

// Définir les options cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Exécuter la requête
$response = curl_exec($ch);

// Gérer les erreurs cURL
if (curl_errno($ch)) {
    echo "Erreur cURL: " . curl_error($ch);
    die();
}

// Décoder le JSON
$data = json_decode($response, true);
curl_close($ch);


?>

<?php
$title = "Lignes";
require_once "includebis/fonctions.inc.php";
require "includebis/header.inc.php";

?>


<h1>Départs des Trains – Horaires en Direct et Recherche</h1>

<div class="search-section">
    <form method="get" action="./lignes.php" class="search-form">
        <label for="search">Rechercher :</label>
        <input type="text" id="search" placeholder="Entrer un nom de gare" name="q" />
        <input type="submit" value="Rechercher" />
    </form>


    <h2 class="heading2"><?= $valGare; ?></h2>
    <table>
        <thead>
            <tr>
                <th>Destination</th>
                <th>Heure de départ</th>
                <th>Ligne</th>
            </tr>
        </thead>
        <tbody>

            <?php
            departsTrains($data);
            ?>

        </tbody>
    </table>
</div>




<?php
require "./includebis/footer.inc.php";
?>