<?php
/*
  DS:sl400replies:COUNTER:600:0:125000000000 \
  DS:sl401replies:COUNTER:600:0:125000000000 \
  DS:sl403replies:COUNTER:600:0:125000000000 \
  DS:sl404replies:COUNTER:600:0:125000000000 \
  DS:sl407replies:COUNTER:600:0:125000000000 \
  DS:sl408replies:COUNTER:600:0:125000000000 \
  DS:sl483replies:COUNTER:600:0:125000000000 \
  DS:sl4xxreplies:COUNTER:600:0:125000000000 \
*/

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

$array = array('sl400replies'  => array('descr' => '400 Replies'),
               'sl401replies'  => array('descr' => '401 Replies'),
               'sl403replies'  => array('descr' => '403 Replies'),
               'sl404replies'  => array('descr' => '404 Replies'),
               'sl407replies'  => array('descr' => '407 Replies'),
               'sl408replies'  => array('descr' => '408 Replies'),
               'sl483replies'  => array('descr' => '483 Replies'),
               'sl4xxreplies'  => array('descr' => '4XX Replies'),
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