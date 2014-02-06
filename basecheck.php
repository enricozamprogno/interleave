<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This script does a simple base check of the database. It's meant to
 * be used to check if your Interleave repository is functioning pro-
 * perly using your systems management platform (e.g. nagios)
 *
 * This script will return the zero-character ("0") when all is fine. It
 * will return "1" if something happened. It will not tell what went wrong
 * because as this script can be ran by unauthenticated users, it should
 * not reveal sensitive information like error messages.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */


// Start outputbuffer to make sure error messages are not passed to viewer (he's not logged in, these messages are secret)
ob_start();

// Set configuration file
$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";

// Load configuration file, when available, else fail.
if (is_file($GLOBALS['CONFIGFILE'])) {
	require($GLOBALS['CONFIGFILE']);
	require("functions.php");
	// Try to connect to first repository which has a database hostname set
	$c = 0;
	foreach ($host AS $h) {
		if ($h != "") {
			// Load repository environment
			SwitchToRepos($c);
		}
		$c++;
	}
} else {
	print "1";
}

// Get printed stuff, if any
$tmp = ob_get_contents();

// Clear the output buffer
ob_end_clean();

// If this all happens without any error message, all is fine. 
if ($tmp == "") {
	print "0";
} else {
	// The outputbuffer contained something, which must be an error of some sort.
	// Errors are not displayed online because they can reveal information.
	print "1";
//	print $tmp;
}

?>