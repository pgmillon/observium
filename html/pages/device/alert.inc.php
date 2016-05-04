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

// User level 7-9 only can see config
$readonly = $_SESSION['userlevel'] < 10;

if ($entry = get_alert_entry_by_id($vars['alert_entry']))
{
  if ($entry['device_id'] != $device['device_id'])
  {
    print_error("This alert entry id does not match this device.");
  } else {

  // Run actions
  if ($vars['submit'] == 'update-alert-entry' && $_SESSION['userlevel'] >= 10)
  {

    if (isset($vars['ignore_until_ok']) && ($vars['ignore_until_ok'] == '1' || $entry['ignore_until_ok'] == '1'))
    {
      $update_state['ignore_until_ok'] = '1';
    } else {
      $update_state['ignore_until_ok'] = '0';
    }

    // 2019-12-05 23:30:00

    if (isset($vars['ignore_until']) && $vars['ignore_until_enable'])
    {
      $update_state['ignore_until'] = $vars['ignore_until'];
    } else {
      $update_state['ignore_until'] = array('NULL');
    }

    if (is_array($update_state))
    {
      $up_s = dbUpdate($update_state, 'alert_table', '`alert_table_id` =  ?', array($vars['alert_entry']));
    }

    // Refresh array because we've changed the database.
    $entry = get_alert_entry_by_id($vars['alert_entry']);
  }

  // End actions

  humanize_alert_entry($entry);

  $alert_rules = cache_alert_rules();
  $alert       = $alert_rules[$entry['alert_test_id']];
  $state       = json_decode($entry['state'], TRUE);
  $conditions  = json_decode($alert['conditions'], TRUE);
  $entity      = get_entity_by_id_cache($entry['entity_type'], $entry['entity_id']);

//  r($entry);
//  r($alert);

?>

<div class="row">
  <div class="col-md-3">
    <div class="box box-solid">
      <div class="box-header with-border">
        <!-- <i class="oicon-bell"></i> --><h3 class="box-title">Alert Details</h3>
      </div>
      <div class="box-body no-padding">
        <table class="table table-condensed  table-striped ">
          <tbody>
            <tr><th>Type</th><td><?php echo '<i class="' . $config['entities'][$alert['entity_type']]['icon'] . '"></i> ' . nicecase($entry['entity_type']); ?></td></tr>
            <tr><th>Entity</th><td><?php echo generate_entity_link($entry['entity_type'], $entry['entity_id'], $entity['entity_name']); ?></td></tr>
            <tr><th>Checker</th><td><a href="<?php echo generate_url(array('page' => 'alert_check', 'alert_test_id' => $alert['alert_test_id'])); ?>"><?php echo escape_html($alert['alert_name']); ?></a></td></tr>
            <tr><th>Fail Msg</th><td><?php echo escape_html($alert['alert_message']); ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="box box-solid">
      <div class="box-header with-border">
        <!-- <i class="oicon-time"></i> --><h3 class="box-title">Status</h3>
      </div>
      <div class="box-body no-padding">

        <table class="table table-condensed  table-striped ">
          <tr><th>Status</th><td><span class="<?php echo $entry['class']; ?>"><?php echo $entry['last_message']; ?></span></td></tr>
          <tr><th>Last Checked</th><td><?php echo $entry['checked']; ?></td></tr>
          <tr><th>Last Changed</th><td><?php echo $entry['changed']; ?></td></tr>
          <tr><th>Last Alerted</th><td><?php echo $entry['alerted']; ?></td></tr>
          <tr><th>Last Recovered</th><td><?php echo $entry['recovered']; ?></td></tr>
        </table>
      </div>
    </div>
  </div>


  <div class="col-md-5">
<?php

      $form = array('type'      => 'horizontal',
                    'id'        => 'update_alert_entry',
                    'title'     => 'Alert Settings',
                    //'icon'      => 'oicon-gear',
                    'fieldset'  => array('edit' => ''),
                    );

      $form['row'][0]['editing']   = array(
                                      'type'        => 'hidden',
                                      'value'       => 'yes');
      $form['row'][1]['ignore_until'] = array(
                                      'type'        => 'datetime',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Ignore Until',
                                      'placeholder' => '',
                                      //'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'disabled'    => empty($entry['ignore_until']),
                                      'min'         => 'current',
                                      'value'       => ($entry['ignore_until'] ? $entry['ignore_until'] : ''));
      $form['row'][1]['ignore_until_enable'] = array(
                                      'type'        => 'switch',
                                      'readonly'    => $readonly,
                                      'onchange'    => "toggleAttrib('disabled', 'ignore_until')",
                                      'value'       => !empty($entry['ignore_until']));

      $form['row'][2]['ignore_until_ok'] = array(
                                      'type'        => 'switch',
                                      'name'        => 'Ignore Until OK',
                                      //'fieldset'    => 'edit',
                                      'size'        => 'small',
                                      'on-color'    => 'danger',
                                      'off-color'   => 'primary',
                                      'readonly'    => $readonly,
                                      'value'       => $entry['ignore_until_ok']);

      if (!$readonly) // Hide button for readonly
      {
        $form['row'][7]['submit'] = array(
                                      'type'        => 'submit',
                                      'name'        => 'Save Changes',
                                      'icon'        => 'icon-ok icon-white',
                                      'div_style'   => 'padding-top: 10px; padding-bottom: 10px;',
                                      'right'       => TRUE,
                                      'class'       => 'btn-primary',
                                      'readonly'    => $readonly,
                                      'value'       => 'update-alert-entry');
      }

      print_form($form);
      unset($form);
?>
  </div>

  <div class="col-md-12">
<?php echo generate_box_open(array('title' => 'Historical Availability')); ?>

<table class="table table-condensed  table-striped">

<tr><td>
<?php
  $graph_array['id']     = $entry['alert_table_id'];
  $graph_array['type']   = 'alert_status';
  print_graph_row($graph_array);
?>
</td></tr>
</table>
<?php

  echo generate_box_close();
  echo("</div></div>"); // end row
  }

} else {
  print_error("Unfortunately, this alert entry id does not seem to exist in the database!");
}

// EOF
