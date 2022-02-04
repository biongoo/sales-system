<?php
    $data = 'actual_orders';
    require_once 'include/header.php';
    
    $content .= '<div class="container-fluid px-0" id="main">';

        $temp_orders = $sql->query('SELECT `temporary_basket`.*, `clients`.`nick` FROM `temporary_basket` INNER JOIN `clients` ON `temporary_basket`.`id_client`=`clients`.`id` ORDER BY `temporary_basket`.`date` DESC')->fetchAll();

        $content .= '<div class="accordion" id="orders">';

        $script_content = '<script>name_src=\''.$name2.'\';</script>';
        if(count($temp_orders) < 1) $script_content = '<script>name_src=\''.$name2.'\'; show_alert(name_src)</script>';

        foreach($temp_orders as $order)
        {
            $products = json_decode($order['products']);
            foreach (array_keys($products, 'DEL', true) as $key) {
                unset($products[$key]);
            }
    
            $products = array_values($products);

            $content .= '<div class="card" id="row'.$order['id'].'">';
                $content .= '<div class="card-header" id="heading'.$order['id'].'">';
                    $content .= '<h2 class="mb-0">';
                        $content .= '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$order['id'].'" aria-expanded="true" aria-controls="collapse'.$order['id'].'">';
                            $content .= $order['date'].', Klient: '.$order['nick'].', Wartość: '.$order['value'].' zł';
                        $content .= '</button>';
                    $content .= '</h2>';
                $content .= '</div>';
                $content .= '<div id="collapse'.$order['id'].'" class="collapse" aria-labelledby="heading'.$order['id'].'" data-parent="#orders">';
                    $content .= '<div class="card-body mb-5">';
                        $content .= '<table class="table table-hover table-bordered">';
                            $content .= '<thead>';
                                $content .= '<tr>';
                                    $content .= '<th scope="col">Produkt</th>';
                                    $content .= '<th scope="col">Ilość</th>';
                                    $content .= '<th scope="col">Cena</th>';
                                    $content .= '<th scope="col">Wartość</th>';
                                $content .= '</tr>';
                            $content .= '</thead>';
                            $content .= '<tbody>';
                                $i = 1;
                                if(count($products) == 0) $content .= '<tr><td style="text-align: center;" colspan="4">Brak produktów</td></tr>';
                                foreach($products as $product)
                                {   
                                    $content .= '<tr>';
                                        $content .= '<td>'.$product[0].'</td>';
                                        $content .= '<td>'.$product[2].'</td>';
                                        $content .= '<td>'.$product[3].'</td>';
                                        $content .= '<td>'.$product[2] * $product[3].'</td>';
                                    $content .= '</tr>';
                                    $i++;
                                    $src = '<script>orders++;</script>';
                                    $script_content .= $src;
                                }
                            $content .= '</tbody>';
                        $content .= '</table>';

                        $content .= '<button type="button" class="btn btn-lg btn-outline-danger float-left" onclick="delete_temp('.$order['id'].')">Usuń</button>';
                        $content .= '<button type="button" class="btn btn-lg btn-outline-success float-right" onclick="window.location = \'main.php?continue='.$order['id'].'\';">Kontynuuj</button>';

                    $content .= '</div>';
                $content .= '</div>';
            $content .= '</div>';
        }

        $content .= '</div>';

    $content .= '';

    $content .= '</div>';
            
    require_once 'include/footer.php';
?>