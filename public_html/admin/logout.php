<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';

logout();
setFlash('sucesso', 'Você saiu com segurança.');
redirect(ADMIN_URL . '/login.php');

