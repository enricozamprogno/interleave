<?php
/*
 *********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the Interleave updater - it should be run from your browser,
 * not from the command line.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
*/

// Disable the menu
$_REQUEST['nonavbar'] = 1;
// Set error reporting level
error_reporting(E_ERROR);
$GLOBALS['DONTPRINTERRORS'] = true;
// Limit restrictions
$custinsertmode=1;
if ($_SERVER['HTTP_HOST']) {
	$web = 1;
}
if ($web) {
	$web = 1;
	$EnableRepositorySwitcherOverrule="n";
} else {
	$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
	foreach ($argv AS $cmdlineargument) {
		if (substr($cmdlineargument,0,4) == "cfg=") {
				$cmdlineargument = str_replace("cfg=", "" , $cmdlineargument);
				if (is_file($cmdlineargument)) {
					$GLOBALS['CONFIGFILE'] = $cmdlineargument;
					print "Using config file " . $GLOBALS['CONFIGFILE'] . "\n";

					continue;
				} else {
					die("Config file declaration is not correct. Fatal.");
				}
		} 
	}

	include($GLOBALS['CONFIGFILE']);

	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
	print "Database upgrade procedure\n\nPlease proof your identity - log on to any of the following repositories as an administrator:\n\n";
	if ($argv[1]=="-help" || $argv[1]=="--help" || $argv[1]=="help" || $argv[1]=="-h") {
		print "\nUsage:\n";
		print "\t[no arguments]\t:Interactive\n";
		exit;
	}
	if ($argv[1]) {
		$repository = $argv[1];
	}
	if ($argv[2]) {
		$username = $argv[2];
	}
	if ($argv[3]) {
		$password = $argv[3];
	}
	if ($argv[4]) {
		$auto = $argv[4];
		$auto=1;
	}
	$GLOBALS['CMDLINE'] = true;
	if (!CommandlineLogin($username,$password,$repository)) {
		print "Exiting...";
		exit;
	} else {
		
	}
	print "Please wait ... \n\n";

	print $a;
	while (!$GLOBALS['end'] == true) {
		unset($host);
		unset($pass);
		unset($database);
		unset($slave);
		unset($table_prefix);
		include($GLOBALS['CONFIGFILE']);
		print ReturnReposList();
		CLMenu();
	}

	exit;
}
	require("initiate.php");
	ShowHeaders();
	print $GLOBALS['doctype'];
	print $GLOBALS['htmlopentag'];
	?>
	<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
	<?php
	print "<title>Interleave $version installation procedure</title>";
	PrintUnauthenticatedHeaderJavascript();
	?>
	<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen">
	<link rel="stylesheet" href="css/pww.css" type="text/css">
	<link rel="stylesheet" href="css/crm_dft.css" type="text/css">
	<title>Interleave Business Process Automation</title>
	</head>

	<body style="background-color: #ffffff; margin: 0; color: #333333;"><div>

	<div id="page">
		<h1 id='title'>
		Interleave <?php echo $version;?> database upgrade procedure
	</h1>
	<h2>Interleave Business Process Automation</h2>
<?php
MustBeAdmin();
?>

<?php


if ($_REQUEST['A193TO194']) {
	A193TO194();
} elseif ($_REQUEST['A194TO195'])	{
	A194TO195();
} elseif ($_REQUEST['A195TO196'])	{
	A195TO196();
} elseif ($_REQUEST['A196TO2'])		{
	A196TO2();
} elseif ($_REQUEST['A2TO21'])		{
	A2TO21();
} elseif ($_REQUEST['A21TO22'])		{
	A21TO22();
} elseif ($_REQUEST['A22TO23'])		{
	A22TO23();
} elseif ($_REQUEST['A23TO24'])		{
	A23TO24();
} elseif ($_REQUEST['A24TO241'])	{
	A24TO241();
} elseif ($_REQUEST['A241TO242'])	{
	A241TO242();
} elseif ($_REQUEST['A242TO243'])	{
	A242TO243();
} elseif ($_REQUEST['A243TO244'])	{
	A243TO244();
} elseif ($_REQUEST['A244TO245'])	{
	A244TO245();
} elseif ($_REQUEST['A245TO246'])	{
	A245TO246();
} elseif ($_REQUEST['A246TO25'])	{
	A246TO25();
} elseif ($_REQUEST['A250TO251'])	{
	A250TO251();
} elseif ($_REQUEST['A251TO252'])	{
	A251TO252();
} elseif ($_REQUEST['A252TO253'])	{
	A252TO253();
} elseif ($_REQUEST['A253TO260'])	{
	A253TO260();
} elseif ($_REQUEST['A260TO261'])	{
	A260TO261();
} elseif ($_REQUEST['A261TO262'])	{
	A261TO262();
} elseif ($_REQUEST['A262TO300'])	{
	A262TO300();
} elseif ($_REQUEST['A300TO310'])	{
	A300TO310();
} elseif ($_REQUEST['A310TO320'])	{
	A310TO320();
} elseif ($_REQUEST['A320TO330'])	{
	A320TO330();
} elseif ($_REQUEST['A330TO331'])	{
	A330TO331();
} elseif ($_REQUEST['A331TO332'])	{
	A331TO332();
} elseif ($_REQUEST['A332TO340'])	{
	A332TO340();
} elseif ($_REQUEST['A340TO341'])   {
	A340TO341();
} elseif ($_REQUEST['A341TO342'])   {
	A341TO342();
} elseif ($_REQUEST['A342TO343'])   {
	A342TO343();
} elseif ($_REQUEST['A343TO400'])   {
	A343TO400();
} elseif ($_REQUEST['A400TO401'])   {
	A400TO401();
} elseif ($_REQUEST['A401TO410'])   {
	A401TO410();
} elseif ($_REQUEST['A410TO420'])   {
	A410TO420();
} elseif ($_REQUEST['A420TO430'])   {
	A420TO430();
} elseif ($_REQUEST['A430TO50']) {
	A430TO50();
} elseif ($_REQUEST['A50TO501']) {
	A50TO501();
} elseif ($_REQUEST['A501TO510']) {
	A501TO510();
} elseif ($_REQUEST['A510TO511']) {
	A510TO511();
} elseif ($_REQUEST['A511TO520']) {
	A511TO520();
} elseif ($_REQUEST['A520TO530']) {
	A520TO530();
} elseif ($_REQUEST['A530TO531']) {
	A530TO531();
} elseif ($_REQUEST['A531TO532']) {
	A531TO532();
} elseif ($_REQUEST['A532TO540']) {
	A532TO540();
} elseif ($_REQUEST['A540TO541']) {
	A540TO541();
} elseif ($_REQUEST['A541TO542']) {
	A541TO542();
} elseif ($_REQUEST['A542TO550']) {
	A542TO550();
} elseif ($_REQUEST['A550TO5501']) {
	A550TO5501();
} elseif ($_REQUEST['AutoUpgrade']) {
	$to_version = $_REQUEST['AutoUpgrade'];
	$queries = unserialize(@file_get_contents("queries.sql.ser"));
	$parent = $queries[$to_version]['parent'];
	$sqla = array();
	foreach ($queries[$to_version] AS $name => $query) {
		if (strstr($query, "PRFX@@@@@@@")) {
			$sqla[] = $query;
		}
	}
	Upgrade($parent, $to_version, $sqla);

} elseif ($_REQUEST['set_mm_on'] == "1") {
	$sqla = array();
	array_push($sqla, "UPDATE PRFX@@@@@@@settings SET value='Yes' WHERE setting='MAINTENANCE_MODE'");
	$GLOBALS['IGNORE_VERSION'] = true;
	Upgrade("%", "%", $sqla);
	?>
	<script type="text/javascript">
	<!--
		document.location='upgrade.php?mm_is_now_on';
	//-->
	</script>
	<?php
} elseif ($_REQUEST['set_mm_on'] == "2") {
	$sqla = array();
	array_push($sqla, "UPDATE PRFX@@@@@@@settings SET value='No' WHERE setting='MAINTENANCE_MODE'");
	$GLOBALS['IGNORE_VERSION'] = true;
	Upgrade("%", "%", $sqla);
	?>
	<script type="text/javascript">
	<!--
		document.location='upgrade.php?mm_is_now_off';
	//-->
	</script>
	<?php
} else	{
		print "<p>This procedure converts your Interleave database which is needed when you upgrade to a new version of Interleave. As this script alters your database(s), only use this script after you have made backups of your database and your web directory!</p>";

		print "<p>This procedure will only convert repositories which have the correct database version number. It will automatically upgrade any configured fail-over databases. This script will upgrade all databases (repositories) found in your config/config.inc.php.</p>";

		print "<p><a href='upgrade.php?set_mm_on=1'>Enable maintenance mode on all repositories</a> (required for upgrading)<br>";
		print "<a href='upgrade.php?set_mm_on=2'>Disable maintenance mode on all repositories</a> (required for use)</p>";


		print "<ul>";

		$queries = array_reverse(unserialize(@file_get_contents("queries.sql.ser")));

		foreach ($queries AS $version => $list) {
			if ($version != "snapshot") {
				print "<li>To upgrade all " . $list['parent'] . " databases to version " . $version . " click <a href='upgrade.php?AutoUpgrade=" . $version . "'>here</a>.<br></td>";
			}
		}

		print "<li>To upgrade all 5.5.0 databases to version 5.5.0.1 click <a href='upgrade.php?A550TO5501=1'>here</a>.<br></td>";
		print "<li>To upgrade all 5.4.2 databases to version 5.5.0 click <a href='upgrade.php?A542TO550=1'>here</a>.<br></td>";
		print "<li>To upgrade all 5.4.1 databases to version 5.4.2 click <a href='upgrade.php?A541TO542=1'>here</a>.<br></td>";

		
		if (isset($_REQUEST['ShowAllOptions'])) {
			print "<li>To upgrade all 5.4.0 databases to version 5.4.1 click <a href='upgrade.php?A540TO541=1'>here</a>.<br></td>";
			print "<li>To upgrade all 5.3.2 databases to version 5.4.0 click <a href='upgrade.php?A532TO540=1'>here</a>.</li>";
			print "<li>To upgrade all 5.3.1 databases to version 5.3.2 click <a href='upgrade.php?A531TO532=1'>here</a>.</li>";
			print "<li>To upgrade all 5.3.0 databases to version 5.3.1 click <a href='upgrade.php?A530TO531=1'>here</a>.</li>";
			print "<li>To upgrade all 5.2.0 databases to version 5.3.0 click <a href='upgrade.php?A520TO530=1'>here</a>.</li>";
			print "<li>To upgrade all 5.1.1 databases to version 5.2.0 click <a href='upgrade.php?A511TO520=1'>here</a>.</li>";
			print "<li>To upgrade all 5.1.0 databases to version 5.1.1 click <a href='upgrade.php?A510TO511=1'>here</a>.</li>";
			print "<li>To upgrade all 5.0.1 databases to version 5.1.0 click <a href='upgrade.php?A501TO510=1'>here</a>.</li>";
			print "<li>To upgrade all 5.0.0 databases to version 5.0.1 click <a href='upgrade.php?A50TO501=1'>here</a>.<br></td>";
			print "<li>To upgrade all 4.3.0 databases to version 5.0.0 click <a href='upgrade.php?A430TO50=1'>here</a>.<br></td>";
			print "<li>To upgrade all 4.2.0 databases to version 4.3.0 click <a href='upgrade.php?A420TO430=1'>here</a>.</li>";
			print "<li>To upgrade all 4.1.0 databases to version 4.2.0 click <a href='upgrade.php?A410TO420=1'>here</a>.</li>";
			print "<li>To upgrade all 4.0.1 databases to version 4.1.0 click <a href='upgrade.php?A401TO410=1'>here</a>.</li>";
			print "<li>To upgrade all 4.0.0 databases to version 4.0.1 click <a href='upgrade.php?A400TO401=1'>here</a>.</li>";
			print "<li>To upgrade all 3.4.3 databases to version 4.0.0 click <a href='upgrade.php?A343TO400=1'>here</a>.</li>";
			print "<li>To upgrade all 3.4.2 databases to version 3.4.3 click <a href='upgrade.php?A342TO343=1'>here</a>.</li>";
			print "<li>To upgrade all 3.4.1 databases to version 3.4.2 click <a href='upgrade.php?A341TO342=1'>here</a>.</li>";
			print "<li>To upgrade all 3.4.0 databases to version 3.4.1 click <a href='upgrade.php?A340TO341=1'>here</a>.</li>";
			print "<li>To upgrade all 3.3.2 databases to version 3.4.0 click <a href='upgrade.php?A332TO340=1'>here</a>.</li>";
			print "<li>To upgrade all 3.3.1 databases to version 3.3.2 click <a href='upgrade.php?A331TO332=1'>here</a>.</li>";
			print "<li>To upgrade all 3.3.0 databases to version 3.3.1 click <a href='upgrade.php?A330TO331=1'>here</a>.</li>";
			print "<li>To upgrade all 3.2.0 databases to version 3.3.0 click <a href='upgrade.php?A320TO330=1'>here</a>. <br></td>";
			print "<li>To upgrade all 3.1.0 databases to version 3.2.0 click <a href='upgrade.php?A310TO320=1'>here</a>.</li>";
			print "<li>To upgrade all 3.0.0 databases to version 3.1.0 click <a href='upgrade.php?A300TO310=1'>here</a>.</li>";
			print "<li>To upgrade all 2.6.2 databases to version 3.0.0 click <a href='upgrade.php?A262TO300=1'>here</a>.</li>";
			print "<li>To upgrade all 2.6.1 databases to version 2.6.2 click <a href='upgrade.php?A261TO262=1'>here</a>.</li>";
			print "<li>To upgrade all 2.6.0 databases to version 2.6.1 click <a href='upgrade.php?A260TO261=1'>here</a>.</li>";
			print "<li>To upgrade all 2.5.3 databases to version 2.6.0 click <a href='upgrade.php?A253TO260=1'>here</a>.</li>";
			print "<li>To upgrade all 2.5.2 databases to version 2.5.3 click <a href='upgrade.php?A252TO253=1'>here</a>.</li>";
			print "<li>To upgrade all 2.5.1 databases to version 2.5.2 click <a href='upgrade.php?A251TO252=1'>here</a>.</li>";
			print "<li>To upgrade all 2.5.0 databases to version 2.5.1 click <a href='upgrade.php?A250TO251=1'>here</a>.</li>";
			print "<li>To upgrade all 2.4.6 databases to version 2.5.0 click <a href='upgrade.php?A246TO25=1'>here</a>.</li>";
			print "<li>To upgrade all 2.4.5 databases to version 2.4.6 click <a href='upgrade.php?A245TO246=1'>here</a>.</li>";
			print "<li>To upgrade all 2.4.4 databases to version 2.4.5 click <a href='upgrade.php?A244TO245=1'>here</a>.</li>";
			print "<li>To upgrade all 2.4.3 databases to version 2.4.4 click <a href='upgrade.php?A243TO244=1'>here</a>.</li>";
			print "<li>To upgrade all 2.4.2 databases to version 2.4.3 click <a href='upgrade.php?A242TO243=1'>here</a>.</li>";
			print "<li>To upgrade all 2.4.1 databases to version 2.4.2 click <a href='upgrade.php?A241TO242=1'>here</a>.</li>";
			print "<li>To upgrade all 2.4.0 databases to version 2.4.1 click <a href='upgrade.php?A24TO241=1'>here</a>.</li>";
			print "<li>To upgrade all 2.3.0 databases to version 2.4.0 click <a href='upgrade.php?A23TO24=1'>here</a>.</li>";
			print "<li>To upgrade all 2.2.0 databases to version 2.3.0 click <a href='upgrade.php?A22TO23=1'>here</a>.</li>";
			print "<li>To upgrade all 2.1.0 databases to version 2.2.0 click <a href='upgrade.php?A21TO22=1'>here</a>.</li>";
			print "<li>To upgrade all 2.0.0 databases to version 2.1.0 click <a href='upgrade.php?A2TO21=1'>here</a>.</li>";
			print "<li>To upgrade all 1.9.6 databases to version 2.0.0 click <a href='upgrade.php?A196TO2=1'>here</a>.</li>";
			print "<li>To upgrade all 1.9.5 databases to version 1.9.6 click <a href='upgrade.php?A195TO196=1'>here</a>.</li>";
			print "<li>To upgrade all 1.9.4 databases to version 1.9.5 click <a href='upgrade.php?A194TO195=1'>here</a>.</li>";
			print "<li>To upgrade all 1.9.3 databases to version 1.9.4 click <a href='upgrade.php?A193TO194=1'>here</a>.</li>";
			print "<li>To upgrade all 1.9.2 databases to version 1.9.3 click <a href='upgrade.php?A192TO193=1'>here</a>.</li>";
		} else {
			print "<li><a href='upgrade.php?ShowAllOptions'>Show older upgrade options</a></li>";
		}
		print "</ul>";

				print "<p>The following repositories were found in the configuration file and will be upgraded when the version matches the option you choose below:</p>";
		print "<table class='crm'><tr><td><strong>id</strong></td><td><strong>Database</strong></td><td><strong>DB Version</strong></td><td><strong>Name</strong></td><td><strong>MM enabled</strong></td></tr>";
		if (sizeof($pass)>0) {
						for ($r=0;$r<64;$r++) {
							if ($database[$r]) {
								if ($db = @mysql_connect($host[$r], $user[$r], $pass[$r])) {
									if (@mysql_select_db($database[$r],$db)) {
										$sql = "SELECT value FROM " . $table_prefix[$r] . "settings WHERE setting='title'";
										if ($result= mysql_query($sql)) {

											$sql = "SELECT value FROM " . $table_prefix[$r] . "settings WHERE setting='title'";
											$result= @mcq_upg($sql,$db);
											$maxU1 = @mysql_fetch_array($result);
											$title = $maxU1[0];
											$sql = "SELECT value FROM " . $table_prefix[$r] . "settings WHERE setting='DBVERSION'";
											$result= @mcq_upg($sql,$db);
											$maxU1 = @mysql_fetch_array($result);
											$version = $maxU1[0];
											$sql = "SELECT value FROM " . $table_prefix[$r] . "settings WHERE setting='MAINTENANCE_MODE'";
											$result= @mcq_upg($sql,$db);
											$maxU1 = @mysql_fetch_array($result);
											$maintenancemode = $maxU1[0];

											if ($version=="") { $version="Prior to 1.9.0."; }
											$sql = "SELECT COUNT(*) FROM " . $table_prefix[$r] . "entity";
											$result= @mcq_upg($sql,$db);
											$res = @mysql_fetch_array($result);
											$enum = $res[0];
											$sql = "SHOW TABLE STATUS";
											$result= @mcq_upg($sql,$db);
											while ($stat = @mysql_fetch_array($result))
											{
												$size += $stat["Data_length"];
												$size += $stat["Index_lenght"];
											}

											$size = round(($size/1024)) . "K";

											if ($slave[$r]) {
												$ins = "mysql://" . $user[$r] . "@" . $slave[$r]. ":" . $database[$r] . "/" . $table_prefix[$r] . "*";
											} else {
												$ins = "[none]";
											}
											print "<tr><td>" . $r . "</td><td>" . $database[$r] . "</td><td>" . $version . "</td><td>" . $title . "</td><td>" . $maintenancemode . "</td></tr>";
											$totent += $enum;
										} // end if 1st query was ok
									} else {
										print "<tr><td>$r</td><td>$database[$r]</td><td><span style='color: #ff3300;'>n/a</span></td><td><span style='color: #ff3300;'>n/a</span></td><td><span style='color: #FF3300;'>Couldn't select database</span></td></tr>";
									}
								} else {
									print "<tr><td>" . $r . "</td><td>" . $database[$r] . "</td><td>n/a</td><td><span style='color: #FF3300;'>Database host unreachable</span></td><td>n/a</td></tr>";
								}
							}
							}
					}
		print"</table>";
		print "<p>Any user who can log into one of your repositories as an administrator can run this script so please be careful. Delete this script after use.</p>";

		if ($totent > 1000) {
			print "<p><img src='images/error.gif' alt=''> You have a large database. Please consider upgrading using the command line! See the <a href='docs_examples/CRM-CTT_Interleave_Adminmanual.pdf' onclick=\"window.open(this.href); return false;\">manual</a> for details.</p>";
		}

		print "<a href='index.php?logout=1'>To login page</a>";

		EndHTML();
		exit;

}

