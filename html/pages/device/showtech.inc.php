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

if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

?>

<div class="row">
  <div class="col-md-12">

<?php

  $box_args = array('title' => 'Duration',
                    'header-border' => TRUE,
                    'padding' => TRUE,
                   );

  $box_args['header-controls'] = array('controls' => array('perf' => array('text' => 'Performance Data',
                                                                           //'icon' => 'icon-trash',
                                                                           'anchor' => TRUE,
                                                                           'url'  => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'perf')),
                                                                          )));

  echo generate_box_open($box_args);

  $ptime = dbFetchRow('SELECT * FROM `devices_perftimes` WHERE `operation` = "poll" AND `device_id` = ? ORDER BY `start` DESC LIMIT 1', array($device['device_id']));

  echo "Last Polled: <b>" . format_unixtime($ptime['start']) .'</b> (took '.$ptime['duration'].'s) - <a href="' . generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'perf')) . '">Details</a>';

  $dtime = dbFetchRow('SELECT * FROM `devices_perftimes` WHERE `operation` = "discover" AND `device_id` = ? ORDER BY `start` DESC LIMIT 1', array($device['device_id']));

  echo "<p>Last discovered: <b>" . format_unixtime($dtime['start']) .'</b> (took '.$dtime['duration'].'s) - <a href="' . generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'perf')) . '">Details</a></p>';

  echo generate_box_close();

  $box_args = array('title' => 'RANCID',
                    'header-border' => TRUE,
                    'padding' => TRUE,
                   );

  $box_args['header-controls'] = array('controls' => array('perf' => array('text' => 'Show Config',
                                                                           'anchor' => TRUE,
                                                                           'url'  => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig')),
                                                                          )));

  echo generate_box_open($box_args);

  if (count($config['rancid_configs']))
  {
    $device_config_file = get_rancid_filename($device['hostname'], 1);

    echo('<p />');

    if ($device_config_file)
    {
      print_success("Configuration file for device was found; will be displayed to users with level 7 or higher.");
    } else {
      print_warning("Configuration file for device was not found.");
    }
  } else {
    print_warning("No RANCID directories configured.");
  }

  echo generate_box_close();

  $box_args = array('title' => 'Smokeping',
                    'header-border' => TRUE,
                    'padding' => TRUE,
                   );

  $box_args['header-controls'] = array('controls' => array('perf' => array('text' => 'Show Latency',
                                                                           'anchor' => TRUE,
                                                                           'url'  => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'latency')),
                                                                          )));

  echo generate_box_open($box_args);

  if ($config['smokeping']['dir'] != '')
  {
    $smokeping_files = get_smokeping_files(1);

    echo('<p />');

    if ($smokeping_files['incoming'][$device['hostname']])
    {
      print_success("RRD for incoming latency found.");
    } else {
      print_error("RRD for incoming latency not found.");
    }

    if ($smokeping_files['outgoing'][$device['hostname']])
    {
      print_success("RRD for outgoing latency found.");
    } else {
      print_error("RRD for outgoing latency not found.");
    }
  } else {
    print_warning("No Smokeping directory configured.");
  }

  echo generate_box_close();

  $box_args = array('title' => 'Device Graphs',
                    'header-border' => TRUE,
                    //'padding' => TRUE,
                   );

  $box_args['header-controls'] = array('controls' => array('perf' => array('text' => 'Show Graphs',
                                                                           'anchor' => TRUE,
                                                                           'url'  => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'graphs')),
                                                                          )));

  echo generate_box_open($box_args);

  echo('<table class="' . OBS_CLASS_TABLE_STRIPED_MORE . '">');

?>
      <thead><tr>
        <th>Graph Type</th>
        <th style="width: 80px;">Has File</th>
        <th style="width: 80px;">Has Array</th>
        <th style="width: 80px;">Enabled</th>
        <th></th>
      </tr></thead>
      <tbody>
<?php
  foreach ($device['graphs'] as $graph_entry)
  {
    echo('<tr><td>'.$graph_entry['graph'].'</td>');

    if (is_file('includes/graphs/device/'.$graph_entry['graph'].'.inc.php'))
    { echo('<td><i class="icon-ok-sign green"></i></td>'); } else { echo('<td><i class="icon-remove-sign red"></i></td>'); }

    if (is_array($config['graph_types']['device'][$graph_entry['graph']]))
    { echo('<td><i class="icon-ok-sign green"></i></td>'); } else { echo('<td><i class="icon-remove-sign red"></i></td>'); }

    if ($graph_entry['enabled'])
    { echo('<td><i class="icon-ok-sign green"></i></td>'); } else { echo('<td><i class="icon-remove-sign red"></i></td>'); }

    echo('<td>'.print_r($config['graph_types']['device'][$graph_entry['graph']], TRUE).'</td>');

    echo('</tr>');
  }
?>
        </tbody>
        </table>
<?php

  echo generate_box_close();

  echo generate_box_open();
  print_vars($device);
  echo generate_box_close();
?>
  </div>
<?php

// EOF
