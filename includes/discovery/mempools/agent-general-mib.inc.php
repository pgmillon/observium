<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

# DGS-3450
#AGENT-GENERAL-MIB::agentDRAMutilizationUnitID.0 = INTEGER: 0
#AGENT-GENERAL-MIB::agentDRAMutilizationTotalDRAM.0 = INTEGER: 262144 KB
#AGENT-GENERAL-MIB::agentDRAMutilizationUsedDRAM.0 = INTEGER: 174899 KB
#AGENT-GENERAL-MIB::agentDRAMutilization.0 = INTEGER: 66

# DES-3550, DES-3526, DES-3028 (and other Stacking switches)
# AGENT-GENERAL-MIB::agentDRAMutilizationUnitID.1 = INTEGER: 1
# AGENT-GENERAL-MIB::agentDRAMutilizationTotalDRAM.1 = INTEGER: 22495072 KB
# AGENT-GENERAL-MIB::agentDRAMutilizationUsedDRAM.1 = INTEGER: 12431462 KB
# AGENT-GENERAL-MIB::agentDRAMutilization.1 = INTEGER: 55

$mib = 'AGENT-GENERAL-MIB';
echo("$mib ");

$mempool_array = snmpwalk_cache_oid($device, "agentDRAMutilizationEntry", NULL, $mib, mib_dirs('d-link'));

if (is_array($mempool_array))
{
  foreach ($mempool_array as $index => $entry)
  {
    if (is_numeric($entry['agentDRAMutilizationUsedDRAM']))
    {
      $descr     = ($index === 0 ? "Memory" : "Unit " . $index);
      $used      = $entry['agentDRAMutilizationUsedDRAM'];
      $total     = $entry['agentDRAMutilizationTotalDRAM'];
      $precision = (strlen($total) > 7 ? 1 : 1024); // Stacking swiches uses wrong units
      //$used     *= $precision;
      //$total    *= $precision;
      discover_mempool($valid['mempool'], $device, $index, $mib, $descr, $precision, $total, $used);
    }
  }
}
unset ($mempool_array, $index, $descr, $precision, $total, $used);

// EOF
