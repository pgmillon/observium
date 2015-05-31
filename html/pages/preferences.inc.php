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

$pagetitle[] = "User preferences";

// Change password
if ($_POST['password'] == "save")
{
  if (authenticate($_SESSION['username'],$_POST['old_pass']))
  {
    if ($_POST['new_pass'] == "" || $_POST['new_pass2'] == "")
    {
      print_warning("Password must not be blank.");
    }
    elseif ($_POST['new_pass'] == $_POST['new_pass2'])
    {
      auth_change_password($_SESSION['username'], $_POST['new_pass']);
      print_success("Password Changed.");
    }
    else
    {
      print_warning("Passwords don't match.");
    }
  } else {
    print_warning("Incorrect password");
  }
}

unset($prefs);
if (is_numeric($_SESSION['user_id']))
{
  $user_id = $_SESSION['user_id'];
  $prefs = get_user_prefs($user_id);

  // Reset RSS/Atom key
  if ($_POST['atom_key'] == "toggle")
  {
    if (set_user_pref($user_id, 'atom_key', md5(strgen())))
    {
      print_success('RSS/Atom key reset.');
      $prefs = get_user_prefs($user_id);
    } else {
      print_error('Error generating RSS/Atom key.');
    }
  }

  // Reset API key
  if ($_POST['api_key'] == "toggle")
  {
    if (set_user_pref($user_id, 'api_key', md5(strgen())))
    {
      print_success('API key reset.');
      $prefs = get_user_prefs($user_id);
    } else {
      print_error('Error generating API key.');
    }
  }
}
$atom_key_updated = (isset($prefs['atom_key']['updated']) ? formatUptime(time() - strtotime($prefs['atom_key']['updated']), 'shorter').' ago' : 'Never');
$api_key_updated  = (isset($prefs['api_key']['updated'])  ? formatUptime(time() - strtotime($prefs['api_key']['updated']),  'shorter').' ago' : 'Never');
?>

<form id="edit" name="edit" method="post" class="form-horizontal" action="">
<fieldset>
  <legend>User Preferences</legend>
</fieldset>
<div class="row">
<?php
if (auth_can_change_password($_SESSION['username']))
{
  ?>
  <div class="col-md-6">
  <div class="well info_box">
    <div class="title"><i class="oicon-gear"></i> Change Password</div>
    <fieldset>

      <div class="control-group">
        <label class="control-label" for="old_pass">Old Password</label>
        <div class="controls">
          <input type="password" name="old_pass" autocomplete="off" size="32" />
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="new_pass">New Password</label>
        <div class="controls">
          <input type="password" name="new_pass" autocomplete="off" size="32" />
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="new_pass2">New Password (repeat)</label>
        <div class="controls">
          <input type="password" name="new_pass2" autocomplete="off" size="32" />
        </div>
      </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" name="password" value="save"><i class="icon-ok icon-white"></i> Save Password</button>
    </div>
    </fieldset>
 </div>
</div>
<?php
}

if     ($_SESSION['userlevel'] == 10) { $user_device = '<strong class="text text-success">Global Administrative Access</strong>'; }
elseif ($_SESSION['userlevel'] < 10 && $_SESSION['userlevel'] >= 5) { $user_device = '<strong class="text text-info">Global Viewing Access</strong>'; }
elseif ($_SESSION['userlevel'] < 5)
{
  $user_device = '';
  foreach (dbFetchRows("SELECT * FROM `entity_permissions` AS P, `devices` AS D WHERE `user_id` = ? AND P.entity_id = D.device_id", array($_SESSION['user_id'])) as $perm)
  {
    $user_device .= generate_device_link($perm).'<br />'.PHP_EOL;
    $dev_access = 1;
  }

  if (!$dev_access) { $user_device = "No access!"; }
}

?>

  <div class="col-lg-6 pull-right">
  <div class="well info_box">
  <div class="title"><i class="oicon-key"></i> Permissions</div>
  <table class="table table-bordered table-striped table-condensed">
    <tr>
      <th style="width: 60px;">Devices permission level</th>
      <th style="width: 120px;"><?php echo($user_device); ?></th>
    </tr>
  </table>
  </div>
  </div>

  <div class="col-lg-6 pull-right">
  <div class="well info_box">
  <div class="title"><i class="oicon-key"></i> Encrypted Keys</div>
  <table class="table table-bordered table-striped table-condensed">
    <tr>
      <th>RSS/Atom access key</th>
<?php
  // Warn about lack of mcrypt unless told not to.
  if (!check_extension_exists('mcrypt'))
  {
    echo('<th colspan="2"><span class="text text-danger">To use RSS/Atom feeds the PHP mcrypt module is required.</span></th>');
  }
  elseif (!check_extension_exists('SimpleXML'))
  {
    echo('<th colspan="2"><span class="text text-danger">To use RSS/Atom feeds the PHP SimpleXML module is required.</span></th>');
  } else {
    echo("      <th>RSS/Atom access key created $atom_key_updated.</th>");
    echo <<<RSS
      <th><form id="atom_key" method="post" action="">
          <button type="submit" class="btn btn-mini btn-success" name="atom_key" value="toggle">Reset</button>
          </form>
      </th>
RSS;
  }
?>
    </tr>
    <tr>
      <th colspan=3></th>
    </tr>
    <tr>
      <th>API access key</th>
      <th>API access key created <?php echo($api_key_updated); ?>.</th>
      <th><form id="api_key" method="post" action="">
          <button type="submit" class="btn btn-mini btn-success" name="api_key" value="toggle" disabled="disabled">Reset</button>
          </form>
      </th>
    </tr>
  </table>
  </div>
  </div>

</div>

</form>

<?php

// EOF
