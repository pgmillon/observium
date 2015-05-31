<?php // -*- mode:php; tab-width:4; c-basic-offset:4; intent-tabs-mode:nil; -*-
/* 
 * This file contains the MAC class
 *
 * Copyright (c) 2006 Andrew Teixeira
 *
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link      http://pear.php.net/package/Net_MAC
 * @author    Andrew Teixeira <ateixeira@gmail.com>
 *
 * $Id: MAC.php,v 1.6 2007/05/01 18:03:57 atex Exp $
 */

/**
 * Require PEAR/Exception.php since we will be using PEAR Exceptions
 * in this class.
 */
require_once 'PEAR/Exception.php';

/**
 * Constant to represent the maximum length of a line in the
 * manufacturers file.
 */
define('NET_MAC_LINE_MAXLENGTH', 256);

/**
 * Error constant: signifies no problem (OK)
 */
define('NET_MAC_ERROR_OK', 0);

/**
 * Error constant: signifies a bad option being passed to a function
 */
define('NET_MAC_ERROR_BADOPT', 1);

/**
 * Error constant: signifies bad data being passed to a function
 */
define('NET_MAC_ERROR_BADDATA', 2);

/**
 * Error constant: signifies a bad database connection
 */
define('NET_MAC_ERROR_BADDB', 3);

/**
 * Error constant: signifies a bad manufacturers file
 */
define('NET_MAC_ERROR_BADFILE', 4);


/**
 * Extension of the main PEAR_Exception Class for use with the Net_MAC
 * class.
 *
 * @package   Net_MAC
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link      http://pear.php.net/package/Net_MAC
 * @version   1.0
 * @author    Andrew Teixeira <ateixeira@gmail.com>
 *
 * @access    public
 */
class Net_MAC_Exception extends PEAR_Exception {}


/**
 * Class to validate and cleanly format Media Access Control (MAC)
 * addresses
 *
 * @package   Net_MAC
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://pear.php.net/package/Net_MAC
 * @version   1.0
 * @author    Andrew Teixeira <ateixeira@gmail.com>
 *
 * @access    public
 */
class Net_MAC
{
    /**
     * The MAC address to work on.
     *
     * @access protected
     * @var string
     */
    protected $_macaddr;

    /**
     * A database instance to use in looking up MAC-to-vendor
     * relationships.
     *
     * @access protected
     * @var object
     */
    protected $_db;

    /**
     * The options to use while connecting to the database.
     *
     * @access protected
     * @var array
     */
    protected $_dbOptions;

    /**
     * The default constructor
     *
     * This is the default constructor that will create and populate a
     * valid Net_MAC object.
     *
     * @access public
     *
     * @param object &$db A valid instantiated {@link
     * http://pear.php.net/package/MDB2/ MDB2} object to use when
     * adding/retrieving information from the database for MAC address
     * vendors
     * @param array $options An array of options to use with the
     * database in retrieving MAC address vendors.  The associative
     * array should have key/value pairs as follows:
     *
     * <ul>
     *  <li><b>tablename</b>: The name of the table where MAC address
     *  vendor information lives</li>
     *  <li><b>macaddrcol</b>: The name of the column containing the
     *  MAC address prefixes</li>
     *  <li><b>vendorcol</b>: The name of the column containing the
     *  vendor name</li>
     *  <li><b>desccol</b>: The name of the column containing any
     *  extra descriptive information derived from the vendor list</li>
     * </ul>
     *
     * @return void No return value.  A {@link Net_MAC_Exception
     * Net_MAC_Exception} Exception object will be thrown if there is
     * an error during construction so the constructor should be
     * called from a try/catch block
     */
    function __construct(&$db, $options = NULL) {
        require_once 'MDB2.php';

        $this->_db = NULL;
        $this->_macaddr = NULL;
        $this->_dbOptions = NULL;
    
        if (!$db instanceof MDB2_Driver_Common) {
            throw new Net_MAC_Exception('Bad database object', NET_MAC_ERROR_BADDB);
        }

        if (!is_array($options)) {
            throw new Net_MAC_Exception('Second parameter must be an array', NET_MAC_ERROR_BADOPT);
        }

        if (!isset($options['tablename'])) {
            throw new Net_MAC_Exception('No table name given in options', NET_MAC_ERROR_BADDATA);
        }

        if (!isset($options['macaddrcol'])) {
            throw new Net_MAC_Exception('No MAC address column name given in options', NET_MAC_ERROR_BADDATA);
        }

        if (!isset($options['vendorcol'])) {
            throw new Net_MAC_Exception('No vendor column name given in options', NET_MAC_ERROR_BADDATA);
        }

        if (!isset($options['desccol'])) {
            throw new Net_MAC_Exception('No description column name given in options', NET_MAC_ERROR_BADDATA);
        }

        $this->_db = $db;
        $this->_db->loadModule('Extended');
        $this->_dbOptions = $options;
    } /* end default constructor */

