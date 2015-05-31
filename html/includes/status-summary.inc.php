<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if ($devices['down'])  { $devices['class']  = "error"; } else { $devices['class']  = ""; }
if ($ports['down'])    { $ports['class']    = "error"; } else { $ports['class']    = ""; }
if ($sensors['alert'])  { $sensors['class']  = "error"; } else { $sensors['class']  = ""; }
//if ($services['down']) { $services['class'] = "error"; } else { $services['class'] = ""; }

?>

<div class="row">
<div class="col-md-6">
<table class="table table-bordered table-condensed-more table-rounded table-striped">
  <thead>
    <tr>
      <th></th>
      <th>Total</th>
      <th>Up</th>
      <th>Down</th>
      <th>Ignored</th>
      <th>Disabled</th>
    </tr>
  </thead>
  <tbody>
    <tr class="<?php echo($devices['class']); ?>">
      <td><strong><a href="<?php echo(generate_url(array('page' => 'devices'))); ?>">Devices</a></strong></td>
      <td><a href="<?php echo(generate_url(array('page' => 'devices'))); ?>"><?php echo($devices['count']) ?></a></td>
      <td><a class="green" href="<?php echo(generate_url(array('page' => 'devices', 'status' => '1'))); ?>"><?php echo($devices['up']) ?> up</a></td>
      <td><a class="red" href="<?php echo(generate_url(array('page' => 'devices', 'status' => '0'))); ?>"><?php echo($devices['down']) ?> down</a></td>
      <td><a class="black" href="<?php echo(generate_url(array('page' => 'devices', 'ignore' => '1'))); ?>"><?php echo($devices['ignored']) ?> ignored</a> </td>
      <td><a class="grey" href="<?php echo(generate_url(array('page' => 'devices', 'disabled' => '1'))); ?>"><?php echo($devices['disabled']) ?> disabled</a></td>
    </tr>
    <tr class="<?php echo($ports['class']) ?>">
      <td><strong><a href="<?php echo(generate_url(array('page' => 'ports'))); ?>">Ports</a></strong></td>
      <td><a href="<?php echo(generate_url(array('page' => 'ports'))); ?>"><?php echo($ports['count']) ?></a></td>
      <td><a class="green" href="<?php echo(generate_url(array('page' => 'ports', 'state' => 'up'))); ?>"><?php echo($ports['up']) ?> up </a></td>
      <td><a class="red" href="<?php echo(generate_url(array('page' => 'ports', 'state' => 'down'))); ?>"><?php echo($ports['down']) ?> down </a></td>
      <td><a class="black" href="<?php echo(generate_url(array('page' => 'ports', 'ignore' => '1'))); ?>"><?php echo($ports['ignored'] + count($cache['ports']['device_ignored'])) ?> ignored </a></td>
      <td><a class="grey" href="<?php echo(generate_url(array('page' => 'ports', 'state' => 'admindown'))); ?>"><?php echo($ports['disabled']) ?> disabled</a></td>
    </tr>
    <tr class="<?php echo($sensors['class']) ?>">
      <td><strong><a href="<?php echo(generate_url(array('page' => 'health'))); ?>">Sensors</a></strong></td>
      <td><a               href="<?php echo(generate_url(array('page' => 'health'))); ?>"><?php echo($sensors['count']) ?></a></td>
      <td><a class="green" href="<?php echo(generate_url(array('page' => 'health'))); ?>"><?php echo($sensors['up']) ?> ok </a></td>
      <td><a class="red"   href="<?php echo(generate_url(array('page' => 'health'))); ?>"><?php echo($sensors['alert']) ?> alert </a></td>
      <td><a class="black" href="<?php echo(generate_url(array('page' => 'health'))); ?>"><?php echo($sensors['ignored']) ?> ignored </a></td>
      <td><a class="grey"  href="<?php echo(generate_url(array('page' => 'health'))); ?>"><?php echo($sensors['disabled']) ?> disabled</a></td>
    </tr>
  </tbody>
</table>
</div>

<?php

