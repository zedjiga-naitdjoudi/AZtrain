<?php
declare(strict_types=1);
$title = "Departs Trains";
$h1 = "Consultez les horaires de passage ainsi que les informations relatives aux gares";
$description = "Deuxième partie du projet développement web";
require "./includebis/header.inc.php";


function saveToCSV($data)
{
    $file = 'consultations_gares.csv';
    $handle = fopen($file, 'a');

    if ($handle !== false) {
        // Convertir en MAJ
        $data['from'] = strtoupper($data['from']);
        $data['to'] = strtoupper($data['to']);

        fputcsv($handle, $data);

        fclose($handle);
    } else {
        echo "Impossible d'ouvrir le fichier CSV pour l'écriture.";
    }
}

function searchStationByName($stationName): array
{
    $url = "https://api.navitia.io/v1/coverage/fr-idf/places?q={$stationName}&type[]=stop_area";
    $headers = [
        'Authorization: 30cd424f-eab0-4148-86e9-67f200488920'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        echo "Erreur CURL : " . curl_error($ch);
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status == 200) {
        $data = json_decode($response, true);
        if (!empty($data['places'])) {
            return $data['places'];
        }
    }
    return [];
}

function getStationId($stationName): ?string
{
    $encodedStationName = urlencode($stationName);
    $url = "https://api.navitia.io/v1/coverage/fr-idf/places?q={$encodedStationName}&type[]=stop_area";
    $headers = [
        'Authorization: 30cd424f-eab0-4148-86e9-67f200488920'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        echo "Erreur CURL : " . curl_error($ch);
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status == 200) {
        $data = json_decode($response, true);
        if (!empty($data['places'])) {
            return $data['places'][0]['id'];
        }
    }
    return null;
}

function makeNavitiaApiRequest($fromStation, $toStation, $date, $time): string
{
    $fromStationId = getStationId($fromStation);
    $toStationId = getStationId($toStation);

    if (!$fromStationId || !$toStationId) {
        return "Il est impossible de repérer l'identifiant de la gare à partir des noms fournis.";
    }

    $url = "https://api.navitia.io/v1/coverage/fr-idf/journeys?from={$fromStationId}&to={$toStationId}&datetime={$date}T{$time}:00&max_nb_journeys=5";
    $headers = [
        'Authorization: 30cd424f-eab0-4148-86e9-67f200488920'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        echo "Erreur CURL : " . curl_error($ch);
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status == 200) {
        return $response;
    } else {
        return "Une erreur s'est produite lors de la récupération des données.";
    }
}

function displayResponse($response): string
{
    $res = "";
    $obj = json_decode($response);
    if (isset($obj->journeys)) {
        $journeyNumber = 1;
        foreach ($obj->journeys as $journey) {
            $res .= "<div class='itinerary'>";
            $res .= "<h3 class='heading3'>Itinéraire " . $journeyNumber . " :</h3>";
            $res .= "<div class='journey-details'>";
            foreach ($journey->sections as $section) {
                if ($section->type == 'public_transport') {
                    $res .= "<div class='depart-arrivee-container'>";
                    $res .= "<span>Départ : " . ($section->from->name ?? 'Non spécifié') . "</span>";
                    $res .= "<span>Arrivée : " . ($section->to->name ?? 'Non spécifié') . "</span>";
                    $res .= "</div>"; // Close depart-arrivee-container
                    $res .= "<div class='ligne-direction-container'>";
                    $res .= "<span>Ligne : " . ($section->display_informations->code ?? 'Non spécifié') . "</span>";
                    $res .= "<span>Direction : " . ($section->display_informations->direction ?? 'Non spécifié') . "</span>";
                    $res .= "</div>"; // Close ligne-direction-container

                    if (isset($section->stop_date_times) && !empty($section->stop_date_times)) {
                        $stops = array_map(function ($stop) {
                            return $stop->stop_point->name ?? 'Non spécifié';
                        }, $section->stop_date_times);
                        $res .= "<button onclick='toggleStops(this)'>Voir les détails de l'itinéraire</button>";
                        $res .= "<ul class='stops-list' style='display:none;'>"; // La liste est cachée par défaut
                        foreach ($stops as $stop) {
                            $res .= "<li>$stop</li>";
                        }
                        $res .= "</ul>";
                    } else {
                        $res .= "<p>Gares desservies: Informations non disponibles</p>";
                    }
                    if ($section->type == 'transfer') {
                        $res .= "<p>Correspondance</p>";
                    }
                }
            }
            $res .= "</div>"; // Close journey-details
            $res .= "</div>"; // Close itinerary
            $journeyNumber++;
        }
    }
    return $res;

}



