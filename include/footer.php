<?php

            //<!-- JS -->
            $content .= '<script src="js/jquery-3.3.1.slim.min.js"></script>';
            $content .= '<script src="js/popper.min.js"></script>';
            $content .= '<script src="js/bootstrap.min.js"></script>';
            $content .= '<script src="js/bootbox.all.min.js"></script>';
            $content .= '<script src="js/bootstrap-input-spinner.js"></script>';

            if(file_exists('js/'.$data.'.js'))
                $content .= '<script src="js/'.$data.'.js"></script>';

            if(isset($script_content))
                $content .= $script_content;
                
        $content .= '</body>';
    $content .= '</html>';

    echo $content;
?>