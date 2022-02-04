<?php
    require_once '../config.php';

    if(isset($_GET['add_row_regular']))
    {   
        $id_client = intval($_GET['add_row_regular']);
        $new_id_product = intval($_GET['new_id_product']);

        $stmt = $sql->prepare('SELECT * FROM `products_regular` WHERE `id_client` = ?');
        $stmt->execute([$id_client]);
        $regular_list = $stmt->fetch();

        $products = json_decode($regular_list[2]);
        $products[] = ["", "0", $new_id_product];
        $products = json_encode($products);
        
        $stmt = $sql->prepare('UPDATE `products_regular` SET `products`= ?');
        if($stmt->execute([$products])) echo 1;
    }

    elseif(isset($_GET['change_client']))
    {
        $id_client = intval($_GET['change_client']);
        $stmt = $sql->prepare('SELECT `symbols` FROM `clients` WHERE `id`=?');
        $stmt->execute([$id_client]);
        echo $stmt->fetchColumn();
    }

    elseif(isset($_GET['change_price']))
    {
        $new_price = $_GET['change_price'];
        $id = intval($_GET['id']);
        if($sql->prepare('UPDATE `products_actual` SET `price` = ? WHERE `products_actual`.`id_product_hist` = ?')->execute([$new_price, $id]))
            {
                $stmt = $sql->prepare('SELECT `amount` FROM `products_actual` WHERE `products_actual`.`id_product_hist` = ?');
                $stmt->execute([$id]);
                echo $stmt->fetchColumn();
            }
        else
            echo '0';
    }

    elseif(isset($_GET['change_product_regular']))
    {
        $id_client = intval($_GET['regular_client']);
        $prod_index = intval($_GET['change_product_regular']) - 1;
        $new_prod_id = intval($_GET['new_prod_id']);

        $stmt = $sql->prepare('SELECT * FROM `products_regular` WHERE `id_client` = ?');
        $stmt->execute([$id_client]);
        $regular_list = $stmt->fetch();

        $products = json_decode($regular_list[2]);
        $products[$prod_index][0] = $new_prod_id;
        $products = json_encode($products);

        $stmt = $sql->prepare('UPDATE `products_regular` SET `products`= ?');
        if($stmt->execute([$products])) echo 1;
    }

    elseif(isset($_GET['delete_order']))
    {   
        $id = intval($_GET['delete_order']);
        if($sql->prepare('DELETE FROM `orders` WHERE `id`=?')->execute([$id])) echo 1;
    }

    elseif(isset($_GET['delete_row_regular']))
    {   
        $id_row = intval($_GET['delete_row_regular']) - 1;
        $id_client = intval($_GET['delete_row_client']);

        $stmt = $sql->prepare('SELECT * FROM `products_regular` WHERE `id_client` = ?');
        $stmt->execute([$id_client]);
        $regular_list = $stmt->fetch();
        
        $products = json_decode($regular_list[2]);
        unset($products[$id_row]);
        $products = array_values($products);
        $products = json_encode($products);

        $stmt = $sql->prepare('UPDATE `products_regular` SET `products`= ?');
        if($stmt->execute([$products])) echo 1;
    }

    elseif(isset($_GET['delete_product']))
    {
        $id = intval($_GET['delete_product']);
        if($sql->prepare('DELETE FROM `products_actual` WHERE `products_actual`.`id_product_hist` = ?')->execute([$id]))
            echo '1';
        else
            echo '0';
    }

    elseif(isset($_GET['delete_temp']))
    {   
        $id = intval($_GET['delete_temp']);
        if($sql->prepare('DELETE FROM `temporary_basket` WHERE `id`=?')->execute([$id])) echo 1;
    }

    elseif(isset($_GET['finish']))
    {
        $id = intval($_GET['finish']);
        $stmt = $sql->prepare('SELECT `products`, `value`, `id_edit`, `id_client` FROM `temporary_basket` WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $value = $row['value'];
        $id_edit = $row['id_edit'];
        $id_client = $row['id_client'];
        $products = json_decode($row['products'],true);

        foreach (array_keys($products, 'DEL', true) as $key) {
            unset($products[$key]);
        }

        $filtered_products = array_values($products);

        if(count($filtered_products) < 1) 
        {
            echo 0;
            exit;
        }

        $insert_data = json_encode($filtered_products);

        $sql->prepare('DELETE FROM `temporary_basket` WHERE `id`=?')->execute([$id]);

        if(!$id_edit)
            $sql->prepare('INSERT INTO `orders` (`id_client`, `value`, `date`, `products`) VALUES (?, ?, NOW(), ?)')->execute([$id_client, $value, $insert_data]);
        else
            $sql->prepare('UPDATE `orders` SET `value`=?, `products`=?, `date_edit`=NOW() WHERE `id`=?')->execute([$value, $insert_data, $id_edit]);

        $mysql_id = $sql->lastInsertId();
        if(!$mysql_id) $mysql_id = $id_edit;

        $date = $sql->query('SELECT DATE(`date`) FROM `orders` WHERE `id`='.$mysql_id)->fetchColumn();

        $id_order = $sql->query('SELECT COUNT(*) FROM `orders` WHERE DATE(`date`) = \''. $date .'\' AND `id` <= '. $mysql_id)->fetchColumn();

        $echo_data = [$insert_data, $id_order, $value, $mysql_id];

        echo json_encode($echo_data);
    }

    elseif(isset($_GET['finish_regular']))
    {   
        $products = json_decode($_GET['finish_regular']);
        $value = json_decode($_GET['value']);
        $id = $sql->query('SELECT `id` FROM `orders` WHERE id_client = 46 AND DATE(`date`)=CURDATE()')->fetchColumn();

        if(!$id)
            $sql->prepare('INSERT INTO `orders` (`id_client`, `value`, `date`, `products`) VALUES (46, ?, NOW(), ?)')->execute([$value, $_GET['finish_regular']]);
        else
            $sql->prepare('UPDATE `orders` SET `value`=?, `products`=?, `date_edit`=NOW() WHERE `id`=?')->execute([$value, $_GET['finish_regular'], $id]);

        $mysql_id = $sql->lastInsertId();
        if(!$mysql_id) $mysql_id = $id;

        $date = $sql->query('SELECT DATE(`date`) FROM `orders` WHERE `id`='.$mysql_id)->fetchColumn();
        $id_order = $sql->query('SELECT COUNT(*) FROM `orders` WHERE DATE(`date`) = \''. $date .'\' AND `id` <= '. $mysql_id)->fetchColumn();

        $products_regular = $sql->query('SELECT `products` FROM `products_regular` WHERE id_client = 46')->fetchColumn();
        $products_regular = json_decode($products_regular);
        
        ob_start();    
        $content = '<!DOCTYPE html>';
        $content .= '<html lang="pl">';
            $content .= '<head>';
                //<!-- Required meta tags -->
                $content .= '<meta charset="utf-8">';
                $content .= '<meta name="author" content="biongoo">';
                $content .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';

                //<!-- CSS -->
                $content .= '<link rel="stylesheet" href="../css/bootstrap.min.css">';
                $content .= '<link rel="stylesheet" href="../../css/bootstrap.min.css">';

                $content .= '<style>tr, td, th { border: 5px solid black !important; padding: .50rem !important; }  @media print { @page { margin: -20px 30px 0 0px; } body { margin: 1.6cm; } tr:nth-child(odd) td { background-color: #808080 !important; -webkit-print-color-adjust: exact; } }</style>';

                //<!-- Title -->
                $content .= '<title>System zamówień</title>';

            $content .= '</head>';
            $content .= '<body>';

                $content_a = '<div class="container-fluid px-0" id="main">';
                    $content_a .= '<h2><b>Zamówienie nr: ' . $id_order .'/' . $date . '</b></h2>';
                    $content_a .= '<div style="font-size: 1.2em;">Klient: <b>PALEO</b></div>';
                    $content_a .= '<br><table class="table table-striped">';
                        $content_a .= '<thead style="font-size: 1.1em;">';
                            $content_a .= '<tr>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 5%">Lp.</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle;">Produkt</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 10%">Ilość</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 10%">Cena</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 10%">Ostatnia cena</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 15%">Wartość</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 20%">Uwagi</th>';
                            $content_a .= '</tr>';
                        $content_a .= '</thead>';
                        $content_a .= '<tbody style="font-size: 1.1em;">';
                            $i = 1;
                            foreach($products as $product)
                            {
                                foreach($products_regular as $key => $row)
                                {
                                    if($row[2] == $product[6])
                                    {
                                        $products_regular_index = $key;
                                        break;
                                    }
                                }
                                $products_regular[$products_regular_index][1] = $product[3];
                                $content_a .= '<tr>';
                                    $content_a .= '<td style="text-align: center; vertical-align: middle;"><b>'.$i.'</b></td>';
                                    $content_a .= '<td><b>'.$product[0].'</b></td>';
                                    $content_a .= '<td style="text-align: center; vertical-align: middle;"><b>'.$product[2].'</b></td>';
                                    $content_a .= '<td style="text-align: center; vertical-align: middle;"><b>'.$product[3].'</b></td>';
                                    $content_a .= '<td style="text-align: center; vertical-align: middle;"><b>'.$product[7].'</b></td>';
                                    $content_a .= '<td style="text-align: center; vertical-align: middle;"><b>'.round($product[3] * $product[2], 2).'</b></td>';
                                    $content_a .= '<td></td>';
                                $content_a .= '</tr>';
                                $i++;
                            }
                        $content_a .= '</tbody>';
                    $content_a .= '</table>';

                $content_a .= '</div>';

                $content_a .= '<div class="row mx-0 d-flex flex-row-reverse"><div style="font-size: 1.5em;" class="text-right">Przelew: <b>'.$value.' zł</b></div></div>';
                $content_a .= '<div class="row mx-0 d-flex flex-row-reverse"><div style="font-size: 1.5em;" class="text-right">Wartość: <b>0 zł</b></div></div>';

                $content .= $content_a;

            $content .= '</body>';

        $content .= '</html>';

        $today = date('Y-m-d');

        $sql->prepare('UPDATE `products_regular` SET `products`=? WHERE `id_client`=46')->execute([json_encode($products_regular)]);
        
        echo $content;
        if($print)
        {

            $cont = ob_get_contents();
            $file = fopen('../zamowienia/PALEO/PALEO-'.$today.'.html','w');
            fwrite($file, $cont);
            fclose($file);
        }
    }

    elseif(isset($_GET['get_name']))
    {   
        if(empty($_GET['get_name'] || $_GET['get_name'] == 'null'))
            return 0;

        $stmt = $sql->prepare('SELECT `nick` FROM `clients` WHERE `id`=?'); $stmt->execute([$_GET['get_name']]); 
        $nick = $stmt->fetchColumn();

        if(!empty($nick))
            echo $nick;
        else
            echo 0;
    }

    elseif(isset($_GET['info']))
    {   
        $id = intval($_GET['info']);
        $stmt = $sql->prepare('SELECT `purchase_price`, `desc_long` FROM `products_history` WHERE id=?');
        $stmt->execute([$id]); 
        $info = $stmt->fetch();
        echo 'Cena: ' . $info['purchase_price'] . ', Opis długi: ' . $info['desc_long'];
    }

    elseif(isset($_GET['insert_temp']))
    {
        if($_GET['edit_id']) 
            $edit = intval($_GET['edit_id']);
        else
            $edit = NULL;

        if(isset($_GET['id_mysql']))
        {
            if(is_integer(intval($_GET['id_mysql'])))
            {
                $id_mysql = intval($_GET['id_mysql']);
                if($sql->prepare('UPDATE `temporary_basket` SET `products`=?, `value`=?, `date`=NOW() WHERE `id`=?')->execute([$_GET['insert_temp'], $_GET['val'], $id_mysql]))
                    echo $id_mysql;
                else
                    echo 0;
            }
            else
            {
                echo 0;
                exit;
            }
        }
        else
        {
            $stmt = $sql->prepare('INSERT INTO `temporary_basket` (`products`, `value`, `id_client`, `date`, `id_edit`) VALUES (?, ?, ?, NOW(), ?)');
            $stmt->execute([$_GET['insert_temp'], $_GET['val'], $_GET['client'], $edit]);
            $id = $sql->lastInsertId();
            echo $id;
        }
    }

    elseif(isset($_GET['last_prod_id']))
    {   
        $id_client = intval($_GET['last_prod_id']);
        $sql->prepare('UPDATE `products_regular` SET `last_prod_id` = `last_prod_id` + 1 WHERE `id_client`=?')->execute([$id_client]);
        $stmt = $sql->prepare('SELECT `last_prod_id` FROM `products_regular` WHERE `id_client`=?');
        $stmt->execute([$id_client]); 
        $new_id = $stmt->fetchColumn();
        echo $new_id;
    }

    elseif(isset($_GET['print']))
    {   
        ob_start();
        $id = intval($_GET['print']);

        $stmt = $sql->prepare('SELECT `orders`.`id`, `orders`.`value`, DATE(`orders`.`date`) as `date`, `orders`.`products`, `clients`.`nick`, `orders`.`id_client` FROM `orders` INNER JOIN `clients` ON `orders`.`id_client` = `clients`.`id` WHERE `orders`.`id`=?');
        $stmt->execute([$id]);
        $stmt = $stmt->fetch();

        if(empty($stmt))
            return 0;

        $new_id = $sql->query('SELECT COUNT(*) FROM `orders` WHERE DATE(`date`) = DATE(\''. $stmt['date'] .'\') AND `id` <= '. $id .'')->fetchColumn();

        if(isset($_GET['credit']) && $_GET['credit'] != 0)
        {
            $on_credit = floatval($_GET['credit']);
            $stmtv2 = $sql->prepare('INSERT INTO `credits` (`id_client`, `value`, `date`) VALUES (?, ?, NOW())');
            $stmtv2->execute([$stmt['id_client'], $on_credit]);
        }
        else 
            $on_credit = 0;

        $value = round($stmt['value']) - $on_credit;
        
        $content = '<!DOCTYPE html>';
        $content .= '<html lang="pl">';
            $content .= '<head>';
                //<!-- Required meta tags -->
                $content .= '<meta charset="utf-8">';
                $content .= '<meta name="author" content="biongoo">';
                $content .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';

                //<!-- CSS -->
                $content .= '<link rel="stylesheet" href="../css/bootstrap.min.css">';
                $content .= '<link rel="stylesheet" href="../../css/bootstrap.min.css">';

                $content .= '<style>tr, td, th { border: 5px solid black !important; padding: .50rem !important; }  @media print { @page { margin: -20px 30px 0 0px; } body { margin: 1.6cm; } tr:nth-child(odd) td { background-color: #808080 !important; -webkit-print-color-adjust: exact; } }</style>';

                //<!-- Title -->
                $content .= '<title>System zamówień</title>';

            $content .= '</head>';
            $content .= '<body>';

                $content_a = '<div class="container-fluid px-0" id="main">';
                    $content_a .= '<h2><b>Zamówienie nr: ' . $new_id .'/' . $stmt['date'] . '</b></h2>';
                    $content_a .= '<div style="font-size: 1.2em;">Klient: <b>'.$stmt['nick'].'</b></div>';
                    $content_a .= '<br><table class="table table-striped">';
                        $content_a .= '<thead style="font-size: 1.1em;">';
                            $content_a .= '<tr>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 5%">Lp.</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle;">Produkt</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle;">Ilość</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle;">Cena</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle;">Waga/sztuki</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle;">Cena 1 kg/szt</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle;">Wartość</th>';
                            $content_a .= '</tr>';
                        $content_a .= '</thead>';
                        $content_a .= '<tbody style="font-size: 1.1em;">';
                            $products = json_decode($stmt['products']);
                            $i = 1;
                            foreach($products as $product)
                            {
                                if($product[5] == 1) $product[5] = 'op';
                                elseif($product[5] == 2) $product[5] = 'szt';
                                elseif($product[5] == 3) $product[5] = 'kg';

                                $price_unit = $product[4];

                                if($product[5] == 'op') $unit = 'kg/szt';
                                elseif($product[5] == 'kg') { $unit = 'kg'; $price_unit = 1; $product[4] = $product[2];}
                                elseif($product[5] == 'szt') { $unit = 'szt'; $product[4] = $product[2]; $price_unit = 1; }

                                $content_a .= '<tr>';
                                    $content_a .= '<td><b>'.$i.'</b></td>';
                                    $content_a .= '<td><b>'.$product[0].'</b></td>';
                                    $content_a .= '<td><b>'.$product[2].' '.$product[5].'</b></td>';
                                    $content_a .= '<td><b>'.$product[3].'</b></td>';
                                    $content_a .= '<td><b>'.$product[4].' '.$unit.'</b></td>';
                                    $content_a .= '<td style="text-align: center;"><b>'.round($product[3]/$price_unit, 2).' zł</b></td>';
                                    $content_a .= '<td><b>'.round($product[3] * $product[2], 2).'</b></td>';
                                $content_a .= '</tr>';
                                $i++;
                            }
            
                        $content_a .= '</tbody>';
                    $content_a .= '</table>';

                $content_a .= '</div>';

                if($on_credit) 
                {
                    $content_a .= '<div class="row mx-0 d-flex flex-row-reverse"><div style="font-size: 1.5em;" class="float-right">Kredyt: <b>'.$on_credit.' zł</b></div></div>';
                    $content_a .= '<div class="row mx-0 d-flex flex-row-reverse"><div style="font-size: 1.5em;" class="text-right">Wpłacono: <b>'.$value.' zł</b></div></div>';
                }
                else
                    $content_a .= '<div class="row mx-0 d-flex flex-row-reverse"><div style="font-size: 1.5em;" class="text-right">Wartość: <b>'.$value.' zł</b></div></div>';

                $content .= $content_a;
                
                if(count($products) <= 10) { $content .= '<hr class="my-4" style="height:2px;border:none;color:black;background-color:black;">'; $content .= $content_a; }

            $content .= '</body>';

        $content .= '</html>';

        $today = date('Y-m-d');
        
        echo $content;
        if($print)
        {

            $cont = ob_get_contents();
            if(count($products) <= 10) 
                $file = fopen('../zamowienia/1/'.$today.'-'.$new_id.'.html','w');
            else
                $file = fopen('../zamowienia/2/'.$today.'-'.$new_id.'.html','w');
            fwrite($file, $cont);
            fclose($file);
        }
    }

    elseif(isset($_GET['print_regular']))
    {   
        ob_start();
        $products = json_decode($_GET['print_regular']);
        $today = date('Y-m-d');

        $content = '<!DOCTYPE html>';
        $content .= '<html lang="pl">';
            $content .= '<head>';
                //<!-- Required meta tags -->
                $content .= '<meta charset="utf-8">';
                $content .= '<meta name="author" content="biongoo">';
                $content .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';

                //<!-- CSS -->
                $content .= '<link rel="stylesheet" href="../css/bootstrap.min.css">';
                $content .= '<link rel="stylesheet" href="../../css/bootstrap.min.css">';

                $content .= '<style>tr, td, th { border: 5px solid black !important; padding: .50rem !important; height: 50px; }  @media print { @page { margin: -20px 30px 0 0px; } body { margin: 1.6cm; } tr:nth-child(odd) td { background-color: #808080 !important; -webkit-print-color-adjust: exact; } }</style>';

                //<!-- Title -->
                $content .= '<title>System zamówień</title>';

            $content .= '</head>';
            $content .= '<body>';

                $content_a = '<div class="container px-0" id="main">';
                    $content_a .= '<h2><b>Zamówienie PALEO z dnia '.$today.'</b></h2>';
                    $content_a .= '<br><table class="table table-striped" style=" width: 70%;">';
                        $content_a .= '<thead style="font-size: 1.3em;">';
                            $content_a .= '<tr>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 5%; border: none;">&#10004;</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 5%;">Lp.</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 30%;">Produkt</th>';
                                $content_a .= '<th scope="col" style="text-align: center; vertical-align: middle; width: 15%;">Ilość</th>';
                            $content_a .= '</tr>';
                        $content_a .= '</thead>';
                        $content_a .= '<tbody style="font-size: 1.3em;">';
                            $i = 1;
                            foreach($products as $product)
                            {
                                $content_a .= '<tr>';
                                    $content_a .= '<td></td>';
                                    $content_a .= '<td style="text-align: center;"><b>'.$i.'</b></td>';
                                    $content_a .= '<td><b>'.$product[0].'</b></td>';
                                    $content_a .= '<td style="text-align: center;"><b>'.$product[2].'</b></td>';
                                $content_a .= '</tr>';
                                $i++;
                            }
                        $content_a .= '</tbody>';
                    $content_a .= '</table>';

                $content_a .= '</div>';

                $content .= $content_a;

            $content .= '</body>';

        $content .= '</html>';
        
        echo $content;
        if($print)
        {
            $cont = ob_get_contents();
            $file = fopen('../zamowienia/1/PALEO - '.$today.'.html','w');
            fwrite($file, $cont);
            fclose($file);
        }
    }

    elseif(isset($_GET['set_tr']))
    {  
        $id = intval($_GET['id']);
        if($_GET['set_tr'] == 'check')
        {
            $sql->prepare('UPDATE `clients` SET `transfer`="1" WHERE `id`=?')->execute([$id]);
            echo 'Klient został dodany to przelewów!';
        }
        elseif($_GET['set_tr'] == 'uncheck')
        {
            $sql->prepare('UPDATE `clients` SET `transfer`="0" WHERE `id`=?')->execute([$id]);
            echo 'Klient został usunięty z przelewów!';
        }
    }

    elseif(isset($_GET['show_history']))
    {  
        $id_tran = intval($_GET['show_history']);
        $stmt = $sql->prepare('SELECT `ids_of_transfers` FROM `transfers_history` WHERE `id`=?');
        $stmt->execute([$id_tran]);
        
        $ids = json_decode($stmt->fetchColumn());
        $len = count($ids);

        $stmt = $sql->prepare('SELECT `date` FROM `transfers_history` WHERE `id`=? LIMIT 1');
        $stmt->execute([$id_tran]);
        $dateOfTransfersComplete = $stmt->fetchColumn();
        

        $xd = '(';

        foreach($ids as $key=>$id)
        {
            if($key+1 == $len)
                $xd .= intval($id);
            else
                $xd .= intval($id) . ', ';
        }

        $xd .= ')';

        $stmt = $sql->prepare('SELECT `id_invoice`, `value`, `date` FROM `transfers` WHERE `id_invoice` IN '.$xd.' AND `date` BETWEEN DATE_SUB(\''.$dateOfTransfersComplete.'\', INTERVAL 180 DAY) AND \''.$dateOfTransfersComplete.'\'');
        $stmt->execute([]);
        $transfers_h = $stmt->fetchAll();

        $content = '';
        $j = 1;
        foreach($transfers_h as $tran_h)
        {
            $content .= '<tr>';
                $content .= '<th scope="row">'.$j.'</th>';
                $content .= '<td>'.$tran_h['date'].'</td>';
                $content .= '<td class="bg-success">FS '.$tran_h['id_invoice'].'</td>';
                $content .= '<td>'.$tran_h['value'].'</td>';
            $content .= '</tr>';
            $j++;
        }
        
        echo $content;
    }

    elseif(isset($_GET['sort_regular']))
    {  
        $index = intval($_GET['sort_regular']) - 1;
        $id_client = intval($_GET['sort_regular_client']);
        $set = $_GET['sort_regular_set'];

        $stmt = $sql->prepare('SELECT * FROM `products_regular` WHERE `id_client` = ?');
        $stmt->execute([$id_client]);
        $regular_list = $stmt->fetch();

        $products = json_decode($regular_list[2]);
        $temp1 = $products[$index];

        switch($set) {
            case 'up':
                $new_index = $index - 1;
                break;
            case 'down':
                $new_index = $index + 1;
                break;
        }

        $temp2 = $products[$new_index];

        $products[$new_index] = $temp1;
        $products[$index] = $temp2;

        $products = json_encode($products);

        $stmt = $sql->prepare('UPDATE `products_regular` SET `products`= ?');
        if($stmt->execute([$products])) echo 1;
    }

    elseif(isset($_GET['symbols']))
    {
        $id = intval($_GET['symbols']);
        $stmt = $sql->prepare('SELECT `symbols` FROM `clients` WHERE `id`=?'); $stmt->execute([$id]); 
        $symbols = $stmt->fetchColumn();
        if($symbols && $symbols != '[]')
        {
            $data = json_decode($symbols);
            foreach($data as $row)
                echo '<option value="'.$row.'">'.$row.'</option>';
        }
        else
            echo '<option value="">Brak symboli</option>';
    }
?>