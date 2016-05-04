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

include($config['install_dir'] . '/includes/polling/functions.inc.php');

// Fetch all MIBs we support for this specific OS
foreach (get_device_mibs($device) as $mib) { $mibs[$mib]++; }

// Sort alphabetically
ksort($mibs);

$attribs = get_entity_attribs('device', $device['device_id']);

if ($vars['submit'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    if ($vars['toggle_mib'] && isset($mibs[$vars['toggle_mib']]))
    {
      $mib = $vars['toggle_mib'];

      if (isset($attribs['mib_'.$mib]))
      {
        del_entity_attrib('device', $device, 'mib_' . $mib);
      } else {
        set_entity_attrib('device', $device, 'mib_' . $mib, "0");
      }

      // reload attribs
      $attribs = get_entity_attribs('device', $device['device_id']);
    }
  }
}

//$poll_period = 300;
$error_codes = $GLOBALS['config']['snmp']['errorcodes'];
$poll_period = $GLOBALS['config']['rrd']['step'];
// Count critical errors into DB (only for poller)
$snmp_errors = array();
$sql  = 'SELECT * FROM `snmp_errors` WHERE `device_id` = ?;';
foreach (dbFetchRows($sql, array($device['device_id'])) as $entry)
{
  $timediff   = $entry['updated'] - $entry['added'];
  $poll_count = round($timediff / $poll_period) + 1;

  $entry['error_rate'] = $entry['error_count'] / $poll_count; // calculate error rate
  $snmp_errors[$entry['mib']][] = $entry;
}
ksort($snmp_errors);

print_warning("This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.");

?>

<div class="row"> <!-- begin row -->

  <div class="col-md-5"> <!-- begin MIB options -->

    <div class="box box-solid">

      <div class="box-header with-border">
        <!-- <i class="oicon-gear"></i> --><h3 class="box-title">Device MIBs</h3>
      </div>
      <div class="box-body no-padding">

<table class="table table-striped table-condensed-more">
  <thead>
    <tr>
      <th style="padding: 0px;"></th>
      <th style="padding: 0px; width: 60px;"></th>
      <th style="padding: 0px; width: 80px;"></th>
    </tr>
  </thead>
  <tbody>

<?php

foreach ($mibs as $mib => $count)
{
  $attrib_set = isset($attribs['mib_'.$mib]);
  $mib_errors = isset($snmp_errors[$mib]);

  echo('<tr'. ($mib_errors ? ' class="error"' : '') . '><td><strong>'.$mib.'</strong></td><td>');

  if ($attrib_set && $attribs['mib_'.$mib] == 0)
  {
    $attrib_status = '<span class="label label-error">disabled</span>';
    $toggle = 'Enable'; $btn_class = 'btn-success'; $btn_icon = 'icon-ok';
  } else {
    $attrib_status = '<span class="label label-success">enabled</span>';
    $toggle = "Disable"; $btn_class = "btn-danger"; $btn_icon = 'icon-remove';
  }

  echo($attrib_status.'</td><td>');

      $form = array('type'  => 'simple');
      // Elements
      $form['row'][0]['toggle_mib']  = array('type'     => 'hidden',
                                             'value'    => $mib);
      $form['row'][0]['submit']      = array('type'     => 'submit',
                                             'name'     => $toggle,
                                             'class'    => 'btn-mini '.$btn_class,
                                             'icon'     => $btn_icon,
                                             'right'    => TRUE,
                                             'readonly' => $readonly,
                                             'value'    => 'mib_toggle');
      print_form($form); unset($form);

  echo('</td></tr>');
}
?>
  </tbody>
</table>

  </div> </div>
