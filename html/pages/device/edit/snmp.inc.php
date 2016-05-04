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

$ok = FALSE;
if ($vars['editing'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    $update = array();
    switch ($vars['snmp_version'])
    {
      case 'v3':
        switch ($vars['snmp_authlevel'])
        {
          case 'authPriv':
            if ($vars['snmp_cryptoalgo'] == 'DES' || $vars['snmp_cryptoalgo'] == 'AES')
            {
              $ok = TRUE;
              $update['snmp_cryptoalgo'] = $vars['snmp_cryptoalgo'];
              $update['snmp_cryptopass'] = $vars['snmp_cryptopass'];
            }
            // no break here
          case 'authNoPriv':
            if ($vars['snmp_authalgo'] == 'MD5' || $vars['snmp_authalgo'] == 'SHA')
            {
              $ok = TRUE;
              $update['snmp_authalgo']   = $vars['snmp_authalgo'];
              $update['snmp_authname']   = $vars['snmp_authname'];
              $update['snmp_authpass']   = $vars['snmp_authpass'];
            } else {
              $ok = FALSE;
            }
            break;
          case 'noAuthNoPriv':
            $ok = TRUE;
            break;
        }
        if ($ok) { $update['snmp_authlevel'] = $vars['snmp_authlevel']; }
        break;
      case 'v2c':
      case 'v1':
        if (is_string($vars['snmp_community']))
        {
          $ok = TRUE;
          $update['snmp_community'] = $vars['snmp_community'];
        }
        break;
    }
    if ($ok)
    {
      $update['snmp_version'] = $vars['snmp_version'];
      if (in_array($vars['snmp_transport'], $config['snmp']['transports']))
      {
        $update['snmp_transport'] = $vars['snmp_transport'];
      } else {
        $update['snmp_transport'] = 'udp';
      }
      if (is_numeric($vars['snmp_port']) && $vars['snmp_port'] > 0 && $vars['snmp_port'] <= 65535)
      {
        $update['snmp_port'] = (int)$vars['snmp_port'];
      } else {
        $update['snmp_port'] = 161;
      }
      if (is_numeric($vars['snmp_timeout']) && $vars['snmp_timeout'] > 0 && $vars['snmp_timeout'] <= 120)
      {
        $update['snmp_timeout'] = (int)$vars['snmp_timeout'];
      } else {
        $update['snmp_timeout'] = array('NULL');
      }
      if (is_numeric($vars['snmp_retries']) && $vars['snmp_retries'] > 0 && $vars['snmp_retries'] <= 10)
      {
        $update['snmp_retries'] = (int)$vars['snmp_retries'];
      } else {
        $update['snmp_retries'] = array('NULL');
      }

      if (dbUpdate($update, 'devices', '`device_id` = ?', array($device['device_id'])))
      {
        print_success("Device SNMP configuration updated");
        log_event('Device SNMP configuration changed.', $device['device_id'], 'device', $device['device_id'], 5);
      } else {
        print_warning("Device SNMP configuration update is not required");
      }
    }
    if (!$ok) { print_error("Device SNMP configuration not updated"); }

    unset($update);
  }
}

$device = device_by_id_cache($device['device_id'], $ok);
$transports = array();
foreach ($config['snmp']['transports'] as $transport)
{
  $transports[$transport] = strtoupper($transport);
}

$form = array('type'      => 'horizontal',
              'id'        => 'edit',
              //'space'     => '20px',
              //'title'     => 'General',
              //'icon'      => 'oicon-gear',
              //'class'     => 'box',
              );
// top row div
$form['fieldset']['edit']    = array('div'   => 'top',
                                     'title' => 'Basic Configuration',
                                     'icon'  => 'oicon-gear',
                                     'class' => 'col-md-6');
$form['fieldset']['snmpv2']  = array('div'   => 'top',
                                     'title' => 'Authentication Configuration',
                                     'icon'  => 'oicon-lock-warning',
                                     //'right' => TRUE,
                                     'class' => 'col-md-6 col-md-pull-0');
$form['fieldset']['snmpv3']  = array('div'   => 'top',
                                     'title' => 'Authentication Configuration',
                                     'icon'  => 'oicon-lock-warning',
                                     //'right' => TRUE,
                                     'class' => 'col-md-6 col-md-pull-0');
// bottom row div
$form['fieldset']['submit']  = array('div'   => 'bottom',
                                     'style' => 'padding: 0px;',
                                     'class' => 'col-md-12');

$form['row'][0]['editing']   = array(
                                'type'        => 'hidden',
                                'value'       => 'yes');
// left fieldset
$form['row'][1]['snmp_version'] = array(
                                'type'        => 'select',
                                'fieldset'    => 'edit',
                                'name'        => 'Protocol Version',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'values'      => array('v1' => 'v1', 'v2c' => 'v2c', 'v3' => 'v3'),
                                'value'       => $device['snmp_version']);
