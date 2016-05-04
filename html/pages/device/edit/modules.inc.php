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
include($config['install_dir'] . '/includes/discovery/functions.inc.php');

if ($vars['submit'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    if ($vars['toggle_poller'] && isset($config['poller_modules'][$vars['toggle_poller']]))
    {
      $module = $vars['toggle_poller'];
      if (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] != $config['poller_modules'][$module])
      {
        del_dev_attrib($device, 'poll_' . $module);
      } elseif ($config['poller_modules'][$module] == 0) {
        set_dev_attrib($device, 'poll_' . $module, "1");
      } else {
        set_dev_attrib($device, 'poll_' . $module, "0");
      }
      $attribs = get_dev_attribs($device['device_id']);
    }

    if ($vars['toggle_ports'] && isset($config[$vars['toggle_ports']]) && strpos($vars['toggle_ports'], 'enable_ports_') === 0)
    {
      $module = $vars['toggle_ports'];
      if (isset($attribs[$module]) && $attribs[$module] != $config[$module])
      {
        del_dev_attrib($device, $module);
      } elseif ($config[$module] == 0) {
        set_dev_attrib($device, $module, "1");
      } else {
        set_dev_attrib($device, $module, "0");
      }
      $attribs = get_dev_attribs($device['device_id']);
    }

    if ($vars['toggle_discovery'] && isset($config['discovery_modules'][$vars['toggle_discovery']]))
    {
      $module = $vars['toggle_discovery'];
      if (isset($attribs['discover_'.$module]) && $attribs['discover_'.$module] != $config['discovery_modules'][$module])
      {
        del_dev_attrib($device, 'discover_' . $module);
      } elseif ($config['discovery_modules'][$module] == 0) {
        set_dev_attrib($device, 'discover_' . $module, "1");
      } else {
        set_dev_attrib($device, 'discover_' . $module, "0");
      }
      $attribs = get_dev_attribs($device['device_id']);
    }
  }
}

?>

<div class="row"> <!-- begin row -->

  <div class="col-md-6"> <!-- begin poller options -->

    <div class="box box-solid">

      <div class="box-header with-border">
        <h3 class="box-title">Poller Modules</h3>
      </div>
      <div class="box-body no-padding">

<table class="table table-striped table-condensed">
  <thead>
    <tr>
      <th>Module</th>
      <th style="width: 60px;">Global</th>
      <th style="width: 60px;">Device</th>
      <th style="width: 80px;"></th>
    </tr>
  </thead>
  <tbody>

<?php
foreach (array_merge(array('os' => 1, 'system' => 1), $config['poller_modules']) as $module => $module_status)
{
  $attrib_set = isset($attribs['poll_'.$module]);

  echo('<tr><td><strong>'.$module.'</strong></td><td>');
  echo(($module_status ? '<span class="label label-success">enabled</span>' : '<span class="label label-important">disabled</span>'));
  echo('</td><td>');

  $attrib_status = '<span class="label label-important">disabled</span>';
  $toggle = 'Enable'; $btn_class = 'btn-success'; $btn_icon = 'icon-ok';
  $disabled = FALSE;
  if ($module == 'os' || $module == 'system')
  {
    $attrib_status = '<span class="label label-default">locked</span>';
    $toggle = "Locked"; $btn_class = ''; $btn_icon = 'icon-lock';
    $disabled = TRUE;
  }
  else if (poller_module_excluded($device, $module))
  {
    $attrib_status = '<span class="label label-default">excluded</span>';
    $toggle = "Excluded"; $btn_class = ''; $btn_icon = 'icon-lock';
    $disabled = TRUE;
  }
  else if (($attrib_set && $attribs['poll_'.$module]) || (!$attrib_set && $module_status))
  {
    $attrib_status = '<span class="label label-success">enabled</span>';
    $toggle = "Disable"; $btn_class = "btn-danger"; $btn_icon = 'icon-remove';
  }

  echo($attrib_status.'</td><td>');

      $form = array('type'  => 'simple');
      // Elements
      $form['row'][0]['toggle_poller'] = array('type'    => 'hidden',
                                             'value'    => $module);
      $form['row'][0]['submit']      = array('type'     => 'submit',
                                             'name'     => $toggle,
                                             'class'    => 'btn-mini '.$btn_class,
                                             'icon'     => $btn_icon,
                                             'right'    => TRUE,
                                             'readonly' => $readonly,
                                             'disabled' => $disabled,
                                             'value'    => 'Toggle');
      print_form($form); unset($form);

  echo('</td></tr>');
}
?>
  </tbody>
</table>

  </div> </div>
</div> <!-- end poller options -->

<div class="col-md-6"> <!-- begin ports options -->

    <div class="box box-solid">

      <div class="box-header with-border">
        <h3 class="box-title">Ports polling options</h3>
      </div>
      <div class="box-body no-padding">

