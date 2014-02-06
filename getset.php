<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file does several things :)
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */



if ($_GET['name'] && $_GET['password'] && $_GET['repository']) {
	unset($_COOKIE);
}
// Some input protection
if ($_REQUEST['e']) {
	if (!is_numeric($_REQUEST['e']) && $_REQUEST['e'] <> "_new_") {
		unset($_REQUEST['e']);
		unset($e);
	}
}
if ($_REQUEST['eid']) {
	if (!is_numeric($_REQUEST['eid']) && $_REQUEST['eid'] <> "_new_") {
		unset($_REQUEST['eid']);
		unset($eid);
	}
}

$GLOBALS['starttime'] = microtime_float();
if ($GLOBALS['DisplayREQUESTArray']) {
	print "Printing _REQUEST array (see config/config-vars.php)<pre>";
	print_r($_REQUEST);
	print "</pre>";
}





	// Add a warning when debug, text logging or query logging is enabled
	if ($GLOBALS['logqueries']) { $CRM_VERSION .= " - QLOG ENABLED"; }
	if ($GLOBALS['debug']) { $CRM_VERSION .= " - DEBUG ENABLED"; }
	if ($GLOBALS['logtext']) { $CRM_VERSION .= " - LOGTEXT ENABLED"; }
	$GLOBALS['webhost'] = getenv("HOSTNAME");
	// Extract last repository number and language setting from cookie
	// Don't do it if blank=1
	if (!isset($_REQUEST['blank']) && !$rss && !$_GET['rep'] && !isset($_GET['repository']) && !isset($_POST['repository'])) {
		$_REQUEST['repository_nr'] = $_COOKIE['repository'];
		$language_display = $_COOKIE['language_display'];
		qlog(INFO, "Fetched repository number from cookie (" . $_COOKIE['repository'] . ")");
		$repository = $_REQUEST['repository_nr'];
	} else {
		qlog(INFO, "Did NOT fetch repository number from cookie");
		if (isset($_GET['rep']) && !stristr($_SERVER['SCRIPT_NAME'],"rss.php")) {
			$_REQUEST['repository_nr'] = $_GET['rep'];
			$repository = $_REQUEST['rep'];
			if (!$repository) $repository = "0";
			qlog ("REP Found. Setting repository to " . $_GET['rep']);
		} elseif (isset($_GET['rep']) && stristr($_SERVER['SCRIPT_NAME'],"rss.php")) {
			$_REQUEST['repository_nr'] = $_GET['rep'];
			$repository = $_REQUEST['rep'];
			if (!$repository) $repository = "0";
			qlog(INFO, "REP Found. Setting repository to " . $_GET['rep']);
		} elseif ($_GET['repository'] || $_POST['repository']) {
			$_REQUEST['repository_nr'] = $_GET['repository'];
			if ($_REQUEST['repository_nr'] == "") {
				$_REQUEST['repository_nr'] = $_POST['repository'];
			}
			$repository = $_REQUEST['repository'];
			qlog(INFO, "REP Found. Setting repository to " . $_REQUEST['repository']);
		} else {
			$_REQUEST['repository_nr'] = $_GET['repository'];
			$repository = $_REQUEST['repository_nr'];
			qlog ("REPOSITORY Found. Setting repository to " . $_GET['repository']);
		}
	}
	setcookie('repository', $repository, 0, "", "", $_GLOBALS['SecureCookie'], "");

	$_REQUEST['repository_nr'] = $repository;

	$GLOBALS['ef_inline_edit'] = $_COOKIE['ef_inline_edit'];

// Catch reposnr var (given when the cron job comes along)
if ($_REQUEST['reposnr']) {
		$_REQUEST['repository_nr'] = $_GET['reposnr'];
}
if ($repository == "") {
	qlog(INFO, "No repository found, set it to first configured repository");

	foreach ($host AS $key => $value) {
		if (is_numeric($key) || $key == 0) {
			$_REQUEST['repository_nr']= $key;
			break;
		}
	}

	qlog(INFO, "Repository found: " . $_REQUEST['repository_nr']);
}

