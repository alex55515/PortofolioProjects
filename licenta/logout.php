<?php

include 'includes/config.php';

$conn = getDB();

session_start();
session_unset();
session_destroy();

header("location: index.php");
exit;

?>