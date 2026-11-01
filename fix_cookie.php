<?php
$content = file_get_contents('app/Http/Controllers/Api/AuthController.php');
$content = str_replace("withCookie('jwt_token', \$token, 60 * 24, '/', null, false, true)", "withCookie('jwt_token', \$token, 60 * 24, '/', null, true, true, 'None')", $content);
$content = str_replace("withCookie('jwt_token', null, -1, '/', null, false, true)", "withCookie('jwt_token', null, -1, '/', null, true, true, 'None')", $content);
file_put_contents('app/Http/Controllers/Api/AuthController.php', $content);
echo "fixed\n";
