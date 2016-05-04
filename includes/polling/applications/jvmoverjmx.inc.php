<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Kresimir Jurasovic
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (!empty($agent_data['app']['jvmoverjmx']))
{

  foreach ($agent_data['app']['jvmoverjmx'] as $instance => $instanceData)
  {
    $jvmoverjmx = $instanceData;

    $app_id = discover_app($device, 'jvmoverjmx', $instance);

    $rrd_filename = "app-jvmoverjmx-$app_id.rrd";

    echo(" jvmoverjmx statistics".PHP_EOL);

    $jmxdata =  explode("\n", $jvmoverjmx);

    $jvm_vendor = "";
    $jvm_version = "";
    $jvm_uptime = 0;
    $jvm_heap_memory_max_usage = 0;
    $jvm_heap_memory_used = 0;
    $jvm_non_heap_memory_max = 0;
    $jvm_non_heap_memory_used = 0;
    $jvm_eden_space_max = 0;
    $jvm_eden_space_used = 0;
    $jvm_perm_gen_max = 0;
    $jvm_perm_gen_used = 0;
    $jvm_old_gen_max = 0;
    $jvm_old_gen_used = 0;
    $jvm_daemon_threads = 0;
    $jvm_total_threads = 0;
    $jvm_loaded_class_count = 0;
    $jvm_unloaded_class_count = 0;
    $jvm_g1_old_generation_collection_count = 0;
    $jvm_g1_old_generation_collection_time = 0;
    $jvm_g1_young_generation_collection_count = 0;
    $jvm_g1_young_generation_collection_time = 0;
    $jvm_cms_collection_count = 0;
    $jvm_cms_collection_time = 0;
    $jvm_par_new_collection_count = 0;
    $jvm_par_new_collection_time = 0;
    $jvm_copy_collection_count = 0;
    $jvm_copy_collection_time = 0;
    $jvm_ps_mark_sweep_collection_count = 0;
    $jvm_ps_mark_sweep_collection_time = 0;
    $jvm_ps_scavenge_collection_count = 0;
    $jvm_ps_scavenge_collection_time = 0;

    foreach ($jmxdata as $jmxdataValue) {
      list($key, $value) = explode(":", $jmxdataValue);

      $key = trim($key);
      $value = trim($value);

      switch ($key) {
        case "Vendor":
          $jvm_vendor = $value;
          break;
        case "Version":
          $jvm_version = $value;
          break;
        case "UpTime":
          $jvm_uptime = $value;
          break;
        case "HeapMemoryMaxUsage":
          $jvm_heap_memory_max_usage = $value;
          break;
        case "HeapMemoryUsed":
          $jvm_heap_memory_used = $value;
          break;
        case "NonHeapMemoryMax":
          $jvm_non_heap_memory_max = $value;
          break;
        case "NonHeapMemoryUsed":
          $jvm_non_heap_memory_used = $value;
          break;
        case "EdenSpaceMax":
          $jvm_eden_space_max = $value;
          break;
        case "EdenSpaceUsed":
          $jvm_eden_space_used = $value;
          break;
        case "PermGenMax":
          $jvm_perm_gen_max = $value;
          break;
        case "PermGenUsed":
          $jvm_perm_gen_used = $value;
          break;
        case "OldGenMax":
          $jvm_old_gen_max = $value;
          break;
        case "OldGenUsed":
          $jvm_old_gen_used = $value;
          break;
        case "DaemonThreads":
          $jvm_daemon_threads = $value;
          break;
        case "TotalThreads":
          $jvm_total_threads = $value;
          break;
        case "LoadedClassCount":
          $jvm_loaded_class_count = $value;
          break;
        case "UnloadedClassCount":
          $jvm_unloaded_class_count = $value;
          break;
        case "G1OldGenCollectionCount":
          $jvm_g1_old_generation_collection_count = $value;
          break;
        case "G1OldGenCollectionTime":
          $jvm_g1_old_generation_collection_time = $value;
          break;
        case "G1YoungGenCollectionCount":
          $jvm_g1_young_generation_collection_count = $value;
          break;
        case "G1YoungGenCollectionTime":
          $jvm_g1_young_generation_collection_time = $value;
          break;
        case "CMSCollectionCount":
          $jvm_cms_collection_count = $value;
          break;
        case "CMSCollectionTime":
          $jvm_cms_collection_time = $value;
          break;
        case "ParNewCollectionCount":
          $jvm_par_new_collection_count = $value;
          break;
        case "ParNewCollectionTime":
          $jvm_par_new_collection_time = $value;
          break;
        case "CopyCollectionCount":
          $jvm_copy_collection_count = $value;
          break;
        case "CopyCollectionTime":
          $jvm_copy_collection_time = $value;
          break;
        case "PSMarkSweepCollectionCount":
          $jvm_ps_mark_sweep_collection_count = $value;
          break;
        case "PSMarkSweepCollectionTime":
          $jvm_ps_mark_sweep_collection_time = $value;
          break;
        case "PSScavengeCollectionCount":
          $jvm_ps_scavenge_collection_count = $value;
          break;
        case "PSScavengeCollectionTime":
          $jvm_ps_scavenge_collection_time = $value;
          break;
      }
    }

    rrdtool_create($device, $rrd_filename, " \
      DS:UpTime:GAUGE:600:0:125000000000 \
      DS:HeapMemoryMaxUsage:GAUGE:600:0:125000000000 \
      DS:HeapMemoryUsed:GAUGE:600:0:125000000000 \
      DS:NonHeapMemoryMax:GAUGE:600:0:125000000000 \
      DS:NonHeapMemoryUsed:GAUGE:600:0:125000000000 \
      DS:EdenSpaceMax:GAUGE:600:0:125000000000 \
      DS:EdenSpaceUsed:GAUGE:600:0:125000000000 \
      DS:PermGenMax:GAUGE:600:0:125000000000 \
      DS:PermGenUsed:GAUGE:600:0:125000000000 \
      DS:OldGenMax:GAUGE:600:0:125000000000 \
      DS:OldGenUsed:GAUGE:600:0:125000000000 \
      DS:DaemonThreads:GAUGE:600:0:125000000000 \
      DS:TotalThreads:GAUGE:600:0:125000000000 \
      DS:LoadedClassCount:GAUGE:600:0:125000000000 \
      DS:UnloadedClassCount:GAUGE:600:0:125000000000 \
      DS:G1OldGenCount:GAUGE:600:0:125000000000 \
      DS:G1OldGenTime:GAUGE:600:0:125000000000 \
      DS:G1YoungGenCount:GAUGE:600:0:125000000000 \
      DS:G1YoungGenTime:GAUGE:600:0:125000000000 \
      DS:CMSCount:GAUGE:600:0:125000000000 \
      DS:CMSTime:GAUGE:600:0:125000000000 \
      DS:ParNewCount:GAUGE:600:0:125000000000 \
      DS:ParNewTime:GAUGE:600:0:125000000000 \
      DS:CopyCount:GAUGE:600:0:125000000000 \
      DS:CopyTime:GAUGE:600:0:125000000000 \
      DS:PSMarkSweepCount:GAUGE:600:0:125000000000 \
      DS:PSMarkSweepTime:GAUGE:600:0:125000000000 \
      DS:PSScavengeCount:GAUGE:600:0:125000000000 \
      DS:PSScavengeTime:GAUGE:600:0:125000000000");

    rrdtool_update($device, $rrd_filename, "N:$jvm_uptime:$jvm_heap_memory_max_usage:$jvm_heap_memory_used:$jvm_non_heap_memory_max:$jvm_non_heap_memory_used:$jvm_eden_space_max:$jvm_eden_space_used:$jvm_perm_gen_max:$jvm_perm_gen_used:$jvm_old_gen_max:$jvm_old_gen_used:$jvm_daemon_threads:$jvm_total_threads:$jvm_loaded_class_count:$jvm_unloaded_class_count:$jvm_g1_old_generation_collection_count:$jvm_g1_old_generation_collection_time:$jvm_g1_young_generation_collection_count:$jvm_g1_young_generation_collection_time:$jvm_cms_collection_count:$jvm_cms_collection_time:$jvm_par_new_collection_count:$jvm_par_new_collection_time:$jvm_copy_collection_count:$jvm_copy_collection_time:$jvm_ps_mark_sweep_collection_count:$jvm_ps_mark_sweep_collection_time:$jvm_ps_scavenge_collection_count:$jvm_ps_scavenge_collection_time");

    unset($jvm_uptime);
    unset($jvm_heap_memory_max_usage,$jvm_heap_memory_used,$jvm_non_heap_memory_max,$jvm_non_heap_memory_used);
    unset($jvm_eden_space_max,$jvm_eden_space_used,$jvm_perm_gen_max,$jvm_perm_gen_used,$jvm_old_gen_max,$jvm_old_gen_used);
    unset($jvm_daemon_threads,$jvm_total_threads);
    unset($jvmoverjmx,$rrd_filename,$jmxdata);
    unset($jvm_loaded_class_count,$jvm_unloaded_class_count);
    unset($jvm_g1_old_generation_collection_count,$jvm_g1_old_generation_collection_time,$jvm_g1_young_generation_collection_count,$jvm_g1_young_generation_collection_time);
    unset($jvm_cms_collection_count,$jvm_cms_collection_time);
    unset($jvm_par_new_collection_count,$jvm_par_new_collection_time);
    unset($jvm_copy_collection_count,$jvm_copy_collection_time);
    unset($$jvm_ps_mark_sweep_collection_count,$jvm_ps_mark_sweep_collection_time);
    unset($jvm_ps_scavenge_collection_count,$jvm_ps_scavenge_collection_time);
  }
}

// EOF
