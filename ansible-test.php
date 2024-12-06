<?php

$host = '127.0.0.1';
$port = 65432;

// Datos que enviamos al servicio (en formato JSON)
$data = [
    'playbook' => 'df.yml',
    'extra_vars' => ['some_var' => 'some_value']
];

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo json_encode(["error" => "Error al crear el socket", "details" => socket_strerror(socket_last_error())]);
    exit;
}

$result = socket_connect($socket, $host, $port);
if ($result === false) {
    echo json_encode(["error" => "No se pudo conectar al socket", "details" => socket_strerror(socket_last_error($socket))]);
    exit;
}

socket_write($socket, json_encode($data), strlen(json_encode($data)));

$response = '';
$openBraces = 0; // Contador para llaves abiertas
$jsonComplete = false;

while (!$jsonComplete) {
    $chunk = socket_read($socket, 1024); // Leer fragmentos de 1024 bytes
    if ($chunk === false) {
        echo json_encode(["error" => "Error al leer del socket", "details" => socket_strerror(socket_last_error($socket))]);
        exit;
    }
    if ($chunk === '') {
        // No hay más datos, pero el JSON aún no está completo
        break;
    }

    $response .= $chunk;

    // Verificar balanceo de llaves
    foreach (str_split($chunk) as $char) {
        if ($char === '{' || $char === '[') {
            $openBraces++;
        } elseif ($char === '}' || $char === ']') {
            $openBraces--;
        }
    }

    // JSON completo si las llaves están balanceadas
    if ($openBraces === 0 && trim($response) !== '') {
        $jsonComplete = true;
    }
}

socket_close($socket);

$responseArray = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // Imprimir la respuesta original y terminar
    echo "Error al decodificar JSON: " . json_last_error_msg() . "\n";
    echo "Respuesta original: " . $response;
    exit;
}

if (isset($responseArray['status']) && $responseArray['status'] === 'success' && isset($responseArray['result'])) {
    echo json_encode($responseArray['result'], JSON_PRETTY_PRINT);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error al decodificar el campo 'result': " . json_last_error_msg() . "\n";
        echo "Contenido de 'result': " . $responseArray['result'];
        exit;
    }
} else {
    echo "Status no es success o el campo 'status' no existe.\n";
    var_dump($responseArray);
}
