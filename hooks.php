<?php

//TODO: on admin password change update in tera.
//http://docs.whmcs.com/Hooks:AdminClientServicesTabFieldsSave

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once("class.api.php");

function ssd100tb_deleteSsdVps($vars) {

    $result = select_query("tblhosting","",array(
        "id" => $vars['id']
    ));

    if (($result && mysql_num_rows($result) > 0)) {
        $params = mysql_fetch_array($result);

        $result = select_query("tblproducts","",array(
            "id" => $params['packageid']
        ));

        if (($result && mysql_num_rows($result) > 0)) {
            $config = mysql_fetch_array($result);

            $result = full_query("
                SELECT  `tblcustomfieldsvalues`.`value` AS 'id'
                FROM  `tblcustomfieldsvalues`
                LEFT OUTER JOIN  `tblcustomfields` ON  `tblcustomfieldsvalues`.`fieldid` =  `tblcustomfields`.`id`
                WHERE  `tblcustomfieldsvalues`.`relid` = {$vars['id']}
                LIMIT 1
            ");

            $vps = mysql_fetch_array($result);

            try {
                $API = new API($config['configoption4']);

                $response = $API->delete("/vps.json/servers/{$vps['id']}");

                if ($response != true) {
                    throw new Exception('Failed to delete SSD VPS server.');
                }
            } catch (Exception $e) {
                // Record the error in WHMCS's module log.
                logModuleCall(
                    'ssd100tb',
                    __FUNCTION__,
                    $params,
                    //$e->getTraceAsString()
                    $e->getMessage()
                );

                return $e->getMessage();
            }
        }
    }
}

add_hook("ServiceDelete",1,"ssd100tb_deleteSsdVps");