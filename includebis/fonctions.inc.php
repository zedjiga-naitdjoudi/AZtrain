<?php 
/**
 * Retourne le nom du navigateur de l'internaute.
 *
 * @return string Le nom du navigateur.
 */
function get_navigateur() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
        return 'Internet Explorer';
    } elseif (strpos($userAgent, 'Edge') !== false) {
        return 'Microsoft Edge';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        return 'Mozilla Firefox';
    } elseif (strpos($userAgent, 'Chrome') !== false) {
        return 'Google Chrome';
    } elseif (strpos($userAgent, 'Safari') !== false) {
        return 'Safari';
    } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
        return 'Opera';
    } else {
        return 'Navigateur inconnu';
    }
}



/**
 * Récupère l'Astronomy Picture of the Day (APOD) depuis l'API de la NASA.
 *
 * @param string $api_key La clé API de la NASA.
 * @param string|null $date La date pour laquelle récupérer l'APOD, au format YYYY-MM-DD. Utilise la date actuelle si non spécifiée.
 * @return void Affiche le contenu APOD, y compris le titre, l'image/vidéo et la description.
 */
function getNasaApod($api_key, $date = null) {
    // Utilise la date actuelle si aucune date n'est fournie.
    if (is_null($date)) {
        $date = date("Y-m-d");
    }

    // Construction de l'URL de requête à l'API APOD de la NASA.
    $url = "https://api.nasa.gov/planetary/apod?api_key={$api_key}&date={$date}";

    // Requête GET à l'URL spécifiée.
    $reponse = file_get_contents($url);

    // Vérifie si la requête a réussi.
    if ($reponse) {
        $donnee = json_decode($reponse, true); // Décodage de la réponse JSON.

        // Affichage du titre, si présent.
        if (isset($donnee['title'])) {
            echo "<h2>{$donnee['title']}</h2>";
        }

        // Affichage de l'image ou de la vidéo, en fonction du type de média.
        if (isset($donnee['media_type']) && $donnee['media_type'] == 'image') {
            echo "<img src='{$donnee['url']}' alt='APOD Image' style='width:100%;max-width:600px;'/>";
        } elseif (isset($donnee['media_type']) && $donnee['media_type'] == 'video') {
            echo "<video controls style='width:100%;max-width:600px;'><source src='{$donnee['url']}' type='video/mp4'>Votre navigateur ne supporte pas la vidéo.</video>";
        }

        // Affichage de la description, si présente.
        if (isset($donnee['explanation'])) {
            echo "<p>{$donnee['explanation']}</p>";
        }

        // Affichage des droits d'auteur, si présents.
        if (isset($donnee['copyright'])) {
            echo "<p>Copyright: {$donnee['copyright']}</p>";
        }

    } else {
        // Message d'erreur si la requête échoue.
        echo "Impossible de récupérer l'image du jour.";
    }
}


/**
 * Récupère et affiche les informations de géolocalisation pour une adresse IP donnée.
 *
 * @param string $ip L'adresse IP pour laquelle récupérer les informations de géolocalisation.
 * @return void Affiche la ville et le pays associés à l'adresse IP. Si les informations ne peuvent pas être récupérées, affiche un message d'erreur.
 */
function getGeoLocationInfo($ip) {
    // Construit l'URL pour la requête à l'API GeoPlugin.
    $url = "http://www.geoplugin.net/xml.gp?ip={$ip}";

    // Tente de charger le fichier XML retourné par l'API.
    $xml = simplexml_load_file($url);

    // Vérifie si le chargement du XML a réussi et si oui, extrait les informations.
    if ($xml) {
        $city = $xml->geoplugin_city;
        $country = $xml->geoplugin_countryName;
        echo "Votre localisation approximative est : $city, $country";
    } else {
        echo "Impossible de récupérer les informations de localisation.";
    }
}


/**
 * Récupère les informations de géolocalisation pour une adresse IP spécifiée en utilisant l'API ipinfo.io.
 *
 * @param string $ip L'adresse IP pour laquelle récupérer les informations de géolocalisation.
 * @return void Affiche la ville et le pays associés à l'adresse IP. Si les informations ne peuvent pas être récupérées, affiche un message d'erreur.
 */
