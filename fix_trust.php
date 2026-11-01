<?php
$content = file_get_contents('bootstrap/app.php');
$old = "        \$middleware->trustProxies('*', \\Illuminate\\Http\\Request::HEADER_X_FORWARDED_ALL);";
$new = "        \$middleware->trustProxies('*');";
$content = str_replace($old, $new, $content);
file_put_contents('bootstrap/app.php', $content);
echo "done\n";
