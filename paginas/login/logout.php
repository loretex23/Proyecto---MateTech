<?php
session_start();
session_destroy();

header('Location: /web/paginas/login/login.php');
exit;
