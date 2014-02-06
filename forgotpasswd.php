<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * forgotpassword.php - handles users who lost their password
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
//print_r($_REQUEST);
require_once("initiate.php");

$requested_page	= str_replace("|", "&", $_REQUEST['url_to_go_to']);
$requested_page	= str_replace("%7C", "&", $requested_page);

$lang['cookiewarning'] = str_replace("\n","",$lang['cookiewarning']);

if ($GLOBALS['Overrides']['Logo'] != "") {
	$logo = "" . htme($GLOBALS['Overrides']['Logo']) . "";
} else {
	$logo = "images/crm.gif";
}

if ($GLOBALS['passwdreset_errormsg'] == "") $GLOBALS['passwdreset_errormsg'] = "The information you submitted is not valid (either username, e-mail or both).";
if ($GLOBALS['passwdreset_intro'] == "") $GLOBALS['passwdreset_intro'] = "Use this form to request a new password. Make sure your username is correct and the e-mail address you enter in this form is exactly the same e-mail address Interleave uses to send you e-mails.";

if ($GLOBALS['passwdreset_mailbody'] == "") $GLOBALS['passwdreset_mailbody'] = "Your " . $GLOBALS['PRODUCT'] . " password was re-set to @@@NEWPASS@@@. You can use this password only once; as soon as you log in you will need to change it.";
if ($GLOBALS['passwdreset_mailsubject'] == "") $GLOBALS['passwdreset_mailsubject'] = $GLOBALS['PRODUCT'] . " password reset";
if ($GLOBALS['passwdreset_reposmsg'] == "") $GLOBALS['passwdreset_reposmsg'] = "<strong>Warning: </strong> This procedure will only change your password in one repository (the one you choose below). Other repositories will not be changed.";

if ($GLOBALS['passwdreset_backlink'] == "") $GLOBALS['passwdreset_backlink'] = "Back to log in page";
if ($GLOBALS['passwdreset_usernametext'] == "") $GLOBALS['passwdreset_usernametext'] = "Username";
if ($GLOBALS['passwdreset_emailtext'] == "") $GLOBALS['passwdreset_emailtext'] = "E-mail address";
if ($GLOBALS['passwdreset_submitbuttontext'] == "") $GLOBALS['passwdreset_submitbuttontext'] = "Request a new password";
if ($GLOBALS['passwdreset_title'] == "") $GLOBALS['passwdreset_title'] = "Password reset";

if ($GLOBALS['passwdreset_successmsg'] == "") $GLOBALS['passwdreset_successmsg'] = "A new password was e-mailed to you.";

PrintHTMLHeader();
print "<title>Request password</title>";
PrintUnauthenticatedHeaderJavascript();
print "</head>";
print  '<body id="main"><div id="WaitImageDiv"></div>' . $GLOBALS['LogonPageHeader'] . '<div id="page"><h1 id="title">' . $GLOBALS['PRODUCT'] . '</h1><h2 id="subtitle">' . $GLOBALS['passwdreset_title'] . '</h2>';
print '<div>';

