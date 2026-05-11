<?php
/**
 * Fichier de test pour les inscriptions
 * Accédez à : http://localhost/gestion_formation_inscription/gestion_formation/test_inscription.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Model/Inscription.php';
require_once __DIR__ . '/Controller/Inscriptioncontroller .php';

$ic = new InscriptionController();

echo "<h1>Test du système d'Inscription</h1>";
echo "<hr>";

// Test 1 : Ajouter une inscription
echo "<h2>1. Ajouter une inscription</h2>";
$inscription1 = new Inscription(
    user_id: 1,
    formation_id: 1,
    nom: "Dupont",
    prenom: "Jean",
    email: "jean.dupont@example.com",
    niveau: "Débutant",
    mode_formation: "En ligne"
);
$ic->addInscription($inscription1);
echo "✅ Inscription ajoutée avec succès !<br>";

// Test 2 : Ajouter une deuxième inscription
echo "<h2>2. Ajouter une deuxième inscription</h2>";
$inscription2 = new Inscription(
    user_id: 2,
    formation_id: 2,
    nom: "Martin",
    prenom: "Marie",
    email: "marie.martin@example.com",
    niveau: "Intermédiaire",
    mode_formation: "Présentiel"
);
$ic->addInscription($inscription2);
echo "✅ Deuxième inscription ajoutée !<br>";

// Test 3 : Lister toutes les inscriptions
echo "<h2>3. Liste de toutes les inscriptions</h2>";
$inscriptions = $ic->listInscriptions()->fetchAll();
echo "<table border='1' cellpadding='10' cellspacing='0'>";
echo "<tr style='background-color:#3bafda;color:white;'>";
echo "<th>ID</th><th>User ID</th><th>Formation ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Niveau</th><th>Mode</th>";
echo "</tr>";
foreach ($inscriptions as $insc) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($insc['id']) . "</td>";
    echo "<td>" . htmlspecialchars($insc['user_id']) . "</td>";
    echo "<td>" . htmlspecialchars($insc['formation_id']) . "</td>";
    echo "<td>" . htmlspecialchars($insc['nom']) . "</td>";
    echo "<td>" . htmlspecialchars($insc['prenom']) . "</td>";
    echo "<td>" . htmlspecialchars($insc['email']) . "</td>";
    echo "<td>" . htmlspecialchars($insc['niveau'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($insc['mode_formation'] ?? '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test 4 : Récupérer une inscription spécifique
echo "<h2>4. Récupérer une inscription (ID = 1)</h2>";
$insc = $ic->getInscription(1);
if ($insc) {
    echo "Nom : " . htmlspecialchars($insc['nom']) . "<br>";
    echo "Prénom : " . htmlspecialchars($insc['prenom']) . "<br>";
    echo "Email : " . htmlspecialchars($insc['email']) . "<br>";
    echo "Niveau : " . htmlspecialchars($insc['niveau'] ?? '-') . "<br>";
    echo "Mode : " . htmlspecialchars($insc['mode_formation'] ?? '-') . "<br>";
} else {
    echo "❌ Inscription non trouvée";
}

// Test 5 : Modifier une inscription
echo "<h2>5. Modifier l'inscription (ID = 1)</h2>";
$inscriptionModifiee = new Inscription(
    user_id: 1,
    formation_id: 1,
    nom: "Dupont",
    prenom: "Jean-Pierre",
    email: "jean.pierre@example.com",
    niveau: "Avancé",
    mode_formation: "Hybride"
);
$ic->updateInscription(1, $inscriptionModifiee);
echo "✅ Inscription modifiée !<br>";

// Vérifier la modification
$insc = $ic->getInscription(1);
echo "Nouveau prénom : " . htmlspecialchars($insc['prenom']) . "<br>";
echo "Nouveau niveau : " . htmlspecialchars($insc['niveau']) . "<br>";

// Test 6 : Supprimer une inscription
echo "<h2>6. Supprimer l'inscription (ID = 2)</h2>";
$ic->deleteInscription(2);
echo "✅ Inscription supprimée !<br>";

// Vérifier la suppression
$inscriptions = $ic->listInscriptions()->fetchAll();
echo "Nombre d'inscriptions restantes : " . count($inscriptions) . "<br>";

echo "<hr>";
echo "<h2>✅ Tous les tests sont terminés !</h2>";
echo "<p><a href='test_inscription.php'>Rafraîchir</a> | <a href='View/backoffice-formation/listInscriptions.php'>Voir le backoffice</a></p>";
?>
