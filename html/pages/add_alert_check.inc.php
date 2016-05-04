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

//        <span style="clear:none; float: right; margin-right: 10px; border-left: 1px;">
//          <a href="#delete_alert_modal" data-toggle="modal"><i class="oicon-minus-circle"></i></a>
//          <a href="#delete_alert_modal" data-toggle="modal"><i class="oicon-minus-circle"></i></a>
//        </span>

// Global write permissions required.
if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

include($config['html_dir']."/includes/alerting-navbar.inc.php");

  // print_vars($vars);

  if (isset($vars['submit']) && $vars['submit'] == "add_alert_check")
  {
    $message = '<h4>Adding alert checker</h4> ';

    $ok = TRUE;
    foreach (array('entity_type', 'alert_name', 'alert_severity', 'check_conditions', 'assoc_device_conditions', 'assoc_entity_conditions') as $var)
    {
      if (!isset($vars[$var]) || strlen($vars[$var]) == '0') { $ok = FALSE; }
    }

    if ($ok)
    {
      $check_array = array();

      $conds = array();
      foreach (explode("\n", $vars['check_conditions']) AS $cond)
      {
        list($this['metric'], $this['condition'], $this['value']) = explode(" ", trim($cond), 3);
        $conds[] = $this;
      }
      $check_array['conditions'] = json_encode($conds);

      $check_array['entity_type'] = $vars['entity_type'];
      $check_array['alert_name'] = $vars['alert_name'];
      $check_array['alert_message'] = $vars['alert_message'];
      $check_array['severity'] = $vars['alert_severity'];
      $check_array['suppress_recovery'] = ($vars['alert_send_recovery'] == '1' || $vars['alert_send_recovery'] == 'on' ? 0 : 1);
      $check_array['alerter'] = NULL;
      $check_array['and'] = $vars['alert_and'];
      $check_array['delay'] = $vars['alert_delay'];
      $check_array['enable'] = '1';

      $check_id = dbInsert('alert_tests', $check_array);
      if (is_numeric($check_id))
      {
        $message .= '<p>Alert inserted as <a href="'.generate_url(array('page' => 'alert_check', 'alert_test_id' => $check_id)).'">'.$check_id.'</a></p>';

        $assoc_array = array();
        $assoc_array['alert_test_id'] = $check_id;
        $assoc_array['entity_type'] = $vars['entity_type'];
        $assoc_array['enable'] = '1';
        $dev_conds = array();
        foreach (explode("\n", $vars['assoc_device_conditions']) AS $cond)
        {
          list($this['attrib'], $this['condition'], $this['value']) = explode(" ", trim($cond), 3);
          $dev_conds[] = $this;
        }
        $assoc_array['device_attribs'] = json_encode($dev_conds);
        if ($vars['assoc_device_conditions'] == "*") { $vars['assoc_device_conditions'] = json_encode(array()); }
        $ent_conds = array();
        foreach (explode("\n", $vars['assoc_entity_conditions']) AS $cond)
        {
          list($this['attrib'], $this['condition'], $this['value']) = explode(" ", trim($cond), 3);
          $ent_conds[] = $this;
        }
        $assoc_array['entity_attribs'] = json_encode($ent_conds);
        if ($vars['assoc_entity_conditions'] == "*") { $vars['assoc_entity_conditions'] = json_encode(array()); }

        $assoc_id = dbInsert('alert_assoc', $assoc_array);
        if (is_numeric($assoc_id))
        {
          print_success($message . "<p>Association inserted as ".$assoc_id."</p>");
          unset($vars); // Clean vars for use with new associations
        } else {
          print_warning($message . "<p>Association creation failed.</p>");
          dbDelete('alert_tests', "`alert_test_id` = ?", array($check_id)); // Undo alert checker create
        }
      } else {
        print_error($message . "<p>Alert creation failed. Please note that the alert name <b>must</b> be unique.</p>");
      }
    } else {
      print_warning($message . "Missing required data.");
    }

    if (OBS_DEBUG)
    {
      print_message("<h4>TEMPLATE:<h4> <pre>" . escape_html(generate_template('alert', array_merge($check_array, $vars))) . "</pre>", 'console', FALSE);
    }

  }