$gareDepart = $gareArrivee = $horaire = $date = "";
$result = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $gareDepart = $_POST["gareDepart"];
    $gareArrivee = $_POST["gareArrivee"];
    $horaire = $_POST["horaire"];
    $date = $_POST["date"];


    $result = makeNavitiaApiRequest($gareDepart, $gareArrivee, $date, $horaire);
}


if (!empty($result)) {
    $last_journey = [
        'from' => $gareDepart,
        'to' => $gareArrivee,
        'date' => $date,
        'time' => $horaire
    ];
    setcookie('last_journey', json_encode($last_journey), time() + (86400 * 10), "/");
}


?>
<script>
    function toggleStops(button) {
        var stopsList = button.nextElementSibling; // Récupère l'élément suivant le bouton, qui est la liste des arrêts
        if (stopsList.style.display === 'none') {
            stopsList.style.display = 'block'; // Affiche la liste si elle est cachée
            button.textContent = "Cacher les détails de l'itinéraire"; // Change le texte du bouton
        } else {
            stopsList.style.display = 'none'; // Cache la liste si elle est affichée
            button.textContent = "Voir les détails de l'itinéraire"; // Remet le texte original du bouton
        }
    }

</script>
<h1>Planifiez votre trajet</h1>
<div class="mc">
    <div class="sidebar">
        <?php
        $dossier = 'photos';
        $photos = [];
        if (is_dir($dossier)) {
            if ($dh = opendir($dossier)) {
                while (($fichier = readdir($dh)) !== false) {
                    if ($fichier != '.' && $fichier != '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $fichier)) {
                        $photos[] = $fichier;
                    }
                }
                closedir($dh);
            }
        }
        if (!empty($photos)) {
            $imageChoisie = $photos[array_rand($photos)];
            echo "<figure><img src='$dossier/$imageChoisie' alt='Image aléatoire' /><figcaption>$imageChoisie</figcaption></figure>";
        } else {
            echo "Aucune image trouvée dans le dossier.";
        }
        ?>
    </div>
    <div class="content1">

        <section>
            <h2>Veuillez saisir votre itinéraire</h2>
            <article>
                <div class="blocp">
                    <h3 style="color: #000000">Recherche d'un iténiraire</h3>
                    <form action="./departs.php" method="POST">
                        <div>
                            <label for="gareDepart">Gare de départ :</label>
                            <input type="text" id="gareDepart" name="gareDepart"
                                value="<?= htmlspecialchars($gareDepart) ?>" />
                        </div>
                        <div>
                            <label for="gareArrivee">Gare d'arrivée :</label>
                            <input type="text" id="gareArrivee" name="gareArrivee"
                                value="<?= htmlspecialchars($gareArrivee) ?>" />
                        </div>
                        <div>
                            <label for="date">Date de depart:</label>
                            <input type="date" id="date" name="date" value="<?= htmlspecialchars($date) ?>" />
                        </div>
                        <div>
                            <label for="horaire">Heure de départ:</label>
                            <input type="time" id="horaire" name="horaire" value="<?= htmlspecialchars($horaire) ?>" />
                        </div>
                        <div>
                            <button type="submit">Rechercher</button>
                        </div>
                    </form>
                </div>


            </article>
        </section>
        <section class="last-journey">
            <div>
                <h2>Dernier voyage</h2>
                <?php
                if (isset($_COOKIE['last_journey'])) {
                    $last_journey = json_decode($_COOKIE['last_journey'], true);
                    echo "<p>Départ : " . htmlspecialchars($last_journey['from']) . "</p>";
                    echo "<p>Arrivée : " . htmlspecialchars($last_journey['to']) . "</p>";
                    echo "<p>Date : " . htmlspecialchars($last_journey['date']) . "</p>";
                    echo "<p>Heure : " . htmlspecialchars($last_journey['time']) . "</p>";
                } else {
                    echo "<p>Aucun voyage précédent.</p>";
                }
                ?>
            </div>
        </section>
    
        <div class="uiverse">
            <a href="lignes.php" class="tooltip-button">
                <span class="tooltip">Horaires Gares</span>
                <span>Informations</span>
            </a>
        </div>

    </div>
</div>
<?php
if (!empty($result)) {
    echo "<div>";
    echo "<h2 class='heading1a'>Itinéraires disponibles :</h2>";
    echo displayResponse($result);
    echo "</div>";
}
?>






<?php
require "./includebis/footer.inc.php";
?>