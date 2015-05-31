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

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");

  exit;
}

echo("<h2>Add Device</h2>");

echo('<div class="well well-white">');

if ($_POST['hostname'])
{
  if ($_SESSION['userlevel'] > '5')
  {
    $hostname = $_POST['hostname'];

    if ($_POST['port'] && is_numeric($_POST['port'])) { $port = (int) $_POST['port']; } else { $port = 161; }

    if ($_POST['snmpver'] === "v2c" or $_POST['snmpver'] === "v1")
    {
      if ($_POST['community'])
      {
        $config['snmp']['community'] = array($_POST['community']);
      }

      $snmpver = $_POST['snmpver'];
      print_message("Adding host $hostname communit" . (count($config['snmp']['community']) == 1 ? "y" : "ies") . " "  . implode(', ',$config['snmp']['community']) . " port $port");
    }
    elseif ($_POST['snmpver'] === "v3")
    {
      $v3 = array (
        'authlevel' => $_POST['authlevel'],
        'authname' => $_POST['authname'],
        'authpass' => $_POST['authpass'],
        'authalgo' => $_POST['authalgo'],
        'cryptopass' => $_POST['cryptopass'],
        'cryptoalgo' => $_POST['cryptoalgo'],
      );

      array_push($config['snmp']['v3'], $v3);

      $snmpver = "v3";

      print_message("Adding SNMPv3 host $hostname port $port");
    }
    else
    {
      print_error("Unsupported SNMP Version. There was a dropdown menu, how did you reach this error ?"); // We have a hacker!
    }

    if ($_POST['ignorerrd'] == 'confirm') { $config['rrd_override'] = TRUE; }

    $result = add_device($hostname, $snmpver, $port);
    if ($result)
    {
      print_success("Device added (id = $result)");
    }
  } else {
    print_error("You don't have the necessary privileges to add hosts.");
  }
}

$pagetitle[] = "Add host";

?>

<form name="form1" method="post" action="" class="form-horizontal">

  <p>Devices will be checked for Ping and SNMP reachability before being probed. Only devices with recognised OSes will be added.</p>

  <fieldset>
    <legend>Device Properties</legend>
    <div class="control-group">
      <label class="control-label" for="hostname">Hostname</label>
      <div class="controls">
         <input type="text" name="hostname" size="32" value="<?php echo($vars['hostname']); ?>"/>
      </div>
      <label class="control-label" for="ignorerrd">Ignore RRD exist</label>
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="ignorerrd" value="confirm" <?php if ($config['rrd_override']) { echo('disabled checked'); } ?> />Add device anyway if directory with RRDs already exists
        </label>
      </div>
    </div>

  <input type="hidden" name="editing" value="yes">
  <fieldset>
    <legend>SNMP Properties</legend>
    <div class="control-group">
      <label class="control-label" for="snmpver">SNMP Version</label>
      <div class="controls">
        <select name="snmpver">
          <option value="v1"  <?php echo($vars['snmpver'] == 'v1'  || ($vars['snmpver'] == '' && $config['snmp']['version'] == 'v1')  ? 'selected' : ''); ?> >v1</option>
          <option value="v2c" <?php echo($vars['snmpver'] == 'v2c' || ($vars['snmpver'] == '' && $config['snmp']['version'] == 'v2c') ? 'selected' : ''); ?> >v2c</option>
          <option value="v3"  <?php echo($vars['snmpver'] == 'v3'  || ($vars['snmpver'] == '' && $config['snmp']['version'] == 'v3')  ? 'selected' : ''); ?> >v3</option>
        </select>
      </div>
    </div>

    <div class="control-group">
       <label class="control-label" for="port">SNMP Port</label>
       <div class="controls">
         <input type="text" name="port" size="32" value="161"/>
       </div>
     </div>
  </fieldset>

  <!-- To be able to hide it -->
  <div id="snmpv12">
    <fieldset>
      <legend>SNMPv1/v2c Configuration</legend>
      <div class="control-group">
        <label class="control-label" for="community">SNMP Community</label>
        <div class="controls">
          <input type="text" name="community" size="32" value="<?php echo $vars['community']; ?>"/>
        </div>
      </div>
    </fieldset>
  </div>

  <!-- To be able to hide it -->
  <div id='snmpv3'>
    <fieldset>
      <legend>SNMPv3 Configuration</legend>
      <div class="control-group">
        <label class="control-label" for="authlevel">Auth Level</label>
        <div class="controls">
          <select name="authlevel">
            <option value="noAuthNoPriv" <?php echo($vars['authlevel'] == 'noAuthNoPriv' ? 'selected' : ''); ?> >noAuthNoPriv</option>
            <option value="authNoPriv"   <?php echo($vars['authlevel'] == 'authNoPriv' ? 'selected' : ''); ?> >authNoPriv</option>
            <option value="authPriv"     <?php echo($vars['authlevel'] == 'authPriv' ? 'selected' : ''); ?> >authPriv</option>
          </select>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="authname">Auth User Name</label>
        <div class="controls">
          <input type="text" name="authname" size="32" value="<?php echo $vars['authname']; ?>"/>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="authpass">Auth Password</label>
        <div class="controls">
          <input type="text" name="authpass" size="32" value=""/>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="authalgo">Auth Algorithm</label>
        <div class="controls">
          <select name="authalgo">
            <option value='MD5'>MD5</option>
            <option value='SHA' <?php echo($vars['authalgo'] === "SHA" ? 'selected' : ''); ?>>SHA</option>
          </select>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="cryptopass">Crypto Password</label>
        <div class="controls">
          <input type="text" name="cryptopass" size="32" value=""/>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="cryptoalgo">Crypto Algorithm</label>
        <div class="controls">
          <select name="cryptoalgo">
            <option value='AES'>AES</option>
            <option value='DES' <?php echo($vars['authalgo'] === "DES" ? 'selected' : ''); ?>>DES</option>
          </select>
        </div>
      </div>

    </fieldset>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-success" name="submit" value="save"><i class="oicon-plus oicon-white"></i> Add Device</button>
  </div>

</form>

</div> <?php /* FIXME there is no opening div for this, can probably go? */ ?>
