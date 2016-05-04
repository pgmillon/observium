<?php
/*
  DS:usrlocloccontacts:GAUGE:600:0:125000000000 \
  DS:usrloclocusers:GAUGE:600:0:125000000000 \
  DS:usrlocregusers:GAUGE:600:0:125000000000
*/

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

$array = array('usrlocloccontacts'  => array('descr' => 'Contacts'),
               'usrloclocusers'     => array('descr' => 'Users'),
               'usrlocregusers'     => array('descr' => 'Registered Users'),
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