function A550TO5501() {
	$db_ver_from = "5.5.0";
	$db_ver_to = "5.5.0.1";

	$sqla = array();
	for ($i=1;$i<256;$i++) {
		$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextable" . $i . "` CHANGE `readonly` `readonly` ENUM( 'no', 'yes' ) NOT NULL DEFAULT 'no'";
		$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextable" . $i . "` CHANGE `deleted` `deleted` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n'";
	}

	$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextabledefs` CHANGE `refers_to` `refers_to` VARCHAR( 100 ) NOT NULL ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `displaylistname` VARCHAR(250) NOT NULL AFTER `name`";
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, datetime, discription) values ('ShowInactiveCustomers', 'Yes', now(), 'Set this option to Yes to hide inactive customers from the customer list')";
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, datetime, discription) values ('UseMailQueue', 'Yes', now(), 'Set this option to No to send e-mails in user session, Yes to let housekeeping send the e-mails.')";
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, datetime, discription) values ('TimestampLastHousekeeping', '', now(), 'Internal - refers to last time housekeeping ran')";
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, datetime, discription) values ('TimestampLastDuedateCron', '', now(), 'Internal - refers to last time duedate-notify-cron ran')";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `CUSTOMERACCESSEVALMODULE` INT NOT NULL ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `CUSTOMERACCESSEVALMODULE` INT NOT NULL ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@templates` CHANGE `stylesheet` `stylesheet` INT NOT NULL ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@journal` ADD INDEX ( `timestamp` ) ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@journal` ADD INDEX ( `user` )";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@binfiles` DROP `stylesheet` ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@uselog` ADD INDEX `qs` ( `qs` ( 30 ) ) ";
	$sqla[] = "DROP TABLE IF EXISTS PRFX@@@@@@@calendar";
	$sqla[] = "DROP TABLE IF EXISTS PRFX@@@@@@@help";
	$sqla[] = "DROP TABLE IF EXISTS PRFX@@@@@@@customaddons";
	$sqla[] = "DROP TABLE IF EXISTS PRFX@@@@@@@searchindex";
	$sqla[] = "DROP TABLE IF EXISTS PRFX@@@@@@@webdav_locks";
	$sqla[] = "DROP TABLE IF EXISTS PRFX@@@@@@@webdav_properties";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@entity` CHANGE `openepoch` `openepoch` INT NULL DEFAULT NULL ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@entity` CHANGE `closeepoch` `closeepoch` INT NULL DEFAULT NULL ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@failoverquerystore` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `users_may_select_columns` ENUM( 'y', 'n' ) NOT NULL  ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `skip_security` ENUM( 'n', 'y' ) NOT NULL  ";
	$sqla[] = "CREATE TABLE `PRFX@@@@@@@attributes` ( `entity` int(250) NOT NULL,  `identifier` varchar(20) NOT NULL,  `attribute` varchar(250) NOT NULL,  `value` longtext NOT NULL,  UNIQUE KEY `DB_PRIM` (`entity`,`identifier`,`attribute`))";
	$sqla[] = "CREATE TABLE `PRFX@@@@@@@mailqueue` (  `queueid` int(11) NOT NULL AUTO_INCREMENT,  `user` int(11) NOT NULL, `template` mediumtext NOT NULL,  `entity` int(11) NOT NULL,  `customer` int(11) NOT NULL,  `from` varchar(512) NOT NULL DEFAULT '',  `fromname` varchar(512) NOT NULL DEFAULT '',  `to` varchar(1024) NOT NULL DEFAULT '',  `PDF` ENUM('not','used') NOT NULL,  `subject` varchar(1024) NOT NULL DEFAULT '',  `attach_to_dossier` enum('0','1') NOT NULL,  `attach_as_filename` enum('0','1') NOT NULL,  `report_attach` VARCHAR(20) NOT NULL,  `flextableid` int(11) NOT NULL,  `flextablerecord` int(11) NOT NULL, `date_queued` timestamp NOT NULL, `date_sent` TIMESTAMP NOT NULL, `status` ENUM('unsent', 'sent', 'error'), `worker_hash` VARCHAR(64) NOT NULL DEFAULT '', PRIMARY KEY (`queueid`)) ENGINE=MyISAM COMMENT='Interleave mail queue'";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `underwaterfield` ENUM( 'n', 'y' ) NOT NULL";

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A542TO550() {
	$db_ver_from = "5.4.2";
	$db_ver_to = "5.5.0";
	$sqla = array();

	for ($i=1;$i<256;$i++) {
		$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextable" . $i . "` ADD PRIMARY KEY ( `recordid` ) ";
		$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextable" . $i . "` CHANGE `recordid` `recordid` INT( 11 ) NOT NULL AUTO_INCREMENT ";
		$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextable" . $i . "` ADD `formid` INT NOT NULL AFTER `refer` ";
	}

	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, datetime, discription) values ('ShowDefaultPDFReport', 'Yes', now(), 'Set this option to No to hide the default PDF reports')";
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, datetime, discription) values ('ShowMinimalErrorMessages', 'No', now(), 'Set this option to Yes to hide technical details from SQL- and internal Interleave error messages')";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `showasradio` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `excludefromfilters` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `showfilters` ENUM( 'y', 'n' ) NOT NULL";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `exclude_from_rep` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `INTERACTIVEFIELDS` MEDIUMTEXT NOT NULL DEFAULT ''";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `INTERACTIVEFIELDS` MEDIUMTEXT NOT NULL DEFAULT ''";
	$sqla[] = "CREATE TABLE `PRFX@@@@@@@extrafieldrequiredconditions` (  `conid` int(11) NOT NULL auto_increment,  `efid` int(11) NOT NULL,  `field` varchar(255) NOT NULL,  `value` text NOT NULL,  `trueorfalse` enum('true','false') NOT NULL,  `deletetemplaterow` enum('n','y') NOT NULL default 'n',  `displayvalueintext` enum('n','y') NOT NULL default 'n',  PRIMARY KEY  (`conid`))";
	$sqla[] = "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'filterisactive', 'You have filters enabled which might prevent rows from showing. Click here to disable the filter')";
	$sqla[] = "UPDATE PRFX@@@@@@@extrafields SET fieldtype='numeric' WHERE fieldtype IN ('invoice cost','invoice cost including VAT','invoice qty')";
	$sqla[] = "DELETE FROM PRFX@@@@@@@settings WHERE setting IN ('ENABLESINGLEENTITYINVOICING', 'INVOICENUMBERPREFIX', 'ENABLEMAILMERGEANDINVOICING')";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `USERSPECTRUM` ENUM( 'all', 'in_group', 'customer_related', 'none' ) NOT NULL ";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `USERSPECTRUM` ENUM( 'all', 'in_group', 'customer_related', 'none' ) NOT NULL ";



	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A541TO542() {

	$db_ver_from = "5.4.1";
	$db_ver_to = "5.4.2";
	$sqla = array();


	for ($x=1;$x<256;$x++) {
		$sqla[] = "ALTER TABLE `PRFX@@@@@@@flextable" . $x . "` ADD `deleted` ENUM( 'n', 'y' ) NOT NULL AFTER `recordid` ";
	}

	$sqla[] = "ALTER TABLE `PRFX@@@@@@@triggers` ADD `enabled` ENUM( 'yes', 'no' ) NOT NULL";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@modules` ADD `module_list_html` VARCHAR( 255 ) NOT NULL";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `ENTITYACCESSEVALMODULE` INT NOT NULL";
	$sqla[] = "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `ENTITYACCESSEVALMODULE` INT NOT NULL" ;
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('BASEURL','','The base URL to this installation');";
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('DISPLAYSAVEREMINDER','Yes','Whether or not to display the Save dialog when leaving an unsaved entity');";

	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('SYSWIDECSS','','The stylesheet to be loaded on all pages');";
	$sqla[] = "INSERT INTO PRFX@@@@@@@settings(setting, value, datetime, discription) values ('ShowIconsAboveMainContentBox', 'Yes', now(), 'Set this option to Yes to show icons above main content box')";

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A540TO541() {
	$db_ver_from = "5.4.0";
	$db_ver_to = "5.4.1";
	$sqla = array();

	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','setdatefilter','advanced date filter');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','adjustdatefilter','change date filter');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languagesL(ANGID, TEXTID, TEXT) VALUES('ENGLISH','deletedatefilter','clear date filter');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','mustbebefore','Must be before');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','mustbeafter','Must be after');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','showwhenempty','Show empty');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','datefield','Date field');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','youngest','youngest');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','oldest','oldest');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','daysfromnow','days from [now]');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','weeksfromnow','weeks from [now]');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','daysbeforenow','days before [now]');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','weeksbeforenow','weeks before [now]');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','datefilteractive',\"<span class='noway'><strong>Date filter active</strong></span>\");");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','orblank','or blank');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','after','after');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','before','before');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','today','Today');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','yesterday','Yesterday');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thisweek','This week');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thismonth','This month');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thisquarter','This quarter');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thisyear','This year');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thisyearq1','This year Q1');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thisyearq2','This year Q2');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thisyearq3','This year Q3');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thisyearq4','This year Q4');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','lastyearq1','Last year Q1');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','lastyearq2','Last year Q2');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','lastyearq3','Last year Q3');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','lastyearq4','Last year Q4');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','tomorrow','Tomorrow');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','nextweek','Next week');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','nextmonth','Next month');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','lastweek','Last week');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','lastmonth','Last month');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','lastyear','Last year');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month1','January');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month2','February');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month3','March');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month4','April');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month5','May');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month6','June');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month7','July');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month8','August');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month9','September');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month10','October');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month11','November');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','month12','December');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','group','Group');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','week','week');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','listofentities_assigned', 'List of entities assigned to you')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','listofentities_owned', 'List of entities owned by you')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','listofentities_recent', 'List of entities recently opened by you')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','listofentities_today', 'List of entities due today (or overdue)')");


	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'setdatefilter', 'geavanceerde periodeselectie');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'adjustdatefilter', 'periodeselectie aanpassen');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'deletedatefilter', 'periodeselectie wissen');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'mustbebefore', 'Moet eerder zijn dan');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'mustbeafter', 'Moet later zijn dan');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'showwhenempty', 'Toon lege velden');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'datefield', 'Datumveld');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'youngest', 'jongste waarde');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'oldest', 'oudste waarde');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'daysfromnow', 'dagen na [nu]');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'weeksfromnow', 'weken na [nu]');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'daysbeforenow', 'dagen voor [nu]');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'weeksbeforenow', 'weken voor [nu]');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'datefilteractive', '<span style=''color: #ff0000;''>Periodeselectie actief</span>');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'orblank', 'of leeg');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'after', 'na');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'before', 'voor');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'today', 'Vandaag');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'yesterday', 'Gisteren');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thisweek', 'Huidige week');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thismonth', 'Huidige maand');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thisquarter', 'Huidig kwartaal');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thisyear', 'Huidig jaar');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thisyearq1', 'Huidig jaar Q1');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thisyearq2', 'Huidig jaar Q2');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thisyearq3', 'Huidig jaar Q3');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'thisyearq4', 'Huidig jaar Q4');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'lastyearq1', 'Vorig jaar Q1');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'lastyearq2', 'Vorig jaar Q2');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'lastyearq3', 'Vorig jaar Q3');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'lastyearq4', 'Vorig jaar Q4');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'tomorrow', 'Morgen');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'nextweek', 'Volgende week');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'nextmonth', 'Volgende maand');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'lastweek', 'Vorige week');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'lastmonth', 'Vorige maand');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'lastyear', 'Vorig jaar');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month1', 'Januari');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month2', 'Februari');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month3', 'Maart');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month4', 'April');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month5', 'Mei');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month6', 'Juni');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month7', 'Juli');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month8', 'Augustus');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month9', 'September');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month10', 'Oktober');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month11', 'November');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'month12', 'December');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@languages` (`LANGID`, `TEXTID`, `TEXT`) VALUES('NEDERLANDS', 'group', 'Groep');");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `tableheaderrepeat` INT NOT NULL ");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@statusvars ADD listorder INT");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@priorityvars ADD listorder INT");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('SHOWTABLEHEADEREVERY','50','Repeat the table header every XX lines');");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `extractedascii` LONGTEXT NOT NULL ");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` ADD FULLTEXT (`extractedascii`)");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@blobs` ADD `minithumbnail` BLOB NOT NULL ;");

	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@templates` ( `templateid` int(11) NOT NULL auto_increment, `templatename` varchar(200) NOT NULL default '', `creation_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, `templatetype` varchar(150) NOT NULL default '', `username` varchar(150) NOT NULL default '', `template_subject` varchar(250) NOT NULL default '', `show_on_add_list` enum('y','n') NOT NULL, `stylesheet` mediumint(9) NOT NULL, `orientation` enum('P','L') default NULL, `content` mediumtext NOT NULL, PRIMARY KEY (`templateid`)) COMMENT='Interleave templates' ;");


	array_push($sqla, "INSERT INTO PRFX@@@@@@@templates(`templateid`, `templatename`, `creation_date`, `templatetype`, `username`, `template_subject`, `show_on_add_list`, `stylesheet`, `orientation`, `content`) SELECT PRFX@@@@@@@binfiles.fileid, filename, creation_date, filetype, username, file_subject, show_on_add_list, stylesheet, orientation, content FROM PRFX@@@@@@@binfiles, PRFX@@@@@@@blobs WHERE PRFX@@@@@@@binfiles.fileid=PRFX@@@@@@@blobs.fileid AND PRFX@@@@@@@binfiles.koppelid=0;");

	array_push($sqla, "UPDATE PRFX@@@@@@@settings SET discription='The number of rows to show per page on the recent entities list' WHERE setting='SHOWRECENTEDITEDENTITIES'");

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A532TO540() {
	// DO NOT CHANGE THE db_ver_from VARIABLE BELOW. PLEASE CHECK THE UPGRADING FILE FOR DETAILS ABOUT UPGRADING FROM 5.3.2 TO 5.4.0.
	$db_ver_from = "5.3.2-CopyEAVDone";
	$db_ver_to = "5.4.0";
	$sqla = array();
	array_push($sqla, "DROP TABLE PRFX@@@@@@@processes;");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@settings WHERE setting = 'AUTOCOMPLETECUSTOMERNAMES';");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('PAGINATECUSTOMERLIST','30','The number of customers to show per page');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('FILELISTSORTORDER','Date','The sort order for all file lists');");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `ALLOWEDADDFORMS` LONGTEXT NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `ALLOWEDADDFORMS` LONGTEXT NOT NULL");
	array_push($sqla, "UPDATE `PRFX@@@@@@@userprofiles` SET ALLOWEDADDFORMS = ADDFORMS;");
	array_push($sqla, "UPDATE `PRFX@@@@@@@loginusers` SET ALLOWEDADDFORMS = ADDFORMS;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `FORCEUSERCLLIMIT` ENUM( 'n', 'y' ) NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `remarks` LONGTEXT NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `lastchange` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , ADD `lastchangeby` INT NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `lastchange` `lastchange` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('SUBTITLE','- subtitle -','The sub-title of this repository (will be displayed on login page)');");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@extrafields CHANGE `defaultval` `defaultval` LONGTEXT  NULL DEFAULT NULL");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('ENTITYLOCKTIMEOUT','3600','The number of seconds an entity locks lasts');");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A531TO532() {
	$db_ver_from = "5.3.1";
	$db_ver_to = "5.3.2";
	$sqla = array();

	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('MINIMUMPASSWORDSTRENGTH','2','The required password strength for users')");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@configsnapshots` (  `id` int(11) NOT NULL auto_increment,  `datetime` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,  `comment` varchar(255) NOT NULL,  `config` longtext NOT NULL,  `snapshottype` ENUM( 'nousers', 'withusers', 'wholedb'),  PRIMARY KEY  (`id`)) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='Interleave configuration snapshots';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `orientation` ENUM('P','L') DEFAULT 'P'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `FORCESTARTFORM` INT NOT NULL DEFAULT '0'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `FORCESTARTFORM` INT NOT NULL DEFAULT '0'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `sort_on` INT NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `sort_direction` ENUM('Ascending', 'Descending') NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `access_controlled_by_field` INT NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafieldconditions` ADD `deletetemplaterow` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@tabmenudefinitions` ADD `menu_type` VARCHAR( 255 ) NOT NULL DEFAULT 'Tabbed' AFTER `menu_name` ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafieldconditions` ADD `displayvalueintext` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n'");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@entity ADD starttime VARCHAR(4)");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','monday_short','mo')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','tuesday_short','tue')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','wednesday_short','wed')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thursday_short','thu')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','friday_short','fri')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','saturday_short','sat')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','sunday_short','sun')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','time','time')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','monday','Monday')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','tuesday','Tuesday')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','wednesday','Wednesday')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','thursday','Thursday')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','friday','Friday')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','saturday','Saturday')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','sunday','Sunday')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','hours','hours')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','currentbookmarks', 'Bookmarks')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','nobookmarks', 'No bookmarks defined')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','bookmarkselection', 'Create bookmark based on selected fields')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','managebookmarks', 'Manage bookmarks')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','selectbookmark', 'Select bookmark')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','date', 'Date')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','hour', 'Hour')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','exportincompletelayout', 'Download Excel sheet (all fields)')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','exportinlistlayout', 'Download Excel sheet (fields in this list)')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','withselected', 'With selected')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','mu_set', 'Set')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','mu_to', 'to')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','undelete', 'undelete')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','mu_pressbutton', 'Press button')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','dailymail', 'Receive daily mail with entities assigned to you')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','emailallupdates', 'E-mail all entity updates')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','mustchangepwd', 'You <strong>must</strong> change your password right now')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','currentpassword', 'Current password')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','newpassword', 'New password')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','newpasswordconfirm', 'New password (confirm)')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','chgpwdallrep', 'Alter your password on all repositories')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','chgpwdallrepwarning', '(only works when your name and password are the same)')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','mailboxsettings', 'Mailbox settings (POP3 only)')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','host', 'Host')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','checktodelete', 'Check this box to delete mail settings')");


	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','passwarning', 'Your password is not strong enough.')");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `israwhtml` ENUM( 'n', 'y' ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `access_denied_method` ENUM( 'readonly', 'invisible' ) NOT NULL AFTER `access_controlled_by_field`");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A530TO531() {
	$db_ver_from = "5.3.0";
	$db_ver_to = "5.3.1";
	$sqla = array();

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `MENUTOUSE` VARCHAR(7) NOT NULL DEFAULT 'default';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `MENUTOUSE` VARCHAR(7) NOT NULL DEFAULT 'default'");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@tabmenudefinitions` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`menu_name` VARCHAR( 255 ) NOT NULL ,`menu_array` MEDIUMTEXT NOT NULL ,INDEX ( `menu_name` )) ENGINE = MYISAM COMMENT = 'Interleave custom tab menu definitions'");


	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A520TO530() {
	$db_ver_from = "5.2.0";
	$db_ver_to = "5.3.0";
	$sqla = array();

	// The new customer form is added in the upgrade function itself

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `addlinktext` VARCHAR( 255 ) NOT NULL , ADD `sumnumrows` ENUM( 'y', 'n' ) NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `parent` )");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('USECUSTOMERSELECTPOPUP','No','Set to Yes for a popup-box to select customers instead of the drop-down box')");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `compact_view` ENUM( 'n', 'y' ) NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `add_in_popup` ENUM( 'y', 'n' ) NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `stylesheet` MEDIUMINT NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@accesscache` ADD INDEX ( `eidcid` )");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `status` )");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `priority` )");

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A511TO520() {
	$db_ver_from = "5.1.1";
	$db_ver_to = "5.2.0";
	$sqla = array();

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `optioncolors` LONGTEXT NOT NULL AFTER `options`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` CHANGE `type` `type` VARCHAR( 20 ) NOT NULL DEFAULT 'entity'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` ADD INDEX ( `type` )");

	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('BODY_ADMIN_ERRORMSG','','Put any HTML in this field to replace the form a user gets with Access Denied event')");

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A510TO511() {
	$db_ver_from = "5.1.0";
	$db_ver_to = "5.1.1";
	$sqla = array();
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@todo` (`todoid` INT NOT NULL AUTO_INCREMENT ,`onchange` VARCHAR( 255 ) NOT NULL ,`to_value` TEXT NOT NULL ,`eid` INT NOT NULL ,`user` INT NOT NULL ,`timestamp` TIMESTAMP NOT NULL ,PRIMARY KEY ( `todoid` ));  ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@todo` ADD `from_value` TEXT NOT NULL AFTER `onchange`;");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('USE_AUTOSAVE','Yes','Set this value to Yes to enable automatic saving of entity forms in the background'); ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` CHANGE `type` `type` ENUM( 'page', 'report', 'form' )  NOT NULL DEFAULT 'page'");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('HIDEENTITYADDTABS','No','Set this to yes to block the sub-tabs on the add-entity screen');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('EXCELFILEFORMAT','2003','Select Excel export file format - XLS vs. XLSX')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('ALLOWLOGONPAGEPASSCHANGE','Yes','Set this to No to disable the logon-page change-password functionality')");

	// hier onder

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@failoverquerystore` CHANGE `id` `id` BIGINT( 9 ) NOT NULL AUTO_INCREMENT");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` CHANGE `CLLEVEL` `CLLEVEL` LONGTEXT NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` CHANGE `CLLEVEL` `CLLEVEL` LONGTEXT NOT NULL");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('LDAP_BIND_USERNAME','','Bind user for LDAP');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('LDAP_BIND_PASSWORD','','Password for LDAP bind user');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('LDAP_BASE_DN','','LDAP Base DN');");
	// to be sure....
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@triggers` ADD `processorder` INT NOT NULL AFTER `onchange`");

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A501TO510() {
		global $web;
		if (!$web) {
			// Call upgrade function
			A501TO510Really();
		} elseif ($_REQUEST['A501TO510Really']) { // Request to really upgrade
			// Call upgrade function
			A501TO510Really();

		} else {

			// Other PHP/Interleave functions used in this part are htme() and SimpleMail(). The
			// first adds htmlspecialchars to all input, the second e-mails the mail body. Both add
			// no information, but feel free to check htme() at functions.php:249 and SimpleMail()
			// at functions.php:10689.

			// Header thingy
			print "<tr><td align='center'><br><br><table width='50%'><tr><td>";
			print "<fieldset><legend>Interleave 5.1.0 upgrade procedure</legend>";

			if ($_REQUEST['FormSub']) { // Feedback form was submitted

				// Create mail body
				$body = "Feedback form submitted during 5.1.0 upgrade:<br>";
				$body .= "<br>Name: " . htme($_REQUEST['Name']);
				$body .= "<br>E-mail: " . htme($_REQUEST['Email']);
				$body .= "<br>Primary use: " . htme($_REQUEST['PrimaryUse']);
				$body .= "<br>User since: " . htme($_REQUEST['Since']);
				$body .= "<br>No. of repos: " . htme($_REQUEST['NoOfRepos']);
				$body .= "<br>Comments:<br> " . htme($_REQUEST['feedback']);

				// E-mail body to info@interleave.nl
				// SimpleMail Syntax:
				//			SimpleMail(FROM, TO, SUBJECT, MAILBODY, FILE_ATTACHMENT_ARRAY);

				SimpleMail("info@interleave.nl", "info@interleave.nl", "Feedback form", $body, false);

				// Print nice thank-you message
				print "Thank you very much for your feedback!";

				// Provide hyperlink to the rest of the upgrade routine
				print "<br><br><a href='upgrade.php?A501TO510Really=1&A501TO510=1'>Continu with upgrade</a>";

			} else { // No form was submitted, print the form

				// Some text
				print "<strong>Request for feedback</strong><br><br>";
				print "Before upgrading your repository, please take a minute to give us some feedback. If you would be so kind to fill in the form below, the information will be e-mailed to info@interleave.nl using your Interleave mail settings.";
				print "<br><br><fieldset>This function will <strong>not send any other information</strong> than the information you enter in the form below and the information your mailserver adds to your message, if any. To make sure, feel free to take a look at the source code of this script (start at line 302).</fieldset>";
				print "<br><br>It's really important for us to get feedback about your usage and your wishes. And while at it, please also <a href='https://sourceforge.net/donate/index.php?group_id=61096&amt=0&type=0' onclick=\"window.open(this.href); return false;\">consider donating</a> to the project (link opens a new window).";

				// The actual form
				print "<form id='feedback' method='post' action='upgrade.php?A501TO510=1&FormSub=1'><div class='showinline'><br><br>";
				print "<table>";

				// Name
				print "<tr><td>Your name:</td><td><input type='text' name='Name' size='50' value='A.Nonymous'></td></tr>";

				// E-mail
				print "<tr><td>Your e-mail address:</td><td><input type='text' name='Email' size='50' value='(if you want our reaction)'></td></tr>";

				// Primary use
				print "<tr><td>Primary Interleave use:</td><td><input type='text' name='PrimaryUse' size='50'></td></tr>";

				// Since ...
				print "<tr><td>User since:</td><td><input type='text' name='Since' size='4'> (year)</td></tr>";

				// No. of repositories
				print "<tr><td>No. of repositories: </td><td><input type='text' name='NoOfRepos' size='2' value='1'></td></tr>";

				// The actual feedback
				print "<tr><td valign='top'>What to change: </td><td><textarea name='feedback' cols='40' rows='5'>I'd like to see more ... less ... better ... etc.</textarea></td></tr>";

				// Submit button
				print "<tr><td><input type='submit' name='Submit' value='Submit'></td></tr>";

				// Print skip-this link
				print "<tr><td colspan='2'><br><br><br><a href='upgrade.php?A501TO510Really=1&A501TO510=1'>Skip this, I just want to upgrade</a>!</td></tr>";

				print "</table>";
			}

			// Footer thingy
			print "</fieldset></td></tr></table>";
			print "</td><tr>";
		}
}

