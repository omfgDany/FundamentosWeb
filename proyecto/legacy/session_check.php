<?php
// auth/session_check.php
// Devuelve el usuario de la sesión actual como JSON
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'user' => [
            'id'     => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'],
            'email'  => $_SESSION['user_email'],
            'rol'    => $_SESSION['user_rol']
        ]
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
