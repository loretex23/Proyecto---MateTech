<?php
session_start();

if (!isset($_SESSION["ClubID"])) {

    header("Location: login/login.php");
    exit;

}