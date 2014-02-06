<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file handles modules.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */


if ($GLOBALS['CONFIGFILE'] == "") {
	$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
}


if (substr($argv[0], strlen($argv[0])-11, 11) == "modules.php") {

	foreach ($argv AS $cmdlineargument) {
		if (substr($cmdlineargument,0,4) == "cfg=") {
				$cmdlineargument = str_replace("cfg=", "" , $cmdlineargument);
				if (is_file($cmdlineargument)) {
					$GLOBALS['CONFIGFILE'] = $cmdlineargument;
					$printlater = "Using config file " . $GLOBALS['CONFIGFILE'] . "\n";
					continue;
				} else {
					die("Config file declaration is not correct. Fatal.");
				}
		}
	}
	print "Command-line module run interface\n";
	$GLOBALS['PATHTOINTERLEAVE'] = str_replace("modules.php", "", $argv[0]);
	require_once($GLOBALS['CONFIGFILE']);
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");
	require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
	if (!$argv[2] || !$argv[3] || !$argv[4]) {
		$argv[1] = "-help";
	}
	if ($argv[1]=="-help" || $argv[1]=="--help" || $argv[1]=="help" || $argv[1]=="-h") {
		print "\nUsage:\n";
		print "\tphp modules.php REPOS USER PASS MODULENUM [cfg=/path/to/config.inc.php]\n\n";
		exit;
	}
	if (isset($argv[1])) {
		$repository = $argv[1];
		if (!is_numeric($repository) || $repository == "") {
			$repository = "0";
		}
	}
	if ($argv[2]) {
		$username = $argv[2];
	}
	if ($argv[3]) {
		$password = $argv[3];
	}
	if ($argv[4]) {
		$module = $argv[4];
	}
	if ($username != "" && $password != "") {
		$silent = true;
	} else {
		print $printlater;
	}
	if (!CommandlineLogin($username,$password,$repository)) {
		print "Exiting...";
		exit;
	} 

	if (!is_numeric($module)) {
		print "Module number is not numeric.\n\n";
		exit;
	} else {
		$GLOBALS['ModuleRunByCommandline'] = true;
		RunModule($module);
	}
	EndHTML(false);
	print "\n\nDone running module " . $module . " (end).\n\n";
	exit;
 }
 if (!$_REQUEST['ExecInsModule'] && $_REQUEST['action'] <> "run" && !$_REQUEST['dl']) {
	require_once("initiate.php");
	$_GET['SkipMainNavigation'] = true;
	ShowHeaders();
	SafeModeInterruptCheck();
	MustBeAdmin();
	$_GET['SkipMainNavigation'] = true;
 } elseif ($_REQUEST['action'] == "run") {
	
	require_once("initiate.php");
	$modcod = db_GetValue("SELECT module_code FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE mid='" . mres($_REQUEST['mid']) . "'");

	if (!$_REQUEST['noheaders'] && !strstr($modcod, "header(")) {
		ShowHeaders();
	} else {
		$noheaders = 1;
	}






 } elseif ($_REQUEST['ExecInsModule']) {
	 require_once("initiate.php");
	$_REQUEST['nonavbar'] = 1;
	RunModule($_REQUEST['ExecInsModule']);
	EndHTML();
	exit;
 } elseif ($_REQUEST['dl'] && $_REQUEST['dl']) {
	 require_once("initiate.php");
	$_REQUEST['nonavbar'] = 1;
	SafeModeInterruptCheck();
	qlog(INFO, "Download module . " . $_REQUEST['dl']);
	$module = db_GetRow("SELECT module_name, module_description, module_code FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE mid='" . mres($_REQUEST['mid']) . "'");
	$def = serialize($module);
	$filename = str_replace(" ", "_", $module['module_name']) . ".mod";
	header("Content-Type: CSV");
	header("Content-Disposition: attachment; filename=$filename" );
	header("Window-target: _top");
	print $def;
	exit;
 }



