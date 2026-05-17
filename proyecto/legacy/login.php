<?php
// auth/login.php
session_start();
header('Content-Type: application/json');
require_once('../config/sql.php');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Leer JSON del body
$body = json_decode(file_get_contents('php://input'), true);
$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

// Validación básica
if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Campos requeridos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Correo no válido.']);
    exit;
}

// Buscar usuario por email
$stmt = $conn->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// Verificar que existe y la contraseña es correcta
if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Correo o contraseña incorrectos.']);
    exit;
}

// Crear sesión
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_nombre'] = $user['nombre'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_rol']   = $user['rol'];

$conn->close();

echo json_encode([
    'success' => true,
    'user' => [
        'id'     => $user['id'],
        'nombre' => $user['nombre'],
        'email'  => $user['email'],
        'rol'    => $user['rol']
    ]
]);
