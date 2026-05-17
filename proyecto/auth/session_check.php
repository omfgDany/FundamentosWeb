<?php
// auth/session_check.php
// Devuelve el usuario de la sesión actual como JSON
session_start();
header('Content-Type: application/json');

$defaultPfp = 'media/pfp/1.png';

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'user' => [
            'id'          => $_SESSION['user_id'],
            'nombre'      => $_SESSION['user_nombre'],
            'email'       => $_SESSION['user_email'],
            'rol'         => $_SESSION['user_rol'],
            'foto_perfil' => $_SESSION['user_foto_perfil'] ?? $defaultPfp
        ]
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