if (strlen($lang['CHARACTER-ENCODING'])>2) {
	qlog(INFO, "Character-encoding override in effect: " . $lang['CHARACTER-ENCODING']);
	$charset = $lang['CHARACTER-ENCODING'];
	$GLOBALS['CHARACTER-ENCODING'] = $lang['CHARACTER-ENCODING'];
} else {
	$charset = "ISO-8859-1";
	$GLOBALS['CHARACTER-ENCODING'] = "ISO-8859-1";
}

$GLOBALS['repository_nr'] = $_REQUEST['repository_nr'];
$GLOBALS['ORIGINAL_REPOSITORY'] = $_REQUEST['repository_nr'];

$GLOBALS['TBL_PREFIX'] = $table_prefix[$GLOBALS['ORIGINAL_REPOSITORY']];

$repository = $GLOBALS['ORIGINAL_REPOSITORY'];

qlog(INFO, "SET ORG REPOS: " . $GLOBALS['ORIGINAL_REPOSITORY']);
$_COOKIE['repository'] = $GLOBALS['ORIGINAL_REPOSITORY'];


if (($logqueries || $logtext)) {
		$mysql_query_counter = 0;
		$fp = @fopen("qlist.txt","a");
		@fputs($fp,"=============================================================================\n");
		@fputs($fp,$_SERVER['PHP_SELF'] . " " . date("d-m-Y H:i:s") . "s (" . fillout($_SERVER['QUERY_STRING'], 80,true) . ")\n");
		@fclose($fp);
}
qlog(INFO, "Connecting to repository " . $repository);
// If no TBL_PREFIX is found, it ought to be "CRM"

if ($GLOBALS['TBL_PREFIX']=="") {
	$GLOBALS['TBL_PREFIX']="CRM";
	$GLOBALS['FORCED_TBL']="1";
	qlog(WARNING, "WARNING - FORCED TABLE PREFIX TO CRM! Please set a table prefix for this repository dude!");
}

	$GLOBALS['ORIGINAL_REPOSITORY'] = $_REQUEST['repository_nr'];
	if (SwitchToRepos($_REQUEST['repository_nr']) == false) {
		for ($t=0;$t<65;$t++) {
			if ($host[$t] <> "") {
				$to_conn = $t;
				$_REQUEST['repository_nr'] = $t;
				continue;
			}
		}
		if (SwitchToRepos($_REQUEST['repository_nr']) == false) {
			qlog(ERROR, "ERROR Connecting to " . $_REQUEST['repository_nr']);

			setcookie('repository','', 0, "", "", $_GLOBALS['SecureCookie'], "");
			setcookie('mainpagequery','', 0, "", "", $_GLOBALS['SecureCookie'], "");


//			PrintHeaders();
			DisplayCSS();
			?>

			<table width='75%'>
				<tr>
					<td>
						<fieldset>
							<legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;
								<img src='images/error.gif' alt=''>
							</legend>
							<?php
									print "Tried to connect to: " . $_REQUEST['repository_nr'];
									?>
							An error occured. Interleave was unable to connect to the database as stored in your configuration or cookie.<br><br>
							This error could have been caused by:
								<ul>
									<li> You, or an admin, deleted the repository in which you are working </li>
									<li> The database-server (<?php echo $host[$_REQUEST['repository_nr']];?>) was not responding  </li>
									<li> The username or password configured in Interleave for contacting the database is incorrect </li>
									<li> The configuration file has been altered </li>
									<li> Your browser might not support cookies </li>
								</ul>
							Please contact your system administrator for more information. The database error message is:<br><br>
							<pre><?php print mysql_error(); ?></pre>
							<br>
							You can try to login again ignoring any cookies <a class='arrow' href='index.php?blank=1&repos=AA&rep=AA&CookieOverride=true'>here</a><br>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php
			$GLOBALS['ShowTraceLink'] = true;
			// DO NOT USE ENDHTML! (since there is no database connection this will make things crash)
			print "</body></html>";
			//EndHTML();
			exit;
		} else {
			$GLOBALS['main_repository_nr'] = "0";
		}
	} else {
		$GLOBALS['main_repository_nr'] = $_REQUEST['repository_nr'];
	}
