<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ai_chat'])) {
    $_SESSION['ai_chat'] = [];
}

function responder_con_groq($mensaje) {
    $apiKey = getenv('GROQ_API_KEY');

    if (!$apiKey) {
        return null;
    }

    $data = [
        'model' => 'llama-3.1-8b-instant',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Eres un asistente de cocina para RecetaCreativa. Responde en español, breve, claro y con ideas prácticas.'
            ],
            [
                'role' => 'user',
                'content' => $mensaje
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 180
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        curl_close($ch);
        return 'No pude conectarme con la IA en este momento. Revisa que cURL esté activo en XAMPP.';
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);

    if ($statusCode >= 400) {
        return $json['error']['message'] ?? 'La API de IA respondió con un error.';
    }

    return $json['choices'][0]['message']['content'] ?? 'La IA no devolvió una respuesta válida.';
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
    'texto' => responder_con_groq($mensaje) ?? responder_ia_basica($mensaje)
];

echo json_encode([
    'success' => true,
    'messages' => $_SESSION['ai_chat']
], JSON_UNESCAPED_UNICODE);
