<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Pagination
echo(pagination($vars, $ports_count));

if ($vars['pageno'])
{
  $ports = array_chunk($ports, $vars['pagesize']);
  $ports = $ports[$vars['pageno']-1];
}
// End Pagination

// Populate ports array (much faster for large systems)
$port_ids = array();
foreach ($ports as $p)
{
  $port_ids[] = $p['port_id'];
}
$where = ' WHERE `ports`.`port_id` IN (' . implode(',', $port_ids) . ') ';

$select = "`ports`.*, `ports-state`.*, `ports`.`port_id` AS `port_id`";
#$select = "*,`ports`.`port_id` as `port_id`";

include($config['html_dir']."/includes/port-sort-select.inc.php");

$sql  = "SELECT ".$select;
$sql .= " FROM `ports`";
$sql .= " INNER JOIN `devices` ON `ports`.`device_id` = `devices`.`device_id`";
$sql .= " LEFT JOIN `ports-state` ON `ports`.`port_id` = `ports-state`.`port_id`";
$sql .= " ".$where;

unset($ports);

$ports = dbFetchRows($sql);

// Re-sort because the DB doesn't do that.
include($config['html_dir']."/includes/port-sort.inc.php");

// End populating ports array

echo '<div class="box box-solid">';
echo('<table class="table table-striped  table-hover table-condensed">');
echo('  <thead>');

echo('<tr class="entity">');
echo('      <th class="state-marker"></th>'.PHP_EOL);
echo('      <th style="width: 1px;"></th>'.PHP_EOL);

$cols = array( array('head' => 'Device',      'sort' => 'device',       'width' => 250),
               array('head' => 'Port',        'sort' => 'port',         'width' => 350),
               array('head' => 'Traffic',     'sort' => 'traffic',      'width' => 100),
               array('head' => 'Traffic %',   'sort' => 'traffic_perc', 'width' => 90),
               array('head' => 'Packets',     'sort' => 'packets',      'width' => 90),
               array('head' => 'Speed',       'sort' => 'speed',        'width' => 90),
               array('head' => 'MAC Address', 'sort' => 'mac',          'width' => 150)
              );

foreach ($cols as $col)
{
  echo('<th');
  if (is_numeric($col['width'])) { echo(' style="width: '.$col['width'].';"'); }
  echo('>');
  if ($vars['sort'] == $col['sort'])
  {
    echo($col['head'].' *');
  } else {
    echo('<a href="'. generate_url($vars, array('sort' => $col['sort'])).'">'.$col['head'].'</a>');
  }
  echo("</th>");
}

echo("      </tr></thead>");

$ports_disabled = 0; $ports_down = 0; $ports_up = 0; $ports_total = 0;
foreach ($ports as $port)
{

  $ports_total++;
  print_port_row($port, $vars);

}

echo('</table>');

echo '</div>';

echo pagination($vars, $ports_count);

// EOF
