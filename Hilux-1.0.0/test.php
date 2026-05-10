<?php
echo "<h1>Test Page - Apache is working!</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<hr>";
echo "<h2>Links to test:</h2>";
echo "<ul>";
echo "<li><a href='View/entretien/list.php'>List Entretiens</a></li>";
echo "<li><a href='View/entretien/add.php'>Add Entretien</a></li>";
echo "</ul>";