if ($_REQUEST['action'] <> "run") {
	$_GET['SkipMainNavigation'] = true;
	AdminTabs("modules");
	SafeModeInterruptCheck();
	AddBreadCrum("Modules");
	$tabbs["avail"] = array("modules.php?action=avail" => "Available modules", "comment" => "Currently installed modules");
	$tabbs["addfromfile"] = array("modules.php?action=addfromfile" => "Upload a module", "comment" => "Upload a module definition file");
	$tabbs["addfromprojectwebsite"] = array("modules.php?action=addfromprojectwebsite" => "Install a module from the Interleave web site", "comment" => "Download and install modules directly from the Interleave project website");
	$tabbs["addonline"] = array("modules.php?action=addonline" => "Create a new module", "comment" => "Manually add a module");
	if (!$_REQUEST['action']) {
		$_REQUEST['action'] = "avail";
	}
	$navid = $_REQUEST['action'];
	$to_tabs = array("avail",  "addonline", "addfromfile", "addfromprojectwebsite");

	if ($_REQUEST['action'] == "edit") {
		$tabbs["current"] = array("" => "Editing module " . $_REQUEST['mid']);
		$to_tabs[] = "current";
		$navid = "current";
	}
	
	InterTabs($to_tabs, $tabbs, $navid);
}
if (($_REQUEST['module_upload'] == "yes" && !$_FILES['userfile']['tmp_name'] =="" && !$_FILES['userfile']['name']=="" && !$_FILES['userfile']['size']=="" && !$_FILES['userfile']['type']=="") || $_REQUEST['packurl']) {
	if ($_REQUEST['packurl']) {
		ValidateAndInsertNewModule(base64_decode($_REQUEST['packurl']));
	} else {
		ValidateAndInsertNewModule($_FILES['userfile']['tmp_name']);
	}
}
if ($_REQUEST['nm_name'] && $_REQUEST['nm_description']) {
	$code = "<?php\n// This is a default module file.\n// Edit it the way you like. The tab navigation will come free with all modules\n// and all global settings and database connections are at your disposal.\n\n// If this module is ran by a trigger, the entity ID is in \$eid.\n// If this module is ran by a trigger when an e-mail is received, the from address is\n// in \$GLOBALS['EMAIL_SENDER_ADDRESS'], the to-address is in \$GLOBALS['EMAIL_SENDER_TO']\n// and the body is in \$GLOBALS['EMAIL_BODY']. Attachements are stored in \$GLOBALS['Attachments'] (associative: filename => file contents).\n\n// If you're using the main list to link to your module (see option below) than you can find the list eid's in \$GLOBALS['eids'].\n\nprint \"This module works.\";\n\n// Don't use the EndHTML() function, it will be added while processing your module.\n?>";
	mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "modules (module_name,module_description,module_add_by,module_add_date,module_code) VALUES ('" . mres($_REQUEST['nm_name']) . "','" . mres($_REQUEST['nm_description']) . "','" . mres($GLOBALS['USERID']) . "','" . date('U') . "','" . mres($code) . "')", $db);
		print "<br>&nbsp;&nbsp;&nbsp;<img src='images/info.gif' alt=''> Module " . $_REQUEST['nm_name'] . " added.<br>";
		qlog(INFO, "Manually inserted module definition file " . $module_definition_file_location);
}
if ($_REQUEST['delmod']) {
	SafeModeInterruptCheck();
	DeleteModule($_REQUEST['delmod']);
}
//if (!$noheaders) print "<table border='0' width='100%'><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>";
switch ($_REQUEST['action']) {
		case "avail" :
			$int = "";
			print "<p><form id='SearchModuleForm' method='get' action=''>Search modules: <img src='images/searchbox.png' alt='' class='search_img'><input type='search' class='search_input' name='SearchModule' onchange='this.form.submit();'></form>";
			print "Highlighted modules in this table are modules which can be ran by anybody who has a valid account.</p>";
			print "<table class='sortable' width='100%'><tr><td>mid</td><td>Name</td><td>Description</td><td>Size</td><td>Added by</td><td>Last run date</td><td>Last run result</td><td>Run count</td><td colspan='4'></td></tr>";

			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "modules ORDER BY mid";
			$rs = mcq($sql, $db);
			while ($row = mysql_fetch_array($rs)) {
				$int = "";
				if ($_REQUEST['SearchModule']) {
					$sw = $_REQUEST['SearchModule'];
					if (stristr($row['module_code'], $sw) || stristr($row['module_name'], $sw)) {
						$int = " <img src='images/ok.gif' alt='Found!'>&nbsp;&nbsp;";
					} else {
						$int = " <img src='images/notfound.gif' alt='Not found!'>&nbsp;&nbsp;";
					}
					
				}
				$accarr = array();
				$accarr = unserialize($row['module_accessarray']);
				
				if ($accarr[0] == "") {
					$int .= " <img src='images/filtered2.jpg' alt='Everybody can run this module!' " . PrintToolTipCode("Warning: everyone who has a valid login account can run this module!") . ">&nbsp;";
				} 
				
				print "<tr><td style='cursor: pointer;' onclick=\"document.location='modules.php?action=edit&amp;mid=" . $row['mid'] . "';\">" . $int . $row['mid'] . "</td><td style='cursor: pointer;' onclick=\"document.location='modules.php?action=edit&amp;mid=" . $row['mid'] . "';\">" . $row['module_name'] . "</td><td style='cursor: pointer;' onclick=\"document.location='modules.php?action=edit&amp;mid=" . $row['mid'] . "';\">" . fillout($row['module_description'],20) . "</td><td>" . strlen($row['module_code']) . " b.</td><td style='cursor: pointer;' onclick=\"document.location='modules.php?action=edit&amp;mid=" . $row['mid'] . "';\">" . GetUserName($row['module_add_by']) . "</td><td style='cursor: pointer;' onclick=\"document.location='modules.php?action=edit&amp;mid=" . $row['mid'] . "';\">" . strftime("%a, %d %b %Y %H:%M:%S", $row['module_last_run_date']) . "</td><td style='cursor: pointer;' onclick=\"document.location='modules.php?action=edit&amp;mid=" . $row['mid'] . "';\">" . $row['module_last_run_result'] . "</td><td>" . GetAttribute("module", "ModuleRunCount", $row['mid']) . "</td><td class='nwrp'><a class='plainlink' href='modules.php?action=run&amp;mid=" . $row['mid'] . "' >Run</a></td><td class='nwrp'><a class='plainlink' href='modules.php?action=edit&amp;mid=" . $row['mid'] . "' >Edit</a></td><td class='nwrp'><a class='plainlink' href='javascript:PopRightsChooserModules(" . $row['mid'] . ");'>Access</a></td><td class='nwrp'><a href='modules.php?delmod=" . $row['mid'] . "'><img src='images/delete.gif' alt=''></a></td></tr>";
				$count++;
				//&nbsp;<a href='modules.php?action=edit&amp;mid=" . $row['mid'] . "&amp;plain=true' >(plain)</a>
				//<td class='nwrp'><a class='plainlink' href='modules.php?dl=1&amp;mid=" . $row['mid'] . "' >Download</a></td>
			}
			if (!$count) {
				print "<tr><td colspan='20'>No modules installed</td></tr>";
			}

			print "</table>";

		break;
		case "addfromfile" :
			print "<table class='nicerow'><tr><td>Upload module definition file</td><td><form id='UploadModuleDefinitionFile' method='post' enctype='multipart/form-data' action=''><div class='showinline'><input type='hidden' name='module_upload' value='yes'><input type='hidden' name='max_file_size' value='52428800'><input name='userfile' type='file'>&nbsp;&nbsp;<input type='submit' value='Go'></div></form></td></tr></table>";
		break;
		case "addfromprojectwebsite" :
				print "<table width='50%'><tr><td>";

				if (!$fd = @fopen("http://download.interleave.nl/get_modules.php", "r")) {
					$printbox_size = "100%";
					$legend = "<img src='images/error.gif' alt=''>";
					print "Connect to remote site failed. Your <span class='underln'>server</span> needs to be able to connect to the internet for this function - or maybe the service is unavailable.<br>";
					EndHTML();
					exit;
				} else {
					$listarr = array();
					$urlarr = array();
					$y = 0;
					while ($line=fgets($fd,1000))
					  {
						if (!$check) {
							//crm_language_pack_remote_install // check if response is OK
							if (trim($line)<>"crm_modules_remote_install") {
								$printbox_size = "100%";
								print "<img src='images/error.gif' alt=''>";
								print "The received response was not expected. Please try again later.<br>";
								EndHTML();
								exit;
							} else {
								$check = 1;
							}
						} else {
							$line = trim($line);
							$tmp = split(",",$line);
							//$tmp[1] = str_replace(".mod","",$tmp[1]);
							//$tmp[1] = str_replace("_"," ",$tmp[1]);
			//				$tmp[0] = str_replace(">","",$tmp[1]);
							$listarr[$y]['url'] = $tmp[0];
							$listarr[$y]['name'] = $tmp[1];
							$listarr[$y]['mname'] = $tmp[2];
							$listarr[$y]['mdesc'] = $tmp[3];
							$y++;
							unset($tmp);
			//				print_r($listarr);
						}
					  }
					  fclose ($fd);
				}

				sort($listarr);

				$t = "The following modules are available at the project page. Click on a module to install. <br>Please mind; this procedure does not check if a module is already installed.<br><br>";
				$t .= "<table class='sortable' width='100%'>";
				$t .= "<tr><td>Filename</td><td>Module name</td><td>Module description</td></tr>";
				for ($i=0;$i<sizeof($listarr);$i++) {
					$t .= "<tr><td><a href='modules.php?packurl=" . base64_encode($listarr[$i]['url']) . "' class='plainlink'>" . $listarr[$i]['name'] . "</a></td><td>" . $listarr[$i]['mname'] . "</td><td>" . $listarr[$i]['mdesc'] . "</td></tr>";
				}
				$t .= "</table>";
				print $t;
				print "<br><br>";
				print "</td></tr></table>";
		break;
		case "addonline":
				print "<form id='addform' method='post' action='modules.php'><div class='showinline'>";
				print "<table width='50%' class='nicetableclear'>";
				print "<tr class='nicerow'><td colspan='2'><strong>Add a new module</strong></td></tr>";
				print "<tr><td>Module name</td><td><input size='50' name='nm_name' type='text'></td></tr>";
				print "<tr><td>Module description</td><td><input size='50' name='nm_description' type='text'></td></tr>";
				print "<tr><td colspan='2'><br><input type='submit' value='Add module'></td></tr>";
				print "</table>";
				print "</div></form><br>";

				break;
		case "run" :
			if (!$noheaders) {
				if (isset($_REQUEST['noajax'])) {
					RunModule($_REQUEST['mid']);
				} else {
					print AjaxBox("RunModule", false, "&ExecInsModule=" . $_REQUEST['mid']);
				}
				EndHTML();
			} else {
				RunModule($_REQUEST['mid']);
				EndHTML(false);
			}
			exit;
		break;
		case "edit" :
			ShowModuleEditScreen();
		break;
}
//print "</td></tr></table>";
EndHTML();
?>