?>

<form name="form1" method="post" action="<?php echo(generate_url(array('page' => 'add_alert_check'))); ?>" class="form-horizontal">

<div class="row">
  <div class="col-md-6">

<?php

   $box_args = array('title' => 'New Checker Details',
                     'header-border' => TRUE,
                     'padding' => TRUE,
                    );

   echo generate_box_open($box_args);

?>

  <fieldset>
  <div class="control-group">
    <label class="control-label" for="entity_type">Entity Type</label>
    <div class="controls">
        <?php
        $item = array('id'          => 'entity_type',
                      'live-search' => FALSE,
                      'width'       => '220px',
                      'value'       => $vars['entity_type']);
        foreach ($config['entities'] as $entity_type => $entity_type_array)
        {
          if (!$entity_type_array['hide'])
          { // ignore this type if it's a meta-entity
            if (!isset($entity_type_array['icon'])) { $entity_type_array['icon'] = $config['entity_default']['icon']; }
            $item['values'][$entity_type] = array('name' => nicecase($entity_type),
                                                  'icon' => $entity_type_array['icon']);
          }
        }
        echo(generate_form_element($item, 'select'));
        ?>
    </div>
        </div>

  <div class="control-group">
    <label class="control-label" for="alert_name">Alert Name</label>
    <div class="controls">
      <?php
      $item = array('id'          => 'alert_name',
                    'name'        => 'Alert name',
                    'placeholder' => TRUE,
                    'width'       => '220px',
                    'value'       => $vars['alert_name']);
      echo(generate_form_element($item, 'text'));
      ?>
    </div>
  </div>
        <div class="control-group">
    <label class="control-label" for="alert_message">Message</label>
    <div class="controls">
      <?php
      $item = array('id'          => 'alert_message',
                    'name'        => 'Alert message',
                    'placeholder' => TRUE,
                    //'width'       => '220px',
                    'class'       => 'col-md-11',
                    'rows'        => 3,
                    'value'       => $vars['alert_message']);
      echo(generate_form_element($item, 'textarea'));
      ?>
    </div>
        </div>
        <div class="control-group">
    <label class="control-label" for="alert_delay">Alert Delay</label>
    <div class="controls">
      <?php
      $item = array('id'          => 'alert_delay',
                    'name'        => '&#8470; of checks to delay alert',
                    'placeholder' => TRUE,
                    'width'       => '220px',
                    'value'       => $vars['alert_delay']);
      echo(generate_form_element($item, 'text'));
      ?>
    </div>
  </div>
    <div class="control-group">
      <label class="control-label" for="alert_send_recovery">Send recovery</label>
      <div class="controls">
      <?php
      $item = array('id'          => 'alert_send_recovery',
                    'size'        => 'small',
                    'off-color'   => 'danger',
                    'value'       => (isset($vars['alert_send_recovery']) ? $vars['alert_send_recovery'] : 1)); // Set to on by default
      echo(generate_form_element($item, 'switch'));
      ?>
      </div>
    </div>
        <div class="control-group">
    <label class="control-label" for="alert_severity">Severity</label>
    <div class="controls">
        <?php
        $item = array('id'          => 'alert_severity',
                      //'name'        => 'Severity',
                      'live-search' => FALSE,
                      'width'       => '220px',
                      'value'       => $vars['alert_severity'],
                      'values'      => array('crit' => array('name' => 'Critical',
                                                             'icon' => 'oicon-exclamation-red'),
                                             //'warn' => array('name' => 'Warning',
                                             //                'icon' => 'oicon-warning'),
                                             //'info' => array('name' => 'Informational',
                                             //                'icon' => 'oicon-information'),
                                             )
                      );
        echo(generate_form_element($item, 'select'));
        ?>
    </div>
        </div>
  </fieldset>

  <?php echo generate_box_close(); ?>

  </div> <!-- col -->

<div class="col-md-6">

