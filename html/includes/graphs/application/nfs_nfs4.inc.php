<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-nfs-".$app['app_id'].".rrd");

$array = array(
      "null",      "read",      "write",   "commit",      "open",        "open_conf",
      "open_noat", "open_dgrd", "close",   "setattr",     "fsinfo",      "renew",
      "setclntid", "confirm",   "lock",
      "lockt",     "locku",     "access",  "getattr",     "lookup",      "lookup_root",
      "remove",    "rename",    "link",    "symlink",     "create",      "pathconf",
      "statfs",    "readlink",  "readdir", "server_caps", "delegreturn", "getacl",
      "setacl",    "fs_locations",
      "rel_lkowner", "secinfo",
      /* nfsv4.1 client ops */
      "exchange_id",
      "create_ses",
      "destroy_ses",
      "sequence",
      "get_lease_t",
      "reclaim_comp",
      "layoutget",
      "getdevinfo",
      "layoutcommit",
      "layoutreturn",
      "secinfo_noname",
      "test_stateid",
      "free_stateid",
      "getdevlist",
      "bind_contoses",
      "dstr_clientid"
);

$i = 0;

if (is_file($rrd_filename))
{
  foreach ($array as $name)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $name;
    $rrd_list[$i]['ds'] = 'proc4'.$name;
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 0;
$unit_text = "Rows";

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
