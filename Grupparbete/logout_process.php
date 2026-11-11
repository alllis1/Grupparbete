<?php
require_once __DIR__ . '/include/bootstrap.php';

$_SESSION = [];

session_destroy();

header("Location: views/index.php");
exit;

