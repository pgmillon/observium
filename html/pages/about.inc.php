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

?>

<h2 style="margin-top:0;">About <?php echo OBSERVIUM_PRODUCT_LONG; ?></h2>
<div class="row">
  <div class="col-md-6">
<?php

print_versions();

?>
  <div style="margin-bottom: 20px; margin-top: 10px;">
  <table style="width: 100%; background: transparent;">
    <tr>
      <td style="width: 20%; text-align: center;"><a class="btn btn-small" target="_blank" href="<?php echo OBSERVIUM_URL; ?>"><i style="font-size: small;" class="icon-globe"></i> Web</a></td>
      <td style="width: 20%; text-align: center;"><a class="btn btn-small" target="_blank" href="http://jira.observium.org/"><i style="font-size: small;" class="icon-bug"></i> Bugtracker</a></td>
      <td style="width: 20%; text-align: center;"><a class="btn btn-small" target="_blank" href="<?php echo OBSERVIUM_URL; ?>/wiki/Mailing_Lists"><i style="font-size: small;" class="icon-envelope"></i> Mailing List</a></td>
      <td style="width: 20%; text-align: center;"><a class="btn btn-small" target="_blank" href="http://twitter.com/observium"><i style="font-size: small;" class="icon-twitter-sign"></i> Twitter</a></td>
      <!--<td><a class="btn btn-small" target="_blank" href="http://twitter.com/observium_svn"><i class="icon-twitter-sign"></i> SVN Twitter</a></td>-->
      <td style="width: 20%; text-align: center;"><a class="btn btn-small" target="_blank" href="http://www.facebook.com/pages/Observium/128354461353"><i style="font-size: small;" class="icon-facebook-sign"></i> Facebook</a></td>
    </tr>
  </table>
  </div>

  <div class="well info_box">
    <div class="title"><i class="oicon-user-detective"></i> Development Team</div>
    <div class="content">
        <dl class="dl-horizontal" style="margin: 0px 0px 5px 0px;">
          <dt style="text-align: left;"><i class="icon-user"></i> Adam Armstrong</dt><dd>Project Leader</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Tom Laermans</dt><dd>Committer & Developer</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Mike Stupalov</dt><dd>Committer & Developer</dd>
        </dl>
    </div>
  </div>

  <div class="well info_box">
    <div class="title"><i class="oicon-users"></i> Acknowledgements</div>
    <div class="content">
        <dl class="dl-horizontal" style="margin: 0px 0px 5px 0px;">
          <dt style="text-align: left;"><i class="icon-user"></i> Twitter</dt><dd>Bootstrap CSS Framework</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> <a href="mailto:p@yusukekamiyamane.com" alt="p@yusukekamiyamane.com">Yusuke Kamiyamane</a></dt><dd>Fugue Iconset</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Mark James</dt><dd>Silk Iconset</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Jonathan De Graeve</dt><dd>SNMP code improvements</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Xiaochi Jin</dt><dd>Logo design</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Akichi Ren</dt><dd>Post-steampunk observational hamster</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Bruno Pramont</dt><dd>Collectd code</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> <a href="mailto:DavidPFarrell@gmail.com" alt="DavidPFarrell@gmail.com">David Farrell</a></dt><dd>Help with parsing net-SNMP output in PHP</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Job Snijders</dt><dd>Python-based multi-instance poller wrapper</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Dennis de Houx</dt><dd>Code contributions</dd>
          <dt style="text-align: left;"><i class="icon-user"></i> Geert Hauwaerts</dt><dd>Code contributions</dd>
        </dl>
        </div>
      </div>

  <div class="well info_box">
    <div class="title"><i class="oicon-chart"></i> Statistics</div>
    <div class="content">

<?php
$stat_devices   = dbFetchCell("SELECT COUNT(*) FROM `devices`;");
$stat_ports     = dbFetchCell("SELECT COUNT(*) FROM `ports`;");
$stat_syslog    = dbFetchCell("SELECT COUNT(*) FROM `syslog`;");
$stat_events    = dbFetchCell("SELECT COUNT(*) FROM `eventlog`;");
$stat_apps      = dbFetchCell("SELECT COUNT(*) FROM `applications`;");
$stat_services  = dbFetchCell("SELECT COUNT(*) FROM `services`;");
$stat_storage   = dbFetchCell("SELECT COUNT(*) FROM `storage`;");
$stat_diskio    = dbFetchCell("SELECT COUNT(*) FROM `ucd_diskio`;");
$stat_processors = dbFetchCell("SELECT COUNT(*) FROM `processors`;");
$stat_memory    = dbFetchCell("SELECT COUNT(*) FROM `mempools`;");
$stat_sensors   = dbFetchCell("SELECT COUNT(*) FROM `sensors`;");
$stat_sensors  += dbFetchCell("SELECT COUNT(*) FROM `status`;");
$stat_toner     = dbFetchCell("SELECT COUNT(*) FROM `toner`;");
$stat_hrdev     = dbFetchCell("SELECT COUNT(*) FROM `hrDevice`;");
$stat_entphys   = dbFetchCell("SELECT COUNT(*) FROM `entPhysical`;");

