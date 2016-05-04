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

echo generate_box_open();

echo('<table class="table table-condensed table-striped ">'.PHP_EOL);
echo('  <thead>'.PHP_EOL);
echo('    <tr>'.PHP_EOL);
echo('      <th style="width: 300px;">Package name</th>'.PHP_EOL);
echo('      <th>Version</th>'.PHP_EOL);
echo('      <th>Architecture</th>'.PHP_EOL);
echo('      <th>Type</th>'.PHP_EOL);
echo('      <th>Size</th>'.PHP_EOL);
echo('    </tr>'.PHP_EOL);
echo('  </thead>'.PHP_EOL);
echo('  <tbody>'.PHP_EOL);

$i = 0;
foreach (dbFetchRows("SELECT * FROM `packages` WHERE `device_id` = ? ORDER BY `name`", array($device['device_id'])) as $entry)
{

  switch($entry['arch'])
  {
    case "amd64":
      $entry['arch_class'] = 'label-success';
      break;
    case "i386":
      $entry['arch_class'] = 'label-info';
      break;
    default:
      $entry['arch_class'] = '';
  }

  switch($entry['manager'])
  {
    case "deb":
      $entry['manager_class'] = 'label-warning';
      break;
    case "rpm":
      $entry['manager_class'] = 'label-important';
      break;
    default:
      $entry['manager_class'] = '';
  }

  echo('    <tr>'.PHP_EOL);
  echo('      <td class="entity"><a href="'. generate_url(array('page' => 'packages', 'name' => $entry['name'])).'">'.$entry['name'].'</a></td>'.PHP_EOL);
  if ($build != '') { $dbuild = '-'.$entry['build']; } else { $dbuild = ''; }
  echo('      <td>'.$entry['version'].$dbuild.'</td>'.PHP_EOL);
  echo('      <td><span class="label '.$entry['arch_class'].'">'.$entry['arch'].'</span></td>'.PHP_EOL);
  echo('      <td><span class="label '.$entry['manager_class'].'">'.$entry['manager'].'</span></td>'.PHP_EOL);
  echo('      <td>'.format_si($entry['size']).'</td>'.PHP_EOL);
  echo('    </tr>'.PHP_EOL);

  $i++;
}

echo('  </tbody>'.PHP_EOL);
echo('</table>'.PHP_EOL);

echo generate_box_close();

// EOF
