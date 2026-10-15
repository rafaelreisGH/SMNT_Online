<?php
// index.php na raiz
// Como não quer sessão, usamos apenas a lógica de verificação de Token no JS ou redirecionamento direto
header("Location: login.php");
exit;
