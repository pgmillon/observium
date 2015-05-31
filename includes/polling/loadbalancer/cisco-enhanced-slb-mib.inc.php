<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo(" CISCO-ENHANCED-SLB-MIB ");

$rserver_array = snmpwalk_cache_oid($device, "cesServerFarmRserverTable", array(), "CISCO-ENHANCED-SLB-MIB");
$rserver_db = dbFetchRows("SELECT * FROM `loadbalancer_rservers` WHERE `device_id` = ?", array($device['device_id']));

foreach ($rserver_db as $serverfarm) { $serverfarms[$serverfarm['farm_id']] = $serverfarm; }

foreach ($rserver_array as $index => $serverfarm)
{
  $clean_index = preg_replace('@\d+\."(.*?)"\.\d+@', '\\1', $index);

  $oids = array (
        "cesServerFarmRserverTotalConns",
        "cesServerFarmRserverCurrentConns",
        "cesServerFarmRserverFailedConns");

  $db_oids = array($clean_index => 'farm_id', "cesServerFarmRserverStateDescr" => "StateDescr");

  if (!is_array( $serverfarms[$clean_index]))
  {
    $rserver_id = dbInsert(array('device_id' => $device['device_id'], 'farm_id' => $clean_index, 'StateDescr' => $serverfarm['cesServerFarmRserverStateDescr']), 'loadbalancer_rservers');
   } else {
     foreach ($db_oids as $db_oid => $db_value)
     {
       $db_update[$db_value] = $serverfarm[$db_oid];
     }

    $updated = dbUpdate($db_update, 'loadbalancer_rservers', '`rserver_id` = ?', $serverfarm['cesServerFarmRserverFailedConns']['farm_id']);
  }

  echo(".");

  $rrd_file = "rserver-".$serverfarms[$clean_index]['rserver_id'].".rrd";

  foreach ($oids as $oid)
  {
    $oid_ds = truncate(str_replace("cesServerFarm", "", $oid), 19, '');
    $rrd_create .= " DS:$oid_ds:GAUGE:600:-1:100000000";
  }

  $rrdupdate = "N";

  foreach ($oids as $oid)
  {
    if (is_numeric($serverfarm[$oid]))
    {
      $value = $serverfarm[$oid];
    } else {
      $value = "0";
    }
    $rrdupdate .= ":$value";
  }

  if (isset($serverfarms[$clean_index]))
  {
    rrdtool_create($device, $rrd_file, $rrd_create);
    rrdtool_update($device, $rrd_file, $rrdupdate);
  }

  unset($rrd_create);
}

unset($oids, $oid, $serverfarm);

// EOF
