<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage update
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo(" Updating netscaler service RRDs\n");

//foreach (dbFetchRows("SELECT * FROM `netscaler_services`") as $svc)
if (FALSE)
{

  $device = device_by_id_cache($svc['device_id']);
  $filename = "netscaler-svc-".safename($svc['svc_name']).".rrd";

  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/netscaler-svc-".safename($svc['svc_name']).".rrd";
  $rrd_file_new = $config['rrd_dir'] . "/" . $device['hostname'] . "/nscaler-svc-".safename($svc['svc_name']).".rrd";

  echo(str_pad($device['hostname'], 16) . str_pad($svc['svc_label'], 32) . $rrd_file);

  $old_xml = $config['temp_dir']."/".$filename.".xml";
  $new_xml = $config['temp_dir']."/".$filename.".new.xml";

  if (is_file($rrd_file))
  {
    shell_exec($config['install_dir'] . "/scripts/add_ds.pl --source=\"$rrd_file\" --ds=\"DS:AvgSvrTTFB:GAUGE:600:U:1000000\"");
    shell_exec($config['install_dir'] . "/scripts/add_ds.pl --source=\"$rrd_file\" --ds=\"DS:CurClntConnections:GAUGE:600:U:1000000\"");
    shell_exec($config['install_dir'] . "/scripts/add_ds.pl --source=\"$rrd_file\" --ds=\"DS:totalJsTransactions:GAUGE:600:U:1000000\"");
    shell_exec($config['install_dir'] . "/scripts/add_ds.pl --source=\"$rrd_file\" --ds=\"DS:svcdosQDepth:GAUGE:600:U:1000000\"");

    rename($rrd_file, $rrd_file_new);
  }

  echo("\n");
}

// EOF
