<?php
$title = "Accueil";
require "./includebis/header.inc.php";
?>
<script>
    function toggleSection(sectionId, button) {
        var section = document.getElementById(sectionId);
        var displayStyle = section.style.display;

        if (displayStyle === 'none' || displayStyle === '') {
            section.style.display = 'block';
            button.textContent = 'Fermez';
        } else {
            section.style.display = 'none';
            button.textContent = 'Ouvrez';
        }
    }
</script>

<div class="plan-site-container">
    <h1 class="t1">PLAN DU SITE</h1>
    <div class="plan-section" id="section1">
        <h2>Horaires & itinéraires</h2>
        <button class="toggle-button" onclick="toggleSection('sous-liste1', this)">Ouvrez</button>
        <ul id="sous-liste1" class="sous-liste">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="departs.php">Departs Trains</a></li>
            <li><a href="infos.php">Infos Gare</a></li>
            <li><a href="statistiques.php">Statistiques Gare</a></li>

            <!-- Autres éléments -->
        </ul>
    </div>
    <div class="plan-section" id="section2">
        <h2>Utiles</h2>
        <button class="toggle-button" onclick="toggleSection('sous-liste2', this)">Ouvrez</button>
        <ul id="sous-liste2" class="sous-liste">
            <li><a href="apropos.php">A propos</a></li>
            <li><a href="tech.php">Page Tech</a></li>
            <!-- Autres éléments -->
        </ul>
    </div>
    <!-- Répéter pour chaque section avec son contenu respectif -->
</div>

<?php require "./includebis/footer.inc.php"; ?>