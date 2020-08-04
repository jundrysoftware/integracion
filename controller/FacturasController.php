<?php
require_once 'models/conection.php';
class Facturas extends DB{
    function obtenerFacturasByPeriodo($periodo){
        $query = $this->connect()->query("
        --FACTURACION CONCEPTOS-- FINANCIERA
        --ACUEDUCTO--
        SELECT * from (
        (select 1 as cod_conc,'Acueducto CF' as descripcion, sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) as suma
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1) 
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 101 as cod_conc,'Acueducto CF SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) < 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 102 as cod_conc,'Acueducto CF CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 103 as cod_conc,'Acueducto CONSUMO' as descripcion, sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (coalesce((fd.subapo_cons*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 104 as cod_conc,'Acueducto CONSUMO SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) < 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 105 as cod_conc,'Acueducto CONSUMO CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 106 as cod_conc,'Acueducto TASA USO' as descripcion, 
        sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (coalesce((fd.subapo_tasa_r1*-1),0)+coalesce((fd.subapo_tasa_r2*-1),0)+coalesce((fd.subapo_tasa_r3*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 107 as cod_conc,'Acueducto SUBS TASA USO' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) < 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 108 as cod_conc,'Acueducto CONTR TASA USO' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        --ALCANTARILLADO--
        (select 2 as cod_conc,'Alcantarillado CF' as descripcion, sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 201 as cod_conc,'Alcantarillado CF SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) < 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 202 as cod_conc,'Alcantarillado CF CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 203 as cod_conc,'Alcantarillado VERTIMIENTO' as descripcion, sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (coalesce((fd.subapo_cons*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 204 as cod_conc,'Alcantarillado VERTIMIENTO SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) < 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 205 as cod_conc,'Alcantarillado VERTIMIENTO CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 206 as cod_conc,'Alcantarillado TASA RETRIBUTIVA' as descripcion, 
        sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (coalesce((fd.subapo_tasa_r1*-1),0)+coalesce((fd.subapo_tasa_r2*-1),0)+coalesce((fd.subapo_tasa_r3*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 207 as cod_conc,'Alcantarillado SUBS TASA RETRIBUTIVA' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) < 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 208 as cod_conc,'Alcantarillado CONTR TASA RETRIBUTIVA' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select fd.cod_conc,ct.descripcion, sum(coalesce(fd.total_concp,0)) 
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,2,99)
        and cod_conc not in (1,2,7,8))
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 771,'Ajuste a la decena ACUEDUCTO', sum(coalesce(fd.vad_conc,0)) 
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8))
        --group by fd.cod_conc,ct.descripcion
        --order by fd.cod_conc
        )
        UNION
        (select 772,'Ajuste a la decena ALCANTARILLADO', sum(coalesce(fd.vad_conc,0)) 
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri='$periodo' and fc.nro_seq=1
        and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8))
        --group by fd.cod_conc,ct.descripcion
        --order by fd.cod_conc
        )
        ) tbl
        order by 1,2
        ---------HASTA AQUI------------");
        return $query;
    }


    function obtenerFacturasByRangoPeriodo($periodo1,$periodo2){
        $query = $this->connect()->query("
        --FACTURACION CONCEPTOS-- FINANCIERA
        --ACUEDUCTO--
        SELECT * from (
        (select 1 as cod_conc,'Acueducto CF' as descripcion, sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) as suma
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1) 
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 101 as cod_conc,'Acueducto CF SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) < 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 102 as cod_conc,'Acueducto CF CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 103 as cod_conc,'Acueducto CONSUMO' as descripcion, sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (coalesce((fd.subapo_cons*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 104 as cod_conc,'Acueducto CONSUMO SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) < 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 105 as cod_conc,'Acueducto CONSUMO CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 106 as cod_conc,'Acueducto TASA USO' as descripcion, 
        sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (coalesce((fd.subapo_tasa_r1*-1),0)+coalesce((fd.subapo_tasa_r2*-1),0)+coalesce((fd.subapo_tasa_r3*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 107 as cod_conc,'Acueducto SUBS TASA USO' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) < 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 108 as cod_conc,'Acueducto CONTR TASA USO' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (1)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        --ALCANTARILLADO--
        (select 2 as cod_conc,'Alcantarillado CF' as descripcion, sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 201 as cod_conc,'Alcantarillado CF SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) < 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 202 as cod_conc,'Alcantarillado CF CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 203 as cod_conc,'Alcantarillado VERTIMIENTO' as descripcion, sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (coalesce((fd.subapo_cons*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION 
        (select 204 as cod_conc,'Alcantarillado VERTIMIENTO SUBS' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) < 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 205 as cod_conc,'Alcantarillado VERTIMIENTO CONTR' as descripcion, 
        SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 206 as cod_conc,'Alcantarillado TASA RETRIBUTIVA' as descripcion, 
        sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (coalesce((fd.subapo_tasa_r1*-1),0)+coalesce((fd.subapo_tasa_r2*-1),0)+coalesce((fd.subapo_tasa_r3*-1),0)))
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 207 as cod_conc,'Alcantarillado SUBS TASA RETRIBUTIVA' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) < 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 208 as cod_conc,'Alcantarillado CONTR TASA RETRIBUTIVA' as descripcion, 
        SUM( CASE WHEN 
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) 
        ELSE 0 END )
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (2)
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select fd.cod_conc,ct.descripcion, sum(coalesce(fd.total_concp,0)) 
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,2,99)
        and cod_conc not in (1,2,7,8))
        group by fd.cod_conc,ct.descripcion
        order by fd.cod_conc)
        UNION
        (select 771,'Ajuste a la decena ACUEDUCTO', sum(coalesce(fd.vad_conc,0)) 
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8))
        --group by fd.cod_conc,ct.descripcion
        --order by fd.cod_conc
        )
        UNION
        (select 772,'Ajuste a la decena ALCANTARILLADO', sum(coalesce(fd.vad_conc,0)) 
        from factura_cab fc, factura_det fd, concepto ct
        where fc.cod_pred=fd.cod_pred and fc.cod_peri=fd.cod_peri
        and fc.cod_munip=fd.cod_munip and fc.cod_empr=fd.cod_empr
        and fc.nro_seq=fd.nro_seq
        and fd.cod_conc=ct.cod_conc and fd.cod_empr=ct.cod_empr
        and fc.cod_peri BETWEEN '$periodo1' and '$periodo2' and fc.nro_seq=1
        and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8))
        --group by fd.cod_conc,ct.descripcion
        --order by fd.cod_conc
        )
        ) tbl
        order by 1,2
        ---------HASTA AQUI------------");
        return $query;
    }

}