if (isset($_POST['repositoryToLoginTo']) && $_POST['fg_username'] && $_POST['fg_email']) {
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/class.phpmailer.php");
	SwitchToRepos($_POST['repositoryToLoginTo']);
	$userid = GetUserID($_REQUEST['fg_username']);
	$email  = GetUserEmail($userid);


	if (GetSetting('ALLOWLOGONPAGEPASSCHANGE') == "Yes") {
		if (($email != "") && (strtolower(trim($email)) == strtolower(trim($_REQUEST['fg_email'])))) {
			$newpass = ResetPassword($userid);
			$html = str_replace("@@@NEWPASS@@@", $newpass, $GLOBALS['passwdreset_mailbody']);

			SimpleMail($GLOBALS['admemail'], $email,$GLOBALS['passwdreset_mailsubject'], $html, false);
			log_msg("WARNING: User [" . $_REQUEST['fg_username'] . "] id [" . $userid . "] submitted email [" . $email . "] requested a new password (successful)");
			if ($GLOBALS['Overrides']['LoginPage'] != "") {

				header("Location: " . $GLOBALS['Overrides']['LoginPage'] . "?msg=" . urlencode($GLOBALS['passwdreset_successmsg']) . "&url_to_go_to=" . $_REQUEST['url_to_go_to']);
				EndHTML();
				exit;
			 } else {
				print "<p>" . $GLOBALS['passwdreset_successmsg'] . "</p>";
			}
			$done = true;
		} else {
			log_msg("WARNING: User [" . $_REQUEST['fg_username'] . "] id [" . $userid . "] submitted email [" . $_REQUEST['fg_email'] . "] requested a new password (UNsuccessful)");

			if ($GLOBALS['Overrides']['LoginPage'] != "") {
				header("Location: " . $GLOBALS['Overrides']['LoginPage'] . "?msg=" . urlencode($GLOBALS['passwdreset_errormsg']) . "&url_to_go_to=" . $_REQUEST['url_to_go_to']);
				EndHTML();
				exit;
			 } else {
				print "<p><span class='noway'>" . $GLOBALS['passwdreset_errormsg'] . "</span><br><br></p>";
			}
		}
	} else {
		log_msg("WARNING: User [" . $_REQUEST['fg_username'] . "] id [" . $userid . "] submitted email [" . $email . "] requested a new password (UNsuccessful, is DISABLED)");

		if ($GLOBALS['Overrides']['LoginPage'] != "") {
			header("Location: " . $GLOBALS['Overrides']['LoginPage'] . "?msg=" . urlencode("This function was disabled by your " . $GLOBALS['PRODUCT'] . " administrator (for this repository).") . "&url_to_go_to=" . $_REQUEST['url_to_go_to']);
			EndHTML();
			exit;
		 } else {
			print "<p><span class='noway'>This function was disabled by your " . $GLOBALS['PRODUCT'] . " administrator (for this repository).</span><br><br>";
		}
			$done = true;
	}

} else {
//DA($_REQUEST);
}

$counted = 0;
for ($i=0;$i<257;$i++) {
	if ($host[$i] != "") $counted++;
	$lastrepos = $i;
}

if (!$done) {

	print '<div id="forgotpassword_headertext"><p>' . $GLOBALS['passwdreset_intro'] . '</p></div>';
		if ($counted > 1) {
			print '<div id="forgotpassword_headertext_warning"><p>';
			print $GLOBALS['passwdreset_reposmsg'] . '</p></div>';
		}

	print html_compress('
	<form id="req_pass" method="post" action="forgotpasswd.php"><div class="showinline">
	<table width="100%" class="forgotpassword-table"><tr>
			<td>
				' . $GLOBALS['passwdreset_usernametext'] . '
			</td>
			<td>
				<input type="text" name="fg_username" id="JS_fg_username">
			</td>
		</tr>
		<tr>
			<td>
				' . $GLOBALS['passwdreset_emailtext'] . '
			</td>
			<td>
				<input type="text" name="fg_email" id="JS_fg_email">
			</td>
		</tr>');

	
		if ($counted > 1) {
		print html_compress('
		<tr>
			<td>
				Repository
			</td>
			<td>
				' . PrintReposOptions() . '
			</td>
		</tr>');
	
		} else {
			$ins = '<input type="hidden" name="repositoryToLoginTo" id="JS_repository" value="' . $i . '">'; 
		}
		print '<tr><td colspan="2"><br>' . $ins . '<input id="JS_fg_submitbutton" type="submit" name="knop" value="' . $GLOBALS['passwdreset_submitbuttontext'] . '"></td></tr>';

	print '</table></div></form></div>';

	
}
print "<div id=\"forgotpasswordbacktologinpagelink\"><p><a href='index.php?logout=1'>" . $GLOBALS['passwdreset_backlink'] . "</a></p></div>";
endHTML();
?>