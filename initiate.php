<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Initiates database connection and checks security
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

if (!$GLOBALS['INITIATED']) {
	/* Set internal character encoding to UTF-8 */
	//mb_internal_encoding("UTF-8");

	$t = $_SERVER['SCRIPT_FILENAME'];
	$t = str_replace("\\","/", $t);
	$u = explode("/", $t);
	$path = "";
	for ($p=0;$p<sizeof($u)-1;$p++) {
		$path .= $u[$p] . "/";
	}


	
	$GLOBALS['PATHTOINTERLEAVE'] = $path;

	if ($_SERVER['HTTPS'] == "on") {
		$_GLOBALS['SecureCookie'] = 1;
	} else {
		$_GLOBALS['SecureCookie'] = 0;
	}

	if (!$GLOBALS['CONFIGFILE']) {
		$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
	}
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
	require_once($GLOBALS['CONFIGFILE']);
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");


	// This is done so we can use this var without htme later on. These vars should never contain characters which should be
	// encoded so this is only for security (if someone starts messing around)

	if (isset($_REQUEST['AjaxHandler']))	$_REQUEST['AjaxHandler'] = htme($_REQUEST['AjaxHandler']);
	if (isset($_REQUEST['e']))				$_REQUEST['e']			 = htme($_REQUEST['e']);
	if (isset($_REQUEST['eid']))			$_REQUEST['eid']		 = htme($_REQUEST['eid']);
	if (isset($_REQUEST['id']))				$_REQUEST['id']			 = htme($_REQUEST['id']);
	if (isset($_REQUEST['recordid']))		$_REQUEST['recordid']	 = htme($_REQUEST['recordid']);
	if (isset($_REQUEST['templateid']))		$_REQUEST['templateid']	 = htme($_REQUEST['templateid']);
	if (isset($_REQUEST['fileid']))			$_REQUEST['fileid']		 = htme($_REQUEST['fileid']);
	if (isset($_REQUEST['tid']))			$_REQUEST['tid']		 = htme($_REQUEST['tid']);



	if (!is_array($host)) {
		header("Location: install.php"); /* Redirect browser */

		// Terminate to be sure in case the browser didn't listen.
		exit;

	} else {
		
		
		if ($_REQUEST['repository'] == "" && $_REQUEST['repositoryToLoginTo'] == "") {
			$_REQUEST['repositoryToLoginTo'] = "0";
		}

		if (($_REQUEST['username'] != "") && ($_REQUEST['password'] != "") && ($_REQUEST['repository'] != "" || $_REQUEST['repositoryToLoginTo'] != "")) {

			if ($_REQUEST['repositoryToLoginTo'] != "") {
				$_REQUEST['repository'] = $_REQUEST['repositoryToLoginTo'];
			}
			
			if ($_REQUEST['repository'] != "") {


				unset($_COOKIE['repository']);
				$GLOBALS['REPOSITORY'] = $_REQUEST['repository'];
				setcookie('repository', $_REQUEST['repository']);

				if (DB_Connect($_REQUEST['repository'], false)) {
					SwitchToRepos($_REQUEST['repository']);

					if (AuthenticateUser($_REQUEST['username'], $_REQUEST['password'], false)) {
						setcookie('repository', $_REQUEST['repository']);
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
					
						
						InitUser();
						ThingsToDoAtLogin();
						if (!isset($_REQUEST['GetCSS']) && !isset($_REQUEST['GetJS']) && !isset($_REQUEST['GetjQueryUiPlacementJS']) && CheckIfPasswordMustBeChanged() && !$_REQUEST['passonly'] && !$GLOBALS['keeplocked']) { 
							header("Location: profile.php?1216991162&passonly=true");
							exit;
						}
						
						$logonmsg = GetSetting("Logon message");

						if (trim($logonmsg)<>"") {
							do_language();
							$logonmsg = $lang['sysmsg'] . ":\\n\\n" . $logonmsg;
							$GLOBALS['Overrides']['HeaderExtras'] .= '<script type="text/javascript">alert("' . ($logonmsg) . '");</script>';
						}
				
						
						
			

					} else {


						exit;
					}
				} else {
					print "Unable to connect to the database: repository " . $GLOBALS['REPOSITORY'] . ".\n";
					exit;
				}
			}
		} elseif ($_COOKIE['session'] != "") {
			require_once($GLOBALS['PATHTOINTERLEAVE'] . "getset.php");
		} elseif (substr($_SERVER['SCRIPT_NAME'], strlen($_SERVER['SCRIPT_NAME']) - 9, 9) != "login.php" && substr($_SERVER['SCRIPT_NAME'], strlen($_SERVER['SCRIPT_NAME']) - 16, 16) != "forgotpasswd.php") {
			if ($_REQUEST['url_to_go'] != "" || $_REQUEST['url_to_go_to']) {
				if ($_REQUEST['url_to_go'] == "") {
					$_REQUEST['url_to_go'] = $_REQUEST['url_to_go_to'];
				}
				$extraheader = "?url_to_go_to=" .  $_REQUEST['url_to_go'];
			}
			header("Location: login.php" . $extraheader);
			exit;
		} else {
			for ($i=0;$i<256;$i++) {
				if ($host[$i] && $user[$i] && $pass[$i]) {
					SwitchToRepos($i);
					break;
				}
			}
			$lang = do_language();

			// Auto-login?
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
						header("Location: login.php?InValidSession");
						EndHTML();
						exit;
					} 
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
					
					qlog(DEBUG, "User id found and set to " . $GLOBALS['USERID']);
					InitUser($actuser);
					SetAttribute("user", "LastActivity", date('U'), $GLOBALS['USERID']);
					ProcessTriggers("user_login", false, false, false, false);
					ThingsToDoAtLogin();
					if (!isset($_REQUEST['GetCSS']) && !isset($_REQUEST['GetJS']) && !isset($_REQUEST['GetjQueryUiPlacementJS']) && CheckIfPasswordMustBeChanged() && !$_REQUEST['passonly'] && !$GLOBALS['keeplocked']) {
						header("Location: profile.php?1216991162&passonly=true");
						exit;
					}
					if (!isset($_REQUEST['AutomaticPublicLogin'])) {
						header("Location: index.php");
					} 
					exit;
				} 
			}
			$noload = true;
		}
		
		if (!$noload) {
			require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/class.phpmailer.php");

			// Frequently downloaded files wich do *not* need the language set to be loaded
			// are specified here to save time. 

			if (!isset($_REQUEST['GetCSS']) && !isset($_REQUEST['GetjQueryUiPlacementJS']) && !strstr($_SERVER['SCRIPT_FILENAME'], "chat.php")) {
				$lang = do_language();
			}
			

			$GLOBALS['INITIATED'] = true;
			
			if ($GLOBALS['logrequests']) {
				$page_id = randomstring(32);
				$GLOBALS['page_id'] = $page_id;
				@$fp = fopen($GLOBALS['PATH_TO_BASE'] . "querylog.txt","a");
				@fputs($fp,$GLOBALS['USERNAME'] . ":" . $_SERVER['SCRIPT_NAME'] . " " . $_SERVER['QUERY_STRING'] . " id: " . $page_id . "\n");
				@fclose($fp);	
			}
		}
	}

	$mod_inc = GetAttribute("system", "NumberOfModuleToIncludeInAllPageloads", 2);
		
	if ($mod_inc > 0) {
		qlog(INFO, "Running module " . $mod_inc . "");
		RunModule($mod_inc);
	}


}

if (($_REQUEST['url_to_go'] != "" || $_REQUEST['url_to_go_to']) && is_numeric($GLOBALS['USERID'])) {
	if ($_REQUEST['url_to_go'] == "") {
		$_REQUEST['url_to_go'] = $_REQUEST['url_to_go_to'];
	}
	$requested_page	= str_replace("|", "&", $_REQUEST['url_to_go']);
	header("Location: " . $requested_page);
	print "	
	<script type=\"text/javascript\">
			<!--
	
		document.location = '" . jsencode($requested_page)  . "';
		//-->
		</script>";
}
?>