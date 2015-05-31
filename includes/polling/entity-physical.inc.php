<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo("Entity Physical: ");

if($device['os'] == "ios")
{

 ## FIXME: We know this only exists on a small number of devices, perhaps we chould check for them?

 echo("Cisco Cat6xxx/76xx Crossbar : \n");
 $mod_stats  = snmpwalk_cache_oid($device, "cc6kxbarModuleModeTable", array(), "CISCO-CAT6K-CROSSBAR-MIB");
 $chan_stats = snmpwalk_cache_oid($device, "cc6kxbarModuleChannelTable", array(), "CISCO-CAT6K-CROSSBAR-MIB");
 $chan_stats = snmpwalk_cache_oid($device, "cc6kxbarStatisticsTable", $chan_stats, "CISCO-CAT6K-CROSSBAR-MIB");

 foreach ($mod_stats as $index => $entry)
 {
   $group = 'c6kxbar';
   foreach ($entry as $key => $value)
   {
     $subindex = NULL;
     $entPhysical_state[$index][$subindex][$group][$key] = $value;
   }
 }

 foreach ($chan_stats as $index => $entry)
 {
   list($index,$subindex) = explode(".", $index, 2);
   $group = 'c6kxbar';
   foreach ($entry as $key => $value)
   {
     $entPhysical_state[$index][$subindex][$group][$key] = $value;
   }

   $chan_update = $entry['cc6kxbarStatisticsInUtil'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsOutUtil'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsOutDropped'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsOutErrors'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsInErrors'];

   $rrd = "c6kxbar-$index-$subindex.rrd";

   if ($debug) { echo("$rrd "); }

   rrdtool_create($device, $rrd, " \
     DS:inutil:GAUGE:600:0:100 \
     DS:oututil:GAUGE:600:0:100 \
     DS:outdropped:DERIVE:600:0:125000000000 \
     DS:outerrors:DERIVE:600:0:125000000000 \
     DS:inerrors:DERIVE:600:0:125000000000 ");

   rrdtool_update($device, $rrd,"N:$chan_update");
 }

#print_vars($entPhysical_state);

}

// Remove/Update Entity state
foreach (dbFetch("SELECT * FROM `entPhysical-state` WHERE `device_id` = ?", array($device['device_id'])) as $entity)
{
  if (!isset($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']]))
  {
    dbDelete('entPhysical-state', "`device_id` = ? AND `entPhysicalIndex` = ? AND `subindex` = ? AND `group` = ? AND `key` = ?",
                               array($device['device_id'], $entity['entPhysicalIndex'], $entity['subindex'], $entity['group'], $entity['key']));
  } else {
    if ($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']] != $entity['value'])
    {
#      echo("no match!");
      dbUpdate(array('value' => $entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']]), 'entPhysical-state', '`entPhysical_state_id` = ?', array($entity['entPhysical_state_id']));
    }
    unset($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']]);
  }
}
// End Remove/Update Entity Attribs

// Insert state
foreach ($entPhysical_state as $epi => $entity)
{
  foreach ($entity as $subindex => $si)
  {
    foreach ($si as $group => $ti)
    {
      foreach ($ti as $key => $value)
      {
        dbInsert(array('device_id' => $device['device_id'], 'entPhysicalIndex' => $epi, 'subindex' => $subindex, 'group' => $group, 'key' => $key, 'value' => $value), 'entPhysical-state');
      }
    }
  }
}
// End Insert Entity state

echo(PHP_EOL);

// EOF
