<?php
session_start();
if (!isset($_SESSION['masv'])) {
    header("Location: login.php");
    exit();
}
