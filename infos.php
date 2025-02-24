<?php
$title = "Infos Gares";
require_once "includebis/fonctions.inc.php";
require "includebis/header.inc.php";


$nomGareDefaut = "Pontoise";
$codeINSEEDefaut = "95500"; // Code INSEE 
$latDefaut = 49.0487;
$lonDefaut = 2.1014;

?>
<h1>Infos Gares, Horaires et Statistiques</h1>
<div class="search-section">
    <form method="GET" class="search-form">
        <label for="search">Rechercher :</label>
        <input type="text" id="search" name="search" placeholder="Entrez le nom de la gare" />
        <input type="submit" value="Rechercher" />
    </form>


    <?php


    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $_GET['search'];
        $searchTerm = urlencode($search);
        $url = "https://ressources.data.sncf.com/api/explore/v2.1/catalog/datasets/gares-de-voyageurs/records?limit=100&where=codeinsee%20like%20%22$searchTerm%22%20OR%20nom%20like%20%22$searchTerm%22";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['results']) && count($data['results']) > 0) {
            enregistrerConsultationGare($search);
            echo "<div class='result-section-container'>";
            echo "<h1 class='heading1a'>Résultats de la recherche :</h1>";
            foreach ($data['results'] as $result) {
                $nomGare = $result['nom'];
                $codeINSEE = $result['codeinsee'];
                $lon = $result['position_geographique']['lon'];
                $lat = $result['position_geographique']['lat'];

                echo "<h2 class='heading2'>Gare : $nomGare</h2>";
                echo "<p>Code INSEE : $codeINSEE</p>";
                echo "<p>Position géographique : Latitude $lat , Longitude $lon</p>";
                carteGare($lat, $lon);
                echo "<h2 class='heading2'>Horaires de la gare :</h2>";
                horairesGare($nomGare);
            }
            echo "</div>"; // Fermeture de result-section-container
        } else {
            echo "<p>Aucune donnée trouvée pour votre recherche.</p>";
        }

    } else {
        // Affichage des infos de la gare par défaut si aucune recherche n'est effectuée
        echo "<div class='result-section-container'>";
        echo "<h2 class='heading2'>Gare : $nomGareDefaut</h2>";
        echo "<p>Code INSEE : $codeINSEEDefaut</p>";
        echo "<p>Position géographique : Latitude $latDefaut , Longitude $lonDefaut</p>";
        carteGare($latDefaut, $lonDefaut, 600, 400);
        echo "<h3>Horaires de la gare :</h3>";
        horairesGare($nomGareDefaut);
        echo "</div>";
    }
    function enregistrerConsultationGare($nomGare)
    {
        $file_path = 'consultations_gares.csv';
        $gares = [];

        // Lire les données existantes
        if (file_exists($file_path)) {
            $file = fopen($file_path, 'r');
            while (($data = fgetcsv($file)) !== false) {
                if (isset($data[0]) && isset($data[1])) {
                    $gares[$data[0]] = (int) $data[1];
                }
            }
            fclose($file);
        }

        // Mettre à jour ou ajouter la gare
        if (array_key_exists($nomGare, $gares)) {
            $gares[$nomGare] += 1;
        } else {
            $gares[$nomGare] = 1;
        }

        // Réécrire le fichier avec les données mises à jour
        $file = fopen($file_path, 'w'); // Ouvrir en mode écriture, ce qui efface le contenu existant
        foreach ($gares as $gare => $count) {
            fputcsv($file, [$gare, $count]);
        }
        fclose($file);
    }






    ?>
</div>
<div class="stat">
    <h2>Histogramme résumant l'ensemble des recherches de gares </h2>
    <div class="histogram-container">
        <?php
        // Lire les données à partir du fichier CSV
        $data = readFromCSV('consultations_gares.csv');

        // Générer l'histogramme
        $histogram = generateHistogram($data);

        // Déterminer la hauteur maximale de la barre pour une normalisation visuelle
        $maxCount = !empty($histogram) ? max($histogram) : 0; // Vérifier si le tableau n'est pas vide
        
        // Définir les couleurs pour les stations
        $colors = array(
            '#fde951',
            '#71a2f0',
            '#ff9e9e',
            '#a3f071',
            '#f071a3',
            '#87CEEB',
            '#FF6347',
            '#32CD32',
            '#FFD700',
            '#9932CC',
            '#FF4500',
            '#ADFF2F',
            '#00BFFF',
            '#FF1493',
            '#00CED1',
            '#FF69B4',
            '#8A2BE2',
            '#00FF7F',
            '#1E90FF',
            '#FFA07A',
            '#4682B4',
            '#FFD700',
            '#008080',
            '#DAA520',
            '#800080',
            '#B0C4DE',
            '#7FFF00',
            '#FA8072',
            '#FFE4B5',
            '#008B8B'
        );
        // Afficher les barres d'histogramme avec les couleurs correspondantes
        $colorIndex = 0;
        foreach ($histogram as $station => $count) {
            // Calculer la hauteur de la barre en pourcentage par rapport au maximum
            $heightPercentage = ($count / $maxCount) * 100;
            echo "<div class='bar' style='height: $heightPercentage%; background-color: {$colors[$colorIndex]};'></div>";
            $colorIndex = ($colorIndex + 1) % count($colors); // Pour que l'index de couleur revienne à zéro lorsque toutes les couleurs sont utilisées
        }
        ?>
    </div>

    <!-- Noms des stations avec couleur correspondante -->
    <div class="stations">
        <?php
        if (!empty($histogram)) { // Vérifier si le tableau n'est pas vide
            $colorIndex = 0;
            foreach ($histogram as $station => $count) {
                echo "<div class='station' style='color: {$colors[$colorIndex]};'>";
                echo "<span class='color-box' style='background-color: {$colors[$colorIndex]};'></span> {$station} x{$count}";
                echo "</div>";
                $colorIndex = ($colorIndex + 1) % count($colors); // Pour que l'index de couleur revienne à zéro lorsque toutes les couleurs sont utilisées
            }
        } else {
            echo "<div class='no-data-message'>";
            echo "<span style='font-size: 24px;'>Aucune gare recherchée pour le moment.</span>";
            echo "</div>";
        }


        /**
         * Génère un histogramme à partir des données fournies.
         *
         * @param array $data Les données à partir desquelles générer l'histogramme.
         * @return array L'histogramme généré, sous forme de tableau associatif avec les clés représentant les valeurs et les valeurs représentant les fréquences.
         */
        function generateHistogram($data)
        {
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
        /**
         * Lit les données à partir d'un fichier CSV et les stocke dans un tableau.
         *
         * @param string $file Le chemin vers le fichier CSV à lire.
         * @return array Les données lues depuis le fichier CSV, sous forme de tableau.
         */
        function readFromCSV($file)
        {
            $data = [];
            $handle = fopen($file, 'r');

            if ($handle !== false) {
                while (($row = fgetcsv($handle)) !== false) {
                    $data[] = $row;
                }
                fclose($handle);
            } else {
                echo "Impossible d'ouvrir le fichier CSV pour la lecture.";
            }

            return $data;
        }

        ?>

    </div>
</div>
<?php require "includebis/footer.inc.php"; ?>