<?php

   $box_args = array('title' => 'New Checker Conditions',
                                'header-border' => TRUE,
                                'padding' => TRUE,
                    );


   $box_args['header-controls'] = array('controls' => array('tooltip'   => array('icon'   => 'icon-info text-primary',
                                                                                 'anchor' => TRUE,
                                                                                 'class'  => 'tooltip-from-element',
                                                                                 //'url'    => '#',
                                                                                 'data'   => 'data-tooltip-id="tooltip-help-conditions"')));

   echo generate_box_open($box_args);

?>

        <div style="margin-bottom: 10px;">
        <?php
        $item = array('id'          => 'alert_and',
                      //'name'        => 'Severity',
                      'live-search' => FALSE,
                      'width'       => '220px',
                      'value'       => (isset($vars['alert_and']) ? $vars['alert_and'] : 1), // Set to and by default
                      'values'      => array('0' => array('name' => 'Require any condition',
                                                          'icon' => 'oicon-or'),
                                             '1' => array('name' => 'Require all conditions',
                                                          'icon' => 'oicon-and'),
                                             )
                      );
        echo(generate_form_element($item, 'select'));

        echo(PHP_EOL . '          </div>' . PHP_EOL);

      $item = array('id'          => 'check_conditions',
                    'name'        => 'Metric Conditions',
                    'placeholder' => TRUE,
                    //'width'       => '220px',
                    'class'       => 'col-md-12',
                    'rows'        => 3,
                    'value'       => $vars['check_conditions']);
      echo generate_form_element($item, 'textarea');

      echo generate_box_close();

   $box_args = array('title' => 'New Checker Association',
                                'header-border' => TRUE,
                                'padding' => TRUE,
                    );

   $box_args['header-controls'] = array('controls' => array('tooltip'   => array('icon'   => 'icon-info text-primary',
                                                                                 'anchor' => TRUE,
                                                                                 'class'  => 'tooltip-from-element',
                                                                                 //'url'    => '#',
                                                                                 'data'   => 'data-tooltip-id="tooltip-help-associations"')));
   echo generate_box_open($box_args);

?>

        <div class="control-group">
          <label>Device Association</label>
      <?php
      $item = array('id'          => 'assoc_device_conditions',
                    'name'        => 'Device Association',
                    'placeholder' => TRUE,
                    'class'       => 'col-md-12',
                    'rows'        => 3,
                    'value'       => $vars['assoc_device_conditions']);
      echo(generate_form_element($item, 'textarea'));
      ?>
          </div>
        <div class="control-group">
          <label>Entity Association</label>
      <?php
      $item = array('id'          => 'assoc_entity_conditions',
                    'name'        => 'Entity Association',
                    'placeholder' => TRUE,
                    'class'       => 'col-md-12',
                    'rows'        => 3,
                    'value'       => $vars['assoc_entity_conditions']);
      echo(generate_form_element($item, 'textarea'));
      ?>
        </div>

    <?php echo generate_box_close(); ?>

  </div> <!-- col -->
</div> <!-- row -->

<div class="form-actions">
  <?php
  $item = array('id'          => 'submit',
                'name'        => 'Add Check',
                'class'       => 'btn-success',
                'icon'        => 'icon-plus oicon-white',
                'value'       => 'add_alert_check');
  echo(generate_form_element($item, 'submit'));
  ?>
</div>

</form>

<div id="tooltip-help-conditions" style="display: none;">

      Conditions should be entered in this format
      <pre>metric_1 condition value_1
metric_2 condition value_2
metric_3 condition value_3</pre>

      For example to alert when an enabled port is down
      <pre>ifAdminStatus equals up
ifOperStatus equals down</pre>

</div>

<div id="tooltip-help-associations" style="display: none;">

      Associations should be entered in this format
      <pre>attribute_1 condition value_1
attribute_2 condition value_2
attribute_3 condition value_3</pre>

      For example, to match a network device with core in its hostname
      <pre>type equals network
hostname match *cisco*</pre>

      For example, to match an ethernet port which is connected at 10 gigabit
      <pre>ifType equals ethernetCsmacd
ifSpeed ge 100000000</pre>

      If you put * in either the device or entity fields, it will match all devices or all entities respectively.
</div>

<?php

// EOF
