<?php

/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing headers for API requests
 */

/**
 * Add CORS headers to allow cross-origin requests
 */
function addCorsHeaders()
{
    // Allow requests from any origin
    header('Access-Control-Allow-Origin: *');

    // Allow common HTTP methods
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

    // Allow common headers
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Cache preflight requests for 24 hours
    header('Access-Control-Max-Age: 86400');
}

/**
 * Handle preflight OPTIONS requests
 * Returns 200 OK for OPTIONS requests and exits
 */
function handlePreflight()
{
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Apply CORS handling automatically when this file is included
addCorsHeaders();
handlePreflight();
