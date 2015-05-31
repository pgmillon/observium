<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Graph sections is used to categorize /device/graphs/

$config['graph_sections'] = array('general', 'system', 'firewall', 'netstats', 'wireless',
                                  'storage', 'vpdn', 'appliance', 'poller', 'netapp',
                                  'netscaler_tcp' => 'Netscaler TCP', 'netscaler_ssl' => 'Netscaler SSL',
                                  'netscaler_http' => 'Netscaler HTTP', 'netscaler_comp' => 'Netscaler Compression',
                                  'proxysg' => 'Proxy SG');

// Graph types

$config['graph_types']['port']['bits']       = array('name' => 'Bits',            'descr' => "Traffic in bits/sec");
$config['graph_types']['port']['upkts']      = array('name' => 'Ucast Pkts',      'descr' => "Unicast packets/sec");
$config['graph_types']['port']['nupkts']     = array('name' => 'NU Pkts',         'descr' => "Non-unicast packets/sec");
$config['graph_types']['port']['pktsize']    = array('name' => 'Pkt Size',        'descr' => "Average packet size");
$config['graph_types']['port']['percent']    = array('name' => 'Percent',         'descr' => "Percent utilization");
$config['graph_types']['port']['errors']     = array('name' => 'Errors',          'descr' => "Errors/sec");
$config['graph_types']['port']['etherlike']  = array('name' => 'Ethernet Errors', 'descr' => "Detailed Errors/sec for Ethernet-like interfaces");
$config['graph_types']['port']['fdb_count']  = array('name' => 'FDB counts',      'descr' => "FDB count");

$config['graph_types']['device']['wifi_clients']['section'] = 'wireless';
$config['graph_types']['device']['wifi_clients']['order'] = '0';
$config['graph_types']['device']['wifi_clients']['descr'] = 'Wireless Clients';

// NetApp graphs
$config['graph_types']['device']['netapp_ops']     = array('section' => 'netapp', 'descr' => 'NetApp Operations', 'order' => '0');
$config['graph_types']['device']['netapp_net_io']  = array('section' => 'netapp', 'descr' => 'NetApp Network I/O', 'order' => '1');
$config['graph_types']['device']['netapp_disk_io'] = array('section' => 'netapp', 'descr' => 'NetApp Disk I/O', 'order' => '2');
$config['graph_types']['device']['netapp_tape_io'] = array('section' => 'netapp', 'descr' => 'NetApp Tape I/O', 'order' => '3');
$config['graph_types']['device']['netapp_cp_ops']  = array('section' => 'netapp', 'descr' => 'NetApp Checkpoint Operations', 'order' => '4');

// Poller graphs

$config['graph_types']['device']['poller_perf']    = array(
  'section'   => 'poller',
  'descr'     => 'Poller Duration',
  'file'      => 'perf-poller.rrd',
  'colours'   => 'greens',
  'unit_text' => ' ',
  'ds'        => array (
    'val'   => array ('label' => 'Seconds', 'draw' => 'AREA', 'line' => TRUE)
  )
);

$config['graph_types']['device']['ping'] = array(
  'section'   => 'poller',
  'descr'     => 'Ping Response',
  'file'      => 'ping.rrd',
  'colours'   => 'reds',
  'unit_text' => 'Milliseconds',
  'ds'        => array(
    'ping' => array('label' => 'Ping', 'draw' => 'AREA', 'line' => TRUE)
  )
);

$config['graph_types']['device']['ping_snmp'] = array(
  'section'   => 'poller',
  'descr'     => 'SNMP Response',
  'file'      => 'ping_snmp.rrd',
  'colours'   => 'blues',
  'unit_text' => 'Milliseconds',
  'ds'        => array(
    'ping_snmp' => array('label' => 'SNMP', 'draw' => 'AREA', 'line' => TRUE)
  )
);

$config['graph_types']['device']['agent']['section'] = 'poller';
$config['graph_types']['device']['agent']['order'] = '0';
$config['graph_types']['device']['agent']['descr'] = 'Agent Execution Time';

$config['graph_types']['device']['netstat_arista_sw_ip'] = array(
  'section' => 'netstats',
  'order' => '0',
  'descr' => "Software forwarded IPv4 Statistics"
);
$config['graph_types']['device']['netstat_arista_sw_ip_frag'] = array(
  'section' => 'netstats',
  'order' => '0',
  'descr' => "Software forwarded IPv4 Fragmentation Statistics"
);
$config['graph_types']['device']['netstat_arista_sw_ip6'] = array(
  'section' => 'netstats',
  'order' => '0',
  'descr' => "Software forwarded IPv6 Statistics"
);
$config['graph_types']['device']['netstat_arista_sw_ip6_frag'] = array(
  'section' => 'netstats',
  'order' => '0',
  'descr' => "Software forwarded IPv6 Fragmentation Statistics"
);

