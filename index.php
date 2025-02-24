<?php
$title = "Accueil";
require "./includebis/header.inc.php";
?>
<div class="content">
    <div class="theme-background">
        <div class="main-content">
            <div class="scroller">
                <span>
                    Trajets Quotidiens<br />
                    &nbsp;&nbsp; Voyages & Voies<br />
                    &nbsp;&nbsp;&nbsp;Assistance 24/7<br />
                    &nbsp;&nbsp;&nbsp;&nbsp;Destinations<br />
                    Trajets Quotidiens<br />
                </span>
            </div>
        </div>
    </div>
</div>
<h1 class="heading1">Découvrz nos fonctionnalités</h1>
<div class="card-container">

    <div class="card" style="background-image:url('images/trip.jpg');">
        <h3>Planifiez un Trajet</h3>
        <a href="departs.php" class="card-button">Explorer</a>
    </div>
    <div class="card" style="background-image:url('images/voyage.jpg');">
        <h3>Infos de Gares</h3>
        <a href="infos.php" class="card-button">Plus d'Infos</a>
    </div>
    <div class="card" style="background-image:url('images/lignes.jpg');">
        <h3>Infos sur les Lignes</h3>
        <a href="lignes.php" class="card-button">Détails</a>
    </div>
</div>


<?php require "./includebis/footer.inc.php"; ?>