function getIPGeoLocation($ip) {
    // Construit l'URL pour la requête à l'API ipinfo.io.
    $url = "https://ipinfo.io/{$ip}/geo";

    // Tente de récupérer la réponse depuis l'URL.
    $reponse = file_get_contents($url);

    // Vérifie si la requête a réussi et si oui, traite la réponse.
    if ($reponse) {
        // Convertit la réponse JSON en tableau associatif.
        $data = json_decode($reponse, true);

        // Construit et affiche la localisation à partir des données récupérées.
        $location = isset($data['city']) ? $data['city'] : "Non spécifiée";
        $location .= isset($data['country']) ? ", " . $data['country'] : "";
        echo "Votre localisation approximative est : $location";
    } else {
        echo "Impossible de récupérer les informations de localisation.";
    }
}
/**
 * Affiche les informations de départ des trains retournées par l'API sous forme de tableau HTML.
 *
 * Cette fonction parcourt le tableau associatif fourni, contenant les informations sur les départs des trains,
 * et génère une ligne de tableau HTML pour chaque départ. Chaque ligne inclut le nom de la gare de destination,
 * l'heure de départ, ainsi que le type et le nom de la ligne de train.
 *
 * @param array $data Le tableau associatif retourné par l'API, contenant les informations sur les départs des trains.
 *                    Ce tableau doit contenir une clé "departures" qui mène à un tableau de départs,
 *                    chaque départ étant lui-même un tableau contenant les informations nécessaires.
 */

function departsTrains(array $data) : void
{
  foreach($data["departures"] as $v) {

    // nom de la gare
    $nomGare = $v["display_informations"]["direction"];

    //heure de départ + conversion (YYYYmmdd T hhmmss --> h + m)
    $departureTime = $v["stop_date_time"]["departure_date_time"];
    $heure = substr($departureTime, 9, 2);
    $minute = substr($departureTime, 11, 2);

    //type de train + ligne
    $trainType = $v["route"]["line"]["commercial_mode"]["name"];
    $line = $v["route"]["line"]["name"];

    echo "<tr><td>$nomGare</td><td>$heure h $minute</td><td>$trainType $line</td></tr>";  
  }
}



/**
 * Affiche les horaires d'une gare spécifique en se basant sur son nom.
 * 
 * Cette fonction interroge une API pour récupérer les horaires des gares et les affiche dans un tableau HTML.
 * Le nom de la gare est utilisé pour construire la requête à l'API. Les résultats, incluant les jours de la semaine
 * et les horaires correspondants, sont ensuite présentés dans un tableau.
 * Si aucune information d'horaire n'est trouvée pour la gare spécifiée, un message indiquant l'absence d'horaires est affiché.
 * 
 * @param string $nomGare Le nom de la gare pour laquelle afficher les horaires. Ce nom est utilisé dans la requête à l'API.
 */
function horairesGare($nomGare) {
    $base_path = "https://ressources.data.sncf.com/api/explore/v2.1";
    $horaires_dataset_path = "/catalog/datasets/horaires-des-gares1/records";
    $nomGare = urlencode($nomGare);
    $url_horaires = $base_path . $horaires_dataset_path . "?where=nom_normal%20like%20%22$nomGare%22";

    $response_horaires = file_get_contents($url_horaires);
    $data_horaires = json_decode($response_horaires, true);

    if (isset($data_horaires['results']) && count($data_horaires['results']) > 0) {
        echo "<div class='table-container'>"; // Conteneur pour le style
        echo "<table><thead><tr><th>Jour</th><th>Horaires</th></tr></thead><tbody>";
        foreach ($data_horaires['results'] as $horaire) {
            $jour = $horaire['jour'] ?? "Non spécifié";
            $horaireNormal = $horaire['horaire_normal'] ?? "Non disponible";
            
            echo "<tr><td>$jour</td><td>$horaireNormal</td></tr>";
        }
        echo "</tbody></table>";
        echo "</div>"; // Fermeture du conteneur de tableau
    } 
}

