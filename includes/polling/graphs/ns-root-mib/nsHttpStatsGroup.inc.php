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

$table_defs['NS-ROOT-MIB']['nsHttpStatsGroup'] = array (
  'table'     => 'nsHttpStatsGroup',
  'numeric'   => '.1.3.6.1.4.1.5951.4.1.1.48',
  'mib'       => 'NS-ROOT-MIB',
  'mib_dir'   => 'citrix',
  'file'      => 'nsHttpStatsGroup.rrd',
  'descr'     => 'Netscaler HTTP Statistics',
  'graphs'    => array('nsHttpRequests', 'nsHttpBytes', 'nsHttpSPDY'),
  'ds_rename' => array('http' => ''),
  'oids'      => array(
    'httpTotGets'                 => array('numeric' => '45', 'descr' => 'Total number of HTTP requests received with the GET method.', 'ds_min' => '0'),
    'httpTotPosts'                => array('numeric' => '46', 'descr' => 'Total number of HTTP requests received with the POST method.', 'ds_min' => '0'),
    'httpTotOthers'               => array('numeric' => '47', 'descr' => 'Total number of HTTP requests received with methods other than GET and POST. Some of the other well-defined HTTP methods are HEAD, PUT, DELETE, OPTIONS, and TRACE. User-defined methods are also allowed.', 'ds_min' => '0'),
    'httpTotRxRequestBytes'       => array('numeric' => '48', 'descr' => 'Total number of bytes of HTTP request data received.', 'ds_min' => '0'),
    'httpTotRxResponseBytes'      => array('numeric' => '49', 'descr' => 'Total number of bytes of HTTP response data received.', 'ds_min' => '0'),
    'httpTotTxRequestBytes'       => array('numeric' => '50', 'descr' => 'Total number of bytes of HTTP request data transmitted.', 'ds_min' => '0'),
    'httpTotTxResponseBytes'      => array('numeric' => '51', 'descr' => 'Total number of bytes of HTTP response data transmitted.', 'ds_min' => '0'),
    'httpTot10Requests'           => array('numeric' => '52', 'descr' => 'Total number of HTTP/1.0 requests received. '),
    'httpTotResponses'            => array('numeric' => '53', 'descr' => 'Total number of HTTP responses sent.', 'ds_min' => '0'),
    'httpTot10Responses'          => array('numeric' => '54', 'descr' => 'Total number of HTTP/1.0 responses sent.', 'ds_min' => '0'),
    'httpTotClenResponses'        => array('numeric' => '55', 'descr' => 'Total number of HTTP responses sent in which the Content-length field of the HTTP header has been set. Content-length specifies the length of the content, in bytes, in the associated HTTP body.', 'ds_min' => '0'),
    'httpTotChunkedResponses'     => array('numeric' => '56', 'descr' => 'Total number of HTTP responses sent in which the Transfer-Encoding field of the HTTP header has been set to chunked. This setting is used when the server wants to start sending the response before knowing its total length. The server breaks the response into chunks and sends them in sequence, inserting the length of each chunk before the actual data. The message ends with a chunk of size zero.', 'ds_min' => '0'),
    'httpErrIncompleteRequests'   => array('numeric' => '57', 'descr' => 'Total number of HTTP requests received in which the header spans more than one packet.', 'ds_min' => '0'),
    'httpErrIncompleteResponses'  => array('numeric' => '58', 'descr' => 'Total number of HTTP responses received in which the header spans more than one packet.', 'ds_min' => '0'),
    'httpErrIncompleteHeaders'    => array('numeric' => '60', 'descr' => 'Total number of HTTP requests and responses received in which the HTTP header spans more than one packet.', 'ds_min' => '0'),
    'httpErrServerBusy'           => array('numeric' => '61', 'descr' => 'Total number of HTTP error responses received. Some of the error responses are: 500 Internal Server Error 501 Not Implemented 502 Bad Gateway 503 Service Unavailable 504 Gateway Timeout 505 HTTP Version Not Supported.', 'ds_min' => '0'),
    'httpTotChunkedRequests'      => array('numeric' => '62', 'descr' => 'Total number of HTTP requests in which the Transfer-Encoding field of the HTTP header has been set to chunked. '),
    'httpTotClenRequests'         => array('numeric' => '63', 'descr' => 'Total number of HTTP requests in which the Content-length field of the HTTP header has been set. Content-length specifies the length of the content, in bytes, in the associated HTTP body.', 'ds_min' => '0'),
    'httpErrLargeContent'         => array('numeric' => '64', 'descr' => 'Total number of requests and responses received with large body.', 'ds_min' => '0'),
    'httpErrLargeCtlen'           => array('numeric' => '65', 'descr' => 'Total number of requests received with large content, in which the Content-length field of the HTTP header has been set. Content-length specifies the length of the content, in bytes, in the associated HTTP body.', 'ds_min' => '0'),
    'httpErrLargeChunk'           => array('numeric' => '66', 'descr' => 'Total number of requests received with large chunk size, in which the Transfer-Encoding field of the HTTP header has been set to chunked.', 'ds_min' => '0'),
    'httpTotRequests'             => array('numeric' => '67', 'descr' => 'Total number of HTTP requests received.', 'ds_min' => '0'),
    'httpTot11Requests'           => array('numeric' => '68', 'descr' => 'Total number of HTTP/1.1 requests received.', 'ds_min' => '0'),
    'httpTot11Responses'          => array('numeric' => '69', 'descr' => 'Total number of HTTP/1.1 responses sent.', 'ds_min' => '0'),
    'httpTotNoClenChunkResponses' => array('numeric' => '70', 'descr' => 'Total number of FIN-terminated responses sent. In FIN-terminated responses, the server finishes sending the data and closes the connection.', 'ds_min' => '0'),
    'httpErrNoreuseMultipart'     => array('numeric' => '71', 'descr' => 'Total number of HTTP multi-part responses sent. In multi-part responses, one or more entities are encapsulated within the body of a single message.', 'ds_min' => '0'),
    'spdy2TotStreams'             => array('numeric' => '72', 'descr' => 'Total number of requests received over SPDY.')
  )
);

// EOF
