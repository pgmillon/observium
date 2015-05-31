<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 * @version    1.1
 *
 */

$pagetitle[] = "Poller/Discovery Timing";

?>

<h3>Poller/Discovery Timing</h3>

<table class="table table-striped table-condensed table-bordered">
  <thead>
    <tr>
      <th></th>
      <th>Device</th>
      <th colspan="3">Last Polled</th>
      <th></th>
      <th colspan="3">Last Discovered</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
<?php

$proc['avg']['poller']    = round($cache['devices']['timers']['polling']   / $devices['count'], 2);
$proc['avg']['discovery'] = round($cache['devices']['timers']['discovery'] / $devices['count'], 2);
$proc['avg2']['poller']    = 0;
$proc['avg2']['discovery'] = 0;
$proc['max']['poller']    = 0;
$proc['max']['discovery'] = 0;

// Make poller table
$poller_table = array();
foreach ($cache['devices']['hostname'] as $hostname => $id)
{
  // Reference the cache.
  $device = &$cache['devices']['id'][$id];

  if ($device['disabled'] == 1 && !$config['web_show_disabled']) { continue; }

  // Find max poller/discovery times
  if ($device['last_polled_timetaken'] > $proc['max']['poller'])        { $proc['max']['poller'] = $device['last_polled_timetaken']; }
  if ($device['last_discovered_timetaken'] > $proc['max']['discovery']) { $proc['max']['discovery'] = $device['last_discovered_timetaken']; }
  $proc['avg2']['poller']    += pow($device['last_polled_timetaken'], 2);
  $proc['avg2']['discovery'] += pow($device['last_discovered_timetaken'], 2);

  $poller_table[] = array(
    'html_row_class'            => $device['html_row_class'],
    'html_tab_colour'           => $device['html_tab_colour'],
    'device_hostname'           => $device['hostname'],
    'device_link'               => generate_device_link($device),
    'last_polled_timetaken'     => $device['last_polled_timetaken'],
    'last_polled'               => $device['last_polled'],
    'last_discovered_timetaken' => $device['last_discovered_timetaken'],
    'last_discovered'           => $device['last_discovered']
  );
}

// Sort poller table
// sort order: $polled > $discovered > $hostname
$poller_table = array_sort_by($poller_table, 'last_polled_timetaken', SORT_DESC, SORT_NUMERIC, 'last_discovered_timetaken', SORT_DESC, SORT_NUMERIC, 'device_hostname', SORT_ASC, SORT_STRING);

// Print poller table
foreach ($poller_table as $row)
{
  $proc['time']['poller']     = round($row['last_polled_timetaken'] * 100 / $proc['max']['poller']);
  $proc['color']['poller']    = "success";
  if     ($row['last_polled_timetaken'] >  ($proc['max']['poller'] * 0.75)) { $proc['color']['poller'] = "danger"; }
  elseif ($row['last_polled_timetaken'] >  ($proc['max']['poller'] * 0.5))  { $proc['color']['poller'] = "warning"; }
  elseif ($row['last_polled_timetaken'] >= ($proc['max']['poller'] * 0.25)) { $proc['color']['poller'] = "info"; }
  $proc['time']['discovery']  = round($row['last_discovered_timetaken'] * 100 / $proc['max']['discovery']);
  $proc['color']['discovery'] = "success";
  if     ($row['last_discovered_timetaken'] >  ($proc['max']['discovery'] * 0.75)) { $proc['color']['discovery'] = "danger"; }
  elseif ($row['last_discovered_timetaken'] >  ($proc['max']['discovery'] * 0.5))  { $proc['color']['discovery'] = "warning"; }
  elseif ($row['last_discovered_timetaken'] >= ($proc['max']['discovery'] * 0.25)) { $proc['color']['discovery'] = "info"; }

  // Poller times
  echo('    <tr class="'.$row['html_row_class'].'">
      <td style="width: 1px; max-width: 1px; background-color: '.$row['html_tab_colour'].'; margin: 0px; padding: 0px"></td>
      <td class="entity">'.$row['device_link'].'</td>
      <td style="width: 12%;">
        <div class="progress progress-'.$proc['color']['poller'].' active" style="margin-bottom: 5px;"><div class="bar" style="text-align: right; width: '.$proc['time']['poller'].'%;"></div></div>
      </td>
      <td style="width: 7%">
        '.$row['last_polled_timetaken'].'s
      </td>
      <td>'.format_timestamp($row['last_polled']).' </td>
      <td>'.formatUptime($config['time']['now'] - strtotime($row['last_polled']), 'shorter').' ago</td>');

  // Discovery times
  echo('
      <td style="width: 12%;">
        <div class="progress progress-'.$proc['color']['discovery'].' active" style="margin-bottom: 5px;"><div class="bar" style="text-align: right; width: '.$proc['time']['discovery'].'%;"></div></div>
      </td>
      <td style="width: 7%">
        '.$row['last_discovered_timetaken'].'s
      </td>
      <td>'.format_timestamp($row['last_discovered']).'</td>
      <td>'.formatUptime($config['time']['now'] - strtotime($row['last_discovered']), 'shorter').' ago</td>

    </tr>
');
}

// Calculate root mean square
$proc['avg2']['poller']    = sqrt($proc['avg2']['poller'] / $devices['count']);
$proc['avg2']['poller']    = round($proc['avg2']['poller'], 2);
$proc['avg2']['discovery'] = sqrt($proc['avg2']['discovery'] / $devices['count']);
$proc['avg2']['discovery'] = round($proc['avg2']['discovery'], 2);

echo('    <tr>
      <th></th>
      <th style="text-align: right;">Total time for all devices (average per device):</th>
      <th></th>
      <th colspan="3">'.$cache['devices']['timers']['polling'].'s ('.$proc['avg2']['poller'].'s)</th>
      <th></th>
      <th colspan="3">'.$cache['devices']['timers']['discovery'].'s ('.$proc['avg2']['discovery'].'s)</th>
    </tr>
');

unset($poller_table, $proc, $row);

?>
  </tbody>
</table>
<?php

// EOF
