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

print_message("This page allows you to disable or enable certain Graphs detected for a device.");

$graphs_db = array();
foreach (dbFetchRows("SELECT `graph`,`enabled` FROM `device_graphs` WHERE `device_id` = ?", array($device['device_id'])) as $entry)
{
  $graph   = $entry['graph'];
  $section = $config['graph_types']['device'][$graph]['section'];
  $graphs_db[$graph] = (bool)$entry['enabled'];
  // Another array sorted by sections
  $graphs_sections[$section][$graph] = (bool)$entry['enabled'];
}

if ($vars['submit'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    $graph = $vars['toggle_graph'];
    if ($graph && isset($graphs_db[$graph]) &&
        !in_array($config['graph_types']['device'][$graph]['section'], array('poller', 'system')))
    {
      $value = (int)!$graphs_db[$graph]; // Toggle current 'enabled' value
      $updated = dbUpdate(array('enabled' => $value), 'device_graphs', '`device_id` = ? AND `graph` = ?', array($device['device_id'], $graph));
      if ($updated)
      {
        print_success("Graph '$graph' ".($value ? 'enabled' : 'disabled').'.');
        $graphs_sections[$config['graph_types']['device'][$graph]['section']][$graph] = (bool)$value;
      }
    }
  }
}

?>

<div class="row"> <!-- begin row -->
  <div class="col-md-6"> <!-- begin poller options -->

    <div class="box box-solid">

      <div class="box-header with-border">
        <!-- <i class="oicon-gear"></i> --><h3 class="box-title">Device Graphs</h3>
      </div>
      <div class="box-body no-padding">

<table class="table table-striped table-condensed-more">
  <thead>
    <tr>
      <th>Name</th>
      <th>Description</th>
      <th>Section</th>
      <th style="width: 60px;">Status</th>
      <th style="width: 80px;"></th>
    </tr>
  </thead>
  <tbody>

<?php

foreach ($graphs_sections as $section => $entry)
{
  foreach ($entry as $graph => $enabled)
  {
    echo('<tr><td><strong>'.$graph.'</strong></td><td>');
    echo($config['graph_types']['device'][$graph]['descr'].'</td><td>');
    echo(nicecase($section).'</td><td>');

    if (!$enabled)
    {
      $attrib_status = '<span class="label label-important">disabled</span>';
      $toggle = 'Enable'; $btn_class = 'btn-success'; $btn_icon = 'icon-ok';
    } else {
      $attrib_status = '<span class="label label-success">enabled</span>';
      $toggle = "Disable"; $btn_class = "btn-danger"; $btn_icon = 'icon-remove';
    }

    echo($attrib_status.'</td><td>');

    if (!in_array($section, array('poller', 'system')))
    {
      $form = array('type'  => 'simple');
      // Elements
      $form['row'][0]['toggle_graph'] = array('type'    => 'hidden',
                                             'value'    => $graph);
      $form['row'][0]['submit']      = array('type'     => 'submit',
                                             'name'     => $toggle,
                                             'class'    => 'btn-mini '.$btn_class,
                                             'icon'     => $btn_icon,
                                             'right'    => TRUE,
                                             'readonly' => $readonly,
                                             'value'    => 'graph_toggle');
      print_form($form); unset($form);
    } else {
      echo('<span style="line-height: 20px;" class="btn btn-mini disabled pull-right"><i class="icon-lock"></i>&nbsp;Required</span>');
    }

    echo('</td></tr>');
  }
}
?>
  </tbody>
</table>

  </div> </div>
</div> <!-- end poller options -->

  </div> <!-- end row -->
</div> <!-- end container -->
<?php

// EOF
