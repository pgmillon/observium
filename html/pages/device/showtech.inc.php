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

if ($_SESSION['userlevel'] == 10) // Admin page only
{

?>

<div class="row">
  <div class="col-md-12">
    <div class="well info_box">
      <div class="title"><a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'perf'))); ?>">
        <i class="oicon-clock"></i> Duration</a></div>
      <div class="content">

<?php
  $ptime = dbFetchRow('SELECT * FROM `devices_perftimes` WHERE `operation` = "poll" AND `device_id` = ? ORDER BY `start` DESC LIMIT 1', array($device['device_id']));

  echo "Last Polled: <b>" . format_unixtime($ptime['start']) .'</b> (took '.$ptime['duration'].'s) - <a href="' . generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'perf')) . '">Details</a><p />';

  $dtime = dbFetchRow('SELECT * FROM `devices_perftimes` WHERE `operation` = "discover" AND `device_id` = ? ORDER BY `start` DESC LIMIT 1', array($device['device_id']));

  echo "Last discovered: <b>" . format_unixtime($dtime['start']) .'</b> (took '.$dtime['duration'].'s) - <a href="' . generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'perf')) . '">Details</a><p />';
?>

      </div>
    </div>

    <div class="well info_box">
      <div class="title"><a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'showconfig'))); ?>">
        <i class="oicon-blocks"></i> RANCID</a></div>
      <div class="content">
<?php
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
?>
      </div>
    </div>

    <div class="well info_box">
      <div class="title"><a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'latency'))); ?>">
        <i class="oicon-blocks"></i> Smokeping</a></div>
      <div class="content">
<?php
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
?>
      </div>
    </div>
  </div>

</div>

    <div class="well info_box">
      <div class="title"><a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'graphs'))); ?>">
        <i class="oicon-blocks"></i> Device Graphs</a></div>
      <div class="content">

        <table class="table table-rounded table-bordered table-striped">
        <tr><th>Graph Type</th><th style="width: 80px;">Has File</th><th style="width: 80px;">Has Array</th><th style="width: 80px;">Enabled</th></tr>
<?php
  foreach (dbFetchRows("SELECT * FROM `device_graphs` WHERE `device_id` = ? ORDER BY `graph`", array($device['device_id'])) as $graph_entry)
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

        </table>
      </div>
    <div class="well info_box">
<?php

print_vars($device);

?>
    </div>
    </div>
  </div>

<?php
}
else
{
  include("includes/error-no-perm.inc.php");
}

// EOF
