<?php
$content = file_get_contents('bootstrap/app.php');
$content = preg_replace("/\$middleware->trustProxies\('.*?'.*?\);/", "\$middleware->trustProxies('*');", $content);
file_put_contents('bootstrap/app.php', $content);
echo "done\n";