if (OBSERVIUM_EDITION != 'community')
{
  // Fetch a quick set of alert_status values to build the alert check status text
  $query = 'SELECT `alert_status` FROM `alert_table` ';
  $query .= 'LEFT JOIN  `alert_table-state` ON  `alert_table`.`alert_table_id` =  `alert_table-state`.`alert_table_id`';

  $check['entities'] = dbFetchRows($query);
  $check['entity_status'] = array('up' => 0, 'down' => 0, 'unknown' => 0, 'delay' => 0, 'suppress' => 0);
  foreach ($check['entities'] as $alert_table_id => $alert_table_entry)
  {
    if ($alert_table_entry['alert_status'] == '1')       { ++$check['entity_status']['up'];
    } elseif($alert_table_entry['alert_status'] == '0') { ++$check['entity_status']['down'];
    } elseif($alert_table_entry['alert_status'] == '2') { ++$check['entity_status']['delay'];
    } elseif($alert_table_entry['alert_status'] == '3') { ++$check['entity_status']['suppress'];
    } else                                              { ++$check['entity_status']['unknown']; }
  }

  if ($check['entity_status']['up'] == count($check['entities']))
  {
    $check['class']  = "green"; $check['table_tab_colour'] = "#194b7f"; $check['html_row_class'] = "";
  } elseif($check['entity_status']['down'] > '0') {
    $check['class']  = "red"; $check['table_tab_colour'] = "#cc0000"; $check['html_row_class'] = "error";
  } elseif($check['entity_status']['delay'] > '0') {
    $check['class']  = "orange"; $check['table_tab_colour'] = "#ff6600"; $check['html_row_class'] = "warning";
  } elseif($check['entity_status']['suppress'] > '0') {
    $check['class']  = "purple"; $check['table_tab_colour'] = "#740074"; $check['html_row_class'] = "suppressed";
  } elseif($check['entity_status']['up'] > '0') {
    $check['class']  = "green"; $check['table_tab_colour'] = "#194b7f"; $check['html_row_class'] = "";
  } else {
    $check['entity_status']['class']  = "gray"; $check['table_tab_colour'] = "#555555"; $check['html_row_class'] = "disabled";
  }

  $check['status_numbers'] = '<span class="green">'. $check['entity_status']['up']. '</span>/<span class="purple">'. $check['entity_status']['suppress'].
         '</span>/<span class=red>'. $check['entity_status']['down']. '</span>/<span class=orange>'. $check['entity_status']['delay'].
         '</span>/<span class=gray>'. $check['entity_status']['unknown']. '</span>';
?>

<div class="col-md-6">
  <table class="table table-bordered table-condensed-more table-rounded table-striped">
  <thead>
    <tr>
     <th></th>
     <th>Ok</th>
     <th>Fail</th>
     <th>Delay</th>
     <th>Supp</th>
     <th>Other</th>
    </tr>
  </thead>
    <tbody>
      <tr class="<?php echo($check['html_row_class']); ?>">
        <td><strong>Alerts</strong></td>
        <td><span class="green"><?php echo $check['entity_status']['up']; ?></span></td>
        <td><span class="red"><?php echo $check['entity_status']['down']; ?></span></td>
        <td><span class="orange"><?php echo $check['entity_status']['delay']; ?></span></td>
        <td><span class="purple"><?php echo $check['entity_status']['suppress']; ?></span></td>
        <td><span class="gray"><?php echo $check['entity_status']['unknown']; ?></span></td>
      </tr>
    </tbody>
  </table>

<?php

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'Groups';
$navbar['style'] = 'margin-top: 20px';

foreach ($navbar['options'] as $option => $array)
{
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
}

foreach (array('device', 'port', 'sensor', 'processor', 'mempool') as $type)
{
  $entity_type = $config['entities'][$type];
  $navbar['options'][$type]['icon'] = $entity_type['icon'];
  $navbar['options'][$type]['text'] = nicecase($type);
  foreach (get_type_groups($type) as $group)
  {
    $navbar['options'][$type]['suboptions'][$group['group_id']]['text'] = $group['group_name'];
    $navbar['options'][$type]['suboptions'][$group['group_id']]['url']  = generate_url(array('page' => 'group', 'group_id' => $group['group_id']));
  }
}

print_navbar($navbar);

?>

 </div>
<?php
}
echo('</div>');

// EOF
