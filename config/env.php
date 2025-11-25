<?php
/**
 * Simple Environment Variable Loader
 * Loads variables from .env.local or .env file
 */

function loadEnv($filePath = null) {
    if ($filePath === null) {
        $filePath = __DIR__ . '/../.env.local';
        
        // Fallback to .env if .env.local doesn't exist
        if (!file_exists($filePath)) {
            $filePath = __DIR__ . '/../.env';
        }
    }
    
    if (!file_exists($filePath)) {
        // If no env file exists, use defaults (for first-time setup)
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            // Set as environment variable and make accessible via getenv()
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

/**
 * Get environment variable with optional default
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Convert string booleans to actual booleans
    if (strtolower($value) === 'true') {
        return true;
    }
    if (strtolower($value) === 'false') {
        return false;
    }
    
    return $value;
}

// Load environment variables automatically
loadEnv();
