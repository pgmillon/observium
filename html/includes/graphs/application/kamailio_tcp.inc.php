<?php
/*
  DS:tcpconreset:GAUGE:600:0:125000000000 \
  DS:tcpcontimeout:GAUGE:600:0:125000000000 \
  DS:tcpconnectfailed:GAUGE:600:0:125000000000 \
  DS:tcpconnectsuccess:GAUGE:600:0:125000000000 \
  DS:tcpcurrentopenedcon:GAUGE:600:0:125000000000 \
  DS:tcpcurrentwrqsize:GAUGE:600:0:125000000000 \
  DS:tcpestablished:GAUGE:600:0:125000000000 \
  DS:tcplocalreject:GAUGE:600:0:125000000000 \
  DS:tcppassiveopen:GAUGE:600:0:125000000000 \
  DS:tcpsendtimeout:GAUGE:600:0:125000000000 \
  DS:tcpsendqfull:GAUGE:600:0:125000000000 \
*/


include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

$array = array('tcpconreset'          => array('descr' => 'Connection Reset'),
               'tcpcontimeout'        => array('descr' => 'Connection Timeout'),
               'tcpconnectfailed'     => array('descr' => 'Connection Failed'),
               'tcpconnectsuccess'    => array('descr' => 'Connection Success'),
               'tcpcurrentopenedcon'  => array('descr' => 'Open Connections'),
               'tcpcurrentwrqsize'    => array('descr' => 'Write Queue Size'),
               'tcpestablished'       => array('descr' => 'Establiched'),
               'tcplocalreject'       => array('descr' => 'Local Rejected'),
               'tcppassiveopen'       => array('descr' => 'Passiive Open'),
               'tcpsendtimeout'       => array('descr' => 'Send Timeout'),
               'tcpsendqfull'         => array('descr' => 'Send Queue Full'),
              );

$i = 0;
if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $data['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";

include("includes/graphs/generic_multi_line.inc.php");

// EOF