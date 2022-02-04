<?php

    function alert($info)
    {
        global $data, $name2;
        $_SESSION['msg'] = $info;
        header('Location: '.$name2.$data.'.php');
        exit;
    }
    
    function clients()
    {
        global $sql;
        $clients = '<option value="">Brak</option>';
        $stmt = $sql->query('SELECT * FROM `clients` ORDER BY `nick`'); 
        foreach($stmt as $row) 
        { 
            $clients .= '<option value="'.$row['id'].'">'.$row['nick'].'</option>';
        }
        return $clients;
    }

    function products()
    {
        global $sql;
        $products = '<option value="">Brak</option>';
        $stmt = $sql->query('SELECT * FROM `products` ORDER BY `name`'); 
        foreach($stmt as $row) 
        { 
            $products .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
        return $products;
    }
?>