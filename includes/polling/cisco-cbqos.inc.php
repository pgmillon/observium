<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Check if QoS exists on the host

echo 'Cisco Class-based QoS: ';

$query  = 'SELECT * FROM `ports_cbqos`';
$query .= ' WHERE `device_id` = ?';

$cbq_db = dbFetchRows($query, array($device['device_id']));
foreach ($cbq_db as $cbq) { $cbq_table[$cbq['policy_index']][$cbq['object_index']] = $cbq; }

$oids = array('cbQosCMPrePolicyPkt64','cbQosCMPrePolicyByte64', 'cbQosCMPostPolicyByte64', 'cbQosCMDropPkt64', 'cbQosCMDropByte64', 'cbQosCMNoBufDropPkt64');

// Walk the first service policies OID and then see if it was populated before we continue

$device_context = $device;
if (!count($cbq_db))
{
  // Set retries to 1 for speedup first walking, only if previously polling also empty (DB empty)
  $device_context['snmp_retries'] = 1;
}
$service_policies = snmpwalk_cache_oid($device_context, "cbQosIfType", array(), "CISCO-CLASS-BASED-QOS-MIB", mib_dirs('cisco'));
unset($device_context);

if (count($service_policies))
{
  // Continue populating service policies
  $service_policies = snmpwalk_cache_oid($device, "cbQosPolicyDirection", $service_policies, "CISCO-CLASS-BASED-QOS-MIB", mib_dirs('cisco'));
  $service_policies = snmpwalk_cache_oid($device, "cbQosIfIndex", $service_policies, "CISCO-CLASS-BASED-QOS-MIB", mib_dirs('cisco'));

  # $policy_maps = snmpwalk_cache_oid($device, "cbQosPolicyMapCfgEntry", array(), "CISCO-CLASS-BASED-QOS-MIB", mib_dirs('cisco'));
  # $class_maps  = snmpwalk_cache_oid($device, "cbQosCMCfgEntry", array(), "CISCO-CLASS-BASED-QOS-MIB", mib_dirs('cisco'));
  # $object_indexes = snmpwalk_cache_twopart_oid($device, "cbQosConfigIndex", array(), "CISCO-CLASS-BASED-QOS-MIB", mib_dirs('cisco'));

  #print_r($policy_maps);
  #print_r($class_maps);
  #print_r($object_indexes);

  $cm_stats = array();
  foreach ($oids as $oid)
  {
    $cm_stats = snmpwalk_cache_twopart_oid($device, $oid, $cm_stats, "CISCO-CLASS-BASED-QOS-MIB", mib_dirs('cisco'));
  }

  foreach ($cm_stats as $policy_index => $policy_entry)
  {
    foreach ($policy_entry as $object_index => $object_entry)
    {

      $port = get_port_by_ifIndex($device['device_id'], $service_policies[$policy_index]['cbQosIfIndex']);
      $object_entry['port_id']      = $port['port_id'];
      $object_entry['direction']    = $service_policies[$policy_index]['cbQosPolicyDirection'];
      $object_entry['policy_index'] = $policy_index;
      $object_entry['object_index'] = $object_index;
      $object_entry['cm_cfg_index'] = $object_indexes[$policy_index][$object_index]['cbQosConfigIndex'];
      $object_entry['pm_cfg_index'] = $object_indexes[$policy_index][$policy_index]['cbQosConfigIndex'];
      if (!is_numeric($object_entry['pm_cfg_index'])) {
        $object_entry['pm_cfg_index'] = $object_indexes[$policy_index]['1']['cbQosConfigIndex'];
      }
      $object_entry['policy_name']  = $policy_maps[$object_entry['pm_cfg_index']]['cbQosPolicyMapName'];
      $object_entry['policy_desc']  = $policy_maps[$object_entry['pm_cfg_index']]['cbQosPolicyMapDesc'];

      $object_entry['cm_name']      = $class_maps[$object_entry['cm_cfg_index']]['cbQosCMName'];
      $object_entry['cm_desc']      = $class_maps[$object_entry['cm_cfg_index']]['cbQosCMDesc'];
      $object_entry['cm_info']      = $class_maps[$object_entry['cm_cfg_index']]['cbQosCMInfo'];

      #print_r($object_entry);

      if (!isset($cbq_table[$policy_index][$object_index]))
      {
        dbInsert(array('device_id' => $device['device_id'], 'port_id' => $port['port_id'], 'policy_index' => $policy_index, 'object_index' => $object_index, 'direction' => $object_entry['direction']), 'ports_cbqos');
        echo("+");
        $cbq_table[$policy_index][$object_index] = dbFetchRow("SELECT * FROM `ports_cbqos` WHERE `device_id` = ? AND `port_id` = ? AND `policy_index` = ? AND `object_index` = ?",
                                                               array($device['device_id'], $port['port_id'], $policy_index, $object_index));
      }

      // Do the RRD thing!
      unset($snmpstring, $rrd_update, $snmpdata, $snmpdata_cmd, $rrd_create);
      $rrd_file = safename("cbqos-".$policy_index."-".$object_index.".rrd");;
      $rrd_update = "N";

      // Loop OIDs to build rrd_create array and rrd_update
      foreach ($oids as $oid)
      {
        $oid_ds = truncate(str_replace("cbQosCM", "", str_replace("64", "", $oid)), 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";

        if (is_numeric($object_entry[$oid]))
        {
          $rrd_update .= ":".$object_entry[$oid];
        } else {
          $rrd_update .= ":U";
        }
      }

      rrdtool_create($device, $rrd_file, $rrd_create);
      rrdtool_update($device, $rrd_file, $rrd_update);

    }
  }

} // End check if QoS is enabled before we walk everything
else
{

echo 'QoS not configured.', PHP_EOL;

}

// EOF
