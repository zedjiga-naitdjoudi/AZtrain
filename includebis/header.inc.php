<?php
declare(strict_types=1);
include_once "fonctions.inc.php";

$cookieName = 'theme';


$cookiePath = '/';

// Durée de vie du cookie 
$cookieLifetime = 30 * 24 * 60 * 60;

// Vérification et mise à jour du cookie si nécessaire
if (isset($_GET['style']) && in_array($_GET['style'], ['style', 'alternatif'])) {
    $themeValue = $_GET['style'] == 'style' ? 'day' : 'night';
    setcookie($cookieName, $themeValue, time() + $cookieLifetime, $cookiePath);
    $currentTheme = $themeValue;
} elseif (isset($_COOKIE[$cookieName])) {

    $currentTheme = in_array($_COOKIE[$cookieName], ['day', 'night']) ? $_COOKIE[$cookieName] : 'day';
} else {

    $currentTheme = 'day'; // Thème par défaut
}
$style = $currentTheme == 'day' ? 'style.css' : 'stylenuit.css';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="images/icon.png" />
    <title>
        <?php echo $title ?>
    </title>
    <link rel="stylesheet" href="<?php echo $style; ?>" />
</head>

<body>
    <header>

        <a href="index.php">
            <img src="images/icon.png" alt="Logo AZtrains" class="logo" />
        </a>
        <h1 class="h11"><span class="c1">A</span><span class="c2">Z</span>trains</h1>

        <div class="theme-switcher">
            <a href="?style=style"><img src="./images/day.png" alt="Mode Clair" /></a>
            <a href="?style=alternatif"><img src="./images/night.png" alt="Mode Sombre" /></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Bienvenue</a></li>
                <li><a href="departs.php">Prochains Trains</a></li>
                <li><a href="infos.php">Détails des Gare</a></li>
                <li><a href="lignes.php">Informations</a></li>
                <li><a href="apropos.php">Notre Équipe</a></li>
            </ul>
        </nav>
    </header>
    <main>