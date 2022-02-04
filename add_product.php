<?php
    $data = 'add_product';
    require_once 'include/header.php';

    $content .= '<div class="container mt-3">';

        if(isset($_POST['submit']))
        {
            $id_product = $_POST['products'];
            $unit = $_POST['unit'];//
            (isset($_POST['quantity'])) ? $quantity = $_POST['quantity'] : $quantity = '';
            $amount = $_POST['amount'];//
            (isset($_POST['desc_short'])) ? $desc_short = $_POST['desc_short'] : $desc_short = '';
            (isset($_POST['desc_long'])) ? $desc_long = $_POST['desc_long'] : $desc_long = '';
            $purchase_price = $_POST['purchase_price'];
            $price = $_POST['price'];//
            $date = $_POST['date'];

            //alert($id_product .' ' . $unit .' ' . $quantity .' ' . $amount .' ' . $desc_short .' ' . $desc_long .' ' . $purchase_price .' ' . $price .' ' . $date);

            if(empty($date)) 
                $date = date('Y.m.d');
            $sql->prepare('INSERT INTO `products_history` (`id_product`, `desc_short`, `desc_long`, `quantity`, `purchase_price`, `date`) VALUES (?, ?, ?, ?, ?, ?)')->execute([$id_product, $desc_short, $desc_long, $quantity, $purchase_price, $date]);
            $id_mysql = $sql->lastInsertId();
            
            if(isset($_POST['add']))
                $sql->prepare('INSERT INTO `products_actual` (`id_product_hist`, `amount`, `unit`, `price`) VALUES (?, ?, ?, ?)')->execute([$id_mysql, $amount, $unit, $price]);

            alert('Dodano produkt pomyślnie!');
        }

        $content .= '<form method="POST">';
            $content .= '<div class="form-group">';

                $content .= '<label for="products">Produkt:</label>';
                $content .= '<select class="custom-select" id="products" name="products" required>';
                    $content .= products();
                $content .= '</select>';
            $content .= '</div>';

            $content .= '<div class="form-group">';
                $content .= '<div class="btn-group btn-group-toggle" data-toggle="buttons">';
                    $content .= '<label class="btn btn-secondary active">';
                        $content .= '<input type="radio" name="unit" id="option1" value="op" autocomplete="off" onchange="change_unit(\'op\');" checked> Opakowania';
                    $content .= '</label>';
                    //$content .= '<label class="btn btn-secondary">';
                        //$content .= '<input type="radio" name="unit" id="option2" value="szt" autocomplete="off" onchange="change_unit(\'szt\');"> Sztuki';
                    //$content .= '</label>';
                    $content .= '<label class="btn btn-secondary">';
                        $content .= '<input type="radio" name="unit" id="option3" value="kg" autocomplete="off" onchange="change_unit(\'kg\');"> Kilogramy';
                    $content .= '</label>';
                $content .= '</div>';
            $content .= '</div>';

            $content .= '<div class="form-group">';
                $content .= '<label for="quantity">Ilość:</label>';
                $content .= '<input type="number" class="form-control" min="0.00" max="10000.00" step="0.01" id="quantity" name="quantity">';
            $content .= '</div>';

            $content .= '<div class="form-group" id="unit_div">';
                $content .= '<label id="unit_value" for="amount">Waga opakowania / ilość sztuk w opakowaniu:</label>';
                $content .= '<input type="number" class="form-control" min="0.00" max="10000.00" step="0.01" id="amount" name="amount" required>';
            $content .= '</div>';

            $content .= '<div class="form-group">';
                $content .= '<label for="desc_short">Opis krótki:</label>';
                $content .= '<input type="text" class="form-control" id="desc_short" name="desc_short">';
            $content .= '</div>';

            $content .= '<div class="form-group">';
                $content .= '<label for="desc_long">Opis długi:</label>';
                $content .= '<input type="text" class="form-control" id="desc_long" name="desc_long">';
            $content .= '</div>';

            $content .= '<div class="form-group">';
                $content .= '<label for="purchase_price">Cena zakupu:</label>';
                $content .= '<input type="number" class="form-control" min="0.00" max="10000.00" step="0.01" id="purchase_price" name="purchase_price" required>';
            $content .= '</div>';

            $content .= '<div class="form-group">';
                $content .= '<label for="price">Cena sprzedaży:</label>';
                $content .= '<input type="number" class="form-control" min="0.00" max="10000.00" step="0.01" id="price" name="price" required>';
            $content .= '</div>';

            $content .= '<div class="form-group">';
                $content .= '<label for="date">Data:</label>';
                $content .= '<input type="date" class="form-control" id="date" name="date" value="0">';
            $content .= '</div>';

            $content .= '<div class="form-check">';
                $content .= '<input class="form-check-input" type="checkbox" value="" name="add" id="add" checked>';
                $content .= '<label for="add" class="form-check-label">Dodaj do stanu</label>';
            $content .= '</div>';
            
            $content .= '<button type="submit" class="btn btn-primary" name="submit">Dodaj</button>';
        $content .= '</form>';

    $content .= '</div>';
    require_once 'include/footer.php';