$servername = $_SERVER['SERVER_NAME'];
// Collect settings
$sql= "SELECT setting, value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting<>'STASH'";
$result= mcq($sql,$db);
while ($resarr=mysql_fetch_array($result)){
		$$resarr['setting'] = $resarr['value'];
		$GLOBALS[$resarr['setting']] = $resarr['value'];
}
// Unserialize some data
$GLOBALS['PersonalTabs']				= unserialize($GLOBALS['PersonalTabs']);
usort($GLOBALS['PersonalTabs'], "SortPTArray");
$GLOBALS['TABCOLORS']					= unserialize($GLOBALS['TABCOLORS']);
$GLOBALS['UC']['CustomerListColumnsToShow']	= unserialize($GLOBALS['UC']['CustomerListColumnsToShow']);
$GLOBALS['TABSTOHIDE']					= unserialize($GLOBALS['TABSTOHIDE']);
$GLOBALS['SHORTLISTLAYOUT']				= unserialize($GLOBALS['SHORTLISTLAYOUT']);

if ($EnableRepositorySwitcherOverrule) {
	$EnableRepositorySwitcher = $EnableRepositorySwitcherOverrule;
	$GLOBALS['EnableRepositorySwitcher'] = $EnableRepositorySwitcherOverrule;
	qlog(INFO, "Overrule EnableRepositorySwitcher");
}
// Authenticate!
if (!stristr($_SERVER['PHP_SELF'],"rss.php") && (!stristr($_SERVER['SCRIPT_NAME'],"duedate-notify-cron.php")) && !stristr($_SERVER['PHP_SELF'],"duedate-notify-cron.php") && !$GLOBALS['PUBLISHING']) {

		if ($c_l == "1") {
			$sn = $_SERVER['argv'][0];
		} else {
			$sn = "blank";
		}
		$fn = str_replace($_SERVER['REQUEST_URI'],"",$_SERVER['SCRIPT_NAME']);
		if ((($sn == "crmlogger.php" || $sn == "./crmlogger.php" || strstr($sn,"/crmlogger.php"))) && $c_l=="1") {
		} else {
			require_once($GLOBALS['PATHTOINTERLEAVE'] . "auth3.inc.php");
		}
}

if ($GLOBALS['MAINTENANCE_MODE'] == "Yes" && !is_administrator()) {
	do_language();
	?>
	<script type="text/javascript">
	<!--
		document.write('<link href="css/crm_dft.css" rel="stylesheet" type="text/css">');
	//-->
	</script>

	<center>
	<table style='width: 100%; height: 100%;'><tr><td valign='center'><center>
	<img src='images/crm.gif' alt=''><br>

	<br>
	<?php
		print $lang['maintenancemodeison'] . ".";
	?>
	<br>
	<?php
		print "<br><a class='arrow' href='index.php?logout=1&blank=1'>" . $lang['logout'] . "</a>";
	?>
	</center></td></tr></table></center>
	<?php
	EndHTML();
	exit;
} elseif ($GLOBALS['MAINTENANCE_MODE'] == "Yes") {
	$GLOBALS['BODY_URGENTMESSAGE'] .= " <span class='noway'>Maintenance mode is enabled. Only administrators can log in!</span>";
}
// Catch version having the wrong database version
if ($GLOBALS['VERSION'] <> $GLOBALS['DBVERSION'] && (!stristr($_SERVER['PHP_SELF'],"upgrade.php"))) {
	IntermediateDatabaseUpgrade();
}
	$GLOBALS['CURFUNC'] = "InitValues::";
	$res = db_GetRow("SELECT templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatename='Default form' AND templatetype='TEMPLATE_HTML_FORM' AND username='Hidde Fennema'");
	$GLOBALS['DefaultForm'] = $res[0];
	qlog(INFO, "Default emergency form is " . $res[0]);
//	print "Formid set " . $res[0];


