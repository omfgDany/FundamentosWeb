<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ai_chat'])) {
    $_SESSION['ai_chat'] = [];
}

function responder_ia_basica($mensaje) {
    $texto = function_exists('mb_strtolower')
        ? mb_strtolower($mensaje, 'UTF-8')
        : strtolower($mensaje);

    if (strpos($texto, 'pollo') !== false) {
        return 'Con pollo puedes preparar tacos, ensalada fresca o pasta cremosa. Si tienes limón, ajo y especias, ya tienes una base muy buena.';
    }

    if (strpos($texto, 'postre') !== false || strpos($texto, 'dulce') !== false) {
        return 'Para algo dulce y sencillo, prueba fruta con yogurt y granola, arroz con leche o hot cakes con miel.';
    }

    if (strpos($texto, 'saludable') !== false) {
        return 'Una opción saludable puede combinar proteína, verduras y un carbohidrato ligero: pollo o huevo, ensalada y arroz o tortilla.';
    }

    if (strpos($texto, 'ingrediente') !== false || strpos($texto, 'tengo') !== false) {
        return 'Dime qué ingredientes tienes y te propongo una receta simple con pasos cortos.';
    }

    return 'Buena idea. Para empezar simple, piensa en una base, una proteína, verduras y una salsa. Puedo ayudarte a convertir eso en una receta paso a paso.';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'messages' => $_SESSION['ai_chat']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($body['message'] ?? '');

if ($mensaje === '') {
    echo json_encode(['success' => false, 'error' => 'Escribe una pregunta para el chat.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['ai_chat'][] = [
    'autor' => 'usuario',
    'texto' => $mensaje
];

$_SESSION['ai_chat'][] = [
    'autor' => 'bot',
    'texto' => responder_ia_basica($mensaje)
];

echo json_encode([
    'success' => true,
    'messages' => $_SESSION['ai_chat']
], JSON_UNESCAPED_UNICODE);
