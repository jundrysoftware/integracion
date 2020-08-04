<?php
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    include_once 'Api.php';
    $api = new Api();

    if(isset($_POST['facturacion'])){
        if(isset($_POST['rango'])){
            $in = $_POST['periodoIn'];
            $out = $_POST['periodoOut'];
            $api->getFacturasByRango($in, $out);
        }else{
            $periodo = $_POST['periodo'];
            $api->getFacturasByPeriodo($periodo);
        }  
    }


    if(isset($_POST['recaudo'])){
        $pin = $_POST['pfechaIn'];
        $pout = $_POST['pfechaOut'];
        $rin = $_POST['rfechaIn'];
        $rout = $_POST['rfechaOut'];
        $api->getRecaudoByRango($pin, $pout, $rin, $rout);
    }

    if(isset($_POST['recaudos'])){
        $api->getAllRecaudo();
    }
?>