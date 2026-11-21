<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Robots-Tag: noindex, nofollow');
$file = dirname(__DIR__).'/index.html';
if (!is_file($file)) { http_response_code(404); exit('Not found'); }
$source = file_get_contents($file);
if ($source === false || strlen($source) > 150000) { http_response_code(500); exit('Unable to read homepage'); }
echo '<!doctype html><html><head><meta name="robots" content="noindex,nofollow"></head><body><pre id="source">'.htmlspecialchars(base64_encode($source), ENT_QUOTES,'UTF-8').'</pre></body></html>';
