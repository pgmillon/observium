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

$user_data = array('user_id' => $_SESSION['user_id'], 'level' => $_SESSION['userlevel']);
humanize_user($user_data);
//r($user_data);

?>

<div class="row">

  <div class="col-lg-6">
<?php

  echo generate_box_open(array('header-border' => TRUE, 'title' => 'Access Keys'));

?>

  <table class="table table-striped table-condensed">
    <tr>
      <td>RSS/Atom access key</td>
<?php
  // Warn about lack of mcrypt unless told not to.
  if (!check_extension_exists('mcrypt'))
  {
    echo('<td colspan="2"><span class="text text-danger">To use RSS/Atom feeds the PHP mcrypt module is required.</span></td>');
  }
  else if (!check_extension_exists('SimpleXML'))
  {
    echo('<td colspan="2"><span class="text text-danger">To use RSS/Atom feeds the PHP SimpleXML module is required.</span></td>');
  } else {
    echo("      <td>RSS/Atom access key created $atom_key_updated.</td>");
    echo('      <td>');

    $form = array('type'  => 'simple');
    // Elements
    $form['row'][0]['key_type'] = array('type'     => 'hidden',
                                        'value'    => 'atom');
    $form['row'][0]['atom_key'] = array('type'     => 'submit',
                                        'name'     => 'Reset',
                                        'icon'     => '',
                                        'class'    => 'btn-mini btn-success',
                                        'value'    => 'toggle');
    print_form($form); unset($form);

    echo('</td>');
  }
?>
    </tr>
    <tr>
      <td colspan=3 style="padding: 0px; border: 0px none;"></td> <!-- hidden row -->
    </tr>
    <tr>
      <td>API access key</td>
<?php
    echo("      <td>API access key created $api_key_updated.</td>");
    echo('      <td>');

    $form = array('type'  => 'simple');
    // Elements
    $form['row'][0]['key_type'] = array('type'     => 'hidden',
                                        'value'    => 'api');
    $form['row'][0]['api_key']  = array('type'     => 'submit',
                                        'name'     => 'Reset',
                                        'icon'     => '',
                                        'class'    => 'btn-mini btn-success',
                                        'disabled' => TRUE, // Not supported for now
                                        'value'    => 'toggle');
    print_form($form); unset($form);

    echo('</td>');
?>
    </tr>
  </table>

<?php
  echo generate_box_close();
?>

  </div>

  <div class="col-lg-6 pull-right">

<?php

