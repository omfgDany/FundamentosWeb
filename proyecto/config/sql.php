<?php
$host     = "localhost";
$usuario  = "root";
$password = "";
$base     = "foodsoulsql";

// Crear conexión
$conn = mysqli_connect($host, $usuario, $password, $base);

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

echo "Conexión exitosa";
?>