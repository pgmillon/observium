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

if ($vars['editing'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    $wmi_override = $vars['wmi_override'];
    if ($wmi_override)
    {
      $wmi_hostname = $vars['wmi_hostname'];
      $wmi_domain   = $vars['wmi_domain'];
      $wmi_username = $vars['wmi_username'];
      $wmi_password = $vars['wmi_password'];
    }

    if ($wmi_override)         { set_dev_attrib($device, 'wmi_override', $wmi_override); } else { del_dev_attrib($device, 'wmi_override'); }
    if (!empty($wmi_hostname)) { set_dev_attrib($device, 'wmi_hostname', $wmi_hostname); } else { del_dev_attrib($device, 'wmi_hostname'); }
    if (!empty($wmi_domain))   { set_dev_attrib($device, 'wmi_domain', $wmi_domain); } else { del_dev_attrib($device, 'wmi_domain'); }
    if (!empty($wmi_username)) { set_dev_attrib($device, 'wmi_username', $wmi_username); } else { del_dev_attrib($device, 'wmi_username'); }
    if (!empty($wmi_password)) { set_dev_attrib($device, 'wmi_password', $wmi_password); } else { del_dev_attrib($device, 'wmi_password'); }

    $update_message = "Device WMI data updated.";
    $updated = 1;

    if ($vars['toggle_poller'] && isset($GLOBALS['config']['wmi']['modules'][$vars['toggle_poller']]))
    {
      $module = $vars['toggle_poller'];
      if (isset($attribs['wmi_poll_'.$module]) && $attribs['wmi_poll_'.$module] != $GLOBALS['config']['wmi']['modules'][$vars['toggle_poller']])
      {
        del_dev_attrib($device, 'wmi_poll_' . $module);
      } elseif ($GLOBALS['config']['wmi']['modules'][$vars['toggle_poller']] == 0) {
        set_dev_attrib($device, 'wmi_poll_' . $module, "1");
      } else {
        set_dev_attrib($device, 'wmi_poll_' . $module, "0");
      }
      $attribs = get_dev_attribs($device['device_id']);
    }
  }
}

?>

<script type="text/javascript">
  $(document).ready(function() {
    toggleDisable();
    $("#wmi_override").change(function() {
      toggleDisable();
    });
  });

  function toggleDisable() {
    if (!$("#wmi_override").is(":checked"))
    {
      $('#edit input[type=text], #edit input[type=password]').prop("disabled", true);
    }
    else
    {
      $('#edit input[type=text], #edit input[type=password]').prop("disabled", false);
    }
  }
</script>
<div class="row">
  <div class="col-md-6">
    <div class="box box-solid">
    <div class="box-header with-border">
      <!-- <i class="oicon-lock-warning"></i> --><h3 class="box-title">WMI Authentication</h3>
    </div>
    <div class="box-body" style="padding-top: 10px;">
      <form id="edit" name="edit" method="post" action="" class="form-horizontal">
        <fieldset>
          <input type="hidden" name="editing" value="yes">
          <div class="control-group">
            <label class="control-label" for="wmi_override">Override WMI Config</label>
            <div class="controls">
              <input type="checkbox" id="wmi_override" name="wmi_override" <?php if (get_dev_attrib($device,'wmi_override')) { echo(' checked="1"'); } ?> />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_hostname">WMI Hostname</label>
            <div class="controls">
              <input name="wmi_hostname" type="text" size="32" value="<?php echo(escape_html(get_dev_attrib($device,'wmi_hostname'))); ?>" />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_domain">WMI Domain</label>
            <div class="controls">
              <input name="wmi_domain" type="text" size="32" value="<?php echo(escape_html(get_dev_attrib($device,'wmi_domain'))); ?>" />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_username">WMI Username</label>
            <div class="controls">
              <input name="wmi_username" type="text" size="32" value="<?php echo(escape_html(get_dev_attrib($device,'wmi_username'))); ?>" />
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="wmi_password">WMI Password</label>
            <div class="controls">
              <input name="wmi_password" type="password" size="32" value="<?php echo(escape_html(get_dev_attrib($device,'wmi_password'))); // FIXME. For passwords we should use filter instead escape! ?>" />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" name="submit" value="save"><i class="icon-ok icon-white"></i> Save Changes</button>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
  </div>
  <div class="col-md-6">
    <div class="box box-solid">
    <div class="box-header with-border">
      <!-- <i class="oicon-gear"></i> --><h3 class="box-title">WMI Poller Modules</h3>
    </div>
    <div class="box-body no-padding">
      <table class="table  table-striped table-condensed ">
        <thead>
        <tr>
          <th>Module</th>
          <th style="width: 80;">Global</th>
          <th style="width: 80;">Device</th>
          <th style="width: 80;"></th>
        </tr>
        </thead>
        <tbody>
<?php

foreach ($GLOBALS['config']['wmi']['modules'] as $module => $module_status)
{
  echo('<tr><td><b>'.$module.'</b></td><td>');

  echo(($module_status ? '<span class="label label-success">enabled</span>' : '<span class="label label-important">disabled</span>' ));

  echo('</td><td>');

  if (isset($attribs['wmi_poll_'.$module]))
  {
    if ($attribs['wmi_poll_'.$module]) { echo('<span class="label label-success">enabled</span>'); $toggle = "Disable"; $btn_class = "btn-danger";
    } else { echo('<span class="label label-important">disabled</span>'); $toggle = "Enable"; $btn_class = "btn-success";}
  } else {
    if ($module_status) { echo('<span class="label label-success">enabled</span>'); $toggle = "Disable"; $btn_class = "btn-danger";
    } else { echo('<span class="label label-important">disabled</span>'); $toggle = "Enable"; $btn_class = "btn-success";}
  }

  echo('</td><td>');
  
        $form = array('type'  => 'simple');
      // Elements
      $form['row'][0]['toggle_poller']  = array('type'     => 'hidden',
                                             'value'    => $module);
      $form['row'][0]['editing']      = array('type'     => 'submit',
                                             'name'     => $toggle,
                                             'class'    => 'btn-mini '.$btn_class,
                                             //'icon'     => $btn_icon,
                                             'right'    => TRUE,
                                             'readonly' => $readonly,
                                             'value'    => 'toggle_poller');
      print_form($form); unset($form);

  echo('</td></tr>');
}

?>
        </tbody>
      </table>
    </div>
    </div>
  </div>
</div>
<?php

// EOF