$config['graph_types']['device']['cipsec_flow_bits']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_bits']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_bits']['descr'] = 'IPSec Tunnel Traffic Volume';
$config['graph_types']['device']['cipsec_flow_pkts']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_pkts']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_pkts']['descr'] = 'IPSec Tunnel Traffic Packets';
$config['graph_types']['device']['cipsec_flow_stats']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_stats']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_stats']['descr'] = 'IPSec Tunnel Statistics';
$config['graph_types']['device']['cipsec_flow_tunnels']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_tunnels']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_tunnels']['descr'] = 'IPSec Active Tunnels';
$config['graph_types']['device']['cras_sessions']['section'] = 'firewall';
$config['graph_types']['device']['cras_sessions']['order'] = '0';
$config['graph_types']['device']['cras_sessions']['descr'] = 'Remote Access Sessions';
$config['graph_types']['device']['fortigate_sessions']['section'] = 'firewall';
$config['graph_types']['device']['fortigate_sessions']['order'] = '0';
$config['graph_types']['device']['fortigate_sessions']['descr'] = 'Active Sessions';
$config['graph_types']['device']['fortigate_cpu']['section'] = 'system';
$config['graph_types']['device']['fortigate_cpu']['order'] = '0';
$config['graph_types']['device']['fortigate_cpu']['descr'] = 'CPU';
$config['graph_types']['device']['screenos_sessions']['section'] = 'firewall';
$config['graph_types']['device']['screenos_sessions']['order'] = '0';
$config['graph_types']['device']['screenos_sessions']['descr'] = 'Active Sessions';
$config['graph_types']['device']['panos_sessions']['section'] = 'firewall';
$config['graph_types']['device']['panos_sessions']['order'] = '0';
$config['graph_types']['device']['panos_sessions']['descr'] = 'Active Sessions';

// CHECKPOINT-MIB

$config['graph_types']['device']['checkpoint_connections'] = array(
  'section'   => 'firewall',
  'descr'     => 'Concurrent Connections',
  'file'      => 'checkpoint-mib_fw.rrd',
  'scale_min' => '0',
  'colours'   => 'mixed',
  'unit_text' => 'Concurrent Connections',
  'ds'        => array(
    'NumConn'     => array('label' => 'Current', 'draw' => 'LINE'),
    'PeakNumConn' => array('label' => 'Peak',    'draw' => 'LINE'),
  )
);

$config['graph_types']['device']['checkpoint_packets']    = array(
  'section'   => 'firewall',
  'descr'     => 'Packets',
  'file'      => 'checkpoint-mib_fw.rrd',
  'colours'   => 'mixed',
  'unit_text' => 'Packets',
  'ds'        => array(
    'Accepted'   => array('label' => 'Accepted', 'draw' => 'LINE'),
    'Rejected'   => array('label' => 'Rejected', 'draw' => 'LINE'),
    'Dropped'    => array('label' => 'Dropped',  'draw' => 'LINE'),
    'Logged'     => array('label' => 'Logged',   'draw' => 'LINE')
  )
);

// Not enabled
$config['graph_types']['device']['checkpoint_packets_rate'] = array(
  'section'   => 'firewall',
  'descr'     => 'Packets Rate',
  'file'      => 'checkpoint-mib_fw.rrd',
  'colours'   => 'mixed',
  'unit_text' => 'Packets/s',
  'ds'        => array(
    'PacketsRate'       => array('label' => 'Packets',        'draw' => 'LINE'),
    'AcceptedBytesRate' => array('label' => 'Accepted Bytes', 'draw' => 'LINE'),
    'DroppedBytesRate'  => array('label' => 'Dropped Bytes',  'draw' => 'LINE'),
    'DroppedRate'       => array('label' => 'Dropped Total',  'draw' => 'LINE'),
  )
);

$config['graph_types']['device']['checkpoint_vpn_sa']    = array(
  'section'   => 'firewall',
  'descr'     => 'VPN IKE/IPSec SAs',
  //'file'      => 'checkpoint-mib_cpvikeglobals.rrd',
  'scale_min' => '0',
  'colours'   => 'mixed',
  'unit_text' => 'IKE/IPSec SAs',
  'ds'        => array(
    'IKECurrSAs'     => array('label' => 'IKE SAs',       'draw' => 'LINE', 'file' => 'checkpoint-mib_cpvikeglobals.rrd'),
    'CurrEspSAsIn'   => array('label' => 'IPSec SAs in',  'draw' => 'LINE', 'file' => 'checkpoint-mib_cpvsastatistics.rrd'),
    'CurrEspSAsOut'  => array('label' => 'IPSec SAs out', 'draw' => 'LINE', 'file' => 'checkpoint-mib_cpvsastatistics.rrd')
  )
);

