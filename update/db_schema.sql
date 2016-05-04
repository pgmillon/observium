-- Generation Time: Feb 08, 2015 at 06:08 PM
-- Server version: 5.5.41-0ubuntu0.12.04.1-log

--
-- Initial observium DB schema for clean install (version 188)
--
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `observium`
--
CREATE DATABASE IF NOT EXISTS `observium` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `observium`;

-- --------------------------------------------------------

--
-- Table structure for table `accesspoints`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `accesspoints` (
  `accesspoint_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `radio_number` tinyint(4) DEFAULT NULL,
  `type` varchar(16) NOT NULL,
  `mac_addr` varchar(24) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Access Points';

-- --------------------------------------------------------

--
-- Table structure for table `accesspoints-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `accesspoints-state` (
  `accesspoint_id` int(11) NOT NULL,
  `channel` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `txpow` tinyint(4) NOT NULL DEFAULT '0',
  `radioutil` tinyint(4) NOT NULL DEFAULT '0',
  `numasoclients` smallint(6) NOT NULL DEFAULT '0',
  `nummonclients` smallint(6) NOT NULL DEFAULT '0',
  `numactbssid` tinyint(4) NOT NULL DEFAULT '0',
  `nummonbssid` tinyint(4) NOT NULL DEFAULT '0',
  `interference` tinyint(3) unsigned NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Access Points';

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `alerts` (
  `id` int(11) NOT NULL,
  `importance` int(11) NOT NULL DEFAULT '0',
  `device_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `time_logged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alerted` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `alert_assoc`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `alert_assoc` (
  `alert_assoc_id` int(11) NOT NULL,
  `alert_test_id` int(11) NOT NULL,
  `entity_type` varchar(64) CHARACTER SET utf8 NOT NULL,
  `device_attribs` text COLLATE utf8_unicode_ci,
  `entity_attribs` text CHARACTER SET utf8,
  `enable` tinyint(1) NOT NULL DEFAULT '1',
  `alerter` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `severity` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_contacts`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `alert_contacts` (
  `contact_id` int(11) NOT NULL,
  `contact_descr` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `contact_method` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `contact_endpoint` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `contact_disabled` tinyint(1) NOT NULL DEFAULT '0',
  `contact_disabled_until` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_contacts_assoc`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `alert_contacts_assoc` (
  `aca_id` int(11) NOT NULL,
  `alert_checker_id` int(11) NOT NULL,
  `alert_contact_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_log`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `alert_log` (
  `event_id` int(11) NOT NULL,
  `alert_test_id` int(11) DEFAULT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `timestamp` datetime DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci,
  `entity_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entity_id` int(11) NOT NULL,
  `log_type` enum('ALERT_NOTIFY','FAIL','FAIL_DELAYED','FAIL_SUPPRESSED','OK','RECOVER_NOTIFY','RECOVER_SUPPRESSED') COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_table`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `alert_table` (
  `alert_table_id` int(11) NOT NULL,
  `alert_test_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `entity_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `entity_id` int(11) NOT NULL,
  `alert_assocs` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `delay` int(11) NOT NULL,
  `ignore_until` datetime DEFAULT NULL,
  `ignore_until_ok` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_table-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `alert_table-state` (
  `alert_table_id` int(11) NOT NULL,
  `last_checked` int(11) NOT NULL,
  `last_changed` int(11) NOT NULL,
  `last_recovered` int(11) NOT NULL,
  `last_failed` int(11) NOT NULL,
  `has_alerted` tinyint(1) NOT NULL,
  `last_message` varchar(128) NOT NULL,
  `alert_status` tinyint(4) NOT NULL DEFAULT '-1',
  `last_alerted` int(11) NOT NULL,
  `state` varchar(512) NOT NULL,
  `count` int(11) NOT NULL,
  `state_entry` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `alert_tests`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `alert_tests` (
  `alert_test_id` int(11) NOT NULL,
  `entity_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `alert_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `alert_message` text COLLATE utf8_unicode_ci NOT NULL,
  `conditions` text CHARACTER SET utf8 NOT NULL,
  `and` tinyint(1) NOT NULL DEFAULT '1',
  `severity` enum('crit','err') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'crit',
  `delay` int(11) DEFAULT '0',
  `alerter` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enable` tinyint(1) NOT NULL DEFAULT '1',
  `show_frontpage` int(1) NOT NULL DEFAULT '1',
  `suppress_recovery` tinyint(1) DEFAULT '0',
  `ignore_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `applications` (
  `app_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `app_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `app_instance` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_state` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UNKNOWN',
  `app_status` varchar(8) CHARACTER SET utf8 NOT NULL,
  `app_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `applications-state` (
  `application_id` int(11) NOT NULL,
  `app_last_polled` int(11) NOT NULL,
  `app_status` tinyint(1) NOT NULL,
  `app_state` varchar(1024) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `authlog`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `authlog` (
  `id` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` text CHARACTER SET utf8 NOT NULL,
  `address` text CHARACTER SET utf8 NOT NULL,
  `result` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bgpPeers`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `bgpPeers` (
  `bgpPeer_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `astext` varchar(64) CHARACTER SET utf8 NOT NULL,
  `reverse_dns` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bgpPeerIdentifier` varchar(39) CHARACTER SET utf8 NOT NULL,
  `bgpPeerRemoteAs` int(11) unsigned NOT NULL,
  `bgpPeerState` text CHARACTER SET utf8 NOT NULL,
  `bgpPeerAdminStatus` text CHARACTER SET utf8 NOT NULL,
  `bgpPeerLocalAddr` varchar(39) CHARACTER SET utf8 NOT NULL,
  `bgpPeerRemoteAddr` varchar(39) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bgpPeers-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `bgpPeers-state` (
  `bgpPeer_id` int(11) NOT NULL,
  `bgpPeerInUpdates` int(11) DEFAULT NULL,
  `bgpPeerOutUpdates` int(11) DEFAULT NULL,
  `bgpPeerInTotalMessages` int(11) DEFAULT NULL,
  `bgpPeerOutTotalMessages` int(11) DEFAULT NULL,
  `bgpPeerFsmEstablishedTime` int(11) DEFAULT NULL,
  `bgpPeerInUpdateElapsedTime` int(11) DEFAULT NULL,
  `bgpPeerInUpdates_delta` int(11) DEFAULT NULL,
  `bgpPeerInUpdates_rate` int(11) DEFAULT NULL,
  `bgpPeerOutUpdates_delta` int(11) DEFAULT NULL,
  `bgpPeerOutUpdates_rate` int(11) DEFAULT NULL,
  `bgpPeerInTotalMessages_delta` int(11) DEFAULT NULL,
  `bgpPeerInTotalMessages_rate` int(11) DEFAULT NULL,
  `bgpPeerOutTotalMessages_delta` int(11) DEFAULT NULL,
  `bgpPeerOutTotalMessages_rate` int(11) DEFAULT NULL,
  `bgpPeer_polled` int(11) DEFAULT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bgpPeers_cbgp`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `bgpPeers_cbgp` (
  `cbgp_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `bgpPeerRemoteAddr` varchar(39) CHARACTER SET utf8 NOT NULL,
  `bgpPeerIndex` smallint(5) unsigned DEFAULT NULL,
  `afi` varchar(16) CHARACTER SET utf8 NOT NULL,
  `safi` varchar(16) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bgpPeers_cbgp-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `bgpPeers_cbgp-state` (
  `cbgp_id` int(11) NOT NULL,
  `AcceptedPrefixes` int(11) DEFAULT NULL,
  `DeniedPrefixes` int(11) DEFAULT NULL,
  `PrefixAdminLimit` int(11) DEFAULT NULL,
  `PrefixThreshold` int(11) DEFAULT NULL,
  `PrefixClearThreshold` int(11) DEFAULT NULL,
  `AdvertisedPrefixes` int(11) DEFAULT NULL,
  `SuppressedPrefixes` int(11) DEFAULT NULL,
  `WithdrawnPrefixes` int(11) DEFAULT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `bills` (
  `bill_id` int(11) NOT NULL,
  `bill_name` text CHARACTER SET utf8 NOT NULL,
  `bill_type` text CHARACTER SET utf8 NOT NULL,
  `bill_cdr` bigint(20) DEFAULT NULL,
  `bill_day` int(11) NOT NULL DEFAULT '1',
  `bill_quota` bigint(20) DEFAULT NULL,
  `bill_polled` int(11) NOT NULL,
  `rate_95th_in` bigint(20) NOT NULL,
  `rate_95th_out` bigint(20) NOT NULL,
  `rate_95th` bigint(20) NOT NULL,
  `dir_95th` varchar(3) CHARACTER SET utf8 NOT NULL,
  `total_data` bigint(20) NOT NULL,
  `total_data_in` bigint(20) NOT NULL,
  `total_data_out` bigint(20) NOT NULL,
  `rate_average_in` bigint(20) NOT NULL,
  `rate_average_out` bigint(20) NOT NULL,
  `rate_average` bigint(20) NOT NULL,
  `bill_last_calc` datetime NOT NULL,
  `bill_custid` varchar(64) CHARACTER SET utf8 NOT NULL,
  `bill_contact` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bill_threshold` int(11) DEFAULT NULL,
  `bill_notify` tinyint(1) NOT NULL DEFAULT '0',
  `bill_ref` varchar(64) CHARACTER SET utf8 NOT NULL,
  `bill_notes` varchar(256) CHARACTER SET utf8 NOT NULL,
  `bill_autoadded` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bill_data`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `bill_data` (
  `bill_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `period` int(11) NOT NULL,
  `delta` bigint(11) NOT NULL,
  `in_delta` bigint(11) NOT NULL,
  `out_delta` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bill_history`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `bill_history` (
  `bill_hist_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bill_datefrom` datetime NOT NULL,
  `bill_dateto` datetime NOT NULL,
  `bill_type` text NOT NULL,
  `bill_allowed` bigint(20) NOT NULL,
  `bill_used` bigint(20) NOT NULL,
  `bill_overuse` bigint(20) NOT NULL,
  `bill_percent` decimal(10,2) NOT NULL,
  `rate_95th_in` bigint(20) NOT NULL,
  `rate_95th_out` bigint(20) NOT NULL,
  `rate_95th` bigint(20) NOT NULL,
  `dir_95th` varchar(3) NOT NULL,
  `rate_average` bigint(20) NOT NULL,
  `rate_average_in` bigint(20) NOT NULL,
  `rate_average_out` bigint(20) NOT NULL,
  `traf_in` bigint(20) NOT NULL,
  `traf_out` bigint(20) NOT NULL,
  `traf_total` bigint(20) NOT NULL,
  `pdf` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bill_ports`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `bill_ports` (
  `bill_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `bill_port_autoadded` tinyint(1) NOT NULL DEFAULT '0',
  `bill_port_polled` int(11) NOT NULL,
  `bill_port_period` int(11) NOT NULL,
  `bill_port_counter_in` bigint(20) DEFAULT NULL,
  `bill_port_delta_in` bigint(20) DEFAULT NULL,
  `bill_port_counter_out` bigint(20) DEFAULT NULL,
  `bill_port_delta_out` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cef_prefix`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `cef_prefix` (
  `cef_pfx_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `afi` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `cef_pfx` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cef_switching`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `cef_switching` (
  `cef_switching_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `afi` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `cef_index` int(11) NOT NULL,
  `cef_path` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `drop` int(11) NOT NULL,
  `punt` int(11) NOT NULL,
  `punt2host` int(11) NOT NULL,
  `drop_prev` int(11) NOT NULL,
  `punt_prev` int(11) NOT NULL,
  `punt2host_prev` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `updated_prev` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `config` (
  `config_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `config_value` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int(11) NOT NULL,
  `username` char(64) CHARACTER SET utf8 NOT NULL,
  `password` char(32) CHARACTER SET utf8 NOT NULL,
  `string` char(64) CHARACTER SET utf8 NOT NULL,
  `level` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbSchema`
--
-- Creation: Feb 08, 2015 at 02:29 PM
--

CREATE TABLE IF NOT EXISTS `dbSchema` (
  `version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dbSchema`
--

INSERT INTO `dbSchema` (`version`) VALUES
(188);

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--
-- Creation: Feb 08, 2015 at 02:29 PM
--

CREATE TABLE IF NOT EXISTS `devices` (
  `device_id` int(11) NOT NULL,
  `hostname` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `sysName` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `community` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authlevel` enum('noAuthNoPriv','authNoPriv','authPriv') COLLATE utf8_unicode_ci DEFAULT NULL,
  `authname` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authpass` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `authalgo` enum('MD5','SHA') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cryptopass` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cryptoalgo` enum('AES','DES') COLLATE utf8_unicode_ci DEFAULT NULL,
  `snmpver` enum('v1','v2c','v3') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'v2c',
  `port` smallint(5) unsigned NOT NULL DEFAULT '161',
  `timeout` int(11) DEFAULT NULL,
  `retries` int(11) DEFAULT NULL,
  `ssh_port` int(11) NOT NULL DEFAULT '22',
  `agent_version` int(11) DEFAULT NULL,
  `transport` enum('udp','tcp','udp6','tcp6') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'udp',
  `bgpLocalAs` int(11) unsigned DEFAULT NULL,
  `snmpEngineID` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sysObjectID` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sysDescr` text COLLATE utf8_unicode_ci,
  `sysContact` text COLLATE utf8_unicode_ci,
  `version` text COLLATE utf8_unicode_ci,
  `hardware` text COLLATE utf8_unicode_ci,
  `features` text COLLATE utf8_unicode_ci,
  `location` text COLLATE utf8_unicode_ci NOT NULL,
  `os` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `ignore` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_until` datetime DEFAULT NULL,
  `asset_tag` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `uptime` bigint(20) DEFAULT NULL,
  `last_polled` timestamp NULL DEFAULT NULL,
  `last_discovered` timestamp NULL DEFAULT NULL,
  `is_polling` tinyint(1) NOT NULL DEFAULT '0',
  `is_discovering` tinyint(1) NOT NULL DEFAULT '0',
  `last_polled_timetaken` double(5,2) DEFAULT NULL,
  `last_discovered_timetaken` double(5,2) DEFAULT NULL,
  `purpose` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serial` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_state` text COLLATE utf8_unicode_ci,
  `distro` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `distro_ver` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kernel` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `arch` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices_attribs`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `devices_attribs` (
  `attrib_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `attrib_type` varchar(128) CHARACTER SET utf8 NOT NULL,
  `attrib_value` text CHARACTER SET utf8 NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices_locations`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `devices_locations` (
  `location_id` int(11) unsigned NOT NULL,
  `device_id` int(11) unsigned NOT NULL,
  `location` text COLLATE utf8_unicode_ci NOT NULL,
  `location_lat` decimal(10,7) DEFAULT NULL,
  `location_lon` decimal(10,7) DEFAULT NULL,
  `location_country` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `location_state` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `location_county` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `location_city` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `location_geoapi` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `location_status` text COLLATE utf8_unicode_ci,
  `location_manual` tinyint(1) NOT NULL DEFAULT '0',
  `location_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores geo location information';

-- --------------------------------------------------------

--
-- Table structure for table `devices_mibs`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `devices_mibs` (
  `mib_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `mib` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `table_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `oid` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores disabled MIBs or combination MIB with tables/oids';

-- --------------------------------------------------------

--
-- Table structure for table `devices_perftimes`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `devices_perftimes` (
  `device_id` int(11) NOT NULL,
  `operation` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `start` int(11) NOT NULL,
  `duration` double(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_graphs`
--
-- Creation: Feb 08, 2015 at 12:45 PM
--

CREATE TABLE IF NOT EXISTS `device_graphs` (
  `device_graph_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `graph` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eigrp_ports`
--
-- Creation: Feb 08, 2015 at 02:29 PM
--

CREATE TABLE IF NOT EXISTS `eigrp_ports` (
  `eigrp_port_id` int(11) NOT NULL,
  `eigrp_vpn` int(11) NOT NULL,
  `eigrp_as` int(11) NOT NULL,
  `eigrp_ifIndex` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `eigrp_peer_count` int(11) NOT NULL,
  `eigrp_MeanSrtt` int(11) NOT NULL,
  `eigrp_authmode` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_permissions`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `entity_permissions` (
  `user_id` int(11) NOT NULL,
  `entity_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `entity_id` int(11) NOT NULL,
  `access_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entPhysical`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `entPhysical` (
  `entPhysical_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `entPhysicalIndex` int(11) NOT NULL,
  `entPhysicalDescr` text CHARACTER SET utf8 NOT NULL,
  `entPhysicalClass` text CHARACTER SET utf8 NOT NULL,
  `entPhysicalName` text CHARACTER SET utf8 NOT NULL,
  `entPhysicalHardwareRev` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalFirmwareRev` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalSoftwareRev` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalAlias` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `entPhysicalAssetID` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `entPhysicalIsFRU` varchar(8) CHARACTER SET utf8 DEFAULT NULL,
  `entPhysicalModelName` text CHARACTER SET utf8 NOT NULL,
  `entPhysicalVendorType` text CHARACTER SET utf8,
  `entPhysicalSerialNum` text CHARACTER SET utf8 NOT NULL,
  `entPhysicalContainedIn` int(11) NOT NULL,
  `entPhysicalParentRelPos` int(11) NOT NULL,
  `entPhysicalMfgName` text CHARACTER SET utf8 NOT NULL,
  `ifIndex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entPhysical-state`
--
-- Creation: Jan 26, 2015 at 06:12 AM
--

CREATE TABLE IF NOT EXISTS `entPhysical-state` (
  `entPhysical_state_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `entPhysicalIndex` varchar(64) NOT NULL,
  `subindex` varchar(64) DEFAULT NULL,
  `group` varchar(64) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eventlog`
--
-- Creation: Feb 01, 2015 at 12:51 PM
--

CREATE TABLE IF NOT EXISTS `eventlog` (
  `event_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `timestamp` datetime DEFAULT NULL,
  `message` text CHARACTER SET latin1,
  `entity_type` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `entity_id` int(64) DEFAULT NULL,
  `severity` tinyint(4) NOT NULL DEFAULT '6'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `graph_types`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `graph_types` (
  `graph_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_subtype` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_section` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_descr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `graph_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `graph_types_dead`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `graph_types_dead` (
  `graph_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_subtype` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_section` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `graph_descr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `graph_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` int(11) NOT NULL,
  `entity_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `group_name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `group_descr` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `group_menu` tinyint(1) NOT NULL DEFAULT '0',
  `group_ignore` tinyint(4) NOT NULL,
  `group_ignore_until` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups_assoc`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `groups_assoc` (
  `group_assoc_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `entity_type` varchar(64) CHARACTER SET utf8 NOT NULL,
  `device_attribs` text COLLATE utf8_unicode_ci,
  `entity_attribs` text CHARACTER SET utf8
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_table`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `group_table` (
  `group_table_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `entity_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `entity_id` int(11) NOT NULL,
  `group_assocs` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrDevice`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `hrDevice` (
  `hrDevice_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `hrDeviceIndex` int(11) NOT NULL,
  `hrDeviceDescr` text CHARACTER SET latin1 NOT NULL,
  `hrDeviceType` text CHARACTER SET latin1 NOT NULL,
  `hrDeviceErrors` int(11) NOT NULL,
  `hrDeviceStatus` text CHARACTER SET latin1 NOT NULL,
  `hrProcessorLoad` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipsec_tunnels`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ipsec_tunnels` (
  `tunnel_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `peer_port` int(11) NOT NULL,
  `peer_addr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `local_addr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `local_port` int(11) NOT NULL,
  `tunnel_name` varchar(96) COLLATE utf8_unicode_ci NOT NULL,
  `tunnel_status` varchar(11) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipv4_addresses`
--
-- Creation: Feb 08, 2015 at 02:29 PM
--

CREATE TABLE IF NOT EXISTS `ipv4_addresses` (
  `ipv4_address_id` int(11) NOT NULL,
  `ipv4_address` varchar(32) CHARACTER SET latin1 NOT NULL,
  `ipv4_prefixlen` int(11) NOT NULL,
  `ipv4_network_id` varchar(32) CHARACTER SET latin1 NOT NULL,
  `port_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipv4_networks`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ipv4_networks` (
  `ipv4_network_id` int(11) NOT NULL,
  `ipv4_network` varchar(64) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipv6_addresses`
--
-- Creation: Feb 08, 2015 at 02:29 PM
--

CREATE TABLE IF NOT EXISTS `ipv6_addresses` (
  `ipv6_address_id` int(11) NOT NULL,
  `ipv6_address` varchar(128) CHARACTER SET latin1 NOT NULL,
  `ipv6_compressed` varchar(128) CHARACTER SET latin1 NOT NULL,
  `ipv6_prefixlen` int(11) NOT NULL,
  `ipv6_origin` varchar(16) CHARACTER SET latin1 NOT NULL,
  `ipv6_network_id` varchar(128) CHARACTER SET latin1 NOT NULL,
  `port_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipv6_networks`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ipv6_networks` (
  `ipv6_network_id` int(11) NOT NULL,
  `ipv6_network` varchar(64) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ip_mac`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ip_mac` (
  `mac_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `mac_address` char(12) NOT NULL,
  `ip_address` varchar(39) NOT NULL,
  `ip_version` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `juniAtmVp`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `juniAtmVp` (
  `juniAtmVp_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `vp_id` int(11) NOT NULL,
  `vp_descr` varchar(32) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `links`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL,
  `local_port_id` int(11) DEFAULT NULL,
  `remote_port_id` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `protocol` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `remote_hostname` varchar(128) CHARACTER SET utf8 NOT NULL,
  `remote_port` varchar(128) CHARACTER SET utf8 NOT NULL,
  `remote_platform` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remote_version` varchar(256) CHARACTER SET utf8 NOT NULL,
  `remote_address` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loadbalancer_rservers`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `loadbalancer_rservers` (
  `rserver_id` int(11) NOT NULL,
  `farm_id` varchar(128) CHARACTER SET utf8 NOT NULL,
  `device_id` int(11) NOT NULL,
  `StateDescr` varchar(64) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loadbalancer_vservers`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `loadbalancer_vservers` (
  `classmap_id` int(11) NOT NULL,
  `classmap` varchar(128) CHARACTER SET utf8 NOT NULL,
  `serverstate` varchar(64) CHARACTER SET utf8 NOT NULL,
  `device_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mac_accounting`
--
-- Creation: Feb 08, 2015 at 02:29 PM
--

CREATE TABLE IF NOT EXISTS `mac_accounting` (
  `ma_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `vlan_id` int(11) NOT NULL DEFAULT '0',
  `device_id` int(11) NOT NULL,
  `mac` varchar(32) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mac_accounting-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `mac_accounting-state` (
  `ma_id` int(11) NOT NULL,
  `bytes_input` bigint(20) DEFAULT NULL,
  `bytes_input_delta` bigint(20) DEFAULT NULL,
  `bytes_input_rate` int(11) DEFAULT NULL,
  `bytes_output` bigint(20) DEFAULT NULL,
  `bytes_output_delta` bigint(20) DEFAULT NULL,
  `bytes_output_rate` int(11) DEFAULT NULL,
  `pkts_input` bigint(20) DEFAULT NULL,
  `pkts_input_delta` bigint(20) DEFAULT NULL,
  `pkts_input_rate` int(11) DEFAULT NULL,
  `pkts_output` bigint(20) DEFAULT NULL,
  `pkts_output_delta` bigint(20) DEFAULT NULL,
  `pkts_output_rate` int(11) DEFAULT NULL,
  `poll_time` int(11) DEFAULT NULL,
  `poll_period` int(11) DEFAULT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mempools`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `mempools` (
  `mempool_id` int(11) NOT NULL,
  `mempool_index` varchar(16) CHARACTER SET latin1 NOT NULL,
  `entPhysicalIndex` int(11) DEFAULT NULL,
  `hrDeviceIndex` int(11) DEFAULT NULL,
  `mempool_mib` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mempool_precision` int(11) NOT NULL DEFAULT '1',
  `mempool_hc` tinyint(1) NOT NULL DEFAULT '0',
  `mempool_descr` varchar(64) CHARACTER SET latin1 NOT NULL,
  `device_id` int(11) NOT NULL,
  `mempool_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mempools-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `mempools-state` (
  `mempool_id` int(11) NOT NULL,
  `mempool_polled` int(11) NOT NULL,
  `mempool_perc` int(11) NOT NULL,
  `mempool_used` bigint(16) NOT NULL,
  `mempool_free` bigint(16) NOT NULL,
  `mempool_total` bigint(16) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `munin_plugins`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `munin_plugins` (
  `mplug_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `mplug_type` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `mplug_instance` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_category` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_info` text COLLATE utf8_unicode_ci,
  `mplug_vlabel` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_args` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mplug_total` binary(1) NOT NULL DEFAULT '0',
  `mplug_graph` binary(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `munin_plugins_ds`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `munin_plugins_ds` (
  `mplug_ds_id` int(11) NOT NULL,
  `mplug_id` int(11) NOT NULL,
  `ds_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_type` enum('COUNTER','ABSOLUTE','DERIVE','GAUGE') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'GAUGE',
  `ds_label` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_cdef` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_draw` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_graph` enum('no','yes') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `ds_info` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_extinfo` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_max` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_min` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_negative` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_warning` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_critical` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_colour` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_sum` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_stack` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ds_line` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `netscaler_services`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `netscaler_services` (
  `svc_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `svc_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `svc_fullname` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `svc_label` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `svc_ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `svc_port` int(8) NOT NULL,
  `svc_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `svc_state` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `svc_clients` int(11) NOT NULL,
  `svc_server` int(11) NOT NULL,
  `svc_req_rate` int(11) NOT NULL,
  `svc_bps_in` int(11) NOT NULL,
  `svc_bps_out` int(11) NOT NULL,
  `svc_ignore` tinyint(1) NOT NULL DEFAULT '0',
  `svc_ignore_until` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `netscaler_services_vservers`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `netscaler_services_vservers` (
  `sv_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `vsvr_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `svc_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `service_weight` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `netscaler_vservers`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `netscaler_vservers` (
  `vsvr_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `vsvr_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_fullname` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsvr_label` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsvr_ip` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_ipv6` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsvr_port` int(8) NOT NULL,
  `vsvr_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_entitytype` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vsvr_state` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `vsvr_clients` int(11) NOT NULL,
  `vsvr_server` int(11) NOT NULL,
  `vsvr_req_rate` int(11) NOT NULL,
  `vsvr_bps_in` int(11) NOT NULL,
  `vsvr_bps_out` int(11) NOT NULL,
  `vsvr_ignore` tinyint(1) NOT NULL DEFAULT '0',
  `vsvr_ignore_until` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ospf_areas`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ospf_areas` (
  `device_id` int(11) NOT NULL,
  `ospfAreaId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAuthType` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ospfImportAsExtern` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ospfSpfRuns` int(11) NOT NULL,
  `ospfAreaBdrRtrCount` int(11) NOT NULL,
  `ospfAsBdrRtrCount` int(11) NOT NULL,
  `ospfAreaLsaCount` int(11) NOT NULL,
  `ospfAreaLsaCksumSum` int(11) NOT NULL,
  `ospfAreaSummary` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAreaStatus` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ospf_instances`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ospf_instances` (
  `device_id` int(11) NOT NULL,
  `ospf_instance_id` int(11) NOT NULL,
  `ospfRouterId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAdminStat` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfVersionNumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAreaBdrRtrStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfASBdrRtrStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfExternLsaCount` int(11) NOT NULL,
  `ospfExternLsaCksumSum` int(11) NOT NULL,
  `ospfTOSSupport` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfOriginateNewLsas` int(11) NOT NULL,
  `ospfRxNewLsas` int(11) NOT NULL,
  `ospfExtLsdbLimit` int(11) DEFAULT NULL,
  `ospfMulticastExtensions` int(11) DEFAULT NULL,
  `ospfExitOverflowInterval` int(11) DEFAULT NULL,
  `ospfDemandExtensions` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ospf_nbrs`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ospf_nbrs` (
  `device_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `ospf_nbr_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrIpAddr` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrAddressLessIndex` int(11) NOT NULL,
  `ospfNbrRtrId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrOptions` int(11) NOT NULL,
  `ospfNbrPriority` int(11) NOT NULL,
  `ospfNbrState` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrEvents` int(11) NOT NULL,
  `ospfNbrLsRetransQLen` int(11) NOT NULL,
  `ospfNbmaNbrStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbmaNbrPermanence` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfNbrHelloSuppressed` varchar(32) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ospf_ports`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ospf_ports` (
  `device_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `ospf_port_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfIfIpAddress` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfAddressLessIf` int(11) NOT NULL,
  `ospfIfAreaId` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `ospfIfType` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfAdminStat` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfRtrPriority` int(11) DEFAULT NULL,
  `ospfIfTransitDelay` int(11) DEFAULT NULL,
  `ospfIfRetransInterval` int(11) DEFAULT NULL,
  `ospfIfHelloInterval` int(11) DEFAULT NULL,
  `ospfIfRtrDeadInterval` int(11) DEFAULT NULL,
  `ospfIfPollInterval` int(11) DEFAULT NULL,
  `ospfIfState` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfDesignatedRouter` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfBackupDesignatedRouter` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfEvents` int(11) DEFAULT NULL,
  `ospfIfAuthKey` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfStatus` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfMulticastForwarding` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfDemand` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ospfIfAuthType` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `packages` (
  `pkg_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `manager` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL,
  `version` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `build` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `arch` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `size` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perf_times`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `perf_times` (
  `type` varchar(8) CHARACTER SET latin1 NOT NULL,
  `doing` varchar(64) CHARACTER SET latin1 NOT NULL,
  `start` int(11) NOT NULL,
  `duration` double(8,2) NOT NULL,
  `devices` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ports`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ports` (
  `port_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `port_64bit` tinyint(1) DEFAULT NULL,
  `port_label` varchar(255) NOT NULL DEFAULT '',
  `port_descr_type` varchar(255) DEFAULT NULL,
  `port_descr_descr` varchar(255) DEFAULT NULL,
  `port_descr_circuit` varchar(255) DEFAULT NULL,
  `port_descr_speed` varchar(32) DEFAULT NULL,
  `port_descr_notes` varchar(255) DEFAULT NULL,
  `ifDescr` varchar(255) DEFAULT NULL,
  `ifName` varchar(64) DEFAULT NULL,
  `portName` varchar(128) DEFAULT NULL,
  `ifIndex` int(11) NOT NULL,
  `ifSpeed` bigint(20) DEFAULT NULL,
  `ifConnectorPresent` varchar(12) DEFAULT NULL,
  `ifPromiscuousMode` varchar(12) DEFAULT NULL,
  `ifHighSpeed` int(11) DEFAULT NULL,
  `ifOperStatus` varchar(16) DEFAULT NULL,
  `ifAdminStatus` varchar(16) DEFAULT NULL,
  `ifDuplex` varchar(12) DEFAULT NULL,
  `ifMtu` int(11) DEFAULT NULL,
  `ifType` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifAlias` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifPhysAddress` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifHardType` varchar(64) DEFAULT NULL,
  `ifLastChange` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ifVlan` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifTrunk` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `ifVrf` int(16) DEFAULT NULL,
  `ignore` tinyint(1) NOT NULL DEFAULT '0',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `detailed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `pagpOperationMode` varchar(32) DEFAULT NULL,
  `pagpPortState` varchar(16) DEFAULT NULL,
  `pagpPartnerDeviceId` varchar(48) DEFAULT NULL,
  `pagpPartnerLearnMethod` varchar(16) DEFAULT NULL,
  `pagpPartnerIfIndex` int(11) DEFAULT NULL,
  `pagpPartnerGroupIfIndex` int(11) DEFAULT NULL,
  `pagpPartnerDeviceName` varchar(128) DEFAULT NULL,
  `pagpEthcOperationMode` varchar(16) DEFAULT NULL,
  `pagpDeviceId` varchar(48) DEFAULT NULL,
  `pagpGroupIfIndex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ports-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `ports-state` (
  `port_id` int(11) unsigned NOT NULL,
  `ifInUcastPkts` bigint(20) unsigned NOT NULL,
  `ifInUcastPkts_delta` int(20) unsigned NOT NULL,
  `ifInUcastPkts_rate` int(11) unsigned NOT NULL,
  `ifOutUcastPkts` bigint(20) unsigned NOT NULL,
  `ifOutUcastPkts_delta` int(20) unsigned NOT NULL,
  `ifOutUcastPkts_rate` int(11) unsigned NOT NULL,
  `ifInErrors` bigint(20) unsigned NOT NULL,
  `ifInErrors_delta` int(10) unsigned NOT NULL,
  `ifInErrors_rate` smallint(5) unsigned NOT NULL,
  `ifOutErrors` bigint(20) unsigned NOT NULL,
  `ifOutErrors_delta` int(10) unsigned NOT NULL,
  `ifOutErrors_rate` smallint(5) unsigned NOT NULL,
  `ifOctets_rate` bigint(20) unsigned NOT NULL,
  `ifUcastPkts_rate` int(11) unsigned NOT NULL,
  `ifErrors_rate` smallint(5) unsigned NOT NULL,
  `ifInOctets` bigint(20) unsigned NOT NULL,
  `ifInOctets_delta` bigint(20) unsigned NOT NULL,
  `ifInOctets_rate` bigint(20) unsigned NOT NULL,
  `ifOutOctets` bigint(20) unsigned NOT NULL,
  `ifOutOctets_delta` bigint(20) unsigned NOT NULL,
  `ifOutOctets_rate` bigint(20) unsigned NOT NULL,
  `ifInOctets_perc` tinyint(3) unsigned NOT NULL,
  `ifOutOctets_perc` tinyint(3) unsigned NOT NULL,
  `poll_time` int(11) unsigned NOT NULL,
  `poll_period` int(11) unsigned NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ports_adsl`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ports_adsl` (
  `port_id` int(11) NOT NULL,
  `port_adsl_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adslLineCoding` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `adslLineType` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `adslAtucInvVendorID` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `adslAtucInvVersionNumber` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `adslAtucCurrSnrMgn` decimal(5,1) NOT NULL,
  `adslAtucCurrAtn` decimal(5,1) NOT NULL,
  `adslAtucCurrOutputPwr` decimal(5,1) NOT NULL,
  `adslAtucCurrAttainableRate` int(11) NOT NULL,
  `adslAtucChanCurrTxRate` int(11) NOT NULL,
  `adslAturInvSerialNumber` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `adslAturInvVendorID` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `adslAturInvVersionNumber` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `adslAturChanCurrTxRate` int(11) NOT NULL,
  `adslAturCurrSnrMgn` decimal(5,1) NOT NULL,
  `adslAturCurrAtn` decimal(5,1) NOT NULL,
  `adslAturCurrOutputPwr` decimal(5,1) NOT NULL,
  `adslAturCurrAttainableRate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ports_cbqos`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `ports_cbqos` (
  `cbqos_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `direction` varchar(16) NOT NULL,
  `PrePolicyPkt` int(11) NOT NULL,
  `PrePolicyPkt_rate` int(11) NOT NULL,
  `PrePolicyByte` int(11) NOT NULL,
  `PrePolicyByte_rate` int(11) NOT NULL,
  `PostPolicyByte` int(11) NOT NULL,
  `PostPolicyByte_rate` int(11) NOT NULL,
  `DropPkt` int(11) NOT NULL,
  `DropPkt_rate` int(11) NOT NULL,
  `DropByte` int(11) NOT NULL,
  `DropByte_rate` int(11) NOT NULL,
  `NoBufDropPkt` int(11) NOT NULL,
  `NoBufDropPkt_rate` int(11) NOT NULL,
  `cbqos_lastpolled` int(11) NOT NULL,
  `policy_index` int(11) NOT NULL,
  `object_index` int(11) NOT NULL,
  `policy_name` varchar(64) NOT NULL,
  `object_name` varchar(64) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ports_stack`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ports_stack` (
  `device_id` int(11) NOT NULL,
  `port_id_high` int(11) NOT NULL,
  `port_id_low` int(11) NOT NULL,
  `ifStackStatus` varchar(32) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ports_vlans`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ports_vlans` (
  `port_vlan_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `vlan` int(11) NOT NULL,
  `baseport` int(11) NOT NULL,
  `priority` bigint(32) NOT NULL,
  `state` varchar(16) CHARACTER SET utf8 NOT NULL,
  `cost` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processors`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `processors` (
  `processor_id` int(11) NOT NULL,
  `entPhysicalIndex` int(11) DEFAULT NULL,
  `hrDeviceIndex` int(11) DEFAULT NULL,
  `device_id` int(11) NOT NULL,
  `processor_oid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `processor_index` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `processor_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `processor_descr` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `processor_precision` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processors-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `processors-state` (
  `processor_id` int(11) NOT NULL,
  `processor_usage` int(11) NOT NULL,
  `processor_polled` int(11) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='Processor Usage';

-- --------------------------------------------------------

--
-- Table structure for table `pseudowires`
--
-- Creation: Jan 27, 2015 at 06:36 PM
--

CREATE TABLE IF NOT EXISTS `pseudowires` (
  `pseudowire_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `mib` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CISCO-IETF-PW-MIB',
  `pwOid` int(11) unsigned NOT NULL,
  `pwID` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `peer_device_id` int(11) DEFAULT NULL,
  `peer_addr` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `pwMplsPeerLdpID` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `peer_rdns` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pwType` varchar(32) CHARACTER SET utf8 NOT NULL,
  `pwPsnType` varchar(32) CHARACTER SET utf8 NOT NULL,
  `pwLocalIfMtu` int(11) NOT NULL,
  `pwRemoteIfMtu` int(11) NOT NULL,
  `pwDescr` varchar(128) CHARACTER SET utf8 NOT NULL,
  `pwRemoteIfString` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensors`
--
-- Creation: Feb 08, 2015 at 01:36 PM
--

CREATE TABLE IF NOT EXISTS `sensors` (
  `sensor_id` int(11) NOT NULL,
  `sensor_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `sensor_class` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `poller_type` enum('snmp','agent','ipmi') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'snmp',
  `sensor_oid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sensor_index` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sensor_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sensor_descr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sensor_divisor` float NOT NULL DEFAULT '1',
  `sensor_multiplier` float NOT NULL DEFAULT '1',
  `sensor_limit` float DEFAULT NULL,
  `sensor_limit_warn` float DEFAULT NULL,
  `sensor_limit_low` float DEFAULT NULL,
  `sensor_limit_low_warn` float DEFAULT NULL,
  `sensor_custom_limit` tinyint(1) NOT NULL DEFAULT '0',
  `entPhysicalIndex_measured` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `measured_class` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `measured_entity` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalIndex` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entPhysicalClass` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sensor_ignore` tinyint(1) NOT NULL DEFAULT '0',
  `sensor_disable` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensors-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `sensors-state` (
  `sensor_id` int(11) NOT NULL,
  `sensor_value` float(14,5) DEFAULT NULL,
  `sensor_polled` int(11) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `services` (
  `service_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `service_ip` text CHARACTER SET utf8 NOT NULL,
  `service_type` varchar(16) CHARACTER SET utf8 NOT NULL,
  `service_desc` text CHARACTER SET utf8 NOT NULL,
  `service_param` text CHARACTER SET utf8 NOT NULL,
  `service_ignore` tinyint(1) NOT NULL,
  `service_status` tinyint(4) NOT NULL DEFAULT '0',
  `service_checked` int(11) NOT NULL DEFAULT '0',
  `service_changed` int(11) NOT NULL DEFAULT '0',
  `service_message` text CHARACTER SET utf8 NOT NULL,
  `service_disabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slas`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `slas` (
  `sla_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `sla_nr` int(11) NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `rtt_type` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storage`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `storage` (
  `storage_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `storage_mib` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `storage_index` int(11) NOT NULL,
  `storage_type` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `storage_descr` text CHARACTER SET utf8 NOT NULL,
  `storage_hc` tinyint(1) NOT NULL DEFAULT '0',
  `storage_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storage-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `storage-state` (
  `storage_id` int(11) NOT NULL,
  `storage_polled` int(11) NOT NULL,
  `storage_size` bigint(20) NOT NULL,
  `storage_units` int(11) NOT NULL,
  `storage_used` bigint(20) NOT NULL,
  `storage_free` bigint(20) NOT NULL,
  `storage_perc` int(11) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `syslog`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `syslog` (
  `device_id` int(11) DEFAULT NULL,
  `facility` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `priority` tinyint(4) NOT NULL DEFAULT '8',
  `level` tinyint(4) NOT NULL DEFAULT '8',
  `tag` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `program` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `msg` text CHARACTER SET utf8,
  `seq` bigint(20) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toner`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `toner` (
  `toner_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL DEFAULT '0',
  `toner_index` int(11) NOT NULL,
  `toner_type` varchar(64) CHARACTER SET utf8 NOT NULL,
  `toner_oid` varchar(64) CHARACTER SET utf8 NOT NULL,
  `toner_descr` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `toner_capacity` int(11) NOT NULL DEFAULT '0',
  `toner_current` int(11) NOT NULL DEFAULT '0',
  `toner_capacity_oid` varchar(64) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucd_diskio`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `ucd_diskio` (
  `diskio_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `diskio_index` int(11) NOT NULL,
  `diskio_descr` varchar(32) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucd_diskio-state`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `ucd_diskio-state` (
  `diskio_id` int(11) NOT NULL,
  `diskIONReadX` int(11) NOT NULL,
  `diskIONReadX_rate` int(11) NOT NULL,
  `diskIONWrittenX` int(11) NOT NULL,
  `diskIONWrittenX_rate` int(11) NOT NULL,
  `diskIOReads` int(11) NOT NULL,
  `diskIOReads_rate` int(11) NOT NULL,
  `diskIOWrites` int(11) NOT NULL,
  `diskIOWrites_rate` int(11) NOT NULL,
  `diskio_polled` int(11) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL,
  `username` char(30) CHARACTER SET utf8 NOT NULL,
  `password` varchar(34) CHARACTER SET utf8 DEFAULT NULL,
  `realname` varchar(64) CHARACTER SET utf8 NOT NULL,
  `email` varchar(64) CHARACTER SET utf8 NOT NULL,
  `descr` char(30) CHARACTER SET utf8 NOT NULL,
  `level` tinyint(4) NOT NULL DEFAULT '0',
  `can_modify_passwd` tinyint(4) NOT NULL DEFAULT '1',
  `user_options` text CHARACTER SET utf8
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_ckeys`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `users_ckeys` (
  `user_ckey_id` int(11) NOT NULL,
  `user_encpass` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `expire` int(11) NOT NULL,
  `username` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `user_uniq` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_ckey` varchar(32) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_prefs`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `users_prefs` (
  `pref_id` int(11) NOT NULL,
  `user_id` int(16) NOT NULL,
  `pref` varchar(32) CHARACTER SET utf8 NOT NULL,
  `value` varchar(128) CHARACTER SET utf8 NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vlans`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `vlans` (
  `vlan_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `vlan_vlan` int(11) DEFAULT NULL,
  `vlan_domain` int(11) DEFAULT NULL,
  `vlan_name` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `vlan_type` varchar(16) CHARACTER SET utf8 DEFAULT NULL,
  `vlan_mtu` int(11) DEFAULT NULL,
  `vlan_status` varchar(16) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vlans_fdb`
--
-- Creation: Feb 08, 2015 at 02:29 PM
--

CREATE TABLE IF NOT EXISTS `vlans_fdb` (
  `device_id` int(11) NOT NULL,
  `vlan_id` int(11) NOT NULL,
  `port_id` int(11) NOT NULL,
  `mac_address` varchar(32) CHARACTER SET latin1 NOT NULL,
  `fdb_status` varchar(32) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vminfo`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `vminfo` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `vm_type` varchar(16) NOT NULL DEFAULT 'vmware',
  `vmwVmVMID` int(11) NOT NULL,
  `vmwVmDisplayName` varchar(128) NOT NULL,
  `vmwVmGuestOS` varchar(128) NOT NULL,
  `vmwVmMemSize` int(11) NOT NULL,
  `vmwVmCpus` int(11) NOT NULL,
  `vmwVmState` varchar(128) NOT NULL,
  `vmwVmUUID` varchar(64) DEFAULT NULL,
  `vmwVmGuestState` varchar(16) DEFAULT NULL,
  `vmwVmConfigFile` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vrfs`
--
-- Creation: Jan 26, 2015 at 06:11 AM
--

CREATE TABLE IF NOT EXISTS `vrfs` (
  `vrf_id` int(11) NOT NULL,
  `vrf_oid` varchar(256) CHARACTER SET utf8 NOT NULL,
  `vrf_name` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `mplsVpnVrfRouteDistinguisher` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `mplsVpnVrfDescription` text CHARACTER SET utf8 NOT NULL,
  `device_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wifi_accesspoints`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `wifi_accesspoints` (
  `wifi_accesspoint_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `ap_number` int(11) DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serial` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fingerprint` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delete` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wifi_radios`
--
-- Creation: Feb 01, 2015 at 12:38 PM
--

CREATE TABLE IF NOT EXISTS `wifi_radios` (
  `wifi_radio_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `radio_mib` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `radio_number` int(11) NOT NULL,
  `radio_type` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `radio_protection` varchar(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  `radio_bsstype` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `radio_status` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `radio_ap` int(11) NOT NULL,
  `radio_clients` int(11) DEFAULT NULL,
  `radio_txpower` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `radio_channel` int(11) DEFAULT NULL,
  `radio_mac` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wifi_sessions`
--
-- Creation: Feb 08, 2015 at 02:48 PM
--

CREATE TABLE IF NOT EXISTS `wifi_sessions` (
  `wifi_session_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `mac_addr` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session_id` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ipv4_addr` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ssid` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `radio_id` int(11) DEFAULT NULL,
  `uptime` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accesspoints`
--
ALTER TABLE `accesspoints`
  ADD PRIMARY KEY (`accesspoint_id`), ADD KEY `deleted` (`deleted`), ADD KEY `name` (`name`,`radio_number`);

--
-- Indexes for table `accesspoints-state`
--
ALTER TABLE `accesspoints-state`
  ADD PRIMARY KEY (`accesspoint_id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD KEY `id` (`id`);

--
-- Indexes for table `alert_assoc`
--
ALTER TABLE `alert_assoc`
  ADD PRIMARY KEY (`alert_assoc_id`);

--
-- Indexes for table `alert_contacts`
--
ALTER TABLE `alert_contacts`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `alert_contacts_assoc`
--
ALTER TABLE `alert_contacts_assoc`
  ADD PRIMARY KEY (`aca_id`);

--
-- Indexes for table `alert_log`
--
ALTER TABLE `alert_log`
  ADD PRIMARY KEY (`event_id`), ADD KEY `type` (`entity_type`), ADD KEY `device_id` (`device_id`), ADD KEY `timestamp` (`timestamp`), ADD KEY `entity` (`entity_type`,`entity_id`), ADD KEY `alert_device` (`alert_test_id`,`device_id`);

--
-- Indexes for table `alert_table`
--
ALTER TABLE `alert_table`
  ADD PRIMARY KEY (`alert_table_id`), ADD UNIQUE KEY `alert_id_2` (`alert_test_id`,`entity_type`,`entity_id`), ADD KEY `device_id` (`device_id`), ADD KEY `device_id_2` (`device_id`);

--
-- Indexes for table `alert_table-state`
--
ALTER TABLE `alert_table-state`
  ADD PRIMARY KEY (`alert_table_id`);

--
-- Indexes for table `alert_tests`
--
ALTER TABLE `alert_tests`
  ADD PRIMARY KEY (`alert_test_id`), ADD UNIQUE KEY `alert_name` (`alert_name`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`app_id`), ADD UNIQUE KEY `dev_type_inst` (`device_id`,`app_type`,`app_instance`);

--
-- Indexes for table `applications-state`
--
ALTER TABLE `applications-state`
  ADD UNIQUE KEY `application_id` (`application_id`);

--
-- Indexes for table `authlog`
--
ALTER TABLE `authlog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bgpPeers`
--
ALTER TABLE `bgpPeers`
  ADD PRIMARY KEY (`bgpPeer_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `bgpPeers-state`
--
ALTER TABLE `bgpPeers-state`
  ADD PRIMARY KEY (`bgpPeer_id`);

--
-- Indexes for table `bgpPeers_cbgp`
--
ALTER TABLE `bgpPeers_cbgp`
  ADD PRIMARY KEY (`cbgp_id`), ADD UNIQUE KEY `unique_index` (`device_id`,`bgpPeerRemoteAddr`,`afi`,`safi`), ADD KEY `device_id` (`device_id`,`bgpPeerRemoteAddr`);

--
-- Indexes for table `bgpPeers_cbgp-state`
--
ALTER TABLE `bgpPeers_cbgp-state`
  ADD UNIQUE KEY `unique_index` (`cbgp_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD UNIQUE KEY `bill_id` (`bill_id`);

--
-- Indexes for table `bill_data`
--
ALTER TABLE `bill_data`
  ADD KEY `bill_id` (`bill_id`,`timestamp`);

--
-- Indexes for table `bill_history`
--
ALTER TABLE `bill_history`
  ADD PRIMARY KEY (`bill_hist_id`), ADD UNIQUE KEY `unique_index` (`bill_id`,`bill_datefrom`,`bill_dateto`), ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `bill_ports`
--
ALTER TABLE `bill_ports`
  ADD UNIQUE KEY `bill_id_2` (`bill_id`,`port_id`), ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `cef_prefix`
--
ALTER TABLE `cef_prefix`
  ADD PRIMARY KEY (`cef_pfx_id`);

--
-- Indexes for table `cef_switching`
--
ALTER TABLE `cef_switching`
  ADD PRIMARY KEY (`cef_switching_id`), ADD UNIQUE KEY `device_id` (`device_id`,`entPhysicalIndex`,`afi`,`cef_index`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`), ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `dbSchema`
--
ALTER TABLE `dbSchema`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`device_id`), ADD KEY `status` (`status`), ADD KEY `hostname` (`hostname`), ADD KEY `sysName` (`sysName`), ADD KEY `os` (`os`), ADD KEY `ignore` (`ignore`), ADD KEY `disabled_lastpolled` (`disabled`,`last_polled_timetaken`);

--
-- Indexes for table `devices_attribs`
--
ALTER TABLE `devices_attribs`
  ADD PRIMARY KEY (`attrib_id`), ADD KEY `device_id` (`device_id`), ADD KEY `idx0_devices_attribs` (`device_id`,`attrib_type`(50)), ADD KEY `device_id_2` (`device_id`), ADD KEY `device_type` (`device_id`,`attrib_type`(50));

--
-- Indexes for table `devices_locations`
--
ALTER TABLE `devices_locations`
  ADD PRIMARY KEY (`location_id`), ADD UNIQUE KEY `device_id` (`device_id`);

--
-- Indexes for table `devices_mibs`
--
ALTER TABLE `devices_mibs`
  ADD PRIMARY KEY (`mib_id`), ADD KEY `mib` (`mib`,`table_name`,`oid`);

--
-- Indexes for table `devices_perftimes`
--
ALTER TABLE `devices_perftimes`
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `device_graphs`
--
ALTER TABLE `device_graphs`
  ADD PRIMARY KEY (`device_graph_id`), ADD KEY `device_id` (`device_id`), ADD KEY `graph` (`graph`);

--
-- Indexes for table `eigrp_ports`
--
ALTER TABLE `eigrp_ports`
  ADD PRIMARY KEY (`eigrp_port_id`), ADD UNIQUE KEY `eigrp_vpn` (`eigrp_vpn`,`eigrp_as`,`eigrp_ifIndex`,`device_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `entPhysical`
--
ALTER TABLE `entPhysical`
  ADD PRIMARY KEY (`entPhysical_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `entPhysical-state`
--
ALTER TABLE `entPhysical-state`
  ADD PRIMARY KEY (`entPhysical_state_id`), ADD KEY `device_id_index` (`device_id`,`entPhysicalIndex`);

--
-- Indexes for table `eventlog`
--
ALTER TABLE `eventlog`
  ADD PRIMARY KEY (`event_id`), ADD KEY `host` (`device_id`), ADD KEY `datetime` (`timestamp`), ADD KEY `host_2` (`device_id`,`timestamp`), ADD KEY `type` (`entity_type`), ADD KEY `type_device` (`entity_type`,`device_id`);

--
-- Indexes for table `graph_types`
--
ALTER TABLE `graph_types`
  ADD KEY `graph_type` (`graph_type`), ADD KEY `graph_subtype` (`graph_subtype`), ADD KEY `graph_section` (`graph_section`);

--
-- Indexes for table `graph_types_dead`
--
ALTER TABLE `graph_types_dead`
  ADD KEY `graph_type` (`graph_type`), ADD KEY `graph_subtype` (`graph_subtype`), ADD KEY `graph_section` (`graph_section`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`), ADD UNIQUE KEY `group_name` (`group_name`);

--
-- Indexes for table `groups_assoc`
--
ALTER TABLE `groups_assoc`
  ADD PRIMARY KEY (`group_assoc_id`);

--
-- Indexes for table `group_table`
--
ALTER TABLE `group_table`
  ADD PRIMARY KEY (`group_table_id`), ADD UNIQUE KEY `alert_id_2` (`group_id`,`entity_type`,`entity_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `hrDevice`
--
ALTER TABLE `hrDevice`
  ADD PRIMARY KEY (`hrDevice_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `ipsec_tunnels`
--
ALTER TABLE `ipsec_tunnels`
  ADD PRIMARY KEY (`tunnel_id`), ADD UNIQUE KEY `unique_index` (`device_id`,`peer_addr`);

--
-- Indexes for table `ipv4_addresses`
--
ALTER TABLE `ipv4_addresses`
  ADD PRIMARY KEY (`ipv4_address_id`), ADD KEY `interface_id` (`port_id`), ADD KEY `ipv4_address` (`ipv4_address`);

--
-- Indexes for table `ipv4_networks`
--
ALTER TABLE `ipv4_networks`
  ADD PRIMARY KEY (`ipv4_network_id`);

--
-- Indexes for table `ipv6_addresses`
--
ALTER TABLE `ipv6_addresses`
  ADD PRIMARY KEY (`ipv6_address_id`), ADD KEY `interface_id` (`port_id`), ADD KEY `ipv6_address` (`ipv6_address`);

--
-- Indexes for table `ipv6_networks`
--
ALTER TABLE `ipv6_networks`
  ADD PRIMARY KEY (`ipv6_network_id`);

--
-- Indexes for table `ip_mac`
--
ALTER TABLE `ip_mac`
  ADD PRIMARY KEY (`mac_id`), ADD KEY `port_id` (`port_id`);

--
-- Indexes for table `juniAtmVp`
--
ALTER TABLE `juniAtmVp`
  ADD PRIMARY KEY (`juniAtmVp_id`), ADD KEY `port_id` (`port_id`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`), ADD KEY `src_if` (`local_port_id`), ADD KEY `dst_if` (`remote_port_id`);

--
-- Indexes for table `loadbalancer_rservers`
--
ALTER TABLE `loadbalancer_rservers`
  ADD PRIMARY KEY (`rserver_id`);

--
-- Indexes for table `loadbalancer_vservers`
--
ALTER TABLE `loadbalancer_vservers`
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `mac_accounting`
--
ALTER TABLE `mac_accounting`
  ADD PRIMARY KEY (`ma_id`), ADD UNIQUE KEY `port_vlan_mac` (`port_id`,`vlan_id`,`mac`), ADD KEY `device_id` (`device_id`), ADD KEY `device_ma` (`device_id`,`ma_id`);

--
-- Indexes for table `mac_accounting-state`
--
ALTER TABLE `mac_accounting-state`
  ADD PRIMARY KEY (`ma_id`);

--
-- Indexes for table `mempools`
--
ALTER TABLE `mempools`
  ADD PRIMARY KEY (`mempool_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `mempools-state`
--
ALTER TABLE `mempools-state`
  ADD PRIMARY KEY (`mempool_id`);

--
-- Indexes for table `munin_plugins`
--
ALTER TABLE `munin_plugins`
  ADD PRIMARY KEY (`mplug_id`), ADD UNIQUE KEY `dev_mplug` (`device_id`,`mplug_type`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `munin_plugins_ds`
--
ALTER TABLE `munin_plugins_ds`
  ADD PRIMARY KEY (`mplug_ds_id`), ADD UNIQUE KEY `splug_id` (`mplug_id`,`ds_name`);

--
-- Indexes for table `netscaler_services`
--
ALTER TABLE `netscaler_services`
  ADD PRIMARY KEY (`svc_id`), ADD KEY `device_id` (`device_id`,`svc_name`);

--
-- Indexes for table `netscaler_services_vservers`
--
ALTER TABLE `netscaler_services_vservers`
  ADD PRIMARY KEY (`sv_id`), ADD UNIQUE KEY `index` (`device_id`,`vsvr_name`,`svc_name`);

--
-- Indexes for table `netscaler_vservers`
--
ALTER TABLE `netscaler_vservers`
  ADD PRIMARY KEY (`vsvr_id`), ADD KEY `device_id` (`device_id`,`vsvr_name`);

--
-- Indexes for table `ospf_areas`
--
ALTER TABLE `ospf_areas`
  ADD UNIQUE KEY `device_area` (`device_id`,`ospfAreaId`);

--
-- Indexes for table `ospf_instances`
--
ALTER TABLE `ospf_instances`
  ADD UNIQUE KEY `device_id` (`device_id`,`ospf_instance_id`);

--
-- Indexes for table `ospf_nbrs`
--
ALTER TABLE `ospf_nbrs`
  ADD UNIQUE KEY `device_id` (`device_id`,`ospf_nbr_id`);

--
-- Indexes for table `ospf_ports`
--
ALTER TABLE `ospf_ports`
  ADD UNIQUE KEY `device_id` (`device_id`,`ospf_port_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`pkg_id`), ADD UNIQUE KEY `unique_key` (`device_id`,`name`,`manager`,`arch`,`version`,`build`), ADD KEY `device_id` (`device_id`), ADD KEY `name` (`name`);

--
-- Indexes for table `perf_times`
--
ALTER TABLE `perf_times`
  ADD KEY `type` (`type`);

--
-- Indexes for table `ports`
--
ALTER TABLE `ports`
  ADD PRIMARY KEY (`port_id`), ADD UNIQUE KEY `device_ifIndex` (`device_id`,`ifIndex`), ADD KEY `if_2` (`ifDescr`);

--
-- Indexes for table `ports-state`
--
ALTER TABLE `ports-state`
  ADD PRIMARY KEY (`port_id`);

--
-- Indexes for table `ports_adsl`
--
ALTER TABLE `ports_adsl`
  ADD UNIQUE KEY `interface_id` (`port_id`);

--
-- Indexes for table `ports_cbqos`
--
ALTER TABLE `ports_cbqos`
  ADD PRIMARY KEY (`cbqos_id`), ADD UNIQUE KEY `device_id` (`device_id`,`port_id`,`policy_index`,`object_index`);

--
-- Indexes for table `ports_stack`
--
ALTER TABLE `ports_stack`
  ADD UNIQUE KEY `device_id` (`device_id`,`port_id_high`,`port_id_low`);

--
-- Indexes for table `ports_vlans`
--
ALTER TABLE `ports_vlans`
  ADD PRIMARY KEY (`port_vlan_id`), ADD UNIQUE KEY `unique` (`device_id`,`port_id`,`vlan`);

--
-- Indexes for table `processors`
--
ALTER TABLE `processors`
  ADD PRIMARY KEY (`processor_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `processors-state`
--
ALTER TABLE `processors-state`
  ADD PRIMARY KEY (`processor_id`);

--
-- Indexes for table `pseudowires`
--
ALTER TABLE `pseudowires`
  ADD PRIMARY KEY (`pseudowire_id`), ADD KEY `port_id` (`port_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `sensors`
--
ALTER TABLE `sensors`
  ADD PRIMARY KEY (`sensor_id`), ADD KEY `sensor_host` (`device_id`), ADD KEY `sensor_class` (`sensor_class`), ADD KEY `sensor_type` (`sensor_type`);

--
-- Indexes for table `sensors-state`
--
ALTER TABLE `sensors-state`
  ADD PRIMARY KEY (`sensor_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`), ADD KEY `service_host` (`device_id`);

--
-- Indexes for table `slas`
--
ALTER TABLE `slas`
  ADD PRIMARY KEY (`sla_id`), ADD UNIQUE KEY `unique_key` (`device_id`,`sla_nr`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `storage`
--
ALTER TABLE `storage`
  ADD PRIMARY KEY (`storage_id`), ADD UNIQUE KEY `index_unique` (`device_id`,`storage_mib`,`storage_index`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `storage-state`
--
ALTER TABLE `storage-state`
  ADD PRIMARY KEY (`storage_id`);

--
-- Indexes for table `syslog`
--
ALTER TABLE `syslog`
  ADD PRIMARY KEY (`seq`), ADD KEY `datetime` (`timestamp`), ADD KEY `device_id` (`device_id`), ADD KEY `program` (`program`), ADD KEY `priority` (`priority`), ADD KEY `program_device` (`program`,`device_id`);

--
-- Indexes for table `toner`
--
ALTER TABLE `toner`
  ADD PRIMARY KEY (`toner_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `ucd_diskio`
--
ALTER TABLE `ucd_diskio`
  ADD PRIMARY KEY (`diskio_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `ucd_diskio-state`
--
ALTER TABLE `ucd_diskio-state`
  ADD PRIMARY KEY (`diskio_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`), ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users_ckeys`
--
ALTER TABLE `users_ckeys`
  ADD PRIMARY KEY (`user_ckey_id`);

--
-- Indexes for table `users_prefs`
--
ALTER TABLE `users_prefs`
  ADD PRIMARY KEY (`pref_id`), ADD UNIQUE KEY `user_id.pref` (`user_id`,`pref`), ADD KEY `pref` (`pref`);

--
-- Indexes for table `vlans`
--
ALTER TABLE `vlans`
  ADD PRIMARY KEY (`vlan_id`), ADD KEY `device_id` (`device_id`,`vlan_vlan`);

--
-- Indexes for table `vlans_fdb`
--
ALTER TABLE `vlans_fdb`
  ADD UNIQUE KEY `dev_vlan_mac` (`device_id`,`vlan_id`,`mac_address`), ADD KEY `device_id` (`device_id`), ADD KEY `port_id` (`port_id`);

--
-- Indexes for table `vminfo`
--
ALTER TABLE `vminfo`
  ADD PRIMARY KEY (`id`), ADD KEY `device_id` (`device_id`), ADD KEY `vmwVmVMID` (`vmwVmVMID`);

--
-- Indexes for table `vrfs`
--
ALTER TABLE `vrfs`
  ADD PRIMARY KEY (`vrf_id`), ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `wifi_accesspoints`
--
ALTER TABLE `wifi_accesspoints`
  ADD PRIMARY KEY (`wifi_accesspoint_id`);

--
-- Indexes for table `wifi_radios`
--
ALTER TABLE `wifi_radios`
  ADD PRIMARY KEY (`wifi_radio_id`), ADD UNIQUE KEY `unique_dev_ap_number` (`device_id`,`radio_ap`,`radio_number`);

--
-- Indexes for table `wifi_sessions`
--
ALTER TABLE `wifi_sessions`
  ADD PRIMARY KEY (`wifi_session_id`), ADD KEY `device_id` (`device_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accesspoints`
--
ALTER TABLE `accesspoints`
  MODIFY `accesspoint_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `alert_assoc`
--
ALTER TABLE `alert_assoc`
  MODIFY `alert_assoc_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `alert_contacts`
--
ALTER TABLE `alert_contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `alert_contacts_assoc`
--
ALTER TABLE `alert_contacts_assoc`
  MODIFY `aca_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `alert_log`
--
ALTER TABLE `alert_log`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `alert_table`
--
ALTER TABLE `alert_table`
  MODIFY `alert_table_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `alert_tests`
--
ALTER TABLE `alert_tests`
  MODIFY `alert_test_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `authlog`
--
ALTER TABLE `authlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bgpPeers`
--
ALTER TABLE `bgpPeers`
  MODIFY `bgpPeer_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bgpPeers_cbgp`
--
ALTER TABLE `bgpPeers_cbgp`
  MODIFY `cbgp_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bill_history`
--
ALTER TABLE `bill_history`
  MODIFY `bill_hist_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cef_prefix`
--
ALTER TABLE `cef_prefix`
  MODIFY `cef_pfx_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cef_switching`
--
ALTER TABLE `cef_switching`
  MODIFY `cef_switching_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `devices_attribs`
--
ALTER TABLE `devices_attribs`
  MODIFY `attrib_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `devices_locations`
--
ALTER TABLE `devices_locations`
  MODIFY `location_id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `devices_mibs`
--
ALTER TABLE `devices_mibs`
  MODIFY `mib_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `device_graphs`
--
ALTER TABLE `device_graphs`
  MODIFY `device_graph_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eigrp_ports`
--
ALTER TABLE `eigrp_ports`
  MODIFY `eigrp_port_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `entPhysical`
--
ALTER TABLE `entPhysical`
  MODIFY `entPhysical_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `entPhysical-state`
--
ALTER TABLE `entPhysical-state`
  MODIFY `entPhysical_state_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eventlog`
--
ALTER TABLE `eventlog`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `groups_assoc`
--
ALTER TABLE `groups_assoc`
  MODIFY `group_assoc_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `group_table`
--
ALTER TABLE `group_table`
  MODIFY `group_table_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hrDevice`
--
ALTER TABLE `hrDevice`
  MODIFY `hrDevice_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ipsec_tunnels`
--
ALTER TABLE `ipsec_tunnels`
  MODIFY `tunnel_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ipv4_addresses`
--
ALTER TABLE `ipv4_addresses`
  MODIFY `ipv4_address_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ipv4_networks`
--
ALTER TABLE `ipv4_networks`
  MODIFY `ipv4_network_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ipv6_addresses`
--
ALTER TABLE `ipv6_addresses`
  MODIFY `ipv6_address_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ipv6_networks`
--
ALTER TABLE `ipv6_networks`
  MODIFY `ipv6_network_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ip_mac`
--
ALTER TABLE `ip_mac`
  MODIFY `mac_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `juniAtmVp`
--
ALTER TABLE `juniAtmVp`
  MODIFY `juniAtmVp_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `loadbalancer_rservers`
--
ALTER TABLE `loadbalancer_rservers`
  MODIFY `rserver_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mac_accounting`
--
ALTER TABLE `mac_accounting`
  MODIFY `ma_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mempools`
--
ALTER TABLE `mempools`
  MODIFY `mempool_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `munin_plugins`
--
ALTER TABLE `munin_plugins`
  MODIFY `mplug_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `munin_plugins_ds`
--
ALTER TABLE `munin_plugins_ds`
  MODIFY `mplug_ds_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `netscaler_services`
--
ALTER TABLE `netscaler_services`
  MODIFY `svc_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `netscaler_services_vservers`
--
ALTER TABLE `netscaler_services_vservers`
  MODIFY `sv_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `netscaler_vservers`
--
ALTER TABLE `netscaler_vservers`
  MODIFY `vsvr_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `pkg_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ports`
--
ALTER TABLE `ports`
  MODIFY `port_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ports_cbqos`
--
ALTER TABLE `ports_cbqos`
  MODIFY `cbqos_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ports_vlans`
--
ALTER TABLE `ports_vlans`
  MODIFY `port_vlan_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `processors`
--
ALTER TABLE `processors`
  MODIFY `processor_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pseudowires`
--
ALTER TABLE `pseudowires`
  MODIFY `pseudowire_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sensors`
--
ALTER TABLE `sensors`
  MODIFY `sensor_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `slas`
--
ALTER TABLE `slas`
  MODIFY `sla_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `storage`
--
ALTER TABLE `storage`
  MODIFY `storage_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `syslog`
--
ALTER TABLE `syslog`
  MODIFY `seq` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `toner`
--
ALTER TABLE `toner`
  MODIFY `toner_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ucd_diskio`
--
ALTER TABLE `ucd_diskio`
  MODIFY `diskio_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users_ckeys`
--
ALTER TABLE `users_ckeys`
  MODIFY `user_ckey_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users_prefs`
--
ALTER TABLE `users_prefs`
  MODIFY `pref_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vlans`
--
ALTER TABLE `vlans`
  MODIFY `vlan_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vminfo`
--
ALTER TABLE `vminfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vrfs`
--
ALTER TABLE `vrfs`
  MODIFY `vrf_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wifi_accesspoints`
--
ALTER TABLE `wifi_accesspoints`
  MODIFY `wifi_accesspoint_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wifi_radios`
--
ALTER TABLE `wifi_radios`
  MODIFY `wifi_radio_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `wifi_sessions`
--
ALTER TABLE `wifi_sessions`
  MODIFY `wifi_session_id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
