<?php
/*
  DS:registraraccregs:COUNTER:600:0:125000000000 \
  DS:registrardefexpire:GAUGE:600:0:125000000000 \
  DS:registrardefexpirer:GAUGE:600:0:125000000000 \
  DS:registrarmaxcontact:GAUGE:600:0:125000000000 \
  DS:registrarmaxexpires:GAUGE:600:0:125000000000 \
  DS:registrarrejregs:COUNTER:600:0:125000000000 \
*/

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

$array = array('registraraccregs'     => array('descr' => 'Accepted Registrations'),
               'registrarmaxcontact'  => array('descr' => 'Max Contacts'),
               'registrarrejregs'     => array('descr' => 'Rejected Registrations'),
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