$config['graph_types']['device']['checkpoint_vpn_packets']    = array(
  'section'   => 'firewall',
  'descr'     => 'VPN Packets',
  'file'      => 'checkpoint-mib_cpvgeneral.rrd',
  'scale_min' => '0',
  'colours'   => 'mixed',
  'unit_text' => 'Packets/s',
  'ds'        => array(
    'EncPackets' => array('label' => 'Encrypted', 'draw' => 'LINE'),
    'DecPackets' => array('label' => 'Decrypted', 'draw' => 'LINE')
  )
);

$config['graph_types']['device']['checkpoint_memory']    = array(
  'section'   => 'firewall',
  'descr'     => 'Kernel / Hash memory',
  'file'      => 'checkpoint-mib_fwkmem.rrd',
  'scale_min' => '0',
  'colours'   => 'mixed',
  'unit_text' => 'Bytes',
  'ds'        => array(
    'Kmem-bytes-used'   => array('label' => 'Kmem used',   'draw' => 'LINE'),
    'Kmem-bytes-unused' => array('label' => 'Kmem unused', 'draw' => 'LINE'),
    'Kmem-bytes-peak'   => array('label' => 'Kmem peak',   'draw' => 'LINE'),
    'Hmem-bytes-used'   => array('label' => 'Hmem used',   'draw' => 'LINE', 'file' => 'checkpoint-mib_fwhmem.rrd'),
    'Hmem-bytes-unused' => array('label' => 'Hmem unused', 'draw' => 'LINE', 'file' => 'checkpoint-mib_fwhmem.rrd'),
    'Hmem-bytes-peak'   => array('label' => 'Hmem peak',   'draw' => 'LINE', 'file' => 'checkpoint-mib_fwhmem.rrd')
  )
);

$config['graph_types']['device']['checkpoint_memory_operations']    = array(
  'section'   => 'firewall',
  'descr'     => 'Kernel / Hash memory operations',
  'file'      => 'checkpoint-mib_fwkmem.rrd',
  'scale_min' => '0',
  'colours'   => 'mixed',
  'unit_text' => 'Operations/s',
  'ds'        => array(
    'Kmem-alc-operations'  => array('label' => 'Kmem alloc',         'draw' => 'LINE'),
    'Kmem-free-operation'  => array('label' => 'Kmem free',          'draw' => 'LINE'),
    'Kmem-failed-alc'      => array('label' => 'Kmem failed alloc',  'draw' => 'LINE'),
    'Kmem-failed-free'     => array('label' => 'Kmem failed free',   'draw' => 'LINE'),
    'Hmem-alc-operations'  => array('label' => 'Hmem alloc',         'draw' => 'LINE', 'file' => 'checkpoint-mib_fwhmem.rrd'),
    'Hmem-free-operation'  => array('label' => 'Hmem free',          'draw' => 'LINE', 'file' => 'checkpoint-mib_fwhmem.rrd'),
    'Hmem-failed-alc'      => array('label' => 'Hmem failed alloc',  'draw' => 'LINE', 'file' => 'checkpoint-mib_fwhmem.rrd'),
    'Hmem-failed-free'     => array('label' => 'Hmem failed free',   'draw' => 'LINE', 'file' => 'checkpoint-mib_fwhmem.rrd')
  )
);

$config['graph_types']['device']['juniperive_users']['section'] = 'appliance';
$config['graph_types']['device']['juniperive_users']['order'] = '0';
$config['graph_types']['device']['juniperive_users']['descr'] = 'Concurrent Users';
$config['graph_types']['device']['juniperive_meetings']['section'] = 'appliance';
$config['graph_types']['device']['juniperive_meetings']['order'] = '0';
$config['graph_types']['device']['juniperive_meetings']['descr'] = 'Meetings';
$config['graph_types']['device']['juniperive_connections']['section'] = 'appliance';
$config['graph_types']['device']['juniperive_connections']['order'] = '0';
$config['graph_types']['device']['juniperive_connections']['descr'] = 'Connections';
$config['graph_types']['device']['juniperive_storage']['section'] = 'appliance';
$config['graph_types']['device']['juniperive_storage']['order'] = '0';
$config['graph_types']['device']['juniperive_storage']['descr'] = 'Storage';