/**
 * Affiche une carte OpenStreetMap d'une position géographique spécifiée avec un marqueur.
 * 
 * Cette fonction génère un iframe contenant une carte de OpenStreetMap centrée sur les coordonnées fournies.
 * Un marqueur est placé à la position exacte spécifiée par les paramètres de latitude et de longitude.
 * Les dimensions de l'iframe peuvent être ajustées via les paramètres de largeur et de hauteur.
 *
 * @param float $latitude La latitude de la position à marquer sur la carte.
 * @param float $longitude La longitude de la position à marquer sur la carte.
 * @param int $largeur La largeur de l'iframe de la carte en pixels. Par défaut à 600px.
 * @param int $hauteur La hauteur de l'iframe de la carte en pixels. Par défaut à 400px.
 */
function carteGare($latitude, $longitude, $largeur = 600, $hauteur = 400) {
    $url = "https://www.openstreetmap.org/export/embed.html?bbox=" . ($longitude-0.01) . "," . ($latitude-0.01) . "," . ($longitude+0.01) . "," . ($latitude+0.01) . "&amp;layer=mapnik";
    $url .= "&amp;marker=$latitude,$longitude";
    echo "<iframe class=\"iframe-style\" style=\"width:{$largeur}px; height:{$hauteur}px;\" src=\"$url\"></iframe>";

}



/* function updateStats(string $nomGare) : void
{
    // on ouvre les fichiers : le fichier original en lecture, et un fichier temporaire en écriture
    $oldCSVFile = fopen('statsRecherche.csv', 'r');
    $newCSVFile = fopen('tempStats.csv', 'w');
    $modified = false;
    // on parcourt tout le fichier en modifiant le nombre de visite si la gare est trouvée, puis on écrit la ligne dans le fichier temporaire
    while (($line = fgetcsv($oldCSVFile)) !== false)
    {
        if ($line[0] === $nomGare)
        {
            $line[1]++;
            $modified = true;
        }
        fputcsv($newCSVFile, $line);
    }
    // si on a rien modifié, on ajoute au fichier temporaire une nouvelle ligne avec le nom de la gare, et un compteur de recherche à 1
    if(!$modified)
    {
        $line = [$nomGare,1];
        fputcsv($newCSVFile,$line);
    }
    // on ferme les 2 fichiers, on supprime l'original, et le temporaire devient le nouvel "original"
    fclose($oldCSVFile);
    fclose($newCSVFile);
    unlink('statsRecherche.csv');
    rename('tempStats.csv','statsRecherche.csv');
}

/**
 * récupère les statistiques stockées dans le fichier CSV, et fait un classement des gares les plus recherchées à partir de celles-ci.
 *
 * @return string le tableau html du classement.
 */
/* function getStats() : string
{
    // on récupère le contenu du fichier csv, et on le met dans un tableau, et on trie ce tableau par ordre décroissant
    $CSVFile = fopen('statsRecherche.csv', 'r');
    $ranking = [];
    while(($line = fgetcsv($CSVFile)) !== false)
    {
        $ranking[$line[0]] = $line[1];
    }
    arsort($ranking);

    //ensuite on formatte un tableau en HTML affichant le classement
    $html = "<h2> nombre de recherches par gare</h2>
    <table>";
    $html .= 
    "<thead>
        <tr>
            <th>Classement</th>
            <th>Gare</th>
            <th>Nombre de recherches</th>
        </tr>
    </thead><tbody>";
    $i = 0;
    foreach($ranking as $nomGare => $nbRecherches)
    {
        //on ignore l'entête du fichier
        if($nomGare === "nom de gare")
        {
            continue;
        }
        $i++;
        $html .="<tr>
                    <td>$i</td>
                    <td>$nomGare</td>
                    <td>$nbRecherches</td>
                </tr>";
    }
    $html .="</tbody></table>";

    return $html;
}*/


  function spawnHistogram($data) {
      $histogram = [];
      foreach ($data as $row) {
          // Vérifier si la ligne contient au moins deux éléments
          if (count($row) >= 2) {
              $fromStation = $row[0];
              $count = intval($row[1]);
              if (isset($histogram[$fromStation])) {
                  $histogram[$fromStation] += $count;
              } else {
                  $histogram[$fromStation] = $count;
              }
          }
      }
    
      arsort($histogram);
    
      return $histogram;
    }
?>





