<?php

ini_set('php_output_buffering', 'off');

// Set a valid header so browsers pick it up correctly.
header('Content-type: text/html; charset=utf-8');

// Emulate the header BigPipe sends so we can test through Varnish.
header('Surrogate-Control: BigPipe/1.0');

// Explicitly disable caching so Varnish and other upstreams won't cache.
header("Cache-Control: no-cache, must-revalidate");

// Setting this header instructs Nginx to disable fastcgi_buffering and disable
// gzip for this request.
header('X-Accel-Buffering: no');

echo 'Begin test...<br />' . "\r\n";

// For 3 seconds, repeat a 1024 byte string.
for ($i = 0; $i < 10; $i++) {
    // 1024 byte string.
    $one_kilobyte_string = str_repeat('.', 1024);
    echo $one_kilobyte_string . '<br />' . "\r\n";
    echo $i . '<br />' . "\r\n";
    flush();
    sleep(1);
}

echo 'End test...<br />' . "\r\n";
?>