$config['graph_types']['device']['bits']['section'] = 'netstats';
$config['graph_types']['device']['bits']['order'] = '0';
$config['graph_types']['device']['bits']['descr'] = 'Total Traffic';
$config['graph_types']['device']['ipsystemstats_ipv4']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv4']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv4']['descr'] = 'IPv4 Packet Statistics';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['descr'] = 'IPv4 Fragmentation Statistics';
$config['graph_types']['device']['ipsystemstats_ipv6']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv6']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv6']['descr'] = 'IPv6 Packet Statistics';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['descr'] = 'IPv6 Fragmentation Statistics';
$config['graph_types']['device']['netstat_icmp_info']['section'] = 'netstats';
$config['graph_types']['device']['netstat_icmp_info']['order'] = '0';
$config['graph_types']['device']['netstat_icmp_info']['descr'] = 'ICMP Informational Statistics';
$config['graph_types']['device']['netstat_icmp']['section'] = 'netstats';
$config['graph_types']['device']['netstat_icmp']['order'] = '0';
$config['graph_types']['device']['netstat_icmp']['descr'] = 'ICMP Statistics';
$config['graph_types']['device']['netstat_ip']['section'] = 'netstats';
$config['graph_types']['device']['netstat_ip']['order'] = '0';
$config['graph_types']['device']['netstat_ip']['descr'] = 'IP Statistics';
$config['graph_types']['device']['netstat_ip_frag']['section'] = 'netstats';
$config['graph_types']['device']['netstat_ip_frag']['order'] = '0';
$config['graph_types']['device']['netstat_ip_frag']['descr'] = 'IP Fragmentation Statistics';

$config['graph_types']['device']['netstat_snmp_stats']           = array(
                                                                'section' => 'netstats',
                                                                'order'   => '0',
                                                                'descr'   => 'SNMP Statistics');

$config['graph_types']['device']['netstat_snmp_packets']    = array(
                                                                'section' => 'netstats',
                                                                'order'   => '0',
                                                                'descr'   => 'SNMP Packets');

