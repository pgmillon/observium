<?php
/*
echo "totalaccesses:$totalaccesses"
echo "totalkbytes:$totalkbytes"
echo "uptime:$uptime"
echo "busyservers:$busyservers"
echo "idleservers:$idleservers"
echo "connectionsp:$connectionsp"
echo "connectionsC:$connectionsC"
echo "connectionsE:$connectionsE"
echo "connectionsk:$connectionsk"
echo "connectionsr:$connectionsr"
echo "connectionsR:$connectionsR"
echo "connectionsW:$connectionsW"
echo "connectionsh:$connectionsh"
echo "connectionsq:$connectionsq"
echo "connectionsQ:$connectionsQ"
echo "connectionss:$connectionss"
echo "connectionsS:$connectionsS"
*/

if (!empty($agent_data['app']['lighttpd']))
{
  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-lighttpd-".$app['app_id'].".rrd";

$data = array(
    'totalaccesses' => 0,
    'totalkbytes' => 0,
    'uptime' => 0,
    'busyservers' => 0,
    'idleservers' => 0,
    'connectionsp' => 0,
    'connectionsC' => 0,
    'connectionsE' => 0,
    'connectionsk' => 0,
    'connectionsr' => 0,
    'connectionsR' => 0,
    'connectionsW' => 0,
    'connectionsh' => 0,
    'connectionsq' => 0,
    'connectionsQ' => 0,
    'connectionss' => 0,
    'connectionsS' => 0,
  );

  $lines = explode("\n", $agent_data['app']['lighttpd']);
  foreach ($lines as $line)
  {
    list($key, $val) = explode(":", $line);
    $data[trim($key)] = intval(trim($val));
  }

  if (!is_file($rrd_filename))
  {
    rrdtool_create($rrd_filename, " \
        DS:totalaccesses:COUNTER:600:0:125000000000 \
        DS:totalkbytes:COUNTER:600:0:125000000000 \
        DS:uptime:GAUGE:600:0:125000000000 \
        DS:busyservers:GAUGE:600:0:125000000000 \
        DS:idleservers:GAUGE:600:0:125000000000 \
        DS:connectionsp:GAUGE:600:0:125000000000 \
        DS:connectionsC:GAUGE:600:0:125000000000 \
        DS:connectionsE:GAUGE:600:0:125000000000 \
        DS:connectionsk:GAUGE:600:0:125000000000 \
        DS:connectionsr:GAUGE:600:0:125000000000 \
        DS:connectionsR:GAUGE:600:0:125000000000 \
        DS:connectionsW:GAUGE:600:0:125000000000 \
        DS:connectionsh:GAUGE:600:0:125000000000 \
        DS:connectionsq:GAUGE:600:0:125000000000 \
        DS:connectionsQ:GAUGE:600:0:125000000000 \
        DS:connectionss:GAUGE:600:0:125000000000 \
        DS:connectionsS:GAUGE:600:0:125000000000 " );
  }

  rrdtool_update($rrd_filename,  "N:$data[totalaccesses]:$data[totalkbytes]:$data[uptime]:$data[busyservers]:$data[idleservers]:$data[connectionsp]:$data[connectionsC]:$data[connectionsE]:$data[connectionsk]:$data[connectionsr]:$data[connectionsR]:$data[connectionsW]:$data[connectionsh]:$data[connectionsq]:$data[connectionsQ]:$data[connectionss]:$data[connectionsS]");

}

?>
