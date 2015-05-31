<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

?>
<h2>Observium User Management</h2>
<?php

include("usermenu.inc.php");
include("includes/javascript-interfacepicker.inc.php");

$pagetitle[] = "Edit user";

if ($_SESSION['userlevel'] != '10') { include("includes/error-no-perm.inc.php"); } else
{
?>

<form method="post" action="" class="form form-inline">
<div class="navbar navbar-narrow">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand">Edit User</a>
      <ul class="nav">

<?php

  $user_list = auth_user_list();

  echo('
         <li>
          <input type="hidden" value="edituser" name="page">
          <select class="selectpicker" name="user_id" onchange="location.href=\'edituser/user_id=\' + this.options[this.selectedIndex].value + \'/\';">');
  if (!isset($vars['user_id'])) { echo('<option value="">Select User</option>'); }

  foreach ($user_list as $user_entry)
  {
    echo("<option value='" . $user_entry['user_id']  . "'");
    if ($user_entry['user_id'] == $vars['user_id']) { echo(' selected '); }
    #echo(" onchange=\"location.href='edituser/user_id=' + this.options[this.selectedIndex].value + '/';\" ");
    echo(">" . $user_entry['username'] . "</option>");
  }

  echo('</select>
      </li>
    </ul>');

  if ($vars['user_id'])
  {
    // Load the user's information
    $user_data = dbFetchRow("SELECT * FROM users WHERE user_id = ?", array($vars['user_id']));

    // Become the selected user. Dirty.
    // FIXME this functionality is currently BROKEN. Commented out the link until we handle this better.
    // echo("<li><a href='edituser/action=becomeuser/user_id=".$vars['user_id']."/'>Become User</a></li>");

    // Delete the selected user.
    if (auth_usermanagement() && $vars['user_id'] !== $_SESSION['user_id'])
    {
      echo('<ul class="nav pull-right">');
      echo('<li><a href="'.generate_url(array('page'=>'edituser', 'action'=>'deleteuser', 'user_id'=>$vars['user_id'])).'"><i class="oicon-cross-button"></i> Delete User</a></li>');
      echo('</ul>');
    }
  }
?>

    </div>
  </div>
</div>
</form>

<?php
  if ($vars['user_id'])
  {
   if ($vars['action'] == "deleteuser")
   {
     include("pages/edituser/deleteuser.inc.php");
   } else {

    // Perform actions if requested

    if (auth_can_change_password($user_data['username']) && $vars['action'] == "changepass")
    {
      if ($_POST['new_pass'] == "" || $_POST['new_pass2'] == "")
      {
        print_warning("Password cannot be blank.");
      }
      elseif ($_POST['new_pass'] == $_POST['new_pass2'])
      {
        auth_change_password($user_data['username'], $_POST['new_pass']);
        print_message("Password Changed.");
      } else {
        print_error("Passwords don't match!");
      }
    }

    // FIXME broken PoS code.
    if ($vars['action'] == "becomeuser")
    {
      $_SESSION['origusername'] = $_SESSION['username'];
      $_SESSION['username'] = $user_data['username'];
      header('Location: '.$config['base_url']);
      dbInsert(array('user' => $_SESSION['origusername'], 'address' => $_SERVER["REMOTE_ADDR"], 'result' => 'Became ' . $_SESSION['username']), 'authlog');

      include("includes/authenticate.inc.php");
    }

    if ($vars['action'] == "perm_del")
    {
      if (dbFetchCell("SELECT COUNT(*) FROM `entity_permissions` WHERE `entity_type` = ? AND `entity_id` = ? AND `user_id` = ?", array($vars['entity_type'], $vars['entity_id'] ,$vars['user_id'])))
      {
        dbDelete('entity_permissions', "`entity_type` = ? AND `entity_id` =  ? AND `user_id` = ?", array($vars['entity_type'], $vars['entity_id'], $vars['user_id']));
      }
    }
    if ($vars['action'] == "perm_add")
    {
      if (!is_array($vars['entity_id'])) { $vars['entity_id'] = array($vars['entity_id']); }
      foreach ($vars['entity_id'] as $entry)
      {
        if (!dbFetchCell("SELECT COUNT(*) FROM `entity_permissions` WHERE `entity_type` = ? AND `entity_id` = ? AND `user_id` = ?", array($vars['entity_type'], $entry, $vars['user_id'])))
        {
          dbInsert(array('entity_id' => $entry, 'entity_type' => $vars['entity_type'], 'user_id' => $vars['user_id']), 'entity_permissions');
        }
      }
    }

?>
  <div class="row">

    <div class="col-md-8">
      <div class="well">
        <h2>User Information</h2>

        <table class="table table-bordered table-striped table-condensed">
          <tr>
            <th>Username</th>
            <td><?php echo($user_data['username']); ?></td>
            <th>User Level</th>
            <td><?php echo($user_data['level']); ?></td>
          </tr>
        </table>
      </div>
    </div>

<?php
    if (auth_can_change_password($vars['user_id'])) // FIXME user_id? function takes username as a parameter, so this can't work!
    {
?>

    <div class="col-md-4">
      <div class="well">
        <form id="edit" name="edit" method="post" class="form-horizontal" action="" style="margin-bottom: 0;">
        <input type="hidden" name='action' value='changepass'>
        <div id="change_password">
          <fieldset>
            <legend>Change Password</legend>
            <div class="control-group">
              <label class="control-label" for="new_pass">New Password</label>
              <div class="controls">
                <input type="password" name="new_pass" size="32" value="">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="new_pass2">Retype Password</label>
              <div class="controls">
                <input type="password" name="new_pass2" size="32" value="">
              </div>
            </div>
          </fieldset>
        </div>
        <div class="form-actions" style="margin-bottom: 0;">
          <button type="submit" class="btn" name="submit" value="save"><i class="oicon-lock-warning"></i> Update Pasword</button>
        </div>
       </form>
     </div>
   </div>
<?php
    }
?>

  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="well">
        <h2>Device Permissions</h2>

<?php

foreach (dbFetchRows("SELECT * FROM `entity_permissions` WHERE `user_id` = ?", array($vars['user_id'])) as $entity)
{
  $user_permissions[$entity['entity_type']][$entity['entity_id']] = TRUE;
}

    // Display devices this users has Permissions to

    if (count($user_permissions['device']))
    {
      foreach (array_keys($user_permissions['device']) as $device_id)
      {

        $device = device_by_id_cache($device_id);

        $devicebtn = '<button class="btn"><i class="oicon-servers"></i> '.generate_device_link($device).'</button>';

        $del_url = generate_url(array('page'=>'edituser', 'action'=>'perm_del', 'user_id'=>$vars['user_id'], 'entity_type'=>'device', 'entity_id'=>$device_id));

        echo '            <div class="btn-group" style="margin-bottom: 5px;">';
        echo '              <button class="btn btn-danger" style="color: #fff;" onclick="location.href=\''.$del_url.'\';"><i class="icon-minus-sign icon-white"></i> Delete</button>';
        echo '              '.$devicebtn;
        echo '            </div><br />';

        $permissions_list[] = $device_perm['device_id'];
        $permdone = "yes";
      }
    } else {
      echo('<div class="alert alert-danger">No permissions</div>');
    }
?>

    <hr />
    <h3>Grant new device Permissions</h3>

    <form method="post" action="">
            <input type="hidden" name="action" value="perm_add">
            <input type="hidden" name="entity_type" value="device">
            <select name="entity_id[]" class="selectpicker" data-width="60%" multiple>

<?php

    $devices = dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`");
    foreach ($devices as $device)
    {
      unset($done);
      foreach ($permissions_list as $ac) { if ($ac == $device['device_id']) { $done = 1; } }
      if (!$done)
      {
        echo("<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>");
      }
    }

?>

          </select>
          <button class="btn pull-right" type="submit" name="Submit" value="Add"><i class="oicon-plus-circle"></i> Add</button>
        </form>
      </div>
    </div>
    <div class="col-md-4">
      <div class="well">
        <h2>Port Permissions</h2>

<?php

    if (count($user_permissions['port']))
    {

      foreach (array_keys($user_permissions['port']) as $entity_id)
      {

      $port   = get_port_by_id($entity_id);
      $device = device_by_id_cache($port['device_id']);

      $emptyCheck = true;
      $count++;

      $devicebtn = '<button class="btn"><i class="oicon-servers"></i> '.generate_device_link($device).'</button>';

      if (empty($port['ifAlias'])) { $portalias = ""; } else { $portalias = " - ".$port['ifAlias'].""; }

      $portbtn = '<button class="btn">'.generate_port_link($port, '<i class="oicon-network-ethernet"></i> '.rewrite_ifname($port['label']).$portalias).'</button>';

      $del_url = generate_url(array('page'=>'edituser', 'action'=>'perm_del', 'user_id'=>$vars['user_id'], 'entity_type'=>'port', 'entity_id'=>$entity_id));

      echo '            <div class="btn-group" style="margin: 5px;">';
      echo '              <button class="btn btn-danger" style="color: #fff;" onclick="location.href=\''.$del_url.'\';"><i class="icon-minus-sign icon-white"></i> Delete</button>';
      echo '              '.$devicebtn;
      echo '              '.$portbtn;
      echo '            </div>';

    }
   } else {
    echo('<div class="alert alert-danger">No permissions</div>');
   }

?>

    <hr />
    <h3>Grant new port permissions</h3>

<?php

    // Display devices this user doesn't have Permissions to

    echo("<form class='form form-inline' action='' method='post'>
        <input type='hidden' name='user_id' value='" . $vars['user_id'] . "'>
        <input type='hidden' name='entity_type' value='port'>
        <input type='hidden' name='action' value='perm_add'>

        <select class='selectpicker' data-width='40%' id='device_id' name='device_id' onchange='getInterfaceList(this, \"port_entity_id\")'>
          <option value=''>Select a device</option>");

    foreach ($devices as $device)
    {
      unset($done);
      foreach ($permissions_list as $ac) { if ($ac == $device['device_id']) { $done = 1; } }
      if (!$done) { echo("<option value='" . $device['device_id']  . "'>" . $device['hostname'] . "</option>"); }
    }

?>

       </select>
       <select class="selectpicker" id='port_entity_id' name='entity_id[]' data-width="40%" multiple>
       </select>
       <button class="btn pull-right" type="submit" name="Submit" value="Add"><i class="oicon-plus-circle"></i> Add</button>
    </form>

      </div>
    </div>

    <div class="col-md-4">
      <div class="well">
        <h2>Bill Permissions</h2>

<?php

    if (count($user_permissions['bill']))
    {
      foreach (array_keys($user_permissions['bill']) as $entity_id)
      {

      $bill = get_bill_by_id($entity_id);

      $button = '<button class="btn"><i class="oicon-servers"></i> '.$bill['bill_name'].'</button>';

      $del_url = generate_url(array('page'=>'edituser', 'action'=>'perm_del', 'user_id'=>$vars['user_id'], 'entity_type'=>'bill', 'entity_id'=>$entity_id));

      echo '            <div class="btn-group" style="margin-bottom: 5px;">';
      echo '              <button class="btn btn-danger" style="color: #fff;" onclick="location.href=\''.$del_url.'\';"><i class="icon-minus-sign icon-white"></i> Delete</button>';
      echo '              '.$button;
      echo '            </div>';

      $bill_Permissions_list[] = $bill_perm['bill_id'];

      $bpermdone = TRUE;
    }
  } else {
    echo('<div class="alert alert-danger">No permissions</div>');
  }

?>

        <hr />
        <h3>Grant new bill access</h3>

<?php
    // Display devices this user doesn't have Permissions to
    echo("<form method='post' action=''>
        <input type='hidden' name='user_id' value='" . $vars['user_id'] . "'>
        <input type='hidden' name='entity_type' value='bill'>
        <input type='hidden' name='action' value='perm_add'>
            <select name='entity_id[]' class='selectpicker' multiple>");

    $bills = dbFetchRows("SELECT * FROM `bills` ORDER BY `bill_name`");
    foreach ($bills as $bill)
    {
      unset($done);
      foreach ($bill_Permissions_list as $ac) { if ($ac == $bill['bill_id']) { $done = 1; } }
      if (!$done)
      {
        echo("<option value='" . $bill['bill_id'] . "'>" . $bill['bill_name'] . "</option>");
      }
    }

?>

          </select>
          <button class="btn pull-right" type="submit" name="Submit" value="Add"><i class="oicon-plus-circle"></i> Add</button>
        </form>
      </div>
    </div>
  </div>

<?php

   }

  }

}

// EOF