function A510TOA511() {
	$db_ver_from = "5.1.0";
	$db_ver_to = "5.1.1";
	$sqla = array();

	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('ALLOWLOGONPAGEPASSCHANGE','Yes','Set this to No to disable the logon-page change-password functionality')");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` CHANGE `type` `type` ENUM( 'page', 'report', 'form' )  NOT NULL DEFAULT 'page'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@triggers` ADD `processorder` INT NOT NULL AFTER `onchange`");
	array_push($sqla, "UPDATE PRFX@@@@@@@triggers SET PRFX@@@@@@@triggers.processorder=(10 * PRFX@@@@@@@triggers.tid)");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('HIDEENTITYADDTABS','No','Set this to yes to block the sub-tabs on the add-entity screen')");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@todo` (`todoid` INT NOT NULL AUTO_INCREMENT ,`onchange` VARCHAR( 255 ) NOT NULL ,`to_value` TEXT NOT NULL ,`eid` INT NOT NULL ,`user` INT NOT NULL ,`timestamp` TIMESTAMP NOT NULL ,PRIMARY KEY ( `todoid` ))");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@todo` ADD `from_value` TEXT NOT NULL AFTER `onchange`");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('USE_AUTOSAVE','Yes','Set this value to Yes to enable automatic saving of entity forms in the background')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('EXCELFILEFORMAT','2007','Select Excel export file format - XLS vs. XLSX')");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `options` `options` LONGTEXT NOT NULL ");

	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('LDAP_BIND_USERNAME','','Bind user for LDAP')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('LDAP_BIND_PASSWORD','','Password for LDAP bind user')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (setting, value, discription) VALUES('LDAP_BASE_DN','','LDAP Base DN')");

	Upgrade($db_ver_from, $db_ver_to, $sqla, "Make sure you enable the housekeeping script! See UPGRADING for details.<br>");

}

function A501TO510Really() {
	$db_ver_from = "5.0.1";
	$db_ver_to = "5.1.0";
	$sqla = array();
	array_push($sqla, "DELETE FROM PRFX@@@@@@@settings WHERE setting='ADMPASSWORD'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@settings WHERE setting='MIPASSWORD'");

	array_push($sqla, "CREATE TABLE IF NOT EXISTS `PRFX@@@@@@@triggerconditions` (  `conid` int(11) NOT NULL auto_increment,  `triggerid` int(11) NOT NULL,  `field` varchar(255) NOT NULL,  `value` text NOT NULL,  `trueorfalse` enum('true','false') NOT NULL,  `failmessage` varchar(255) NOT NULL,  PRIMARY KEY  (`conid`));");
	array_push($sqla, "CREATE TABLE IF NOT EXISTS `PRFX@@@@@@@extrafieldconditions` (`conid` int(11) NOT NULL auto_increment, `efid` int(11) NOT NULL, `field` varchar(255) NOT NULL, `value` text NOT NULL,  `trueorfalse` enum('true','false') NOT NULL,  PRIMARY KEY  (`conid`))");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `ELISTLAYOUT` TEXT NOT NULL , ADD `CLISTLAYOUT` TEXT NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` CHANGE `CLLEVEL` `CLLEVEL` TEXT NOT NULL;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` CHANGE `CLLEVEL` `CLLEVEL` TEXT NOT NULL;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@cache` ADD `session` VARCHAR( 32 ) NOT NULL AFTER `epoch` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@triggerconditions` ADD `successmessage` VARCHAR( 255 ) NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@triggerconditions` ADD `failmessage` VARCHAR( 255 ) NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@blobs` ADD `gz` ENUM( 'n', 'y' ) NOT NULL");

	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:5:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:14:\"CustomerSeeOwn\";i:4;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='logger';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:15:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:11:\"AssignedSee\";i:4;s:12:\"AssignedEdit\";i:5;s:8:\"OtherSee\";i:6;s:9:\"OtherEdit\";i:7;s:11:\"CommentsAdd\";i:8;s:16:\"MaySelectColumns\";i:9;s:20:\"MayUseMainlistFilter\";i:10;s:11:\"CustomerAdd\";i:11;s:14:\"CustomerSeeOwn\";i:12;s:15:\"CustomerEditOwn\";i:13;s:16:\"CustomerSeeOther\";i:14;s:17:\"CustomerEditOther\";}' WHERE CLLEVEL='rw';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:16:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:11:\"AssignedSee\";i:4;s:12:\"AssignedEdit\";i:5;s:8:\"OtherSee\";i:6;s:9:\"OtherEdit\";i:7;s:11:\"CommentsAdd\";i:8;s:16:\"MaySelectColumns\";i:9;s:20:\"MayUseMainlistFilter\";i:10;s:11:\"CustomerAdd\";i:11;s:14:\"CustomerSeeOwn\";i:12;s:15:\"CustomerEditOwn\";i:13;s:16:\"CustomerSeeOther\";i:14;s:17:\"CustomerEditOther\";i:15;s:13:\"Administrator\";}' WHERE administrator='yes';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:3:{i:0;s:6:\"OwnSee\";i:1;s:14:\"CustomerSeeOwn\";i:2;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='ooro';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:4:{i:0;s:9:\"EntityAdd\";i:1;s:11:\"AssignedSee\";i:2;s:14:\"CustomerSeeOwn\";i:3;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-see-own-assigned-entities';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:5:{i:0;s:6:\"OwnSee\";i:1;s:11:\"AssignedSee\";i:2;s:8:\"OtherSee\";i:3;s:14:\"CustomerSeeOwn\";i:4;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='read-only-all';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:5:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:11:\"AssignedSee\";i:3;s:14:\"CustomerSeeOwn\";i:4;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-see-own-assigned-and-owned-entities';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:5:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:14:\"CustomerSeeOwn\";i:4;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-own-entities';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET CLLEVEL='a:7:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:11:\"AssignedSee\";i:4;s:12:\"AssignedEdit\";i:5;s:14:\"CustomerSeeOwn\";i:6;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-owned-assigned-entities';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET LIMITTOCUSTOMERS=replace(replace(substring(substring_index(FULLNAME, ':', 2), length(substring_index(FULLNAME, ':', 2 - 1)) + 1), ',', ''),':',''), CLLEVEL='a:3:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:13:\"NoOwnNoAssign\";}', HIDEFROMASSIGNEEANDOWNERLISTS='y', FULLNAME=replace(replace(substring(substring_index(FULLNAME, ':', 3), length(substring_index(FULLNAME, ':', 3 - 1)) + 1), ',', ''),':','') WHERE CLLEVEL='ro';");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET LIMITTOCUSTOMERS=replace(replace(substring(substring_index(FULLNAME, ':', 2), length(substring_index(FULLNAME, ':', 2 - 1)) + 1), ',', ''),':',''), CLLEVEL='a:4:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:11:\"CommentsAdd\";i:3;s:13:\"NoOwnNoAssign\";}', HIDEFROMASSIGNEEANDOWNERLISTS='y', FULLNAME=replace(replace(substring(substring_index(FULLNAME, ':', 3), length(substring_index(FULLNAME, ':', 3 - 1)) + 1), ',', ''),':','') WHERE CLLEVEL='ro+';");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET CLLEVEL='a:15:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:11:\"AssignedSee\";i:4;s:12:\"AssignedEdit\";i:5;s:8:\"OtherSee\";i:6;s:9:\"OtherEdit\";i:7;s:11:\"CommentsAdd\";i:8;s:16:\"MaySelectColumns\";i:9;s:20:\"MayUseMainlistFilter\";i:10;s:11:\"CustomerAdd\";i:11;s:14:\"CustomerSeeOwn\";i:12;s:15:\"CustomerEditOwn\";i:13;s:16:\"CustomerSeeOther\";i:14;s:17:\"CustomerEditOther\";}' WHERE CLLEVEL='rw';");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET CLLEVEL='a:3:{i:0;s:6:\"OwnSee\";i:1;s:14:\"CustomerSeeOwn\";i:2;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='ooro';");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET CLLEVEL='a:4:{i:0;s:9:\"EntityAdd\";i:1;s:11:\"AssignedSee\";i:2;s:14:\"CustomerSeeOwn\";i:3;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-see-own-assigned-entities';");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET CLLEVEL='a:5:{i:0;s:6:\"OwnSee\";i:1;s:11:\"AssignedSee\";i:2;s:8:\"OtherSee\";i:3;s:14:\"CustomerSeeOwn\";i:4;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='read-only-all';");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET CLLEVEL='a:5:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:11:\"AssignedSee\";i:3;s:14:\"CustomerSeeOwn\";i:4;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-see-own-assigned-and-owned-entities';");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET CLLEVEL='a:5:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:14:\"CustomerSeeOwn\";i:4;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-own-entities';");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET CLLEVEL='a:7:{i:0;s:9:\"EntityAdd\";i:1;s:6:\"OwnSee\";i:2;s:7:\"OwnEdit\";i:3;s:11:\"AssignedSee\";i:4;s:12:\"AssignedEdit\";i:5;s:14:\"CustomerSeeOwn\";i:6;s:16:\"CustomerSeeOther\";}' WHERE CLLEVEL='full-access-owned-assigned-entities';");

	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','contacts','Contacts');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','selectcolumns','select columns');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','clearfilter','clear filter');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'CATEGORYBOXSIZE', '50', NOW() , 'The default size of the category field');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'TABCOLORS', '', NOW() , 'The serialized array containing custom tab colors (hidden)');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'CLIPLISTAT', '500', NOW() , 'The main entity list will never show more entities than specified in this setting');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'FILECOMPRESSIONLEVEL', 'A', '', 'File compression level, see details below or in manual.');");

	//array_push($sqla, "ALTER TABLE `PRFX@@@@@@@failoverquerystore` CHANGE `lockhash` `lockhash` VARCHAR( 100 ) NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@processes` CHANGE `duration` `duration` FLOAT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@processes` CHANGE `memory` `memory` FLOAT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@processes` CHANGE `message` `message` TEXT NULL ");

	array_push($sqla, "UPDATE PRFX@@@@@@@settings SET discription='NO LONGER USED; use triggers!' WHERE setting='EMAILNEWENTITIES'");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `maxrowsperpage` TINYINT NOT NULL , ADD `headerhtml` TEXT NOT NULL ;");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entityformcache` ADD `tabletype` VARCHAR( 15 ) NOT NULL DEFAULT 'entity' AFTER `formid`;");

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A50TO501() {
	$db_ver_from = "5.0";
	$db_ver_to = "5.0.1";
	$sqla = array();
	array_push($sqla, "CREATE TABLE  `PRFX@@@@@@@triggerconditions` ( `conid` INT NOT NULL AUTO_INCREMENT , `triggerid` INT NOT NULL , `field` VARCHAR( 255 ) NOT NULL , `value` TEXT NOT NULL , `trueorfalse` ENUM(  'true',  'false' ) NOT NULL , PRIMARY KEY (  `conid` ) )");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A430TO50() {
	$db_ver_from = "4.3.0";
	$db_ver_to = "5.0";
	$sqla = array();
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@flextabledefs` (  `recordid` int(11) NOT NULL auto_increment,  `tablename` varchar(128) NOT NULL,  `orientation` enum('many_entities_to_one','one_entity_to_many') NOT NULL,  `formid` int(11) NOT NULL,  `refers_to` enum('entity','customer') NOT NULL,  `refer_field_layout` tinytext NOT NULL,  `comment` varchar(250) NOT NULL,  PRIMARY KEY  (`recordid`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Interleave flextable definition table'");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@sessions` ADD UNIQUE (`temp`)");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@sessions` CHANGE `temp` `temp` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ");
	array_push($sqal, "ALTER TABLE `PRFX@@@@@@@sessions` CHANGE `temp` `temp` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `DASHBOARDFILEID` INT NOT NULL AFTER `HIDEOVERDUEFROMDUELIST`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `FORCEPASSCHANGE` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n',ADD `LASTPASSCHANGE` TIMESTAMP NOT NULL");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET LASTPASSCHANGE=CURRENT_TIMESTAMP");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` CHANGE `LASTPASSCHANGE` `LASTPASSCHANGE` DATETIME NOT NULL");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `DASHBOARDFILEID` INT NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` CHANGE `type` `type` VARCHAR( 50 )  NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `tabletype` `tabletype` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'entity'");

	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'STICKYENTITY', 'No', NOW() , 'Set this option to Yes if you DO NOT WANT to go back to the list after saving an entity')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'DISABLEENTITYFORMCACHE', 'No', NOW() , 'Set this option to Yes if you DO NOT WANT entity forms to be cached. NOT recommended!')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'ALLOWEDIPADRESSES', '', NOW() , 'Enter semicolon-separated list of allowed IP-adresses to use this application (careful!)')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'PASSWORDEXPIRE', '90', NOW() , 'The number of days it takes for a password to expire. The user will be forced to change it if it expires.')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings (settingid , setting , value , datetime , discription) VALUES ('', 'ONEMAILPERTRIGGER', 'No', NOW() , 'Set this option to Yes to send only one triggered mail per recipient per trigger.')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'earlierversions','Earlier versions')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'newfile','New file')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'newversionof','New version of')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'version','Version')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'contacts','Contacts')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'dashboard','Dashboard')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH', 'lostlock','You have lost your exclusive write lock for this entity')");
    array_push($sqla, "UPDATE PRFX@@@@@@@binfiles SET filetype='image/gif',creation_date=creation_date WHERE filetype='mage/gif'");
    array_push($sqla, "UPDATE PRFX@@@@@@@binfiles SET filetype='image/jpeg',creation_date=creation_date WHERE filetype='mage/jpeg'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `showsearchbox` ENUM( 'n', 'y' ) NOT NULL");
    array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `showsearchbox` `showsearchbox` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n'");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `allowuserstoaddoptions` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n'");


	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD `limitddtowidth` INT NOT NULL");

	array_push($sqla, "UPDATE PRFX@@@@@@@languages SET TEXT='Contacts' WHERE TEXTID='phonebookshort' AND TEXT LIKE 'PB%'");

	array_push($sqla, "UPDATE PRFX@@@@@@@languages SET TEXT='Total contacts: ' WHERE TEXT LIKE 'This phonebook%'");
 	array_push($sqla, "UPDATE PRFX@@@@@@@languages SET TEXT='' WHERE TEXT LIKE 'entries%'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@phonebook` CHANGE `Company` `Customer` INT( 50 ) NOT NULL");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `table_layout` MEDIUMTEXT NOT NULL AFTER `refer_field_layout`");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@flextabledefs` ADD `accessarray` LONGTEXT NOT NULL AFTER `table_layout`");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` ADD INDEX ( `type` )");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@modules` ADD `module_accessarray` LONGTEXT NOT NULL");

	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@breadcrumtrail` (  `id` int(11) NOT NULL auto_increment,  `user` int(11) NOT NULL,  `text` varchar(255) NOT NULL,  `link` longtext NOT NULL,  `stamp` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,  PRIMARY KEY  (`id`),  KEY `user` (`user`))");

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}

function A420TO430() {
	$db_ver_from = "4.2.0";
	$db_ver_to = "4.3.0";
	$sqla = array();

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@sessions` ADD UNIQUE (`temp`)");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@sessions` CHANGE `temp` `temp` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ");
	array_push($sqal, "ALTER TABLE PRFX@@@@@@@sessions CHANGE `temp` `temp` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@processes` (  `pid` int(11) NOT NULL auto_increment,  `datetime` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,  `page` varchar(100) NOT NULL,  `user` int(11) NOT NULL,  `result` enum('unknown','succes','error','sqlerror','not closed properly','running') NOT NULL,  `duration` float NOT NULL,  `memory` float NOT NULL,  `message` text NOT NULL,  PRIMARY KEY  (`pid`)) ENGINE=MyISAM AUTO_INCREMENT=3690 DEFAULT CHARSET=latin1 COMMENT='Interleave running process list';");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@triggers` ADD `mailtype` ENUM( 'email', 'inmail' ) NOT NULL AFTER `template_fileid` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `BOSS` INT NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `BOSS` INT NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `TRACE` ENUM( 'Off', 'On' ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `openidurl` TEXT NOT NULL AFTER `noexp` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `version_belonging_to` INT DEFAULT '0' NOT NULL , ADD `version_no` INT DEFAULT '1' NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@blobs` ADD `thumbnail` BLOB NOT NULL ;");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` CHANGE `filesize` `filesize` INT( 11 ) NOT NULL DEFAULT '0';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` CHANGE `type` `type` ENUM( 'entity', 'cust' ) NOT NULL;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@ejournal` CHANGE `formid` `formid` INT(11 ) NOT NULL DEFAULT '0';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` ADD `sqlstartdate` DATE NOT NULL AFTER `sqldate` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` ADD `startdate` VARCHAR( 15 ) NOT NULL AFTER `deleted`;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` CHANGE `formid` `formid` INT( 11 ) NOT NULL DEFAULT '0';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entityformcache` CHANGE `eid` `eid` INT( 11 ) NOT NULL;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entityformcache` CHANGE `formcacheid` `formcacheid` INT( 11 ) NOT NULL AUTO_INCREMENT ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entityformcache` CHANGE `formid` `formid` INT( 11 ) NOT NULL;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entityformcache` CHANGE `user` `user` INT( 11 ) NOT NULL;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `ordering` `ordering` INT( 11 ) NOT NULL DEFAULT '0';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@failoverquerystore` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@internalmessages` CHANGE `dub_count` `dub_count` INT( 11 ) NOT NULL DEFAULT '1';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@modules` CHANGE `mid` `mid` INT( 11 ) NOT NULL AUTO_INCREMENT ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@phonebook` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` CHANGE `as_user` `as_user` INT( 11 ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` CHANGE `repository` `repository` INT( 11 ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` CHANGE `template` `template` INT( 11 ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` CHANGE `visible_from` `visible_from` INT( 11 ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` CHANGE `visible_until` `visible_until` INT( 11 ) NOT NULL; ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@searchindex` CHANGE `eid` `eid` INT( 11 ) NOT NULL ;");

	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='composenewmessage';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='deleteallfrominbox';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='deleteallreadfrominbox';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='incomingmessages';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='last5messages';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='messageinbox';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='nomessages';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='sendmessage';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='ENGLISH' AND TEXTID='yourmessagewassend';");

	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUE('ENGLISH', 'startdate', 'Start date');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','composenewmessage','Compose new message');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','deleteallfrominbox','Delete all messages from inbox');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','deleteallreadfrominbox','Delete read messages from inbox');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','incomingmessages','Incoming messages');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','last5messages','Last 5 messages');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','messageinbox','Message inbox');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','nomessages','No messages');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','sendmessage','Send message');");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','yourmessagewassend','Your message was send as message id :');");

	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('earlierversions','Earlier versions')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('newfile','New file')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('newversionof','New version of')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('version','Version')");

	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DASHBOARDTEMPLATE', 'Normal', '', 'Enter the template file id in this field to use that template as dashboard template for all users');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ENABLEFILEVERSIONING', 'Yes', '', 'Set this variable to Yes to enable the file versioning functions for files attached to entities');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ENABLEIMAGETHUMBNAILS', 'Yes', '', 'Set this variable to Yes to enable the thumbnail popup when hovering the mouse over an image file name');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ENABLEOPENIDAUTH', 'No', '', 'Set this to Yes to enable users to enter their OpenID URL in their profile and use OpenID to log in.');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'HIDEMAINTAB', '', 'No', 'Set this variable to Yes to hide the main tab (either Main or Dashboard link)');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'TABSTOHIDE', '', 'No', 'Serialized array of tab names to hide (invisible)');");

	array_push($sqla, "UPDATE PRFX@@@@@@@binfiles SET filetype='image/gif',creation_date=creation_date WHERE filename LIKE '%.gif';");
	array_push($sqla, "UPDATE PRFX@@@@@@@binfiles SET filetype='image/jpeg',creation_date=creation_date WHERE filename LIKE '%.jpg';");
	array_push($sqla, "UPDATE PRFX@@@@@@@entity SET sqlstartdate = date_format(cdate, '%Y-%m-%d');");
	array_push($sqla, "UPDATE PRFX@@@@@@@entity SET startdate = date_format(cdate, '%d-%m-%Y');");
	array_push($sqla, "UPDATE PRFX@@@@@@@languages SET TEXT='Save' WHERE TEXT LIKE '%Save to database%';");


	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers set ALLOWEDSTATUSVARS='a:1:{i:0;s:3:\"All\";}' WHERE ALLOWEDSTATUSVARS=''");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers set ALLOWEDPRIORITYVARS='a:1:{i:0;s:3:\"All\";}' WHERE ALLOWEDPRIORITYVARS=''");

	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles set ALLOWEDSTATUSVARS='a:1:{i:0;s:3:\"All\";}' WHERE ALLOWEDSTATUSVARS=''");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles set ALLOWEDPRIORITYVARS='a:1:{i:0;s:3:\"All\";}' WHERE ALLOWEDPRIORITYVARS=''");;

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A410TO420() {
	$db_ver_from = "4.1.0";
	$db_ver_to = "4.2.0";
	$sqla = array();
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'SAFE_MODE', '', NOW( ) , 'Enter a semicolon-separated list of super users here (limits other admins from doing scary things)')");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@triggers` CHANGE `action` `action` MEDIUMTEXT NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@failoverquerystore` ADD `microtime_float` decimal(20,4) NOT NULL");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A401TO410() {
	$db_ver_from = "4.0.1";
	$db_ver_to = "4.1.0";
	$sqla = array();
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'SYNC_DISABLED_UNTIL', '', NOW( ) , ' Sync is disabled until this timestamp is met. Setting should not be visible for user.');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'SYNC_TIMEOUT', '30', NOW( ) , 'Number of minutes fail-over synchronisation halts before trying again (low values cause user delays)');");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `USEDASHBOARDASENTRY` ENUM( 'n', 'y' ) NOT NULL , ADD `HIDEOVERDUEFROMDUELIST` ENUM( 'n', 'y' ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `DASHBOARDCACHE` MEDIUMTEXT NOT NULL ;");
	array_push($sqla, "DELETE FROM `PRFX@@@@@@@settings` WHERE setting='FormFinity';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpagescache` ADD INDEX ( `pageid` ) ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpagescache` ADD INDEX ( `formid` ) ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpagescache` ADD INDEX ( `eid` ) ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpagescache` ADD `reportmd5` VARCHAR( 250 ) NOT NULL ;");
	array_push($sqla, "UPDATE PRFX@@@@@@@settings SET value='No' WHERE setting='FAILOVER_CACHEONLY';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` ADD `table` VARCHAR( 250 ) NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `show_on_add_list` ENUM( 'y', 'n' ) NOT NULL ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `IMPORTANTENTITIES` VARCHAR( 255 ) NOT NULL");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A400TO401() {
	$db_ver_from = "4.0.0";
	$db_ver_to = "4.0.1";
	$sqla = array();
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `FORCEFORM` VARCHAR( 100 ) DEFAULT 'no_force' NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `FORCEFORM` VARCHAR( 100 ) DEFAULT 'no_force' NOT NULL");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','addyourcommentshere','Add your comments here:')");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` CHANGE `status` `status` VARCHAR( 250 ) NOT NULL DEFAULT 'open'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@entity` CHANGE `priority` `priority` VARCHAR( 250 ) NOT NULL DEFAULT 'medium'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@statusvars` CHANGE `varname` `varname` VARCHAR( 250 ) NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@priorityvars` CHANGE `varname` `varname` VARCHAR( 250 )NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@webdav_properties` DROP PRIMARY KEY");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@cache` DROP INDEX `value`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `value`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customer` DROP INDEX `cust_address`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` DROP INDEX `name`");

	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` DROP INDEX `SAVEDSEARCHES`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` DROP INDEX `EMAILCREDENTIALS`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` DROP INDEX `SAVEDSEARCHES`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` DROP INDEX `EMAILCREDENTIALS`");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='nomessages'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='messageinbox'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='last5messages'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='incomingmessages'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='composenewmessage'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='yourmessagewassend'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='sendmessage'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='deleteallreadfrominbox'");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@languages WHERE TEXTID='deleteallfrominbox'");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','nomessages','No messages')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','messageinbox','Message inbox')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','last5messages','Last 5 messages')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','incomingmessages','Incoming messages')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','composenewmessage','Compose new message')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','yourmessagewassend','Your message was send as message id :')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','sendmessage','Send message')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','deleteallreadfrominbox','Delete read messages from inbox')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','deleteallfrominbox','Delete all messages from inbox')");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A343TO400() {
	$db_ver_from = "3.4.3";
	$db_ver_to = "4.0.0";
	$sqla = array();
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@modules` (`mid` MEDIUMINT NOT NULL AUTO_INCREMENT ,`module_name` VARCHAR( 255 ) NOT NULL ,`module_description` TEXT NOT NULL ,`module_add_by` INT NOT NULL ,`module_last_run_result` TEXT NOT NULL ,`module_code` BLOB NOT NULL ,PRIMARY KEY ( `mid` )) TYPE = MYISAM COMMENT = 'Interleave Module code table';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@modules` ADD `module_last_run_date` VARCHAR( 50 ) NOT NULL AFTER `module_last_run_result` ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@modules` ADD `module_add_date` VARCHAR( 50 ) NOT NULL AFTER `module_add_by` ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@modules` CHANGE `module_code` `module_code` MEDIUMBLOB NOT NULL ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` ADD PRIMARY KEY (`id`) ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@extrafields` DROP INDEX `id` ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@languages` ADD PRIMARY KEY (`id`) ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@languages` DROP INDEX `id` ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@phonebook` ADD PRIMARY KEY (`id`) ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@phonebook` DROP INDEX `id` ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@settings` ADD PRIMARY KEY (`settingid`) ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@settings` DROP INDEX `settingid` ");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@sessions` ADD UNIQUE (`temp`); ");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` )VALUES ('', 'ENABLE_SUBSCRIPTIONS', 'Yes', NOW( ) , 'Yes to enable outside users to sign up for an account, No to disable. Needs a master acount!');");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` )VALUES ('', 'FAILOVER_CACHEONLY', 'No', NOW( ) , 'When set to Yes, Interleave will cache al queries instead of replicating them. Replication will only be done by the cron job.');");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@searchindex` (`eid` bigint(20) NOT NULL, `value` varchar(255) NOT NULL, `tp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,  KEY `eid` (`eid`)) TYPE=MyISAM COMMENT='Interleave Search words index';");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@settings(setting,value,discription) VALUES('ENABLEINDEXEDSEARCHING','Yes','Set to Yes will enable fast indexed searching. You need to index your database now and than for this!');");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@entity ADD INDEX tp(tp);");
	array_push($sqla, "UPDATE PRFX@@@@@@@settings SET discription ='The main font size (in pixels)' WHERE discription ='The main font size';");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@settings WHERE setting='SHOWPDWASLINK';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@internalmessages` ADD `subject` VARCHAR( 255 ) NOT NULL AFTER `from` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@internalmessages` ADD `dub_count` SMALLINT NOT NULL AFTER `read` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@internalmessages` CHANGE `dub_count` `dub_count` SMALLINT( 6 ) NOT NULL DEFAULT '1'");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@publishedpages` (  `id` int(11) NOT NULL auto_increment,  `repository` smallint(6) NOT NULL,  `visible_from` bigint(9) NOT NULL,  `visible_until` bigint(9) NOT NULL,  `as_user` smallint(6) NOT NULL,  `description` varchar(255) default NULL,  `template` mediumint(9) NOT NULL,  PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Interleave Published pages table';");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@publishedpagescache` (`pagecacheid` INT NOT NULL AUTO_INCREMENT ,`pageid` INT NOT NULL ,`userid` INT NOT NULL ,`formid` INT NOT NULL ,`eid` INT NOT NULL ,`content` LONGTEXT NOT NULL ,PRIMARY KEY ( `pagecacheid` )) TYPE = MYISAM COMMENT = 'Interleave Cache of published pages';");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` ADD `type` ENUM( 'page', 'report' ) DEFAULT 'page' NOT NULL AFTER `as_user` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@publishedpages` ADD `report_query` LONGTEXT NOT NULL AFTER `type` ;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@sessions` DROP INDEX `temp` ;");
	array_push($sqla, "DROP TABLE IF EXISTS PRFX@@@@@@@users;");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `HIDEFROMASSIGNEEANDOWNERLISTS` ENUM( 'n', 'y' ) NOT NULL");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','nomessages','No messages')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','messageinbox','Message inbox')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','last5messages','Last 5 messages')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','incomingmessages','Incoming messages')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','composenewmessage','Compose new message')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','yourmessagewassend','Your message was send as message id :')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','sendmessage','Send message')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','deleteallreadfrominbox','Delete read messages from inbox')");
	array_push($sqla, "INSERT INTO PRFX@@@@@@@languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','deleteallfrominbox','Delete all messages from inbox')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DISABLE_BINARY_SEARCH', 'No', NOW( ) , 'Set this to Yes to disable automatic searching through binary attachments.')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` ADD `on_form` VARCHAR( 10 ) DEFAULT 'all' NOT NULL AFTER `template_fileid`");

	array_push($sqla,"DELETE FROM PRFX@@@@@@@internalmessages WHERE body LIKE '%processes%'");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A342TO343() {
	$db_ver_from = "3.4.2";
	$db_ver_to = "3.4.3";
	$sqla = array();
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD `ALLOWEDSTATUSVARS` LONGTEXT NOT NULL , ADD `ALLOWEDPRIORITYVARS` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `ALLOWEDSTATUSVARS` LONGTEXT NOT NULL , ADD `ALLOWEDPRIORITYVARS` LONGTEXT NOT NULL");
	array_push($sqla, "UPDATE `PRFX@@@@@@@userprofiles` SET `ALLOWEDSTATUSVARS`='a:1:{i:0;s:3:\"All\";}'");
	array_push($sqla, "UPDATE `PRFX@@@@@@@loginusers` SET `ALLOWEDSTATUSVARS`='a:1:{i:0;s:3:\"All\";}'");
	array_push($sqla,"UPDATE PRFX@@@@@@@settings SET value='Tahoma' WHERE setting='DFT_FONT'");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('Mass_Update', 'No', 'Set this to yes to enable mass entity updates using checkboxes on the main list')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` ADD `comment` MEDIUMTEXT NOT NULL");
	array_push($sqla,"DELETE FROM `PRFX@@@@@@@settings` WHERE setting='FormFinity'");
	//array_push($sqla,"CREATE TABLE `PRFX@@@@@@@entityformcache` (`formcacheid` mediumint(9) NOT NULL auto_increment,`eid` mediumint(9) NOT NULL,`formid` mediumint(9) NOT NULL,`user` mediumint(9) NOT NULL,`content` longtext NOT NULL,`timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, PRIMARY KEY  (`formcacheid`)) TYPE=MyISAM COMMENT='Interleave Cached parsed forms'");
	array_push($sqla,"CREATE TABLE `PRFX@@@@@@@entityformcache` (`formcacheid` mediumint(9) NOT NULL auto_increment,`eid` mediumint(9) NOT NULL,`formid` mediumint(9) NOT NULL,`user` mediumint(9) NOT NULL,`content` longtext NOT NULL,`timestamp` timestamp(14) NOT NULL,PRIMARY KEY  (`formcacheid`)     ) TYPE=MyISAM COMMENT='Interleave Cached parsed forms'");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'PaginateMainEntityList', '30', NOW( ) , '0 for no pagination, [number] for max number of entities per page.');");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'MAINTENANCE_MODE', 'No', NOW( ) , 'When set to Yes, only administrators can log in. Required when upgrading.');");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@failoverquerystore` (`id` mediumint(9) NOT NULL auto_increment,`query` mediumblob NOT NULL,`targethost` varchar(100) NOT NULL, `lockhash` varchar(100) NOT NULL, PRIMARY KEY  (`id`)) TYPE=MyISAM COMMENT='Interleave Fail-over queries to replicate'");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@languages` ( `id` , `LANGID` , `TEXTID` , `TEXT` )VALUES ('', 'ENGLISH', 'maintenancemodeison', 'This repository is in maintenance mode. You cannot enter it now, please try again later.')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@settings(setting, value, discription) VALUES('ONEENTITYPERCUSTOMER', 'No', 'When enabled, only one entity per customer may exist.')");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A341TO342() {
	$db_ver_from = "3.4.1";
	$db_ver_to = "3.4.2";
	$sqla = array();
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'NOBARSWINDOW', 'No', NOW( ) , 'Set this option to Yes to force a no-bars (fullscreen) window');");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@customaddons ADD KEY eid (eid)");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@entity ADD KEY formid (formid)");
	// Database cleanup thingies
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@accesscache` CHANGE `cacheid` `cacheid` INT NOT NULL AUTO_INCREMENT ,CHANGE `user` `user` INT NOT NULL DEFAULT '0',CHANGE `eidcid` `eidcid` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@binfiles` CHANGE `fileid` `fileid` INT NOT NULL AUTO_INCREMENT ,CHANGE `koppelid` `koppelid` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@blobs` CHANGE `fileid` `fileid` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@cache` CHANGE `stashid` `stashid` INT NOT NULL AUTO_INCREMENT");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT , CHANGE `eid` `eid` INT NOT NULL DEFAULT '0', CHANGE `name` `name` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@ejournal` CHANGE `lasteditby` `lasteditby` INT NOT NULL DEFAULT '0',CHANGE `createdby` `createdby` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` CHANGE `lasteditby` `lasteditby` INT NOT NULL DEFAULT '0',CHANGE `createdby` `createdby` INT NOT NULL DEFAULT '0',CHANGE `parent` `parent` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entitylocks` CHANGE `lockid` `lockid` INT NOT NULL AUTO_INCREMENT ,CHANGE `lockon` `lockon` INT NOT NULL DEFAULT '0',CHANGE `lockby` `lockby` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT");

	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@internalmessages` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT ,CHANGE `to` `to` INT NOT NULL DEFAULT '0',CHANGE `from` `from` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@journal` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT ,CHANGE `eid` `eid` INT NOT NULL DEFAULT '0',CHANGE `user` `user` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@languages` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@settings` CHANGE `settingid` `settingid` INT NOT NULL AUTO_INCREMENT");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` CHANGE `tid` `tid` INT NOT NULL AUTO_INCREMENT ,CHANGE `template_fileid` `template_fileid` INT NOT NULL DEFAULT '0',CHANGE `report_fileid` `report_fileid` INT NOT NULL DEFAULT '0'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@uselog` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@binfiles` COMMENT = 'Interleave Binary files'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@ejournal` DROP INDEX `id`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@users` DROP INDEX `id`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` DROP INDEX `EMAILCREDENTIALS_2`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` DROP INDEX `EMAILCREDENTIALS_3`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@calendar` CHANGE `calendarid` `calendarid` MEDIUMINT NOT NULL AUTO_INCREMENT");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@phonebook` CHANGE `id` `id` MEDIUMINT NOT NULL AUTO_INCREMENT");
	array_push($sqla,"DELETE FROM PRFX@@@@@@@settings WHERE setting='ShowShortKeyLegend'");
	array_push($sqla, "ALTER TABLE PRFX@@@@@@@binfiles ADD INDEX ( `filetype` ) ");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A340TO341() {
	$db_ver_from = "3.4.0";
	$db_ver_to = "3.4.1";
	$sqla = array();
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@blobs` ( `fileid` bigint(20) NOT NULL, `content` mediumblob NOT NULL,PRIMARY KEY  (`fileid`) ) TYPE=MyISAM COMMENT='Blob stand-alone table';");
	//array_push($sqla, "INSERT INTO PRFX@@@@@@@blobs(SELECT fileid, content FROM PRFX@@@@@@@binfiles)");
	//array_push($sqla, "ALTER TABLE PRFX@@@@@@@binfiles DROP content");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@settings WHERE setting='STASH'");
        array_push($sqla, "CREATE TABLE PRFX@@@@@@@accesscache (  cacheid bigint(20) NOT NULL auto_increment,  user bigint(20) NOT NULL,  type enum('e','c') NOT NULL,  eidcid bigint(20) NOT NULL,  result enum('nok','readonly','ok') NOT NULL,  PRIMARY KEY  (cacheid),  KEY user (user),  KEY type (type)) TYPE=MyISAM COMMENT='Interleave Access cache table'");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'USE_EXTENDED_CACHE', 'Yes', NOW( ) , 'Use extensive access rights and extra fields caching. Improves performance.')");
