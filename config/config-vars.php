<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@crm-ctt.com)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * config-vars.php: this script should only contain variable declarations
 * IT SHOULD CONTAIN NO PROGRAM LOGIC WHATSOVER AND IT MAY NOT GENERATE
 * ANY OUTPUT!
 *
 * Check http://www.interleave.nl/en/ for more information
 **********************************************************************

 This is not the Interleave config file. There is no need to adjust these
 settings for normal use. Only alter variables for debug use.

 Important:

 ->  For all file logging you need to have a file called "qlist.txt" in your  <-
 ->  Interleave installation directory which is writeable by the user under   <-
 ->  which your webserver runs.                                               <-

-->  Please be aware that all debug functions compromise security.            <--

*/

// Some PHP initial settings
// (mostly not allowed to change but trying won't hurt)

ini_set("magic_quotes_gpc","Off");							// Try to turn off magic quotes
ini_set("magic_quotes_runtime","Off");						// Try to turn off magic quotes runtime
error_reporting(E_ERROR);									// Try to set error reporting to errors only

define("CACHE", 1);											// Cache hits + the rest (e.g. *all* messaages) (quite useless)
define("INFO", 2);											// Informative, warnings and errors (e.g. all but CACHE)
define("WARNING", 3);										// Warnings and errors (access descisions are treated as warnings)
define("ERROR", 4);											// Only errors (start here)
define("DEBUG", 99);										// WARNING and ERROR messages and messages called with DEBUG log level (for development only)

// Easy debug options
$GLOBALS['logtext']						= false;			// CACHE, INFO, WARNING, ERROR or DEBUG (see above)
$GLOBALS['logrequests']					= false;			// Logs all requestst to querylog.txt
$GLOBALS['disable_all_cache']			= false;			// Disables all cache (truncates all cache on each load).
$GLOBALS['qlog_onscreen']				= false;			// Displays pop-up containing logtext log.
$GLOBALS['ShowTraceLink']				= false;			// Displays qlog trace link at end of page (same 25% slower as 'logtext').
$GLOBALS['log_slowest_query']			= false;			// Logs slowest SQL query of a page. logtext needs te be enabled.

// Advanced debug options
$GLOBALS['logqueries']					= false;			// Logs all queries (10% slower) - a file called querylog.txt must exist and it must be writable
$GLOBALS['DisplayREQUESTArray']			= false;			// Will display passwords; use for debug only!
$GLOBALS['DISABLEENTITYFORMCACHE']		= false;			// False means ENABLED! (superceded by disable_all_cache) - can also be enabled by setting DISABLEENTITYFORMCACHE.

// Security options

// To enable login try count (temporary block an IP after 10 failed attempts from the same IP) add 
// an empty *readable and writeable* file called "logins.txt" in the main installation directory 
// and set the variable below for notification.
$GLOBALS['LoginTryCountNotifyTo']		= false;			// replace with "example@my-server.com"

// Insane debug options
$GLOBALS['ShowFunctionTrace']			= false;			// Adds complete backtrace to each qlog/logtext call. Adds ~5000 loglines per page load.

// Constants
$GLOBALS['USE_EXTENDED_CACHE_WHAT']		= "all";			// This has become a constant. Do not change.
$GLOBALS['FormFinity']					= "Yes";			// This has become a constant. Do not change.
$GLOBALS['doctype']						= '<!DOCTYPE html>';
$GLOBALS['htmlopentag']					= '<html>';

// Database connection global overruide
$GLOBALS['CMD_CONN_OVRW']				= false;			// Set this to false to (always) use regular connections instead of persistent connections


$GLOBALS['AUTHOR']						= "Hidde Fennema";	// Daddy
if ($GLOBALS['Overrides']['ProductName'] == "" || strstr($GLOBALS['Overrides']['ProductName'], " ")) {
	$GLOBALS['PRODUCT']						= "Interleave";		// Product name
} else {
	$GLOBALS['PRODUCT'] = $GLOBALS['Overrides']['ProductName'];
}

// Table definitions (do not change)

$tb = array();

$tb[0]['etn']							= "entity";			// The name of main entity table
$tb[0]['etnr']							= 0;				// The no. of main entity table
$tb[0]['extn']							= "extrafields";	// The name of main entity extrafields table

$GLOBALS['etn']							= $tb[0]['etn'];	// Global entity table name
$GLOBALS['etnr']						= $tb[0]['etnr'];	// Global entity table no.
$GLOBALS['extn']						= $tb[0]['extn'];	// Global extrafields table name


// Global and current local scope variable name & size dump
// Use by calling " eval($GLOBALS['VAR_DUMP']); " anywhere in your code/script

$GLOBALS['VAR_DUMP']					= '
print "<hr /><h1>Start variable dump</h1>";
print "<table border=1>";
$tmp = get_defined_vars();

foreach ($tmp AS $var => $val) {
	if ($var != "GLOBALS") {
		$totsize = 0;
		$valtoshow = "";

		if (is_array($val)) {
			$totsize += array_size($val);
			$valtoshow = string_r($val);	

		} else {
			$totsize += strlen($val);
			$valtoshow = $val;
		
		}
		if (strlen($valtoshow) > 200) {
			$valtoshow= substr($valtoshow, 0, 200);
		}

		print "<tr><td>" . $var . "</td><td>" . $totsize . " bytes</td><td><pre>" . htme($valtoshow) . "</pre></td></tr>";
	}
}
print "<tr><td colspan=3>Global Vars</td></tr>";
foreach ($GLOBALS AS $var => $val) {
	if ($var != "GLOBALS") {
		$totsize = 0;
		$valtoshow = "";
		if (is_array($val)) {
			$totsize += array_size($val);
			$valtoshow = string_r($val);	

		} else {
			$totsize += strlen($val);
			$valtoshow = $val;
		
		}
		if (strlen($valtoshow) > 200) {
			$valtoshow= substr($valtoshow, 0, 200);
		}

		print "<tr><td>" . $var . "</td><td>" . $totsize . " bytes</td><td><pre>" . htme($valtoshow) . "</pre></td></tr>";
	}
}
print "</table>";
print "<hr />";
';




?>