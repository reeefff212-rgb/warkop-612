<?php
require_once __DIR__ . '/config.php';
$to = ($_GET['to'] ?? '') === 'owner' ? 'owner/login.php' : 'kasir/login.php';
unset($_SESSION['user']);
session_regenerate_id(true);
redirect($to);
