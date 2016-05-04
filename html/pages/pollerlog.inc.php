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

$page_title[] = "Poller/Discovery Timing";

$rrd_file = $config['rrd_dir'].'/poller-wrapper.rrd';
if (is_file($rrd_file) && $_SESSION['userlevel'] >= 7)
{
  echo generate_box_open(array('header-border' => TRUE, 'title' => 'Poller Wrapper Graphs'));

  $graph_array = array('type'   => 'poller_wrapper_threads',
                       //'operation' => 'poll',
                       'width'  => 1095,
                       'height' => 100,
                       'from'   => $config['time']['week'],
                       'to'     => $config['time']['now'],
                       );
  echo(generate_graph_tag($graph_array));
  //echo "<h3>Poller wrapper Total time</h3>";
  $graph_array = array('type'   => 'poller_wrapper_times',
                       //'operation' => 'poll',
                       'width'  => 1095,
                       'height' => 100,
                       'from'   => $config['time']['week'],
                       'to'     => $config['time']['now'],
                       );
  echo(generate_graph_tag($graph_array));
  echo('<blockquote>
  <footer><i>NOTE. Total time for poller wrapper not same as mentioned below.<br />Total poller wrapper time is real polling time for all devices considering threads. But below shows the amount of all polling times for all devices.</i></footer>
</blockquote>');

  echo generate_box_close();
}

echo generate_box_open(array('header-border' => TRUE, 'title' => 'Poller/Discovery Timing'));
echo('<table class="'.OBS_CLASS_TABLE_STRIPED_MORE.'">' . PHP_EOL);
?>

  <thead>
    <tr>
      <th class="state-marker"></th>
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
      <td class="state-marker"></td>
      <td class="entity">'.$row['device_link'].'</td>
      <td style="width: 12%;">
        <div class="progress progress-'.$proc['color']['poller'].' active" style="margin: 2px 0 1px;"><div class="bar" style="text-align: right; width: '.$proc['time']['poller'].'%;"></div></div>
      </td>
      <td style="width: 7%">
        '.$row['last_polled_timetaken'].'s
      </td>
      <td>'.format_timestamp($row['last_polled']).' </td>
      <td>'.formatUptime($config['time']['now'] - strtotime($row['last_polled']), 'shorter').' ago</td>');

  // Discovery times
  echo('
      <td style="width: 12%;">
        <div class="progress progress-'.$proc['color']['discovery'].' active" style="margin: 2px 0 1px;"><div class="bar" style="text-align: right; width: '.$proc['time']['discovery'].'%;"></div></div>
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

echo generate_box_close();

// EOF
