<?php

// ---- PON AQUÍ LA CONTRASEÑA QUE QUIERAS USAR ---
$mi_password_nueva = "adminpass"; 

// Esto crea el hash seguro
$hash_nuevo = password_hash($mi_password_nueva, PASSWORD_DEFAULT);

// Esto lo imprime en la pantalla
echo "¡Tu hash nuevo está listo! <br><br>";
echo "Contraseña: <b>" . htmlspecialchars($mi_password_nueva) . "</b><br>";
echo "Pega este Hash en la Base de Datos: <br>";
echo "<pre>" . htmlspecialchars($hash_nuevo) . "</pre>";

?>