<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This is the Interleave installer - it should be run from your browser,
 * not from the command line.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");

// The main version number to install with (5.5.0.1 is the last hardcoded version)
$version = "5.5.0.1";

// Fetch the rest from the query file
$queries = unserialize(@file_get_contents("queries.sql.ser"));
foreach ($queries AS $versions => $list) {
	if ($versions != "snapshot") {
		$version = $versions;
	}
}


$GLOBALS['etn'] = "entity";
if ($_REQUEST['AddRepository']=="1") {
	require("initiate.php");
	ShowHeaders();
	SafeModeInterruptCheck();
	AdminTabs();
	MainAdminTabs();
	$to_tabs = array("Current repositories","New repository");
	$tabbs["main"] = array("admin.php" => "<strong>Back</strong>", "comment" => "bla");
	$tabbs["Current repositories"] = array("admin.php?reposman=1&amp;resman=1&amp;manageres=1&amp;1156969438" => "Current repositories", "comment" => "View the list of currently configured repositories");
	$tabbs["New repository"] = array("install.php?AddRepository=1&amp;step=1" => "New repository", "comment" => ".");
	$navid = "New repository";
	InterTabs($to_tabs, $tabbs, $navid);
	MustBeAdmin();
	$legend = "Add a repository&nbsp;";
} elseif ($_REQUEST['step'] != 'dl') {

	$legend = "Interleave Installation&nbsp;";
	print $GLOBALS['doctype'];
	print $GLOBALS['htmlopentag'];
	?>
	<head>
	<meta http-equiv="Content-type" content="text/html;charset=ISO-8859-1">
	<?php
	print "<title>Interleave $version installation procedure</title>";
	PrintUnauthenticatedHeaderJavascript();
	?>
	<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen">
	<link rel="stylesheet" href="css/pww.css" type="text/css">
	<link rel="stylesheet" href="css/crm_dft.css" type="text/css">

	</head>

	<body>
	<div>

	<div id="page">
		<h1 id='title'>
		Interleave <?php echo $version;?> installation
	</h1>
<?php
}
	// && !isset($_REQUEST['ovrw'])
 if ((@filesize("config/config.inc.php")>0) && ($_REQUEST['trywrite']<>1) && (!$_REQUEST['AddRepository']) && !isset($_REQUEST['ovrw'])) {
	header("Location: index.php");
	exit;
}

if ($_REQUEST['step']=='dl') {
	header("Content-Type: text/php	");
	header("Content-Disposition: attachment; filename=config.inc.php");
	header("Window-target: _top");
	// Push attachment from variable
	print $_REQUEST['cfgfile'];
	exit;
} elseif ($_REQUEST['step']=='write') {
	$_REQUEST['trywrite'] = 1;
    if ($fp = @fopen("config/config.inc.php","w")) {
		fputs($fp,$_REQUEST['cfgfile']);
		fclose($fp);
        $write = "gelukt";
    } else {
        $write = "niet gelukt";
    }
	$_REQUEST['step'] = 4;
}


$GLOBALS['Installer'] = true;

if (!$_REQUEST['step']) {
		$ver = phpversion();


		$ver_a = explode(".", $ver);

		$k = "<span class='noway'>Error</span>";

		if ($ver_a[0] < 5) {
			$fatal = 1;
			$phpver = "Too old: " . $ver;
		} elseif ($ver_a[0] == 5 && $ver_a[1] < 2) {
			$fatal = 1;
			$phpver = "Too old: " . $ver;
		} elseif ($ver_a[0] == 5 && $ver_a[1] == 2 && $ver_a[2] < 3) {
			$fatal = 1;
			$phpver = "Too old: " . $ver;
		} else {
			$phpver = $ver;
			$k = "<span style='color: #66CC00;'><strong>OK</strong></span>";
		}

		$sv = $k . "</td><td>" . $phpver;
		$path = realpath(".");
		$webpath = $_SERVER['PHP_SELF'];
		$webhost = $_SERVER['SERVER_NAME'];
		$a = get_loaded_extensions();
		if (!in_array("mysql",$a)) {
				$mysqlsupport = "<span class='noway'>Error</span></td><td>MySQL support in PHP not found";
				$fatal = 1;
		} else {
				$mysqlsupport = "<span style='color: #66CC00;'><strong><strong>OK</strong></strong></span></td><td>Available";
		}
		if (in_array("gd",$a)) {
				$gd = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>Installed";
			} else {
				$gd = "<span class='noway'>Not installed</span></td><td>You will not be able to generate pictures.";
			}
		if (ini_get('register_globals')=="1" || strtolower(ini_get('register_globals'))=="on" || strtolower(ini_get('register_globals'))=="yes") {
				$rg = "<span class='noway'>Warning</span></td><td>The PHP variable \"REGISTER_GLOBALS\" is enabled. This is dangerous.</span>";

		} else {
				$rg = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>Off";
				//$fatal = 1;
		}


		$t = ini_get('memory_limit');
		$t = str_replace("M","", $t);
		if (is_numeric($t) && $t > 15) {
				$mem = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>" . $t . "MB";
		} elseif (is_numeric($t)) {
				$mem = "<span class='noway'>Error</span></td><td>" . $t . "MB is not enough, set it to at least 16MB.";
				$fatal = 1;
		} else {
				$mem = "Unable to determine";
		}
		$dir = ini_get("session.save_path");
		if ($f = tempnam($dir,"INTLV-")) {
				$fa = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>$f";
				unlink($f);
		} else {
				$fa = "<span class='noway'>Warning</span></td><td>The PHP variable \"session.save_path\" is pointing to directory " . $dir .". Interleave was not able to write a file in this directory.<br>This is essential. This installation procedure will ask you for a new file location in the next step. Without a temporary file location, you cannot continue.</span>";
				$tmpfileerror=1;
		}


//		printheaderinst("&nbsp;<br>&nbsp;<strong>Welcome to the Interleave $version installation procedure</strong>&nbsp;<br>&nbsp;");


		print "<h2>This 4-step procedure will install Interleave for you</h2>";
		print "<strong>Things you must know before starting:</strong><ol>";
		print "<li>Your MySQL server hostname, database name, username and password</li>";
		print "<li>The administrator's email address</li>";
		print "<li>A name and password for the initial administrative login account</li>";
		print "<li>Your company name (Interleave repository main title)</li></ol>";



		print "<strong>System sanity checks</strong>";
		print "<table class='crm' width=\"100%\"><thead><tr><td colspan='3'>";
		print "<strong>Required server properties &amp; settings</strong></td></tr></thead>";
		print "<tr><td>PHP version </td><td>" . $sv . "</td></tr>";
		print "<tr><td>";
		print "MySQL support</td><td>" . $mysqlsupport . "</td></tr>";
		print "<tr><td>";
		print "Global registry of variables</td><td>" . $rg . "</td></tr>";
		if (ini_get('magic_quotes_gpc') == "Yes" || ini_get('magic_quotes_gpc') == "On" || ini_get('magic_quotes_gpc') == "1") {
			print "<tr><td>";
			print "Magic quotes</td><td><span class='noway'>Error</span></td><td>MQ is on. Interleave will try to set it off, but this can cause problems.</td></tr>";
		} else {
			print "<tr><td>";
			print "Magic quotes</td><td><span style='color: #66CC00;'><strong>OK</strong></span></td><td>Off</td></tr>";
		}
		print "<tr><td>";
		print "Temp file space</td><td>" . $fa . "</td></tr>";
		print "<tr><td>";
		print "Maximum memory for PHP scripts</td><td>" . $mem . "</td></tr>";

		print "<tr><td>";
		if (function_exists("zip_open")) {
			$zip = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>Available";
		} else {
			$zip = "<span class='noway'>Error</span></td><td> (not found)";
			$fatal = 1;
		}
		print "PHP Zip compiled</td><td>" . $zip . "</td></tr>";

		

		$ro = ini_get('request_order');
		$name = "request order";
		if ($ro == "") {
			$ro = ini_get('variables_order');
			$name = "variables order";
		}
		print "<tr><td>PHP " . $name . "</td><td>";
		if (substr(strtolower($ro),0,3)!="gpc" && strtolower($ro)!="egpcs") {
				print "<span class='noway'>Error</span></td><td>The " . $name . " of PHP is not set correctly (should be GPC but is " . $ro . ")</span>";
				$fatal = 1;
		} else {
				print "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>" . $ro;

				//$fatal = 1;
		}
		print "</td></tr><tr><td>";
		if (function_exists("iconv")) {
			$zip = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>Available";
		} else {
			$zip = "<span class='noway'>Error</span></td><td> (not found)";
			$fatal = 1;
		}
		print "iConv support</td><td>" . $zip . "</td></tr>";
		print "<thead><tr><td colspan='3'><strong>Optional libraries and file properties</strong><sup>1</sup></td></tr></thead>";
		print "<tr><td>";
		print "Advanced PHP Caching support (APC)</td>";

		if (function_exists("apc_fetch")) {
			print "<td><span style='color: #66CC00;'><strong>OK</strong></span></td><td>Available</td>";
		} else {
			print "<td><span class='noway'>x</span></td><td>Not available</td>";
		}


		print "<tr><td>";
		print "GD Library</td><td>" . $gd . "</td></tr>";
		print "<tr><td>";
		if (@require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/PEAR.php")) {
			$pear = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>Installed";
		} else {
			$pear = "<span class='noway'>Error</span></td><td>You will not be able to use Microsoft&reg; Excel&reg; formatted exports";
		}
		print "PEAR Classes</td><td>" . $pear . "</td></tr>";

		$b = get_perms("config/config.inc.php");
		if ($fp = @fopen("config/config.inc.php","w")) {
			$configfile = "<span style='color: #66CC00;'><strong>OK</strong></span></td><td>$b";
		} else {
			$configfile = "<span class='noway'>x</span></td><td>Not writeable";
		}
		print "<tr><td>Write access to config file</td><td>" . $configfile . "</td></tr>";
		print "</table>";
		print "<form id='inst' action='install.php' method='post'><div class='showinline'>";
		print "<br>";
		print "<input type='hidden' name='step' value='GPL'>";
		print "<input type='hidden' name='tmpfileerror' value='" . $tmpfileerror . "'>";
		print "<input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'>";
		print "<input type='hidden' name='ovrw' value='" . $_REQUEST['ovrw'] . "'>";
		if (!$fatal) {
			print "<input type='submit' name='knop' value='Next page'>";
		} else {
			print "<input type='submit' name='knop' value='Fatal error found - unable to continue' disabled='disabled'>";
		}

		print "</div></form><p>";
		print "<sup>1</sup> Some external libraries are used by Interleave, though they are optional. Interleave uses the GD library ";
		print "to create images, and PEAR classes to support the Microsoft&reg; Excel&reg; ";
		print "export function. If you don't have GD or PEAR, you can still use install Interleave except for these two functions. The APC library makes Interleave faster to work with, though it's not required for Interleave to function properly.</p>";
		print "<p>If you need any help go to our <a href='http://www.interleave.nl/'>website</a> or directly to our <a href=\"http://support.interleave.nl/\">support application</a>.</p>";
		print "</div></body></html>";


} elseif ($_REQUEST['step']=="GPL") {
		print "<h2>Please accept the GPL license</h2>";
		print "<form id='inst' action='install.php' method='post'><div class='showinline'>";
		print "<input type='hidden' name='step' value='1'>";
		print "<input type='hidden' name='tmpfileerror' value='" . $tmpfileerror . "'>";
		print "<input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'>";
		print "<div style='max-height: 600px;overflow: auto;'><pre>";
		require("COPYING");
		print "</pre></div>";
		print "<input type='submit' name='knop' value='I agree, start installation'>";
		print "<input type='hidden' name='ovrw' value='" . $_REQUEST['ovrw'] . "'>";
		print "<input type='button' onclick=\"document.location='http://www.google.com/';\" name='knop' value=\"I don't agree\" >";
		print "</form>";

} elseif ($_REQUEST['step']==1) {

		print "<h2>MySQL database name &amp; credentials</h2>";

		print "<form id='sql' action='install.php' method='post'><div class='showinline'><table class='crm' width=\"100%\">";
		print "<tr><td>MySQL host</td><td><input type='text' name='sqlhost' size='30'></td></tr>";
		print "<tr><td>MySQL username</td><td><input type='text' name='sqluser' size='30'></td></tr>";
		print "<tr><td>MySQL password</td><td><input type='password' name='sqlpwd' size='30'></td></tr>";
		print "<tr><td>MySQL table prefix <sup>1</sup></td><td><input type='text' name='TABLEPREFIX' size='30' value='CRM'></td></tr>";
		print "<tr><td>MySQL database <sup>2</sup></td><td><input type='text' name='sqldb' size='30'></td></tr>";
		print "<tr><td>Delete database prior to create&nbsp;<sup>3</sup></td><td><input type='checkbox' name='dropdb' value='yes'></td></tr>";
		if ($tmpfileerror) {
			print "<tr><td>Temp file path (e.g. /tmp)</td><td><input type='text' name='tmp_file_path' size='30'></td></tr>";
		}
		print "</table>";

		print "<p><input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'></p>";
		print "<p><br><input type='hidden' name='step' value='2'><input type='submit' name='knop' value='Go to step 2'></p></div></form>";

		print '<br>';
		print "<sup>1</sup> All tables will start with these characters. If unsure, choose &quot;CRM&quot;<br>";
		print "<sup>2</sup> The install procedure will try to create this database but will continue if it already exists<br>";
		print "<sup>3</sup> When selected the install procedure will first issue a DROP DATABASE before trying to create it. This will effectively delete the database if it currently exists. Use with care.<br>";


		print "</div></body></html>";

} elseif ($_REQUEST['step']==2) {
	if (!$_REQUEST['TABLEPREFIX']) {
		printbox("Error! No TABLEPREFIX received. Press 'back' in your browser and try again");
		exit;
	} else {
		$GLOBALS['TBL_PREFIX'] = $_REQUEST['TABLEPREFIX'];
	}
	if ((!$_REQUEST['sqlhost']) || (!$_REQUEST['sqluser']) || (!$_REQUEST['sqldb'])) {
		printerror("");
	}
	testsql($_REQUEST['sqlhost'],$_REQUEST['sqluser'],$_REQUEST['sqlpwd']);
	$bla = "<ul>";
	if ($_REQUEST['dropdb'] == "yes") {
		mcqinstall("DROP DATABASE IF EXISTS `" . $_REQUEST['sqldb'] . "`", $db);
		$bla .= "<li>Old database dropped (if it existed)</li>";
	}


	$bla .= "<li>Database information was OK, the connection has been tested</li>";
		$sql = "CREATE DATABASE IF NOT EXISTS `" . $_REQUEST['sqldb'] . "`";
		mcqinstall($sql,$db);
		$db = @mysql_connect($_REQUEST['sqlhost'], $_REQUEST['sqluser'], $_REQUEST['sqlpwd']);
		if (@mysql_select_db($_REQUEST['sqldb'],$db)) {
			$bla .= "<li>Database " . $_REQUEST['sqldb'] . " was created and selected successfully</li>";
		} else {
			$bla .= "<img src='images/error.gif' alt=''>&nbsp;&nbsp;Database " . $_REQUEST['sqldb'] . " was NOT created succesfully.<br><br><em>This could not be fatal. Trying to use it anyway.</em><br><br>";
		}


		// LANGUAGE IMPORT ROUTINE
		// The main pack file MUST BE NAMED "ENG.CRM"
		$sqla = array();
		$sqla = fillarray();
		mysql_query("SET NAMES UTF8");


		for ($x=0;$x<sizeof($sqla);$x++) {
			$result= @mysql_query($sqla[$x]) or printerror("An error occured while importing the database structure!<br><br>The reported error is: " . mysql_error () . "<br><br>Please Press 'back' in your browser and try again. (" . $sqla[$x] . ")");
		}

		$bla .= "<li>Database structure was successfully imported.</li>";

		$fc = file("ENG.CRM");
		$fc[0] = ""; // Interleave LANGUAGE PACK EXPORT FILE - Pack ENGLISH. This is the original, distributed pack.
		$fc[1] = ""; // Generated May 9, 2006, 21:05 on Interleave version Interleave 3.4.2 (c) 2001-2012 - LOGTEXT ENABLED.
		$fc[2] = ""; // PACK|||ENGLISH|||318
		for ($x=3;$x<sizeof($fc);$x++) {
				$tmp=explode("|||",$fc[$x]);
				$fc1[$x] = mres(trim($tmp[0]));
				$fc2[$x] = mres(trim($tmp[1]));
				$fc3[$x] = mres(trim($tmp[2]));
		}
//		fclose($fp);
//			print "FILE RECEIVED! - $_FILES['userfile']['tmp_name']  ($_FILES['userfile']['name'])";
		$bla2 = $fc[2];
		$header=explode("|||",$bla2);
		$pack = $header[1];
		//printbox("<br>Processing language pack $header[1] containing $header[2] entries.<br>");
		$outp = "";

			for ($p=3;$p<sizeof($fc2);$p++) {
						$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID,TEXTID,TEXT) VALUES('" . mres($fc1[$p]) . "', '" . mres($fc2[$p]) . "', '" . mres($fc3[$p]) . "')";
						 mcqinstall($sql,$db);
					}
					$outp .= "<li>" . $p . " Language entries were imported (English)</li>";

		print "<h2>Database created</h2>";
		print $bla;
		print $outp;
		print "</ul>";


		//JH 2010-08-17 not in use
		$tmp_file_path = "";
		if ($tmp_file_path) {
			$dir = $tmp_file_path;
			if ($f = @tempnam($dir,"BLA")) {
					printbox("Temporary file path OK");
					unlink($f);
					$tmpdir = $tmp_file_path;
			} else {
					printbox("<img src='images/error.gif' alt=''>&nbsp;<span class='noway'>Interleave was not able to write a file in the provided temporary file directory.<br>This installation will continue, though Interleave <strong>will not work properly</strong>. As soon as Interleave is installed, go to the system administration page, select 'Change system values' and set the TMP_FILE_PATH to directory in which the webserver is allowed to place files. Please, be warned and fix this!</span>");
					$tmpdir = "ERROR!";
			}
		} else {
			$tmpdir = ini_get("session.save_path");
		}
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres($tmpdir) . "' WHERE setting='TMP_FILE_PATH'";
		mcqinstall($sql,$db);