$form['row'][2]['snmp_transport'] = array(
                                'type'        => 'select',
                                'fieldset'    => 'edit',
                                'name'        => 'Transport',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'values'      => $transports,
                                'value'       => $device['snmp_transport']);
$form['row'][3]['snmp_port'] = array(
                                'type'        => 'text',
                                'fieldset'    => 'edit',
                                'name'        => 'Port',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'value'       => escape_html($device['snmp_port']));
$form['row'][4]['snmp_timeout'] = array(
                                'type'        => 'text',
                                'fieldset'    => 'edit',
                                'name'        => 'Timeout',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'value'       => escape_html($device['snmp_timeout']));
$form['row'][5]['snmp_retries'] = array(
                                'type'        => 'text',
                                'fieldset'    => 'edit',
                                'name'        => 'Retries',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'value'       => escape_html($device['snmp_retries']));
// Snmp v1/2c fieldset
$form['row'][6]['snmp_community'] = array(
                                'type'        => 'password',
                                'fieldset'    => 'snmpv2',
                                'name'        => 'SNMP Community',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'show_password' => !$readonly,
                                'value'       => escape_html($device['snmp_community'])); // FIXME. For passwords we should use filter instead escape!

// Snmp v3 fieldset
$form['row'][7]['snmp_authlevel'] = array(
                                'type'        => 'select',
                                'fieldset'    => 'snmpv3',
                                'name'        => 'Auth Level',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'values'      => array('noAuthNoPriv' => 'noAuthNoPriv',
                                                       'authNoPriv'   => 'authNoPriv',
                                                       'authPriv'     => 'authPriv'),
                                'value'       => $device['snmp_authlevel']);
$form['row'][8]['snmp_authname'] = array(
                                'type'        => 'password',
                                'fieldset'    => 'snmpv3',
                                'name'        => 'Auth Username',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'value'       => escape_html($device['snmp_authname']));
$form['row'][9]['snmp_authpass'] = array(
                                'type'        => 'password',
                                'fieldset'    => 'snmpv3',
                                'name'        => 'Auth Password',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'show_password' => !$readonly,
                                'value'       => escape_html($device['snmp_authpass'])); // FIXME. For passwords we should use filter instead escape!
$form['row'][10]['snmp_authalgo'] = array(
                                'type'        => 'select',
                                'fieldset'    => 'snmpv3',
                                'name'        => 'Auth Algorithm',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'values'      => array('MD5' => 'MD5', 'SHA' => 'SHA'),
                                'value'       => $device['snmp_authalgo']);
$form['row'][11]['snmp_cryptopass'] = array(
                                'type'        => 'password',
                                'fieldset'    => 'snmpv3',
                                'name'        => 'Crypto Password',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'show_password' => !$readonly,
                                'value'       => escape_html($device['snmp_cryptopass'])); // FIXME. For passwords we should use filter instead escape!
$form['row'][12]['snmp_cryptoalgo'] = array(
                                'type'        => 'select',
                                'fieldset'    => 'snmpv3',
                                'name'        => 'Crypto Algorithm',
                                'width'       => '250px',
                                'readonly'    => $readonly,
                                'values'      => array('AES' => 'AES', 'DES' => 'DES'),
                                'value'       => $device['snmp_cryptoalgo']);

$form['row'][20]['submit']    = array(
                                'type'        => 'submit',
                                'fieldset'    => 'submit',
                                'name'        => 'Save Changes',
                                'icon'        => 'icon-ok icon-white',
                                //'right'       => TRUE,
                                'class'       => 'btn-primary',
                                'readonly'    => $readonly,
                                'value'       => 'save');

print_form_box($form);
unset($form);

?>

<script type="text/javascript">
<!--
$("#snmp_version").change(function() {
   var select = this.value;
        if (select === 'v3') {
            $('#snmpv3').show();
            $("#snmpv2").hide();
        } else {
            $('#snmpv2').show();
            $('#snmpv3').hide();
        }
}).change();

$("#snmp_authlevel").change(function() {
  var select = this.value;
  if (select === 'authPriv') {
    $('[id^="snmp_authname"]').show();
    $('[id^="snmp_authpass"]').show();
    $('[id^="snmp_authalgo"]').show();
    $('[id^="snmp_cryptopass"]').show();
    $('[id^="snmp_cryptoalgo"]').show();
  } else if (select === 'authNoPriv') {
    $('[id^="snmp_authname"]').show();
    $('[id^="snmp_authpass"]').show();
    $('[id^="snmp_authalgo"]').show();
    $('[id^="snmp_cryptopass"]').hide();
    $('[id^="snmp_cryptoalgo"]').hide();
  } else {
    $('[id^="snmp_authname"]').hide();
    $('[id^="snmp_authpass"]').hide();
    $('[id^="snmp_authalgo"]').hide();
    $('[id^="snmp_cryptopass"]').hide();
    $('[id^="snmp_cryptoalgo"]').hide();
  }
}).change();
// -->
</script>

<?php

// EOF
