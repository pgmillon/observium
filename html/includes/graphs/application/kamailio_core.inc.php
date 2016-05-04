<?php
/*
  DS:corebadURIsrcvd:COUNTER:600:0:125000000000 \
  DS:corebadmsghdr:COUNTER:600:0:125000000000 \
  DS:coredropreplies:COUNTER:600:0:125000000000 \
  DS:coredroprequests:COUNTER:600:0:125000000000 \
  DS:coreerrreplies:COUNTER:600:0:125000000000 \
  DS:coreerrrequests:COUNTER:600:0:125000000000 \
  DS:corefwdreplies:COUNTER:600:0:125000000000 \
  DS:corefwdrequests:COUNTER:600:0:125000000000 \
  DS:corercvreplies:COUNTER:600:0:125000000000 \
  DS:corercvrequests:COUNTER:600:0:125000000000 \
  DS:coreunsupportedmeth:COUNTER:600:0:125000000000 \
*/

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

$array = array('corebadURIsrcvd'      => array('descr' => 'Bad URIs Recieved'),
               'corebadmsghdr'        => array('descr' => 'Bad Msg Header'),
               'coredropreplies'      => array('descr' => 'Dropped Replies'),
               'coredroprequests'     => array('descr' => 'Drop Requests'),
               'coreerrreplies'       => array('descr' => 'Error Replies'),
               'coreerrrequests'      => array('descr' => 'Error Requests'),
               'corefwdreplies'       => array('descr' => 'Forward Replies'),
               'corefwdrequests'      => array('descr' => 'Forward Requests'),
               'corercvrequests'      => array('descr' => 'Recieved Replies'),
               'corercvreplies'       => array('descr' => 'Recieved Requests'),
               'coreunsupportedmeth'  => array('descr' => 'Unsupported Methods'),
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