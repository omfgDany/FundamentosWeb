<?php
// auth/register.php
session_start();
header('Content-Type: application/json');
require_once('../config/sql.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true);
$nombre   = trim($body['nombre']   ?? '');
$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');

// Validaciones
if (!$nombre || !$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Correo no válido.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

// Verificar si el email ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'Este correo ya está registrado.']);
    exit;
}
$stmt->close();

// Hashear contraseña e insertar
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')");
$stmt->bind_param("sss", $nombre, $email, $hash);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Error al crear la cuenta.']);
    exit;
}

$nuevo_id = $conn->insert_id;
$stmt->close();

// Iniciar sesión automáticamente tras registrarse
$_SESSION['user_id']     = $nuevo_id;
$_SESSION['user_nombre'] = $nombre;
$_SESSION['user_email']  = $email;
$_SESSION['user_rol']    = 'usuario';
$_SESSION['user_foto_perfil'] = 'media/pfp/1.png';

$conn->close();

echo json_encode([
    'success' => true,
    'user' => [
        'id'     => $nuevo_id,
        'nombre' => $nombre,
        'email'  => $email,
        'rol'    => 'usuario',
        'foto_perfil' => $_SESSION['user_foto_perfil']
    ]
]);
