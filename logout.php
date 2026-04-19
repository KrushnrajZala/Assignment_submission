<?php
require_once 'config.php';
session_destroy();
redirect(BASE_URL . 'login.php');
?>
