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
    $override_sysContact_bool = $vars['override_sysContact'];
    if (isset($vars['sysContact'])) { $override_sysContact_string  = $vars['sysContact']; }
    $disable_notify  = $vars['disable_notify'];

    if ($override_sysContact_bool) { set_dev_attrib($device, 'override_sysContact_bool', '1'); } else { del_dev_attrib($device, 'override_sysContact_bool'); }
    if (isset($override_sysContact_string)) { set_dev_attrib($device, 'override_sysContact_string', $override_sysContact_string); };
    if ($disable_notify) { set_dev_attrib($device, 'disable_notify', '1'); } else { del_dev_attrib($device, 'disable_notify'); }

    // 2019-12-05 23:30:00

    if (isset($vars['ignore_until']) && $vars['ignore_until_enable'])
    {
      $update['ignore_until'] = $vars['ignore_until'];
      $device['ignore_until'] = $vars['ignore_until'];
    } else {
      $update['ignore_until'] = array('NULL');
      $device['ignore_until'] = '';
    }

    dbUpdate($update, 'devices', '`device_id` = ?', array($device['device_id']));

    $update_message = "Device alert settings updated.";
    $updated = 1;
  }

  if ($updated && $update_message)
  {
    print_message($update_message);
  }
  else if ($update_message)
  {
    print_error($update_message);
  }
}

$override_sysContact_bool = get_dev_attrib($device,'override_sysContact_bool');
$override_sysContact_string = get_dev_attrib($device,'override_sysContact_string');
$disable_notify = get_dev_attrib($device,'disable_notify');

      $form = array('type'      => 'horizontal',
                    'id'        => 'edit',
                    //'space'     => '20px',
                    'title'     => 'Alert Settings',
                    'icon'      => 'oicon-gear',
                    //'class'     => 'box box-solid',
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
                                      'disabled'    => empty($device['ignore_until']),
                                      'min'         => 'current',
                                      'value'       => ($device['ignore_until'] ? $device['ignore_until'] : ''));
      $form['row'][1]['ignore_until_enable'] = array(
                                      'type'        => 'switch',
                                      'readonly'    => $readonly,
                                      'onchange'    => "toggleAttrib('disabled', 'ignore_until')",
                                      'value'       => !empty($device['ignore_until']));

      $form['row'][2]['override_sysContact'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Override sysContact',
                                      //'fieldset'    => 'edit',
                                      'placeholder' => 'Use custom contact below',
                                      'readonly'    => $readonly,
                                      'onchange'    => "toggleAttrib('disabled', 'sysContact')",
                                      'value'       => $override_sysContact_bool);
      $form['row'][3]['sysContact'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Custom contact',
                                      'placeholder' => '',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'disabled'    => !$override_sysContact_bool,
                                      'value'       => escape_html($override_sysContact_string));
      $form['row'][5]['disable_notify'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Disable alerts',
                                      //'fieldset'    => 'edit',
                                      'placeholder' => 'Don\'t send alert mails (<i>but write to eventlog</i>)',
                                      'readonly'    => $readonly,
                                      'value'       => $disable_notify);
      $form['row'][7]['submit']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Save Changes',
                                      'icon'        => 'icon-ok icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-primary',
                                      'readonly'    => $readonly,
                                      'value'       => 'save');

      print_form($form);
      unset($form);

// EOF
