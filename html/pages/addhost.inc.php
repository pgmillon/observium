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

if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

//echo("<h2>Add Device</h2>");

if ($vars['hostname'])
{
  if ($_SESSION['userlevel'] >= '10')
  {
    $hostname = strip_tags($vars['hostname']);
    $snmp_community = strip_tags($vars['snmp_community']);

    if ($vars['snmp_port'] && is_numeric($vars['snmp_port'])) { $snmp_port = (int)$vars['snmp_port']; } else { $snmp_port = 161; }

    if ($vars['snmp_version'] === "v2c" || $vars['snmp_version'] === "v1")
    {
      if ($vars['snmp_community'])
      {
        $config['snmp']['community'] = array($snmp_community);
      }

      $snmp_version = $vars['snmp_version'];
      print_message("Adding host $hostname communit" . (count($config['snmp']['community']) == 1 ? "y" : "ies") . " "  . implode(', ',$config['snmp']['community']) . " port $snmp_port");
    }
    else if ($vars['snmp_version'] === "v3")
    {
      $snmp_v3 = array (
        'authlevel'  => $vars['snmp_authlevel'],
        'authname'   => $vars['snmp_authname'],
        'authpass'   => $vars['snmp_authpass'],
        'authalgo'   => $vars['snmp_authalgo'],
        'cryptopass' => $vars['snmp_cryptopass'],
        'cryptoalgo' => $vars['snmp_cryptoalgo'],
      );

      array_unshift($config['snmp']['v3'], $snmp_v3);

      $snmp_version = "v3";

      print_message("Adding SNMPv3 host $hostname port $snmp_port");
    } else {
      print_error("Unsupported SNMP Version. There was a dropdown menu, how did you reach this error?"); // We have a hacker!
    }

    if ($vars['ignorerrd'] == 'confirm' || $vars['ignorerrd'] == '1' || $vars['ignorerrd'] == 'on') { $config['rrd_override'] = TRUE; }

    $snmp_options = array();
    if ($vars['ping_skip'] == '1' || $vars['ping_skip'] == 'on') { $snmp_options['ping_skip'] = TRUE; }

    $result = add_device($hostname, $snmp_version, $snmp_port, strip_tags($vars['snmp_transport']), $snmp_options);
    if ($result)
    {
      print_success("Device added (id = $result)");
    }
  } else {
    print_error("You don't have the necessary privileges to add hosts.");
  }
} else {
  // Defaults
  switch ($vars['snmp_version'])
  {
    case 'v1':
    case 'v2c':
    case 'v3':
      $snmp_version = $vars['snmp_version'];
      break;
    default:
      $snmp_version = $config['snmp']['version'];
  }
  if (in_array($vars['snmp_transport'], $config['snmp']['transports']))
  {
    $snmp_transport = $vars['snmp_transport'];
  } else {
    $snmp_transport = $config['snmp']['transports'][0];
  }
}

$page_title[] = "Add Device";

// Add form
$transports = array();
foreach ($config['snmp']['transports'] as $transport)
{
  $transports[$transport] = strtoupper($transport);
}

      $form = array('type'      => 'horizontal',
                    'id'        => 'edit',
                    //'space'     => '20px',
                    //'title'     => 'Add Device',
                    //'icon'      => 'oicon-gear',
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

      //$form['row'][0]['editing']   = array(
      //                                'type'        => 'hidden',
      //                                'value'       => 'yes');
      // left fieldset
      $form['row'][1]['hostname'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Hostname',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['hostname']));
      $form['row'][2]['ping_skip'] = array(
                                      'type'        => 'checkbox',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Skip ping',
                                      'placeholder' => 'Skip ICMP echo checks, only SNMP availability',
                                      'value'       => '');
      $form['row'][3]['snmp_version'] = array(
                                      'type'        => 'select',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Protocol Version',
                                      'width'       => '250px',
                                      'values'      => array('v1' => 'v1', 'v2c' => 'v2c', 'v3' => 'v3'),
                                      'value'       => ($vars['snmp_version'] ? $vars['snmp_version'] : $config['snmp']['version']));
      $form['row'][4]['snmp_transport'] = array(
                                      'type'        => 'select',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Transport',
                                      'width'       => '250px',
                                      'values'      => $transports,
                                      'value'       => ($vars['snmp_transport'] ? $vars['snmp_transport'] : $config['snmp']['transports'][0]));
      $form['row'][5]['snmp_port'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Port',
                                      'placeholder' => '161',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['snmp_port']));
      $form['row'][6]['snmp_timeout'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Timeout',
                                      'placeholder' => '1',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['snmp_timeout']));
      $form['row'][7]['snmp_retries'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Retries',
                                      'placeholder' => '5',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['snmp_retries']));
      $form['row'][8]['ignorerrd'] = array(
                                      'type'        => 'checkbox',
                                      'fieldset'    => 'edit',
                                      'name'        => 'Ignore RRD exist',
                                      'placeholder' => 'Add device anyway if directory with RRDs already exists',
                                      'disabled'    => $config['rrd_override'],
                                      'value'       => $config['rrd_override']);

      // Snmp v1/2c fieldset
      $form['row'][16]['snmp_community'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'snmpv2',
                                      'name'        => 'SNMP Community',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['snmp_community'])); // FIXME. For passwords we should use filter instead escape!

      // Snmp v3 fieldset
      $form['row'][17]['snmp_authlevel'] = array(
                                      'type'        => 'select',
                                      'fieldset'    => 'snmpv3',
                                      'name'        => 'Auth Level',
                                      'width'       => '250px',
                                      'values'      => array('noAuthNoPriv' => 'noAuthNoPriv',
                                                             'authNoPriv'   => 'authNoPriv',
                                                             'authPriv'     => 'authPriv'),
                                      'value'       => $vars['snmp_authlevel']);
      $form['row'][18]['snmp_authname'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'snmpv3',
                                      'name'        => 'Auth Username',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['snmp_authname']));
      $form['row'][19]['snmp_authpass'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'snmpv3',
                                      'name'        => 'Auth Password',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['snmp_authpass'])); // FIXME. For passwords we should use filter instead escape!
      $form['row'][20]['snmp_authalgo'] = array(
                                      'type'        => 'select',
                                      'fieldset'    => 'snmpv3',
                                      'name'        => 'Auth Algorithm',
                                      'width'       => '250px',
                                      'values'      => array('MD5' => 'MD5', 'SHA' => 'SHA'),
                                      'value'       => $vars['snmp_authalgo']);
      $form['row'][21]['snmp_cryptopass'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'snmpv3',
                                      'name'        => 'Crypto Password',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['snmp_cryptopass'])); // FIXME. For passwords we should use filter instead escape!
      $form['row'][22]['snmp_cryptoalgo'] = array(
                                      'type'        => 'select',
                                      'fieldset'    => 'snmpv3',
                                      'name'        => 'Crypto Algorithm',
                                      'width'       => '250px',
                                      'values'      => array('AES' => 'AES', 'DES' => 'DES'),
                                      'value'       => $vars['snmp_cryptoalgo']);

      $form['row'][30]['submit']    = array(
                                      'type'        => 'submit',
                                      'fieldset'    => 'submit',
                                      'name'        => 'Add device',
                                      'icon'        => 'icon-ok icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-primary',
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
