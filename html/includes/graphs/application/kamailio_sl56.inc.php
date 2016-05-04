<?php
/*
  DS:sl500replies:COUNTER:600:0:125000000000 \
  DS:sl5xxreplies:COUNTER:600:0:125000000000 \
  DS:sl6xxreplies:COUNTER:600:0:125000000000 \
  DS:slreceivedACKs:COUNTER:600:0:125000000000 \
  DS:slsenterrreplies:COUNTER:600:0:125000000000 \
  DS:slsentreplies:COUNTER:600:0:125000000000 \
  DS:slxxxreplies:COUNTER:600:0:125000000000 \
*/

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

$array = array('sl500replies'      => array('descr' => '500 Replies'),
               'sl5xxreplies'      => array('descr' => '5XX Replies'),
               'sl6xxreplies'      => array('descr' => '6XX Replies'),
               'slxxxreplies'      => array('descr' => 'XXX Replies'),
               'slreceivedACKs'    => array('descr' => 'Received Acks'),
               'slsenterrreplies'  => array('descr' => 'Sent Error Replies'),
               'slsentreplies'     => array('descr' => 'Sent Replies'),
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