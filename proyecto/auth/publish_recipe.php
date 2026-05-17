<?php
// auth/publish_recipe.php
session_start();
header('Content-Type: application/json');
require_once('../config/sql.php'); // Asegura que la ruta a tu conexión SQL sea la correcta

// 1. Validar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no activa. Debes iniciar sesión.']);
    exit;
}

// 2. Solo aceptar peticiones por método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

// 3. Capturar campos del formulario
$usuario_id   = $_SESSION['user_id'];
$titulo       = trim($_POST['titulo'] ?? '');
$categoria    = trim($_POST['categoria'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$ingredientes = trim($_POST['ingredientes'] ?? '');
$preparacion  = trim($_POST['preparacion'] ?? '');
$imagen_ruta  = null; // Por defecto si decide no subir foto

// Validación básica de campos vacíos
if (!$titulo || !$categoria || !$descripcion || !$ingredientes || !$preparacion) {
    echo json_encode(['success' => false, 'error' => 'Todos los campos de texto son requeridos.']);
    exit;
}

// 4. Procesar subida de archivo (Si el usuario adjuntó una imagen)
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    
    // Definimos la carpeta destino (raíz/media/)
    $target_dir = "../media/recipes/"; // Asegúrate de que esta carpeta exista y tenga permisos de escritura
    
    // Crear el directorio de forma automática si no existe en el proyecto
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    // Validar formato de imagen admitido
    if (in_array($file_extension, $allowed_extensions)) {
        // Renombramos el archivo con un timestamp único para evitar sobreescritura de imágenes
        $new_filename = "receta_" . time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Mover el archivo temporal a su destino definitivo
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            // Guardamos la ruta relativa que se guardará en la BD para consumirla fácilmente desde el HTML
            $imagen_ruta = "media/" . $new_filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo mover la imagen al repositorio media/.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Formato de imagen inválido (Soportados: JPG, PNG, WEBP, GIF).']);
        exit;
    }
}

// 5. Inserción segura en la Base de Datos con Sentencias Preparadas (PreparedStatement)
try {
    // Ajusta los nombres de tus columnas e integrantes según la estructura exacta de tu tabla 'recetas'
    $sql = "INSERT INTO recetas (usuario_id, titulo, categoria, descripcion, ingredientes, preparacion, imagen, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $usuario_id, $titulo, $categoria, $descripcion, $ingredientes, $preparacion, $imagen_ruta);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se logró guardar la receta en la base de datos: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Fallo interno en el servidor SQL: ' . $e->getMessage()]);
}
$conn->close();