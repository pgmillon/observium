<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// We can draw more graphs from these now.
$table_defs['BLUECOAT-SG-PROXY-MIB']['sgProxyHttpPerf'] = array(
  'mib'           => 'BLUECOAT-SG-PROXY-MIB',
  'mib_dir'       => 'bluecoat',
  'table'         => 'sgProxyHttpPerf', // Group sgProxyHttpPerf contains sgProxyHttpClient/sgProxyHttpServer/sgProxyHttpConnections
  'ds_rename'     => array('sgProxyHttp' => '', 'Connections' => 'Conn', 'Idle' => 'Id', 'Active' => 'Ac'),
  'graphs'        => array('bluecoat_http_client', 'bluecoat_http_server', 'bluecoat_cache', 'bluecoat_server'),
  'oids'          => array(

    //sgProxyHttpClient
    'sgProxyHttpClientRequests'          => array('descr' => 'The number of HTTP requests received from clients.', 'ds_min' => '0'), //203536100
    'sgProxyHttpClientHits'              => array('descr' => 'The number of HTTP hits that the proxy clients have produced.', 'ds_min' => '0'), //24340977
    'sgProxyHttpClientPartialHits'       => array('descr' => 'The number of HTTP partial (near) hits that the proxy clients have produced.', 'ds_min' => '0'), //160332
    'sgProxyHttpClientMisses'            => array('descr' => '', 'ds_min' => '0'), //177260105
    'sgProxyHttpClientErrors'            => array('descr' => '', 'ds_min' => '0'), //1781827
    'sgProxyHttpClientRequestRate'       => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //32
    'sgProxyHttpClientHitRate'           => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //12
    'sgProxyHttpClientByteHitRate'       => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //22
    'sgProxyHttpClientInBytes'           => array('descr' => '', 'ds_min' => '0'), //135298339836
    'sgProxyHttpClientOutBytes'          => array('descr' => '', 'ds_min' => '0'), //2427341431710

    //sgProxyHttpServer
    'sgProxyHttpServerRequests'          => array('descr' => '', 'ds_min' => '0'), //193132381
    'sgProxyHttpServerErrors'            => array('descr' => '', 'ds_min' => '0'), //7774954
    'sgProxyHttpServerInBytes'           => array('descr' => '', 'ds_min' => '0'), //2340862768156
    'sgProxyHttpServerOutBytes'          => array('descr' => '', 'ds_min' => '0'), //108911530133

    //sgProxyHttpConnections
    'sgProxyHttpClientConnections'       => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //173
    'sgProxyHttpClientConnectionsActive' => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //142
    'sgProxyHttpClientConnectionsIdle'   => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //31
    'sgProxyHttpServerConnections'       => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //140
    'sgProxyHttpServerConnectionsActive' => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //140
    'sgProxyHttpServerConnectionsIdle'   => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE')  //0
  )
);

// This needs graphs
$table_defs['BLUECOAT-SG-PROXY-MIB']['sgProxyHttpResponse'] = array(
  'mib'           => 'BLUECOAT-SG-PROXY-MIB',
  'mib_dir'       => 'bluecoat',
  'table'         => 'sgProxyHttpResponse', // Group sgProxyHttpResponse contains sgProxyHttpResponseTime,sgProxyHttpResponseFirstByte,sgProxyHttpResponseByteRate,sgProxyHttpResponseSize
  'ds_rename'     => array('sgProxy' => '', 'Connections' => 'Conn', 'Idle' => 'Id', 'Active' => 'Ac', 'Service' => 'Svc'),
  'graphs'        => array(),
  'oids'          => array(

    //sgProxyHttpResponse
    'sgProxyHttpServiceTimeAll'           => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //29740
    'sgProxyHttpServiceTimeHit'           => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //177
    'sgProxyHttpServiceTimePartialHit'    => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //2426
    'sgProxyHttpServiceTimeMiss'          => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //34122
    'sgProxyHttpTotalFetchTimeAll'        => array('descr' => '', 'ds_min' => '0'), //6053086552347
    'sgProxyHttpTotalFetchTimeHit'        => array('descr' => '', 'ds_min' => '0'), //4313032486
    'sgProxyHttpTotalFetchTimePartialHit' => array('descr' => '', 'ds_min' => '0'), //388996661
    'sgProxyHttpTotalFetchTimeMiss'       => array('descr' => '', 'ds_min' => '0'), //6048531757851
    'sgProxyHttpFirstByteAll'             => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //654
    'sgProxyHttpFirstByteHit'             => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //65
    'sgProxyHttpFirstBytePartialHit'      => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //326
    'sgProxyHttpFirstByteMiss'            => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //742
    'sgProxyHttpByteRateAll'              => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //354684
    'sgProxyHttpByteRateHit'              => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //1680335
    'sgProxyHttpByteRatePartialHit'       => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //398246
    'sgProxyHttpByteRateMiss'             => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //176162
    'sgProxyHttpResponseSizeAll'          => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //11908
    'sgProxyHttpResponseSizeHit'          => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //21767
    'sgProxyHttpResponseSizePartialHit'   => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE'), //206318
    'sgProxyHttpResponseSizeMiss'         => array('descr' => '', 'ds_min' => '0', 'ds_type' => 'GAUGE')  //10497
  )
);

// EOF
