<?php
require_once 'controller/FacturasController.php';
require_once 'controller/RecaudoController.php';
class Api{
    //FACTURAS API CONTENT
    function getFacturasByPeriodo($periodo){
        $facturas = new Facturas();
        $factura = array();
        $factura["facturacion"] = array();
        $res = $facturas->obtenerFacturasByPeriodo($periodo);
        if($res->rowCount()){
            while ($row = $res->fetch(PDO::FETCH_ASSOC)){
                $item=array(
                    "cod_conc" => $row['cod_conc'],
                    "descripcion" => $row['descripcion'],
                    "suma" => $row['suma']
                );
                array_push($factura["facturacion"], $item);
            }
            $this->printJSON($factura);
        }else{
            echo json_encode(array('mensaje' => 'No hay elementos'));
        }
    }
    
    function getFacturasByRango($in, $out){
        $facturas = new Facturas();
        $factura = array();
        $factura["facturacion"] = array();
        $res = $facturas->obtenerFacturasByRangoPeriodo($in,$out);
        if($res->rowCount()){
            while ($row = $res->fetch(PDO::FETCH_ASSOC)){
                $item=array(
                    "cod_conc" => $row['cod_conc'],
                    "descripcion" => $row['descripcion'],
                    "suma" => $row['suma']
                );
                array_push($factura["facturacion"], $item);
            }
            $this->printJSON($factura);
        }else{
            echo json_encode(array('mensaje' => 'No hay elementos'));
        }
    }


    function getRecaudoByRango($pin, $pout, $rin, $rout){
        $recaudos = new Recaudo();
        $recaudo = array();
        $recaudo["recaudos"] = array();
        $res = $recaudos->obtenerRecaudoByRango($pin, $pout, $rin, $rout);
        if($res->rowCount()){
            while ($row = $res->fetch(PDO::FETCH_ASSOC)){
                $item=array(
                    "cod_conc" => $row['cod_conc'],
                    "fecha_pago" => $row['fecha_pago'],
                    "cuenta" => $row['cuenta'],
                    "descripcion" => $row['descripcion'],
                    "sum" => $row['sum']
                );
                array_push($recaudo["recaudos"], $item);
            }
            $this->printJSON($recaudo);
        }else{
            echo json_encode(array('mensaje' => 'No hay elementos'));
        }
    }

    function getAllRecaudo(){
        $recaudos = new Recaudo();
        $recaudo = array();
        $recaudo["recaudos"] = array();
        $res = $recaudos->obtenerRecaudo();
        if($res->rowCount()){
            while ($row = $res->fetch(PDO::FETCH_ASSOC)){
                $item=array(
                    "cod_conc" => $row['cod_conc'],
                    "fecha_pago" => $row['fecha_pago'],
                    "cuenta" => $row['cuenta'],
                    "descripcion" => $row['descripcion'],
                    "sum" => $row['sum']
                );
                array_push($recaudo["recaudos"], $item);
            }
            $this->printJSON($recaudo);
        }else{
            echo json_encode(array('mensaje' => 'No hay elementos'));
        }
    }
    //FIN FACTURAS API CONTENT

    function error($mensaje){
        echo json_encode(array('mensaje' => $mensaje)); 
    }

    function printJSON($array){
        echo json_encode($array);
    }
}

?>