    /**
     * Checks a MAC address
     *
     * This function will check a MAC address to make sure it is
     * valid.
     *
     * @access static
     *
     * @param string $input The string containing the MAC Address
     * @param string $delimiter The string representing the delimiter
     * to use when verifying the MAC Address
     *
     * @return string <b>true</b> if the MAC address is valid,
     * <b>false</b> otherwise
     */
    static function check($input, $delimiter = ':')
    {
        // Check for 6 octets without any punctuation
        $retval = preg_match('/^([0-9a-fA-F][0-9a-fA-F]\Q'.$delimiter.'\E){5}([0-9a-fA-F][0-9a-fA-F]){1}$/', $input);
                    
        return $retval;
    } /* end method check */

    /**
     * Sets the MAC address
     *
     * This method will set the MAC address in the class.
     *
     * @access public
     *
     * @param string $macaddr The string representing the MAC address
     * @param string $delimiter The string representing the delimiter
     * to use when verifying the MAC Address
     *
     * @return bool Returns <b>true</b> if the MAC address is set
     * correctly, <b>false</b> otherwise 
     */
    function setMAC($macaddr, $delimiter = ':')
    {
        $validMAC = self::check($macaddr, $delimiter);
        if ($validMAC) {
            $this->_macaddr = $macaddr;
            return true;
        }

        return false;
    } /* end method setMac */

    /**
     * Formats a MAC address
     *
     * This function will format a MAC address into XX:XX:XX:XX:XX:XX
     * format from whatever format is passed to the function.  The
     * delimiter (: in the example above) will be replaced with
     * whatever string is passed to the $delimiter parameter
     * (default :).
     *
     * @access static
     *
     * @param string $input The string containing the MAC Address
     * @param string $delimiter The string representing the delimiter
     * to use when formatting the MAC Address
     * @param bool $uppercase If set to true (default), the
     * hexadecimal values in the MAC Address will be returned in
     * uppercase.  If false, the hexadecimal values will be returned
     * in lowercase.
     *
     * @return string The formatted MAC Address or <b>false</b> on
     * error
     */
    static function format($input, $delimiter = ':', $uppercase = true)
    {
        /* Replace all characters not in a valid MAC address with
         * nothing.  We are going to be testing for a MAC address as
         * XXXXXXXXXXXX instead of XX:XX:XX:XX:XX:XX
         */
        $macaddr = preg_replace('/[g-zG-Z]*\W*_*/', '', $input);
        
        /* If $uppercase is true, set all the alpha characters to
         * uppercase, otherwise set all the alpha characters to
         * lowercase 
         */
        $macaddr = ($uppercase) ? strtoupper($macaddr) : strtolower($macaddr);

        // Check for 6 octets without any punctuation
        if (!preg_match('/^([0-9a-fA-F][0-9a-fA-F]){6}$/', $macaddr)) {
            return false;
        }
            
        // Add back in the $delimiter delimiters
        $macaddr = preg_replace('/([0-9a-fA-F][0-9a-fA-F]){1}/', '$1' . $delimiter, $macaddr);

        // Remove the trailing $delimiter
        $macaddr = preg_replace('/' . $delimiter . '$/', '', $macaddr);

        return $macaddr;
    } /* end method format */

