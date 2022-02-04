<?php
    require_once 'config.php';

    $content = '<!DOCTYPE html>';
    $content .= '<html lang="pl">';
        $content .= '<head>';
            //<!-- Required meta tags -->
            $content .= '<meta charset="utf-8">';
            $content .= '<meta name="author" content="biongoo">';
            $content .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
            $content .= '<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>';

            //<!-- CSS -->
            $content .= '<link rel="stylesheet" href="css/all.css">';
            $content .= '<link rel="stylesheet" href="css/bootstrap.min.css">';
            $content .= '<link rel="stylesheet" href="css/'.$data.'.css">';
            
            //<!-- Title -->
            $content .= '<title>System zamówień</title>';

        $content .= '</head>';
        $content .= '<body>';

            $content .= '<header>';
                $content .= '<nav class="navbar navbar-expand-lg navbar-dark bg-dark">';
                    $content .= '<a class="navbar-brand" href="'.$name.'">System zamówień</a>';
                    $content .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
                        $content .= '<span class="navbar-toggler-icon"></span>';
                    $content .= '</button>';
                    
                    $content .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
                        $content .= '<ul class="navbar-nav mr-auto">';

                            $content .= '<li class="nav-item dropdown">';
                                $content .= '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Zamówienia</a>';
                                $content .= '<div class="dropdown-menu" aria-labelledby="navbarDropdown">';
                                    $content .= '<a class="dropdown-item" href="'.$name.'main.php?client">Nowa</a>';
                                    $content .= '<div class="dropdown-divider"></div>';
                                    $content .= '<a class="dropdown-item" href="'.$name.'actual_orders.php">Aktualne zamówienia</a>';
                                    $content .= '<div class="dropdown-divider"></div>';
                                    $content .= '<a class="dropdown-item" href="'.$name.'history.php">Historia</a>';
                                $content .= '</div>';
                            $content .= '</li>';

                            $content .= '<li class="nav-item dropdown">';
                                $content .= '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Produkty</a>';
                                $content .= '<div class="dropdown-menu" aria-labelledby="navbarDropdown">';
                                    $content .= '<a class="dropdown-item" href="'.$name.'main.php">Stan</a>';
                                    $content .= '<div class="dropdown-divider"></div>';
                                    $content .= '<a class="dropdown-item" href="'.$name.'add_product.php">Dodaj aktualny</a>';
                                    $content .= '<div class="dropdown-divider"></div>';
                                    $content .= '<a class="dropdown-item" href="'.$name.'products.php">Produkty Główne</a>';
                                $content .= '</div>';
                            $content .= '</li>';

                            $content .= '<li class="nav-item">';
                                $content .= '<a class="nav-link" href="'.$name.'transfers.php">Przelewy</a>';
                            $content .= '</li>';

                            $content .= '<li class="nav-item">';
                                $content .= '<a class="nav-link" href="'.$name.'clients.php">Klienci</a>';
                            $content .= '</li>';

                        $content .= '</ul>';
                    $content .= '</div>';
                $content .= '</nav>';
            $content .= '</header>';

            if(isset($_SESSION['msg']))
            {
                $content .= '<div class="alert alert-primary" role="alert">'.$_SESSION['msg'].'</div>';
                unset($_SESSION['msg']);
            }
?>