<?php
session_start();

if (!$_SESSION['logged'] || $_SESSION['privilege'] != 0) {
    header("refresh:1;url=index.php");
    die("Acesso restrito.");
}

?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>