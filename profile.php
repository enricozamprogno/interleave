<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file does several things :)
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once("initiate.php");
$GLOBALS['CURFUNC'] = "EditProfile::";

$tab = "99";

ShowHeaders();

AddBreadCrum("Profile");

if (strtoupper($GLOBALS['LetUserEditOwnProfile'])<>"YES" && !$_REQUEST['passonly']) {
	$legend = "<img src='images/error.gif' alt=''>";
	PrintAD("Your account doesn't allow this.");
	EndHTML();
} else {


	$sql = "SELECT id,name,EMAIL,RECEIVEDAILYMAIL,FULLNAME,RECEIVEALLOWNERUPDATES,RECEIVEALLASSIGNEEUPDATES,EMAILCREDENTIALS FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE name NOT LIKE 'deleted_user_%'";
	$result = mcq($sql,$db);

	$row = mysql_fetch_array($result);



	if ($_REQUEST['formsub']) {
		if ($_REQUEST['email_user'] && $_REQUEST['email_password'] && $_REQUEST['email_host']) {
				$personalpops = array();
				$personalpops[0] = array();
				$personalpops[0]['popuser'] = $_REQUEST['email_user'];
				$personalpops[0]['poppass'] = $_REQUEST['email_password'];
				$personalpops[0]['pophost'] = $_REQUEST['email_host'];
				$que_ins .= " EMAILCREDENTIALS='" . serialize($personalpops) . "', ";
		}
		if ($_REQUEST['delete_email']) {
				$que_ins .= " EMAILCREDENTIALS='', ";
		}

		if ($_REQUEST['userfullname'] && $_REQUEST['userfullname'] <>"" && $_REQUEST['userfullname'] != GetUserName($GLOBALS['USERID'])) {
				$que_ins .= " FULLNAME='" . mres($_REQUEST['userfullname']) . "',";
				unset($GLOBALS['PageCache']['UserNames']);
		}
		if ($useremail && $useremail<>"" && $useremail<>GetUserEmail($GLOBALS['USERID'])) {
				$que_ins .= " EMAIL='" . mres($useremail) . "',";
		}

		unset($GLOBALS['PageCache']['useremails']);

		$sql = "SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND password=PASSWORD('" . mres($_REQUEST['old_pwd']) . "')";
		$result = mcq($sql,$db);
		$result = mysql_fetch_array($result);

		$cur = GetAttribute("user", "DateFormat", $GLOBALS['USERID']);
		$posted = $_REQUEST['DateFormat'];
		if ($cur != $posted) {
			SetAttribute("user", "DateFormat", $posted, $GLOBALS['USERID']);
		}

		$list = GetTriggersWhichCanBeSwitchedOffByUsers();
		foreach ($list AS $posted) {
			if ($_REQUEST['EnableEmailTrigger' . $posted['tid']] != "") {
				$cur = GetAttribute("user", "EnableEmailTrigger" . $trigger['tid'], $GLOBALS['USERID']);
				if ($cur != $_REQUEST['EnableEmailTrigger' . $posted['tid']]) {
					SetAttribute("user", 'EnableEmailTrigger' . $posted['tid'], $_REQUEST['EnableEmailTrigger' . $posted['tid']], $GLOBALS['USERID'], array("Yes", "No"));
				}
			}
		}



		if ($_REQUEST['new_pwd1'] != "") {
				if ($_REQUEST['new_pwd1'] == $_REQUEST['old_pwd']) {
					print "<div class=\"noway\"  id=\"JS_same_password\">Same password! Password not saved.</div>";
				} elseif (($_REQUEST['new_pwd1'] == $_REQUEST['new_pwd2']) && $result['id']) {

					$que_ins = " password=PASSWORD('" . mres($_REQUEST['new_pwd1']) . "'),LASTPASSCHANGE=NOW(),FORCEPASSCHANGE='n',";
					$passchange = true;
				} else {
					print "<table><td><td><img src='images/error.gif' alt=''>&nbsp;<strong>Password mismatch!<strong> - password not  saved.</td></tr></table>";
				}
		} else {
			// print "No pass received";
		}
		if ($GLOBALS['ENABLEOPENIDAUTH'] == "Yes") {
			$bla = ", openidurl='" . mres(FixOpenIDURL($_REQUEST['openidurl'])) . "'";
		} else {
			unset($bla);
		}
			if (!ValidateEmail($_REQUEST['useremail'])) {
				$_REQUEST['useremail'] = GetUserEmail($GLOBALS['USERID']);
			}


			$upd_query = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET " . $que_ins . " id='" . mres($GLOBALS['USERID']) . "'" . $bla . ", EMAIL='" . mres($_REQUEST['useremail']) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'";
			mcq($upd_query,$db);
			$GLOBALS['CURFUNC'] = "EditProfile::";
			qlog(INFO, "Profile updated (" . $GLOBALS['USERNAME'] . ")");
			$GLOBALS['CURFUNC'] = "";

			$fields = GetExtraUserFields();
			foreach ($fields AS $field) {
				if (isset($_REQUEST['EFID' . $field['id']]) && GetAttribute("extrafield", "UserIsAllowedToEditExtraField", $field['id']) == "Yes") {
					$curval = GetExtraFieldValue($GLOBALS['USERID'], $field['id'], false, true, false);
					if ($curval != $_REQUEST['EFID' . $field['id']]) {
						if (is_array($_REQUEST['EFID' . $field['id']])) {
							$tmp = array();
							foreach($_REQUEST['EFID' . $field['id']] AS $row) {
								if ($row <> "") {
									array_push($tmp, base64_encode($row));
								}
							}
							$_REQUEST['EFID' . $field['id']] = serialize($tmp);
						}
						SetExtraFieldValueSimple($field['id'], $GLOBALS['USERID'], $_REQUEST['EFID' . $field['id']]);
						journal($GLOBALS['USERID'], "Field EFID" . $field['id'] . " updated from [" . $curval . "] to [" . $_REQUEST['EFID' . $field['id']] . "]", "user");
					}
				}
			}
			ClearAccessCache('','e',$GLOBALS['USERID']);
			ClearAccessCache('','c',$GLOBALS['USERID']);
			ExpireFormCacheByUser($GLOBALS['USERID']);
			$journal_add .= "\nCache of this user cleared";
			uselogger("User " . GetUserName($GLOBALS['USERID']) . " just updated his/her profile","");

			print '<div id="profile-updated-message">Your profile has been updated.</div>';

			if ($_REQUEST['passonly']) {
				?>
					<script type="text/javascript">
					<!--
						document.location='dashboard.php';
					//-->
					</script>
				<?php
			}
			
	}


	if ($passchange && $_REQUEST['alteronallrepos'] == "yes") {

		$tmp = $GLOBALS['repository_nr'];
		require($GLOBALS['CONFIGFILE']);
		for ($t=0;$t<64;$t++) {
			if ($host[$t] != "" && SwitchToRepos($t)) {

				$row = db_GetRow("SELECT * FROM " . $table_prefix[$t] . "loginusers WHERE name='" . $GLOBALS['USERNAME'] . "' AND password=PASSWORD('" . mres($_REQUEST['old_pwd']) . "')");
//				print "SELECT * FROM " . $table_prefix[$t] . "loginusers WHERE name='" . $GLOBALS['USERNAME'] . "' AND password=PASSWORD('" . mres($_REQUEST['old_pwd']) . "')";

				if ($row['name'] == $GLOBALS['USERNAME']) {
					$query = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET password=PASSWORD('" . mres($_REQUEST['new_pwd1']) . "'), LASTPASSCHANGE=NOW(), FORCEPASSCHANGE='n' WHERE name='" . $GLOBALS['USERNAME'] . "' AND password=PASSWORD('" . mres($_REQUEST['old_pwd']) . "')";
					mcq($query, $db);
					print "Password (also) changed in repository \"" . $GLOBALS['title'] . "\"<br>";
				} else {
					print "Password NOT changed in repository \"" . $GLOBALS['title'] . "\" (current password not equal)<br>";
				}
				unset($row);
			}
		}
		SwitchToRepos($tmp);



	}

	if ($passchange) {
		?>
		<script>
			document.location = 'profile.php';
		</script>
		<?php
	}


	$usrrow = GetUserRow($GLOBALS['USERID']);
	
	


	print "<form id='profile' name='profile' method='post' action=''>";


	print "<h1>" . ($lang['username']) . "/" . strtolower($lang['password']) . "</h1>";
	

	if (!$_REQUEST['passonly']) {
		print "<table class=\"interleave-table-space\">";	
		print "<tr><td>" . $lang['name'] . "</td><td><input type='text' size='30' name='userfullname' id='JS_userfullname' value='" . htme(GetUserName($GLOBALS['USERID'])) . "'></td></tr>";
		print "<tr id=\"profile-email\"><td>E-mail</td><td><input type='text' size='40' name='useremail' id='JS_useremail' value='" . htme(GetUserEmail($GLOBALS['USERID'])) . "' onblur=\"CheckEmailLocal('JS_useremail');\"></td></tr>";
		
		$tmp = GetAttributeAllowedOptions('user', 'DateFormat');
		$cur = GetAttribute("user", "DateFormat", $GLOBALS['USERID']);

		print "<tr><td>" . $lang['dateformat'] . "</td><td><select name='DateFormat'>";
		foreach ($tmp AS $df) {
			$sel = "";
			if ($df == $cur) $sel = "selected='selected'";
			
			print "<option value='" . htme($df) . "' " . $sel . ">" . htme($df) . "</option>";
		}
		print "</select>";
		
		
		
		print "</td></tr>"; 
		print "<tr><td colspan='2'><strong>" . $lang['edit'] . " " . strtolower($lang['password']) . "</strong></td></tr>";
	} else {
		if (isset($_REQUEST['reset'])) { // && is_administrator()
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET LASTPASSCHANGE=NOW() WHERE id='" . mres($GLOBALS['USERID']) . "'", $db);
			print "<h1>OK, reset.</h1>";
		}
		print "<h2>" . $lang['mustchangepwd'] . ".<input type='hidden' name='passonly' value='true'></h2>";
		print "<table class=\"interleave-table-space\">";
	}
	
	
	print "<tr><td>" . $lang['currentpassword'] . "</td><td><input type='password' name='old_pwd' value=''></td></tr>";
	print "<tr><td>" . $lang['newpassword'] . "</td><td><input type='password' id='pw1' name='new_pwd1' value='' onkeyup=\"InlineCheckPasswordStrength('pw1');\"></td></tr>";
	print "<tr><td>" . $lang['newpasswordconfirm'] . "</td><td><input type='password' id='pw2' name='new_pwd2' value='' onkeyup=\"InlineCheckPasswordStrength('pw2');\"></td></tr>";
	include($GLOBALS['CONFIGFILE']);
	if (count($host) > 1) {
		print "<tr><td>" . $lang['chgpwdallrep'] . "</td><td><input type='checkbox' checked name='alteronallrepos' value='yes'> " . $lang['chgpwdallrepwarning'] . "</td></tr>";
	}
	
	$fields = GetExtraUserFields();
			
	foreach ($fields AS $field) {
		if (GetAttribute("extrafield", "UserIsAllowedToEditExtraField", $field['id']) == "Yes") {
			print "<tr><td>" . $field['name'] . "</td><td>";
			print GetSingleExtraFieldFormBox($GLOBALS['USERID'],$field['id'],false,false,false,false,false);
			print "</td></tr>";
		}
	}
	print "</table>";

	if (!$_REQUEST['passonly']) {

		$list = GetTriggersWhichCanBeSwitchedOffByUsers();
		
		if (count($list) > 0) {
			
			print "<h2>E-mail triggers</h2>";
			
			print "<table class=\"crm-nomax\">";
			print "<thead><tr><td>id</td><td>E-mail event description</td><td>Enabled</td></tr></thead>";
			foreach ($list AS $trigger) {
				print "<tr><td>" . $trigger['tid'] . "</td><td>" . htme($trigger['comment']) . "</td><td>";
				$setting = GetAttribute("user", "EnableEmailTrigger" . $trigger['tid'], $GLOBALS['USERID']);
				$ins = "";
				if ($setting == "No") $ins = "selected=\"selected\"";
				print "<select name=\"EnableEmailTrigger" . $trigger['tid'] . "\">";
				print "<option>Yes</option>";
				print "<option " . $ins . ">No</option>";
				print "</select>";
				print "</td></tr>";
			}
			print "</table>";
			print "<p>&nbsp;</p>";

		}
	}
	
	$calendars = GetAccessibleCalendars();
	/*
		$calObj['commentfield']   = $_REQUEST['cal_commentfield'];
		$calObj['summaryfield']   = $_REQUEST['cal_summaryfield'];
		$calObj['startdatefield'] = $_REQUEST['cal_startdatefield'];
		$calObj['starttimefield'] = $_REQUEST['cal_starttimefield'];
		$calObj['endtimefield']   = $_REQUEST['cal_endtimefield'];
		$calObj['userfield']      = $_REQUEST['cal_userfield'];
		$calObj['locationfield']  = $_REQUEST['cal_locationfield'];
		$calObj['resourcefield']  = $_REQUEST['cal_resourcefield'];
		$calObj['available']	  = $_REQUEST['cal_available'];
		$calObj['useselection']	  = $_REQUEST['cal_useselection'];
	*/

	if (is_array($calendars) && sizeof($calendars) > 0 && !$_REQUEST['passonly']) {
		$stp = false;
		$ical =  "<h2>iCal calendars</h2>";
		$ical .= "<table class=\"crm-nomax\">";
		$ical .= "<thead><tr><td>Description</td><td>URL</td></tr></thead>";
		foreach ($calendars AS $id => $calObj) {

			if ($calObj['available'] != "{{all}}" && $calObj['available'] != "" && !is_administrator()) {
				$acc = "ok";
				if (substr($calObj['available'], 0, 1) == "G") {
					$grouptobe = str_replace("G:", "", $calObj['available']);
					if ($GLOBALS['UC']['GROUP'] != $grouptobe) {
						// Permission denied
						$acc = "nok";
					}
				} elseif (substr($calObj['available'], 0, 1) == "U") {
					$usertobe = str_replace("U:", "", $calObj['available']);
					if ($GLOBALS['USERID'] != $usertobe) {
						// Permission denied
						$acc = "nok";
					}

				} else {
					$acc = "nok";
				}
			} else {
				$acc = "ok";
			}
			if ($acc == "ok") {
				$name = preg_replace("/\W/", "", str_replace(" ", "_", $calObj['description']));
				$ical .=  "<tr><td>" . htme($calObj['description']) . "</td><td><a href=\"" . $GLOBALS['BASEURL'] . "ical.php?repository=" . $GLOBALS['repository_nr'] . "&amp;calObj=" . $calObj['id'] . "&amp;Cal=" . htme($name) . "\">" . $GLOBALS['BASEURL'] . "ical.php?repository=" . $GLOBALS['repository_nr'] . "&amp;calObj=" . $calObj['id'] . "&amp;Cal=" . htme($name) . "</a></td></tr>";
				$stp = true;
			}
		}
		$ical .= "</table>";

	}
	if ($stp) {
		print $ical;
	}


	print "<br><br>&nbsp;&nbsp;<input type='hidden' name='formsub' value='1'><input type='button' onclick=\"CheckForm('profile');\" name='bla' value='" . $lang['save'] . "'><br><br>";
	print '<script type="text/javascript">function CheckPWD() {';
	print 'if (checkPasswordStrength("pw1") >= "' . $GLOBALS['MINIMUMPASSWORDSTRENGTH'] . '") {';
	print 'document.forms["profile"].submit();';
	print '} else {';
	print 'alert("'. $lang['passwarning'] . '");';
	print '}}</script>';

	print "</form>";

	EndHTML();
}


function printbox($msg)
{
		global $printbox_size,$legend;

		if (!$printbox_size) {
			$printbox_size = "70%";
		}

		print "<table border='0' style='width: " . $printbox_size . "%'><tr><td colspan='2'><fieldset>";
		if ($legend) {
			print "<legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;$legend</legend>";
		}
		print $msg . "</fieldset></td></tr></table><br>";

		unset($printbox_size);
		$legend = "";

} // end func

?>
