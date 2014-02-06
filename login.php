<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
* Licensed under the GNU GPL. For full terms see http://www.gnu.org/
*
* This is the login script.
*
* Check http://www.interleave.nl/ for more information
*
* BUILD / RELEASE :: 5.5.1.20121028
*
**********************************************************************
*/
if ($_REQUEST['username'] != "" && $_REQUEST['password'] != "") {
	require("initiate.php");
	require("index.php");
	exit;
} else {

	require_once("initiate.php");
	include($GLOBALS['CONFIGFILE']);
	do_language();
	$requested_page	= str_replace("|", "&", $_REQUEST['url_to_go_to']);
	$requested_page	= str_replace("%7C", "&", $requested_page);

	$lang['cookiewarning'] = str_replace("\n","",$lang['cookiewarning']);

	if ($GLOBALS['Overrides']['Logo'] != "") {
		$logo = "" . htme($GLOBALS['Overrides']['Logo']) . "";
	} else {
		$logo = "images/crm.gif";
	}


	PrintHTMLHeader();

	print "<title>";
	if ($GLOBALS['Overrides']['ProductName']) {
	    print htme($GLOBALS['Overrides']['ProductName']);
	} else {
	    print "Interleave Business Process Management";
	}
	print "</title>";

	PrintUnauthenticatedHeaderJavascript();

	print '<script type="text/javascript">';
	print '$(document).ready(function() { ';
	print ' document.getElementById("JS_username").focus();';
	print ' });';
	print '</script>';
	print '</head>';

	// Whether or not to print the Log On Screen
	$printLOS = true;



	if ($printLOS) {
		if ($GLOBALS['Overrides']['LoginPage'] != "") {

			header("Location: " . $GLOBALS['Overrides']['LoginPage'] . "?msg=" . urlencode($GLOBALS['LogonPageServiceMessage']) . "&url_to_go_to=" . $_REQUEST['url_to_go_to']);
			EndHTML();
			exit;
		}
		
		print '<body onload="checkCooks();" id="main">';
		print '<div>';
		print '<div id="WaitImageDiv"></div>';
		print '<div id="LogonPageHeader">' . $GLOBALS['LogonPageHeader'] . '</div>';
		print '<div id="page"><h1 id="title">' . htme($GLOBALS['title']) . '</h1>';
		print '<h2 id="subtitle">';
		if (strlen($GLOBALS['SUBTITLE']) > 0) {
			print ($GLOBALS['SUBTITLE']);
		} else {
			print "Interleave Business rocess Management";
		}
		print '</h2>';
		if ($GLOBALS['LogonPageServiceMessage']) {
			DisplayInlineLoginForm("<span class=\"logonpageservicemessage\">" . $GLOBALS['LogonPageServiceMessage'] . "</span>", $d, $requested_page);
		} else {
			DisplayInlineLoginForm($lang['pleaseenter'] . "", $d, $requested_page);
		}
		print '<div id="LogonPageMessage">' . $GLOBALS['LogonPageMessage'] . '</div>';

		print '<div id="loginpagemessages"><p>Interleave is an on-line business process management &amp; workflow engine which can be used to make any business process available as an on-line application. Visit the <a href="http://www.interleave.nl">website</a> for more information.</p>';

		print '</div>';
		print '<div id="copythingy">';
		print '<a href="http://www.interleave.nl/"><img src="images/interleave_solo_bg_small.gif" height="25" width="25" alt="Interleave Logo Solo"></a>';
		print '<br><a href="forgotpasswd.php" class="arrow">Forgot your password?</a>  &copy; <a href="http://www.interleave.nl">Interleave</a> 2001-2012&nbsp;';
		print '</div></div></div></body>';
	}
}
print '</html>';