$config['graph_types']['device']['netstat_tcp_stats']            = array(
                                                                'section' => 'netstats',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Statistics');

$config['graph_types']['device']['netstat_tcp_currestab']       = array(
                                                                'section' => 'netstats',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Established Connections');

$config['graph_types']['device']['netstat_tcp_segments']    = array(
                                                                'section' => 'netstats',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Segments');

$config['graph_types']['device']['netstat_udp_errors']         = array(
                                                                'section' => 'netstats',
                                                                'order'   => '0',
                                                                'descr'   => 'UDP Errors');

$config['graph_types']['device']['netstat_udp_datagrams']    = array(
                                                                'section' => 'netstats',
                                                                'order'   => '0',
                                                                'descr'   => 'UDP Datagrams');

$config['graph_types']['device']['fdb_count']['section'] = 'system';
$config['graph_types']['device']['fdb_count']['order'] = '0';
$config['graph_types']['device']['fdb_count']['descr'] = 'FDB Table Usage';

$config['graph_types']['device']['hr_processes'] = array(
  'section'   => 'system',
  'descr'     => 'Running Processes',
  'file'      => 'hr_processes.rrd',
  'colours'   => 'pinks',
  'unit_text' => ' ',
  'ds'        => array(
    'procs' => array('label' => 'Processes', 'draw' => 'AREA', 'line' => TRUE)
  )
);

$config['graph_types']['device']['hr_users'] = array(
  'section'   => 'system',
  'descr'     => 'Users Logged In',
  'file'      => 'hr_users.rrd',
  'colours'   => 'greens',
  'unit_text' => ' ',
  'ds'        => array(
    'users' => array('label' => 'Users', 'draw' => 'AREA', 'line' => TRUE)
  )
);

$config['graph_types']['device']['mempool']['section'] = 'system';
$config['graph_types']['device']['mempool']['order'] = '0';
$config['graph_types']['device']['mempool']['descr'] = 'Memory Pool Usage';
$config['graph_types']['device']['processor']['section'] = 'system';
$config['graph_types']['device']['processor']['order'] = '0';
$config['graph_types']['device']['processor']['descr'] = 'Processors';
$config['graph_types']['device']['storage']['section'] = 'system';
$config['graph_types']['device']['storage']['order'] = '0';
$config['graph_types']['device']['storage']['descr'] = 'Filesystem Usage';

$config['graph_types']['device']['ucd_cpu']['section'] = 'system';
$config['graph_types']['device']['ucd_cpu']['order'] = '0';
$config['graph_types']['device']['ucd_cpu']['descr'] = 'Detailed Processors';

$config['graph_types']['device']['ucd_load'] = array(
  'section'   => 'system',
  'descr'     => 'Load Averages',
  'file'      => 'ucd_load.rrd',
  'unit_text' => 'Load Average',
  'no_mag'    => TRUE,
  'num_fmt'   => '5.2',
  'ds'        => array(
    '1min'   => array('label' => '1 Min',  'colour' => 'c5aa00', 'cdef' => '1min,100,/'),
    '5min'   => array('label' => '5 Min',  'colour' => 'ea8f00', 'cdef' => '5min,100,/'),
    '15min'  => array('label' => '15 Min', 'colour' => 'cc0000', 'cdef' => '15min,100,/')
  )
);

$config['graph_types']['device']['ucd_memory']['section'] = 'system';
$config['graph_types']['device']['ucd_memory']['order'] = '0';
$config['graph_types']['device']['ucd_memory']['descr'] = 'Detailed Memory';
$config['graph_types']['device']['ucd_swap_io']['section'] = 'system';
$config['graph_types']['device']['ucd_swap_io']['order'] = '0';
$config['graph_types']['device']['ucd_swap_io']['descr'] = 'Swap I/O Activity';
$config['graph_types']['device']['ucd_io']['section'] = 'system';
$config['graph_types']['device']['ucd_io']['order'] = '0';
$config['graph_types']['device']['ucd_io']['descr'] = 'System I/O Activity';

$config['graph_types']['device']['ucd_contexts'] = array(
  'section'   => 'system',
  'descr'     => 'Context Switches',
  'file'      => 'ucd_ssRawContexts.rrd',
  'colours'   => 'blues',
  'unit_text' => ' ',
  'ds'        => array(
    'value' => array('label' => 'Switches/s', 'draw' => 'AREA', 'line' => TRUE)
  )
);

$config['graph_types']['device']['ucd_interrupts'] = array(
  'section'   => 'system',
  'descr'     => 'System Interrupts',
  'file'      => 'ucd_ssRawInterrupts.rrd',
  'colours'   => 'reds',
  'unit_text' => ' ',
  'ds'        => array(
    'value' => array('label' => 'Interrupts/s', 'draw' => 'AREA', 'line' => TRUE)
  )
);

$config['graph_types']['device']['uptime'] = array(
  'section'   => 'system',
  'descr'     => 'Device Uptime',
  'file'      => 'uptime.rrd',
  'unit_text' => ' ',
  'ds'        => array(
    'uptime' => array('label' => 'Days Uptime', 'draw' => 'AREA', 'line' => TRUE, 'colour' => 'c5c5c5', 'cdef' => 'uptime,86400,/', 'rra_min' => FALSE, 'rra_max' => FALSE)
  )
);

$config['graph_types']['device']['ksm_pages']['section']           = 'system';
$config['graph_types']['device']['ksm_pages']['order']             = '0';
$config['graph_types']['device']['ksm_pages']['descr']             = 'KSM Shared Pages';

$config['graph_types']['device']['iostat_util']['section']         = 'system';
$config['graph_types']['device']['iostat_util']['order']           = '0';
$config['graph_types']['device']['iostat_util']['descr']           = 'Disk I/O Utilisation';

$config['graph_types']['device']['vpdn_sessions_l2tp']['section']  = 'vpdn';
$config['graph_types']['device']['vpdn_sessions_l2tp']['order']    = '0';
$config['graph_types']['device']['vpdn_sessions_l2tp']['descr']    = 'VPDN L2TP Sessions';

$config['graph_types']['device']['vpdn_tunnels_l2tp']['section']   = 'vpdn';
$config['graph_types']['device']['vpdn_tunnels_l2tp']['order']     = '0';
$config['graph_types']['device']['vpdn_tunnels_l2tp']['descr']     = 'VPDN L2TP Tunnels';

// ALVARION-DOT11-WLAN-MIB

$config['graph_types']['device']['alvarion_events'] = array(
  'section'   => 'wireless',
  'file'      => 'alvarion-events.rrd',
  'descr'     => 'Network events',
  'colours'   => 'mixed',
  'unit_text' => 'Events/s',
  'ds'        => array(
    'TotalTxEvents'      => array('label' => 'Total TX',      'draw' => 'LINE'),
    'TotalRxEvents'      => array('label' => 'Total RX',      'draw' => 'LINE'),
    'OthersTxEvents'     => array('label' => 'Other TX',      'draw' => 'LINE'),
    'RxDecryptEvents'    => array('label' => 'Decrypt RX',    'draw' => 'LINE'),
    'OverrunEvents'      => array('label' => 'Overrun',       'draw' => 'LINE'),
    'UnderrunEvents'     => array('label' => 'Underrun',      'draw' => 'LINE'),
    'DroppedFrameEvents' => array('label' => 'Dropped Frame', 'draw' => 'LINE'),
  )
);

$config['graph_types']['device']['alvarion_frames_errors'] = array(
  'section'   => 'wireless',
  'file'      => 'alvarion-frames-errors.rrd',
  'descr'     => 'Other frames errors',
  'colours'   => 'mixed',
  'unit_text' => 'Frames/s',
  'ds'        => array(
    'FramesDelayedDueToS' => array('label' => 'Delayed Due To Sw Retry',     'draw' => 'LINE'),
    'FramesDropped'       => array('label' => 'Dropped Frames',              'draw' => 'LINE'),
    'RecievedBadFrames'   => array('label' => 'Recieved Bad Frames',         'draw' => 'LINE'),
    'NoOfDuplicateFrames' => array('label' => 'Discarded Duplicate Frames',  'draw' => 'LINE'),
    'NoOfInternallyDisca' => array('label' => 'Internally Discarded MirCir', 'draw' => 'LINE'),
  )
);

$config['graph_types']['device']['alvarion_errors'] = array(
  'section'   => 'wireless',
  'file'      => 'alvarion-errors.rrd',
  'descr'     => 'Unidentified signals and CRC errors',
  'colours'   => 'mixed',
  'unit_text' => 'Frames/s',
  'ds'        => array(
    'PhyErrors' => array('label' => 'Phy Errors', 'draw' => 'LINE'),
    'CRCErrors' => array('label' => 'CRC Errors', 'draw' => 'LINE'),
  )
);

$config['graph_types']['device']['netscaler_tcp_conn']['section']  = 'netscaler_tcp';
$config['graph_types']['device']['netscaler_tcp_conn']['order']    = '0';
$config['graph_types']['device']['netscaler_tcp_conn']['descr']    = 'TCP Connections';

$config['graph_types']['device']['netscaler_tcp_bits']['section']  = 'netscaler_tcp';
$config['graph_types']['device']['netscaler_tcp_bits']['order']    = '0';
$config['graph_types']['device']['netscaler_tcp_bits']['descr']    = 'TCP Traffic';

$config['graph_types']['device']['netscaler_tcp_pkts']['section']  = 'netscaler_tcp';
$config['graph_types']['device']['netscaler_tcp_pkts']['order']    = '0';
$config['graph_types']['device']['netscaler_tcp_pkts']['descr']    = 'TCP Packets';

$config['graph_types']['device']['netscaler_common_errors']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Common Errors');

$config['graph_types']['device']['netscaler_conn_client']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Client Connections');

$config['graph_types']['device']['netscaler_conn_clientserver']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Client and Server Connections');

$config['graph_types']['device']['netscaler_conn_current']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Current Connections');

$config['graph_types']['device']['netscaler_conn_server']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Server Connections');

$config['graph_types']['device']['netscaler_conn_spare']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Spare Connections');

$config['graph_types']['device']['netscaler_conn_zombie_flushed']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Zombie Flushed Connections');

$config['graph_types']['device']['netscaler_conn_zombie_halfclosed']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Zombie Half-Closed Connections');

$config['graph_types']['device']['netscaler_conn_zombie_halfopen']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Zombie Half-Open Connections');

$config['graph_types']['device']['netscaler_conn_zombie_packets']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Zombie Connection Packets');

$config['graph_types']['device']['netscaler_cookie_rejected']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Cookie Rejections');

$config['graph_types']['device']['netscaler_data_errors']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Data Errors');

$config['graph_types']['device']['netscaler_out_of_order']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Out Of Order');

$config['graph_types']['device']['netscaler_retransmission_error']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Retransmission Errors');

$config['graph_types']['device']['netscaler_retransmit_err']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'Retransmit Errors');

$config['graph_types']['device']['netscaler_rst_errors']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP RST Errors');

$config['graph_types']['device']['netscaler_syn_errors']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP SYN Errors');

$config['graph_types']['device']['netscaler_syn_stats']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP SYN Statistics');

$config['graph_types']['device']['netscaler_tcp_errretransmit']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Error Retransmits');

$config['graph_types']['device']['netscaler_tcp_errfullretransmit']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Error Full Retransmits');

$config['graph_types']['device']['netscaler_tcp_errpartialretransmit']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Error Partial Retransmits');

$config['graph_types']['device']['netscaler_tcp_errretransmitgiveup']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Error Retransmission Give Up');

$config['graph_types']['device']['netscaler_tcp_errfastretransmissions']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Error Fast Retransmissions');

$config['graph_types']['device']['netscaler_tcp_errxretransmissions']    = array(
                                                                'section' => 'netscaler_tcp',
                                                                'order'   => '0',
                                                                'descr'   => 'TCP Error Retransmit Count');

$config['graph_types']['device']['nsHttpRequests'] = array(
  'section'   => 'netscaler_http',
  'file'      => 'nsHttpStatsGroup.rrd',
  'descr'     => 'HTTP Requests',
  'colours'   => 'mixed',
  'unit_text' => 'Requests/s',
  'log_y'     => TRUE,
  'ds'        => array(
    'TotGets'   => array('label' => 'GETs',   'draw' => 'AREASTACK'),
    'TotPosts'  => array('label' => 'POSTs',  'draw' => 'AREASTACK'),
    'TotOthers' => array('label' => 'Others', 'draw' => 'AREASTACK'),
  )
);

$config['graph_types']['device']['nsHttpBytes'] = array(
  'section'   => 'netscaler_http',
  'file'      => 'nsHttpStatsGroup.rrd',
  'descr'     => 'HTTP Traffic',
  'colours'   => 'mixed',
  'ds'        => array(
    'TotRxResponseBytes' => array('label' => 'Response In',  'cdef' => 'TotRxResponseBytes,8,*', 'draw' => 'AREA'),
    'TotTxResponseBytes' => array('label' => 'Response Out', 'cdef' => 'TotRxResponseBytes,8,*', 'invert' => TRUE, 'draw' => 'AREA'),
    'TotRxRequestBytes'  => array('label' => 'Request  In',  'cdef' => 'TotRxRequestBytes,8,*'),
    'TotTxRequestBytes'  => array('label' => 'Request  Out', 'cdef' => 'TotTxRequestBytes,8,*', 'invert' => TRUE ),
  )
);

$config['graph_types']['device']['nsHttpSPDY'] = array(
  'section'   => 'netscaler_http',
  'descr'     => 'SPDY Requests',
  'file'      => 'nsHttpStatsGroup.rrd',
  'colours'   => 'mixed',
  'unit_text' => 'Requests/s',
  'log_y'     => TRUE,
  'ds'        => array(
    'spdy2TotStreams' => array('label' => 'SPDY Requests', 'draw' => 'AREA'),
  )
);

$config['graph_types']['device']['nsCompHttpSaving'] = array(
  'section'   => 'netscaler_comp',
  'descr'     => 'Bandwidth saving from TCP compression',
  'file'      => 'nsCompressionStatsGroup.rrd',
  'scale_min' => '0',
  'scale_max' => '100',
  'colours'   => 'greens',
  'unit_text' => 'Percent',
  'ds'        => array(
    'compHttpBandwidthS' => array ('label' => 'Saving', 'draw' => 'AREA'),
  )
);

$config['graph_types']['device']['nsSslTransactions'] = array(
  'section'   => 'netscaler_ssl',
  'descr'     => 'SSL Transactions',
  'file'      => 'netscaler-SslStats.rrd',
  'colours'   => 'mixed',
  'unit_text' => 'Transactions/s',
  'log_y'     => TRUE,
  'ds'        => array(
    'Transactions'      => array('label' => 'Total', 'draw' => 'AREA', 'colour' => 'B0B0B0'),
    'SSLv2Transactions' => array('label' => 'SSLv2'),
    'SSLv3Transactions' => array('label' => 'SSLv3'),
    'TLSv1Transactions' => array('label' => 'TLSv1')
  )
);

$config['graph_types']['device']['netscalersvc_bits']['descr']     = 'Aggregate Service Traffic';
$config['graph_types']['device']['netscalersvc_pkts']['descr']     = 'Aggregate Service Packets';
$config['graph_types']['device']['netscalersvc_conns']['descr']    = 'Aggregate Service Connections';
$config['graph_types']['device']['netscalersvc_reqs']['descr']     = 'Aggregate Service Requests';

$config['graph_types']['device']['netscalervsvr_bits']['descr']    = 'Aggregate vServer Traffic';
$config['graph_types']['device']['netscalervsvr_pkts']['descr']    = 'Aggregate vServer Packets';
$config['graph_types']['device']['netscalervsvr_conns']['descr']   = 'Aggregate vServer Connections';
$config['graph_types']['device']['netscalervsvr_reqs']['descr']    = 'Aggregate vServer Requests';
$config['graph_types']['device']['netscalervsvr_hitmiss']['descr'] = 'Aggregate vServer Hits/Misses';

$config['graph_types']['device']['asyncos_workq']['section'] = 'appliance';
$config['graph_types']['device']['asyncos_workq']['order'] = '0';
$config['graph_types']['device']['asyncos_workq']['descr'] = 'Work Queue Messages';

$config['graph_descr']['device_smokeping_in_all'] = "This is an aggregate graph of the incoming smokeping tests to this host. The line corresponds to the average RTT. The shaded area around each line denotes the standard deviation.";
$config['graph_descr']['device_processor']        = "This is an aggregate graph of all processors in the system.";

$config['graph_descr']['application_unbound_queries'] = "DNS queries to the recursive resolver. The unwanted replies could be innocent duplicate packets, late replies, or spoof threats.";
$config['graph_descr']['application_unbound_queue']   = "The queries that did not hit the cache and need recursion service take up space in the requestlist. If there are too many queries, first queries get overwritten, and at last resort dropped.";
$config['graph_descr']['application_unbound_memory']  = "The memory used by unbound.";
$config['graph_descr']['application_unbound_qtype']   = "Queries by DNS RR type queried for.";
$config['graph_descr']['application_unbound_class']   = "Queries by DNS RR class queried for.";
$config['graph_descr']['application_unbound_opcode']  = "Queries by DNS opcode in the query packet.";
$config['graph_descr']['application_unbound_rcode']   = "Answers sorted by return value. RRSets bogus is the number of RRSets marked bogus per second by the validator.";
$config['graph_descr']['application_unbound_flags']   = "This graphs plots the flags inside incoming queries. For example, if QR, AA, TC, RA, Z flags are set, the query can be rejected. RD, AD, CD and DO are legitimately set by some software.";

$config['graph_types']['application']['bind_answers']['descr'] = 'BIND Received Answers';
$config['graph_types']['application']['bind_query_in']['descr'] = 'BIND Incoming Queries';
$config['graph_types']['application']['bind_query_out']['descr'] = 'BIND Outgoing Queries';
$config['graph_types']['application']['bind_query_rejected']['descr'] = 'BIND Rejected Queries';
$config['graph_types']['application']['bind_req_in']['descr'] = 'BIND Incoming Requests';
$config['graph_types']['application']['bind_req_proto']['descr'] = 'BIND Request Protocol Details';
$config['graph_types']['application']['bind_resolv_dnssec']['descr'] = 'BIND DNSSEC Validation';
$config['graph_types']['application']['bind_resolv_errors']['descr'] = 'BIND Errors while Resolving';
$config['graph_types']['application']['bind_resolv_queries']['descr'] = 'BIND Resolving Queries';
$config['graph_types']['application']['bind_resolv_rtt']['descr'] = 'BIND Resolving RTT';
$config['graph_types']['application']['bind_updates']['descr'] = 'BIND Dynamic Updates';
$config['graph_types']['application']['bind_zone_maint']['descr'] = 'BIND Zone Maintenance';

// Generic Firewall Graphs

$config['graph_types']['device']['firewall_sessions_ipv4']['section']  = 'firewall';
$config['graph_types']['device']['firewall_sessions_ipv4']['order']    = '0';
$config['graph_types']['device']['firewall_sessions_ipv4']['descr']    = 'Firewall Sessions (IPv4)';

// Blue Coat ProxySG graphs
$config['graph_types']['device']['bluecoat_http_client']['section']  = 'proxysg';
$config['graph_types']['device']['bluecoat_http_client']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_client']['descr']    = 'HTTP Client Connections';
$config['graph_types']['device']['bluecoat_http_server']['section']  = 'proxysg';
$config['graph_types']['device']['bluecoat_http_server']['order']    = '0';
$config['graph_types']['device']['bluecoat_http_server']['descr']    = 'HTTP Server Connections';
$config['graph_types']['device']['bluecoat_cache']['section']  = 'proxysg';
$config['graph_types']['device']['bluecoat_cache']['order']    = '0';
$config['graph_types']['device']['bluecoat_cache']['descr']    = 'HTTP Cache Stats';
$config['graph_types']['device']['bluecoat_server']['section']  = 'proxysg';
$config['graph_types']['device']['bluecoat_server']['order']    = '0';
$config['graph_types']['device']['bluecoat_server']['descr']    = 'Server Stats';
$config['graph_types']['device']['bluecoat_tcp']['section']  = 'proxysg';
$config['graph_types']['device']['bluecoat_tcp']['order']    = '0';
$config['graph_types']['device']['bluecoat_tcp']['descr']    = 'TCP Connections';
$config['graph_types']['device']['bluecoat_tcp_est']['section']  = 'proxysg';
$config['graph_types']['device']['bluecoat_tcp_est']['order']    = '0';
$config['graph_types']['device']['bluecoat_tcp_est']['descr']    = 'TCP Established Sessions';

// EDAC agent script
$config['graph_types']['device']['edac_errors']['section'] = 'system';
$config['graph_types']['device']['edac_errors']['order']   = '0';
$config['graph_types']['device']['edac_errors']['descr']   = 'EDAC Memory Errors';
$config['graph_descr']['edac_errors']   = "This graphs plots the number of errors (corrected and uncorrected) detected by the memory controller since the system startup.";

// End includes/definitions/graphtypes.inc.php
