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

/*
ALVARION-DOT11-WLAN-MIB::brzaccVLTxWirelessToEthernet.0 = Counter32: 83603155
ALVARION-DOT11-WLAN-MIB::brzaccVLAUBeaconsToWireless.0 = Counter32: 6596962
ALVARION-DOT11-WLAN-MIB::brzaccVLDataAndOtherMngFramesToWireless.0 = Counter32: 106083423
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRxFramesViaEthernet.0 = Counter32: 110494348
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalTxFramesToWireless.0 = Counter32: 112680440
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRxFramesFromWireless.0 = Counter32: 87590646

ALVARION-DOT11-WLAN-MIB::brzaccVLTotalTransmittedConcatenatedFramesDouble.0 = Counter32: 3243563
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalTransmittedConcatenatedFramesSingle.0 = Counter32: 98731500
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalTransmittedConcatenatedFramesMore.0 = Counter32: 1209610
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRxConcatenatedFramesDouble.0 = Counter32: 2857061
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRxConcatenatedFramesSingle.0 = Counter32: 71832407
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRxConcatenatedFramesMore.0 = Counter32: 1154158

// Frames submitted to the internal bridge via the High/Mid/Low priority queue.
ALVARION-DOT11-WLAN-MIB::brzaccVLFramesSubmittedViaHighQueue.0 = Counter32: 127632
ALVARION-DOT11-WLAN-MIB::brzaccVLFramesSubmittedViaMidQueue.0 = Counter32: 1187662
ALVARION-DOT11-WLAN-MIB::brzaccVLFramesSubmittedViaLowQueue.0 = Counter32: 109874014

// Total
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalTransmittedUnicasts.0 = Counter32: 104787673
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRetransmittedFrames.0 = Counter32: 20662661
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalNoOfDataFramesSubmitted.0 = Counter32: 111337609
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRecievedDataFrames.0 = Counter32: 83541392

// Events
ALVARION-DOT11-WLAN-MIB::brzaccVLDroppedFrameEvents.0 = Counter32: 2744
ALVARION-DOT11-WLAN-MIB::brzaccVLUnderrunEvents.0 = Counter32: 0
ALVARION-DOT11-WLAN-MIB::brzaccVLOthersTxEvents.0 = Counter32: 0
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalTxEvents.0 = Counter32: 2744
ALVARION-DOT11-WLAN-MIB::brzaccVLOverrunEvents.0 = Counter32: 0
ALVARION-DOT11-WLAN-MIB::brzaccVLRxDecryptEvents.0 = Counter32: 0
ALVARION-DOT11-WLAN-MIB::brzaccVLTotalRxEvents.0 = Counter32: 6055720

// Frames errors
ALVARION-DOT11-WLAN-MIB::brzaccVLFramesDelayedDueToSwRetry.0 = Counter32: 0
ALVARION-DOT11-WLAN-MIB::brzaccVLFramesDropped.0 = Counter32: 2744
ALVARION-DOT11-WLAN-MIB::brzaccVLRecievedBadFrames.0 = Counter32: 6055693
ALVARION-DOT11-WLAN-MIB::brzaccVLNoOfDuplicateFramesDiscarded.0 = Counter32: 229107
ALVARION-DOT11-WLAN-MIB::brzaccVLNoOfInternallyDiscardedMirCir.0 = Counter32: 148209

// Errors
ALVARION-DOT11-WLAN-MIB::brzaccVLPhyErrors.0 = Counter32: 0
ALVARION-DOT11-WLAN-MIB::brzaccVLCRCErrors.0 = Counter32: 6055715
*/

$oids_array = array(
  'alvarion_events' => array('brzaccVLTotalTxEvents.0', 'brzaccVLTotalRxEvents.0',
                             'brzaccVLOthersTxEvents.0', 'brzaccVLRxDecryptEvents.0',
                             'brzaccVLOverrunEvents.0', 'brzaccVLUnderrunEvents.0',
                             'brzaccVLDroppedFrameEvents.0'),
  'alvarion_frames_errors' => array('brzaccVLFramesDelayedDueToSwRetry.0', 'brzaccVLFramesDropped.0',
                                    'brzaccVLRecievedBadFrames.0', 'brzaccVLNoOfDuplicateFramesDiscarded.0',
                                    'brzaccVLNoOfInternallyDiscardedMirCir.0'),
  'alvarion_errors'        => array('brzaccVLPhyErrors.0', 'brzaccVLCRCErrors.0'),
);

foreach ($oids_array as $graph => $oids)
{
  $graphs[$graph] = FALSE; // Set graph disabled by default

  if (!isset($graphs_db[$graph]) || $graphs_db[$graph] === TRUE)
  {
    $data = snmp_get_multi($device, $oids, "-OQUs", "ALVARION-DOT11-WLAN-MIB", mib_dirs('alvarion'));
    $data = $data[0];

    $rrd_file = str_replace('_', '-', $graph).".rrd";
    $rrd_create = '';
    foreach ($oids as $oid)
    {
      $oid_ds = truncate(str_replace(array('brzaccVL', '.0'), '', $oid), 19, '');
      $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
    }

    if (!empty($data))
    {
      $rrd_update = "N";

      foreach ($oids as $oid)
      {
        $oid = str_replace('.0', '', $oid);
        if (is_numeric($data[$oid]))
        {
          $rrd_update .= ":".$data[$oid];
        } else {
          $rrd_update .= ":U";
        }
      }

      rrdtool_create($device, $rrd_file, $rrd_create);
      rrdtool_update($device, $rrd_file, $rrd_update);

      $graphs[$graph] = TRUE;
    }
  }
}

unset($oids, $data, $oid);
// EOF