    /**
     * Import a manufacturers' file to the database
     *
     * This method will parse a manufacturers' file, such as the one
     * from {@link
     * http://anonsvn.wireshark.org/wireshark/trunk/manuf}, containing
     * a list of MAC address prefix-to-vendor relationships.  If the
     * $doReturn parameter is <b>false</b>, then the data will be
     * imported into the database defined by the factory of this
     * class.  However, if $doReturn is <b>true</b>, then the return
     * will be an associative array with the key being the MAC address
     * prefix and the data being an associative array with the keys
     * 'vendor' and 'description'.
     *
     * @access public
     *
     * @param string $file The filename or URL of the manufacturers'
     * file to parse.
     * @param bool $doReturn If <b>true</b>, an array will be
     * returned, if <b>false</b>, the data will be imported into the
     * database. (default: false)
     *
     * @return mixed If $doReturn is true, the method will return an
     * array.  Otherwise, the method will return <b>true</b> on
     * success.  A {@link Net_MAC_Exception Net_MAC_Exception}
     * Exception object will be thrown on failure in either case.
     */
    function importVendors($file, $doReturn = false) {
        if ($file == NULL) {
            throw new Net_MAC_Exception('No file or URL given', NET_MAC_ERROR_BADFILE);
        }

        $fp = @fopen($file, 'r');
        if ($fp == false) {
            throw new Net_MAC_Exception('Cannot open the file or URL given', NET_MAC_ERROR_BADFILE);
        }

        if ($doReturn) {
            $retArr = array();
        }
        else {
            // Prepare parameters for MDB2->buildManipSQL()
            $fields = array ($this->_dbOptions['macaddrcol'],
                             $this->_dbOptions['vendorcol'],
                             $this->_dbOptions['desccol']);

            $sql = $this->_db->buildManipSQL($this->_dbOptions['tablename'],
                                             $fields, MDB2_AUTOQUERY_INSERT);

            $query = $this->_db->prepare($sql);
        }

        while ($line = fgets($fp, NET_MAC_LINE_MAXLENGTH)) {
            // Remove comments
            if ( preg_match('/^\#/', $line) ) {
                continue;
            }

            if ( preg_match('/^([0-9A-Fa-f][0-9A-Fa-f]:){2}([0-9A-Fa-f][0-9A-Fa-f]){1}/', $line) ) {
                $pieces = preg_split('/\s+/', $line);

                // Since the file is space-delimited, we now need to
                // reconstruct the description field if it existed
                $desc = NULL;
                for ($i = 3; $i < count($pieces); $i++) {
                    $desc .= $pieces[$i].' ';
                }
                $desc = rtrim($desc);

                if ( (isset($pieces[0])) && (isset($pieces[1])) ) {
                    if ($doReturn) {
                        $retArr[$pieces[0]] = array('vendor' => $pieces[1],
                                                    'description' => $desc);
                    }
                    else {
                        $values = array($pieces[0], $pieces[1], $desc);
                        $result = $query->execute($values);
                    }
                }
            }
        }

        return ($doReturn) ? $retArr : true;
    } /* end method importVendors */

    /**
     * Finds the vendor for a MAC address
     *
     * This method will search through the database to find a vendor
     * that matches the MAC address stored in the class using {@link
     * setMAC setMAC}.  If the $macList parameter is set, the method
     * will use the array stored in $macList as the data source to
     * find the MAC vendor instead of the database.  The array would
     * have to be an array with the same characteristics as one
     * returned from the {@link importVendors importVendors} method
     * when using the $doReturn parameter.
     *
     * @access public
     *
     * @param bool $getDescription If set to true, the return value
     * will be an array with keys 'vendor' and 'description'.
     * Normally the method will simply return the vendor name.
     * @param array $macList An optional list of MAC-to-vendor
     * relationships to search instead of using the
     * database. (default: NULL)
     *
     * @return mixed Returns an associative array if $getDescription
     * is <b>true</b>, returns a string with the vendor name if
     * $getDescription is <b>false</b>.  If the MAC vendor cannot be
     * found in the vendor list, <b>false</b> is returned.
     */
    function findVendor($getDescription = false, $macList = NULL)
    {
        if ($macList == NULL) {
            $macaddrcol = $this->_dbOptions['macaddrcol'];
            $vendorcol = $this->_dbOptions['vendorcol'];
            $desccol = $this->_dbOptions['desccol'];

            /* The manufacturers' list only uses the first 3 octets,
             * so we need to get that portion of the stored MAC
             * address. */
            $macaddr = substr(self::format($this->_macaddr, ':'), 0, 8);

            // Prepare parameters for MDB2->buildManipSQL()
            $fields = array ($macaddrcol, $vendorcol, $desccol);
            $where = "$macaddrcol = '$macaddr'";

            $sql = $this->_db->buildManipSQL($this->_dbOptions['tablename'],
                                             $fields, MDB2_AUTOQUERY_SELECT,
                                             $where);

            $query = $this->_db->prepare($sql);
            $result = $query->execute();

            if ( (MDB2::isError($result)) || ($result->numRows() == 0) ) {
                return false;
            }

            $macInfo = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

            if ($getDescription) {
                return array($vendorcol => $macInfo[$vendorcol],
                             $desccol => $macInfo[$desccol]);
            }
            else {
                return $macInfo[$vendorcol];
            }
        }
        else {
            return false;
        }
    } /* end method findVendor */

} /* end class Net_MAC */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
?>