<table class="table table-striped table-condensed">
  <thead>
    <tr>
      <th>Module</th>
      <th style="width: 60px;">Global</th>
      <th style="width: 60px;">Device</th>
      <th style="width: 80px;"></th>
    </tr>
  </thead>
  <tbody>

<?php
foreach (array_keys($config) as $module)
{
  if (strpos($module, 'enable_ports_') === FALSE) { continue; }

  $module_status = $config[$module];
  $attrib_set = isset($attribs[$module]);

  echo('<tr><td><strong>'.str_replace('enable_ports_', '', $module).'</strong></td><td>');
  echo(($module_status ? '<span class="label label-success">enabled</span>' : '<span class="label label-important">disabled</span>'));
  echo('</td><td>');

  $attrib_status = '<span class="label label-important">disabled</span>';
  $toggle = 'Enable'; $btn_class = 'btn-success'; $btn_icon = 'icon-ok';
  $disabled = FALSE;
  if ($module == 'enable_ports_junoseatmvp' && $device['os'] != 'junose') /// FIXME. see here includes/discovery/junose-atm-vp.inc.php
  {
    $attrib_status = '<span class="label label-default">excluded</span>';
    $toggle = "Excluded"; $btn_class = ''; $btn_icon = 'icon-lock';
    $disabled = TRUE;
  }
  else if (discovery_module_excluded($device, $module)) // What? This is ports options..
  {
    $attrib_status = '<span class="label label-disabled">excluded</span>';
    $toggle = "Excluded"; $btn_class = ''; $btn_icon = 'icon-lock';
    $disabled = TRUE;
  }
  else if (($attrib_set && $attribs[$module]) || (!$attrib_set && $module_status))
  {
    $attrib_status = '<span class="label label-success">enabled</span>';
    $toggle = "Disable"; $btn_class = "btn-danger"; $btn_icon = 'icon-remove';
  }

  echo($attrib_status . '</td><td>');

      $form = array('type'  => 'simple');
      // Elements
      $form['row'][0]['toggle_ports'] = array('type'    => 'hidden',
                                             'value'    => $module);
      $form['row'][0]['submit']      = array('type'     => 'submit',
                                             'name'     => $toggle,
                                             'class'    => 'btn-mini '.$btn_class,
                                             'icon'     => $btn_icon,
                                             'right'    => TRUE,
                                             'readonly' => $readonly,
                                             'disabled' => $disabled,
                                             'value'    => 'Toggle');
      print_form($form); unset($form);

  echo('</td></tr>');
}
?>
  </tbody>
</table>

  </div> </div>
</div> <!-- end ports options -->

<div class="col-md-6"> <!-- begin discovery options -->

    <div class="box box-solid">

      <div class="box-header with-border">
        <h3 class="box-title">Discovery Modules</h3>
      </div>
      <div class="box-body no-padding">

<table class="table table-striped table-condensed">
  <thead>
    <tr>
      <th>Module</th>
      <th style="width: 60px;">Global</th>
      <th style="width: 60px;">Device</th>
      <th style="width: 80px;"></th>
    </tr>
  </thead>
  <tbody>

<?php
foreach ($config['discovery_modules'] as $module => $module_status)
{
  $attrib_set = isset($attribs['discover_'.$module]);

  echo('<tr><td><strong>'.$module.'</strong></td><td>');
  echo(($module_status ? '<span class="label label-success">enabled</span>' : '<span class="label label-important">disabled</span>'));
  echo('</td><td>');

  $attrib_status = '<span class="label label-important">disabled</span>';
  $toggle = 'Enable'; $btn_class = 'btn-success'; $btn_icon = 'icon-ok';
  $disabled = FALSE;
  if (discovery_module_excluded($device,$module))
  {
    $attrib_status = '<span class="label label-disabled">excluded</span>';
    $toggle = "Excluded"; $btn_class = ''; $btn_icon = 'icon-lock';
    $disabled = TRUE;
  }
  else if (($attrib_set && $attribs['discover_'.$module]) || (!$attrib_set && $module_status))
  {
    $attrib_status = '<span class="label label-success">enabled</span>';
    $toggle = "Disable"; $btn_class = "btn-danger"; $btn_icon = 'icon-remove';
  }

  echo($attrib_status . '</td><td>');

      $form = array('type'  => 'simple');
      // Elements
      $form['row'][0]['toggle_discovery'] = array('type'    => 'hidden',
                                             'value'    => $module);
      $form['row'][0]['submit']      = array('type'     => 'submit',
                                             'name'     => $toggle,
                                             'class'    => 'btn-mini '.$btn_class,
                                             'icon'     => $btn_icon,
                                             'right'    => TRUE,
                                             'readonly' => $readonly,
                                             'disabled' => $disabled,
                                             'value'    => 'Toggle');
      print_form($form); unset($form);

  echo('</td></tr>');
}
?>
  </tbody>
</table>

  </div> </div>
</div> <!-- end discovery options -->

  </div> <!-- end row -->
<?php

// EOF
