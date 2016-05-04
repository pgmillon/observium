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

$table_defs['NS-ROOT-MIB']['nsCompressionStatsGroup'] = array (
  'table'      => 'nsCompressionStatsGroup',
  'numeric'    => '.1.3.6.1.4.1.5951.4.1.1.50',
  'mib'        => 'NS-ROOT-MIB',
  'mib_dir'    => 'citrix',
  'file'       => 'nsCompressionStatsGroup.rrd',
  'descr'      => 'Netscaler Compression Statistics',
  'graphs'     => array('nsCompRequests', 'nsCompBits', 'nsCompPkts', 'nsCompHttpSaving'),
  'ds_rename'  => array('Compression' => 'Comp'),
  'oids'       => array(
    'compTotalRequests' =>             array('numeric' => '1',  'descr' => 'Number of HTTP compression requests the NetScaler receives for which the response is successfully compressed. For example, after you enable compression and configure services, if you send requests to the NetScaler with the following header information: “Accept-Encoding: gzip, deflate”, and NetScaler compresses the corresponding response, this counter is incremented.', 'ds_min' => '0'), //Counter64: 26509763
    'compTotalTxBytes' =>              array('numeric' => '2',  'descr' => 'Number of bytes the NetScaler sends to the client after compressing the response from the server.', 'ds_min' => '0'), //Counter64: 261974289071
    'compTotalRxBytes' =>              array('numeric' => '3',  'descr' => 'Number of bytes that can be compressed, which the NetScaler receives from the server. This gives the content length of the response that the NetScaler receives from server.', 'ds_min' => '0'), //Counter64: 869085661597
    'compTotalTxPackets' =>            array('numeric' => '4',  'descr' => 'Number of HTTP packets that the NetScaler sends to the client after compressing the response from the server.', 'ds_min' => '0'), //Counter64: 213907749
    'compTotalRxPackets' =>            array('numeric' => '5',  'descr' => 'Number of HTTP packets that can be compressed, which the NetScaler receives from the server.', 'ds_min' => '0'), //Counter64: 661845040
    'compRatio' =>                     array('numeric' => '6',  'ds_type' => 'GAUGE', 'descr' => 'Ratio of compressible data received to compressed data transmitted expressed as percentage.', 'ds_min' => '0'), //Gauge32: 331
    'compTotalDataCompressionRatio' => array('numeric' => '7',  'ds_type' => 'GAUGE', 'descr' => 'Ratio of total HTTP data received to total HTTP data transmitted expressed as percentage.', 'ds_min' => '0'), //Gauge32: 100
    'compTcpTotalTxBytes' =>           array('numeric' => '8',  'descr' => 'Number of bytes that the NetScaler sends to the client after compressing the response from the server.', 'ds_min' => '0'), //Counter64: 0
    'compTcpTotalRxBytes' =>           array('numeric' => '9',  'descr' => 'Number of bytes that can be compressed, which the NetScaler receives from the server. This gives the content length of the response that the NetScaler receives from server.', 'ds_min' => '0'), //Counter64: 0
    'compTcpTotalTxPackets' =>         array('numeric' => '10', 'descr' => 'Number of TCP packets that the NetScaler sends to the client after compressing the response from the server.', 'ds_min' => '0'), //Counter64: 0
    'compTcpTotalRxPackets' =>         array('numeric' => '11', 'descr' => 'Total number of compressible packets received by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'compTcpTotalQuantum' =>           array('numeric' => '12', 'descr' => 'Number of times the NetScaler compresses a quantum of data. NetScaler buffers the data received from the server till it reaches the quantum size and then compresses the buffered data and transmits to the client.', 'ds_min' => '0'), //Counter64: 0
    'compTcpTotalPush' =>              array('numeric' => '13', 'descr' => 'Number of times the NetScaler compresses data on receiving a TCP PUSH flag from the server. The PUSH flag ensures that data is compressed immediately without waiting for the buffered data size to reach the quantum size.', 'ds_min' => '0'), //Counter64: 0
    'compTcpTotalEoi' =>               array('numeric' => '14', 'descr' => 'Number of times the NetScaler compresses data on receiving End Of Input (FIN packet). When the NetScaler receives End Of Input (FIN packet), it compresses the buffered data immediately without waiting for the buffered data size to reach the quantum size.', 'ds_min' => '0'), //Counter64: 0
    'compTcpTotalTimer' =>             array('numeric' => '15', 'descr' => 'Number of times the NetScaler compresses data on expiration of data accumulation timer. The timer expires if the server response is very slow and consequently, the NetScaler does not receive response for a certain amount of time. Under such a condition, the NetScaler compresses the buffered data immediately without waiting for the buffered data size to reach the quantum size.', 'ds_min' => '0'), //Counter64: 0
    'compTcpRatio' =>                  array('numeric' => '16', 'ds_type' => 'GAUGE', 'descr' => 'Ratio of compressible data received to compressed data transmitted expressed as percentage.', 'ds_min' => '0'), //Gauge32: 0
    'compTcpBandwidthSaving' =>        array('numeric' => '17', 'ds_type' => 'GAUGE', 'descr' => 'Bandwidth saving from TCP compression expressed as percentage.', 'ds_min' => '0'), //INTEGER: 0
    'deCompTcpRxPackets' =>            array('numeric' => '18', 'descr' => 'Total number of compressed packets received by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpTxPackets' =>            array('numeric' => '19', 'descr' => 'Total number of decompressed packets transmitted by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpRxBytes' =>              array('numeric' => '20', 'descr' => 'Total number of compressed bytes received by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpTxBytes' =>              array('numeric' => '21', 'descr' => 'Total number of decompressed bytes transmitted by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpErrData' =>              array('numeric' => '22', 'descr' => 'Number of data errors encountered while decompressing.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpErrLessData' =>          array('numeric' => '23', 'descr' => 'Number of times NetScaler received less data than declared by protocol.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpErrMoreData' =>          array('numeric' => '24', 'descr' => 'Number of times NetScaler received more data than declared by protocol.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpErrMemory' =>            array('numeric' => '25', 'descr' => 'Number of times memory failures occurred while decompressing.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpErrUnknown' =>           array('numeric' => '26', 'descr' => 'Number of times unknown errors occurred while decompressing.', 'ds_min' => '0'), //Counter64: 0
    'deCompTcpRatio' =>                array('numeric' => '27', 'ds_type' => 'GAUGE', 'descr' => 'Ratio of decompressed data transmitted to compressed data received expressed as percentage.', 'ds_min' => '0'), //Gauge32: 0
    'deCompTcpBandwidthSaving' =>      array('numeric' => '28', 'ds_type' => 'GAUGE', 'descr' => 'Bandwidth saving from compression expressed as percentage.', 'ds_min' => '0'), //INTEGER: 0
    'delCompTotalRequests' =>          array('numeric' => '29', 'descr' => 'Total number of delta compression requests received by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompFirstAccess' =>            array('numeric' => '30', 'descr' => 'Total number of delta compression first accesses.', 'ds_min' => '0'), //Counter64: 0
    'delCompDone' =>                   array('numeric' => '31', 'descr' => 'Total number of delta compressions done by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompTcpRxBytes' =>             array('numeric' => '32', 'descr' => 'Total number of delta-compressible bytes received by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompTcpTxBytes' =>             array('numeric' => '33', 'descr' => 'Total number of delta-compressed bytes transmitted by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompTcpRxPackets' =>           array('numeric' => '34', 'descr' => 'Number of delta-compressible packets received.', 'ds_min' => '0'), //Counter64: 0
    'delCompTcpTxPackets' =>           array('numeric' => '35', 'descr' => 'Total number of delta-compressed packets transmitted by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompBaseServed' =>             array('numeric' => '36', 'descr' => 'Total number of basefile requests served by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompBaseTcpTxBytes' =>         array('numeric' => '37', 'descr' => 'Number of basefile bytes transmitted by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompErrBypassed' =>            array('numeric' => '39', 'descr' => 'Number of times delta-compression bypassed by NetScaler.', 'ds_min' => '0'), //Counter64: 0
    'delCompErrBFileWHdrFailed' =>     array('numeric' => '40', 'descr' => 'Number of times basefile could not be updated in NetScaler cache.', 'ds_min' => '0'), //Counter64: 0
    'delCompErrNostoreMiss' =>         array('numeric' => '41', 'descr' => 'Number of times basefile was not found in NetScaler cache.', 'ds_min' => '0'), //Counter64: 0
    'delCompErrReqinfoToobig' =>       array('numeric' => '42', 'descr' => 'Number of times basefile request URL was too large.', 'ds_min' => '0'), //Counter64: 0
    'delCompErrReqinfoAllocfail' =>    array('numeric' => '43', 'descr' => 'Number of times requested basefile could not be allocated.', 'ds_min' => '0'), //Counter64: 0
    'delCompErrSessallocFail' =>       array('numeric' => '44', 'descr' => 'Number of times delta compression session could not be allocated.', 'ds_min' => '0'), //Counter64: 0
    'delCmpRatio' =>                   array('numeric' => '45', 'ds_type' => 'GAUGE', 'descr' => 'Ratio of compressible data received to compressed data transmitted expressed as percentage.', 'ds_min' => '0'), //Gauge32: 0
    'delBwSaving' =>                   array('numeric' => '46', 'ds_type' => 'GAUGE', 'descr' => 'Bandwidth saving from delta compression expressed as percentage.', 'ds_min' => '0'), //INTEGER: 0
    'compHttpBandwidthSaving' =>       array('numeric' => '47', 'ds_type' => 'GAUGE', 'descr' => 'Bandwidth saving from TCP compression expressed as percentage.') //INTEGER: 69
  )
);

// EOF
