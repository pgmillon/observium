<?php
/*
  DS:sl1xxreplies:COUNTER:600:0:125000000000 \
  DS:sl200replies:COUNTER:600:0:125000000000 \
  DS:sl202replies:COUNTER:600:0:125000000000 \
  DS:sl2xxreplies:COUNTER:600:0:125000000000 \
  DS:sl300replies:COUNTER:600:0:125000000000 \
  DS:sl301replies:COUNTER:600:0:125000000000 \
  DS:sl302replies:COUNTER:600:0:125000000000 \
  DS:sl3xxreplies:COUNTER:600:0:125000000000 \
*/

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

$array = array('sl1xxreplies'  => array('descr' => '1XX Replies'),
               'sl200replies'  => array('descr' => '200 Replies'),
               'sl202replies'  => array('descr' => '202 Replies'),
               'sl2xxreplies'  => array('descr' => '2XX Replies'),
               'sl300replies'  => array('descr' => '300 Replies'),
               'sl301replies'  => array('descr' => '301 Replies'),
               'sl302replies'  => array('descr' => '302 Replies'),
               'sl3xxreplies'  => array('descr' => '3XX Replies'),
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