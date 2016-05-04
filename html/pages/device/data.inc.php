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

  $export_device = $device;
  if ($config['snmp']['hide_auth'])
  {
    $params = array('snmp_community', 'snmp_authlevel', 'snmp_authname', 'snmp_authpass', 'snmp_authalgo', 'snmp_cryptopass', 'snmp_cryptoalgo');
    foreach ($params as $param)
    {
      if (strlen($export_device[$param])) { $export_device[$param] = '***'; }
    }
  }

  if ($vars['saveas'] == 'yes' && $vars['filename'])
  {
    download_as_file(gzencode(_json_encode($export_device)), $vars['filename']);
  } else {
    if ($config['snmp']['hide_auth'])
    {
      print_warning("NOTE, <strong>\$config['snmp']['hide_auth']</strong> is set to <strong>TRUE</strong>, snmp community and snmp v3 auth hidden from output and export.");
    } else {
      print_error("WARNING, <strong>\$config['snmp']['hide_auth']</strong> is set to <strong>FALSE</strong>, snmp community and snmp v3 auth <strong>NOT hidden</strong> from output and export.");
    }
    $form = array('type'  => 'rows',
                  'space' => '10px',
                  'url'   => generate_url($vars));
    // Filename
    $form['row'][0]['filename'] = array(
                                    'type'        => 'text',
                                    'name'        => 'Filename',
                                    'value'       => $device['hostname'] . '.json.txt.gz',
                                    //'div_class'   => 'col-md-8',
                                    'width'       => '100%',
                                    'placeholder' => TRUE);
    // Compress
    //$form['row'][0]['compress'] = array(
    //                                'type'        => 'switch',
    //                                'value'       => 1);
    // Search button
    $form['row'][0]['saveas']   = array(
                                    'type'        => 'submit',
                                    'name'        => 'Export',
                                    'icon'        => 'icon-save',
                                    'right'       => TRUE,
                                    'value'       => 'yes'
                                    );
    print_form($form);

    r($export_device);
  }

  unset($export_device, $params, $param);

// EOF
