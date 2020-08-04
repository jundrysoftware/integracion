<?php
require_once 'models/conection.php';
class Recaudo extends DB{

    function obtenerRecaudo(){
        $query = $this->connect()->query("
        SELECT tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion,
        sum(valpagoconc)
        FROM (

        ------------------------------ACUEDUCTO-----------------------------------------
        ----CF ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago, pt.cod_peri, 1 as cod_conc,'Acueducto CF' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc --valor pagado x concepto
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTRIBUCION CF ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 102 as cod_conc,'Acueducto CF CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END ) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 103 as cod_conc,'Acueducto CONSUMO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (case when (coalesce(fd.subapo_cons,0))<0 then 0 else coalesce((fd.subapo_cons*-1),0) end))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 105 as cod_conc,'Acueducto CONSUMO CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----TASA CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 106 as cod_conc,'Acueducto TASA USO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (case when (coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))<0
        then 0 else ((coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))*-1) END)
        )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR TASA CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 108 as cod_conc,'Acueducto CONTR TASA USO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0)
        ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ------------------------------ALCANTARILLADO------------------------------------
        ----CF ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 2 as cod_conc,'Alcantarillado CF' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc --valor pagado x concepto
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTRIBUCION CF ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 202 as cod_conc,'Alcantarillado CF CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END ) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 203 as cod_conc,'Alcantarillado VERTIMIENTO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (case when (coalesce(fd.subapo_cons,0))<0 then 0 else coalesce((fd.subapo_cons*-1),0) end))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 205 as cod_conc,'Alcantarillado VERTIMIENTO CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----TASA VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 206 as cod_conc,'Alcantarillado TASA RETRIBUTIVA' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (case when (coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))<0
        then 0 else ((coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))*-1) END)
        )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR TASA VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 208 as cod_conc,'Alcantarillado CONTR TASA RETRIBUTIVA' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0)
        ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----OTROS CONCEPTOS----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, pt.cod_conc,ct.descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (1,2,99)
        and cod_conc not in (1,2,7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,ct.descripcion, pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----AJUSTE A LA DECENA - ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 771 as cod_conc,'Ajuste a la decena ACUEDUCTO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM(coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8)))/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8)))
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----AJUSTE A LA DECENA - ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 772 as cod_conc,'Ajuste a la decena ALCANTARILLADO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM(coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8)))/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8)))
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('23/05/2020','dd/mm/yyyy') and to_date('30/06/2020','dd/mm/yyyy') and
        pt.fecha_regi > to_date('01/06/2020','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('30/06/2020','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------


        ) as tbl
        group by tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion
        order by tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion");
        return $query;
    }

    function obtenerRecaudoByRango($pagofechain,$pagofechaout,$regfechain,$regfechaout){
        $query = $this->connect()->query("
        SELECT tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion,
        sum(valpagoconc)
        FROM (
        ------------------------------ACUEDUCTO-----------------------------------------
        ----CF ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago, pt.cod_peri, 1 as cod_conc,'Acueducto CF' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc --valor pagado x concepto
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTRIBUCION CF ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 102 as cod_conc,'Acueducto CF CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END ) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 103 as cod_conc,'Acueducto CONSUMO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (case when (coalesce(fd.subapo_cons,0))<0 then 0 else coalesce((fd.subapo_cons*-1),0) end))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 105 as cod_conc,'Acueducto CONSUMO CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----TASA CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 106 as cod_conc,'Acueducto TASA USO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (case when (coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))<0
        then 0 else ((coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))*-1) END)
        )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR TASA CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 108 as cod_conc,'Acueducto CONTR TASA USO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0)
        ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ------------------------------ALCANTARILLADO------------------------------------
        ----CF ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 2 as cod_conc,'Alcantarillado CF' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc --valor pagado x concepto
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTRIBUCION CF ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 202 as cod_conc,'Alcantarillado CF CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END ) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 203 as cod_conc,'Alcantarillado VERTIMIENTO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (case when (coalesce(fd.subapo_cons,0))<0 then 0 else coalesce((fd.subapo_cons*-1),0) end))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 205 as cod_conc,'Alcantarillado VERTIMIENTO CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----TASA VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 206 as cod_conc,'Alcantarillado TASA RETRIBUTIVA' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (case when (coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))<0
        then 0 else ((coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))*-1) END)
        )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR TASA VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 208 as cod_conc,'Alcantarillado CONTR TASA RETRIBUTIVA' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0)
        ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----OTROS CONCEPTOS----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, pt.cod_conc,ct.descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (1,2,99)
        and cod_conc not in (1,2,7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,ct.descripcion, pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----AJUSTE A LA DECENA - ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 771 as cod_conc,'Ajuste a la decena ACUEDUCTO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM(coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8)))/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8)))
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----AJUSTE A LA DECENA - ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 772 as cod_conc,'Ajuste a la decena ALCANTARILLADO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM(coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8)))/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8)))
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------


        ) as tbl
        group by tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion
        order by tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion

        SELECT tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion,
        sum(valpagoconc)
        FROM (

        ------------------------------ACUEDUCTO-----------------------------------------
        ----CF ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago, pt.cod_peri, 1 as cod_conc,'Acueducto CF' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc --valor pagado x concepto
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTRIBUCION CF ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 102 as cod_conc,'Acueducto CF CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END ) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 103 as cod_conc,'Acueducto CONSUMO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (case when (coalesce(fd.subapo_cons,0))<0 then 0 else coalesce((fd.subapo_cons*-1),0) end))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 105 as cod_conc,'Acueducto CONSUMO CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----TASA CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 106 as cod_conc,'Acueducto TASA USO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (case when (coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))<0
        then 0 else ((coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))*-1) END)
        )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR TASA CONSUMO ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 108 as cod_conc,'Acueducto CONTR TASA USO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0)
        ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=1)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=1
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ------------------------------ALCANTARILLADO------------------------------------
        ----CF ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 2 as cod_conc,'Alcantarillado CF' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum(coalesce(fd.cargofijo,0)+coalesce((fd.subapo_cfijo*-1),0)) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc --valor pagado x concepto
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTRIBUCION CF ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 202 as cod_conc,'Alcantarillado CF CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cfijo,0) > 0 THEN
        coalesce(fd.subapo_cfijo,0) ELSE 0 END ) from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 203 as cod_conc,'Alcantarillado VERTIMIENTO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.canti_e1,0)*coalesce(fd.precioe1,0))+
        (coalesce(fd.canti_e2,0)*coalesce(fd.precio_e2,0))+
        (coalesce(fd.canti_e3,0)*coalesce(fd.precioe3,0))+
        (case when (coalesce(fd.subapo_cons,0))<0 then 0 else coalesce((fd.subapo_cons*-1),0) end))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 205 as cod_conc,'Alcantarillado VERTIMIENTO CONTR' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN coalesce(fd.subapo_cons,0) > 0 THEN
        coalesce(fd.subapo_cons,0) ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----TASA VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 206 as cod_conc,'Alcantarillado TASA RETRIBUTIVA' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select sum((coalesce(fd.tasa_ur_r1,0)*coalesce(fd.canti_e1,0))+
        (coalesce(fd.tasa_ur_r2,0)*coalesce(fd.canti_e2,0))+
        (coalesce(fd.tasa_ur_r3,0)*coalesce(fd.canti_e3,0))+
        (case when (coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))<0
        then 0 else ((coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0))*-1) END)
        )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----CONTR TASA VERTIMIENTO ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 208 as cod_conc,'Alcantarillado CONTR TASA RETRIBUTIVA' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM( CASE WHEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0) > 0 THEN
        coalesce(fd.subapo_tasa_r1,0)+coalesce(fd.subapo_tasa_r2,0)+coalesce(fd.subapo_tasa_r3,0)
        ELSE 0 END )
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc=2)
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc=2
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----OTROS CONCEPTOS----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, pt.cod_conc,ct.descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (1,2,99)
        and cod_conc not in (1,2,7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,ct.descripcion, pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----AJUSTE A LA DECENA - ACUEDUCTO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 771 as cod_conc,'Ajuste a la decena ACUEDUCTO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM(coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8)))/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8)))
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (1,99)
        and cod_conc not in (7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------
        UNION
        ----AJUSTE A LA DECENA - ALCANTARILLADO----
        (select pt.cod_pred,pt.fecha_pago,pt.cod_peri, 772 as cod_conc,'Ajuste a la decena ALCANTARILLADO' as descripcion,
        (select nro_cuenta from pago_entidad pe where cod_pent=pc.cod_epgo and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) as cuenta,
        --inicia el porcentaje
        ((select SUM(coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8)))/
        (select sum(coalesce(fd.total_concp,0)+coalesce(fd.vad_conc,0))
        from factura_det fd
        where fd.cod_pred=pt.cod_pred and fd.cod_peri=pt.cod_peri and fd.cod_munip=pt.cod_munip
        and fd.cod_empr=pt.cod_empr and fd.nro_seq=(select max(nro_seq)
        from factura_cab where cod_pred=pt.cod_pred and cod_peri=pt.cod_peri and cod_munip=pt.cod_munip
        and cod_empr=pt.cod_empr) and fd.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8)))
        )
        --hasta aqui es el porcentaje
        *
        sum(coalesce(pt.valor,0)+coalesce(pt.vad_conc,0)) as valpagoconc
        from pago_cab pc,
        pago_concepto pt, concepto ct
        where pc.cod_pago=pt.cod_pago and pc.cod_empr=pt.cod_empr and pc.cod_munip=pt.cod_munip and
        pt.cod_conc=ct.cod_conc and pt.cod_empr=ct.cod_empr
        --pt.fecha_regi
        and pt.fecha_pago
        between to_date('$pagofechain','dd/mm/yyyy') and to_date('$pagofechaout','dd/mm/yyyy') and
        pt.fecha_regi > to_date('$regfechain','dd/mm/yyyy') and
        (pt.fecha_anul is null or pt.fecha_anul>to_date('$regfechaout','dd/mm/yyyy'))
        and ((pc.cod_epgo,pc.cod_spgo,pc.cod_scja) != ('53','5','5'))
        and pt.cod_conc in (select cod_conc from concepto where cod_serv in (2)
        and cod_conc not in (7,8))
        and pt.cod_peri>202005 and ct.cod_serv!='3'
        group by pt.cod_pred,pt.fecha_pago,pc.cod_epgo,pt.cod_peri, pt.cod_conc,pt.cod_munip,pt.cod_empr)
        --------------------------------------------------------------------------------


        ) as tbl
        group by tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion
        order by tbl.cod_conc,tbl.fecha_pago,tbl.cuenta,tbl.descripcion
            ");
        return $query;
    }

}