?>
			<br>
		<form id='inst' action='install.php' method='post'><div class='showinline'>
		<input type='hidden' name='step' value='3'>
		<input type='hidden' name='upgrade' value='<?php echo $_REQUEST['upgrade'];?>'>
			<?php print "<input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'>";?>
		<input type='submit' name='knop' value='Go to step 3'>
		<?php
		print "<input type='hidden' name='sqldb' value='" . $_REQUEST['sqldb'] . "'>";
		print "<input type='hidden' name='sqluser' value='" . $_REQUEST['sqluser'] . "'>";
		print "<input type='hidden' name='sqlpwd' value='" . $_REQUEST['sqlpwd'] . "'>";
		print "<input type='hidden' name='sqlhost' value='" . $_REQUEST['sqlhost'] . "'>";
		print "<input type='hidden' name='TABLEPREFIX' value='" . $GLOBALS['TBL_PREFIX'] . "'>";
		?>
		</div></form>

    	</div>
	</body>
	</html>
	<?php
} elseif ($_REQUEST['step']==3) {
	if (!$_REQUEST['TABLEPREFIX']) {
		printbox("Error! No TABLEPREFIX received. Press 'back' in your browser and try again");
		exit;
	} else {
		$GLOBALS['TBL_PREFIX'] = $_REQUEST['TABLEPREFIX'];
	}
		print "<h2>Repository details and admin user account</h2>";

		print "<table class='crm' width=\"100%\"><tr><td><form id='addinfo' action='install.php' method='post'><div class='showinline'>";
		print "Repository name: </td><td><input type='text' name='cname' size='30' value='My Demo Company'></td></tr>";
		print "<tr><td>";
		print "Administrator's email: </td><td><input type='text' name='admemail' size='30' value=''></td></tr>";
		print "<tr><td>";
		print "Initial login account name: </td><td><input type='text' name='fstaccount' size='30' value=''></td></tr>";
		print "<tr><td>";
		print "Initial login account password: </td><td><input type='password' name='fstpwd1' size='30' value=''></td></tr>";
		print "<tr><td>";
		print "Again: </td><td><input type='password' name='fstpwd2' size='30' value=''></td></tr>";
		print "</td></tr></table>";


		print "<br>";
		print "<input type='hidden' name='step' value='4'>";

		print "<input type='hidden' name='sqldb' value='" . $_REQUEST['sqldb'] . "'>";
		print "<input type='hidden' name='upgrade' value='" . $_REQUEST['upgrade'] . "'>";
		print "<input type='hidden' name='sqluser' value='" . $_REQUEST['sqluser'] . "'>";
		print "<input type='hidden' name='sqlpwd' value='" . $_REQUEST['sqlpwd'] . "'>";
		print "<input type='hidden' name='sqlhost' value='" . $_REQUEST['sqlhost'] . "'>";
		print "<input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'>";
		print "<input type='hidden' name='TABLEPREFIX' value='" . $GLOBALS['TBL_PREFIX'] . "'>";
		?>
		<input type='submit' name='knop' value='Go to step 4'>
		</div></form>

		</div>
		</body>
		</html>
	<?php
} elseif ($_REQUEST['step']==4) {
		if (!$_REQUEST['TABLEPREFIX']) {
			printbox("Error! No TABLEPREFIX received. Press 'back' in your browser and try again");
			exit;
		} else {
			$GLOBALS['TBL_PREFIX'] = $_REQUEST['TABLEPREFIX'];
		}
		if ((!$_REQUEST['trywrite']) && (!$_REQUEST['upgrade'])) {

			if ((!$_REQUEST['cname']) || (!$_REQUEST['admemail']) || (!$_REQUEST['fstaccount']) || (!$_REQUEST['fstpwd1']) || (!$_REQUEST['fstpwd2'])) {
					printerror("");
			}
			if ($_REQUEST['fstpwd1']<>$_REQUEST['fstpwd2']) {
					printerror("Passwords do not match. Press 'back' in your browser and try again.");
			}
			if ($_REQUEST['fstpwd1']=="") {
					printerror("Sorry, password cannot be empty. Press 'back' in your browser and try again.");
			}
			if ((strlen($_REQUEST['fstpwd1']))<3) {
					printerror("Your password is too short (". strlen($_REQUEST['fstpwd1']) . ").  Press 'back' in your browser and enter one longer than 3 characters!");
			}
			if (strlen($_REQUEST['fstaccount'])<3) {
					printerror("Your accountname is too short.  Press 'back' in your browser and enter one longer than 3 characters!");
			}
			if ($logo=="") {
				$logo="*NONE*";
			}
			$db = mysql_connect($_REQUEST['sqlhost'], $_REQUEST['sqluser'], $_REQUEST['sqlpwd']);
			mysql_select_db($_REQUEST['sqldb'],$db);
			$sqla = array();
			$sqla = datafill($_REQUEST['fstaccount'],$_REQUEST['fstpwd1'],$_REQUEST['admemail'],$_REQUEST['cname'],$logo);
			$queries = unserialize(@file_get_contents("queries.sql.ser"));

			$parent = "5.5.0.1";
			$hit = true;
			while ($hit) {
				$lhit = false;
				foreach ($queries AS $version => $querylist) {
					if ($querylist['parent'] == $parent) {
						foreach ($querylist AS $name => $query) {
							if (strstr($query, "PRFX@@@@@@@")) {
								$sqla[] = str_replace("PRFX@@@@@@@", $GLOBALS['TBL_PREFIX'], $query);
							}
						}
						$lhit = true;
						$parent = $version;
					}
				}
				if (!$lhit) $hit = false;
			}

//			print "<pre>";
//			print_r($sqla);
//			exit;

			for ($x=0;$x<sizeof($sqla);$x++) {
				$sqla[$x] = str_replace("@@@@LAST_ID@@@@",$last___ins,$sqla[$x]);

				$result= mysql_query($sqla[$x]);
				//or die (printerror("An error occured while importing settings!<br><br>The reported error is: " . mysql_error () . "<br><br>Please Press 'back' in your browser and try again.<br><br>Query: $sqla[$x]"));
				$last___ins = mysql_insert_id();
				if (!is_numeric($last___ins)) {
					unset($last___ins);
				}

			}
			// POST-INSTALL BUSINESS
			PostInstall();
				$cfgfile = '<?php';
				$cfgfile .= "\n";
				$cfgfile .= '$GLOBALS[LogonPageMessage] = "";';
				$cfgfile .= "\n";
				$cfgfile .= '$host[0] = \'' . $_REQUEST['sqlhost'] . '\';';
				$cfgfile .= "\n";
				$cfgfile .= '$user[0] = \'' . $_REQUEST['sqluser'] . '\';';                                        // SQL-user
				$cfgfile .= "\n";
				$cfgfile .= '$pass[0] = \'' . $_REQUEST['sqlpwd'] . '\';';                                          // SQL-pass
				$cfgfile .= "\n";
				$cfgfile .= '$database[0] = \'' . $_REQUEST['sqldb'] .'\';';                                // CRM-database
				$cfgfile .= "\n";
				$cfgfile .= '$table_prefix[0] = \'' . $GLOBALS['TBL_PREFIX'] . '\';';
				$cfgfile .= "\n";
				$cfgfile .= '?>';
		} // end if !trywrite

	?>
			<?php

		if ($_REQUEST['upgrade']) {
				$cfgfile = '<?php';
				$cfgfile .= "\n";
				$cfgfile .= '$GLOBALS[\'LogonPageMessage\'] = "";';
				$cfgfile .= "\n";
				$cfgfile .= '$host[0] = \'' . $_REQUEST['sqlhost'] . '\';';
				$cfgfile .= "\n";
				$cfgfile .= '$user[0] = \'' . $_REQUEST['sqluser'] . '\';';                                        // SQL-user
				$cfgfile .= "\n";
				$cfgfile .= '$pass[0] = \'' . $_REQUEST['sqlpwd'] . '\';';                                          // SQL-pass
				$cfgfile .= "\n";
				$cfgfile .= '$database[0] = \'' . $_REQUEST['sqldb'] .'\';';                                // CRM-database
				$cfgfile .= "\n";
				$cfgfile .= '$table_prefix[0] = \'' . $GLOBALS['TBL_PREFIX'] . '\';';
				$cfgfile .= "\n";
				$cfgfile .= '?>';
		}
		if ($write=="niet gelukt") {
				$b = get_perms("config/config.inc.php");
				?>
					<script type="text/javascript">
					<!--
					alert("Writing failed. The permissions are <?php echo $b;?> which is not enough!");
					//-->
					</script>
				<?php
		} elseif ($write=="gelukt") {
				$b = get_perms("config/config.inc.php");
   				?>
					<script type="text/javascript">
					<!--
					alert("Writing succesfull! The permissions now are <?php echo $b;?>. Make sure the webserver cannot write to this file anymore!");
					//-->
					</script>
				<?php
		}
		if ($_REQUEST['AddRepository']==1) {
			unset($a);
			for ($i=0;$i<64;$i++) {
				if (!$pass[$i] && !$a) {
					$a = $i;
					continue;
				}
			}
			?>
			<h2>Installation complete</h2>
			<table border='0' width='70%'>
			<tr><td colspan='2'><strong>All is set. The only thing left is to edit your config file.</strong>
			<br><br>
			You need to add the following text. This procedure cannot do that for you.
			<br><br>
						<?php

					$cfgfile = '';
	//				$cfgfile .= "\n";
					$cfgfile .= '$host[' . $a . '] = "' . $_REQUEST['sqlhost'] . '";';
					$cfgfile .= "\n";
					$cfgfile .= '$user[' . $a . '] = "' . $_REQUEST['sqluser'] . '";';                                        // SQL-user
					$cfgfile .= "\n";
					$cfgfile .= '$pass[' . $a . '] = "' . $_REQUEST['sqlpwd'] . '";';                                          // SQL-pass
					$cfgfile .= "\n";
					$cfgfile .= '$database[' . $a . '] = "' . $_REQUEST['sqldb'] .'";';                          // CRM-database
					$cfgfile .= "\n";
					$cfgfile .= '$table_prefix[' . $a . '] = "' . $GLOBALS['TBL_PREFIX'] . '";';
					$cfgfile .= "\n";

				print "<pre>";
				print htme($cfgfile);
				print "</pre>";
				?>
				<br></td></tr>
				<tr><td>When done, point your browser <a href='index.php' class='arrow'>here</a>.</td></tr></table>


			<?php
		} else {
		?>




		<?php
			if (file_exists("config/config.inc.php")) {
				require("config/config.inc.php");
			}
			if ($_REQUEST['cfgfile'] != "") {
				$cfgfile = $_REQUEST['cfgfile'];
			}
			if ($host[0] && $user[0] && $pass[0]) {
				?>
					<h2>That's all. Start working with Interleave!</h2>
					Go <a href="index.php">to the login page</a>.
				<?php
			} else {
				?>
				<h2>Save configuration file</h2>
		All is set. The only thing left is to create the config file. Please choose one of these options:
		<ul>
			<li>Let the install procedure try to write the file itself
			<p>

			<?php echo $a;?>
			<form id='dlcfg' action='install.php' method='post'><div class='showinline'>
			<input type='hidden' name='step' value='write'>
			<input type='hidden' name='cfgfile' value='<?php echo htme($cfgfile); ?>'>
			<?php print "<input type='hidden' name='TABLEPREFIX' value='" . $_REQUEST['TABLEPREFIX'] . "'><input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'>";?>
			<input type='submit' name='knop' value='Try to write the configuration file automatically'>
			</div></form>
		</p>
		</li>

			<li>Download the file to you computer and place it in the installation directory yourself (/config/)
			<p>
			<form id='dlcfg' action='install.php' method='post'><div class='showinline'>
			<input type='hidden' name='step' value='dl'>
			<input type='hidden' name='cfgfile' value='<?php echo htme($cfgfile); ?>'>
			<?php print "<input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'>";?>
			<input type='submit' name='knop' value='Download the config/config.inc.php file'>
			</div></form>
		</p>
		</li>
			<li>Place the below printed text into the config/config.inc.php file yourself:
			<p>
			<?php
			

			print "<pre>";
			print htme($cfgfile);
			print "</pre>";
			?>
			</p>
		</li>

		</ul>
					<h2>You cannot continue until the configuration file is placed correctly.
					<form id='dlcfg' action='install.php' method='post'><div class='showinline'>
					<input type='hidden' name='trywrite' value='1'>
					<input type='hidden' name='step' value='4'>
					<input type='hidden' name='cfgfile' value='<?php echo htme($cfgfile); ?>'>
					<?php print "<input type='hidden' name='TABLEPREFIX' value='" . $_REQUEST['TABLEPREFIX'] . "'><input type='hidden' name='AddRepository' value='" . $_REQUEST['AddRepository'] . "'>";?>
					<input type='submit' name='knop' value='Refresh / check config file'>
					</div></form>
					</h2>
				<?php
			}





		} // end if repository
?>
</div>
			</body>
			</html>
	<?php

}
function testsql($host,$user,$pass)
{
    $link = mysql_connect($host, $user, $pass)
     or printerror("Could not connect to your database system. <br><br>The reported error is: " . mysql_error () . "<br><br>Please Press 'back' in your browser and try again.");
} // end func
function printerror($a)
{

		if (!$a) {
		    $a="You didn't provide all required information. Press 'back' in your browser and try again.";
		}
		?>
		<table width='75%'><tr><td><img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;Error&nbsp;<table border='1' width='70%' cellspacing='0' cellpadding='4'>
		<tr><td colspan='2'><img src='images/error.gif' alt=''>&nbsp;&nbsp;<strong><?php echo $a;?></strong>
		</td></tr></table></td></tr></table></div></body></html>
		<?php
			exit;
} // end func
function printstepheaderinstinst($step)
{

	?>
		<img src='images/crm.gif' alt=''>
	    <table border='1' width='70%' cellspacing='0' cellpadding='4'>
		<tr><td colspan='2'><strong>Interleave installation procedure - step <?php echo $step;?></strong></td></tr>
		</table>
		<br>
	<?php
} // end func
function fillarray()
{
	$GLOBALS['etn'] = "entity";
	// This function fills the sqla array with queries to create Interleave database table structure
	// Last update; 5.5.0.2, february 10 2011
	$sqla = array();
	  $sqla[] = "SET NAMES UTF8";
	  $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "accesscache` (  `user` bigint(20) NOT NULL,  `type` enum('e','c') NOT NULL,  `eidcid` bigint(20) NOT NULL,  `result` enum('nok','readonly','ok') NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  KEY `user` (`user`),  KEY `type` (`type`),  KEY `eidcid` (`eidcid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Interleave Access cache table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "attributes` (  `entity` int(250) NOT NULL,  `identifier` varchar(20) NOT NULL,  `attribute` varchar(250) NOT NULL,  `value` longtext NOT NULL,  `allowed_values` varchar(2048) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  UNIQUE KEY `DB_PRIM` (`entity`,`identifier`,`attribute`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "binfiles` (  `fileid` int(11) NOT NULL AUTO_INCREMENT,  `koppelid` int(11) NOT NULL DEFAULT '0',  `filename` varchar(200) NOT NULL DEFAULT '',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `filesize` mediumint(9) NOT NULL DEFAULT '0',  `filetype` varchar(150) NOT NULL DEFAULT '',  `username` varchar(150) NOT NULL DEFAULT '',  `checked` enum('in','out') NOT NULL DEFAULT 'in',  `checked_out_by` int(11) NOT NULL DEFAULT '0',  `type` varchar(20) NOT NULL DEFAULT 'entity',  `file_subject` varchar(250) NOT NULL DEFAULT '',  `show_on_add_list` enum('y','n') NOT NULL,  `version_belonging_to` int(11) NOT NULL DEFAULT '0',  `version_no` int(11) NOT NULL DEFAULT '1',  `orientation` enum('P','L') DEFAULT NULL,  `extractedascii` longtext NOT NULL,  PRIMARY KEY (`fileid`),  KEY `koppelid` (`koppelid`),  KEY `checked` (`checked`),  KEY `filetype` (`filetype`),  KEY `type` (`type`),  FULLTEXT KEY `extractedascii` (`extractedascii`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Binary files';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "blobs` (  `fileid` int(11) NOT NULL DEFAULT '0',  `content` mediumblob NOT NULL,  `thumbnail` blob NOT NULL,  `gz` enum('n','y') NOT NULL,  `minithumbnail` blob NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`fileid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Blob stand-alone table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "breadcrumtrail` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `user` int(11) NOT NULL,  `text` varchar(255) NOT NULL,  `link` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `user` (`user`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "cache` (  `stashid` int(11) NOT NULL AUTO_INCREMENT,  `epoch` varchar(20) DEFAULT NULL,  `session` varchar(32) NOT NULL,  `value` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`stashid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Query cache table';";
//	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "calendar` (  `user` varchar(20) NOT NULL DEFAULT '',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `datum` date NOT NULL DEFAULT '0000-00-00',  `basicdate` varchar(8) NOT NULL DEFAULT '',  `calendarid` mediumint(9) NOT NULL AUTO_INCREMENT,  `type` varchar(10) NOT NULL DEFAULT '',  `customnum` varchar(12) NOT NULL DEFAULT '',  `emailadress` varchar(150) NOT NULL DEFAULT '',  `eID` varchar(5) NOT NULL DEFAULT '',  PRIMARY KEY (`calendarid`),  KEY `Tijdpostzegel` (`timestamp_last_change`),  KEY `basicdate` (`basicdate`),  KEY `datum` (`datum`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Calendar entries';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "configsnapshots` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `comment` varchar(255) NOT NULL,  `config` longtext NOT NULL,  `snapshottype` enum('nousers','withusers','wholedb') NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave configuration snapshots';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "contactmoments` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `eidcid` int(11) NOT NULL,  `type` enum('entity','customer') NOT NULL,  `user` int(11) NOT NULL,  `meta` mediumtext NOT NULL,  `body` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `to` mediumtext NOT NULL,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Contact moments journal';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "customer` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `readonly` enum('no','yes') NOT NULL DEFAULT 'no',  `custname` varchar(200) NOT NULL DEFAULT '',  `contact` varchar(240) NOT NULL DEFAULT '',  `contact_title` varchar(240) NOT NULL DEFAULT '',  `contact_phone` varchar(50) NOT NULL DEFAULT '',  `contact_email` varchar(240) NOT NULL DEFAULT '',  `cust_address` longtext NOT NULL,  `cust_remarks` longtext NOT NULL,  `cust_homepage` varchar(240) NOT NULL DEFAULT '',  `active` enum('yes','no') NOT NULL DEFAULT 'yes',  `customer_owner` int(11) NOT NULL DEFAULT '0',  `email_owner_upon_adds` enum('no','yes') NOT NULL DEFAULT 'no',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `custname` (`custname`),  KEY `active` (`active`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Main customer table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "ejournal` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `eid` int(11) NOT NULL DEFAULT '0',  `category` varchar(150) NOT NULL DEFAULT '',  `content` longtext NOT NULL,  `status` varchar(50) NOT NULL DEFAULT 'open',  `priority` varchar(50) NOT NULL DEFAULT 'low',  `owner` int(11) NOT NULL DEFAULT '0',  `assignee` int(11) NOT NULL DEFAULT '0',  `CRMcustomer` int(11) NOT NULL DEFAULT '0',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `deleted` enum('n','y') NOT NULL DEFAULT 'n',  `duedate` varchar(15) NOT NULL DEFAULT '',  `sqldate` date NOT NULL DEFAULT '0000-00-00',  `obsolete` enum('y','n') NOT NULL DEFAULT 'n',  `cdate` date NOT NULL DEFAULT '0000-00-00',  `waiting` enum('n','y') NOT NULL DEFAULT 'n',  `readonly` enum('n','y') NOT NULL DEFAULT 'n',  `closedate` date NOT NULL DEFAULT '0000-00-00',  `lasteditby` int(11) NOT NULL DEFAULT '0',  `createdby` int(11) NOT NULL DEFAULT '0',  `notify_assignee` enum('n','y') NOT NULL DEFAULT 'n',  `notify_owner` enum('n','y') NOT NULL DEFAULT 'n',  `private` enum('n','y') NOT NULL DEFAULT 'n',  `duetime` varchar(4) NOT NULL DEFAULT '',  `formid` mediumint(9) NOT NULL DEFAULT '0',  PRIMARY KEY (`id`),  KEY `eid` (`eid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Entity contents journal';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "entity` (  `eid` int(11) NOT NULL AUTO_INCREMENT,  `category` varchar(150) NOT NULL DEFAULT '',  `content` longtext NOT NULL,  `status` varchar(250) NOT NULL DEFAULT 'open',  `priority` varchar(50) NOT NULL DEFAULT 'low',  `owner` int(11) NOT NULL DEFAULT '0',  `assignee` int(11) NOT NULL DEFAULT '0',  `CRMcustomer` int(11) NOT NULL DEFAULT '0',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `deleted` enum('n','y') NOT NULL DEFAULT 'n',  `startdate` varchar(15) NOT NULL,  `duedate` varchar(15) NOT NULL DEFAULT '',  `sqldate` date NOT NULL DEFAULT '0000-00-00',  `sqlstartdate` date NOT NULL,  `obsolete` enum('y','n') NOT NULL DEFAULT 'n',  `cdate` date NOT NULL DEFAULT '0000-00-00',  `waiting` enum('n','y') NOT NULL DEFAULT 'n',  `closedate` date NOT NULL DEFAULT '0000-00-00',  `lasteditby` int(11) NOT NULL DEFAULT '0',  `createdby` int(11) NOT NULL DEFAULT '0',  `readonly` enum('n','y') NOT NULL DEFAULT 'n',  `notify_assignee` enum('n','y') NOT NULL DEFAULT 'n',  `notify_owner` enum('n','y') NOT NULL DEFAULT 'n',  `openepoch` int(11) DEFAULT NULL,  `closeepoch` int(11) DEFAULT NULL,  `private` enum('n','y') NOT NULL DEFAULT 'n',  `duetime` varchar(4) NOT NULL DEFAULT '',  `parent` int(11) NOT NULL DEFAULT '0',  `formid` mediumint(9) NOT NULL DEFAULT '0',  `table` varchar(250) NOT NULL,  `starttime` varchar(4) DEFAULT NULL,  PRIMARY KEY (`eid`),  KEY `duedate` (`duedate`),  KEY `assignee` (`assignee`),  KEY `owner` (`owner`),  KEY `sqldate` (`sqldate`),  KEY `CRMcustomer` (`CRMcustomer`),  KEY `deleted` (`deleted`),  KEY `openepoch` (`openepoch`),  KEY `closeepoch` (`closeepoch`),  KEY `formid` (`formid`),  KEY `tp` (`timestamp_last_change`),  KEY `parent` (`parent`),  KEY `status` (`status`),  KEY `priority` (`priority`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Main entity table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "entityformcache` (  `formcacheid` mediumint(9) NOT NULL AUTO_INCREMENT,  `eid` mediumint(9) NOT NULL,  `formid` mediumint(9) NOT NULL,  `tabletype` varchar(15) NOT NULL DEFAULT 'entity',  `user` mediumint(9) NOT NULL,  `content` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`formcacheid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Cached parsed forms';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "entitylocks` (  `lockid` int(11) NOT NULL AUTO_INCREMENT,  `lockon` int(11) NOT NULL DEFAULT '0',  `lockby` int(11) NOT NULL DEFAULT '0',  `lockepoch` varchar(30) NOT NULL DEFAULT '',  `locktable` varchar(12) NOT NULL DEFAULT 'entity',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`lockid`),  KEY `lockon` (`lockon`),  KEY `lockepoch` (`lockepoch`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Entity record locks';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "extrafieldconditions` (  `conid` int(11) NOT NULL AUTO_INCREMENT,  `efid` int(11) NOT NULL,  `field` varchar(255) NOT NULL,  `value` mediumtext NOT NULL,  `trueorfalse` enum('true','false') NOT NULL,  `deletetemplaterow` enum('n','y') NOT NULL DEFAULT 'n',  `displayvalueintext` enum('n','y') NOT NULL DEFAULT 'n',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`conid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "extrafieldrequiredconditions` (  `conid` int(11) NOT NULL AUTO_INCREMENT,  `efid` int(11) NOT NULL,  `field` varchar(255) NOT NULL,  `value` mediumtext NOT NULL,  `trueorfalse` enum('true','false') NOT NULL,  `deletetemplaterow` enum('n','y') NOT NULL DEFAULT 'n',  `displayvalueintext` enum('n','y') NOT NULL DEFAULT 'n',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`conid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "extrafields` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `ordering` mediumint(9) NOT NULL DEFAULT '0',  `tabletype` varchar(255) NOT NULL DEFAULT 'entity',  `hidden` enum('n','y','a') NOT NULL DEFAULT 'n',  `location` varchar(40) NOT NULL DEFAULT '',  `deleted` enum('n','y') NOT NULL DEFAULT 'n',  `fieldtype` varchar(50) NOT NULL DEFAULT '',  `name` varchar(250) NOT NULL DEFAULT '',  `displaylistname` varchar(250) NOT NULL,  `options` longtext NOT NULL,  `optioncolors` longtext NOT NULL,  `forcing` enum('n','y') NOT NULL DEFAULT 'n',  `defaultval` longtext,  `sort` enum('n','y') NOT NULL DEFAULT 'n',  `storetype` enum('default','3rd_table','3d_table_popup') NOT NULL,  `accessarray` longtext NOT NULL,  `size` int(11) NOT NULL,  `table` int(11) DEFAULT '0',  `showsearchbox` enum('n','y') NOT NULL DEFAULT 'n',  `limitddtowidth` int(11) NOT NULL,  `allowuserstoaddoptions` enum('n','y') NOT NULL DEFAULT 'n',  `israwhtml` enum('n','y') NOT NULL,  `remarks` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `lastchangeby` int(11) NOT NULL,  `showasradio` enum('n','y') NOT NULL,  `excludefromfilters` enum('n','y') NOT NULL,  `underwaterfield` enum('n','y') NOT NULL,  `sum_column` enum('yes','no') NOT NULL,  `number_format` varchar(50) NOT NULL DEFAULT 'normal',  PRIMARY KEY (`id`),  KEY `location` (`location`),  KEY `tabletype` (`tabletype`(1))) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Extra field definitions';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "failoverquerystore` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `query` mediumblob NOT NULL,  `targethost` varchar(100) NOT NULL,  `lockhash` varchar(100) NOT NULL,  `microtime_float` decimal(20,4) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Fail-over queries to replicate';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "flextabledefs` (  `recordid` int(11) NOT NULL AUTO_INCREMENT,  `tablename` varchar(128) NOT NULL,  `orientation` enum('many_entities_to_one','one_entity_to_many') NOT NULL,  `formid` int(11) NOT NULL,  `refers_to` varchar(100) NOT NULL,  `refer_field_layout` text NOT NULL,  `table_layout` longtext NOT NULL,  `accessarray` longtext NOT NULL,  `comment` varchar(250) NOT NULL,  `maxrowsperpage` tinyint(4) NOT NULL,  `headerhtml` mediumtext NOT NULL,  `addlinktext` varchar(255) NOT NULL,  `sumnumrows` enum('y','n') NOT NULL,  `compact_view` enum('n','y') NOT NULL,  `add_in_popup` enum('y','n') NOT NULL,  `access_controlled_by_field` int(11) NOT NULL,  `access_denied_method` enum('readonly','invisible') NOT NULL,  `sort_on` int(11) NOT NULL,  `sort_direction` enum('Ascending','Descending') NOT NULL,  `tableheaderrepeat` int(11) NOT NULL,  `showfilters` enum('y','n') NOT NULL,  `exclude_from_rep` enum('n','y') NOT NULL,  `users_may_select_columns` enum('y','n') NOT NULL,  `skip_security` enum('n','y') NOT NULL,  `stayinformaftersave` enum('n','y') NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`recordid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave flextable definition table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "help` (  `helpid` int(11) NOT NULL AUTO_INCREMENT,  `title` varchar(240) NOT NULL DEFAULT '',  `content` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`helpid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Help contents table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "internalmessages` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `to` int(11) NOT NULL DEFAULT '0',  `from` int(11) NOT NULL DEFAULT '0',  `subject` varchar(255) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `read` enum('n','y') NOT NULL DEFAULT 'n',  `dub_count` smallint(6) NOT NULL DEFAULT '1',  `body` longtext NOT NULL,  PRIMARY KEY (`id`),  KEY `to` (`to`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Internal messages (user-to-user)';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "journal` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `eid` int(11) NOT NULL DEFAULT '0',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `user` int(11) NOT NULL DEFAULT '0',  `message` longtext NOT NULL,  `type` varchar(20) NOT NULL DEFAULT 'entity',  PRIMARY KEY (`id`),  KEY `eid` (`eid`,`user`),  KEY `type` (`type`),  KEY `timestamp` (`timestamp_last_change`),  KEY `user` (`user`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Entity/customer journal';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "languages` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `LANGID` varchar(15) NOT NULL DEFAULT '',  `TEXTID` varchar(30) NOT NULL DEFAULT '',  `TEXT` varchar(255) NOT NULL DEFAULT '',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `LANGID` (`LANGID`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave language table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "loginusers` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(50) NOT NULL DEFAULT '',  `password` varchar(50) DEFAULT NULL,  `PROFILE` varchar(10) NOT NULL DEFAULT '',  `type` enum('normal','limited') NOT NULL DEFAULT 'normal',  `active` enum('yes','no') NOT NULL DEFAULT 'yes',  `exptime` varchar(40) NOT NULL DEFAULT '',  `noexp` enum('n','y') NOT NULL DEFAULT 'n',  `openidurl` mediumtext NOT NULL,  `administrator` enum('yes','no') NOT NULL DEFAULT 'no',  `FULLNAME` varchar(150) NOT NULL DEFAULT '',  `EMAIL` varchar(150) NOT NULL DEFAULT '',  `CLLEVEL` longtext NOT NULL,  `LASTFILTER` longtext NOT NULL,  `LASTSORT` varchar(50) NOT NULL DEFAULT '',  `RECEIVEDAILYMAIL` enum('No','Yes') NOT NULL DEFAULT 'No',  `RECEIVEALLOWNERUPDATES` enum('n','y') NOT NULL DEFAULT 'n',  `RECEIVEALLASSIGNEEUPDATES` enum('n','y') NOT NULL DEFAULT 'n',  `HIDEADDTAB` char(1) NOT NULL DEFAULT '',  `HIDECSVTAB` char(1) NOT NULL DEFAULT '',  `HIDESUMMARYTAB` char(1) NOT NULL DEFAULT '',  `HIDEENTITYTAB` char(1) NOT NULL DEFAULT '',  `HIDEPBTAB` char(1) NOT NULL DEFAULT '',  `SHOWDELETEDVIEWOPTION` char(1) NOT NULL DEFAULT '',  `HIDECUSTOMERTAB` char(1) NOT NULL DEFAULT '',  `SAVEDSEARCHES` longtext NOT NULL,  `EMAILCREDENTIALS` longtext NOT NULL,  `ENTITYADDFORM` varchar(10) NOT NULL DEFAULT '',  `ENTITYEDITFORM` varchar(10) NOT NULL DEFAULT '',  `LIMITTOCUSTOMERS` longtext NOT NULL,  `ADDFORMS` longtext NOT NULL,  `ELISTLAYOUT` mediumtext NOT NULL,  `CLISTLAYOUT` mediumtext NOT NULL,  `ALLOWEDSTATUSVARS` longtext NOT NULL,  `ALLOWEDPRIORITYVARS` longtext NOT NULL,  `HIDEFROMASSIGNEEANDOWNERLISTS` enum('n','y') NOT NULL,  `FORCEFORM` varchar(100) NOT NULL DEFAULT 'no_force',  `USEDASHBOARDASENTRY` enum('n','y') NOT NULL,  `HIDEOVERDUEFROMDUELIST` enum('n','y') NOT NULL,  `DASHBOARDFILEID` int(11) NOT NULL,  `DASHBOARDCACHE` longtext NOT NULL,  `IMPORTANTENTITIES` varchar(255) NOT NULL,  `BOSS` int(11) NOT NULL,  `TRACE` enum('Off','On') NOT NULL,  `FORCEPASSCHANGE` enum('n','y') NOT NULL DEFAULT 'n',  `LASTPASSCHANGE` datetime NOT NULL,  `MENUTOUSE` varchar(7) NOT NULL DEFAULT 'default',  `FORCESTARTFORM` int(11) NOT NULL DEFAULT '0',  `ALLOWEDADDFORMS` longtext NOT NULL,  `ENTITYACCESSEVALMODULE` int(11) NOT NULL,  `INTERACTIVEFIELDS` longtext NOT NULL,  `USERSPECTRUM` enum('all','in_group','customer_related','none') NOT NULL,  `CUSTOMERACCESSEVALMODULE` int(11) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `name` (`name`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave User definition table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "mailqueue` (  `queueid` int(11) NOT NULL AUTO_INCREMENT,  `user` int(11) NOT NULL,  `template` longtext NOT NULL,  `entity` int(11) NOT NULL,  `customer` int(11) NOT NULL,  `from` varchar(512) NOT NULL DEFAULT '',  `fromname` varchar(512) NOT NULL DEFAULT '',  `to` varchar(1024) NOT NULL DEFAULT '',  `PDF` enum('not','used') NOT NULL,  `subject` varchar(1024) NOT NULL DEFAULT '',  `attach_to_dossier` enum('','both','entity','customer') NOT NULL,  `attach_as_filename` varchar(250) NOT NULL,  `report_attach` varchar(20) NOT NULL,  `flextableid` int(11) NOT NULL,  `flextablerecord` int(11) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `date_sent` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',  `status` enum('unsent','sent','error') DEFAULT NULL,  `worker_hash` varchar(64) NOT NULL DEFAULT '',  `date_queued` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',  PRIMARY KEY (`queueid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave mail queue';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "modules` (  `mid` mediumint(9) NOT NULL AUTO_INCREMENT,  `module_name` varchar(255) NOT NULL,  `module_description` mediumtext NOT NULL,  `module_add_by` int(11) NOT NULL,  `module_add_date` varchar(50) NOT NULL,  `module_last_run_result` mediumtext NOT NULL,  `module_last_run_date` varchar(50) NOT NULL,  `module_code` mediumtext NOT NULL,  `module_accessarray` longtext NOT NULL,  `module_list_html` varchar(255) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, for_table VARCHAR(12) NOT NULL DEFAULT 'entity', PRIMARY KEY (`mid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Module code table';";
//	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "phonebook` (  `id` mediumint(9) NOT NULL AUTO_INCREMENT,  `Firstname` varchar(50) NOT NULL DEFAULT '',  `Lastname` varchar(50) NOT NULL DEFAULT '',  `Telephone` varchar(15) NOT NULL DEFAULT '',  `Customer` int(50) NOT NULL,  `Department` varchar(50) NOT NULL DEFAULT '',  `Title` varchar(100) NOT NULL DEFAULT '',  `Room` varchar(60) NOT NULL DEFAULT '',  `Email` varchar(60) NOT NULL DEFAULT '',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Phone book table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "priorityvars` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `varname` varchar(250) NOT NULL,  `color` varchar(7) NOT NULL DEFAULT '',  `listorder` int(11) DEFAULT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `varname` (`varname`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Priority definitions table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "publishedpages` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `repository` smallint(6) NOT NULL,  `visible_from` bigint(9) NOT NULL,  `visible_until` bigint(9) NOT NULL,  `as_user` smallint(6) NOT NULL,  `type` enum('page','report','form') NOT NULL DEFAULT 'page',  `report_query` longtext NOT NULL,  `description` varchar(255) DEFAULT NULL,  `template` mediumint(9) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Published pages table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "publishedpagescache` (  `pagecacheid` int(11) NOT NULL AUTO_INCREMENT,  `pageid` int(11) NOT NULL,  `userid` int(11) NOT NULL,  `formid` int(11) NOT NULL,  `eid` int(11) NOT NULL,  `content` longtext NOT NULL,  `reportmd5` varchar(250) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`pagecacheid`),  KEY `pageid` (`pageid`),  KEY `formid` (`formid`),  KEY `eid` (`eid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Interleave Cache of published pages';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "sessions` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(50) NOT NULL DEFAULT '',  `temp` varchar(32) NOT NULL,  `active` enum('yes','no') NOT NULL DEFAULT 'yes',  `exptime` varchar(40) NOT NULL DEFAULT '',  `noexp` enum('n','y') NOT NULL DEFAULT 'n',  `sessioncache` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  UNIQUE KEY `temp` (`temp`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Session table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "settings` (  `settingid` int(11) NOT NULL AUTO_INCREMENT,  `setting` varchar(150) NOT NULL DEFAULT '',  `value` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `discription` varchar(250) NOT NULL DEFAULT '',  PRIMARY KEY (`settingid`),  KEY `setting` (`setting`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Main settings table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "statusvars` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `varname` varchar(250) NOT NULL,  `color` varchar(7) NOT NULL DEFAULT '',  `listorder` int(11) DEFAULT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `varname` (`varname`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Status definitions table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "tabmenudefinitions` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `menu_name` varchar(255) NOT NULL,  `menu_type` varchar(255) NOT NULL DEFAULT 'Tabbed',  `menu_array` longtext NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `menu_name` (`menu_name`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave custom tab menu definitions';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "templates` (  `templateid` int(11) NOT NULL AUTO_INCREMENT,  `templatename` varchar(200) NOT NULL DEFAULT '',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `templatetype` varchar(150) NOT NULL DEFAULT '',  `username` varchar(150) NOT NULL DEFAULT '',  `template_subject` varchar(250) NOT NULL DEFAULT '',  `show_on_add_list` enum('y','n') NOT NULL,  `stylesheet` int(11) NOT NULL,  `orientation` enum('P','L') DEFAULT NULL,  `content` mediumtext NOT NULL,  `binary_content` mediumblob NOT NULL,  PRIMARY KEY (`templateid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave templates';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "todo` (  `todoid` int(11) NOT NULL AUTO_INCREMENT,  `onchange` varchar(255) NOT NULL,  `from_value` mediumtext NOT NULL,  `to_value` mediumtext NOT NULL,  `eid` int(11) NOT NULL,  `user` int(11) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`todoid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "triggerconditions` (  `conid` int(11) NOT NULL AUTO_INCREMENT,  `triggerid` int(11) NOT NULL,  `field` varchar(255) NOT NULL,  `value` mediumtext NOT NULL,  `trueorfalse` enum('true','false') NOT NULL,  `failmessage` varchar(255) NOT NULL,  `successmessage` varchar(255) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`conid`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "triggers` (  `tid` int(11) NOT NULL AUTO_INCREMENT,  `onchange` varchar(200) NOT NULL DEFAULT '',  `processorder` int(11) NOT NULL,  `action` longtext NOT NULL,  `to_value` varchar(100) NOT NULL DEFAULT '',  `template_fileid` int(11) NOT NULL DEFAULT '0',  `mailtype` enum('email','inmail') NOT NULL DEFAULT 'email',  `on_form` varchar(10) NOT NULL DEFAULT 'all',  `attach` enum('n','y') NOT NULL DEFAULT 'n',  `report_fileid` int(11) NOT NULL DEFAULT '0',  `comment` longtext NOT NULL,  `enabled` enum('yes','no') NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`tid`),  KEY `to_value` (`to_value`),  KEY `onchange` (`onchange`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Entity value change trigger table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "uselog` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `ip` varchar(15) NOT NULL DEFAULT '',  `url` varchar(50) NOT NULL DEFAULT '',  `useragent` varchar(255) NOT NULL DEFAULT '',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  `qs` longtext NOT NULL,  `user` varchar(50) NOT NULL DEFAULT '',  PRIMARY KEY (`id`),  KEY `tijd` (`timestamp_last_change`),  KEY `url` (`url`),  KEY `ip` (`ip`),  KEY `user` (`user`),  KEY `qs` (`qs`(30))) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave Main activity log table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "userprofiles` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `name` varchar(50) NOT NULL DEFAULT '',  `ENTITYADDFORM` varchar(50) NOT NULL DEFAULT '',  `ENTITYEDITFORM` varchar(50) NOT NULL DEFAULT '',  `active` enum('yes','no') NOT NULL DEFAULT 'yes',  `CLLEVEL` longtext NOT NULL,  `RECEIVEDAILYMAIL` enum('No','Yes') NOT NULL DEFAULT 'No',  `RECEIVEALLOWNERUPDATES` enum('n','y') NOT NULL DEFAULT 'n',  `RECEIVEALLASSIGNEEUPDATES` enum('n','y') NOT NULL DEFAULT 'n',  `HIDEADDTAB` char(1) NOT NULL DEFAULT '',  `HIDECSVTAB` char(1) NOT NULL DEFAULT '',  `HIDESUMMARYTAB` char(1) NOT NULL DEFAULT '',  `HIDEENTITYTAB` char(1) NOT NULL DEFAULT '',  `HIDEPBTAB` char(1) NOT NULL DEFAULT '',  `SHOWDELETEDVIEWOPTION` char(1) NOT NULL DEFAULT '',  `HIDECUSTOMERTAB` char(1) NOT NULL DEFAULT '',  `SAVEDSEARCHES` longtext NOT NULL,  `EMAILCREDENTIALS` longtext NOT NULL,  `LIMITTOCUSTOMERS` longtext NOT NULL,  `ADDFORMS` longtext NOT NULL,  `ALLOWEDSTATUSVARS` longtext NOT NULL,  `ALLOWEDPRIORITYVARS` longtext NOT NULL,  `FORCEFORM` varchar(100) NOT NULL DEFAULT 'no_force',  `USEDASHBOARDASENTRY` enum('n','y') NOT NULL,  `HIDEOVERDUEFROMDUELIST` enum('n','y') NOT NULL,  `DASHBOARDFILEID` int(11) NOT NULL,  `BOSS` int(11) NOT NULL,  `ELISTLAYOUT` mediumtext NOT NULL,  `CLISTLAYOUT` mediumtext NOT NULL,  `MENUTOUSE` varchar(7) NOT NULL DEFAULT 'default',  `FORCESTARTFORM` int(11) NOT NULL DEFAULT '0',  `FORCEUSERCLLIMIT` enum('n','y') NOT NULL,  `ALLOWEDADDFORMS` longtext NOT NULL,  `ENTITYACCESSEVALMODULE` int(11) NOT NULL,  `INTERACTIVEFIELDS` longtext NOT NULL,  `USERSPECTRUM` enum('all','in_group','customer_related','none') NOT NULL,  `CUSTOMERACCESSEVALMODULE` int(11) NOT NULL,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`id`),  KEY `name` (`name`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Interleave User profile definition table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "webdav_locks` (  `token` varchar(255) NOT NULL DEFAULT '',  `path` varchar(200) NOT NULL DEFAULT '',  `expires` int(11) NOT NULL DEFAULT '0',  `owner` varchar(200) DEFAULT NULL,  `recursive` int(11) DEFAULT '0',  `writelock` int(11) DEFAULT '0',  `exclusivelock` int(11) NOT NULL DEFAULT '0',  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  PRIMARY KEY (`token`),  KEY `path` (`path`),  KEY `expires` (`expires`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Interleave Webdav file locks table';";
	 $sqla[] = "CREATE TABLE `" . $GLOBALS['TBL_PREFIX'] . "webdav_properties` (  `path` varchar(255) NOT NULL DEFAULT '',  `name` varchar(120) NOT NULL DEFAULT '',  `ns` varchar(120) NOT NULL DEFAULT 'DAV:',  `value` mediumtext,  `timestamp_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Interleave Webdav properties';";
	return($sqla);
} // end func
function datafill($fstaccount,$fstpwd1,$admemail,$cname,$logo)
{
	global $version;
	$sqla = array();
	// 1st Admin user (the dude who installs me)
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "loginusers (name,password,administrator,CLLEVEL,FULLNAME) VALUES('" . mres($fstaccount) . "',PASSWORD('" . mres($fstpwd1) . "'),'Yes','rw','Administrator " . mres($fstaccount) . "')");
	
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings VALUES (1, 'title', '" . mres($cname) . "', 20020520184301, 'Will appear almost anywhere.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings VALUES (4, 'admpassword', '*NONE*', 20020520162029, 'Administration password, *NONE* disables the authentication at all.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings VALUES (5, 'mipassword', '*NONE*', 20020520184155, 'Management Information password, *NONE* disables the authentication at all.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings VALUES (6, 'managementinterface', 'Off', 20020520210325, 'When set to \'on\', users authenticated as a limited user will only see the restricted managementinterface with very limited priviledges. Opposite to \'on\' is \'off\'.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings VALUES (7, 'admemail', '" . mres($admemail) . "', 20020520140033, 'The administrators email-addres.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings VALUES (8, 'cronpassword', '', 20020520180654, 'The password used by the HTTP-GET crond job (duedate-notify-cron.php)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings VALUES (9, 'timeout','20','','Number of minutes before a user is automatically logged off when there is no activity');");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change , discription) VALUES ('', 'Logon message', '', NOW(), 'This message will be displayed when a user logs in.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'navtype', 'TABS', NOW(), 'Navigation bar type. Use NOTABS for normal navigation, anything else for tabs')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'langoverride', 'No', NOW(), 'Language override. No to let the user be able to choose his or her own language, yes to disable this feature and thereby force the use of the system-wide language choosen hereunder.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EmailNewEntities', '', NOW(), 'The e-mail address to which notifications of added entities should be mailed. Leave empty for no notification.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'MonthsToShow', '7', NOW(), 'The number of months to show in the various calendar appearances.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowDeletedViewOption', 'No', NOW(), 'Wheter or not Interleave should display a menu tab to view the deleted entities. Options are yes or no.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EnableCustInsert', 'No', NOW(), 'Set this to yes to enable the [customer] insert functionality, no to keep customers from logging in even if they have a customer account.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'SMTPserver', 'localhost', NOW(), 'The hostname or IP-address of your SMTP (outgoing mail) server')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BODY_DUEDATE', '<body><strong>An alarm date of one of your entities has been reached.</strong><br><br>This email is concerning entity @ENTITYID@, @CATEGORY@<br><br><br>The history of this entity is printed below.<br>If this email was not intended for you, please contact owner @OWNER@ or assignee @ASSIGNEE@.<br><br>History:<br><br><table BORDER=1><tr><td>@CONTENTS@.</td></tr></table><br><br>If you do nothing, you will <em>not</em> be reminded about this entity again.<br><br>End of this e-mail.<br><table><tr><td>Entity:</td><td>@ENTITYID@</td></tr><tr><td>Category:</td><td>@CATEGORY@</td></tr><tr><td>Owner:</td><td>@OWNER@</td></tr><tr><td>Assignee:</td><td>@ASSIGNEE@</td></tr><tr><td>Contents:</td><td>See attachment</td></tr><tr><td>Admin email:</td><td>@ADMINEMAIL@</td></tr><tr><td>Webhost:</td><td>@WEBHOST@</td></tr><tr><td>Title:</td><td>@TITLE@</td></tr><tr><td>Customer:</td><td>@CUSTOMER@</td></tr><tr><td>Due-date:</td><td>@DUEDATE@</td></tr><tr><td>Status:</td><td>@STATUS@</td></tr><tr><td>Priority:</td><td>@PRIORITY@</td></tr></table></body>', NOW(), 'The body of the email which will be sent to an assignee when an alarm date of a certain entity is met. Please read the manual before editing this setting.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BODY_ENTITY_ADD', '<body><strong>A new entity was added to repository \"@TITLE@\"</strong><br><br>This email is concerning a new entity with category \"@CATEGORY@\"<br>This entity will be available in your Interleave installation at @WEBHOST@ under EID number @ENTITYID@.<br><br>If this email was not intended for you, please contact @ADMINEMAIL@<br><br><table><tr><td>Entity:</td><td>@ENTITYID@</td></tr><tr><td>Category:</td><td>@CATEGORY@</td></tr><tr><td>Owner:</td><td>@OWNER@</td></tr><tr><td>Assignee:</td><td>@ASSIGNEE@</td></tr><tr><td>Contents:</td><td>See attachment</td></tr><tr><td>Admin email:</td><td>@ADMINEMAIL@</td></tr><tr><td>Webhost:</td><td>@WEBHOST@</td></tr><tr><td>Title:</td><td>@TITLE@</td></tr><tr><td>Customer:</td><td>@CUSTOMER@</td></tr><tr><td>Due-date:</td><td>@DUEDATE@</td></tr><tr><td>Status:</td><td>@STATUS@</td></tr><tr><td>Priority:</td><td>@PRIORITY@</td></tr></table></body>', NOW(), 'The body of the email which will be sent when a new entity is added. Please read the manual before editing this setting.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BODY_ENTITY_EDIT', '<body><strong>One of your entities in repository \"@TITLE@\" was updated.</strong><br><br>This email is concerning your entity with category \"@CATEGORY@\"<br>This entity is available in your Interleave installation at @WEBHOST@ under EID number @ENTITYID@. <br><br>If this email was not intended for you, please contact @ADMINEMAIL@<br><br><table><tr><td>Entity:</td><td>@ENTITYID@</td></tr><tr><td>Category:</td><td>@CATEGORY@</td></tr><tr><td>Owner:</td><td>@OWNER@</td></tr><tr><td>Assignee:</td><td>@ASSIGNEE@</td></tr><tr><td>Contents:</td><td>See attachment</td></tr><tr><td>Admin email:</td><td>@ADMINEMAIL@</td></tr><tr><td>Webhost:</td><td>@WEBHOST@</td></tr><tr><td>Title:</td><td>@TITLE@</td></tr><tr><td>Customer:</td><td>@CUSTOMER@</td></tr><tr><td>Due-date:</td><td>@DUEDATE@</td></tr><tr><td>Status:</td><td>@STATUS@</td></tr><tr><td>Priority:</td><td>@PRIORITY@</td></tr></table></body>', NOW(), 'The body of the email which will be sent when an entity is updated. Please read the manual before editing this setting.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowNumOfAttachments', 'No', NOW(), 'Wether or not to show the number of attached documents in the main entity lists')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowEmailButton', 'Yes', NOW(), 'Yes to show an extra button to send an e-mail to the assignee when an entity is added or edited, no to disable this option.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowMainPageCalendar', 'Yes', NOW(), 'Yes to show the 3-month calendar on the main page, no to disable this option.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'Category pulldown list', '', NOW() , '')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ForceCategoryPulldown', 'No', NOW() , 'Yes to show a pulldown list for the category, no to make it a text box.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowRecentEditedEntities', '7', NOW(), '0 for no recent list, any number under 20 to show the most recent edited entities on the main page.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EnableEntityJournaling', 'Yes', NOW() , 'Set this value to Yes if you want entity journaling enabled (a link will be added to the main edit entity page)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'AutoCompleteCustomerNames', 'No', NOW() , 'Set this value to Yes if you want a text box wich auto-completes customer names instead of a pull-down menu with all customers.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EnableEntityContentsJournaling', 'Yes', NOW() , 'Set this value to Yes if you want a drop-down box in the main entity window to switch to history states of an entity')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DontShowPopupWindow', 'Yes', NOW() , 'No to show the javascript popup window in the entity overview, yes to disable it and make editing the entity the default action when clicking on the row.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowFilterInMainList', 'Yes', NOW() , 'Wether or not to show the filter pulldowns on top of the main entity list')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'LetUserSelectOwnListLayout', 'Yes', NOW() , 'Wether or not to let the user select his/her own list layout')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BODY_TEMPLATE_CUSTOMER', '<p>&gt;&gt; This mail is regarding entity @ENTITYID@ , \"@CATEGORY@\" in Interleave @TITLE@ at @WEBHOST@<br>-----------------------<br>Send from Interleave <br><a href=\"http://www.interleave.nl/\">http://www.interleave.nl</a><br></p>', NOW() , 'The template wich is used when sending an e-mail to a customer (editable by user before sending)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowPDWASLink', 'No', NOW() , 'Yes to show the PDWAS link in the file list. PDWAS is a Interleave addon which enables you to edit flies and directly save them to Interleave without having to upload the file manually.')");
//	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EnableWebDAVSubsystem', 'No', NOW() , 'Yes to enable the WebDAV subsystem, no to disable it')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DateFormat', 'dd-mm-yyyy', NOW() , 'Enter \'mm-dd-yyyy\' here to display dates in US format, anything else to display dates in international format (which is dd-mm-yyyy).')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'HideCustomerTab', 'No', NOW() , 'Set this value to \'Yes\' if you want the customer tab only to be visible to administrators')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'CustomerListColumnsToShow', 'a:5:{s:2:\"id\";b:1;s:11:\"cb_custname\";b:1;s:10:\"cb_contact\";b:1;s:16:\"cb_contact_phone\";b:1;s:9:\"cb_active\";b:1;}', NOW() , 'The columns to show in the customer list')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ShowSaveAsNewEntityButton', 'Yes', NOW() , 'Yes to show the Save As New Entity button, no to hide it.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'AutoCompleteCategory', 'Yes', NOW() , 'Enter Yes of you would like type-ahead functionality in the category field on the main entity page')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'AutoInsertDateTime', 'No', NOW() , 'Enter Yes of you would like the date and time information inserted automatically when adding text to an entity.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'LetUserEditOwnProfile', 'Yes', NOW() , 'Set this option to \'Yes\' to enable user to change their passwords, edit their full name, and select wether or not they want to receive the daily entity overwiew email.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EnableRepositorySwitcher', 'Yes', NOW() , 'Set this option to \'Yes\' to enable a user to dynamically switch between repositories in which the same users exist with the same password. \'No\' disables this, \'Admin\' enables it only for admins.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BODY_ENTITY_CUSTOMER_ADD', 'You are registerd to customer @CUSTOMER@. Entity @ENTITYID@ was just coupled to that customer, so you might have to do something.', NOW() , 'The body of the e-mail which is send to the customer_owner when an entity (new or existing) is coupled to that customer, and the email_customer_upon_action checkbox in the customer properties is checked.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BlockAllCSVDownloads', 'No', NOW() , 'Set this value to Yes if you want to block all CSV/Excel downloads for all users except for administrators.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'STANDARD_TEXT', '', NOW() , 'A list of standard comments which users can automatically insert as a reply in entities. Leave empty for no standard comments.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'CUSTOMER_LIST_TRESHOLD', '150', NOW() , 'The number of customers listed on the main customers page. If this number of customers is exceeded, the list will not appear for bandwith reasons.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ALSO_PROCESS_DELETED', 'No', NOW() , 'Set this option to Yes if you want the duedate notify script to also process entities on their duedate, even if the entity is deleted.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BODY_EMAILINSERT_REPLY', '<p><strong>Your e-mail was added to repository @TITLE@</strong><br></p> <p> The number is : @EID@ Number of attachments saved: @NUM_ATTM@ </p>', NOW() , 'The body of the e-mail which is send as a reply to people who use the email_in script to log an entity')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'SUBJECT_EMAILINSERT_REPLY', 'Your e-mail to Interleave was saved under number @EID@ in repository @TITLE@', NOW() , 'The subject of the e-mail which is send as a reply to people who use the email_in script to log an entity')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ForceSecureHTTP', 'No', NOW() , 'If set to yes, Interleave will redirect the user to the HTTPS equivalent of the URL he/she is using, to force secure browsing. Your webserver must be configured to accept this.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'BODY_MainPageMessage', '', NOW() , 'When set, this message will be displayed on the main page.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'PDF-ExtraFieldsInTable', 'No', NOW() , 'Set this value to Yes to have extra fields in PDF reports show up in a table instead of each value being printed on a new line.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EnableEntityReporting', 'Yes', NOW() , 'Set this value to Yes to be able to create per-entity or batch RTF reports (a word-icon will appear on the edit entity page and a link will be added to the main page)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DisplayNOToptioninfilters', 'No', NOW() , 'Set this value to Yes to have all filter drop-down boxes also contain logical NOT-operands, like status NOT open.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'AUTOASSIGNINCOMINGENTITIES', 'No', NOW() , 'Set this option to Yes to automatically assign incoming entities to the owner of the customer.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'FORCEDFIELDSTEXT', 'This message is not configured (see admin section). Probably you missed some fields in your form. ', NOW() , 'The message which is displayed when a user did not fill in all required form fields.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EnableEntityLocking', 'Yes', NOW() , 'Set this to Yes to enable entity locking to prevent two people from editing the same entity')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DFT_FOREGROUND_COLOR', '#3f4d7b', NOW() , 'The color of links')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DFT_FORM_COLOR', '#494949', NOW() , 'The color form elements and values')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DFT_PLAIN_COLOR', '#000000', NOW() , 'The color of normal, non-linked, non-form text')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DFT_LEGEND_COLOR', '#3366FF', NOW() , 'The color of fieldset legends')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DFT_FONT', 'Pontano, Helvetica, Arial, Verdana, Sans-serif', NOW() , 'The main font')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DFT_FONT_SIZE', '14', NOW() , 'The main font size')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'REQUIREDDEFAULTFIELDS', 'No', NOW() , 'SHOULD NOT BE VISIBLE')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'MailMethod', 'smtp', NOW() , 'The method to use for sending mail. Can be either sendmail, mail (=PHP mail) or smtp.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'MailUser', '', NOW() , 'The username of your authenticated SMTP-server (only when using authenticated SMTP)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'MailPass', '', NOW() , 'The password of your authenticated SMTP-server (only when using authenticated SMTP)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'UseWaitingAndDoesntBelongHere', 'No', NOW() , 'Set this value to Yes to enable the (old) waiting and doesnt belong here fields')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'PersonalTabs', '', NOW() , 'Set this to Yes to disable the main entity comment field')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DisableCommentField', 'No', NOW() , 'Set this to Yes to disable the main entity comment field')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'EMAILINBOX', '', NOW() , 'The credentials for the read-only access to an POP3 e-mail inbox')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('HIDEADDTAB', 'No', NOW() , 'Set this to Yes to hide the second tab used to add entities')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('HIDECSVTAB', 'No', NOW() , 'Set this to Yes to hide the CSV tab used to download Interleave exports')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('HIDEPBTAB', 'No', NOW() , 'Set this to Yes to hide the phone book tab')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('HIDESUMMARYTAB', 'No', NOW() , 'Set this to Yes to hide the summary tab')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('HIDEENTITYTAB', 'No', NOW() , 'Set this to Yes to hide main entity list tab')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('CAL_MINHOUR', '7', NOW() , 'Starting hour of day, used for scheduling enties')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('CAL_MAXHOUR', '18', NOW() , 'Ending hour of day, used for scheduling enties (24h format: for 6pm use 18')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('CAL_USEWEEKEND', 'No', NOW() , 'Wheter or not to also show the weekend days in the week view of the calendar')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('ShowMainPageLinks', '', NOW() , 'Some links to show on the main page. Leave empty for no links')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('EnableMailMergeAndInvoicing', 'No', NOW() , 'Set to Yes to enable mail merges and invoicing (even then, only for admins)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('DefaultVAT', '19', NOW() , 'Default VAT percentage (only for use with invoicing)')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('subject_new_entity', 'A new entity was added to repository @TITLE@ (@CATEGORY@)', NOW() , 'The subject of the mail which is send when a new entity is added')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('subject_customer_couple', 'Your customer got a new entity coupled in repository @TITLE@', NOW() , 'The subject of the mail which is send to a customer owner when a new entity is coupled to his/her customer')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('subject_update_entity', 'One of your entities was updated in repository @TITLE@', NOW() , 'The subject of the mail which is send to a user owner when his/her entity was updated')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting , value , timestamp_last_change, discription) VALUES ('subject_alarm', 'Alarm notification for entity @ENTITYID@ (@CATEGORY@)', NOW() , 'The subject of the mail which is send to a user owner when his/her entity reaches an alarm date')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting,value,discription) VALUES('MainListColumnsToShow','a:9:{s:2:\"id\";b:1;s:7:\"cb_cust\";b:1;s:8:\"cb_owner\";b:1;s:11:\"cb_assignee\";b:1;s:9:\"cb_status\";b:1;s:11:\"cb_priority\";b:1;s:11:\"cb_category\";b:1;s:10:\"cb_duedate\";b:1;s:12:\"cb_alarmdate\";b:1;}','non-editable by admin')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting,value,discription) VALUES('DBVERSION','" . $version . "','The current database version')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'RSS_FEEDS', '', NOW( ) , 'The list of RSS feeds to serve. No list, no RSS.')");
	// Status and priority
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "statusvars(varname,color) VALUES('Open','#66CC66')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "statusvars(varname,color) VALUES('Closed','#FF6633')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "statusvars(varname,color) VALUES('Awaiting closure','#3399CC')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "priorityvars(varname,color) VALUES('Critical','#FF6666')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "priorityvars(varname,color) VALUES('High','#FFFF66')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "priorityvars(varname,color) VALUES('Medium','#FFFF99')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "priorityvars(varname,color) VALUES('Low','#FFFFCC')");
	// Language
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages (id , LANGID , TEXTID , TEXT) VALUES ('', 'ENGLISH', 'stillchecked1', 'This file is still locked for editing by')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages (id , LANGID , TEXTID , TEXT) VALUES ('', 'ENGLISH', 'stillchecked2', '. Please stop editing this file before trying to check it in.')");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages (id , LANGID , TEXTID , TEXT) VALUES ('', 'ENGLISH', 'saveasnewentity', 'Save as new entity')");
	// Dynamic stuff
	$val = ini_get("session.save_path");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'TMP_FILE_PATH', '" . $val . "', NOW() , 'The path to the directory where Interleave (the user under which your webserver runs) can store temporary files.')");

	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'ENTITY_ADD_FORM', 'Default', NOW( ) , 'The HTML form template to use when a normal user adds an entity')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'BODY_URGENTMESSAGE', '', NOW( ) , 'When set, this message will be displayed above <strong>all</strong> pages. Only use this for very urgent matters. ')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'AUTH_TYPE', 'Interleave Only', NOW( ) , 'The method to use for authentication. ALWAYS: user must exist in Interleave. HTTP REALM: already authenticated users can log in without a password (INTRANET). LDAP: authentications with an LDAP server (allso fill in LDAP_SERVER, LDAP_PORT, LDAP_PREFIX).')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'ENTITY_EDIT_FORM', 'Default', NOW( ) , 'The HTML form template to use when a normal user edits an entity')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'ENTITY_LIMITED_ADD_FORM', 'Default', NOW( ) , 'The HTML form template to use when a limited user adds an entity')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'ENTITY_LIMITED_EDIT_FORM', 'Default', NOW( ) , 'The HTML form template to use when a limited user edits an entity')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'BODY_LIMITEDHEADER', '', NOW( ) , 'This HTML template will be shown at the top of the limited interface')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'SHOW_ADMIN_TOOLTIPS', 'Yes', NOW( ) , 'Wether or not to display tool-tips in the administrative section.')");
	// Interleave 3.3.2 Update queries
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'EnableEntityRelations', 'No', NOW( ) , 'Set this value to Yes to enable entity relations.')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'HideChildsFromMainList', 'No', NOW( ) , 'When enabled, child entities will no longer show up on the main list.')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'LDAP_SERVER', '', NOW( ) , 'The name of the LDAP server')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'LDAP_PORT', '389', NOW( ) , 'The port of the LDAP server; secure=636, non-secure=389 (Default)')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'LDAP_PREFIX', '', NOW( ) , 'The prefix to use before a username on the LDAP server. End with 1 backslash, no two.')");
	// Interleave 3.4.0 Update queries
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'FormFinity', 'Yes', NOW( ) , 'When set to Yes, entities will \'remember\' what form was used to create them, and the entity will always show up in that form.')");
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'UNIFIED_FROMADDRESS', '', NOW( ) , 'An address entered here, will *always* overwrite the from-address in mails. All mails will have this from-address.')");
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'CUSTOMCUSTOMERFORM', '', NOW( ) , 'When you enter the (valid) number of a customer HTML-form here, all customer records will be shown in that form.')");
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'DISPLAYNUMSUMINMAINLIST', 'Yes', NOW( ) , 'When set to Yes, the total value of numeric fields will be displayed under the main entity list.')");
	// Interleave 3.4.1 Update queries
	array_push($sqla, "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='STASH'");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'USE_EXTENDED_CACHE', 'Yes', NOW( ) , 'Use extensive access rights and extra fields caching. Improves performance.')");
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'CHECKFORDOUBLEADDS', 'Yes', NOW( ) , 'Interleave checks if an entity is not added twice within an hour. If this bothers you, disable this check by setting this to No.')");

	// Interleave 3.4.2 Update queries
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'NOBARSWINDOW', 'No', NOW( ) , 'Set this option to Yes to force a no-bars window');");
	// Interleave 3.4.3 Update queries
	array_push($sqla, "UPDATE `" . $GLOBALS['TBL_PREFIX'] . "userprofiles` SET `ALLOWEDSTATUSVARS`='a:1:{i:0;s:3:\"All\";}'");
	array_push($sqla, "UPDATE `" . $GLOBALS['TBL_PREFIX'] . "loginusers` SET `ALLOWEDSTATUSVARS`='a:1:{i:0;s:3:\"All\";}'");
	array_push($sqla, "UPDATE `" . $GLOBALS['TBL_PREFIX'] . "userprofiles` SET `ALLOWEDPRIORITYVARS`='a:1:{i:0;s:3:\"All\";}'");
	array_push($sqla, "UPDATE `" . $GLOBALS['TBL_PREFIX'] . "loginusers` SET `ALLOWEDPRIORITYVARS`='a:1:{i:0;s:3:\"All\";}'");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('Mass_Update', 'Yes', 'Set this to yes to enable mass entity updates using checkboxes on the main list')");
	array_push($sqla,"DELETE FROM `" . $GLOBALS['TBL_PREFIX'] . "settings` WHERE setting='FormFinity'");
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'PaginateMainEntityList', '30', NOW( ) , '0 for no pagination, [number] for max number of entities per page.');");
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'MAINTENANCE_MODE', 'No', NOW( ) , 'When set to Yes, only administrators can log in. Required when upgrading.');");
	array_push($sqla,"INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('ONEENTITYPERCUSTOMER', 'No', 'When enabled, only one entity per customer may exist.')");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` )VALUES ('', 'ENABLE_SUBSCRIPTIONS', 'Yes', NOW( ) , 'Yes to enable outside users to sign up for an account, No to disable. Needs a master acount!');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` )VALUES ('', 'FAILOVER_CACHEONLY', 'No', NOW( ) , 'When set to Yes, Interleave will cache al queries instead of replicating them. Replication will only be done by the cron job.');");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting,value,discription) VALUES('ENABLEINDEXEDSEARCHING','Yes','Set to Yes will enable fast indexed searching. You need to index your database now and than for this!');");
	array_push($sqla, "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET discription ='The main font size (in pixels)' WHERE discription ='The main font size';");
	array_push($sqla, "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='SHOWPDWASLINK';");
	array_push($sqla,"INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'DISABLE_BINARY_SEARCH', 'No', NOW( ) , 'Set this to Yes to disable automatic searching through binary attachments.')");

	// Interleave 4.0.1 Update queries
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID, TEXTID, TEXT) VALUES('ENGLISH','addyourcommentshere','Add your comments here:')");
	// Interleave 4.1.0 Update queries
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'SYNC_DISABLED_UNTIL', '', NOW( ) , ' Sync is disabled until this timestamp is met. Setting should not be visible for user.');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'SYNC_TIMEOUT', '30', NOW( ) , 'Number of minutes fail-over synchronisation halts before trying again (low values cause user delays)');");
	array_push($sqla, "DELETE FROM `" . $GLOBALS['TBL_PREFIX'] . "settings` WHERE setting='FormFinity';");
	array_push($sqla, "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='No' WHERE setting='FAILOVER_CACHEONLY';");
	// Interleave 4.2.0 Update queries
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'SAFE_MODE', '', NOW( ) , 'Enter a semicolon-separated list of super users here (limits other admins from doing scary things)')");
	// Interleave 4.3.0 Update queries



	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'DASHBOARDTEMPLATE', 'Normal', '', 'Enter the template file id in this field to use that template as dashboard template for all users');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'ENABLEFILEVERSIONING', 'Yes', '', 'Set this variable to Yes to enable the file versioning functions for files attached to entities');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'ENABLEIMAGETHUMBNAILS', 'Yes', '', 'Set this variable to Yes to enable the thumbnail popup when hovering the mouse over an image file name');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'ENABLEOPENIDAUTH', 'No', '', 'Set this to Yes to enable users to enter their OpenID URL in their profile and use OpenID to log in.');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'HIDEMAINTAB', '', 'No', 'Set this variable to Yes to hide the main tab (either Main or Dashboard link)');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'TABSTOHIDE', '', 'No', 'Serialized array of tab names to hide (invisible)');");

	array_push($sqla, "UPDATE " . $GLOBALS['TBL_PREFIX'] . "languages SET TEXT='Save' WHERE TEXT LIKE '%Save to database%';");

	// Interleave 5.0 Update queries

	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'STICKYENTITY', 'No', NOW() , 'Set this option to Yes if you DO NOT WANT to go back to the list after saving an entity')");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'DISABLEENTITYFORMCACHE', 'No', NOW() , 'Set this option to Yes if you DO NOT WANT entity forms to be cached. NOT recommended!')");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ALLOWEDIPADRESSES', '', NOW() , 'Enter semicolon-separated list of allowed IP-adresses to use this application (careful!)')");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'PASSWORDEXPIRE', '90', NOW() , 'The number of days it takes for a password to expire. The user will be forced to change it if it expires.')");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'ONEMAILPERTRIGGER', 'No', NOW() , 'Set this option to Yes to send only one triggered mail per recipient per trigger.')");


	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'CATEGORYBOXSIZE', '50', NOW() , 'The default size of the category field');");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'TABCOLORS', '', NOW() , 'The serialized array containing custom tab colors (hidden)');");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (settingid , setting , value , timestamp_last_change, discription) VALUES ('', 'CLIPLISTAT', '500', NOW() , 'The main entity list will never show more entities than specified in this setting');");
	array_push($sqla, "INSERT INTO `" . $GLOBALS['TBL_PREFIX'] . "settings` ( `settingid` , `setting` , `value` , `timestamp_last_change` , `discription` ) VALUES ('', 'FILECOMPRESSIONLEVEL', 'A', '', 'File compression level, see details below or in manual.');");

	array_push($sqla, "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='ADMPASSWORD'");
	array_push($sqla, "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='MIPASSWORD'");

	array_push($sqla, "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET discription='NO LONGER USED; use triggers!' WHERE setting='EMAILNEWENTITIES'");

	// Interleave 5.1.1 Upgrade queries

	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting, value, discription) VALUES('ALLOWLOGONPAGEPASSCHANGE','Yes','Set this to No to disable the logon-page change-password functionality')");

	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('HIDEENTITYADDTABS','No','Set this to yes to block the sub-tabs on the add-entity screen')");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting, value, discription) VALUES('USE_AUTOSAVE','Yes','Set this value to Yes to enable automatic saving of entity forms in the background')");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting, value, discription) VALUES('EXCELFILEFORMAT','2007','Select Excel export file format - XLS vs. XLSX')");

	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting, value, discription) VALUES('LDAP_BIND_USERNAME','','Bind user for LDAP');");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting, value, discription) VALUES('LDAP_BIND_PASSWORD','','Password for LDAP bind user');");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting, value, discription) VALUES('LDAP_BASE_DN','','LDAP Base DN');");

	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('BODY_ADMIN_ERRORMSG','','Put any HTML in this field to replace the form a user gets with Access Denied event')");


	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings (setting, value, discription) VALUES('USECUSTOMERSELECTPOPUP','No','Set to Yes for a popup-box to select customers instead of the drop-down box')");



	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('MINIMUMPASSWORDSTRENGTH','2','The required password strength for users')");


	array_push($sqla, "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting = 'AUTOCOMPLETECUSTOMERNAMES';");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('PAGINATECUSTOMERLIST','30','The number of customers to show per page');");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('FILELISTSORTORDER','Date','The sort order for all file lists');");
	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('SUBTITLE','- subtitle -','The sub-title of this repository (will be displayed on login page)');");

	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('ENTITYLOCKTIMEOUT','3600','The number of seconds an entity locks lasts');");



	array_push($sqla, "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('SHOWTABLEHEADEREVERY','50','Repeat the table header every XX lines');");

	array_push($sqla, "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET discription='The number of rows to show per page on the recent entities list' WHERE setting='SHOWRECENTEDITEDENTITIES'");

	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('BASEURL','','The base URL to this installation');";
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('DISPLAYSAVEREMINDER','Yes','Whether or not to display the Save dialog when leaving an unsaved entity');";
	
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, discription) VALUES('SYSWIDECSS','','The stylesheet to be loaded on all pages')";
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, timestamp_last_change, discription) values ('ShowIconsAboveMainContentBox', 'Yes', now(), 'Set this option to Yes to show icons above main content box')";

	// Interleave 5.5.0 Upgrade queries
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, timestamp_last_change, discription) values ('ShowDefaultPDFReport', 'Yes', now(), 'Set this option to No to hide the default PDF reports')";
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, timestamp_last_change, discription) values ('ShowMinimalErrorMessages', 'No', now(), 'Set this option to Yes to hide technical details from SQL- and internal Interleave error messages')";

	$sqla[] = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting IN ('ENABLESINGLEENTITYINVOICING', 'INVOICENUMBERPREFIX', 'ENABLEMAILMERGEANDINVOICING')";
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, timestamp_last_change, discription) values ('ShowInactiveCustomers', 'Yes', now(), 'Set this option to Yes to hide inactive customers from the customer list')";
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, timestamp_last_change, discription) values ('UseMailQueue', 'Yes', now(), 'Set this option to No to send e-mails in user session, Yes to let housekeeping send the e-mails.')";
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, timestamp_last_change, discription) values ('TimestampLastHousekeeping', '', now(), 'Internal - refers to last time housekeeping ran')";
	$sqla[] = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "settings(setting, value, timestamp_last_change, discription) values ('TimestampLastDuedateCron', '', now(), 'Internal - refers to last time duedate-notify-cron ran')";

	$sqla[] = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET LASTPASSCHANGE=NOW()";
	return($sqla);
} // end func