// ----------------------------ooo
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `ELISTLAYOUT` TEXT NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `CLISTLAYOUT` TEXT NOT NULL");
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@contactmoments` (`id` INT NOT NULL AUTO_INCREMENT ,`eidcid` INT NOT NULL ,`type` ENUM( 'entity', 'customer' ) NOT NULL ,`user` INT NOT NULL ,`meta` TEXT NOT NULL ,`body` LONGTEXT NOT NULL ,`date` TIMESTAMP NOT NULL ,PRIMARY KEY ( `id` ) ) TYPE = MYISAM COMMENT = 'Interleave Contact moments journal'");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@contactmoments` ADD `to` TEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD `storetype` ENUM( 'default', '3rd_table','3d_table_popup' ) NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD `accessarray` LONGTEXT NOT NULL ");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'CHECKFORDOUBLEADDS', 'Yes', NOW( ) , 'Interleave checks if an entity is not added twice within an hour. If this bothers you, disable this check by setting this to No.')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD `size` INT NOT NULL");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A332TO340() {
	$db_ver_from = "3.3.2";
	$db_ver_to = "3.4.0";
	$sqla = array();
	array_push($sqla, "CREATE TABLE `PRFX@@@@@@@internalmessages` (`id` BIGINT NOT NULL AUTO_INCREMENT ,`to` BIGINT NOT NULL ,`from` BIGINT NOT NULL ,`time` TIMESTAMP NOT NULL ,`read` ENUM( 'n', 'y' ) NOT NULL ,`body` MEDIUMTEXT NOT NULL ,PRIMARY KEY ( `id` ) ,INDEX ( `to` ) ) TYPE = MYISAM COMMENT = 'Interleave Internal messages (user-to-user)'");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'FormFinity', 'No', NOW( ) , 'When set to Yes, entities will \'remember\' what form was used to create them, and the entity will always show up in that form.')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD `formid` MEDIUMINT DEFAULT '0' NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@ejournal` ADD `formid` MEDIUMINT DEFAULT '0' NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD `ADDFORMS` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `ADDFORMS` LONGTEXT NOT NULL");
	// NIEUW

	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'UNIFIED_FROMADDRESS', '', NOW( ) , 'An address entered here, will *always* overwrite the from-address in mails. All mails will have this from-address.')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'CUSTOMCUSTOMERFORM', '', NOW( ) , 'When you enter the (valid) number of a customer HTML-form here, all customer records will be shown in that form.')");
	array_push($sqla, "UPDATE PRFX@@@@@@@loginusers SET ADDFORMS ='a:1:{i:0;s:7:\"default\";}'");
	array_push($sqla, "UPDATE PRFX@@@@@@@userprofiles SET ADDFORMS ='a:1:{i:0;s:7:\"default\";}'");

	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DISPLAYNUMSUMINMAINLIST', 'Yes', NOW( ) , 'When set to Yes, the total value of numeric fields will be displayed under the main entity list.')");


	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A331TO332() {
	$db_ver_from = "3.3.1";
	$db_ver_to = "3.3.2";
	$sqla = array();

	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD `parent` BIGINT DEFAULT '0' NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD `sort` ENUM( 'n', 'y' ) NOT NULL");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableEntityRelations', 'No', NOW( ) , 'Set this value to Yes to enable entity relations.')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'HideChildsFromMainList', 'No', NOW( ) , 'When enabled, child entities will no longer show up on the main list.')");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'LDAP_SERVER', '', NOW( ) , 'The name of the LDAP server')");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'LDAP_PORT', '389', NOW( ) , 'The port of the LDAP server; secure=636, non-secure=389 (Default)')");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'LDAP_PREFIX', '', NOW( ) , 'The prefix to use before a username on the LDAP server. End with 1 backslash, not two.')");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'LDAP_AUTO_ADD_USERS', 'NO', NOW( ) , 'Set this to Yes to auto-add an Interleave useraccount when an unknown but LDAP-authenticated user logs in.')");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'RSS_FEEDS', '', NOW( ) , 'The list of RSS feeds to serve. No list, no RSS.')");
	array_push($sqla, "UPDATE `PRFX@@@@@@@settings` SET `datetime` = NOW( ) ,`discription`='The method to use for authentication. ALWAYS: user must exist in Interleave. HTTP REALM: already authenticated users can log in without a password (INTRANET). LDAP: authentications with an LDAP server (allso fill in LDAP_SERVER, LDAP_PORT, LDAP_PREFIX).' WHERE `setting` ='AUTH_TYPE' LIMIT 1");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `LIMITTOCUSTOMERS` LONGTEXT NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@userprofiles` ADD `LIMITTOCUSTOMERS` LONGTEXT NOT NULL");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A330TO331() {
	$db_ver_from = "3.3.0";
	$db_ver_to = "3.3.1";
	$sqla = array();

	//array_push($sqla,"UPDATE `PRFX@@@@@@@settings` SET `datetime` = NOW( ) ,`discription` = 'NO LONGER USED! Use miscellaneous triggers!' WHERE `setting` ='BODY_ENTITY_ADD' LIMIT 1");
	array_push($sqla,"SELECT * FROM PRFX@@@@@@@loginusers");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A320TO330() {
	$db_ver_from = "3.2.0";
	$db_ver_to = "3.3.0";
	$sqla = array();

	// Fix the limited extra field option field
	//array_push($sqla,"UPDATE `PRFX@@@@@@@settings` SET `datetime` = NOW( ) ,`discription` = 'NO LONGER USED! Use miscellaneous triggers!' WHERE `setting` ='BODY_ENTITY_ADD' LIMIT 1");
	//array_push($sqla,"UPDATE `PRFX@@@@@@@settings` SET `datetime` = NOW( ) ,`discription` = 'NO LONGER USED! Use miscellaneous triggers!' WHERE `setting` ='BODY_ENTITY_EDIT' LIMIT 1");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `options` `options` LONGTEXT NOT NULL");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ENTITY_ADD_FORM', 'Default', NOW( ) , 'The HTML form template to use when a normal user adds an entity')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BODY_URGENTMESSAGE', '', NOW( ) , 'When set, this message will be displayed above <strong>all</strong> pages. Only use this for very urgent matters. ')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'AUTH_TYPE', 'Interleave Only', NOW( ) , 'The method to use for authentication. In all cases the user must exist in Interleave. HTTP REALM will allow a user who is already authenticated against the webserver to log in without a password. USE ONLY FOR INTRANETS!')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ENTITY_EDIT_FORM', 'Default', NOW( ) , 'The HTML form template to use when a normal user edits an entity')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ENTITY_LIMITED_ADD_FORM', 'Default', NOW( ) , 'The HTML form template to use when a limited user adds an entity')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ENTITY_LIMITED_EDIT_FORM', 'Default', NOW( ) , 'The HTML form template to use when a limited user edits an entity')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BODY_LIMITEDHEADER', '', NOW( ) , 'This HTML template will be shown at the top of the limited interface')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'SHOW_ADMIN_TOOLTIPS', 'Yes', NOW( ) , 'Wether or not to display tool-tips in the administrative section.')");
	array_push($sqla,"CREATE TABLE PRFX@@@@@@@userprofiles (  id int(11) NOT NULL auto_increment,  name varchar(50) NOT NULL default '',  ENTITYADDFORM varchar(50) NOT NULL default '',  ENTITYEDITFORM varchar(50) NOT NULL default '',  active enum('yes','no') NOT NULL default 'yes',  CLLEVEL varchar(50) NOT NULL default 'ro',  RECEIVEDAILYMAIL enum('No','Yes') NOT NULL default 'No',  RECEIVEALLOWNERUPDATES enum('n','y') NOT NULL default 'n',  RECEIVEALLASSIGNEEUPDATES enum('n','y') NOT NULL default 'n',  HIDEADDTAB char(1) NOT NULL default '',  HIDECSVTAB char(1) NOT NULL default '',  HIDESUMMARYTAB char(1) NOT NULL default '',  HIDEENTITYTAB char(1) NOT NULL default '',  HIDEPBTAB char(1) NOT NULL default '',  SHOWDELETEDVIEWOPTION char(1) NOT NULL default '',  HIDECUSTOMERTAB char(1) NOT NULL default '',  SAVEDSEARCHES longtext NOT NULL,  EMAILCREDENTIALS longtext NOT NULL,  PRIMARY KEY  (id),  KEY name (name),  FULLTEXT KEY SAVEDSEARCHES (SAVEDSEARCHES),  FULLTEXT KEY EMAILCREDENTIALS (EMAILCREDENTIALS)) TYPE=MyISAM COMMENT='Interleave User profile definition table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD `PROFILE` VARCHAR( 10 ) NOT NULL AFTER `password`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `ENTITYADDFORM` VARCHAR( 10 ) NOT NULL ,ADD `ENTITYEDITFORM` VARCHAR( 10 ) NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` CHANGE `name` `name` BIGINT( 20 ) DEFAULT '0' NOT NULL");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `name`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `name_2`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `value`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `value_2`");
	array_push($sqla, "ALTER TABLE `PRFX@@@@@@@customaddons` ADD INDEX ( `name` )");

	array_push($sqla, "CREATE INDEX val ON PRFX@@@@@@@customaddons (value(20))");
	array_push($sqla, "DELETE FROM PRFX@@@@@@@settings WHERE setting='logo'");
	array_push($sqla, "DROP TABLE IF EXIST PRFX@@@@@@@users");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@binfiles` VALUES (5470, 0, 0x3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e3c464f4e542073697a653d2b313e4a6f652773204f776e2048656c706465736b202d207469636b6574266e6273703b40454944403a204043415445474f52594020234c4f434b49434f4e23266e6273703b3c2f464f4e543e3c2f4c4547454e443e0d0a3c5441424c452077696474683d22393025223e0d0a3c54424f44593e0d0a3c54523e0d0a3c54443e0d0a3c5441424c453e0d0a3c54424f44593e0d0a3c54523e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e557365722f637573746f6d65723c2f4c4547454e443e23435553544f4d4552233c2f4649454c445345543e3c2f54443e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e537461747573266e6273703b3c2f4c4547454e443e23535441545553233c2f4649454c445345543e203c2f54443e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e5072696f726974793c2f4c4547454e443e235052494f52495459233c2f4649454c445345543e203c2f54443e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e53686f72742070726f626c656d206465736372697074696f6e266e6273703b3c2f4c4547454e443e2343415445474f525923203c2f4649454c445345543e203c2f54443e0d0a3c54443e3c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e0d0a3c5441424c452063656c6c53706163696e673d312063656c6c50616464696e673d322077696474683d22313030252220626f726465723d303e0d0a3c54424f44593e0d0a3c54523e0d0a3c54443e23434f4e54454e545323203c2f54443e0d0a3c54443e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4f776e65723c2f4c4547454e443e234f574e455223203c2f4649454c445345543e203c42523e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e41737369676e6565266e6273703b3c2f4c4547454e443e2341535349474e454523203c2f4649454c445345543e200d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4475652064617465266e6273703b3c2f4c4547454e443e234455454441544523203c2f4649454c445345543e200d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4475652074696d65266e6273703b3c2f4c4547454e443e2344554554494d4523203c2f4649454c445345543e3c42523e234a4f55524e414c49434f4e2320235245504f525449434f4e23202350444649434f4e23202341435449434f4e2320234c4f434b49434f4e2320234152524f5753233c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e3c42523e0d0a3c5441424c452077696474683d22333025223e0d0a3c54424f44593e0d0a3c54523e0d0a3c54443e526561642d6f6e6c7920746f206f746865722075736572733c2f54443e0d0a3c54443e23524541444f4e4c59424f5823266e6273703b3c2f54443e3c2f54523e0d0a3c54523e0d0a3c54443e507269766174653c2f54443e0d0a3c54443e2350524956415445424f58233c2f54443e3c2f54523e0d0a3c54523e0d0a3c54443e44656c657465643c2f54443e0d0a3c54443e2344454c455445424f58233c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e0d0a3c5441424c453e0d0a3c54424f44593e0d0a3c54523e0d0a3c544420636f6c5370616e3d363e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4174746163682066696c65266e6273703b3c2f4c4547454e443e2346494c45424f582320266e6273703b266e6273703b266e6273703b266e6273703b203c2f4649454c445345543e200d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e43757272656e742066696c6573266e6273703b3c2f4c4547454e443e2346494c454c4953542320266e6273703b266e6273703b266e6273703b266e6273703b203c2f4649454c445345543e3c2f54443e3c2f54443e3c2f54523e2353415645425554544f4e23203c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e3c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e3c2f4649454c445345543e20, 'Joes helpdesk - edit entity form template (example)', '2005-08-07 20:01:52', 0, 'TEMPLATE_HTML_FORM', 'Hidde Fennema', 'in', 0, 'entity', 'Joes edit entity template (example)')");
	array_push($sqla, "INSERT INTO `PRFX@@@@@@@binfiles` VALUES (5473, 0, 0x3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e3c464f4e542073697a653d2b313e4a6f652773204f776e2048656c706465736b202d206164642061206e6577200d0a7469636b65743c2f464f4e543e3c2f4c4547454e443e0d0a3c5441424c452077696474683d22393025223e0d0a3c54424f44593e0d0a3c54523e0d0a3c54443e0d0a3c5441424c453e0d0a3c54424f44593e0d0a3c54523e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e557365722f637573746f6d65723c2f4c4547454e443e23435553544f4d4552233c2f4649454c445345543e3c2f54443e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e537461747573266e6273703b3c2f4c4547454e443e23535441545553233c2f4649454c445345543e203c2f54443e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e5072696f726974793c2f4c4547454e443e235052494f52495459233c2f4649454c445345543e203c2f54443e0d0a3c54442076416c69676e3d746f703e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e53686f72742070726f626c656d206465736372697074696f6e266e6273703b3c2f4c4547454e443e2343415445474f525923200d0a3c2f4649454c445345543e203c2f54443e0d0a3c54443e3c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e0d0a3c5441424c452063656c6c53706163696e673d312063656c6c50616464696e673d322077696474683d22313030252220626f726465723d303e0d0a3c54424f44593e0d0a3c54523e0d0a3c54443e23434f4e54454e545323203c2f54443e0d0a3c54443e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4f776e65723c2f4c4547454e443e234f574e455223203c2f4649454c445345543e203c42523e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e41737369676e6565266e6273703b3c2f4c4547454e443e2341535349474e454523203c2f4649454c445345543e200d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4475652064617465266e6273703b3c2f4c4547454e443e234455454441544523203c2f4649454c445345543e200d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4475652074696d65266e6273703b3c2f4c4547454e443e2344554554494d4523200d0a3c2f4649454c445345543e3c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e3c42523e0d0a3c5441424c452077696474683d22333025223e0d0a3c54424f44593e0d0a3c54523e0d0a3c54443e526561642d6f6e6c7920746f206f746865722075736572733c2f54443e0d0a3c54443e23524541444f4e4c59424f5823266e6273703b3c2f54443e3c2f54523e0d0a3c54523e0d0a3c54443e507269766174653c2f54443e0d0a3c54443e2350524956415445424f58233c2f54443e3c2f54523e0d0a3c54523e0d0a3c54443e3c2f54443e0d0a3c54443e3c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e0d0a3c5441424c453e0d0a3c54424f44593e0d0a3c54523e0d0a3c544420636f6c5370616e3d363e0d0a3c4649454c445345543e3c4c4547454e4420616c69676e3d6c6566743e4174746163682066696c65266e6273703b3c2f4c4547454e443e2346494c45424f5823200d0a266e6273703b266e6273703b266e6273703b266e6273703b203c2f4649454c445345543e203c2f54443e3c2f54443e3c2f54523e2353415645425554544f4e23200d0a3c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e3c2f54443e3c2f54523e3c2f54424f44593e3c2f5441424c453e3c2f4649454c445345543e20, 'Joes helpdesk - new entity form template (example)', '2005-08-07 16:22:43', 0, 'TEMPLATE_HTML_FORM', 'Hidde Fennema', 'in', 0, 'entity', 'Joes helpdesk - new entity form template (example)')");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A310TO320() {
	$db_ver_from = "3.1.0";
	$db_ver_to = "3.2.0";
	$sqla = array();
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'PersonalTabs', '', NOW( ) , 'Set this to Yes to disable the main entity comment field')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `options` `options` LONGTEXT NOT NULL");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DisableCommentField', 'No', NOW( ) , 'Set this to Yes to disable the main entity comment field')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'REQUIREDDEFAULTFIELDS', '', NOW( ) , 'Set this to Yes to disable the main entity comment field')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EMAILINBOX', '', NOW( ) , 'The credentials for the read-only access to an POP3 e-mail inbox')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD `EMAILCREDENTIALS` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD FULLTEXT (`EMAILCREDENTIALS`)");
	// Database optimisation
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@binfiles` DROP INDEX `fileid`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@binfiles` DROP INDEX `type`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `eid`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `type`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `deleted_2`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` DROP INDEX `value_2`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customer` DROP INDEX `id`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` DROP INDEX `id`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` DROP INDEX `id`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@sessions` DROP INDEX `id`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@webdav_locks` DROP INDEX `token`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@webdav_locks` DROP INDEX `path_2`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@webdav_locks` DROP INDEX `path_3`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@webdav_properties` DROP INDEX `path`");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@binfiles` TYPE=MyISAM, COMMENT='Interleave Binairy files'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@calendar` TYPE=MyISAM, COMMENT='Interleave Calendar entries'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` TYPE=MyISAM, COMMENT='Interleave Extra fields sequential table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customer` TYPE=MyISAM, COMMENT='Interleave Main customer table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` TYPE=MyISAM, COMMENT='Interleave Main entity table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` TYPE=MyISAM, COMMENT='Interleave Extra field definitions'");

	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@journal` TYPE=MyISAM, COMMENT='Interleave Entity/customer journal'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` TYPE=MyISAM, COMMENT='Interleave User definition table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@phonebook` TYPE=MyISAM, COMMENT='Interleave Phone book table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@priorityvars` TYPE=MyISAM, COMMENT='Interleave Priority definitions table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@sessions` TYPE=MyISAM, COMMENT='Interleave Session table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@settings` TYPE=MyISAM, COMMENT='Interleave Main settings table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@statusvars` TYPE=MyISAM, COMMENT='Interleave Status definitions table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` TYPE=MyISAM, COMMENT='Interleave Entity value change trigger table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@uselog` TYPE=MyISAM, COMMENT='Interleave Main activity log table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@webdav_locks` TYPE=MyISAM, COMMENT='Interleave Webdav file locks table'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@webdav_properties` TYPE=MyISAM, COMMENT='Interleave Webdav properties'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` MODIFY COLUMN `name` VARCHAR(50) NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` MODIFY COLUMN `name` VARCHAR(250) NOT NULL");
	//array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` MODIFY COLUMN `options` VARCHAR(250) NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` MODIFY COLUMN `defaultval` VARCHAR(250) DEFAULT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@uselog` MODIFY COLUMN `qs` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@calendar` ADD KEY `basicdate` (`basicdate`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@calendar` ADD KEY `datum` (`datum`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` ADD KEY `name` (`name`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entitylocks` ADD KEY `lockepoch` (`lockepoch`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@journal` ADD KEY `type` (`type`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD KEY `name` (`name`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@priorityvars` ADD KEY `varname` (`varname`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@statusvars` ADD KEY `varname` (`varname`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` ADD KEY `to_value` (`to_value`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` ADD KEY `onchange` (`onchange`)");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A300TO310() {
	$db_ver_from = "3.0.0";
	$db_ver_to = "3.1.0";
	$sqla = array();
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'MailMethod', 'smtp', NOW( ) , 'The method to use for sending mail. Can be either sendmail, mail (=PHP mail) or smtp.')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'MailUser', '', NOW( ) , 'The username of your authenticated SMTP-server (only when using authenticated SMTP)')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'MailPass', '', NOW( ) , 'The password of your authenticated SMTP-server (only when using authenticated SMTP)')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'UseWaitingAndDoesntBelongHere', 'No', NOW( ) , 'Set this value to Yes to enable the (old) waiting and doesnt belong here fields')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD `SAVEDSEARCHES` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD FULLTEXT (`SAVEDSEARCHES` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD `defaultval` VARCHAR( 250 ) NOT NULL");
    array_push($sqla,"ALTER TABLE `PRFX@@@@@@@ejournal` ADD INDEX ( `eid` )");
	array_push($sqla,"DELETE FROM `PRFX@@@@@@@settings` WHERE setting='REQUIREDDEFAULTFIELDS'");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'REQUIREDDEFAULTFIELDS', 'No', NOW( ) , 'SHOULD NOT BE VISIBLE')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD `EMAILCREDENTIALS` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD FULLTEXT (`EMAILCREDENTIALS`)");

	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A262TO300() {
	$db_ver_from = "2.6.2";
	$db_ver_to = "3.0.0";
	$sqla = array();

	array_push($sqla,"CREATE TABLE `PRFX@@@@@@@cache` (  `stashid` bigint(20) NOT NULL auto_increment,  `epoch` varchar(20) default NULL,  `value` longtext NOT NULL,  PRIMARY KEY  (`stashid`),  FULLTEXT KEY `value` (`value`)) TYPE=MyISAM COMMENT='Interleave Query cache table'");
	array_push($sqla,"CREATE TABLE `PRFX@@@@@@@extrafields` (`id` BIGINT NOT NULL AUTO_INCREMENT ,`ordering` MEDIUMINT NOT NULL ,`tabletype` ENUM( 'entity', 'customer' ) NOT NULL , `hidden` ENUM( 'n', 'y' ) NOT NULL , `location` ENUM( 'A', 'B', 'C', 'D', 'E' ) NOT NULL, deleted ENUM( 'n', 'y' ) NOT NULL ,`fieldtype` VARCHAR( 50 ) NOT NULL ,`name` VARCHAR( 250 ) NOT NULL ,`options` VARCHAR( 250 ) NOT NULL ,UNIQUE (`id` ),FULLTEXT (`name` ,`options` )) COMMENT = 'Interleave Extra fields'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `location` `location` VARCHAR( 40 ) DEFAULT '' NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `options` `options` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `name` `name` LONGTEXT NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD `forcing` ENUM( 'n', 'y' ) NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` CHANGE `hidden` `hidden` ENUM( 'n', 'y', 'a' ) DEFAULT 'n' NOT NULL");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'FORCEDFIELDSTEXT', 'This message is not configured (see admin section). Probably you missed some fields in your form. ', NOW( ) , 'The message which is displayed when a user did not fill in all required form fields.')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'REQUIREDDEFAULTFIELDS', 'No', NOW( ) , 'SHOULD NOT BE VISIBLE')");
	array_push($sqla,"CREATE TABLE `PRFX@@@@@@@entitylocks` (`lockid` BIGINT NOT NULL AUTO_INCREMENT ,`lockon` BIGINT NOT NULL ,`lockby` BIGINT NOT NULL ,`lockepoch` VARCHAR( 30 ) NOT NULL ,PRIMARY KEY ( `lockid` ) ) COMMENT = 'Interleave Entity record locks'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entitylocks` ADD INDEX ( `lockon` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD INDEX ( `location` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@extrafields` ADD INDEX ( `tabletype` )");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableEntityLocking', 'No', NOW( ) , 'Set this to Yes to enable entity locking to prevent two people from editing the same entity')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DFT_FOREGROUND_COLOR', '#c60', NOW( ) , 'The color of links')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DFT_FORM_COLOR', '#c60', NOW( ) , 'The color form elements and values')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DFT_PLAIN_COLOR', '#000', NOW( ) , 'The color of normal, non-linked, non-form text')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DFT_LEGEND_COLOR', '#3366FF', NOW( ) , 'The color of fieldset legends')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DFT_FONT', 'MS Shell DLG', NOW( ) , 'The main font')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DFT_FONT_SIZE', '11', NOW( ) , 'The main font size')");