</div> <!-- end MIB options -->
<?php

  if (count($snmp_errors))
  {
    //r($snmp_errors);

?>
  <div class="col-md-7 col-md-pull-0"> <!-- begin Errors options -->

    <div class="box box-solid">

      <div class="box-header with-border">
        <!-- <i class="oicon-exclamation"></i> --><h3 class="box-title">SNMP errors</h3>
      </div>
      <div class="box-body no-padding">

<table class="table  table-striped-two table-condensed-more ">
  <thead>
    <tr>
      <th style="padding: 0px; width: 40px;"></th>
      <th style="padding: 0px;"></th>
      <!--<th style="padding: 0px; width: 60px;"></th>-->
    </tr>
  </thead>
  <tbody>

<?php

foreach ($snmp_errors as $mib => $entries)
{
  $attrib_set = isset($attribs['mib_'.$mib]);

  echo('<tr><td><span class="label"><i class="icon-bell"></i> ' . count($entries) . '</span></td>');

  //if ($attrib_set && $attribs['mib_'.$mib] == 0)
  //{
  //  $attrib_status = '<span class="label label-error">disabled</span>';
  //} else {
  //  $attrib_status = '<span class="label label-success">enabled</span>';
  //}
  //echo(<td>$attrib_status.'</td>');

  echo('<td><strong>'.$mib.'</strong></td></tr>' . PHP_EOL);

  // OIDs here
  echo('<tr><td colspan="3">
  <table class="table table-condensed-more">');
  foreach ($entries as $error_db)
  {
    // Detect if error rate is exceeded
    $error_both  = isset($error_codes[$error_db['error_code']]['count']) && isset($error_codes[$error_db['error_code']]['rate']);
    $error_count = isset($error_codes[$error_db['error_code']]['count']) && ($error_codes[$error_db['error_code']]['count'] < $error_db['error_count']);
    $error_rate  = isset($error_codes[$error_db['error_code']]['rate'])  && ($error_codes[$error_db['error_code']]['rate']  < $error_db['error_rate']);
    if ($error_both) { $error_exceeded = $error_count && $error_rate; }
    else             { $error_exceeded = $error_count || $error_rate; }

    if ($error_exceeded)
    {
      $error_class  = 'danger';
      $error_class2 = 'error';
    } else {
      $error_class = $error_class2 = 'warning';
    }
    echo('<tr width="100%" class="'.$error_class2.'"><td style="width: 50%;"><strong><i class="glyphicon glyphicon-exclamation-sign"></i> '.$error_db['oid'].'</strong></td>' . PHP_EOL);
    $timediff = $GLOBALS['config']['time']['now'] - $error_db['updated'];
    echo('<td style="width: 100px; white-space: nowrap; text-align: right;">'.generate_tooltip_link('', formatUptime($timediff, "short-3").' ago', format_unixtime($error_db['updated'])).'</td>' . PHP_EOL);
    echo('<td style="width: 80px; white-space: nowrap;"><span class="text-'.$error_class.'">'.$error_db['error_reason'].'</span></td>' . PHP_EOL);
    echo('<td style="width: 40px; text-align: right;"><span class="label">'.$error_db['error_count'].'</span></td>' . PHP_EOL);
    echo('<td style="width: 80px; text-align: right;"><span class="label">'.round($error_db['error_rate'], 2).'/poll</span></td>' . PHP_EOL);

    echo('<td>' . PHP_EOL);
      $form = array('type'  => 'simple');
      // Elements
      $form['row'][0]['mib']         = array('type'     => 'hidden',
                                             'value'    => $mib);
      $form['row'][0]['toggle_oid']  = array('type'     => 'hidden',
                                             'value'    => $error_db['oid']);
      $form['row'][0]['submit']      = array('type'     => 'submit',
                                             'name'     => '',
                                             'class'    => 'btn-mini btn-'.$error_class,
                                             'icon'     => $btn_icon,
                                             'right'    => TRUE,
                                             'readonly' => $readonly,
                                             'disabled' => TRUE, // This button disabled for now, because disabling oids in progress
                                             'value'    => 'toggle_oid');
      print_form($form); unset($form);
    echo('</td>' . PHP_EOL);

    echo('</td></tr>' . PHP_EOL);
  }
  echo('  </table>
</td></tr>' . PHP_EOL);
}
?>
  </tbody>
</table>

  </div> </div>
</div> <!-- end Errors options -->

<?php } ?>

  </div> <!-- end row -->
</div> <!-- end container -->
<?php

// EOF
