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

$ok = FALSE;
if ($vars['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
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

?>

<form id="edit" name="edit" method="post" class="form-horizontal" action="">
  <input type="hidden" name="editing" value="yes">

<!--
<fieldset>
  <legend>SNMP Settings</legend>
</fieldset>
-->

<div class="row">
  <div class="col-md-6">

  <div class="widget widget-table">
    <div class="widget-header">
      <i class="oicon-gear"></i><h3>Basic Configuration</h3>
    </div>
    <div class="widget-content"  style="padding-top: 10px;">

    <fieldset>
      <div class="control-group">
      <label class="control-label" for="snmp_version">Protocol Version</label>
      <div class="controls">
        <select class="selectpicker" name="snmp_version" id="snmp_version">
          <option value="v1"  <?php echo($device['snmp_version'] == 'v1' ? 'selected' : ''); ?> >v1</option>
          <option value="v2c" <?php echo($device['snmp_version'] == 'v2c' ? 'selected' : ''); ?> >v2c</option>
          <option value="v3"  <?php echo($device['snmp_version'] == 'v3' ? 'selected' : ''); ?> >v3</option>
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
              if ($transport == $device['snmp_transport']) { echo(" selected='selected'"); }
              echo(">".$transport."</option>");
            }
            ?>
          </select>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="snmp_port">Port</label>
        <div class="controls">
          <input type=text name="snmp_port" size="32" value="<?php echo(escape_html($device['snmp_port'])); ?>"/>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="snmp_timeout">Timeout</label>
        <div class="controls">
          <input type=text name="snmp_timeout" size="32" value="<?php echo(escape_html($device['snmp_timeout'])); ?>"/>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="snmp_retries">Retries</label>
        <div class="controls">
          <input type=text name="snmp_retries" size="32" value="<?php echo(escape_html($device['snmp_retries'])); ?>"/>
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
          <input type=text name="snmp_community" size="32" value="<?php echo(escape_html($device['snmp_community'])); // FIXME. For passwords we should use filter instead escape! ?>"/>
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
            <option value="noAuthNoPriv" <?php echo($device['snmp_authlevel'] == 'noAuthNoPriv' ? 'selected' : ''); ?> >noAuthNoPriv</option>
            <option value="authNoPriv"   <?php echo($device['snmp_authlevel'] == 'authNoPriv' ? 'selected' : ''); ?> >authNoPriv</option>
            <option value="authPriv"     <?php echo($device['snmp_authlevel'] == 'authPriv' ? 'selected' : ''); ?> >authPriv</option>
          </select>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="snmp_authname">Auth User Name</label>
        <div class="controls">
          <input type=text name="snmp_authname" size="32" value="<?php echo(escape_html($device['snmp_authname'])); ?>"/>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="snmp_authpass">Auth Password</label>
        <div class="controls">
          <input type="password" name="snmp_authpass" size="32" value="<?php echo(escape_html($device['snmp_authpass'])); // FIXME. For passwords we should use filter instead escape! ?>"/>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="snmp_authalgo">Auth Algorithm</label>
        <div class="controls">
          <select class="selectpicker" name="snmp_authalgo">
            <option value="MD5" <?php echo($device['snmp_authalgo'] == 'MD5' ? 'selected' : ''); ?> >MD5</option>
            <option value="SHA" <?php echo($device['snmp_authalgo'] == 'SHA' ? 'selected' : ''); ?> >SHA</option>
          </select>
        </div>
      </div>
      <div id="authPriv"> <!-- only show this when auth level = authPriv -->
        <div class="control-group">
          <label class="control-label" for="snmp_cryptopass">Crypto Password</label>
          <div class="controls">
            <input type="password" name="snmp_cryptopass" size="32" value="<?php echo(escape_html($device['snmp_cryptopass'])); // FIXME. For passwords we should use filter instead escape! ?>"/>
          </div>
        </div>

        <div class="control-group">
          <label class="control-label" for="snmp_cryptoalgo">Crypto Algorithm</label>
          <div class="controls">
            <select class="selectpicker" name="snmp_cryptoalgo">
              <option value="AES" <?php echo($device['snmp_cryptoalgo'] == "AES" ? 'selected' : ''); ?> >AES</option>
              <option value="DES" <?php echo($device['snmp_cryptoalgo'] == "DES" ? 'selected' : ''); ?> >DES</option>
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
      <button type="submit" class="btn btn-primary" name="submit" value="save"><i class="icon-ok icon-white"></i> Save Changes</button>
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
<?php

// EOF
