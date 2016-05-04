<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$page_title[] = 'Deleted ports';

if ($vars['purge'] == 'all')
{
  foreach (dbFetchRows('SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = "1" AND D.device_id = P.device_id') as $port)
  {
    if (port_permitted($port['port_id'], $port['device_id']))
    {
      print_message(delete_port($port['port_id']), 'console');
    }
  }
}
else if (is_numeric($vars['purge']))
{
  $port = dbFetchRow('SELECT * from `ports` AS P, `devices` AS D WHERE `port_id` = ? AND D.device_id = P.device_id', array($vars['purge']));
  if ($port && port_permitted($port['port_id'], $port['device_id']))
  {
    print_message(delete_port($port['port_id']), 'console');
  }
}

echo('<table class="table table-condensed table-striped  table-condensed">
  <thead><tr>
    <th>Device</th>
    <th>Port</th>
    <th>Description</th>
    <th>Deleted since</th>
    <th style="text-align: right;"><a class="btn btn-danger btn-mini" href="'.generate_url(array('page'=>'deleted-ports', 'purge'=>'all')).'" role="button"><i class="icon-remove icon-white"></i> Purge All</a></th>
  </tr></thead>');

foreach (dbFetchRows('SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = "1" AND D.device_id = P.device_id') as $port)
{
  humanize_port($port);
  $since = $config['time']['now'] - strtotime($port['ifLastChange']);
  if (port_permitted($port['port_id'], $port['device_id']))
  {
    echo('<tr class="list">');
    echo('<td style="width: 200px;" class="strong">'.generate_device_link($port).'</td>');
    echo('<td style="width: 350px;" class="strong">'.generate_port_link($port).'</td>');
    echo('<td>'.escape_html($port['ifAlias']).'</td>');
    echo('<td>'.formatUptime($since, 'short-2').' ago</td>');
    echo('<td style="width: 100px; text-align: right;"><a class="btn btn-danger btn-mini" href="'.generate_url(array('page'=>'deleted-ports', 'purge'=>$port['port_id'])).'" role="button"><i class="icon-remove icon-white"></i> Purge</a></td>');
    echo(PHP_EOL);
  }
}

echo('</table>');

// EOF
