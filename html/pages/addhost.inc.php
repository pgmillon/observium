<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");

  exit;
}

echo '<div class="row">';

echo("<h2>Add Device</h2>");

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

    if ($vars['ignorerrd'] == 'confirm') { $config['rrd_override'] = TRUE; }

    $result = add_device($hostname, $snmp_version, $snmp_port, strip_tags($vars['snmp_transport']));
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

?>

<form id="edit" name="edit" method="post" class="form-horizontal" action="">
  <input type="hidden" name="editing" value="yes">

  <div class="row">
    <div class="col-md-6">

      <div class="widget widget-table">
        <div class="widget-header">
          <i class="oicon-gear"></i><h3>Basic Configuration</h3>
        </div>
        <div class="widget-content"  style="padding-top: 10px;">

          <fieldset>

            <div class="control-group">
              <label class="control-label" for="hostname">Hostname</label>
              <div class="controls">
                <input type=text name="hostname" size="32" value="<?php echo(escape_html($vars['hostname'])); ?>" />
              </div>
            </div>

            <div class="control-group">
              <label class="control-label" for="snmp_version">Protocol Version</label>
              <div class="controls">
                <select class="selectpicker" name="snmp_version" id="snmp_version">
                  <option value="v1"  <?php echo($snmp_version == 'v1'  ? 'selected' : ''); ?> >v1</option>
                  <option value="v2c" <?php echo($snmp_version == 'v2c' ? 'selected' : ''); ?> >v2c</option>
                  <option value="v3"  <?php echo($snmp_version == 'v3'  ? 'selected' : ''); ?> >v3</option>
                </select>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label" for="snmp_transport">Transport</label>
              <div class="controls">
                <select class="selectpicker" name="snmp_transport">
                  <?php
                  foreach ($config['snmp']['transports'] as $transport)
                  {
                    echo("<option value='".$transport."'");
                    if ($transport == $snmp_transport) { echo(" selected='selected'"); }
                    echo(">".$transport."</option>");
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label" for="snmp_port">Port</label>
              <div class="controls">
                <input type=text name="snmp_port" size="32" value="<?php echo(escape_html($vars['snmp_port'])); ?>"/>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label" for="snmp_timeout">Timeout</label>
              <div class="controls">
                <input type=text name="snmp_timeout" size="32" value="<?php echo(escape_html($vars['snmp_timeout'])); ?>"/>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label" for="snmp_retries">Retries</label>
              <div class="controls">
                <input type=text name="snmp_retries" size="32" value="<?php echo(escape_html($vars['snmp_retries'])); ?>"/>
              </div>
            </div>

            <div class="control-group">
              <label class="control-label" for="ignorerrd">Ignore RRD exist</label>
              <div class="controls">
                <label class="checkbox">
                <input type="checkbox" name="ignorerrd" value="confirm" <?php if ($config['rrd_override']) { echo('disabled checked'); } ?> />Add device anyway if directory with RRDs already exists
                </label>
              </div>
            </div>
          </fieldset>
        </div>
      </div>
    </div>

    <div class="col-lg-6 pull-right">
      <div class="widget widget-table">
        <div class="widget-header">
          <i class="oicon-lock-warning"></i><h3>Authentication Configuration</h3>
        </div>
        <div class="widget-content" style="padding-top: 10px;">

          <!-- To be able to hide it -->
          <div id="snmpv2">
            <fieldset>
              <div class="control-group">
                <label class="control-label" for="snmp_community">SNMP Community</label>
                <div class="controls">
                  <input type=text name="snmp_community" size="32" value="<?php echo(escape_html($vars['snmp_community'])); // FIXME. For passwords we should use filter instead escape! ?>"/>
                </div>
              </div>
            </fieldset>
          </div>

          <!-- To be able to hide it -->
          <div id="snmpv3">
            <fieldset>
              <div class="control-group">
                <label class="control-label" for="snmp_authlevel">Auth Level</label>
                <div class="controls">
                  <select class="selectpicker" name="snmp_authlevel" id="snmp_authlevel">
                    <option value="noAuthNoPriv" <?php echo($vars['snmp_authlevel'] == 'noAuthNoPriv' ? 'selected' : ''); ?> >noAuthNoPriv</option>
                    <option value="authNoPriv"   <?php echo($vars['snmp_authlevel'] == 'authNoPriv' ? 'selected' : ''); ?> >authNoPriv</option>
                    <option value="authPriv"     <?php echo($vars['snmp_authlevel'] == 'authPriv' ? 'selected' : ''); ?> >authPriv</option>
                  </select>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="snmp_authname">Auth User Name</label>
                <div class="controls">
                  <input type=text name="snmp_authname" size="32" value="<?php echo(escape_html($vars['snmp_authname'])); ?>"/>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="snmp_authpass">Auth Password</label>
                <div class="controls">
                  <input type="password" name="snmp_authpass" size="32" value="<?php echo(escape_html($vars['snmp_authpass'])); // FIXME. For passwords we should use filter instead escape! ?>"/>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="snmp_authalgo">Auth Algorithm</label>
                <div class="controls">
                  <select class="selectpicker" name="snmp_authalgo">
                    <option value="MD5" <?php echo($vars['snmp_authalgo'] == 'MD5' ? 'selected' : ''); ?> >MD5</option>
                    <option value="SHA" <?php echo($vars['snmp_authalgo'] == 'SHA' ? 'selected' : ''); ?> >SHA</option>
                  </select>
                </div>
              </div>
              <div id="authPriv"> <!-- only show this when auth level = authPriv -->
                <div class="control-group">
                  <label class="control-label" for="snmp_cryptopass">Crypto Password</label>
                  <div class="controls">
                    <input type="password" name="snmp_cryptopass" size="32" value="<?php echo(escape_html($vars['snmp_cryptopass'])); // FIXME. For passwords we should use filter instead escape! ?>"/>
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label" for="snmp_cryptoalgo">Crypto Algorithm</label>
                  <div class="controls">
                    <select class="selectpicker" name="snmp_cryptoalgo">
                      <option value="AES" <?php echo($vars['snmp_cryptoalgo'] == "AES" ? 'selected' : ''); ?> >AES</option>
                      <option value="DES" <?php echo($vars['snmp_cryptoalgo'] == "DES" ? 'selected' : ''); ?> >DES</option>
                    </select>
                  </div>
                </div>
              </div>
            </fieldset>
          </div> <!-- end col -->
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" name="submit" value="save"><i class="icon-plus icon-white"></i> Add Device</button>
    </div>
  </div>
</form>

<script>

  // Show/hide SNMPv1/2c or SNMPv3 authentication settings pane based on setting of protocol version.
  //$("#snmpv2").hide();
  //$("#snmpv3").hide();

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
      $('#authPriv').show();
    } else {
      $('#authPriv').hide();
    }
  }).change();

</script>

</div>
