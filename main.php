<?php
    $data = 'main';
    require_once 'include/header.php';
    
    $content .= '<div class="container-fluid px-0" id="main" unselectable="on" onselectstart="return false;" onmousedown="return false;">';
        $content .= '<table class="table table-striped table-dark table-hover">';
            $content .= '<thead class="bg-primary">';
                $content .= '<tr>';
                    $content .= '<th scope="col">Produkt</th>';
                    $content .= '<th scope="col">Opis</th>';
                    $content .= '<th scope="col">Cena</th>';
                    $content .= '<th scope="col">Kg/szt</th>';
                $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody class="table-bordered">';

                $stmt = $sql->query('SELECT `products_history`.`id`, `products`.`name`, `products_history`.`desc_short`, `products_actual`.`amount`, `products_actual`.`unit`, `products_actual`.`price`, `products_history`.`purchase_price` FROM `products_actual` INNER JOIN `products_history` ON `products_actual`.`id_product_hist` = `products_history`.`id` INNER JOIN `products` ON `products_history`.`id_product` = `products`.`id`  ORDER BY `name`, `desc_short`')->fetchAll();
                foreach($stmt as $row)
                {
                    $content .= '<tr id="row'.$row['id'].'" onclick="product(\''.$row['id'].'\', \''.$row['unit'].'\', \''.$row['amount'].'\')">';
                        $content .= '<td>'.$row['name'].'</td>';
                        $content .= '<td>'.$row['desc_short'].'</td>';
                        $content .= '<td>'.$row['price'].'</td>';
                        $content .= '<td>'.round($row['price']/$row['amount'], 2).'</td>';
                    $content .= '</tr>';
                }

            $content .= '</tbody>';
        $content .= '</table>';
    $content .= '</div>';

    $content .= '<footer class="navbar fixed-bottom bg-danger">';

        $content .= '<div id="basket" class="container-fluid">Koszyk: ';
            $content .= '<span id="elements"> 0zł (0)';
            $content .= '</span>';
            $content .= '<button class="btn btn-primary float-right" name="basket" onclick="show_basket()" id="basket_button">Koszyk</button>';
        $content .= '</div>';

    $content .= '</footer>';

    $script_content = '<script>href_name="'.$name2.'" </script>';

    if(isset($_GET['client']))
    {
        $script_content .= '<script>bootbox.prompt({';
            $script_content .= 'title: "Wybierz klienta: ",';
            $script_content .= 'buttons: {';
                $script_content .= 'cancel: {';
                    $script_content .= 'className: \'btn-outline-warning\'';
                $script_content .= '},';
                $script_content .= 'confirm: {';
                    $script_content .= 'className: \'btn-outline-success\'';
                $script_content .= '}';
            $script_content .= '},';
            $script_content .= 'backdrop: true,';
            $script_content .= 'inputType: "select",';
            $script_content .= 'inputOptions: [';

            $clients[] = ['text' => 'Brak', 'value' => ''];
            $script_content .= '{';
                $script_content .= 'text: "Brak",';
                $script_content .= 'value: "",';
            $script_content .= '},';

            $stmt = $sql->query('SELECT `id`, `nick` FROM `clients` ORDER BY `nick`'); 
            foreach($stmt as $row) 
            {
                $script_content .= '{';
                    $script_content .= 'text: "'.$row['nick'].'",';
                    $script_content .= 'value: "'.$row['id'].'",';
                $script_content .= '},';
            }

            $script_content .= '],';
            $script_content .= 'callback: function (result) {';
                $script_content .= 'client = result; get_client();';
            $script_content .= '}';
        $script_content .= '});</script>';
    }

    elseif(isset($_GET['edit']))
    {
        $id_order = intval($_GET['edit']);

        $stmt = $sql->prepare('SELECT `id` FROM `temporary_basket` WHERE `id_edit`=?'); $stmt->execute([$id_order]);
        $id_temp = $stmt->fetchColumn();
        if($id_temp)
        {
            header("Location: main.php?continue=" . $id_temp);
            exit;
        }

        $stmt = $sql->prepare('SELECT `orders`.`id_client`, `orders`.`value`, DATE(`orders`.`date`) as `date`, `orders`.`products`, `clients`.`nick` FROM `orders` INNER JOIN `clients` ON `orders`.`id_client` = `clients`.`id` WHERE `orders`.`id`=?'); $stmt->execute([$id_order]); 
        $order = $stmt->fetch();

        if(empty($order))
            return;
            
        $script_content .= '<script>';
            $script_content .= 'client = ' . $order['id_client'] . ';';
            $script_content .= 'name_client = \'' . $order['nick'] . '\';';
            $script_content .= 'document.title = ' . $order['value'] . ' + \'zł\' + \' - \' + name_client;';
            $script_content .= 'value = ' . $order['value'] . ';';
            $script_content .= 'basket = JSON.parse(\'' . $order['products'] . '\');';
            $script_content .= 'change_value(value);';
            $script_content .= '$("#basket_button").css("visibility", "visible");';
            $script_content .= 'order_day = \'' . $order['date'] . '\';';
            $script_content .= 'edit = ' . $id_order . ';';
        $script_content .= '</script>';
    }

    if(isset($_GET['continue']) && !isset($_GET['edit']))
    {
        $id_order = intval($_GET['continue']);
        $stmt = $sql->prepare('SELECT `temporary_basket`.`id_client`, `temporary_basket`.`value`, DATE(`temporary_basket`.`date`) as `date`, `temporary_basket`.`products`, `clients`.`nick` FROM `temporary_basket` INNER JOIN `clients` ON `temporary_basket`.`id_client` = `clients`.`id` WHERE `temporary_basket`.`id`=?'); $stmt->execute([$id_order]);
        $order = $stmt->fetch();

        if(empty($order))
            return;

        $products = json_decode($order['products'],true);
        $del = 0;
        foreach (array_keys($products, 'DEL', true) as $key) { $del++;}
        
        $script_content .= '<script>';
            $script_content .= 'client = ' . $order['id_client'] . ';';
            $script_content .= 'name_client = \'' . $order['nick'] . '\';';
            $script_content .= 'document.title = ' . $order['value'] . ' + \'zł\' + \' - \' + name_client;';
            $script_content .= 'value = ' . $order['value'] . ';';
            $script_content .= 'basket = JSON.parse(\'' . $order['products'] . '\');';
            $script_content .= 'del = ' . $del . ';';
            $script_content .= 'change_value(value);';
            $script_content .= '$("#basket_button").css("visibility", "visible");';
            $script_content .= 'id_mysql = ' . $id_order . ';';
        $script_content .= '</script>';
    }
            
    require_once 'include/footer.php';
?>