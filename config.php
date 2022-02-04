<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $name = 'https://ss.ts4ever.pl/';
    $name2 = '/';
    $version = '2';
    $print = 0;

    $server_ip = 'x.x.x.x';
    $db_name = 'systemSP';
    $login = 'xxxxxxx';
    $passwd = 'xxxxxxx';
    $charset = "utf8";

    if (!isset($_SESSION))
        session_start();
    
    try
    {
        $dsn = "mysql:host=".$server_ip.";dbname=".$db_name.";charset=".$charset;
        $sql = new PDO($dsn, $login, $passwd);
        $sql -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e)
    {
        echo "Połączenie nieudane: ".$e->getMessage();
        exit;
    }

    $sql->query('DELETE FROM `orders` WHERE `date` < NOW() - INTERVAL 180 DAY');

    require_once 'include/functions.php';
?>
