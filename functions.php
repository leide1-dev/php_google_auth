<?php
// functions.php
function get_db_status($conn) {
    if ($conn && $conn->ping()) {
        return ['text' => 'Conectada', 'class' => 'ok'];
    } else {
        return ['text' => 'Desconectada', 'class' => 'error'];
    }
}

function get_server_info($conn) {
    $db_status = get_db_status($conn);
    $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? explode(' ', $_SERVER['SERVER_SOFTWARE'])[0] : 'N/A';
    return [
        'php_version' => phpversion(),
        'db_status_text' => $db_status['text'],
        'db_status_class' => $db_status['class'],
        'server_software' => $server_software,
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'N/A',
        'mysql_version' => $conn ? $conn->server_info : 'N/A'
    ];
}
?>