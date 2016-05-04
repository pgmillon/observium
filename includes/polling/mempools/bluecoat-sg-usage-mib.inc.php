<?php

// ProxyAV devices hide their CPUs/Memory/Interfaces in here
echo(" BLUECOAT-SG-USAGE-MIB ");

$av_array = snmpwalk_cache_oid($device, "deviceUsage", array(), "BLUECOAT-SG-USAGE-MIB", mib_dirs('bluecoat'));

$index            = $mempool['mempool_index'];
$mempool['perc'] = $av_array[$index]['deviceUsagePercent'];;

unset ($av_array);

// EOF
