<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
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
ob_start();


$session = $_REQUEST['session'];

if (isset($_REQUEST['logout'])){
  $session = $_COOKIE['session'];
  qlog(INFO, "Auth3::This user logged out");
  $actuser = ActiveUser($_REQUEST['session']);
  $GLOBALS['session_id'] = $_REQUEST['session'];
  $name= $actuser;
  $GLOBALS['username'] = $actuser;
  $GLOBALS['USERNAME'] = $actuser;
  $GLOBALS['USERID']   = GetUserID($actuser);


  SetAttribute("user", "LastLogout", date('U'), $GLOBALS['USERID']);


  RemoveLocks();
  setcookie("session","",time()-500, "", "", $_GLOBALS['SecureCookie'], ""); //unset cookie

  log_msg("Logoff " . $name,"");
  $sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "sessions WHERE temp='" . mres($session) . "'";
  mcq($sql,$db);
  
  // Hook:Maestrano
  // Logout from application
  $maestrano = MaestranoService::getInstance();
  if ($maestrano->isSsoEnabled()) {
    header("Location: " . $maestrano->getSsoLogoutUrl());
  } else {
    if ($_REQUEST['expire']) {
  		$timeout = GetSetting("timeout");
  		$_REQUEST['session'] = base64_encode($lang['signedoffdue1'] . "&nbsp;" . $timeout . "&nbsp;" . $lang['signedoffdue2']);
  		require("login.php");
  		exit;

    } else {
  	  do_language();
  	  $_REQUEST['session'] = base64_encode($lang['signedoff']);
  	  require("login.php");
  	  exit;
    }
  }

} elseif ($_REQUEST['username'] && $_REQUEST['password'] && !$_COOKIE['session']) {
  
	AuthenticateUser($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['silent']);
	InitUser();

    

	if ($_REQUEST['remember_username'] == "yes") {
		//Jeroen: wordt 1 week bewaard
		setcookie("namebla", $_REQUEST['username'], time()+7*24*60*60, "", "", $_GLOBALS['SecureCookie'], "");
	} else {
		setcookie("namebla", "", 0, "", "", $_GLOBALS['SecureCookie'], "");
	}
	if ($_REQUEST['remember_password'] == "yes") {
		//Jeroen: wordt 1 week bewaard
		setcookie("passwordbla", $_REQUEST['password'], time()+7*24*60*60, "", "", $_GLOBALS['SecureCookie'], "");
	} else {
		setcookie("passwordbla", "", 0, "", "", $_GLOBALS['SecureCookie'], "");
	}

	setcookie("disable_triggers", "", 0, "", "", $_GLOBALS['SecureCookie'], "");

	log_msg("Authenticate " . $_REQUEST['username'],"Authenticate " . $_REQUEST['username']);
	qlog(INFO, "Auth3::User " . $_REQUEST['username'] . " logged in");
	$GLOBALS['username'] = $_REQUEST['username'];
	$GLOBALS['USERNAME'] = $_REQUEST['username'];
	$GLOBALS['USERID']   = GetUserID($_REQUEST['username']);
	if ($GLOBALS['USE_EXTENDED_CACHE']) {
		$uri = urlencode($_SERVER['REQUEST_URI']);

		if (trim($uri) == "") {
			$uri = "index.php?";
		}
	PrintHTMLHeader();
	PrintUnauthenticatedHeaderJavascript();
	if ($GLOBALS['Overrides']['Logo'] != "") {
		$logo = "" . htme($GLOBALS['Overrides']['Logo']) . "";
	} else {
		$logo = "images/crm.gif";
	}
	?>
	<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen">
	<link rel="stylesheet" href="css/crm_dft.css" type="text/css">
		<script type="text/javascript">
		<!--
			document.write('<link href="css/pww.css" rel="stylesheet" type="text/css">');
			setTimeout('continueNow()',3000);
			function continueNow() {
				<?php
				if ($GLOBALS['UC']['USEDASHBOARDASENTRY'] == "y") {
					print "document.location = 'dashboard.php?tab=1';\n";
				} else {
					print "document.location = 'index.php';\n";
				}
				?>
			}
		//-->
		</script>
		<title>Interleave</title>
		</head><body><div><div>
		<div id="page2"><br>
		<img src='<?php echo $logo;?>' alt=''><br><br>
		<img src='images/movingbar.gif' style='border: 1px;' alt=''>
		<br><br>
		Interleave Business Process Management<br>
		Visit <a href='http://www.interleave.nl/'>www.interleave.nl</a> for more information.

		</div>
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<object id="UpdateCacheTablesObject" height="1" width="1" type="text/html" data="index.php?session=<?php echo $GLOBALS['session_id'];?>&amp;UpdateCacheTables=do&amp;urltogo=<?php echo $uri;?>"></object>
		<?php
	}
	?>

	<?php

	$logonmsg = GetSetting("Logon message");

	if (trim($logonmsg)<>"") {
		do_language();
		$logonmsg = $lang['sysmsg'] . ":\\n\\n" . $logonmsg;

		?>
			<script type="text/javascript">
			<!--
			alert("<?php echo $logonmsg;?>");
			//-->
			</script>
		<?php
	}
	ThingsToDoAtLogin();
	if ($GLOBALS['USE_EXTENDED_CACHE']) {
		EndHTML();
		exit;
	}
	$session = $md5str;
} elseif (($_REQUEST['session'] || $session || $_COOKIE['session']) && !isset($_REQUEST['NoSession']) && !isset($_REQUEST['AutomaticPublicLogin'])) {
		//check if code is correct and if time correct and not older than 30 minutes
		if (!$_REQUEST['session'] && $session != "") {
			$_REQUEST['session'] = $session;
		} elseif ($_COOKIE['session']) {
			$_REQUEST['session'] = $_COOKIE['session'];
		}
	    $actuser = ActiveUser($_REQUEST['session']);
		$GLOBALS['session_id'] = $_REQUEST['session'];
		$name= $actuser;
		$GLOBALS['username'] = $actuser;
		$GLOBALS['USERNAME'] = $actuser;
		$GLOBALS['USERID']   = GetUserID($actuser);
		if (!is_numeric($GLOBALS['USERID'])) {
			qlog(ERROR, "Could not determine user id. Quitting.");
//			PrintAD("Your session is not or no longer valid.");

			header("Location: login.php?NoSession=1");
			EndHTML();
			exit;
		}
		qlog(DEBUG, "User id found and set to " . $GLOBALS['USERID']);
		
		setcookie("session", $session, 0, "", "", $_GLOBALS['SecureCookie'], "");
	

		//log_msg("","$name");
} else {

	$tmp = GetSetting("AutoLoginUserID");
	$uriString = GetSetting("AutoLoginURIString");
	if (is_numeric($tmp) && (($uriString != "" && stristr($_SERVER['HTTP_HOST'], $uriString) || $uriString == ""))) {

		if (AuthenticateUser3(GetUserLoginNameByID($tmp), "", true, true) && !is_administrator($tmp)) {
			GenerateSecret(GetUserLoginNameByID($tmp));
			$GLOBALS['USERID'] = $tmp;
			$actuser = GetUserLoginNameByID($tmp);
			$GLOBALS['session_id'] = $_REQUEST['session'];
			$name = $actuser;
			$GLOBALS['username'] = $actuser;
			$GLOBALS['USERNAME'] = $actuser;

			if (!is_numeric($GLOBALS['USERID'])) {
				qlog(ERROR, "Could not determine user id. Quitting.");
				//PrintAD("Your session is not or no longer valid.");
				header("Location: login.php");
				EndHTML();
				exit;
			}
			qlog(DEBUG, "User id found and set to " . $GLOBALS['USERID']);
			
			InitUser($actuser);
			SetAttribute("user", "LastActivity", date('U'), $GLOBALS['USERID']);
			if (!isset($_REQUEST['AutomaticPublicLogin'])) {
				header("Location: ?AutomaticPublicLogin");
			} 
			exit;
		} else {
			do_language();

			qlog(INFO, "Redirecting user to login screen. Couldn't authenticate auto-login user id.");
			//print "Redirecting user to login screen. Couldn't authenticate auto-login user id.";
			
			require("login.php");
			exit;
		}
	} else {

		do_language();
		qlog(INFO, "Redirecting user to login screen. No specials.");
		qlog(INFO, "Session is " . $_REQUEST['session'] . " (request) and " . $_COOKIE['session'] . " (cookie)");
		require("login.php");
		exit;
	}
}
function uselogger($comment,$dummy_extra_not_used){
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
	qlog(INFO, ">>>>> OLD USELOGGER FUNCTION IN USE");
	log_msg($comment);
	return(true);
}


if ($_REQUEST['CheckForValidSession']) {
	$seconds_ago = CheckLastUserActivity($GLOBALS['USERID']);
	qlog(INFO, "Last activity: " . $seconds_ago . " seconds ago");
	$timeout_in_seconds = $GLOBALS['timeout'] * 60;
	if ($timeout_in_seconds < ($seconds_ago + 20)) {
		?>
		<script type="text/javascript">
		<!--
			document.location = 'index.php?logout=1&expire=1';
		//-->
		</script>
		<?php

	}
}
?>