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

echo('<table class="table table-condensed table-striped table-bordered">'.PHP_EOL);
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
  echo('    <tr>'.PHP_EOL);
  echo('      <td><a href="'. generate_url(array('page' => 'packages', 'name' => $entry['name'])).'">'.$entry['name'].'</a></td>'.PHP_EOL);
  if ($build != '') { $dbuild = '-'.$entry['build']; } else { $dbuild = ''; }
  echo('      <td>'.$entry['version'].$dbuild.'</td>'.PHP_EOL);
  echo('      <td>'.$entry['arch'].'</td>'.PHP_EOL);
  echo('      <td>'.$entry['manager'].'</td>'.PHP_EOL);
  echo('      <td>'.format_si($entry['size']).'</td>'.PHP_EOL);
  echo('    </tr>'.PHP_EOL);

  $i++;
}

echo('  </tbody>'.PHP_EOL);
echo('</table>'.PHP_EOL);

// EOF