$stat_ipv4_addy = dbFetchCell("SELECT COUNT(*) FROM `ipv4_addresses`;");
$stat_ipv4_nets = dbFetchCell("SELECT COUNT(*) FROM `ipv4_networks`;");
$stat_ipv6_addy = dbFetchCell("SELECT COUNT(*) FROM `ipv6_addresses`;");
$stat_ipv6_nets = dbFetchCell("SELECT COUNT(*) FROM `ipv6_networks`;");

$stat_pw    = dbFetchCell("SELECT COUNT(*) FROM `pseudowires`;");
$stat_vrf   = dbFetchCell("SELECT COUNT(*) FROM `vrfs`;");
$stat_vlans = dbFetchCell("SELECT COUNT(*) FROM `vlans`;");

$stat_db    = get_db_size();
$stat_rrd   = get_dir_size($config['rrd_dir']);

?>
      <table class="table table-bordered table-striped table-condensed">
        <tbody>
          <tr>
            <td style='width: 45%;'><i class='oicon-database'></i> <strong>DB size</strong></td><td><span class='pull-right'><?php echo(formatStorage($stat_db)); ?></span></td>
            <td style='width: 45%;'><i class='oicon-box-zipper'></i> <strong>RRD size</strong></td><td><span class='pull-right'><?php echo(formatStorage($stat_rrd)); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-servers'></i> <strong>Devices</strong></td><td><span class='pull-right'><?php echo($stat_devices); ?></span></td>
            <td><i class='oicon-network-ethernet'></i> <strong>Ports</strong></td><td><span class='pull-right'><?php echo($stat_ports); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-ipv4'></i> <strong>IPv4 Addresses</strong></td><td><span class='pull-right'><?php echo($stat_ipv4_addy); ?></span></td>
            <td><i class='oicon-ipv4'></i> <strong>IPv4 Networks</strong></td><td><span class='pull-right'><?php echo($stat_ipv4_nets); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-ipv6'></i> <strong>IPv6 Addresses</strong></td><td><span class='pull-right'><?php echo($stat_ipv6_addy); ?></span></td>
            <td><i class='oicon-ipv6'></i> <strong>IPv6 Networks</strong></td><td><span class='pull-right'><?php echo($stat_ipv6_nets); ?></span></td>
           </tr>
         <tr>
            <td><i class='oicon-gear'></i> <strong>Services</strong></td><td><span class='pull-right'><?php echo($stat_services); ?></span></td>
            <td><i class='oicon-application-icon-large'></i> <strong>Applications</strong></td><td><span class='pull-right'><?php echo($stat_apps); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-processor'></i> <strong>Processors</strong></td><td><span class='pull-right'><?php echo($stat_processors); ?></span></td>
            <td><i class='oicon-memory'></i> <strong>Memory</strong></td><td><span class='pull-right'><?php echo($stat_memory); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-drive'></i> <strong>Storage</strong></td><td><span class='pull-right'><?php echo($stat_storage); ?></span></td>
            <td><i class='oicon-drive--arrow'></i> <strong>Disk I/O</strong></td><td><span class='pull-right'><?php echo($stat_diskio); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-wooden-box'></i> <strong>HR-MIB</strong></td><td><span class='pull-right'><?php echo($stat_hrdev); ?></span></td>
            <td><i class='oicon-wooden-box'></i> <strong>Entity-MIB</strong></td><td><span class='pull-right'><?php echo($stat_entphys); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-clipboard-eye'></i> <strong>Syslog Entries</strong></td><td><span class='pull-right'><?php echo($stat_syslog); ?></span></td>
            <td><i class='oicon-clipboard-audit'></i> <strong>Eventlog Entries</strong></td><td><span class='pull-right'><?php echo($stat_events); ?></span></td>
          </tr>
          <tr>
            <td><i class='oicon-system-monitor'></i> <strong>Sensors</strong></td><td><span class='pull-right'><?php echo($stat_sensors); ?></span></td>
            <td><i class='oicon-printer-color'></i> <strong>Toner</strong></td><td><span class='pull-right'><?php echo($stat_toner); ?></span></td>
          </tr>
        </tbody>
      </table>

      </div>
    </div>
  </div>
  <div class="col-md-6">

  <div class="well info_box">
    <div class="title"><i class="oicon-notebook"></i> License</div>
    <div class="content">
      <pre class="small">
        <?php include($config['install_dir']."/LICENSE"); ?>
      </pre>
    </div>
  </div>
  </div>
</div>

<?php

// EOF
