<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if ($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg = "#ffffff"; }

if (!is_array($vars['type'])) { $vars['type'] = array($vars['type']); }

$where = 'WHERE 1';
$where .= generate_query_values($vars['type'], 'port_descr_type', 'LIKE');
$where .= generate_query_permitted(array('port'));

$ports = dbFetchRows("SELECT * FROM `ports` AS I, `devices` AS D $where AND I.`device_id` = D.`device_id` ORDER BY I.`ifAlias`");

$if_list = array();
foreach ($ports as $port)
{
  $if_list[] = $port['port_id'];
}
$if_list = implode(',', $if_list);

for ($i = 0; $i < count($vars['type']);$i++) { $vars['type'][$i] = nicecase($vars['type'][$i]); }
$types = implode(' + ', $vars['type']);

echo('<h4>Total Graph for ports of type : '.$types.'</h4>');

if ($if_list)
{
  $graph_type = "multiport_bits_separate";
  $port['port_id'] = $if_list;

  include("includes/print-interface-graphs.inc.php");

?>

<table class="table table-hover table-striped-two table-bordered table-condensed table-rounded" style="margin-top: 10px;">
  <thead>
      <tr>
        <th style="width: 250px;"><span style="font-weight: bold;" class="interface">Description</span></th>
        <th style="width: 150px;">Device</th>
        <th style="width: 100px;">Interface</th>
        <th style="width: 100px;">Speed</th>
        <th style="width: 100px;">Circuit</th>
        <th>Notes</th>
      </tr>
  </thead>

<?php

  foreach ($ports as $port)
  {
    $done = "yes";
    unset($class);
    $port['ifAlias'] = str_ireplace($type . ": ", "", $port['ifAlias']);
    $port['ifAlias'] = str_ireplace("[PNI]", "Private", $port['ifAlias']);
    $ifclass = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);
    if ($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg = "#ffffff"; }
    echo("<tr class='iftype'>
             <td><span class=entity-title>" . generate_port_link($port,$port['port_descr_descr']) . "</span>");
#            <span class=small style='float: left;'>".generate_device_link($port)." ".generate_port_link($port)." </span>");

    if (dbFetchCell("SELECT count(*) FROM mac_accounting WHERE port_id = ?", array($port['port_id'])))
    {
      echo("<span style='float: right;'><a href='device/device=".$port['device_id']."/tab=port/port=".$port['port_id']."/view=macaccounting/'><img src='images/16/chart_curve.png' align='absmiddle'> MAC Accounting</a></span>");
    }

    echo("</td>");

    echo('   <td style="width: 150px;" class="strong">' . generate_device_link($port) . '</td>
             <td style="width: 150px;" class="strong">' . generate_port_link($port, short_ifname($port['ifDescr'])) . '</td>
             <td style="width: 75px;">'.$port['port_descr_speed'].'</td>
             <td style="width: 150px;">'.$port['port_descr_circuit'].'</td>
             <td>'.$port['port_descr_notes'].'</td>');

    echo('</tr><tr><td colspan="6">');

    $rrdfile = get_port_rrdfilename($port, NULL, TRUE);
    if (file_exists($rrdfile))
    {
      $graph_type = "port_bits";

      include("includes/print-interface-graphs.inc.php");
    }

    echo("</td></tr>");
  }

}
else
{
  echo("None found.</td></tr>");
}

?>
</table>
<?php

// EOF
