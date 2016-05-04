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

if (is_device_mib($device, 'UCD-SNMP-MIB'))
{
  $load_rrd  = "ucd_load.rrd";
  $cpu_rrd   = "ucd_cpu.rrd";
  $mem_rrd   = "ucd_mem.rrd";

  // Poll systemStats from UNIX-like hosts running UCD/Net-SNMPd

  #UCD-SNMP-MIB::ssIndex.0 = INTEGER: 1
  #UCD-SNMP-MIB::ssErrorName.0 = STRING: systemStats
  #UCD-SNMP-MIB::ssSwapIn.0 = INTEGER: 0 kB
  #UCD-SNMP-MIB::ssSwapOut.0 = INTEGER: 0 kB
  #UCD-SNMP-MIB::ssIOSent.0 = INTEGER: 1864 blocks/s
  #UCD-SNMP-MIB::ssIOReceive.0 = INTEGER: 7 blocks/s
  #UCD-SNMP-MIB::ssSysInterrupts.0 = INTEGER: 7572 interrupts/s
  #UCD-SNMP-MIB::ssSysContext.0 = INTEGER: 10254 switches/s
  #UCD-SNMP-MIB::ssCpuUser.0 = INTEGER: 4
  #UCD-SNMP-MIB::ssCpuSystem.0 = INTEGER: 3
  #UCD-SNMP-MIB::ssCpuIdle.0 = INTEGER: 77
  #UCD-SNMP-MIB::ssCpuRawUser.0 = Counter32: 194386556
  #UCD-SNMP-MIB::ssCpuRawNice.0 = Counter32: 15673
  #UCD-SNMP-MIB::ssCpuRawSystem.0 = Counter32: 65382910
  #UCD-SNMP-MIB::ssCpuRawIdle.0 = Counter32: 1655192684
  #UCD-SNMP-MIB::ssCpuRawWait.0 = Counter32: 205336019
  #UCD-SNMP-MIB::ssCpuRawKernel.0 = Counter32: 0
  #UCD-SNMP-MIB::ssCpuRawInterrupt.0 = Counter32: 1128048
  #UCD-SNMP-MIB::ssIORawSent.0 = Counter32: 2353983704
  #UCD-SNMP-MIB::ssIORawReceived.0 = Counter32: 3172182750
  #UCD-SNMP-MIB::ssRawInterrupts.0 = Counter32: 427446276
  #UCD-SNMP-MIB::ssRawContexts.0 = Counter32: 4161026807
  #UCD-SNMP-MIB::ssCpuRawSoftIRQ.0 = Counter32: 2605010
  #UCD-SNMP-MIB::ssRawSwapIn.0 = Counter32: 602002
  #UCD-SNMP-MIB::ssRawSwapOut.0 = Counter32: 937422

  $ss = snmpwalk_cache_oid($device, "systemStats", array(), "UCD-SNMP-MIB", mib_dirs());
  if ($GLOBALS['snmp_status'])
  {
    $ss = $ss[0]; // Insert Nazi joke here.

    // Create CPU RRD if it doesn't already exist
    $cpu_rrd_create = " \
       DS:user:COUNTER:600:0:U \
       DS:system:COUNTER:600:0:U \
       DS:nice:COUNTER:600:0:U \
       DS:idle:COUNTER:600:0:U ";

    // This is how we currently collect. We should collect one RRD per stat, for ease of handling differen formats,
    // and because it is per-host and no big performance hit. See new format below
    // FIXME REMOVE

    if (is_numeric($ss['ssCpuRawUser']) && is_numeric($ss['ssCpuRawNice']) && is_numeric($ss['ssCpuRawSystem']) && is_numeric($ss['ssCpuRawIdle']))
    {
      rrdtool_create($device, $cpu_rrd, $cpu_rrd_create);
      rrdtool_update($device, $cpu_rrd, array($ss['ssCpuRawUser'],$ss['ssCpuRawSystem'],$ss['ssCpuRawNice'],$ss['ssCpuRawIdle']));
      $graphs['ucd_cpu'] = TRUE;
    }

    // This is how we'll collect in the future, start now so people don't have zero data.

    $collect_oids = array('ssIORawSent', 'ssIORawReceived', 'ssRawInterrupts', 'ssRawContexts', 'ssRawSwapIn', 'ssRawSwapOut');

    foreach ($collect_oids as $oid)
    {
      if (is_numeric($ss[$oid]))
      {
        $value = $ss[$oid];
        $filename = "ucd_".$oid.".rrd";
        rrdtool_create($device, $filename, " DS:value:COUNTER:600:0:U ");
        rrdtool_update($device, $filename, "N:".$value);
        $graphs['ucd_cpu'] = TRUE;

      }
    }

    $cpu_oids = array('ssCpuRawUser','ssCpuRawNice','ssCpuRawSystem','ssCpuRawIdle','ssCpuRawInterrupt', 'ssCpuRawSoftIRQ', 'ssCpuRawKernel', 'ssCpuRawWait');

    $ss_cpu_total = 0;
    foreach ($cpu_oids as $oid)
    {
      if (is_numeric($ss[$oid]))
      {
        $ss_cpu_valid[$oid] = TRUE;
        $ss_cpu_total += $ss[$oid];
      }
    }

    foreach ($cpu_oids as $oid)
    {
      if ($ss_cpu_valid[$oid])
      {
        $value = $ss[$oid];
        $perc  = $ss[$oid] / $ss_cpu_total * 100;
        $filename = "ucd_".$oid.".rrd";
        rrdtool_create($device, $filename, " DS:value:COUNTER:600:0:U");
        rrdtool_update($device, $filename, "N:".$value);
        $graphs['ucd_ss_cpu'] = TRUE;
        $ucd_ss_cpu[$oid]['perc'] = $perc;
      }
    }

    // WHY
    if (count($ucd_ss_cpu))
    {
      $device_state['ucd_ss_cpu']  = $ucd_ss_cpu;
    }

    // Set various graphs if we've seen the right OIDs.

    if (is_numeric($ss['ssRawSwapIn'])) { $graphs['ucd_swap_io'] = TRUE; }
    if (is_numeric($ss['ssIORawSent'])) { $graphs['ucd_io'] = TRUE; }
    if (is_numeric($ss['ssRawContexts'])) { $graphs['ucd_contexts'] = TRUE; }
    if (is_numeric($ss['ssRawInterrupts'])) { $graphs['ucd_interrupts'] = TRUE; }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////#

    // Poll mem for load memory utilisation stats on UNIX-like hosts running UCD/Net-SNMPd
    #UCD-SNMP-MIB::memIndex.0 = INTEGER: 0
    #UCD-SNMP-MIB::memErrorName.0 = STRING: swap
    #UCD-SNMP-MIB::memTotalSwap.0 = INTEGER: 32762248 kB
    #UCD-SNMP-MIB::memAvailSwap.0 = INTEGER: 32199396 kB
    #UCD-SNMP-MIB::memTotalReal.0 = INTEGER: 8187696 kB
    #UCD-SNMP-MIB::memAvailReal.0 = INTEGER: 1211056 kB
    #UCD-SNMP-MIB::memTotalFree.0 = INTEGER: 33410452 kB
    #UCD-SNMP-MIB::memMinimumSwap.0 = INTEGER: 16000 kB
    #UCD-SNMP-MIB::memBuffer.0 = INTEGER: 104388 kB
    #UCD-SNMP-MIB::memCached.0 = INTEGER: 2595556 kB
    #UCD-SNMP-MIB::memSwapError.0 = INTEGER: noError(0)
    #UCD-SNMP-MIB::memSwapErrorMsg.0 = STRING:

    $mem_rrd_create = " \
         DS:totalswap:GAUGE:600:0:10000000000 \
         DS:availswap:GAUGE:600:0:10000000000 \
         DS:totalreal:GAUGE:600:0:10000000000 \
         DS:availreal:GAUGE:600:0:10000000000 \
         DS:totalfree:GAUGE:600:0:10000000000 \
         DS:shared:GAUGE:600:0:10000000000 \
         DS:buffered:GAUGE:600:0:10000000000 \
         DS:cached:GAUGE:600:0:10000000000 ";

    $snmpdata = snmpwalk_cache_oid($device, "mem", array(), "UCD-SNMP-MIB", mib_dirs());
    if (is_array($snmpdata[0]))
    {
      foreach (array_keys($snmpdata[0]) as $key)
      {
        $$key = $snmpdata[0][$key];
        // Fix for some systems (who report negative values)
        //memShared.0 = 28292
        //memBuffer.0 = -3762592
        //memCached.0 = 203892
        if (is_numeric($$key) && $$key < 0) { $$key = 0; }
      }
    }

    $snmpdata = $snmpdata[0];

    // Check to see that the OIDs are actually populated before we make the rrd
    if (is_numeric($memTotalReal) && is_numeric($memAvailReal) && is_numeric($memTotalFree))
    {
      rrdtool_create($device, $mem_rrd, $mem_rrd_create);
      rrdtool_update($device, $mem_rrd,  array($memTotalSwap, $memAvailSwap, $memTotalReal, $memAvailReal, $memTotalFree, $memShared, $memBuffer, $memCached));
      $graphs['ucd_memory'] = TRUE;

      $device_state['ucd_mem']['swap_total'] = $memTotalSwap;
      $device_state['ucd_mem']['swap_avail'] = $memAvailSwap;

      $device_state['ucd_mem']['mem_total'] = $memTotalReal;
      $device_state['ucd_mem']['mem_avail'] = $memAvailReal;
      $device_state['ucd_mem']['mem_shared'] = $memShared;
      $device_state['ucd_mem']['mem_buffer'] = $memBuffer;
      $device_state['ucd_mem']['mem_cached'] = $memCached;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Poll laLoadInt for load averages on UNIX-like hosts running UCD/Net-SNMPd
    #UCD-SNMP-MIB::laLoadInt.1 = INTEGER: 206
    #UCD-SNMP-MIB::laLoadInt.2 = INTEGER: 429
    #UCD-SNMP-MIB::laLoadInt.3 = INTEGER: 479

    $load_raw = snmpwalk_cache_oid($device, "laLoadInt", array(), "UCD-SNMP-MIB", mib_dirs());

    // Check to see that the 5-min OID is actually populated before we make the rrd
    if (is_numeric($load_raw[2]['laLoadInt']))
    {
      rrdtool_create($device, $load_rrd, "DS:1min:GAUGE:600:0:500000 DS:5min:GAUGE:600:0:500000 DS:15min:GAUGE:600:0:500000 ");
      rrdtool_update($device, $load_rrd, array($load_raw[1]['laLoadInt'], $load_raw[2]['laLoadInt'], $load_raw[3]['laLoadInt']));
      $graphs['ucd_load'] = TRUE;

      $device_state['ucd_load']  = $load_raw[2]['laLoadInt'];
    }
  }

  unset($ss, $load_rrd, $load_raw, $snmpdata);
  unset($memTotalSwap, $memAvailSwap, $memTotalReal, $memAvailReal, $memTotalFree, $memShared, $memBuffer, $memCached);
  unset($key, $mem_rrd, $mem_rrd_create, $collect_oids, $value, $filename, $cpu_rrd, $cpu_rrd_create, $oid);
} # end is_device_mib()

// EOF