if (is_flag_set(OBS_PERMIT_ACCESS, $user_data['permission']) && !is_flag_set(OBS_PERMIT_ALL ^ OBS_PERMIT_ACCESS, $user_data['permission']))
{
  // if user has access and not has read/secure read/edit use individual permissions
  echo generate_box_open();

  // Cache user permissions
  foreach (dbFetchRows("SELECT * FROM `entity_permissions` WHERE `user_id` = ?", array($user_data['user_id'])) as $entity)
  {
    $user_permissions[$entity['entity_type']][$entity['entity_id']] = TRUE;
  }

  // Start bill Permissions
  if (isset($config['enable_billing']) && $config['enable_billing'] && count($user_permissions['bill']))
  {
    // Display info about user bill permissions, only if user has is
    echo generate_box_open(array('header-border' => TRUE, 'title' => 'Bill Permissions'));
    //if (count($user_permissions['bill']))
    //{
      echo('<table class="'.OBS_CLASS_TABLE.'">' . PHP_EOL);

      foreach ($user_permissions['bill'] as $bill_id => $status)
      {
        $bill = get_bill_by_id($bill_id);

        echo('<tr><td style="width: 1px;"></td>
                  <td style="overflow: hidden;"><i class="'.$config['entities']['bill']['icon'].'"></i> '.$bill['bill_name'].'
                  <small>' . $bill['bill_type'] . '</small></td>
                </tr>');
      }
      echo('</table>' . PHP_EOL);

    //} else {
    //  print_warning("This user currently has no permitted bills");
    //}

    echo generate_box_close();
  }
  // End bill permissions

  // Start group permissions
  if (OBSERVIUM_EDITION != 'community')
  {
    echo generate_box_open(array('header-border' => TRUE, 'title' => 'Group Permissions'));

    if (count($user_permissions['group']))
    {
      echo('<table class="'.OBS_CLASS_TABLE.'">' . PHP_EOL);

      foreach ($user_permissions['group'] as $group_id => $status)
      {
        $group = get_group_by_id($group_id);

        echo('<tr><td style="width: 1px;"></td>
                  <td style="overflow: hidden;"><i class="'.$config['entities'][$group['entity_type']]['icon'].'"></i> '.generate_entity_link('group', $group).'
                  <small>' . $group['group_descr'] . '</small></td>
              </tr>' . PHP_EOL);
      }
      echo('</table>' . PHP_EOL);
    } else {
      print_warning("This user currently has no permitted groups");
    }

    echo generate_box_close();
  }
  // End group permissions

  // Start device permissions
  echo generate_box_open(array('header-border' => TRUE, 'title' => 'Device Permissions'));

  if (count($user_permissions['device']))
  {
    echo('<table class="'.OBS_CLASS_TABLE.'">' . PHP_EOL);

    foreach ($user_permissions['device'] as $device_id => $status)
    {
      $device = device_by_id_cache($device_id);

      echo('<tr><td style="width: 1px;"></td>
                <td style="overflow: hidden;"><i class="'.$config['entities']['device']['icon'].'"></i> '.generate_device_link($device).'
                <small>' . $device['location'] . '</small></td>
              </tr>');
    }
    echo('</table>' . PHP_EOL);

  } else {
    print_warning("This user currently has no permitted devices");
  }

  echo generate_box_close();
  // End devices permissions

  // Start port permissions
  echo generate_box_open(array('header-border' => TRUE, 'title' => 'Port Permissions'));
  if (count($user_permissions['port']))
  {
    echo('<table class="'.OBS_CLASS_TABLE.'">' . PHP_EOL);

    foreach (array_keys($user_permissions['port']) as $entity_id)
    {
      $port   = get_port_by_id($entity_id);
      $device = device_by_id_cache($port['device_id']);

      echo('<tr><td style="width: 1px;"></td>
                <td style="width: 200px; overflow: hidden;"><i class="'.$config['entities']['device']['icon'].'"></i> '.generate_entity_link('device', $device).'</td>
                <td style="overflow: hidden;"><i class="'.$config['entities']['port']['icon'].'"></i> '.generate_entity_link('port', $port).'
                <small>' . $port['ifDescr'] . '</small></td>
              </tr>');
    }
    echo('</table>' . PHP_EOL);

  } else {
    print_warning('This user currently has no permitted ports');
  }

  echo generate_box_close();
  // End port permissions

  // Start sensor permissions
  echo generate_box_open(array('header-border' => TRUE, 'title' => 'Sensor Permissions'));
  if (count($user_permissions['sensor']))
  {
    echo('<table class="'.OBS_CLASS_TABLE.'">' . PHP_EOL);

    foreach (array_keys($user_permissions['sensor']) as $entity_id)
    {
      $sensor   = get_entity_by_id_cache('sensor', $entity_id);
      $device   = device_by_id_cache($sensor['device_id']);

      echo('<tr><td style="width: 1px;"></td>
                  <td style="width: 200px; overflow: hidden;"><i class="'.$config['entities']['device']['icon'].'"></i> '.generate_entity_link('device', $device).'</td>
                  <td style="overflow: hidden;"><i class="'.$config['entities']['sensor']['icon'].'"></i> '.generate_entity_link('sensor', $sensor).'
                  <td width="25">
                </tr>');
    }
    echo('</table>' . PHP_EOL);

  } else {
    print_warning('This user currently has no permitted sensors');
  }

  echo generate_box_close();
  // End sensor permissions

} else {
  // All not normal users
  echo generate_box_open(array('header-border' => TRUE, 'title' => 'Permissions', 'padding' => TRUE, 'body-style' => 'padding-bottom: 0px;'));
  print_warning($user_data['subtext']);
}
echo generate_box_close();

?>

  </div>

</div> <!-- end row -->

<?php

// EOF
