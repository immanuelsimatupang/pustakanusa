<?php
/**
 * Simple Environment Variable Loader
 * Loads environment variables from .env file
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }

        // Parse key=value lines
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if ((startsWith($value, '"') && endsWith($value, '"')) || 
                (startsWith($value, "'") && endsWith($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            // Set the environment variable
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
    return true;
}

function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key) ?? $default;
    
    // Convert true/false strings to boolean
    if ($value === 'true') {
        return true;
    } elseif ($value === 'false') {
        return false;
    }
    
    return $value;
}
?>