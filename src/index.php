<?php
echo "<h1>Morgan Edition - Docker Environment</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<hr>";
echo "<h2>PHP Extensions</h2>";
echo "<ul>";
$required = ['mysqli', 'gd', 'mbstring', 'xml', 'zip'];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? "OK" : "MISSING";
    echo "<li>{$ext}: {$status}</li>";
}
echo "</ul>";
echo "<hr>";
echo "<p>Ready for Gnuboard5 installation!</p>";
