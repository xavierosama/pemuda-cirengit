<?php
// Catch any exception that occurs BEFORE Laravel's own exception handler
// to reveal the real root cause error.
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        header('Content-Type: text/plain');
        echo "FATAL PHP ERROR:\n";
        echo $error['message'] . "\nIn: " . $error['file'] . ':' . $error['line'];
    }
});

try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "ROOT CAUSE EXCEPTION:\n";
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "In: " . $e->getFile() . ':' . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
}