//EnableEntityLocking
	Upgrade_Specialfor300($db_ver_from, $db_ver_to, $sqla);
}
function A261TO262() {
	$db_ver_from = "2.6.1";
	$db_ver_to = "2.6.2";
	$sqla = array();

	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` ADD `report_fileid` BIGINT NOT NULL");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A260TO261() {
	$db_ver_from = "2.6.0";
	$db_ver_to = "2.6.1";
	$sqla = array();

	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DisplayNOToptioninfilters', 'No', NOW( ) , 'Set this value to Yes to have all filter drop-down boxes also contain logical NOT-operands, like status NOT open.')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EXTRAFIELDLOCATION', 'B', NOW( ) , 'The location on the main edit entity page were the extra field boxes will appear.')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'AUTOASSIGNINCOMINGENTITIES', 'No', NOW( ) , 'Set this option to Yes to automatically assign incoming entities to the owner of the customer.')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@triggers` ADD `attach` ENUM( 'n', 'y' ) NOT NULL");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A253TO260() {
	$db_ver_from = "2.5.3";
	$db_ver_to = "2.6.0";
	$sqla = array();
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableSingleEntityInvoicing', 'No', NOW( ) , 'Set this value to Yes to enable per-entity invoicing using the invoice icon on the main edit entity page.')");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customer` ADD `readonly` ENUM( 'no', 'yes' ) NOT NULL AFTER `id`");

	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'PDF-ExtraFieldsInTable', 'No', NOW( ) , 'Set this value to Yes to have extra fields in PDF reports show up in a table instead of each value being printed on a new line.')");
	array_push($sqla,"INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableEntityReporting', 'Yes', NOW( ) , 'Set this value to Yes to be able to create per-entity or batch RTF reports (a word-icon will appear on the edit entity page and a link will be added to the main page)')");
	$fp=@fopen("docs_examples/sample_entity_report_template.rtf","r");
	$filecontent=@fread($fp,@filesize("docs_examples/sample_entity_report_template.rtf"));
	@fclose($fp);
	$filecontent = mres($filecontent);
	array_push($sqla,"INSERT INTO PRFX@@@@@@@binfiles(koppelid,content,filename,filesize,filetype,username) VALUES('0','" . $filecontent . "','docs_examples/sample_entity_report_template.rtf','" . @filesize("docs_examples/sample_entity_report_template.rtf") . "','TEMPLATE_REPORT','Upgrade to 2.6.0')");

	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','go','Go!')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','attachindividualtoentity','Attach individual files to entity')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','attachindividualtocustomer','Attach individual files to customer dossier')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','includelog','Include log at end of document')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','rtftemplate','RTF Template')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','createreport','Create an entity report for entity')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','createinvoice','Generate invoice over entity')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','deap','Delete entities after processing')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','apsest','After processing, set entity status to')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','apseot','After processing, set entity owner to')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','apseat','After processing, set entity assignee to')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','apsrft','After processing, set readonly flag to')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','startdate','Start date')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','enddate','End date')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','defVAT','Default VAT')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','IHSwarning','IHS field not found, all qty/hours will be defaulted to 1')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','geninv','Generate invoices')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','selectsingle','Select a single customer')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','abdnya','All but inserted (not yet assigned)')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','ondaa','Only non-deleted and assigned')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','delafterproc','Delete entities after processing')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','onlyactive','Only active')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','donothing','&lt;do nothing&gt;')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','createreports','Generate entity reports')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','lefae','Leave empty for all entities')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','invoiceandmailmerge','Invoice & mailmerge')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','genmailverbose','Generate invoices and mailmerges')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','entreportverbose','Batch-generate entity RTF reports')");
	array_push($sqla,"INSERT INTO PRFX@@@@@@@languages(LANGID,TEXTID,TEXT) VALUES('ENGLISH','alled','All except deleted')");
	array_push($sqla,"CREATE TABLE `PRFX@@@@@@@triggers` (  `tid` bigint(20) NOT NULL auto_increment,  `onchange` varchar(200) NOT NULL default '',  `action` varchar(50) NOT NULL default '',  `to_value` varchar(100) NOT NULL default '',  `template_fileid` bigint(20) NOT NULL default '0',  PRIMARY KEY  (`tid`)) TYPE=MyISAM COMMENT='Event triggers for entity changes'");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@binfiles` ADD `file_subject` VARCHAR( 250 ) NOT NULL");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD SHOWDELETEDVIEWOPTION VARCHAR( 1 ) NOT NULL ");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@loginusers` ADD HIDECUSTOMERTAB VARCHAR( 1 ) NOT NULL ");
	array_push($sqla,"ALTER TABLE PRFX@@@@@@@customaddons ADD FULLTEXT KEY `name_crm` (`name`)");
	array_push($sqla,"ALTER TABLE PRFX@@@@@@@customaddons ADD KEY `type_crm` (`type`)");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@settings` ADD INDEX ( `setting` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@binfiles` ADD INDEX ( `type` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `assignee` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `owner` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `sqldate` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `CRMcustomer` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `deleted` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `openepoch` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@entity` ADD INDEX ( `closeepoch` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customaddons` ADD INDEX ( `deleted` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customer` ADD INDEX ( `custname` )");
	array_push($sqla,"ALTER TABLE `PRFX@@@@@@@customer` ADD INDEX ( `active` )");
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A252TO253() {
	$db_ver_from = "2.5.2";
	$db_ver_to = "2.5.3";
	$sqla[0] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ForceSecureHTTP', 'No', NOW( ) , 'If set to yes, Interleave will redirect the user to the HTTPS equivalent of the URL he/she is using, to force secure browsing. Your webserver must be configured to accept this.')";
	$sqla[1] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BODY_MainPageMessage', '', NOW( ) , 'When set, this message will be displayed on the main page.')";
	$sqla[2] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'InvoiceNumberPrefix', '[unconfigured]', NOW( ) , 'Some text to prefix invoice numbers with')";
	$sqla[3] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'NextInvoiceNumberCounter', '0', NOW( ) , 'The invoice number counter (not accessable)')";
	$sqla[4] = "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `type` ENUM( 'entity', 'cust' ) DEFAULT 'entity' NOT NULL";
	$sqla[5] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'InvoiceNumberPrefix', '[unconfigured]', NOW( ) , 'Some text to prefix invoice numbers with')";
	$fp=@fopen("docs_examples/sample_invoice_template_multiple_VAT.rtf","r");
	$filecontent=@fread($fp,@filesize("docs_examples/sample_invoice_template_multiple_VAT.rtf"));
	@fclose($fp);
	$filecontent = mres($filecontent);
	$sqla[6] = "INSERT INTO PRFX@@@@@@@binfiles(koppelid,content,filename,filesize,filetype,username) VALUES('0','" . $filecontent . "','docs_examples/sample_invoice_template_multiple_VAT.rtf','" . @filesize("docs_examples/sample_invoice_template_multiple_VAT.rtf") . "','TEMPLATE_INVOICE','Upgrade to 2.5.3')";
	$fp=@fopen("docs_examples/sample_invoice_template_single_VAT.rtf","r");
	$filecontent=@fread($fp,@filesize("docs_examples/sample_invoice_template_single_VAT.rtf"));
	@fclose($fp);
	$filecontent = mres($filecontent);
	$sqla[7] = "INSERT INTO PRFX@@@@@@@binfiles(koppelid,content,filename,filesize,filetype,username) VALUES('0','" . $filecontent . "','docs_examples/sample_invoice_template_single_VAT.rtf','" . @filesize("docs_examples/sample_invoice_template_single_VAT.rtf") . "','TEMPLATE_INVOICE','Upgrade to 2.5.3')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A251TO252() {
	$db_ver_from = "2.5.1";
	$db_ver_to = "2.5.2";
	$sqla[0] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('ShowMainPageLinks', 'aHR0cDovL2NybS5pdC1jb21iaW5lLmNvbQ==', NOW( ) , 'Some links to show on the main page. Leave empty for no links')";
	$sqla[1] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('EnableMailMergeAndInvoicing', 'No', NOW( ) , 'Set to Yes to enable mail merges and invoicing (even then, only for admins)')";
	$fp=@fopen("docs_examples/sample_invoice_template.rtf","r");
	$filecontent=@fread($fp,@filesize("docs_examples/sample_invoice_template.rtf"));
	@fclose($fp);
	$filecontent = mres($filecontent);
	$sqla[2] = "INSERT INTO PRFX@@@@@@@binfiles(koppelid,content,filename,filesize,filetype,username) VALUES('0','" . $filecontent . "','docs_examples/sample_invoice_template.rtf','" . @filesize("docs_examples/sample_invoice_template.rtf") . "','TEMPLATE_INVOICE','Upgrade to 2.5.2')";
	$fp=@fopen("docs_examples/sample_mailmerge_template.rtf","r");
	$filecontent=@fread($fp,@filesize("docs_examples/sample_mailmerge_template.rtf"));
	@fclose($fp);
	$filecontent = mres($filecontent);
	$sqla[3] = "INSERT INTO PRFX@@@@@@@binfiles(koppelid,content,filename,filesize,filetype,username) VALUES('0','" . $filecontent . "','docs_examples/sample_mailmerge_template.rtf','" . @filesize("docs_examples/sample_mailmerge_template.rtf") . "','TEMPLATE_MAILMERGE','Upgrade to 2.5.2')";
	$sqla[4] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('DefaultVAT', '19', NOW( ) , 'Default VAT percentage (only for use with invoicing)')";
	unset($filecontent);
	$sqla[5] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('subject_new_entity', 'A new entity was added to repository @TITLE@ (@CATEGORY@)', NOW( ) , 'The subject of the mail which is send when a new entity is added')";
	$sqla[6] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('subject_customer_couple', 'Your customer got a new entity coupled in repository @TITLE@', NOW( ) , 'The subject of the mail which is send to a customer owner when a new entity is coupled to his/her customer')";
	$sqla[7] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('subject_update_entity', 'One of your entities was updated in repository @TITLE@', NOW( ) , 'The subject of the mail which is send to a user owner when his/her entity was updated')";
	$sqla[8] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('subject_alarm', 'Alarm notification for entity @ENTITYID@ (@CATEGORY@)', NOW( ) , 'The subject of the mail which is send to a user owner when his/her entity reaches an alarm date')";
	$sqla[9] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BODY_EMAILINSERT_REPLY', '<p><strong>Your e-mail was added to repository @TITLE@</strong><br></p> <p> The number is : @EID@ Number of attachments saved: @NUM_ATTM@ </p>', NOW( ) , 'The body of the e-mail which is send as a reply to people who use the email_in script to log an entity')";
	$sqla[10] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'SUBJECT_EMAILINSERT_REPLY', 'Your e-mail to Interleave was saved under number @EID@ in repository @TITLE@', NOW( ) , 'The subject of the e-mail which is send as a reply to people who use the email_in script to log an entity')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A250TO251() {
	$db_ver_from = "2.5.0";
	$db_ver_to = "2.5.1";
	$sqla[0] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `HIDEADDTAB` VARCHAR( 1 ) NOT NULL ";
	$sqla[1] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `HIDECSVTAB` VARCHAR( 1 ) NOT NULL ";
	$sqla[2] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `HIDESUMMARYTAB` VARCHAR( 1 ) NOT NULL ";
	$sqla[3] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `HIDEENTITYTAB` VARCHAR( 1 ) NOT NULL ";
	$sqla[4] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `HIDEPBTAB` VARCHAR( 1 ) NOT NULL ";
	$sqla[5] = "ALTER TABLE `PRFX@@@@@@@entity` ADD `duetime` VARCHAR( 4 ) NOT NULL ";
	$sqla[6] = "ALTER TABLE `PRFX@@@@@@@ejournal` ADD `duetime` VARCHAR( 4 ) NOT NULL ";
	$sqla[7] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('HIDEADDTAB', 'No', NOW( ) , 'Set this to Yes to hide the second tab used to add entities')";
	$sqla[8] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('HIDECSVTAB', 'No', NOW( ) , 'Set this to Yes to hide the CSV tab used to download Interleave exports')";
	$sqla[9] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('HIDEPBTAB', 'No', NOW( ) , 'Set this to Yes to hide the phone book tab')";
	$sqla[10] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('HIDESUMMARYTAB', 'No', NOW( ) , 'Set this to Yes to hide the summary tab')";
	$sqla[11] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('HIDEENTITYTAB', 'No', NOW( ) , 'Set this to Yes to hide main entity list tab')";
	$sqla[12] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('CAL_MINHOUR', '7', NOW( ) , 'Starting hour of day, used for scheduling enties')";
	$sqla[13] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('CAL_MAXHOUR', '18', NOW( ) , 'Ending hour of day, used for scheduling enties (24h format: for 6pm use 18')";
	$sqla[14] = "INSERT INTO `PRFX@@@@@@@settings` (`setting` , `value` , `datetime` , `discription` ) VALUES ('CAL_USEWEEKEND', 'No', NOW( ) , 'Wheter or not to also show the weekend days in the week view of the calendar')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A246TO25() {
	$db_ver_from = "2.4.6";
	$db_ver_to = "2.5.0";
	$sqla[0] = "ALTER TABLE PRFX@@@@@@@binfiles ADD INDEX(checked)";
	$sqla[1] = "ALTER TABLE PRFX@@@@@@@entity ADD INDEX (duedate)";
	$sqla[2] = "ALTER TABLE PRFX@@@@@@@loginusers ADD `RECEIVEALLOWNERUPDATES` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[3] = "ALTER TABLE PRFX@@@@@@@loginusers ADD `RECEIVEALLASSIGNEEUPDATES` ENUM( 'n', 'y' ) NOT NULL ";

	$val = ini_get("session.save_path");
	$sqla[4] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'TMP_FILE_PATH', '" . $val . "', NOW( ) , 'The path to the directory where Interleave (the user under which your webserver runs) can store temporary files.')";
	$sqla[5] = "ALTER TABLE PRFX@@@@@@@ejournal ADD `private` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[6] = "ALTER TABLE PRFX@@@@@@@entity ADD `private` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[7] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ( '', 'ALSO_PROCESS_DELETED', 'No', NOW( ) , 'Set this option to Yes if you want the duedate notify script to also process entities on their duedate, even if the entity is deleted.')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A245TO246() {
	$db_ver_from = "2.4.5";
	$db_ver_to = "2.4.6";
	$sqla[0] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'STANDARD_TEXT', '', NOW( ) , 'A list of standard comments which users can automatically insert as a reply in entities. Leave empty for no standard comments.')";
	$sqla[1] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'CUSTOMER_LIST_TRESHOLD', '150', NOW( ) , 'The number of customers listed on the main customers page. If this number of customers is exceeded, the list will not appear for bandwith reasons.')";
	$sqla[2] = "ALTER TABLE `PRFX@@@@@@@entity` ADD `openepoch` VARCHAR( 30 ) NOT NULL ,ADD `closeepoch` VARCHAR( 30 ) NOT NULL";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A244TO245() {
	$db_ver_from = "2.4.4";
	$db_ver_to = "2.4.5";
	$sqla[0] = "ALTER TABLE `PRFX@@@@@@@entity` ADD `notify_assignee` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[1] = "ALTER TABLE `PRFX@@@@@@@entity` ADD `notify_owner` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[2] = "ALTER TABLE `PRFX@@@@@@@ejournal` ADD `notify_assignee` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[3] = "ALTER TABLE `PRFX@@@@@@@ejournal` ADD `notify_owner` ENUM( 'n', 'y' ) NOT NULL";
	$sqla[4] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BODY_ENTITY_CUSTOMER_ADD', 'You are registerd to customer @CUSTOMER@. Entity @ENTITYID@ was just coupled to that customer, so you might have to do something.', NOW( ) , 'The body of the e-mail which is send to the customer_owner when an entity (new or existing) is coupled to that customer, and the email_customer_upon_action checkbox in the customer properties is checked.')";
	$sqla[5] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BlockAllCSVDownloads', 'No', NOW( ) , 'Set this value to Yes if you want to block all CSV/Excel downloads for all users except for administrators.')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A243TO244() {
	$db_ver_from = "2.4.3";
	$db_ver_to = "2.4.4";
	$sqla[0] = "SELECT * FROM `PRFX@@@@@@@loginusers`";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A242TO243() {
	$db_ver_from = "2.4.2";
	$db_ver_to = "2.4.3";
	$sqla[0] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `RECEIVEDAILYMAIL` ENUM( 'No', 'Yes' ) NOT NULL";
	$sqla[1] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'LetUserEditOwnProfile', 'Yes', NOW( ) , 'Set this option to \'Yes\' to enable user to change their passwords, edit their full name, and select wether or not they want to receive the daily entity overwiew email.')";
	$sqla[2] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableRepositorySwitcher', 'Yes', NOW( ) , 'Set this option to \'Yes\' to enable a user to dynamically switch between repositories in which the same users exist with the same password. \'No\' disables this, \'Admin\' enables it only for admins.')";
	$sqla[3] = "ALTER TABLE `PRFX@@@@@@@languages` ADD INDEX ( `LANGID` )";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A241TO242() {
	$db_ver_from = "2.4.1";
	$db_ver_to = "2.4.2";
	$sqla[0] = "ALTER TABLE PRFX@@@@@@@customaddons ADD KEY(deleted)";
	$sqla[1] = "ALTER TABLE PRFX@@@@@@@customaddons ADD KEY(eid)";
	$sqla[2] = "ALTER TABLE PRFX@@@@@@@customaddons ADD KEY(value(20))";
	$sqla[3] = "ALTER TABLE PRFX@@@@@@@customaddons ADD KEY(type)";
	$sqla[4] = "ALTER TABLE PRFX@@@@@@@customaddons ADD KEY(name(20))";
	$sqla[5] = "ALTER TABLE PRFX@@@@@@@customer CHANGE `contact_phone` `contact_phone` VARCHAR( 50 ) NOT NULL";
	$sqla[6] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'AutoInsertDateTime', 'No', NOW( ) , 'Enter Yes of you would like the date and time information inserted automatically when adding text to an entity.')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A24TO241() {
	$db_ver_from = "2.4.0";
	$db_ver_to = "2.4.1";
	$sqla[0] = "ALTER TABLE PRFX@@@@@@@uselog ADD INDEX(user)";
	$sqla[1] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'CustomerListColumnsToShow', 'a:5:{s:2:\"id\";b:1;s:11:\"cb_custname\";b:1;s:10:\"cb_contact\";b:1;s:16:\"cb_contact_phone\";b:1;s:9:\"cb_active\";b:1;}', NOW( ) , 'The columns to show in the customer list')";
	$sqla[2] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ShowSaveAsNewEntityButton', 'Yes', NOW( ) , 'Yes to show the Save As New Entity button, no to hide it.')";
	$sqla[3] = "INSERT INTO `PRFX@@@@@@@languages` ( `id` , `LANGID` , `TEXTID` , `TEXT` ) VALUES ('', 'ENGLISH', 'saveasnewentity', 'Save as new entity')";
	$sqla[4] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'AutoCompleteCategory', 'Yes', NOW( ) , 'Enter Yes of you would like type-ahead functionality in the category field on the main entity page')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A23TO24() {
	$db_ver_from = "2.3.0";
	$db_ver_to = "2.4.0";
	$sqla[0] = "INSERT INTO `PRFX@@@@@@@languages` ( `id` , `LANGID` , `TEXTID` , `TEXT` ) VALUES ('', 'ENGLISH', 'stillchecked2', '. Please stop editing this file before trying to check it in.')";
	$sqla[1] = "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `checked` ENUM( 'in', 'out' ) NOT NULL";
	$sqla[2] = "ALTER TABLE `PRFX@@@@@@@binfiles` ADD `checked_out_by` INT NOT NULL";
	$sqla[3] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `LASTFILTER` LONGTEXT NOT NULL";
	$sqla[4] = "ALTER TABLE `PRFX@@@@@@@loginusers` ADD `LASTSORT` VARCHAR(50) NOT NULL";
	$sqla[5] = "ALTER TABLE `PRFX@@@@@@@customaddons` CHANGE `name` `name` LONGTEXT NOT NULL";
	$sqla[6] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableWebDAVSubsystem', 'No', NOW( ) , 'Yes to enable the WebDAV subsystem, no to disable it')";
	$sqla[7] = "CREATE TABLE `PRFX@@@@@@@webdav_locks` (  `token` varchar(255) NOT NULL default '',  `path` varchar(200) NOT NULL default '',  `expires` int(11) NOT NULL default '0',  `owner` varchar(200) default NULL,  `recursive` int(11) default '0',  `writelock` int(11) default '0',  `exclusivelock` int(11) NOT NULL default '0',  PRIMARY KEY  (`token`),  UNIQUE KEY `token` (`token`),  KEY `path` (`path`),  KEY `path_2` (`path`),  KEY `path_3` (`path`,`token`),  KEY `expires` (`expires`)) TYPE=MyISAM";
	$sqla[8] = "CREATE TABLE `PRFX@@@@@@@webdav_properties` (  `path` varchar(255) NOT NULL default '',  `name` varchar(120) NOT NULL default '',  `ns` varchar(120) NOT NULL default 'DAV:',  `value` text,  PRIMARY KEY  (`path`,`name`,`ns`),  KEY `path` (`path`)) TYPE=MyISAM";
	$sqla[9] = "INSERT INTO `PRFX@@@@@@@languages` ( `id` , `LANGID` , `TEXTID` , `TEXT` ) VALUES ('', 'ENGLISH', 'stillchecked1', 'This file is still locked for editing by')";
	$sqla[10] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DateFormat', 'dd-mm-yyyy', NOW( ) , 'Enter \'mm-dd-yyyy\' here to display dates in US format, anything else to display dates in international format (which is dd-mm-yyyy).')";
	$sqla[11] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'HideCustomerTab', 'no', NOW( ) , 'Set this value to \'Yes\' if you want the customer tab only to be visible to administrators')";
	Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A22TO23() {
		$db_ver_from = "2.2.0";
		$db_ver_to = "2.3.0";
		$sqla[0] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ShowPDWASLink', 'No', NOW( ) , 'Yes to show the PDWAS link in the file list. PDWAS is a Interleave addon which enables you to edit flies and directly save them to Interleave without having to upload the file manually.')";
		$sqla[1] = "ALTER TABLE PRFX@@@@@@@journal ADD `type` varchar(20) NOT NULL default 'entity'";

		$sqla[2] = "ALTER TABLE PRFX@@@@@@@journal CHANGE `message` `message` LONGTEXT NOT NULL";

		$sqla[3] = "ALTER TABLE PRFX@@@@@@@customer ADD `customer_owner` INT(11) NOT NULL;";

		$sqla[4] = "ALTER TABLE PRFX@@@@@@@customer ADD `email_owner_upon_adds` enum('no','yes') NOT NULL DEFAULT 'no'";

		$sqla[5] = "INSERT INTO PRFX@@@@@@@settings ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BODY_ENTITY_CUSTOMER_ADD', 'You are registerd to customer @CUSTOMER@. Entity @ENTITYID@ was just coupled to that customer, so you might have to do something.', NOW( ) , 'The body of the e-mail which is send to the customer_owner when an entity (new or existing) is coupled to that customer, and the email_customer_upon_action checkbox in the customer properties is checked.')";
		Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A21TO22() {

		$db_ver_from = "2.1.0";
		$db_ver_to = "2.2.0";
		$sqla[0] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'BODY_TEMPLATE_CUSTOMER', '<p>&gt;&gt; This mail is regarding entity @ENTITYID@ , \"@CATEGORY@\" in Interleave @TITLE@ at @WEBHOST@<br>-----------------------<br>Send from Interleave <br><a href=\"http://www.interleave.nl/\">http://www.interleave.nl</a><br></p>', NOW( ) , 'The template wich is used when sending an e-mail to a customer (editable by user before sending)');";
		Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A2TO21() {
		$db_ver_from = "2.0.0";
		$db_ver_to = "2.1.0";

		// Version-specific queries into array [sqla]
		$sqla[0] = "ALTER TABLE `PRFX@@@@@@@binfiles` ADD INDEX ( `koppelid` )";
		$sqla[1] = "INSERT INTO PRFX@@@@@@@settings(setting,value,discription) VALUES('MainListColumnsToShow','a:9:{s:2:\"id\";b:1;s:7:\"cb_cust\";b:1;s:8:\"cb_owner\";b:1;s:11:\"cb_assignee\";b:1;s:9:\"cb_status\";b:1;s:11:\"cb_priority\";b:1;s:11:\"cb_category\";b:1;s:10:\"cb_duedate\";b:1;s:12:\"cb_alarmdate\";b:1;}','non-editable by admin')";
		$sqla[2] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'LetUserSelectOwnListLayout', 'Yes', NOW( ) , 'Wether or not to let the user select his/her own list layout')";
		Upgrade($db_ver_from, $db_ver_to, $sqla);
}
function A196TO2() {
		global $host,$user,$pass,$database,$table_prefix;
		$upgradecheck = "SELECT value FROM PRFX@@@@@@@settings WHERE setting='DBVERSION' AND value='2.0.0'";
		$sqla[0] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'DontShowPopupWindow', 'No', NOW() , 'No to show the standard javascript popup window in the entity overview, yes to disable it and make editing the entity the default action when clicking on the row.')";
		$sqla[1] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ShowFilterInMainList', 'Yes', NOW( ) , 'Wether or not to show the filter pulldowns on top of the main entity list')";
		$sqla[2] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'ShowShortKeyLegend', 'Yes', NOW( ) , 'Wether or not to show the ShortKeys (ALT-1..ALT-9) legend on the tabs')";
		$sqla[3] = "UPDATE PRFX@@@@@@@settings SET value='2.0.0' WHERE setting='DBVERSION'";
		for ($t=0;$t<64;$t++) {

				$a = "Upgrading database mysql://$user[$t]:[password]@$host[$t]/$database[$t]<br>";
				$db = mysql_connect($host[$t], $user[$t], $pass[$t]);
				mysql_select_db($database[$t],$db);
				// Catch half-configured installations:
				if ($table_prefix[$t]=="") $table_prefix[$t] = "CRM";
				$sql = "SELECT value FROM $table_prefix[$t]settings WHERE setting='title'";
				if ($result= mysql_query($sql)) {
					$upgradecheck2 = str_replace("PRFX@@@@@@@",$table_prefix[$t],$upgradecheck);
					$result= mcq_upg($upgradecheck2,$db);
					$e= mysql_fetch_array($result);
					if (strlen($e[0])>0) { // not good
							$a .= "<span class='noway'> error. already up-to-date so it seems. skipping this repository!</span><br>$upgradecheck: $e[0]";
							$e[0] = "";
							unset($e);
					} else {
							for ($q=0;$q<sizeof($sqla);$q++) {
								$sqla[$q] = str_replace("PRFX@@@@@@@",$table_prefix[$t],$sqla[$q]);
								mcq_upg($sqla[$q],$db);
					}
					$a .= "<span style='color: #33FF00;'>Repository successfully upgraded!</span>";
					}

					unset($e);
					$e[0] = "";
					printbox($a);
				} else {
					printbox("Error - data tables not found - config file is inconsistant (mysql://$user[$t]:[password]@$host[$t]/$database[$t]/prfx:$table_prefix[$t])");
				}
			}
			printbox("It is a good idea to delete this script from your installation directory now! (upgrade.php)<br>[ <a href='index.php?8823'>to login page</a> ]</div></body></html>");
}
function A195TO196() {
		global $host,$user,$pass,$database,$table_prefix;
		$upgradecheck = "SELECT value FROM PRFX@@@@@@@settings WHERE setting='DBVERSION' AND value='1.9.6' OR value='2.0.0'";
		$sqla[0] = "ALTER TABLE `PRFX@@@@@@@customer` ADD `active` ENUM( 'yes', 'no' ) DEFAULT 'yes' NOT NULL";
		$sqla[1] = "UPDATE PRFX@@@@@@@settings SET value='1.9.6' WHERE setting='DBVERSION'";
		$sqla[2] = "INSERT INTO `PRFX@@@@@@@settings` (`settingid`, `setting`, `value`, `datetime`, `discription`) VALUES ('', 'BODY_ENTITY_EDIT', '<body><strong>One of your entities in repository \"@TITLE@\" was updated.</strong><br><br>This email is concerning your entity with category \"@CATEGORY@\"<br>This entity is available in your Interleave installation at @WEBHOST@ under EID number @ENTITYID@. <br><br>If this email was not intended for you, please contact @ADMINEMAIL@<br><br><table><tr><td>Entity:</td><td>@ENTITYID@</td></tr><tr><td>Category:</td><td>@CATEGORY@</td></tr><tr><td>Owner:</td><td>@OWNER@</td></tr><tr><td>Assignee:</td><td>@ASSIGNEE@</td></tr><tr><td>Contents:</td><td>See attachment</td></tr><tr><td>Admin email:</td><td>@ADMINEMAIL@</td></tr><tr><td>Webhost:</td><td>@WEBHOST@</td></tr><tr><td>Title:</td><td>@TITLE@</td></tr><tr><td>Customer:</td><td>@CUSTOMER@</td></tr><tr><td>Due-date:</td><td>@DUEDATE@</td></tr><tr><td>Status:</td><td>@STATUS@</td></tr><tr><td>Priority:</td><td>@PRIORITY@</td></tr></table></body>', NOW(), 'The body of the email which will be sent when an entity is updated. Please read the manual before editing this setting.')";
		for ($t=0;$t<64;$t++) {

				$a = "Upgrading database mysql://$user[$t]:[password]@$host[$t]/$database[$t]<br>";
				$db = mysql_connect($host[$t], $user[$t], $pass[$t]);
				mysql_select_db($database[$t],$db);
				// Catch half-configured installations:
				if ($table_prefix[$t]=="") $table_prefix[$t] = "CRM";
				$sql = "SELECT value FROM $table_prefix[$t]settings WHERE setting='title'";
				if ($result= mysql_query($sql)) {
					$upgradecheck2 = str_replace("PRFX@@@@@@@",$table_prefix[$t],$upgradecheck);
					$result= mcq_upg($upgradecheck2,$db);

					$e= mysql_fetch_array($result);
						if (strlen($e[0])>0) { // not good
								$a .= "<span class='noway'> error. already up-to-date so it seems. skipping this repository!</span><br>$upgradecheck: $table_prefix[$t] :: $e[0]";
								$e[0] = "";
								unset($e[0]);
								unset($e);
						} else {
								for ($q=0;$q<sizeof($sqla);$q++) {
									$sqla[$q] = str_replace("PRFX@@@@@@@",$table_prefix[$t],$sqla[$q]);
									mcq_upg($sqla[$q],$db);
								}

						$a .= "<span style='color: #33FF00;'>Repository successfully upgraded!</span>";
						}
					$e[0] = "";
					unset($e);
					printbox($a);
				} else {
					printbox("Error - data tables not found - config file is inconsistant (mysql://$user[$t]:[password]@$host[$t]/$database[$t]/prfx:$table_prefix[$t])");
				}
			}
			printbox("It is a good idea to delete this script from your installation directory now! (upgrade.php)<br>[ <a href='index.php?8823'>to login page</a> ]</div></body></html>");
}
//	remember TABLE PREFIX !
function A194TO195() {
		global $host,$user,$pass,$database,$table_prefix;
		$upgradecheck = "SELECT value FROM PRFX@@@@@@@settings WHERE setting='EnableEntityContentsJournaling'";
		$sqla[0] = "CREATE TABLE `PRFX@@@@@@@ejournal` (`id` int(11) NOT NULL auto_increment,		     `eid` int(11) NOT NULL ,  `category` varchar(150) NOT NULL default '',  `content` longtext NOT NULL,  `status` varchar(50) NOT NULL default 'open',  `priority` varchar(50) NOT NULL default 'low',  `owner` int(11) NOT NULL default '0',  `assignee` int(11) NOT NULL default '0',  `CRMcustomer` int(11) NOT NULL default '0',  `tp` timestamp(14) NOT NULL,  `deleted` enum('n','y') NOT NULL default 'n',  `duedate` varchar(15) NOT NULL default '',  `sqldate` date NOT NULL default '0000-00-00',  `obsolete` enum('y','n') NOT NULL default 'n',  `cdate` date NOT NULL default '0000-00-00',  `waiting` enum('n','y') NOT NULL default 'n',  `readonly` enum('n','y') NOT NULL default 'n',  `closedate` date NOT NULL default '0000-00-00',  `lasteditby` bigint(20) NOT NULL default '0',  `createdby` bigint(20) NOT NULL default '0',   PRIMARY KEY  (`id`),   UNIQUE KEY `id` (`id`),   KEY `id_2` (`id`))";
		$sqla[1] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableEntityContentsJournaling', 'Yes', NOW( ) , 'Set this value to Yes if you want a drop-down box in the main entity window to be able to switch to history states of an entity')";
		$sqla[2] = "UPDATE PRFX@@@@@@@settings SET value='1.9.5' WHERE setting='DBVERSION'";
for ($t=0;$t<64;$t++) {

				$a = "Upgrading database mysql://$user[$t]:[password]@$host[$t]/$database[$t]<br>";
				$db = mysql_connect($host[$t], $user[$t], $pass[$t]);
				mysql_select_db($database[$t],$db);
				// Catch half-configured installations:
				if ($table_prefix[$t]=="") $table_prefix[$t] = "CRM";
				$sql = "SELECT value FROM PRFX@@@@@@@settings WHERE setting='title'";
				if ($result= mysql_query($sql)) {
					$upgradecheck = str_replace("PRFX@@@@@@@",$table_prefix[$t],$upgradecheck);
					$result= mcq_upg($upgradecheck,$db);
					$e= mysql_fetch_array($result);
					if (strlen($e[0])>0) { // not good
							$a .= "<span class='noway'> error. already up-to-date so it seems. skipping this repository!</span><br>$upgradecheck: $e[0]";
					} else {
							for ($q=0;$q<sizeof($sqla);$q++) {
								$sqla[$q] = str_replace("PRFX@@@@@@@",$table_prefix[$t],$sqla[$q]);
								mcq_upg($sqla[$q],$db);
					}
					$a .= "<span style='color: #33FF00;'>Repository successfully upgraded!</span>";
					}
					printbox($a);
				} else {
					printbox("Error - data tables not found - config file is inconsistant (mysql://$user[$t]:[password]@$host[$t]/$database[$t]/prfx:$table_prefix[$t])");
				}
			}
			printbox("It is a good idea to delete this script from your installation directory now! (upgrade.php)<br>[ <a href='index.php?8823'>to login page</a> ]</div></body></html>");
}
function A193TO194() {
		global $host,$user,$pass,$database,$table_prefix;
		$upgradecheck = "SELECT value FROM PRFX@@@@@@@settings WHERE setting='EnableEntityJournaling'";
		$sqla[0] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'EnableEntityJournaling', 'Yes', NOW( ) , 'Set this value to Yes if you want entity journaling enabled (a link will be added to the main edit entity page)')";
		$sqla[1] = "CREATE TABLE `PRFX@@@@@@@journal` (`id` BIGINT NOT NULL AUTO_INCREMENT ,`eid` BIGINT NOT NULL ,`timestamp` TIMESTAMP NOT NULL ,`user` BIGINT NOT NULL ,`message` VARCHAR( 250 ) NOT NULL ,PRIMARY KEY ( `id` ) ,INDEX ( `eid` , `user` ) ) COMMENT = 'Interleave Entity journal'";
		$sqla[2] = "INSERT INTO `PRFX@@@@@@@settings` ( `settingid` , `setting` , `value` , `datetime` , `discription` ) VALUES ('', 'AutoCompleteCustomerNames', 'No', NOW( ) , 'Set this value to Yes if you want a text box wich auto-completes customer names instead of a pull-down menu with all customers.')";
		$sqla[3] = "UPDATE PRFX@@@@@@@settings SET value='1.9.4' WHERE setting='DBVERSION'";
		for ($t=0;$t<64;$t++) {

				$a = "Upgrading database mysql://$user[$t]:[password]@$host[$t]/$database[$t]<br>";
				$db = mysql_connect($host[$t], $user[$t], $pass[$t]);
				mysql_select_db($database[$t],$db);
				// Catch half-configured installations:
				if ($table_prefix[$t]=="") $table_prefix[$t] = "CRM";
				$sql = "SELECT value FROM PRFX@@@@@@@settings WHERE setting='title'";
				if ($result= mysql_query($sql)) {
					$upgradecheck = str_replace("PRFX@@@@@@@",$table_prefix[$t],$upgradecheck);
					$result= mcq_upg($upgradecheck,$db);
					$e= mysql_fetch_array($result);
					if (strlen($e[0])>0) { // not good
							$a .= "<span class='noway'> error. already up-to-date so it seems. skipping this repository!</span><br>$upgradecheck: $e[0]";
					} else {
							for ($q=0;$q<sizeof($sqla);$q++) {
								$sqla[$q] = str_replace("PRFX@@@@@@@",$table_prefix[$t],$sqla[$q]);
								mcq_upg($sqla[$q],$db);
					}
					$sql = "SELECT eid FROM PRFX@@@@@@@entity";
					$result1 = mcq_upg($sql,$db);
					while ($result= mysql_fetch_array($result1)) {
						$sql = "INSERT INTO PRFX@@@@@@@journal(eid,user,message) VALUES ('$result[eid]','[upgrade]','Journaling automatically enabled - upgrade from 1.9.3 to 1.9.4')";
						mcq_upg($sql,$db);
						$jcount++;
					}
					$a .= "<span style='color: #33FF00;'>Repository successfully upgraded!</span><br>$jcount journal entries inserted";
					}
					printbox($a);
				} else {
					printbox("Error - data tables not found - config file is inconsistant (mysql://$user[$t]:[password]@$host[$t]/$database[$t]/prfx:$table_prefix[$t])");
				}
			}
			printbox("It is a good idea to delete this script from your installation directory now! (upgrade.php)<br>[ <a href='index.php?8823'>to login page</a> ]</div></body></html>");
}
function printerror($a)
{
		if (stristr($a,"reported error")) {
		    	print "\">";
		}
		if (!$a) {
		    $a="You didn't provide all required information. Press 'back' in your browser and try again.";
		}
		?>
		<center><table border='1' width='70%' cellspacing='0' cellpadding='4'>
		<tr><td colspan='2'><center><img src='images/error.gif' alt=''>&nbsp;&nbsp;<strong><?php echo $a;?></strong>
		</td></tr></table></div></body></html>
		<?php
			exit;
} // end function
function printbox($msg)
{
		global $printbox_size,$legend;

		if (!$printbox_size) {
			$printbox_size = "70%";
		}
		print "<table width='100%'><tr><td>";
		print "<center><table border='0' width='$printbox_size'><tr><td colspan='2' valign='center'><fieldset>";
		if ($legend) {
			print "<legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;$legend</legend>";
		}
		print "<center>" . $msg . "</center></fieldset></td></tr></table><br>";
		print "</td></tr></table>";

		unset($printbox_size);
		$legend = "";
} // end func
function Upgrade_Specialfor300($db_ver_from, $db_ver_to, $sqla) {
		global $host,$user,$pass,$database,$table_prefix,$legend,$name,$CL;
		// OK, now browse through available repositories
		$t = 0;
		array_push($sqla,"INSERT INTO `PRFX@@@@@@@uselog` ( `id` , `ip` , `url` , `useragent` , `tijd` , `qs` , `user` ) VALUES ('', 'upgrade', 'upgrade', 'upgrade', NOW( ) , 'Upgrade from " . $db_ver_from . " to " . $db_ver_to . "', 'upgrade script')");
		for ($t=0;$t<64;$t++) {
				$a = "Processing mysql://$user[$t]@$host[$t]/$database[$t]/$table_prefix[$t]*<br>";
				if ($CL) {
					$a = str_replace("<br>","\n",$a);
					$a = preg_replace("<([^>]+)>", "", (preg_replace("<br>","\015\012",$a)));
					print $a . "\n";
					$a = "";
				}
				if (!$db = @mysql_connect($host[$t], $user[$t], $pass[$t])) {
					$a .= "<marquee><blink>Database connection failed totally</blink></marquee><br>";
					$cancel = true;
				}
				if (!@mysql_select_db($database[$t],$db)) {
					$a .= "<span class='noway'>Database could not be selected</span><br>";
					$cancel = true;
				}
				// Catch half-configured installations:
				if ($table_prefix[$t]=="") {
					$prefix = "CRM";
					$a .= "<span class='noway'>Warning - no table prefix configured. Assuming 'CRM'.</span><br>";
				} else {
					$prefix = $table_prefix[$t];
				}
				if ($CL) {
					$a = preg_replace("<br>","\n",$a);
					$a = preg_replace("<([^>]+)>", "", (preg_replace("<br>","\015\012",$a)));
					print $a . "\n";
					$a = "";
				}
				// Check if the title can be found
				$sql = "SELECT value FROM $table_prefix[$t]settings WHERE setting='title'";
				if (!$cancel) {
					if ($result= mysql_query($sql)) { // OK, it can be found
						$sql = "SELECT value FROM $table_prefix[$t]settings WHERE setting='DBVERSION'";
						$result= mcq_upg($sql,$db);
						$dbv = mysql_fetch_array($result);
						if ($dbv[0]<>$db_ver_from) {
							$cancel = true;
							$a .= "<span class='noway'>Wrong database version. Expected $db_ver_from but got $dbv[0].</span><br>";
						} else {

							$totsize = sizeof($sqla);
							// ACTUALLY UPGRADE THE REPOSITORY HERE
							for ($q=0;$q<sizeof($sqla);$q++) {
								$sql_to_query = str_replace("PRFX@@@@@@@",$prefix,$sqla[$q]);
								mcq_upg($sql_to_query,$db);

								if ($CL) {
									print "\015 Query: " . $q . "/" . $totsize;
								}
							}
							$GLOBALS['TBL_SPREFIX'] = $prefix;
							UpdateExtraFields();

							$sql = "UPDATE " .  $prefix . "settings SET value='" . $db_ver_to . "' WHERE setting='DBVERSION'";
							mcq_upg($sql,$db);
							$a .= "Repository successfully upgraded";
						}
					} else {
						$a .= "<span class='noway'>Tables not found.</span><br>";
						$cancel = true;
					}
				}

				if ($cancel) {
					$legend = "<img src='images/error.gif' alt=''>";
					$a .= "Repository not touched.<br>";
				} else {
					$legend = "<span style='color: #33FF00;'>OK&nbsp;</span>";
				}

				unset($cancel);
				if ($CL<>true) {
					printbox($a);
				} else {
					$a = preg_replace("<br>","\n",$a);
					$a = preg_replace("<([^>]+)>", "", (preg_replace("<br>","\015\012",$a)));
					print $a . "\n";
					$a = "";
				}
		}

	$legend = "CRM&nbsp;";
	if (!$CL) {
		print "<a href='index.php?logout=1'>to login page</a>&nbsp;<a href='upgrade.php?11=11'>to main upgrade page</a>";
	}
}
function UpdateExtraFields() {
	global $a;
	$sql = "SELECT value FROM $GLOBALS[TBL_SPREFIX]settings WHERE setting='EXTRAFIELDLOCATION'";
	$result = mcq_upg($sql,$db);
	$row = mysql_fetch_array($result);

	$old_names = array();
	$new_names = array();
	$old_ids   = array();
	$old_cids  = array();
	$new_ids   = array();
	$new_cids  = array();
	$location = $row['value'];
	if ($location=="A") {
		$location="Middle box, left";
	} else {
		$location="Under main text box, left";
	}
	$sql = "SELECT value FROM $GLOBALS[TBL_SPREFIX]settings WHERE setting='Extra fields list'";
	$result = mcq_upg($sql,$db);
	$row = mysql_fetch_array($result);
	if ($row['value']<>"") {
		$eflist = explode(",",$row['value']);
		//print_r($eflist);
		$queries = array();
		foreach ($eflist AS $field) {
				$num++;
				$orig = $field;
				array_push($old_names,$field);
				array_push($old_ids, "@EF" . $num . "@");

				if (stristr($field,"HIDE_")) {
					$hidden = "y";
					$field = str_replace("HIDE_","",$field);
				} else {
					$hidden = "n";
				}
				if (stristr($field,"DD_VAT_")) {
					$field = str_replace("DD_VAT_","",$field);
					$tmp = explode("|",$field);
					$fieldname = $tmp[0];
					$tmp[0] = "";
					$options_arr = array();
					foreach ($tmp AS $option) {
						if (trim($option)<>"") {
							array_push($options_arr,$option);
						}
					}
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','VAT drop-down','" . $fieldname . "','" . serialize($options_arr) . "','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"DD_")) {
					$field = str_replace("DD_","",$field);
					$tmp = explode("|",$field);
					$fieldname = $tmp[0];
					$tmp[0] = "";
					$options_arr = array();
					foreach ($tmp AS $option) {
						if (trim($option)<>"") {
							array_push($options_arr,$option);
						}
					}
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','drop-down','" . $fieldname . "','" . serialize($options_arr) . "','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"TB_")) {
					$field = str_replace("TB_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','text area','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"EML_")) {
					$field = str_replace("EML_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','mail','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"IHC_")) {
					$field = str_replace("IHC_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','invoice cost','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"IHS_")) {
					$field = str_replace("IHS_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','invoice qty','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"LINK_")) {
					$field = str_replace("LINK_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','hyperlink','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"DATE_")) {
					$field = str_replace("DATE_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','date','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"COMMENT_")) {
					$field = str_replace("COMMENT_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','comment','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} else {
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('entity','" . $hidden . "','textbox','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				}
				// Now update the trigger table

				$orig2 = "@EF_" . $orig . "@";
				$sql = "UPDATE $GLOBALS[TBL_SPREFIX]triggers SET onchange='EFID" . $i . "' WHERE onchange='" . base64_encode($orig2) . "'";
				//print $sql . "\n";
				mcq_upg($sql,$db);
		array_push($new_names, "@EFID" . $i . "@");
		array_push($new_cids, "@EFID" . $i . "@");
		}
	}
	$sql = "DELETE FROM $GLOBALS[TBL_SPREFIX]settings WHERE setting='Extra fields list'";
	mcq_upg($sql,$db);
	$num = 0;

	$sql = "SELECT value FROM $GLOBALS[TBL_SPREFIX]settings WHERE setting='Extra customer fields list'";
	$result = mcq_upg($sql,$db);
	$row = mysql_fetch_array($result);
	if ($row['value']<>"") {
		$eflist = explode(",",$row['value']);
		//print_r($eflist);


		$queries = array();
		foreach ($eflist AS $field) {
				$num++;
				array_push($old_cids, "@ECF" . $num . "@");
				array_push($old_names,$field);
				$orig = $field;

				if (stristr($field,"HIDE_")) {
					$hidden = "y";
					$field = str_replace("HIDE_","",$field);
				} else {
					$hidden = "n";
				}
//				array_push($old_cids, "ECF" . $num);

				if (stristr($field,"DD_VAT_")) {
					$field = str_replace("DD_VAT_","",$field);
					$tmp = explode("|",$field);
					$fieldname = $tmp[0];
					$tmp[0] = "";
					$options_arr = array();
					foreach ($tmp AS $option) {
						if (trim($option)<>"") {
							array_push($options_arr,$option);
						}
					}
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','VAT drop-down','" . $fieldname . "','" . serialize($options_arr) . "','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"DD_")) {
					$field = str_replace("DD_","",$field);
					$tmp = explode("|",$field);
					$fieldname = $tmp[0];
					$tmp[0] = "";
					$options_arr = array();
					foreach ($tmp AS $option) {
						if (trim($option)<>"") {
							array_push($options_arr,$option);
						}
					}
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','drop-down','" . $fieldname . "','" . serialize($options_arr) . "','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"TB_")) {
					$field = str_replace("TB_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','text area','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"EML_")) {
					$field = str_replace("EML_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','mail','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"IHC_")) {
					$field = str_replace("IHC_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','invoice cost','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"IHS_")) {
					$field = str_replace("IHS_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','invoice qty','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"LINK_")) {
					$field = str_replace("LINK_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','hyperlink','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"DATE_")) {
					$field = str_replace("DATE_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','date','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} elseif (stristr($field,"COMMENT_")) {
					$field = str_replace("COMMENT_","",$field);
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','comment','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				} else {
					$sql = "INSERT INTO $GLOBALS[TBL_SPREFIX]extrafields (tabletype,hidden,fieldtype,name,options,location,ordering) VALUES('customer','" . $hidden . "','textbox','" . $field . "','','" . $location . "','" . ($i*10*1.234) . "')";
					//print $sql . "\n";
					mcq_upg($sql,$db);
					$i = mysql_insert_id();
					$sql = "UPDATE $GLOBALS[TBL_SPREFIX]customaddons SET name='" . $i . "' WHERE name='" . $orig . "'";
					mcq_upg($sql,$db);
					//print $sql . "\n";
				}
				array_push($new_names, "@EFID" . $i . "@");
				array_push($new_cids, "@EFID" . $i . "@");
		}
	}
	$sql = "DELETE FROM $GLOBALS[TBL_SPREFIX]settings WHERE setting='Extra customer fields list'";
	mcq_upg($sql,$db);
	$sql = "DELETE FROM $GLOBALS[TBL_SPREFIX]settings WHERE setting='EXTRAFIELDLOCATION'";
	mcq_upg($sql,$db);
	$sql = "DELETE FROM $GLOBALS[TBL_SPREFIX]extrafields WHERE name=''";
	mcq_upg($sql,$db);
	$a .= "<br>" . $num . " Extra fields converted to 3.0.0 format";
	//ConvertTemplatesToNewFormat($old_names,$new_names);
}
function ConvertTemplatesToNewFormat($old_names, $new_names) {

	$sql = "SELECT * FROM $GLOBALS[TBL_SPREFIX]binfiles WHERE koppelid=0 AND filesize<1000000";
		$result= @mcq_upg($sql,$db);
		while ($row = @mysql_fetch_array($result)) {

					for ($x=0;$x<sizeof($old_names);$x++) {
						$row['content'] = str_replace("@ECF_" . $old_names[$x] . "@",$new_names[$x],$row['content']);
						$row['content'] = str_replace("@EF_" . $old_names[$x] . "@",$new_names[$x],$row['content']);
						$row['file_subject'] = str_replace("@ECF_" . $old_names[$x] . "@",$new_names[$x],$row['file_subject']);
						$row['file_subject'] = str_replace("@EF_" . $old_names[$x] . "@",$new_names[$x],$row['file_subject']);
					}
					for ($x=0;$x<sizeof($old_ids);$x++) {
						$row['content'] = str_replace($old_ids[$x],$new_ids[$x],$row['content']);
						$row['file_subject'] = str_replace($old_ids[$x],$new_ids[$x],$row['file_subject']);
					}
					for ($x=0;$x<sizeof($old_cids);$x++) {
						$row['content'] = str_replace($old_cids[$x],$new_cids[$x],$row['content']);
						$row['file_subject'] = str_replace($old_cids[$x],$new_cids[$x],$row['file_subject']);
					}
				$ins =  "INSERT INTO $GLOBALS[TBL_SPREFIX]binfiles(koppelid,content,filename,filesize,filetype,username,file_subject) ";
				$ins .= "VALUES('" . $row['koppelid'] . "','" . mres($row['content']) . "','" . $row['filename'] . "-converted-by-Interleave" . "','" . $row['filesize'] . "','" . $row['filetype'] . "','Interleave 2.6.2 to 3.0.0 upgrade procedure','" . $row['file_subject'] . "')";
				mcq_upg($ins,$db);
		}
}
function CLMenu() {
		global $user, $pass, $host, $database, $table_prefix, $limit_to_db;
		$GLOBALS['CL'] = "Yes";
		print "\n";
		$queries = array_reverse(unserialize(@file_get_contents("queries.sql.ser")));

		foreach ($queries AS $version => $list) {
			if ($version != "snapshot") {
				print $version . "		- Upgrade all " . $list['parent'] . " databases to version " . $version . "\n";
			}
		}

		print "5.5.0.1		- Upgrade all 5.5.0 databases to version 5.5.0.1\n";
		print "5.5.0		- Upgrade all 5.4.2 databases to version 5.5.0\n";
		print "5.4.2		- Upgrade all 5.4.1 databases to version 5.4.2\n";
		print "5.4.1		- Upgrade all 5.4.0 databases to version 5.4.1\n";
		print "5.4.0		- Upgrade all 5.3.2 databases to version 5.4.0\n";
		print "5.3.2		- Upgrade all 5.3.1 databases to version 5.3.2\n";
		print "5.3.1		- Upgrade all 5.3.0 databases to version 5.3.1\n";
		print "5.3.0		- Upgrade all 5.2.0 databases to version 5.3.0\n";
		print "5.2.0		- Upgrade all 5.1.1 databases to version 5.2.0\n";
/*		print "9		- Upgrade all 5.1.0 databases to version 5.1.1\n";
		print "9		- Upgrade all 5.0.1 databases to version 5.1.0\n";
		print "9		- Upgrade all 5.0   databases to version 5.0.1\n";
		print "9		- Upgrade all 4.3.0 databases to version 5.0.0\n";
		print "9		- Upgrade all 4.2.0 databases to version 4.3.0\n";
		print "9		- Upgrade all 4.1.0 databases to version 4.2.0\n";
		print "9		- Upgrade all 4.0.1 databases to version 4.1.0\n";
		print "9		- Upgrade all 4.0.0 databases to version 4.0.1\n";
		print "9		- Upgrade all 3.4.3 databases to version 4.0.0\n";
		print "10		- Upgrade all 3.4.2 databases to version 3.4.3\n";
		print "10		- Upgrade all 3.4.1 databases to version 3.4.2\n";
		print "11		- Upgrade all 3.4.0 databases to version 3.4.1\n";
		print "7		- Upgrade all 3.3.2 databases to version 3.4.0\n";
		print "5		- Upgrade all 3.3.1 databases to version 3.3.2\n";
		print "6		- Upgrade all 3.3.0 databases to version 3.3.1\n";
		print "7		- Upgrade all 3.2.0 databases to version 3.3.0\n";
		print "8		- Upgrade all 3.1.0 databases to version 3.2.0\n";
		print "9		- Upgrade all 3.0.0 databases to version 3.1.0\n";
*/
		print "\n";
		print "limit		- Limit upgrade to a single database\n";
		print "test		- Test upgrade procedure (harmless, but maintenance must be enabled)\n";
		print "mm on		- Enable maintenance mode on all repositories\n";
		print "mm off		- Disable maintenance mode on all repositories\n";
		print "\nexit		- Exit program\n";
		print "\n Enter the version number you want to upgrade your database to below.\n";
		print " \nInterleave > ";
		$a = readln();
		if ($a == "exit") {
			$GLOBALS['end'] = true;
			return;
		}
		if ($a == "limit") {
			print "Limit to which database? (full name, case sensitive)\n";
			print "Limit   > ";
			$limit_to_db = readln();
			print "Which upgrade option? (see above, either 'test' or number)\n";
			print "Option  > ";
			$a = readln();
		}
		if ($a == "mm on") {
			$sqla = array();
			array_push($sqla, "UPDATE PRFX@@@@@@@settings SET value='Yes' WHERE setting='MAINTENANCE_MODE'");
			$GLOBALS['IGNORE_VERSION'] = true;
			Upgrade("%", "%", $sqla);
			$GLOBALS['IGNORE_VERSION'] = false;
		} elseif ($a == "mm off") {
			$sqla = array();
			array_push($sqla, "UPDATE PRFX@@@@@@@settings SET value='No' WHERE setting='MAINTENANCE_MODE'");
			$GLOBALS['IGNORE_VERSION'] = true;
			Upgrade("%", "%", $sqla);
			$GLOBALS['IGNORE_VERSION'] = false;
		} elseif ($a == "SQL") {

			print "Which query to run? Use PRFX@@@@@@@ as prefix replacement if required.\n";
			print "SQL > ";
			$sql = readln();
			unset($sqla);
			$sqla = array();
			array_push($sqla, $sql);
			$GLOBALS['IGNORE_VERSION'] = true;
			Upgrade("%", "%", $sqla);
			$GLOBALS['IGNORE_VERSION'] = false;

		}


		$to_version = $a;
		$queries = unserialize(@file_get_contents("queries.sql.ser"));
		if (is_array($queries[$to_version])) { // Auto-upgrade new style

			$parent = $queries[$to_version]['parent'];

			$sqla = array();
			foreach ($queries[$to_version] AS $name => $query) {
				if (strstr($query, "PRFX@@@@@@@")) {
					$sqla[] = $query;
				}
			}
			
			Upgrade($parent, $to_version, $sqla);
		} else {

			switch ($a) {

				case "test":
					print "Do NOT cancel this test!\n";
					$sqla = array();
					array_push($sqla, "SELECT * FROM PRFX@@@@@@@settings");
					//array_push($sqla, "SELECT * FROM PRFX@@@@@@@uselog");

					$GLOBALS['IGNORE_VERSION'] = true;
					Upgrade("5.4.2", "hidde", $sqla);
					print "========= And now reverse this test .... =========== do NOT interrupt! =========================\n";
					Upgrade("hidde", "5.4.2", $sqla);
					$GLOBALS['IGNORE_VERSION'] = false;
				break;
				case "5.5.0.1":
					A550TO5501();
				break;
				case "5.5.0":
					A542TO550();
				break;
				case "5.4.2":
					A541TO542();
				break;
				case "5.4.1":
					A540TO541();
				break;
				case "5.4.0":
					A532TO540();
				break;
				case "5.3.2":
					A531TO532();
				break;
				case "5.3.1":
					A530TO531();
				break;
				case "5.3.0":
					A520TO530();
				break;
				case "5.2.0":
					A511TO520();
				break;
	/*			case 8:
					A510TO511();
				break;
				case 9:
					A501TO510();
				break;
				case 9:
					A50TO501();
				break;
				case 10:
					A430TO50();
				break;
	*/
	//			case 9:
	//				A420TO430();
	//			break;
	//			case 9:
	//				A410TO420();
	//			break;
	//			case 9:
	//				A401TO410();
	//			break;
	//			case 9:
	//				A400tO401();
	//			break;

	//			case 10:
	//				A343TO400();
	//			break;
	//			case 11:
	//				A340TO341();
	//			break;
	//			case 12:
	//				A332TO340();
	//			break;
	//
				default:
					//print "bye-bye\n";
				break;
			}
		}
	}