function PostInstall() {
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
	AttachFileS("0","SampleHTMLReport","<p><span style='color: #000000;'><img alt=\"\" src=\"images/crm_small.png\">&nbsp;<span style='color: #000000;'>[<a href=\"edit.php?e=@EID@\">edit</a>] <span class='noway'><em>@EID@: @CATEGORY@</em></span><br></span>Attachments: @NUM_ATTM@<br>Owned by @OWNER@, assigned to @ASSIGNEE@<br>For customer @CUSTOMER@, contact is @CUSTOMER_CONTACT@</p><hr>","entity","TEMPLATE_HTML_REPORT","Sample HTML Report (edit this in the template section)");

	$fp=@fopen("docs_examples/sample_entity_report_template.rtf","r");
	$filecontent=@fread($fp,@filesize("docs_examples/sample_entity_report_template.rtf"));
	@fclose($fp);
	AttachFile("0","sample_entity_report_template.rtf",$filecontent,"entity","TEMPLATE_REPORT");

//	AttachFileS("0","Joes helpdesk - edit entity form template (example)","<fieldset><legend align=left>Joe's Own Helpdesk - ticket&nbsp;@EID@: @CATEGORY@ #LOCKICON#&nbsp;</legend><table width=\"90%\"><tbody><tr><td><table><tbody><tr><td vAlign=top><fieldset><legend align=left>User/customer</legend>#CUSTOMER#</fieldset></td><td vAlign=top><fieldset><legend align=left>Status&nbsp;</legend>#STATUS#</fieldset> </td><td vAlign=top><fieldset><legend align=left>Priority</legend>#PRIORITY#</fieldset> </td><td vAlign=top><fieldset><legend align=left>Short problem description&nbsp;</legend>#CATEGORY# </fieldset> </td><td></td></tr></tbody></table><table cellspacing='1' cellpadding='2' width=\"100%\" border='0'><tbody><tr><td>#CONTENTS# </td><td><fieldset><legend align=left>Owner</legend>#OWNER# </fieldset> <br><fieldset><legend align=left>Assignee&nbsp;</legend>#ASSIGNEE# </fieldset> <fieldset><legend align=left>Due date&nbsp;</legend>#DUEDATE# </fieldset> <fieldset><legend align=left>Due time&nbsp;</legend>#DUETIME# </fieldset><br>#JOURNALICON# #REPORTICON# #PDFICON# #ACTICON# #LOCKICON# #ARROWS#</td></tr></tbody></table><br><table width=\"30%\"><tbody><tr><td>Read-only to other users</td><td>#READONLYBOX#&nbsp;</td></tr><tr><td>Private</td><td>#PRIVATEBOX#</td></tr><tr><td>Deleted</td><td>#DELETEBOX#</td></tr></tbody></table><table><tbody><tr><td colSpan=6><fieldset><legend align=left>Attach file&nbsp;</legend>#FILEBOX# &nbsp;&nbsp;&nbsp;&nbsp; </fieldset> <fieldset><legend align=left>Current files&nbsp;</legend>#FILELIST# &nbsp;&nbsp;&nbsp;&nbsp; </fieldset></td></td></tr>#SAVEBUTTON# </td></tr></tbody></table></td></tr></tbody></table></fieldset>","entity","TEMPLATE_HTML_FORM","Joes helpdesk");

	$dashboard = AttachFileS("0", "Default Dashboard Template", "<h1>@TITLE@ #CLOCK#</h1><h2>Click &quot;edit&quot; on the top-right corner of this page to add items to your dashboard</h2><div>#FIRSTBOOT#<div class=\"draggable hideable\" id=\"TodaysEntities\">#TODAY#</div><div class=\"draggable hideable\" id=\"calendar\">#CALENDAR#</div><div class=\"draggable hideable\" id=\"RecentlyAccessEntities\">#RECENT#</div><div class=\"draggable hideable\" id=\"PersonalStatistics\">#PERSSTATS#</div><div class=\"draggable hideable\" id=\"InternalMessages\">#MESSAGES#</div><div class=\"draggable hideable\" id=\"EntityAndCustomerSearch\">#ENTITYSEARCH#<br>#CUSTOMERSEARCH#</div><div class=\"draggable hideable\" id=\"RepositorySwitcherAndNavigation\">#REPOS#<br><br>#NAV#</div><div class=\"draggable hideable\" id=\"DashboardOptions\">#DASHBOARDOPTIONS#</div><div><a href=\"admin.php?templates=1&amp;nav=dashtemplates&amp;t1=dash\">Edit this dashboard template</a></div></div>", "entity", "TEMPLATE_DASHBOARD", "Default Dashboard Template");

	mcqinstall("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . $dashboard . "' WHERE setting='DASHBOARDTEMPLATE'", $db);

	$defaultcustomerform = AttachFileS("0", "Default customer template", "<h1>@NEWONLY@New customer@ENDNEWONLY@@XONLY@Customer @CID@: @CUSTOMER@@ENDXONLY@</h1><table class=\"crm3\"><tbody><tr><td>Active</td><td>#ACTIVE#</td></tr><tr><td>[[customer]]</td><td>#CUSTOMER#</td></tr><tr><td>[[contact]]</td><td>#CUSTOMER_CONTACT#</td></tr><tr><td>[[contacttitle]]</td><td>#CONTACT_TITLE#</td></tr><tr><td>[[contactphone]]</td><td>#CONTACT_PHONE#</td></tr><tr><td>[[contactemail]]</td><td>#CONTACT_EMAIL#</td></tr><tr><td>[[customeraddress]]</td><td>#CUSTOMER_ADDRESS#</td></tr><tr><td>[[custremarks]]</td><td>#CUST_REMARKS#</td></tr><tr><td>[[custhomepage]]</td><td>#CUST_HOMEPAGE#</td></tr><tr><td>[[customer]] [[owner]]:</td><td>#CUST_OWNER#</td></tr><tr><td>[[readonly]]:</td><td>#READONLY#</td></tr><tr><td>E-mail Owner:</td><td>#EMAILOWNERCHECKBOX#</td></tr><tr><td colspan=\"2\">[[attachfile]]</td></tr><tr><td colspan=\"2\">#FILEBOX#</td></tr></tbody></table><br /><div>#SAVEBUTTON# #DELETEBUTTON#</div>", "entity", "TEMPLATE_HTML_CFORM", "Default customer template");

	mcqinstall("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . $defaultcustomerform . "' WHERE setting='CUSTOMCUSTOMERFORM' AND value=''", $db);


	BringInTheForms($GLOBALS['TBL_PREFIX']);
}

function get_perms($file) {
   $p_bin = substr(decbin(@fileperms($file)), -9) ;
   $p_arr = explode(".", substr(chunk_explode($p_bin, 1,"."), 0, 17)) ;
   $perms = ""; $i = 0;
   foreach ($p_arr as $that) {
      $p_char = ($i%3==0 ? "r" : ($i%3==1 ? "w" :"x"));
      $perms .= ($that=="1" ? $p_char : "-") . ($i%3==2 ? " " : "");
      $i++;
   }
   return $perms;
}
function printbox($msg)
{
	print $msg;
} // end func
function mcqinstall($sql,$db) {
	global $mysql_query_counter, $logqueries;
	$mysql_query_counter++;
	if ($logqueries) {
		$fp = fopen("qlist.txt","a");
		fputs($fp,"$mysql_query_counter: $sql\n");
		fclose($fp);
	}
	$a = mysql_query($sql) or die (handle_error_install(mysql_error(),$sql));
	$GLOBALS['Last_Insert'] = mysql_insert_id();
	return($a);
}
function handle_error_install($mysqlerror,$sql) {
	$mysqlerror = str_replace("You have an error in your SQL syntax near","SQL Syntax error near",$mysqlerror);
	print "<table><tr><td>&nbsp;<strong>An internal error occured.</strong><br>&nbsp;&nbsp;&nbsp;This error is fatal.";
	print "<br>";
	print "This procedure cannot tell you exactly what went wrong. Your database action is cancelled, but previous database actions in the Interleave page you're running are executed.<br><br>";
	print "<table width='90%' border='0'><tr><td>The error message from the database is:</td></tr>";
	print "<tr><td><span class='mnspc'>$mysqlerror</span></td></tr>";
	print "<tr><td>&nbsp;$sql</td></tr>";
	print "<tr><td>The concerning query is:</td></tr>";
	print "<tr><td><span class='mnspc'>" . $sql . "</span></td></tr>";
	$deb .= "Host: " . getenv("SERVER_NAME") . "<br>";
	$deb .= "Client: " . $SERVER_['REMOTE_ADDR'] . "<br>";
	$deb .= "Location: " . $_SERVER['PHP_SELF'] . "<br>";
}
function printheaderinst($msg) {
		print "<table border='0'><tr><td colspan='2'><fieldset>";
		if ($legend) {
			print "<legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;$legend</legend>";
		}
		print $msg . "</fieldset></td></tr></table><br>";
}
// Journalling function (Entity ID, Message)
function journal2($eid,$msg,$JournalType="entity") {
	global $EnableEntityJournaling;
	if (strtoupper($EnableEntityJournaling)=="YES" || (stristr($msg,"[admin]"))) {

		$msg = mres($msg);
		// $msg = base64_encode($msg);
		$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "journal (eid,user,message,type) VALUES('" . $eid . "','" . $GLOBALS[USERID] . "','" . $msg . "','" . $JournalType ."')";
		mcq($sql,$db);
	}
}
function BringInTheForms($prefix) {
			// THIS FUNCTION BRINGS IN THE FORMFINITY FORMS (ESSENTIAL DURING INSTALL) - 3.4.3 detail.
			// Create master template, update entities

			$form = "<h1>@XONLY@Entity @EID@@ENDXONLY@@NEWONLY@New entity@ENDNEWONLY@</h1><div>#PDFICON# #ACTICON# #REPORTICON# #JOURNALICON#<div><table class=\"crm3\"><tbody><tr><td class=\"nwrp\">Customer</td><td>#CUSTOMER##CSBOX#</td></tr><tr><td>Priority</td><td>#PRIORITY#</td></tr><tr><td>Status</td><td>#STATUS#</td></tr><tr><td class=\"nwrp\">Owner</td><td>#OWNER#</td></tr><tr><td>Assignee</td><td>#ASSIGNEE#</td></tr><tr><td>Category</td><td>#CATEGORY#</td></tr><tr><td>Due date/time</td><td>#DUEDATE# #DUETIME#</td></tr><tr><td colspan=\"2\">#CONTENTS#<br />#COMMENTBOX#</td></tr><tr><td colspan=\"1\">Add your extra field description here</td><td>Add the extra field tag here</td></tr><tr><td>Deleted entity</td><td>#DELETEBOX#</td></tr><tr><td>Read-only to other users</td><td>#READONLYBOX#</td></tr><tr><td>Private</td><td>#PRIVATEBOX#</td></tr><tr><td colspan=\"2\">Attach file</td></tr><tr><td colspan=\"2\">#FILEBOX#<br />#FILELIST#</td></tr><tr><td>#EMAILDROPDOWN# #SAVEBUTTON#</td></tr></tbody></table></div><div>&nbsp;</div></div><div>&nbsp;</div>";


			mcqinstall("INSERT INTO " . $prefix . "templates(templatename,username,template_subject,templatetype,content) VALUES('Default form','Hidde Fennema','Default form','TEMPLATE_HTML_FORM','" . mres($form) . "')", $db);
			$t = $GLOBALS['Last_Insert'];


			mcqinstall("UPDATE " . $prefix . "entity SET formid=" . $t . " WHERE formid=0", $db);
			mcqinstall("UPDATE " . $prefix . "userprofiles SET ADDFORMS='a:1:{i:0;s:1:\"" . $t . "\";}'", $db);

}
function chunk_explode($glue=' ',$pieces='',$size=2,$final=array()) {
    if(!is_string($pieces) && !is_array($pieces)) 
        return false;

    if(is_string($pieces))
        $pieces = explode($glue,$pieces);

    $num_pieces = sizeof($pieces);
    if($num_pieces <= 0) 
       return $final;

    if($num_pieces >= $size) {
        $arr_chunk = array_chunk($pieces, $size);
        array_push($final,implode($glue,$chunk[0]));
        for($i=0;$i<=$size;$i++) { array_shift($pieces); }
        return chunk_explode($glue,$pieces,$size,$final);
    }
    array_push($final,implode($glue,$pieces));
    return $final;
}

?>