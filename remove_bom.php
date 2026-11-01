<?php
$content = file_get_contents('bootstrap/app.php');
$content = ltrim($content, "\xef\xbb\xbf");
file_put_contents('bootstrap/app.php', $content);
echo "stripped BOM, length=" . strlen($content) . "\n";
