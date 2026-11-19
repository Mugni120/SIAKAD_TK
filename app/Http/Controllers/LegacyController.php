<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegacyController extends Controller
{
    /**
     * Serve the original PHP application without changing its code.
     */
    public function handle(Request $request)
    {
        $requestPath = rawurldecode((string) parse_url($request->getRequestUri(), PHP_URL_PATH));

        // Apache's root .htaccess rewrites /siakad_tk/... to
        // /siakad_tk/public/index.php while keeping the original URL. Remove
        // the project/public prefix so the legacy path can be resolved.
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        $scriptFilename = isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : false;
        $publicBase = '';
        $projectBase = '';

        if ($documentRoot && $scriptFilename) {
            $normalizedDocumentRoot = str_replace('\\', '/', $documentRoot);
            $normalizedScriptFilename = str_replace('\\', '/', $scriptFilename);

            if (stripos($normalizedScriptFilename, $normalizedDocumentRoot) === 0) {
                $relativeScript = substr($normalizedScriptFilename, strlen($normalizedDocumentRoot));
                $publicBase = rtrim(str_replace('\\', '/', dirname($relativeScript)), '/');
                $projectBase = rtrim(str_replace('\\', '/', dirname(dirname($relativeScript))), '/');
            }
        }
        $requestPathLower = strtolower($requestPath);
        $publicBaseLower = strtolower($publicBase);
        $projectBaseLower = strtolower($projectBase);

        if ($publicBase !== '' && ($requestPathLower === $publicBaseLower || str_starts_with($requestPathLower, $publicBaseLower.'/'))) {
            $requestPath = substr($requestPath, strlen($publicBase));
        } elseif ($projectBase !== '' && ($requestPathLower === $projectBaseLower || str_starts_with($requestPathLower, $projectBaseLower.'/'))) {
            $requestPath = substr($requestPath, strlen($projectBase));
        }

        $path = ltrim($requestPath, '/');

        if ($path === '') {
            $path = 'index.php';
        }

        $basePath = realpath(dirname(__DIR__, 3));
        $fullPath = realpath($basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));

        if ($fullPath === false || ! str_starts_with($fullPath, $basePath . DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        if (str_contains($path, '..') || ! is_file($fullPath)) {
            abort(404);
        }

        $allowed = preg_match(
            '#^(?:index\.php|login\.php|register\.php|proses_login\.php|proses_register\.php|logout\.php|buat_admin\.php|admin/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.php|guru/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.php|orang_tua/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.php|assets/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9._-]+\.(?:css|js|map|png|jpe?g|gif|svg|ico|webp|woff2?|ttf|eot))$#i',
            $path
        );

        if (! $allowed) {
            abort(404);
        }

        if (! str_ends_with(strtolower($fullPath), '.php')) {
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $contentTypes = [
                'css' => 'text/css; charset=UTF-8',
                'js' => 'application/javascript; charset=UTF-8',
                'map' => 'application/json; charset=UTF-8',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
                'webp' => 'image/webp',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
            ];

            return response()->file($fullPath, [
                'Content-Type' => $contentTypes[$extension] ?? 'application/octet-stream',
            ]);
        }

        $_SERVER['PHP_SELF'] = '/' . $path;
        $_SERVER['SCRIPT_NAME'] = '/' . $path;
        $_SERVER['SCRIPT_FILENAME'] = $fullPath;

        $previousWorkingDirectory = getcwd();
        chdir(dirname($fullPath));

        ob_start();

        try {
            require $fullPath;
        } finally {
            $content = (string) ob_get_clean();
            chdir($previousWorkingDirectory);
        }

        $status = (int) http_response_code() ?: 200;

        $headers = [];
        foreach (headers_list() as $header) {
            if (preg_match('/^([^:]+):\s*(.+)$/', $header, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        header_remove();

        return response($content, $status, $headers);
    }
}