if (!is_numeric($GLOBALS['DefaultForm']) && (!stristr($_SERVER['SCRIPT_NAME'],"upgrade.php")) && !stristr($_SERVER['PHP_SELF'],"install.php")) {
	qlog(INFO, "Panic! Help! Default template not found. Cowardly quitting.");
	log_msg("ERROR: Default form template id could not be determined. This is fatal.");
	PrintAD("FATAL Error. Default form template id could not be determined.");
	EndHTML();
} else {
	// Dont load again, alreadu done at auth3.inc.php:170
//	InitUser();
	// Check if triggers are disabled
	if ($_COOKIE['disable_triggers'] == "y") {
		qlog(WARNING, "WARNING - Triggers are disabled for this session!");
	}
	// Remove expired entity locks
	RemoveExpiredLocks();
	// Remove field records with empty values
	RemoveEmptyFields();
	// Calculate the genuine session date (important!)
	CalculateSessionDate($GLOBALS['PRODUCT'],$GLOBALS['CRM_VERSION'],$GLOBALS['CRM_SHORTVERSION'],$GLOBALS['AUTHOR']);

	if ($GLOBALS['USE_EXTENDED_CACHE'] == "Yes") {
		$GLOBALS['USE_EXTENDED_CACHE'] = true;
		qlog(INFO, "USE_EXTENDED cache is enabled");
	} else {
		unset($GLOBALS['USE_EXTENDED_CACHE']);
		qlog(INFO, "USE_EXTENDED cache is disabled");
	}

	if ($GLOBALS['USE_EXTENDED_CACHE']) {
		if ((stristr($_SERVER['SCRIPT_NAME'],"edit.php")) || stristr($_SERVER['PHP_SELF'],"summary.php") || stristr($_SERVER['PHP_SELF'],"csv.php") || stristr($_SERVER['PHP_SELF'],"customers.php") || stristr($_SERVER['PHP_SELF'],"cust-insert.php") || stristr($_SERVER['PHP_SELF'],"index.php") || stristr($_SERVER['PHP_SELF'],"dump_to_disk.php") || stristr($_SERVER['PHP_SELF'],"management.php") || stristr($_SERVER['PHP_SELF'],"stats.php") ||  stristr($_SERVER['PHP_SELF'],"dashboard.php") || $_REQUEST['ShowEntityList']) {
			if (stristr($_SERVER['SCRIPT_NAME'],"edit.php") && $_REQUEST['e']) {
				if ($GLOBALS['EnableEntityRelations'] == "Yes") {
					$GLOBALS['USE_EXTENDED_CACHE_WHAT'] = "all";
				} else {
					$GLOBALS['USE_EXTENDED_CACHE_WHAT'] = "cust_only";
				}
			}
			// Load cache tables into memory (this is faster I hope)
			qlog(INFO, "Loading access cache arrays... " . $GLOBALS['USE_EXTENDED_CACHE_WHAT'] . " (EXTENDED_CACHE)");
			$GLOBALS['CheckedCustomerAccessArray'] = array();
			$GLOBALS['CheckedEntityAccessArray'] = array();
			if ($GLOBALS['USE_EXTENDED_CACHE_WHAT'] == "all") {
				$res = mcq("SELECT eidcid,type,result FROM " . $GLOBALS['TBL_PREFIX'] . "accesscache WHERE user='" . mres($GLOBALS['USERID']) . "'", $db);
				while ($row = mysql_fetch_array($res)) {
					if ($row['type'] == "c") {
						$GLOBALS['CheckedCustomerAccessArray'][$row['eidcid']] = $row['result'];
					} else {
						$GLOBALS['CheckedEntityAccessArray'][$row['eidcid']] = $row['result'];
					}
				}
			} elseif ($GLOBALS['USE_EXTENDED_CACHE_WHAT'] == "cust_only") {
				$res = mcq("SELECT eidcid,type,result FROM " . $GLOBALS['TBL_PREFIX'] . "accesscache WHERE user='" . mres($GLOBALS['USERID']) . "' AND type='c'", $db);
				while ($row = mysql_fetch_array($res)) {
					$GLOBALS['CheckedCustomerAccessArray'][$row['eidcid']] = $row['result'];
				}
			}
		} else {
			qlog(INFO, "NOT loading access cache arrays (not important for this page)");
		}
	} else {
		qlog(INFO, "Cache array usage is disabled! (USE_EXTENDED_CACHE)");
	}
	if ($_REQUEST['SFS'] && !$_REQUEST['AjaxHandler']) {
					qlog(INFO, "Going to full-screen...");
					$url = str_replace("SFS","SFSD",$_SERVER['REQUEST_URI']);
					?>
					<script type="text/javascript">
					<!--
						URL = '<?php echo $url;?>';
						day = new Date();
						id = day.getTime();
						eval("page" + id + " = window.open(URL, '" + id + "', 'titlebar=no,toolbar=0,location=0,width=1016,height=718,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1,screenX=0,screenY=0,left=0,top=0');");
						win = top;
						win.opener = top;
						win.close ();
					//-->
					</script>
							<?php
	} elseif ($_REQUEST['SFSD']) {
		qlog(INFO, "Maximizing window...");
		?>
		<script type="text/javascript">
		<!--
			window.moveTo(0,0);
			window.resizeTo(screen.width,screen.height);
		//-->
		</script>
		<?php
	}
	$st = explode("/", $_SERVER['SCRIPT_FILENAME']);
	//CheckForInstalledPatches($st[sizeof($st)-1]);
}
//FetchUserLimits();
//SynchroniseFailOverDatabase();
if ($GLOBALS['disable_all_cache'] || isset($_GET['reason']) || isset($_GET['reasons']) || $GLOBALS['SesMem']['ShowReasons']) {

	qlog(INFO, "ALL C A C H E TABLES TRUNCATED BECAUSE disable_all_cache IS TRUE or REASONS is enabled!");
	// DO NOT TRUNCATE CRMca che - IT'S THE STASH TABLE
	mcq("TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "accesscache", $db);
	mcq("TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "entityformcache", $db);
	mcq("TRUNCATE TABLE " . $GLOBALS['TBL_PREFIX'] . "publishedpagescache", $db);
	ExpireDashboardCache();
	DeleteExpiredTempFiles();
}
if (strlen($GLOBALS['SAFE_MODE'] > 0)) {
	qlog(INFO, "SAFE MODE enabled. Only selected admins can perform really dangerous actions");
	$GLOBALS['SAFE_MODE'] = explode(";", $GLOBALS['SAFE_MODE']);
}
if ($_COOKIE['online_development_mode'] == "y") {
	if (is_numeric($eid)) {
		ExpireFormCache($eid);
	} elseif (IsValidEID($e)) {
		ExpireFormCache($e);
	}
}
if ($GLOBALS['DISABLEENTITYFORMCACHE'] == "Yes") {
	$GLOBALS['DisableEntityFormCaching'] = true;
	qlog(INFO, "DISABLEENTITYFORMCAHCE is enabled; NOT CACHING ANY FORMS!");
} else {
	qlog(INFO, "DISABLEENTITYFORMCAHCE is disabled; forms will be cahced!");
}


$GLOBALS['TriggerDays'] = explode(",", $GLOBALS['TriggerDays']);
//if (!is_array($GLOBALS['TriggerDays'])) $GLOBALS['TriggerDays'] = array("Not configured! See TRIGGERDAYS setting");



setnewtime("", $_COOKIE['session']);


if (!isset($_REQUEST['GetCSS']) && !isset($_REQUEST['GetJS']) && !isset($_REQUEST['GetjQueryUiPlacementJS']) && CheckIfPasswordMustBeChanged() && !$_REQUEST['passonly'] && !$GLOBALS['keeplocked']) {
		?>
			<script type="text/javascript">
			<!--
				document.location = 'profile.php?1216991162&passonly=true';
			//-->
			</script>
		<?php
		header("Location: profile.php?1216991162&passonly=true");
		EndHTML();
		exit;
}

if (function_exists("apc_fetch")) {
    qlog(INFO, "APC support enabled");
	$GLOBALS['USE_APC'] = true;
} else {
    qlog(INFO, "No APC support, using database for cache");
	$GLOBALS['USE_APC'] = false;
}
// Process any triggers which are still waiting to be processed (shouldn't be any)
ProcessOldTodos();

?>