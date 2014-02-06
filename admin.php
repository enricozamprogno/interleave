<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * admin.php. This script forsees in some (most) general admin tasks
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
$_GET['SkipMainNavigation'] = true;
require_once("initiate.php");
//
// USER CONFIGURABLE SETTING:
// Wether or not to allow direct SQL queries (SEE MANUAL!)
// Uncomment (e.g. remove the "//") the underlying line to disable direct admin-user queries to the database
// $Disable_direct = "1";
// Uncomment the above line to disable direct admin-user queries to the database
// END USER CONFIGURABLE SETTING -----------------------------------------------------------------------------
if ($_REQUEST['reposman']) {
	$EnableRepositorySwitcherOverrule="n";
}
if ($_REQUEST['ShowTotalActivityGraph']) {
	
	
	if ($_REQUEST['journal']) {
		ShowTotalActivityGraph(true);
	} else {
		ShowTotalActivityGraph(false);
	}
	exit;
}
if ($_REQUEST['ExportUsers']) {
	
	
	MustBeAdmin();
	$_REQUEST['nonavbar'] = 1;
	ExportUsers();
	exit;
} elseif ($_REQUEST['ExportSettings']) {
	
	
	MustBeAdmin();
	$_REQUEST['nonavbar'] = 1;
	ExportSettings();
	exit;
} elseif ($_REQUEST['ExportExtraFields']) {
	
	
	MustBeAdmin();
	$_REQUEST['nonavbar'] = 1;
	ExportExtraFields();
	exit;
}
ShowHeaders();
if ($_REQUEST['BeThisUser'] && is_administrator()) {

	SafeModeInterruptCheck();
	if (!is_administrator($_REQUEST['BeThisUser'])) {
		log_msg("Administrative user " . GetUserName($GLOBALS['USERID']) . " switched to the profile of user " . GetUserName($_REQUEST['BeThisUser']), "");
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "sessions SET name='" . mres(GetUserLoginNameByID($_REQUEST['BeThisUser'])) . "' WHERE temp='" . mres($_COOKIE['session']) . "'", $db);
		$GLOBALS['USERID'] = $_REQUEST['BeThisUser'];
		?>
			<script type="text/javascript">
			<!--
				alert('You are now logged in as <?php echo GetUserName($_REQUEST['BeThisUser']);?>. You have to\nlog out and in again to go back to your own account.');
				document.location = 'index.php';
			//-->
			</script>
		<?php

	} else {
		PrintAD("You cannot log in as another administrator, that would be unethical. Sorry.");
		EndHTML();
		exit;
	}
}


if (!$_REQUEST['nonavbar'] && is_administrator()) {
	AdminTabs($_REQUEST['tib']);
}

if ($_REQUEST['EditVars'] || $_REQUEST['PriorityVars'] || $_REQUEST['StatusVars']) {
	if (CheckFunctionAccess("StatusAndPrioAdmin") == "ok" || is_administrator()) {
		if (is_administrator()) {
			//AdminTabs("main");
			MainAdminTabs("syscon");
		}
		AddBreadCrum("Status &amp; priority values");

		$to_tabs = array("status","priority","newstatus","newpriority");
		$tabbs["main"] = array("admin.php" => "<strong>Back</strong>");
		$tabbs["status"] = array("admin.php?EditVars=1&amp;WhatVar=stat" => "Status values");
		$tabbs["priority"] = array("admin.php?EditVars=1&amp;WhatVar=prio" => "Priority values");
		$tabbs["newstatus"] = array("admin.php?statusvar=X25ld18=&amp;EditVars=1&amp;WhatVar=newstat" => "New status value");
		$tabbs["newpriority"] = array("admin.php?priorityvar=X25ld18=&amp;priorityVars=1&amp;EditVars=1&amp;WhatVar=newprio" => "New priority value");
		if ($_REQUEST['WhatVar'] == "stat" || $_REQUEST['StatusVars']) {
			$navid = "status";
		} elseif ($_REQUEST['WhatVar'] == "newstat") {
			$navid = "newstatus";
		} elseif ($_REQUEST['WhatVar'] == "newprio") {
			$navid = "newpriority";
		} else {
			$navid = "priority";
		}
		InterTabs($to_tabs, $tabbs, $navid);

		if ($_REQUEST['WhatVar']=="stat" || $_REQUEST['WhatVar']=="newstat" || $_REQUEST['StatusVars']) {
			StatusVars();
		} elseif($_REQUEST['WhatVar']=="prio" || $_REQUEST['WhatVar']=="newprio" || $_REQUEST['PriorityVars']) {
			PriorityVars();
		}
	} else {
		PrintAD("Access to this page is not allowed");
	}
} elseif ($_REQUEST['UpdateCacheTables']) {
	UpdateCacheTables();
	print "Cache tables updated";
} elseif (isset($_REQUEST['FindAndReplace'])) {
	MustBeAdmin();
	print "<form name='farForm' id='JS_farForm' action='' method='post'>";
	if ($_POST['far_field'] != "" && $_POST['lookFor'] != "" && ($_POST['goReplace'] || $_POST['far_field'] == "%%all_config%%")) {

		if ($_POST['far_field'] != "%%all_config%%") {
			$tmp = explode(".", $_POST['far_field']);
			$table = $GLOBALS['TBL_PREFIX'] . $tmp[0];
			$sql = "UPDATE " . $table . " SET " . $GLOBALS['TBL_PREFIX'] . $_POST['far_field'] . " = REPLACE(" . $GLOBALS['TBL_PREFIX'] . $_POST['far_field'] . ",'" . mres($_POST['lookFor']) . "','" . mres($_POST['replaceWith']) . "') WHERE " . $GLOBALS['TBL_PREFIX'] . $_POST['far_field'] . " LIKE '%" . $_POST['lookFor'] . "%'";
			mcq($sql, $db);
		} else {
			
			$fields = array("extrafieldconditions.value", "extrafieldrequiredconditions.value", "extrafields.name","extrafields.remarks", "extrafields.displaylistname", "extrafields.defaultval", "flextabledefs.tablename", "flextabledefs.addlinktext", "flextabledefs.comment", "flextabledefs.headerhtml", "languages.TEXT", "modules.module_name", "modules.module_description", "modules.module_code", "statusvars.varname", "priorityvars.varname", "tabmenudefinitions.menu_name", "templates.templatename", "templates.content", "templates.template_subject", "triggerconditions.value", "triggerconditions.successmessage", "triggerconditions.failmessage", "triggers.to_value", "triggers.comment");

			foreach ($fields AS $field) {
				$tmp = explode(".", $field);
				$table = $GLOBALS['TBL_PREFIX'] . $tmp[0];
				$sql = "UPDATE " . $table . " SET " . $GLOBALS['TBL_PREFIX'] . $field . " = REPLACE(" . $GLOBALS['TBL_PREFIX'] . $field . ",'" . mres($_POST['lookFor']) . "','" . mres($_POST['replaceWith']) . "') WHERE " . $GLOBALS['TBL_PREFIX'] . $field . " LIKE '%" . $_POST['lookFor'] . "%'";
				mcq($sql, $db);
				print "Updated: " . $field . "<br>";
			}


			// options moet nog (serialized)
		
		}

		print "Replacements made.";
	} elseif ($_POST['far_field'] != "" && $_POST['lookFor'] != "") {
		$tmp = explode(".", $_POST['far_field']);
		$table = $GLOBALS['TBL_PREFIX'] . $tmp[0];
		$tmp = db_GetValue("SELECT COUNT(*) FROM " . $table . " WHERE " . $GLOBALS['TBL_PREFIX'] . $_POST['far_field'] . " LIKE '%" . $_POST['lookFor'] . "%'");
		print $tmp . " records will be changed. Continue?<br><br>";
		print "<input type=\"submit\" name=\"farSubmit\" id=\"JS_farSubmit\" value=\"Replace!\">";
		print "<input type=\"hidden\" name=\"FindAndReplace\" id=\"JS_FindAndReplace\" value=\"1\">";
		print "<input type=\"hidden\" name=\"goReplace\" id=\"JS_goReplace\" value=\"1\">";
		print "<input type=\"hidden\" name=\"far_field\" id=\"JS_far_field\" value=\"". htme($_POST['far_field']) . "\">";
		print "<input type=\"hidden\" name=\"lookFor\" id=\"JS_lookFor\" value=\"". htme($_POST['lookFor']) . "\">";
		print "<input type=\"hidden\" name=\"replaceWith\" id=\"JS_replaceWith\" value=\"" . htme($_POST['replaceWith']) . "\">";
	} else {
		
		print "In field:<br>";
		$fields = array("entity.category" => $lang['category'], "entity.status" => $lang['status'], "entity.priority" => $lang['priority'], "entity.duedate" => $lang['duedate'], "entity.startdate" => $lang['startdate'], "entity.owner" => $lang['owner'], "entity.assignee" => $lang['assignee'], "customer.custname" => $lang['customer'], "customer.contact" => $lang['contact'], "customer.contact_title" => $lang['contacttitle'], "customer.contact_phone" => $lang['contactphone'], "customer.contact_email" => $lang['contactemail'], "customer.cust_address" => $lang['customeraddress'], "customer.cust_remarks" => $lang['custremarks'], "customer.cust_homepage" => $lang['custhomepage'], "customer.active" => "active", "templates.content" => "All template contents", "templates.templatename" => "All template names", "extrafields.options" => "All extra field options", "binfiles.filename" => "All filenames (binfiles)", "%%all_config%%" => "All configuration items, no confirmation");
		

		$tmp = db_GetArray("SELECT id,tabletype, name FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE deleted='n'");
		foreach ($tmp AS $field) {
			if (is_numeric($field['tabletype'])) $field['tabletype'] = "flextable" . $field['tabletype'];
			$fields[$field['tabletype'] . ".EFID" . $field['id']] = $field['name'] . " (" . $field['tabletype'] . ")";
		}
		print "<select name=\"far_field\" id=\"JS_far_field\">";
		foreach ($fields AS $field => $desc) {
			print "<option value=\"" . $field . "\">" . $desc . "</option>";
		}
		print "</select>";
		print " " . ReturnDropDownSearchField("JS_far_field");

		print "<br>Replace all occurrences of:<br>";
		print "<input type='text' name='lookFor' id='JS_lookFor' size='50'>";
		print "<br>with:<br>";
		print "<input type=\"text\" name=\"replaceWith\" id=\"JS_replaceWith\" size=\"50\"><br><br>";
		print "<input type=\"submit\" name=\"farSubmit\" id=\"JS_farSubmit\" value=\"Next step\">";
		print "<input type=\"hidden\" name=\"FindAndReplace\" id=\"JS_FindAndReplace\" value=\"1\">";
	
	}
	print "</form>";
} elseif ($_REQUEST['UpdateTemplate']) {
	MustBeAdmin();
	$filename = GetTemplateName($_REQUEST['UpdateTemplate']);
	if ($_FILES['userfile'] <> "") {
		print "File received<br>";
		print "Received file : " . $_FILES['userfile']['name'] . "<br>";
		if ($filename <> $_FILES['userfile']['name']) {
			print "ERROR: File names must be the same!<br>";
		} else {
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "templates SET binary_content='" . mres(file_get_contents($_FILES['userfile']['tmp_name'])) . "' WHERE templateid='" . mres($_REQUEST['UpdateTemplate']) . "'", $db);
			print "File updated!<br>";
		}
	} else {

		print "<table><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>";
		print "Update RTF template " . $_REQUEST['UpdateTemplate'] . ": " . $filename . "<br><br>";
		print "<form method='post' action='' id='uploadAFile' enctype='multipart/form-data'><div class='showinline'><input type='hidden' name='max_file_size' value='52428800'><input type='hidden' name='templates' value='1'><input name='userfile' type='file'> <input type='submit' value='Update template'>";
		print "<input type='hidden' name='UpdateTemplate' value='" . htme($_REQUEST['UpdateTemplate']) . "'>";
		print "</div></form>";
		print "</td></tr></table>";
	}

} elseif (($_REQUEST['generateentities'] == "yes")) {
	MustBeAdmin();
	$a = CreateEntities();
	MainAdminTabs("datman");
	print "<span class='noway'>For every customer an entity was created. ($a entities)</span>";
	
	
} elseif ($_REQUEST['excessfields']) {
	DropUnusedFieldsFromDatabase();
} elseif ($_REQUEST['DeleteOldVersions'] == 1) {
	MustBeAdmin();
	MainAdminTabs("datman");
	SafeModeInterruptCheck();
	DeleteOldVersions();
} elseif ($_REQUEST['ViewRelTree'] == 1) {
	MainAdminTabs("datman");
	print "<h1>Root entity relationship tree</h1>";
	PrintSisters("root","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");

} elseif ($_REQUEST['generateentities'] == "almost") {
	MustBeAdmin();
	MainAdminTabs("datman");
	print "<form id='ce' method='post' action=''><div class='showinline'>";
	print "<fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;Auto-create entities</legend>This is a very dangerous function. It could fill your database with things you don't want; <strong>be careful!</strong>";
	print "<br><br>This function will create an entity for every customer (" . $lang['customer'] . ") which <span class='underln'>doesn't have an entity yet</span><br>";
	print "<br>Please select the default values:<br><br>";
	print "<table>";
	
	print "<tr><td>Owner:</td><td><select name='ac_owner'>";
	$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE LEFT(FULLNAME,3) <> '@@@' AND active<>'no' ORDER BY FULLNAME";
	$result= mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		print "<option value='" . $row['id'] . "'>" . htme($row['FULLNAME']) . "</option>";
	}
	print "</select></td></tr>";
	
	print "<tr><td>Assignee:</td><td><select name='ac_assignee'>";
	$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE LEFT(FULLNAME,3) <> '@@@' AND active<>'no' ORDER BY FULLNAME";
	$result= mcq($sql,$db);
	while ($row = mysql_fetch_array($result)) {
		print "<option value='" . $row['id'] . "'>" . htme($row['FULLNAME']) . "</option>";
	}
	print "</select></td></tr>";
	
	print "<tr><td>" . $lang['status'] . ":</td><td>";
	print "<select name='ac_status'>";
	$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname";
	$result= mcq($sql,$db);
	while($options = mysql_fetch_array($result)) {
		print "<option style='background-color: " . $options['color'] . ";' value='" . htme($options['varname']) . "'>" . htme($options['varname']) . "</option>";
	}
	print "</select></td></tr>";
	
	// which priority
	print "<tr><td>" . $lang['priority'] . ":</td><td>";
	print "<select name='ac_priority'>";
	$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname";
	$result= mcq($sql,$db);
	while($options = mysql_fetch_array($result)) {
		print "<option style='background-color: " . $options['color'] . ";' value='" . htme($options['varname']) . "'>" . htme($options['varname']) . "</option>";
	}
	print "</select></td></tr>";
	
	// which form
	print "<tr><td>Form to use:</td><td>";
	print "<select name='ac_ftu'>";
	$t = db_GetArray("SELECT templatename, templateid, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'");
	foreach($t AS $file) {
		print "<option value='" . $file['templateid'] . "'>" . htme($file['templatename']) . " (" . htme($file['template_subject']) . ")</option>";
	}
	print "</select></td></tr>";
	
	print "<tr><td>Category:</td><td><input type='text' size='70' name='ac_category' value='Auto-generated entity'></td></tr>";
	
	print "<tr><td><input type='hidden' name='generateentities' value='yes'><input type='submit'></td></tr>";
	
	print "</table></fieldset></div></form>";
} elseif ($_REQUEST['ExpireAllFormCache']) {
	ExpireFormCache("%", "As requested", "%");
	qlog(INFO, "All form cache dropped on request");
	log_msg("All form cache dropped on request","");
	print "All form cache dropped on request<br>";
} elseif (isset($_REQUEST['DDEFENUM'])) {
	ConvertDDEFToENUM();
} elseif ($_REQUEST['RecalculateComputedFields']) {
	CalculateAllComputedExtraFields();
	print "All computed extra fields were re-culculated.";
	qlog(INFO, "All computed extra fields were re-culculated.");
	$datman = 1;
	$_REQUEST['datman'] = 1;
} elseif ($_REQUEST['RunCompression']) {
	ProcessArchiving();
} elseif ($_REQUEST['MassMigrateForms']) {
	MustBeAdmin();
	MainAdminTabs("datman");
	?>
			<table width='50%'><tr><td>&nbsp;</td><td>
			<h1>mass-migrate entity forms</h1>
			<form id='migrateform' method='post' action=''><div class='showinline'>

	<?php
	if ($_REQUEST['migrate_from'] && $_REQUEST['migrate_to']) {

			if (is_numeric($_REQUEST['migrate_from']) && is_numeric($_REQUEST['migrate_to'])) {

				mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET formid='" . mres($_REQUEST['migrate_to']) . "' WHERE formid='" . mres($_REQUEST['migrate_from']) . "'", $db);
				$t = mysql_affected_rows();
				print $t . " entities updated!";
				qlog(INFO, "Updated " . $t . " entities from form " . $_REQUEST['migrate_from'] . " to form " . $_REQUEST['migrate_to']);
				log_msg("Updated " . $t . " entities from form " . $_REQUEST['migrate_from'] . " to form " . $_REQUEST['migrate_to'],"");
				ExpireFormCacheByForm($_REQUEST['migrate_from']);
			}
	} else {
			print "<br>With this function you can alter the form of a set of entities.<br><br><table width='100%'><tr><td>Migrate all entities with form</td><td><select name='migrate_from'>";
			$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
			$result = mcq($sql, $db);
			while ($row = mysql_fetch_array($result)) {
				$x++;
				print "<option " . $ins . " value='" . $row['templateid'] . "'>" . $row['templatename'] . " (" . $row['template_subject'] . ")</option>";
			}
			print "</select></td></tr>";
			print "<tr><td>to form</td><td><select name='migrate_to'>";
			$sql = "SELECT templateid, templatename, template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
			$result = mcq($sql, $db);
			while ($row = mysql_fetch_array($result)) {
				$x++;
				print "<option " . $ins . " value='" . $row['templateid'] . "'>" . $row['templatename'] . " (" . $row['template_subject'] . ")</option>";
			}
			print "</select></td></tr><tr><td><input type='submit' value='Go!'></td></tr></table>";
	}
	?>
			</div></form>

			</td></tr></table>
		<?php
	EndHTML();
	exit;

} elseif ($_REQUEST['SendAdHocEmail']) {
	Mainadmintabs("actions");
	MustBeAdmin();
	print "<table width='70%'><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>";
	print "Send e-mail to all Interleave users<br>";
	if ($_REQUEST['data'] && $_REQUEST['subject']) {
//		$_REQUEST['data'] .= "<br>@LIST@";
		$force = 2;
		print "<pre>";
		SendPersonificatedDailyOverviewMail($_REQUEST['data'],$_REQUEST['subject']);
		print "\nDone.";
		print "</pre>";
	} else {
		print "<form id='SendAdHoc' method='get' action=''><div class='showinline'>";
		print "<br><br><strong>Subject</strong>: <input type='text' name='subject' size='80'><input type='hidden' name='SendAdHocEmail' value='1'><br><br>";

		print "<textarea id='editor' rows='70' cols='140' name='data' class='mnspc'>" . htme("<!-- Feel free to edit the source, but don't add javascript or CSS code. -->\n<table border='0'><tr><td>Type your message here. The entity list will be placed under your text and will only contain entities assigned to the recipient.</td></tr></table>\n<br><hr>@LIST@") . "</textarea>";
		print make_html_editor("editor", true);
		print "<input type='submit' value='Send it!'></div></form>";
		print "</td></tr></table>";
	}
} elseif ($_REQUEST['docbox']) {

		print "<h1>Documentation</h1><h2>For support, go to <a href='http://www.interleave.nl/'>www.interleave.nl</a></h2><table width='100%' cellspacing='10'>";
		print "<tr><td>Short-key overview</td><td><a class='plainlink' href='index.php?shortkeys=1&amp;1111707918'>Short keys</a></td></tr>";
		if (file_exists("docs_examples/CRM-CTT_Interleave_Adminmanual.pdf")) {
			print "<tr><td>Administration manual</td><td><a class='plainlink' href='docs_examples/CRM-CTT_Interleave_Adminmanual.pdf'>Interleave Administration manual</a></td></tr>";
			$av++;
		}
		if (file_exists("docs_examples/CRM-CTT_Interleave_Non-technical_management_and_configuration_essentials.pdf")) {
			print "<tr><td>Non-technical management and configuration essentials</td><td><a class='plainlink' href='docs_examples/CRM-CTT_Interleave_Non-technical_management_and_configuration_essentials.pdf'>CRM-CTT_Interleave_Non-technical_management_and_configuration_essentials.pdf</a></td></tr>";
			$av++;
		}

		if (file_exists("README")) {
			print "<tr><td>Readme</td><td><a class='plainlink' href='README'>README</a></td></tr>";
			$av++;
		}
		if (file_exists("CHANGELOG")) {
			print "<tr><td>Changelog</td><td><a class='plainlink' href='CHANGELOG'>CHANGELOG</a></td></tr>";
			$av++;
		}
		if (file_exists("UPGRADING")) {
			print "<tr><td>Upgrade issues</td><td><a class='plainlink' href='UPGRADING'>UPGRADING</a></td></tr>";
			$av++;
		}
		if (!$av) {
			print "<tr><td colspan='2'>No documents found in your installation directory.</td></tr>";
		}
		print "<tr><td>List of valid template tags</td><td><a class='plainlink' href='admin.php?dpstags=1&amp;nonavbar=1'>List of tags</a></td></tr>";
		print "</table>";



} elseif ($_REQUEST['ImportUsers']) {
	//MainAdminTabs("ieb");
	SafeModeInterruptCheck();
	ImportUsers();
} elseif ($_REQUEST['ImportSettings']) {
	MainAdminTabs("ieb");
	SafeModeInterruptCheck();
	ImportSettings();
} elseif (isset($_REQUEST['ImportExtraFields'])) {
	MainAdminTabs("ieb");
	ImportExtraFields();
} elseif ($_REQUEST['ImportCSVUsers']) {
	SafeModeInterruptCheck();
	ImportCSVUsers();
} elseif (!$_REQUEST['nonavbar'] && $_REQUEST['WhatVar']) {
	print "</table><table border='0' width='80%'><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<table border='0' width='100%'>";
} elseif ($_REQUEST['log']) {
	MustBeAdmin();
	MainAdminTabs("a");
	LogFunction($_REQUEST['logquery'], $_REQUEST['anyway'], $_REQUEST['password'],$_REQUEST['warnings'],$_REQUEST['today']);
} elseif (isset($_REQUEST['GeneralFiles'])) {
	MustBeAdmin();
	MainAdminTabs("datman");
	print "<h1>Files for general use</h1>";
	print "<h2>These files are accessible for every user with a valid session.</h2>";
	print "<p>To refer to these files, create a hyperlink in your template to csv.php?fileid={file-id}.</p>";
	print AjaxBox("DisplayFileList", false, "&eid=0");

} elseif ($_REQUEST['deleteclosed'] && !$_REQUEST['deleteclosed1']) {
		MainAdminTabs("datman");
		print "<form id='ReallyDelete' method='post' action=''><div class='showinline'>";
		print "Delete all entities with status: <select name='DeleteAllWithStatus' size='1'>";
		$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname";
		$result= mcq($sql,$db);
		while($options = mysql_fetch_array($result)) {
			if (strtoupper(($ea[status]))==strtoupper($options[varname])) { $a="selected='selected'"; } else { $a=""; }
			print "<option value='" . htme($options['varname']) . "'>" . htme($options['varname']) . "</option>";
		}
		print "</select> <input type='hidden' name='deleteclosed1' value='1'><input type='hidden' name='deleteclosed' value='1'><input type='submit' value='Go!'>";
		print "</div></form>";

} elseif ($_REQUEST['deleteclosed1'] && $_REQUEST['DeleteAllWithStatus']) {
		MainAdminTabs("datman");
		print "<table>";
		$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE status='" . mres($_REQUEST['DeleteAllWithStatus']) . "' AND deleted<>'y'";
		$result= mcq($sql,$db);
		$maxU1 = mysql_fetch_array($result);
		$maxo = $maxU1[0];
		print "<tr><td colspan='12'>Setting " . $maxo . " entities with status <strong>" . $_REQUEST['DeleteAllWithStatus'] . "</strong> to (logically) <strong>deleted</strong> status. Please confirm by clicking the button below.<br>";
		print "<form id='confirm' method='get' action=''><div class='showinline'>";
		print "<input type='hidden' name='DeleteAllWithStatus' value='" . $_REQUEST['DeleteAllWithStatus'] . "'><input type='hidden' name='fdconfirmed' value='1'><input type='hidden' name='password' value='" . htme($_REQUEST['password']) . "'>";
		if ($maxo>0) {
			print "</td></tr><tr><td><br><input type='submit' name='knopje' value='Confirm deletion'></td></tr></table>";
		} else {
			print "</td></tr><tr><td><br><strong>Nothing to do!</strong></td></tr></table>";
		}
		print "</div></form>";
} elseif ($_REQUEST['fdconfirmed']) {
		$epoch = date('U');
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET deleted='y',closeepoch='" . $epoch . "' WHERE status='" . mres($_REQUEST['DeleteAllWithStatus']) . "' AND deleted<>'y'";
		mcq($sql,$db);
		//print $sql;
		print "<tr><td>All entities with status '" . $_REQUEST['DeleteAllWithStatus'] . "' were moved to deleted-list</td></tr>";
		log_msg("All entities with status " . $_REQUEST['DeleteAllWithStatus'] . " were deleted","");
} elseif ($_REQUEST['SendEntityList']) {
	MustBeAdmin();
	if (!$_REQUEST['template']) {
		print "<table><tr><td><form id='SingleReport' method='post' action=''><div class='showinline'>";
		print "<table>";
		print "<tr><td><strong>E-mail to users</strong><br><br></td></tr>";
		print "<tr><td>HTML Template:</td><td><select style='width: 250px;' name='template'>";
		$sql = "SELECT templateid,templatename,timestamp_last_change,username FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML'";
		$result = mcq($sql,$db);
		while ($row = mysql_fetch_array($result)) {
			if ($_REQUEST['template']==$row['templateid']) {
				$ins = "selected='selected'";
			} else {
				unset($ins);
			}
			print "<option $ins value = '" . $row['templateid'] ."'>" . $row['templatename'] . "</option>";
		}
		print "</select><input type='hidden' name='SendEntityList' value='1'><input type='submit' name='Go'></div></form></td></tr></table></td></tr></table>";
		EndHTML();
		exit;
	} elseif (!$_REQUEST['data']) {
		$sql = "SELECT content,template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templateid='" . mres($_REQUEST['template']) . "' AND LEFT(templatetype,8)='TEMPLATE'";

		$result = mcq($sql,$db);
		$row = mysql_fetch_array($result);
		print "<form id='editHTMLtemplateform' method='post' action=''><div class='showinline'>";
		print "<input type='hidden' name='SendEntityList' value='1'>";
		print "<input type='hidden' name='template' value='unimportant'>";
		print "<table><tr><td colspan='3'>";
		print "<strong> WARNING - only generic template tags work when e-mailing users. Use the @LIST@ tag for the list of entities assigned to the user.</strong>";
		print "</td></tr><tr><td>";
		print "<strong>To: all Interleave users</strong><br> ";
		print "<strong> Subject: </strong><input type='text' size='70' name='subject' value='" . $row['template_subject'] . "'><br><br>";
		?>
				<script type="text/javascript"><!-- // load htmlarea
				_editor_url = "";                     // URL to htmlarea files
				var win_ie_ver = parseFloat(navigator.appVersion.split("MSIE")[1]);
				if (navigator.userAgent.indexOf('Mac')        >= 0) { win_ie_ver = 0; }
				if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
				if (navigator.userAgent.indexOf('Opera')      >= 0) { win_ie_ver = 0; }
				if (win_ie_ver >= 5.5) {
				  document.write('<scr' + 'ipt src="' +_editor_url+ 'js/editor.js"');
				  document.write(' type="text/javascript"></scr' + 'ipt>');
				} else { document.write('<scr'+'ipt>function() { return false; }</scr'+'ipt>'); }
				// --></script>
				<?php
				print "<textarea name='data' ROWS='18' COLS='100'>" . htme($row['content']) . "</textarea>";
				?>
				<script type="text/javascript">
					editor_generate('data');
				</script>
			<?php
		print "<br><input type='submit' value='" . $lang['go'] . "'>";
		print "</table>";
		print "</div></form>";
	} else {
		$force = 1;
		SendPersonificatedDailyOverviewMail($_REQUEST['data'],$_REQUEST['subject']);
	}
} elseif ($_REQUEST['dpstags']) {
	print "<table><tr><td>";
	print "Tag overview";
	print "<table border='1' cellpadding='4' cellspacing='4'><tr><td valign='top'>";
	AvailableTags();
	print "</td><td valign='top'>";
	AvailableFormTags();
	print "</td><td valign='top'>";
	AvailableCustomerFormTags();
	print "</td></tr></table>";
	print "</td></tr></table>";
} elseif ($_REQUEST['templates']) {
	AddBreadCrum("Templates");
	if (CheckFunctionAccess("TemplateAdmin") == "nok") {
		PrintAD("Access to this page/function denied.");
		EndHTML();
		exit;
	}
	if (!$_REQUEST['t1'] && !$_REQUEST['editHTMLtemplate']) {
		$_REQUEST['t1'] = "add";
	    $_REQUEST['nav'] = "add";
	}
		if ($_REQUEST['t1']) {
		// Some filter was given
		$t = $_REQUEST['t1'];
		$type = array();
		switch ($t) {
			case "add":
				$dnd = true;
			break;
			case "html":
				$qins = " AND (templatetype ='TEMPLATE_HTML' OR templatetype='TEMPLATE_CSS' OR templatetype='PLAIN') ";
				$legend = "<h1>Plain HTML and CSS templates</h1>";
				$type["Plain HTML template"] = "TEMPLATE_HTML";
				$type["Cascading Style Sheet template"] = "TEMPLATE_CSS";
			break;
			case "htmlr":
				$qins = " AND templatetype ='TEMPLATE_HTML_REPORT'";
				$legend = "<h1>HTML Summary page reports</h1>";
				$type["TEMPLATE_HTML_REPORT"] = "HTML Summary report";
			break;
			case "htmlf":
				$qins = " AND (templatetype ='TEMPLATE_HTML_FORM' OR templatetype ='TEMPLATE_HTML_CFORM')";
				$legend = "<h1>Entity &amp; customer forms</h1>";
				$type["Entity or flextable form"] = "TEMPLATE_HTML_FORM";
				$type["Customer form"] = "TEMPLATE_HTML_CFORM";
			break;
			case "rtfr":
				$qins = " AND (templatetype ='TEMPLATE_REPORT' OR templatetype ='TEMPLATE_MAILMERGE')";
				$legend = "<h1>Report and mailmerge templates</h1>";
				$type["Mailmerge template"] = "TEMPLATE_MAILMERGE";
			break;
			case "pdfr":
				$qins = " AND templatetype ='TEMPLATE_REPORT_PDF' ";
				$legend = "<h1>PDF Report templates</h1><h2>made in HTML</h2>";
				$type["PDF template"] = "TEMPLATE_REPORT_PDF";
			break;
			case "dash":
				$qins = " AND templatetype ='TEMPLATE_DASHBOARD' ";
				$legend = "<h1>Dashboard templates</h1><h2>To enable a dashboard template, set the number in the <a href='admin.php?sysval=1&amp;SettingSearchQuery=DASHBOARD'>DASHBOARDTEMPLATE</a> variable or set it in the user's profile</h2>";

				$type["Dashboard template"]  = "TEMPLATE_DASHBOARD";
			break;
			case "all":
				$qins = "";
				$legend = "<h1>All available templates</h1>";
			break;
		}

		$legend .= "<p>Please bear in mind that templates are <em>not</em> secured. Never put confidential information in a template; all users having a valid " . $GLOBALS['PRODUCT'] . " account could potentially see the template.</p>";

		if (!$noaddform && sizeof($type) > 0) {
			$addform = "<form id='html' method='post' action=''><div class='showinline'>Name: <input type='text' name='new_HTML_template'>&nbsp;&nbsp;Type: <select name='filetype'>";
			foreach ($type AS $desc => $code) {
				$addform .= "<option value='" . $code . "'>" . $desc . "</option>";
			}
			$addform .= "</select>&nbsp;&nbsp;<input type='hidden' name='nav' value='" . htme($_REQUEST['nav']) . "'><input class='txt' type='submit' name='sb' value='Create'><input type='hidden' name='templates' value='1'></div></form><br><br>";
		}

	}


	$tabbs["html"] =        array("admin.php?templates=1&amp;nav=html&amp;t1=html","Plain HTML/CSS", "All plain HTML and CSS templates ");
	$tabbs["htmlforms"] =   array("admin.php?templates=1&amp;nav=htmlforms&amp;t1=htmlf" ,"Forms",  "Entity and customer record forms");
	$tabbs["dashtemplates"] =   array("admin.php?templates=1&amp;nav=dashtemplates&amp;t1=dash"   , "Dashboard",  "Templates used for custom made dashboards");
	$tabbs["htmlreports"] = array("admin.php?templates=1&amp;nav=htmlreports&amp;t1=htmlr" , "HTML Summary page reports",  "Summary page report templates");

	$tabbs["pdfreports"] =  array("admin.php?templates=1&amp;nav=pdfreports&amp;t1=pdfr"   , "PDF Reports",  "PDF templates (in HTML)");

	print "<table><tr><td valign='top'>";

	print "<div class='light-small'>Template name:<br><form id='fsform1' method='get' action=''><div class='showinline'><input type='hidden' name='templates' value='1'><img src='images/searchbox.png' alt='' class='search_img'><input class='search_input' type='search' onchange=\"document.forms['fsform1'].submit();\" name='fssearch' value='" . htme($_REQUEST['fssearch']) . "'>&nbsp;<input type='submit' name='Go' value='Go'></div></form></div><br>";
	print "<div class='light-small'>Template contents:<br><form id='fsform2' method='get' action=''><div class='showinline'><input type='hidden' name='templates' value='1'><img src='images/searchbox.png' alt='' class='search_img'><input class='search_input' type='search' onchange=\"document.forms['fsform2'].submit();\" name='fssearchtype' value='" . htme($_REQUEST['fssearchtype']) . "'>&nbsp;<input type='submit' name='Go' value='Go'></div></form></div>";

	if ($_REQUEST['fssearch'] != "" || $_REQUEST['fssearchtype'] != "") {
		if ($_REQUEST['fssearchtype'] != "") {
			$q = "SELECT " . $GLOBALS['TBL_PREFIX'] . "templates.templateid, templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE UCASE(" . $GLOBALS['TBL_PREFIX'] . "templates.content) LIKE ('%" . mres(strtoupper($_REQUEST['fssearchtype'])). "%')";
			$sres= db_GetArray($q);

		} else {
			$sres= db_GetArray("SELECT " . $GLOBALS['TBL_PREFIX'] . "templates.templateid, templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatename LIKE '%" . mres($_REQUEST['fssearch']) . "%'");
		}
		if (count($sres) == 1 && !$_REQUEST['editHTMLtemplate']) {
			$_REQUEST['editextrafield'] = $sres[0]['id'];
			if ($sres[0]['tablename']) {
				$_REQUEST['tabletype'] = $sres[0]['tablename'];
			} else {
				$_REQUEST['tabletype'] = $sres[0]['tabletype'];
			}

		} elseif (count($sres) == 0) {
			print "<div class='light-small'>No templates found matching criteria</div>";
		}
	}


	print "<ul class='normal'>";
	print "<li><a href='admin.php?templates=1&amp;add=1' class='plainlink'>New template</a><br></li>";
	print "<li><a href='admin.php?templates=1&amp;GF=1' class='plainlink'>General purpose files</a><br></li>";
	foreach ($tabbs AS $nav => $val) {
		$localul = "<li>";

		if ($_REQUEST['nav'] == $nav) {
			$disp1 = "none";
			$disp2 = "inline";
		} else {
			$disp1 = "inline";
			$disp2 = "none";
		}

		$link = $val[0];


		$localul = "<li><img class=\"expand\" title=\"f" . md5($nav) . "div\" style='display: inline; cursor: pointer; border: 0;' src='images/t_plus.jpg' alt=''>&nbsp;&nbsp;<a href='" . $link . "' " . PrintToolTipCode($val[2]) . ">" . str_replace(" ", "&nbsp;", $val[1]) . "</a><br>";

		switch($nav) {
					case "html":
						$q2ins = " AND (templatetype ='TEMPLATE_HTML' OR templatetype='TEMPLATE_CSS' OR templatetype='PLAIN')";
					break;
					case "htmlreports":
						$q2ins = " AND templatetype ='TEMPLATE_HTML_REPORT'";
					break;
					case "htmlforms":
						$q2ins = " AND (templatetype ='TEMPLATE_HTML_FORM' OR templatetype ='TEMPLATE_HTML_CFORM')";
					break;
					case "rtfreports":
						$q2ins = " AND (templatetype ='TEMPLATE_REPORT' OR templatetype ='TEMPLATE_MAILMERGE')";
					break;
					case "pdfreports":
						$q2ins = " AND templatetype ='TEMPLATE_REPORT_PDF' ";
					break;
					case "dashtemplates":
						$q2ins = " AND templatetype ='TEMPLATE_DASHBOARD' ";
					break;
					case "all":
						$q2ins = " AND 1=2";
					break;

					default:
						$q2ins = " AND 1=2";
					break;
		}

		if ($_REQUEST['fssearchtype'] != "") {
			$sres = db_GetArray("SELECT " . $GLOBALS['TBL_PREFIX'] . "templates.templateid, templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE UCASE(" . $GLOBALS['TBL_PREFIX'] . "templates.content) LIKE ('%" . mres(strtoupper($_REQUEST['fssearchtype'])). "%') " . $q2ins . " ORDER BY " . $GLOBALS['TBL_PREFIX'] . "templates.templatename");
		} elseif ($_REQUEST['fssearch'] != "") {
			$sres= db_GetArray("SELECT " . $GLOBALS['TBL_PREFIX'] . "templates.templateid, templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatename LIKE '%" . mres($_REQUEST['fssearch']) . "%'" . $q2ins . " ORDER BY " . $GLOBALS['TBL_PREFIX'] . "templates.templatename");
		}


		if (count($sres) > 0) {
			foreach ($sres AS $res) {
				if (!$nf) {
					$localul .= "<ul>";
				}
				$nf = true;
				if ($res['templateid'] == $_REQUEST['editHTMLtemplate']) {
						$localul .= "<li>" . $res['templatename'] . "</li>";
				} else {
					$link = "admin.php?templates=1&amp;nav=" . $nav . "&amp;editHTMLtemplate=" . $res['templateid'];
					$localul .= "<li><a " . PrintToolTipCode("Subject: " . $res['template_subject']) . " href='" . $link . "'>" . $res['templatename'] . "</a></li>";
				}
			}

			if ($nf) {
				unset($nf);
				$localul .= "</ul>";
			}
		}
		$sresdiv = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE 1=1 " . $q2ins . " ORDER BY " . $GLOBALS['TBL_PREFIX'] . "templates.templatename");
		foreach ($sresdiv AS $res) {
				if ($_REQUEST['nav'] == $nav) {
					$disp = "inline";
				} else {
					$disp = "none";
				}
				if (!$nf) {
					$localul .= "<div id='f". md5($nav) . "div' style='display: " . $disp . ";'><ul>";
				}
				$nf = true;
				if ($res['templateid'] == $_REQUEST['editHTMLtemplate']) {
						$localul .= "<li>" . $res['templatename'] . "</li>";
				} else {
					$link = "admin.php?templates=1&amp;nav=" . $nav . "&amp;editHTMLtemplate=" . $res['templateid'];
					$localul .= "<li><a " . PrintToolTipCode("Subject: " . $res['template_subject']) . " href='" . $link . "'>" . $res['templatename'] . "</a></li>";
				}
		}
		if ($nf) {
			unset($nf);
			$tp = true;
			$localul .= "</ul></div>";
		}

		$localul .= "</li>";



		if ($tp) {
			print $localul;

			unset($tp);
		}
	}
	print "</ul>";
	print "</td><td valign='top'>";
	if ($_REQUEST['nav'] == "taglist") {
			print "Tag overview";
			print "<table class='crm'><tr><td valign='top'>";
			AvailableTags();
			print "</td><td valign='top'>";
			AvailableFormTags();
			print "</td><td valign='top'>";
			AvailableCustomerFormTags();
			print "</td></tr></table>";
			EndHTML();
			exit;
	} elseif (!$_FILES['userfile']['tmp_name'] =="" && !$_FILES['userfile']['name']=="" && !$_FILES['userfile']['size']=="" && !$_FILES['userfile']['type']=="") {

			//  A file was attached

			// Read contents of uploaded file into variable

				$fp=fopen($_FILES['userfile']['tmp_name'] ,"rb");
				$filecontent=fread($fp,filesize($_FILES['userfile']['tmp_name'] ));
				fclose($fp);

	//			$attachment = AttachFile(0,$_FILES['userfile']['name'],$filecontent,"entity",$_REQUEST['filetype']);
				 mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "templates(templatename, templatetype, content, binary_content,username,timestamp_last_change) VALUES('". mres($_FILES['userfile']['name']) . "','" . mres($_REQUEST['filetype']) . "','%%BLOB%%','" . mres($filecontent) . "','" . mres($GLOBALS['USERID']) . "',NOW())", $db);

				unset($filecontent);
				unset($_FILES['userfile']['tmp_name'] );
				unset($_FILES['userfile']['name']);
				unset($_FILES['userfile']['size']);
				unset($_FILES['userfile']['type']);
				print "Template added";
	}
	if ($_REQUEST['new_HTML_template']) {
		qlog(INFO, "Empty HTML tempate created");
		//$attachment = AttachFile(0,$_REQUEST['new_HTML_template'],"[empty]","entity",$_REQUEST['filetype']);
		mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "templates(templatename, templatetype, content, username, timestamp_last_change) VALUES('". mres($_REQUEST['new_HTML_template']) . "','" . mres($_REQUEST['filetype']) . "','[empty]','" . $GLOBALS['USERID'] . "',NOW())", $db);
		$attachment = mysql_insert_id();

		print "<span class='noway'>Template created!</span>";
	}
	if ($_REQUEST['deletetemplate']) {
		if ($GLOBALS['DefaultForm'] == $_REQUEST['deletetemplate']) {
			log_msg("WARNING! Somebody (" . $GLOBALS['USERNAME'] . ") tried to delete the default template, this is dangerous!");
			qlog(WARNING, "WARNING! Somebody (" . $GLOBALS['USERNAME'] . ") tried to delete the default template, this is dangerous!");
			PrintAD("This is the default form. Interleave would be very confused if it was gone.");
			EndHTML();
			exit;
		}
		// First, check if this (possible) form isn't used by any entity (FormFinity)
		$row = db_GetRow("SELECT formid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE formid='" . mres($_REQUEST['deletetemplate']) . "'");
		if ($row['formid']) {
			print " <img src='images/error.gif' alt=''> Form " . $_REQUEST['deletetemplate'] . " is in use by one or more entities. You cannot delete it!";
		} else {
			$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templateid='" . mres($_REQUEST['deletetemplate']) . "'";
			mcq($sql,$db);
		}
	}
	if ($_REQUEST['GF']) {
		print "<table class='nicetableclear'><tr><td>";
		print "<h1>Files for general use</h1>";
		print "<h2>These files are accessible for every user with a valid session.</h2>";
		print "<p>To refer to these files, create a hyperlink in your template to csv.php?filed={file-id}.</p>";
		print AjaxBox("DisplayFileList", false, "&eid=0");
		print "</table>";
		EndHTML();
		exit;
	}
	if ($_REQUEST['editHTMLtemplate']) {
		if ($_REQUEST['saveHTMLtemplate'] && $_REQUEST['data']) {

			if ($_REQUEST['subject'] == "") {
				$_REQUEST['subject'] = "[no subject specified]";
			}

			if ($_REQUEST['stylesheet'] == "") $_REQUEST['stylesheet'] = "0";
			if ($_REQUEST['ShowOnAddList'] == "") $_REQUEST['ShowOnAddList'] = "n";


			$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "templates SET orientation='" . mres($_REQUEST['orientation']) . "', show_on_add_list='" . mres($_REQUEST['ShowOnAddList']) . "', template_subject='" . mres($_REQUEST['subject']) . "', templatename='" . mres($_REQUEST['newfilename']) . "', stylesheet=" . mres($_REQUEST['stylesheet']) . ",timestamp_last_change=timestamp_last_change WHERE templateid='" . mres($_REQUEST['saveHTMLtemplate']) . "'";

			if (GetTemplateType($_REQUEST['saveHTMLtemplate']) == "TEMPLATE_REPORT_PDF" || GetTemplateType($_REQUEST['saveHTMLtemplate']) == "TEMPLATE_PLAIN") {
				$FormatNumbers = GetAttribute("template", "FormatNumbers", $_REQUEST['saveHTMLtemplate']);
				if ($FormatNumbers == "") SetAttribute("template", "FormatNumbers", "No", $_REQUEST['saveHTMLtemplate'], array('No', 'Yes'));
			}

			if (GetTemplateType($_REQUEST['saveHTMLtemplate']) == "TEMPLATE_REPORT_PDF") {
				$AllowUserToEditParsedResultBeforeCreatingPDF = GetAttribute("template", "AllowUserToEditParsedResultBeforeCreatingPDF", $_REQUEST['saveHTMLtemplate']);
				if ($AllowUserToEditParsedResultBeforeCreatingPDF == "") SetAttribute("template", "AllowUserToEditParsedResultBeforeCreatingPDF", "No", $_REQUEST['saveHTMLtemplate'], array('No', 'Yes'));

				$EditBeforeParseHTML = GetAttribute("template", "EditBeforeParseHTML", $_REQUEST['saveHTMLtemplate']);
				if ($EditBeforeParseHTML == "") SetAttribute("template", "EditBeforeParseHTML", "Please review your document", $_REQUEST['saveHTMLtemplate']);

				$EditBeforeParseButtonText = GetAttribute("template", "EditBeforeParseButtonText", $_REQUEST['saveHTMLtemplate']);
				if ($EditBeforeParseButtonText == "") SetAttribute("template", "EditBeforeParseButtonText", "Download PDF document", $_REQUEST['saveHTMLtemplate']);

				$HideHeader = GetAttribute("template", "HideHeader", $_REQUEST['saveHTMLtemplate']);
				if ($HideHeader == "") SetAttribute("template", "HideHeader", "No", $_REQUEST['saveHTMLtemplate'], array('No', 'Yes'));

				$HideFooter = GetAttribute("template", "HideFooter", $_REQUEST['saveHTMLtemplate']);
				if ($HideFooter == "") SetAttribute("template", "HideFooter", "No", $_REQUEST['saveHTMLtemplate'], array('No', 'Yes'));


			}

			mcq($sql,$db);
			if ($_REQUEST['data'] != "unsupported") {
				
				SetAttribute("template", "SaveAction " . date('Y-m-d H:i:s') . " " . GetUserName($GLOBALS['USERID']), GetTemplate($_REQUEST['saveHTMLtemplate']), $_REQUEST['saveHTMLtemplate']);
				SetAttribute("template", "SaveComments " . date('Y-m-d H:i:s') . " " . GetUserName($GLOBALS['USERID']), $_REQUEST['saveComments'], $_REQUEST['saveHTMLtemplate']);
				$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "templates SET content='" . mres($_REQUEST['data']) . "' WHERE templateid='" . mres($_REQUEST['saveHTMLtemplate']) . "'";
				mcq($sql,$db);
				

			}
			ExpireFormCacheByForm($_REQUEST['saveHTMLtemplate']);
			ExpirePublishedPageCache("%");

			$type = GetTemplateType($_REQUEST['saveHTMLtemplate']);
			if ($type == "TEMPLATE_CSS") {
				$tmp = db_GetArray("SELECT templateid FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE stylesheet='" . mres($_REQUEST['saveHTMLtemplate']) . "'");
				foreach ($tmp AS $row) {
					ExpireFormCacheByForm($row['templateid']);
				}
			}


			//print $sql;
			if ($_REQUEST['fromlisturl']) {
				?>
					<script type="text/javascript">
					<!--
						document.location = '<?php echo base64_decode($_REQUEST['fromlisturl']); ?>';
					//-->
					</script>
				<?php
			}
		}
		$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE " . $GLOBALS['TBL_PREFIX'] . "templates.templateid='" . mres($_REQUEST['editHTMLtemplate'])  . "' AND LEFT(templatetype,8)='TEMPLATE'";

		//print $sql;
		$result = mcq($sql,$db);
		$row = mysql_fetch_array($result);


		AddBreadCrum("Edit template \"" . $row['filename'] . "\"");
		$rnd_e = db_GetRow("SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity");
		$rnd_entity = $rnd_e[0];
		$rnd_c = db_GetRow("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "customer");
		$rnd_customer = $rnd_e[0];


		if ($_COOKIE['online_development_mode'] == "y") {
			$toprint .= "<tr><td><a class='plainlink' onclick=\"setCookie('online_development_mode','n');setCookie('ef_inline_edit','');alert('Left online development mode.');\"  " . PrintToolTipCode("Leave online development mode") . ">Leave online development mode</a></td></tr>";
		} else {
			$toprint .= "<tr><td><a class='plainlink' onclick=\"setCookie('online_development_mode','y');setCookie('ef_inline_edit','yes');alert('You are now in online development mode.');\" " . PrintToolTipCode("This enables some admin links on each entity page enabling you to develop forms faster.") . ">Enable online development mode (only for you)</a></td></tr>";
		}
		print "<h1>Editing template " . htme($_REQUEST['editHTMLtemplate']) . " :: " . htme(GetTemplateName($_REQUEST['editHTMLtemplate'])) . "</h1>";

		print "<form id='editHTMLtemplateform' method='post' action=''><div class='showinline'>";
		if ($_REQUEST['fromlist']) {
				print "<input type='hidden' name='fromlisturl' value='" . htme($_REQUEST['fromlist']) . "'><input type='hidden' name='fromlistnow' value='1'>";
			}
		print "<input type='hidden' name='templates' value='1'>";
		print "<input type='hidden' name='editHTMLtemplate' value='" . $_REQUEST['editHTMLtemplate'] . "'>";
		print "<input type='hidden' name='saveHTMLtemplate' value='" . $_REQUEST['editHTMLtemplate'] . "'>";

		print "<table class='nicetableclear'>"; //class='crmneat'

		if ($row['templatetype'] == "TEMPLATE_HTML_FORM-disabled") {
			$formedit = true;
			print "<tr class='nicerow'><td>Compile test</td>";
			print "<td>" . ValidateHTMLForm($_REQUEST['editHTMLtemplate'],"entity");
			print "</td></tr>";
		} elseif ($row['templatetype'] == "TEMPLATE_HTML_CFORM-disabled") {
			$formedit = true;
			print "<tr class='nicerow'><td>Compile test</td>";
			print "<td>" . ValidateHTMLForm($_REQUEST['editHTMLtemplate'], "customer");
			print "</td></tr>";
			$formtype = "customer";
		}

		if ($row['templatename'] == "Default form") {
			
			print "<tr class='nicerow'><td>File name</td><td><input type='text' size='70' name='newfilenameblabla' disabled='disabled' value='" . htme($row['templatename']) . "'><input type='hidden' size='70' name='newfilename' value='" . htme($row['templatename']) . "'></td></tr>";
			print "<tr><td>Subject/description</td><td><input type='text' size='70' name='subject' value='" . htme($row['template_subject']) . "'></td></tr>";

		} else {
			if ($row['templatetype'] == "TEMPLATE_REPORT_PDF" || $row['templatetype'] == "TEMPLATE_PLAIN") {
				print "<tr class='nicerow'><td>Header title &amp; internal name</td><td><input type='text' size='70' name='newfilename' value='" . htme($row['templatename']) . "'></td></tr>";
				print "<tr><td>File name (tags are allowed)</td><td><input type='text' size='70' name='subject' value='" . htme($row['template_subject']) . "'></td></tr>";
			} elseif ($row['templatetype'] == "TEMPLATE_PLAIN") {
				print "<tr class='nicerow'><td>File name</td><td><input type='text' size='70' name='newfilename' value='" . htme($row['templatename']) . "'></td></tr>";
				print "<tr><td>Subject/description</td><td><input type='text' size='70' name='subject' value='" . htme($row['template_subject']) . "'></td></tr>";
			} else {
				print "<tr class='nicerow'><td>File name</td><td><input type='text' size='70' name='newfilename' value='" . htme($row['templatename']) . "'></td></tr>";
				print "<tr><td>Subject/description</td><td><input type='text' size='70' name='subject' value='" . htme($row['template_subject']) . "'></td></tr>";
			}
		}
		if ($row['templatetype'] == "TEMPLATE_HTML" || $row['templatetype'] == "TEMPLATE_DASHBOARD" || $row['templatetype'] == "TEMPLATE_HTML_FORM" || $row['templatetype'] == "TEMPLATE_HTML_CFORM" || $row['templatetype'] == "TEMPLATE_PDF" || $row['templatetype'] == "TEMPLATE_REPORT_PDF") {
			print "<tr class='nicerow'><td>Style sheet</td><td>";
			print "<select name='stylesheet'>";
			print "<option value=''>No specific CSS template</option>";
			foreach (GetStyleSheets() AS $sheet) {
				if ($row['stylesheet'] == $sheet['templateid']) {
					$ins = "selected='selected'";
				} else {
					unset($ins);
				}
				print "<option value='" . $sheet['templateid'] . "' " . $ins . ">" . $sheet['templatename'] . " (" . $sheet['template_subject'] . ")</option>";
			}
			print "</select></td></tr>";
		}

		unset($prt);
		if (($_REQUEST['nav'] == "htmlforms" || $_REQUEST['nav'] == "all") && GetTemplateType($_REQUEST['editHTMLtemplate']) == "TEMPLATE_HTML_FORM" ) {
			$prt = "<tr class='nicerow'><td>Show this form on the add-entity menu</td>";
			$extrains = "&nbsp;&nbsp;A user must have access to this form in order to be able to use it! Check the user or profile to allow access.";
		} elseif ($_REQUEST['nav'] == "pdfreports") {
			$prt = "<tr class='nicerow'><td>Use Interleave logo header</td>";
			$extrains = "";
		}

		if ($prt) {
			print $prt;
			print "<td><select name='ShowOnAddList'>";

			if ($row['show_on_add_list'] == "n") {
				$ins123 = "selected='selected'";
			} else {
				$ins1232 = "selected='selected'";
			}
			print "<option value='n' " . $ins123 . ">No</option><option value='y' " . $ins1232 . ">Yes</option>";

			print "</select>" . $extrains;
			print "</td></tr>";
		}

		if ($row['templatetype'] == "TEMPLATE_REPORT_PDF") {
			print "<tr><td>Page orientation</td><td><select name='orientation'>";
			print "<option value='P'>Portrait</option>";
			if ($row['orientation'] == "L") $sel = "selected='selected'";
			print "<option value='L' " . $sel . ">Landscape</option>";
			print "</select></td></tr>";
		}


		print "<tr><td>Comments/changes</td><td><input type=\"text\" name=\"saveComments\" id=\"JS_saveComments\" size=\"70\">&nbsp;&nbsp;" . AttributeLink("template", $_REQUEST['editHTMLtemplate']) . " &nbsp;<input type='submit' value='Save template'> </td></tr>";
		print "<tr><td colspan='2'>";

		if ($row['templatetype'] == "TEMPLATE_CSS" || $_REQUEST['code_editor']) {

			if ($row['templatetype'] == "TEMPLATE_CSS") {
				$syntax = "css";
			} else {
				$syntax = "php";
				print ValidatePHPSyntax($row['content']) . "<br>";
			}

			?>
			<script type="text/javascript" src="lib/editarea/edit_area/edit_area_full.js"></script>
			<script type="text/javascript">
			editAreaLoader.init({
			    id : "csscode"			// textarea id
			    ,start_highlight: true	// if start with highlight
			    ,allow_resize: "both"
				,syntax_selection_allow: "css"
				,word_wrap: true
			    ,allow_toggle: true
			    ,language: "en"
			    ,syntax: "<?php echo $syntax;?>"
			});
			</script>
			<?php
				print "<textarea id='csscode' rows='35' cols='140' name='data' class='mnspc'>" . htme($row['content']) . "</textarea>";
		} elseif ($row['templatetype'] == "TEMPLATE_DASHBOARD" || strstr($row['templatetype'], "TEMPLATE_HTML") || $row['templatetype'] == "TEMPLATE_REPORT_PDF") {
				$css = GetTemplateStyleSheet($_REQUEST['editHTMLtemplate']);
				if (strstr($row['content'], "<?")) {
					print "<span class='noway'>Warning: this template contains inline PHP</span> <a href='admin.php?templates=1&amp;nav=" . $_REQUEST['nav'] . "&amp;editHTMLtemplate=" . $_REQUEST['editHTMLtemplate'] . "&amp;code_editor=1'> [switch code-editor]</a>";
				}
				if (strstr($row['content'], "\$\$EXCLUDE") || strstr($row['content'], "\$\$DEFAULT")) {
					print "&nbsp;<span class='noway'>Warning: this template contains form directives!</span>";
				}
				print "<textarea id='editor' rows='70' cols='140' name='data' class='mnspc'>" . htme($row['content']) . "</textarea>";
				$extracss = "";
				if ($css > 0)
				{
					$extracss = "csv.php?GetCSS=" . $css;					
				}
				print make_html_editor("editor", true, $extracss, true);
		} else {
			print "This file cannot be edited on-line<input type='hidden' name='data' value='unsupported'>";
			$noedit = true;
		}
		print "</td></tr>";
		if (!$noedit) {
			print "<tr><td><input type='submit' value='Save template'></td><td></td></tr>";
				print "<tr><td colspan=\"2\">Change log:<pre>";
				foreach (db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "attributes WHERE identifier='template' AND entity='" . mres($_REQUEST['editHTMLtemplate']) . "' AND attribute LIKE 'SaveComments%' ORDER BY STR_TO_DATE(SUBSTRING(attribute,14,19), '%Y-%m-%d %H:%i:%s') DESC") AS $row) {
					print str_replace("SaveComments ", "", $row['attribute']) . " :: " . $row['value'] . "\n";
				}
				print "</pre></td></tr>";
		}
		print "<tr class='nicerow'><td colspan='2'>";
		if ($_REQUEST['editHTMLtemplate']) {
			AvailableTags();
			print "&nbsp;&nbsp;&nbsp;";
		}

		if ($_REQUEST['nav'] == "htmlforms") {
			AvailableCustomerFormTags();
			print "&nbsp;&nbsp;&nbsp;";
			AvailableFormTags();
		}
		print "</td></tr>";
		print "</table>";
		print "</div></form>";
	} else {

		if (!$dnd) {


			print "<table class='nicetableclear'><tr><td>". $legend . "";
			print $htmlins . "";
			print $addform;
			print "<table class='sortable' width='100%'>";
			print "<tr><td>No.</td><td>Template name</td><td>Document type</td><td>Created by</td><td>Style</td><td>Last save</td><td>Use count</td><td>Edit/Update</td><td>Delete</td></tr>";
			$sql = "SELECT templateid,templatename,timestamp_last_change,username,templatetype,template_subject FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE (LEFT(templatetype,8)='TEMPLATE' OR templatetype='PLAIN')" . $qins . " ORDER BY templatename";
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {
				$edit = false;
				$tdins = " onclick='document.location=\"admin.php?templates=1&amp;editHTMLtemplate=" . $row['templateid'] . "&amp;nav=" . $_REQUEST['nav'] . "\"' style='cursor: pointer'";
				print "<tr><td " . $tdins . ">" . $row['templateid'] . "</td><td " . $tdins . "><a href='csv.php?templateid=" . $row['templateid'] ."'>" . $row['templatename'] . "</a></td>";
				if ($row['templatetype'] == "TEMPLATE_REPORT") {
						$type = "Entity report";
						$row['template_subject'] = "n/a";
				} elseif ($row['templatetype'] == "TEMPLATE_MAILMERGE") {
						$type = "Mailmerge";
						$row['template_subject'] = "n/a";
				} elseif ($row['templatetype'] == "TEMPLATE_HTML") {
						$type = "HTML";
						$edit = true;
				} elseif ($row['templatetype'] == "TEMPLATE_CSS") {
						$type = "CSS";
						$edit = true;
				} elseif ($row['templatetype'] == "TEMPLATE_DASHBOARD") {
						$type = "HTML";
						$edit = true;
				} elseif ($row['templatetype'] == "TEMPLATE_HTML_FORM") {
						$type = "HTML Entity form";
						$edit = true;
				} elseif ($row['templatetype'] == "TEMPLATE_HTML_CFORM") {
						$type = "HTML Customer form";
						$edit = true;
				} elseif ($row['templatetype'] == "TEMPLATE_HTML_REPORT") {
						$type = "HTML Report";
						$edit = true;
				} elseif ($row['templatetype'] == "TEMPLATE_REPORT_PDF") {
						$type = "PDF Report";
						$edit = true;
				} elseif ($row['templatetype'] == "TEMPLATE_PLAIN") {
						$type = "Plain";
						//$edit = true;

				} else {
						$type = "Unknown";
						$row['template_subject'] = "n/a";
				}
				print "<td " . $tdins . " class='nwrp'>" . $type . "</td>";
				if (is_numeric($row['username'])) {
					$owner = GetUserName($row['username']);
				} else {
					$owner = $row['username'];
				}
				print "<td " . $tdins . " class='nwrp'>" . $owner . "</td>";

				print "<td " . $tdins . " class='nwrp'>" . GetTemplateName(GetTemplateStyleSheet($row['templateid'])) . "</td>";

				print "<td " . $tdins . ">" . $row['timestamp_last_change'] . "</td>";
				print "<td " . $tdins . ">" . GetAttribute("template", "TemplateUseCount", $row['templateid']) . "</td>";

				print "<td>";
				if ($edit) {
						print "<a href='admin.php?templates=1&amp;editHTMLtemplate=" . $row['templateid'] . "&amp;nav=" . $_REQUEST['nav'] . "' class='plainlink'>Edit</a>";
				} else {
						print "<a href='admin.php?UpdateTemplate=" . $row['templateid'] . "' class='plainlink'>Update</a>";
				}
				print "</td><td>";
				if ($GLOBALS['DefaultForm'] == $row['fileid']) {
					print " <span style='color: #B0B0B0;'>Delete</span>";
				} else {
					print "<a href='admin.php?templates=1&amp;deletetemplate=" . $row['templateid'] . "&amp;nav=" . htme($_REQUEST['nav']) ."&amp;t1=" . htme($_REQUEST['t1']) ."' class='noborder'><img src='images/delete.gif'></a>";
				}
				print "</td>";

				print "</tr>";
			}
			print "</table></td></tr></table>";
		}
		if ($_REQUEST['t1']=="add" || $_REQUEST['t1']=="") {

			unset($a);
			$a = "<h1>Create a new template</h1>";
			$a .= "<h2>Carefully select the document type</h2>";
			$a .= "<form id='html' method='post' action=''>";
			$a .= "<table class=\"admintable\">";
			$a .= "<tr><td>Name: <input type='text' name='new_HTML_template'>&nbsp;&nbsp;Document type: <select name='filetype'><option value='TEMPLATE_HTML'>HTML Plain template</option><option value='TEMPLATE_HTML_FORM'>HTML Entity form template</option><option value='TEMPLATE_DASHBOARD'>HTML Dashboard template</option><option value='TEMPLATE_HTML_CFORM'>HTML Customer form template</option><option value='TEMPLATE_HTML_REPORT'>HTML Summary page report template</option><option value='TEMPLATE_REPORT_PDF'>HTML/PDF Report template</option><option value='TEMPLATE_CSS'>CSS Stylesheet</option></select>&nbsp;&nbsp;<input class='txt' type='submit' name='sb' value='Create'><input type='hidden' name='templates' value='1'>";
			$a .= "</table></form>";
			$a .= "<table class=\"admintable\">";
			$a .= "<ul><li>HTML Plain template: A plain HTML template to use as email, a page (custom tab) or as comment field</li>";
			$a .= "<li>HTML Entity form template: A template containing an entity edit form</li>";
			$a .= "<li>HTML Customer form template: A template containing a customer edit form</li>";
			$a .= "<li>HTML Summary page report template: A template to use as a report on the summary page</li>";
			$a .= "<li>HTML/PDF Report template: A template to use as a PDF-report</li>";
			$a .= "<li>CSS template: Cascading stylesheet</li>";
			$a .= "</ul></td></tr></table>";
			$a .= "<form method='post' id='bla123' enctype='multipart/form-data' action=''>";
			$a .= "<h2>Upload a template</h2>";
			$a .=  "<table class='nicerow' width='100%'><tr><td colspan='6'><input type='hidden' name='max_file_size' value='52428800'><input type='hidden' name='templates' value='1'><input name='userfile' type='file'>&nbsp;&nbsp;&nbsp;&nbsp;Document type:&nbsp;<select name='filetype'><option value='TEMPLATE_PLAIN'>Other type (can be any of .docx, .xlsx, .rtf and all OO formats)</option></select>&nbsp;&nbsp;<input class='txt' type='submit' name='sb' value='Upload'>";
			$a .= "</td></tr></table></form>";

			print $a;

		}



	}


	print "</td></tr></table>";


} elseif ($_REQUEST['SaveForcedFields'] && $_REQUEST['ForcedFields'] ) {
	MustBeAdmin();

	$GLOBALS['MainForcedFields'] = array();
	$GLOBALS['MainForcedFields'][0] = array();
	$GLOBALS['MainForcedFields'][0]['fieldtype'] = "main";
	$GLOBALS['MainForcedFields'][0]['name'] = "category";
	$GLOBALS['MainForcedFields'][0]['forcing'] = $_REQUEST['catforced'];
	$GLOBALS['MainForcedFields'][1] = array();
	$GLOBALS['MainForcedFields'][1]['fieldtype'] = "date";
	$GLOBALS['MainForcedFields'][1]['name'] = "duedate";
	$GLOBALS['MainForcedFields'][1]['forcing'] = $_REQUEST['displayDateforced'];
	$GLOBALS['MainForcedFields'][2] = array();
	$GLOBALS['MainForcedFields'][2]['fieldtype'] = "main";
	$GLOBALS['MainForcedFields'][2]['name'] = "content";
	$GLOBALS['MainForcedFields'][2]['forcing'] = $_REQUEST['contentforced'];
	$GLOBALS['MainForcedFields'][3] = array();
	$GLOBALS['MainForcedFields'][3]['fieldtype'] = "date";
	$GLOBALS['MainForcedFields'][3]['name'] = "startdate";
	$GLOBALS['MainForcedFields'][3]['forcing'] = $_REQUEST['startdateforced'];
	$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres(serialize($GLOBALS['MainForcedFields'])) . "' WHERE setting='REQUIREDDEFAULTFIELDS'";
	mcq($sql,$db);
	print "Values were updated.";
} elseif ($_REQUEST['ForcedFields']) {
	MustBeAdmin();
	AddBreadCrum("Required fields");
	$AMF = unserialize($GLOBALS['REQUIREDDEFAULTFIELDS']);
	if (is_array($AMF)) {
		foreach ($AMF AS $field) {
			if ($field['name'] == "cat" && $field['forcing'] == "y") {
				$cat_ins = "checked='checked'";
			} else if ($field['name'] == "duedate" && $field['forcing'] == "y") {
				$dd_ins = "checked='checked'";
			} else if ($field['name'] == "content" && $field['forcing'] == "y") {
				$cont_ins = "checked='checked'";
			} else if ($field['name'] == "startdate" && $field['forcing'] == "y") {
				$sd_ins = "checked='checked'";
			}
		}
	}
	?>
	<form id='SFF' method='post' action=''><div class='showinline'>
	<br>
	Select which fields should be required when saving an entity:<br><br>
	<table>
		<tr>
			<td>Require category</td><td><input type='checkbox' <?php echo $cat_ins;?> name='catforced' value='y'></td>
		</tr>
		<tr>
			<td>Require due date</td><td><input type='checkbox' <?php echo $dd_ins;?> name='displayDateforced' value='y'></td>
		</tr>
		<tr>
			<td>Require start date</td><td><input type='checkbox' <?php echo $sd_ins;?> name='startdateforced' value='y'></td>
		</tr>
		<tr>
			<td>Require main text box content</td><td><input type='checkbox' <?php echo $cont_ins;?> name='contentforced' value='y'></td>
		</tr>
	</table>
	<br>
	<input type='submit' name='SaveForcedFields' value='Save'>
	</div></form>
	<?php
} elseif ($_REQUEST['RemoveAllEntityLocks']) {
	log_msg("All entity locks removed");
	$num = RemoveLocks(true);
	print "&nbsp;&nbsp;&nbsp;All entity locks removed (" . $num ." records)<br>";
} elseif ($GLOBALS['FORCED_TBL']=="1") {
		print "<tr><td><img src='images/error.gif' alt=''>&nbsp;<span class='noway'>You have no table prefix set in your config file. Please adjust this by adding a " . '$table_prefix[' . $repository_nr . "] = \"CRM\"; to your config file section for this repository ($title)</span></td></tr>";
} elseif ($admpassword=="*NONE*") {
		$password=$admpassword;
} elseif ($_REQUEST['ViewJournal']) {
	MustBeAdmin();
	ViewJournal($_REQUEST['VJ']);
} elseif ($_REQUEST['EditSysVar']) {
	MustBeAdmin();
	EditSysVar($_REQUEST['EditSysVar']);
} elseif ($_REQUEST['ImportSettings']) {
	MustBeAdmin();
	SafeModeInterruptCheck();
	ImportSettings();
} elseif ($_REQUEST['userman']) {
    PrintAD("Wrong URL/Hyperlink");
	EndHTML();
	exit;
} elseif ($_REQUEST['failoverman']) {
	MustBeAdmin();
	mainadmintabs();
	SafeModeInterruptCheck();
	AddBreadCrum("Fail-over status");
	log_msg("Fail over section accessed");
	print "<h1>Fail-over status</h1>";
	if($_REQUEST['sync_delay']) {
		if ($_REQUEST['sync_delay'] == "start") {
			$_REQUEST['sync_delay'] = -1000;
		}
		$GLOBALS['SYNC_DISABLED_UNTIL'] = (date('U') + $_REQUEST['sync_delay']);
		UpdateSetting("SYNC_DISABLED_UNTIL", $GLOBALS['SYNC_DISABLED_UNTIL']);
	}

	if ($_REQUEST['unlockstalled']) {
		if ($_REQUEST['unlockstalled'] == "slave") {
			DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], true);
			$th = $GLOBALS['DBHOST'];
		} elseif ($_REQUEST['unlockstalled'] == "master") {
			DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);
			$th = $GLOBALS['FO_DB'];
		}
		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore SET lockhash='' WHERE targethost='" . mres($th) . "'", $db);
		log_msg("Records from " . $_REQUEST['unlockstalled'] . " database unlocked on administrator request.");
		DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);
	}
	if ($_REQUEST['droplist']) {
		if ($_REQUEST['droplist'] == "slave") {
			DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], true);
			$th = $GLOBALS['DBHOST'];
		} else {
			DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);
			$th = $GLOBALS['FO_DB'];
		}
		mcq("DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore WHERE targethost='" . mres($th) . "'", $db);
	//	log_msg("Records from " . $_REQUEST['droplist'] . " database deleted on administrator request.");
		DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);
	}

	$d = date('U');
	print "<table>";
	print "<tr class='nicerow'><td>Fail-over status<br><div class='light'>";
	if ($d < $GLOBALS['SYNC_DISABLED_UNTIL'] && is_numeric($GLOBALS['SYNC_DISABLED_UNTIL'])) {
		print "Synchronisation disabled until <span class='noway'>" . date("Y-m-d H:i:s", $GLOBALS['SYNC_DISABLED_UNTIL']) . "</span></div></td><td colspan='1'><form id='delayform' method='post' action='admin.php'><div class='showinline'><input type='hidden' name='failoverman' value='1'>Further synchronisation delay:<div class='light'><select name='sync_delay'>";
		print "<option value='start'>Restart synchronisation</option>";
	} else {
		print "<strong>Synchronisation is enabled</strong></div></td><td><form id='delayform' method='post' action='admin.php'><div class='showinline'><input type='hidden' name='failoverman' value='1'>Disable synchronization<br><div class='light'>Disable for <select name='sync_delay'>";
	}
	print "<option value='60'>1 minute</option>";
	print "<option value='600'>10 minutes</option>";
	print "<option value='3600'>1 hour</option>";
	print "<option value='7200'>2 hours</option>";
	print "<option value='10800'>3 hours</option>";
	print "<option value='86400'>1 day</option>";
	print "</select>";
	print "&nbsp;<input type='submit' name='do_delay' value='Go'></div></div></form>";
	print "</td></tr>";
	print "<tr class='nicerow'><td>Synchronisation wait time between errors<br><div class='light'>" . $GLOBALS['SYNC_TIMEOUT'] . " minutes</div></td><td>Currently running on database<br><div class='light'>" . $GLOBALS['DBHOST'] . "</div></td></tr>";

	print "<tr class='nicerow'><td>Configured slave database<br><div class='light'>" . $GLOBALS['FO_DB'] . "</div></td>";
	
	require($GLOBALS['CONFIGFILE']);
	if ($slave[$repository_nr]) {
		if (DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], true)) {
			// all ok
			print "<td>Slave database status<br><div class='light'><span class='yesman'>RUNNING</span></div></td></tr>";
			DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);
		} else {
			print "<td>Slave database status<br><div class='light'><span class='noway'>DOWN</span></div></td></tr>";
		}
	}

	$num = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore WHERE targethost='" . mres($GLOBALS['FO_DB']) . "'");
	$lo_local = $num[0];
	print "<tr class='nicerow'><td colspan='2'>Local jobs waiting for replication <br><div class='light'>" . $num[0] . " &nbsp;&nbsp;&nbsp;";

	if ($_REQUEST['viewlist']) {

		if ($num[0] > 0) {
			print "<a class='plainlink' href='admin.php?failoverman=1&amp;viewlist1=" . $_REQUEST['viewlist1'] . "'>hide</a> <a class='plainlink' href='admin.php?failoverman=1&amp;droplist=master'>empty</a> <a class='plainlink' href='admin.php?failoverman=1&amp;unlockstalled=master'>unlock stalled records</a></div></td>";
		} else {
			print "</div></td>";
		}
	} else {

		if ($num[0] > 0) {
			print " <a class='plainlink' href='admin.php?failoverman=1&amp;droplist=master'>empty</a> <a class='plainlink' href='admin.php?failoverman=1&amp;unlockstalled=master'>unlock stalled records</a></div></td></tr>";
		} else {
			print "</div></td></tr>";
		}
	}
	$d = date('U');
	if ($GLOBALS['FO_DB_IS_DOWN'] || ($d < $GLOBALS['SYNC_DISABLED_UNTIL'])) {
		print "<tr class='nicerow'><td colspan='2'>Remote jobs waiting for replication<br><div class='light'>n/a</div></td></tr>";
	} else {
		if (DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], true)) {
			$GLOBALS['IN_SYNC_FUNC'] = true;
			$num = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "failoverquerystore WHERE targethost='" . mres($GLOBALS['DBHOST']) . "'");
			unset($GLOBALS['IN_SYNC_FUNC']);
		}
		@mysql_close($db);

		DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);

		print "<tr class='nicerow'><td  colspan='2'>Remote jobs waiting for replication <br><div class='light'>" . $num[0] . "&nbsp;&nbsp;&nbsp;";

	}

	print "<tr class='nicerow'><td>Replication status<br><div class='light'>";
	if ($num[0] <> 0 && $lo_local <> 0) {
		if ($GLOBALS['FO_DB_IS_DOWN'] || ($d < $GLOBALS['SYNC_DISABLED_UNTIL'])) {
			print "<span class='noway'>SYNC HALTED</span> [will continue when synchronisation restarts]";
		} else {
			print "<span class='yesman'>SYNC HALTED</span> [reason unknown]";
		}
	} else {
		print "<span class='yesman'>OK</span> ";
	}
	print "</div></td>";
	if (!$GLOBALS['FO_DB_IS_DOWN']) {
		$_REQUEST['synccheck'] = true;
	}

	if ($_REQUEST['synccheck']) {
		DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], true);
		$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid DESC LIMIT 20";
		$res = mcq($sql, $db);
		while ($row = mysql_fetch_array($res)) {
			$string1 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
		}
		DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);
		$sql = "SELECT eid, category FROM " . $GLOBALS['TBL_PREFIX'] . "entity ORDER BY eid DESC LIMIT 20";
		$res = mcq($sql, $db);
		while ($row = mysql_fetch_array($res)) {
			$string2 .= "EID: " . $row['eid'] . " CAT:" . $row['category'];
		}
		$GLOBALS['CURFUNC'] = "CheckDatabaseIntegrity::";
		if ($string1 <> $string2) {
			print "<td>Database integrity<br><div class='light'><span style='color: #6666FF;'>Compromised</span> (see trace for details)</div></td></tr>";
			qlog(INFO, "Following strings are not the same (combi of last 20 eids + category)");
			qlog(INFO, "MASTER : " . $string2);
			qlog(INFO, "SLAVE  : " . $string1);
		} else {
			print "<td>Database integrity<br><div class='light'><span class='yesman'>OK</span></div></td></tr>";
		}
	} else {
		if ($GLOBALS['FO_DB_IS_DOWN']) {
			print "<td>Database integrity<br><div class='light'>n/a</div></td></tr>";
		} else {
			print "<td>Database integrity<br><div class='light'><a class='plainlink' href='admin.php?failoverman=1&amp;synccheck=1'>Check now</a></div></td></tr>";
		}
	}
	print "</table>";

} elseif ($_REQUEST['deldb'] && $_REQUEST['daba']) {	// Delete a repository
	SafeModeInterruptCheck();
	$_REQUEST['daba']--;
	if (!$_REQUEST['Confirmed']) {
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1[password];
		DB_Connect($_REQUEST['daba'], false);

		if ($table_prefix[$_REQUEST['daba']]=="") $table_prefix[$_REQUEST['daba']] = "CRM";
		$GLOBALS['TBL_PREFIX'] = $table_prefix[$_REQUEST['daba']];

		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND administrator='Yes'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$foreignpassword = $result1[password];
		if ($curpassword<>$foreignpassword) {
				print "<tr><tr>";
				PrintAD("access to function denied");
				log_msg("Somebody (" . $GLOBALS['USERNAME'] . ") tried to delete this repository","");
				EndHTML();
				exit;
		}

		$sql = "SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='title'";
		$result= @mcq($sql,$db);
		$maxU1 = @mysql_fetch_array($result);
		$title = $maxU1[0];
		$dabb = $_REQUEST['daba'];
		$_REQUEST['daba']++;

		print "<tr><td>";
		SetTIU("");
		foreach ($GLOBALS['TABLES_IN_USE'] AS $table) {
			$deltables.= "<br>DROP TABLE " . $database[$dabb] . "." . $table;
		}

		printbox("Are you sure you want to delete repository '" . $title . "' running on database " . $database[$dabb] . "?<br><br><span class='noway'>WARNING! All your data in repository " . $title . " will be deleted!</span><br><br>SQL statements: <br><table><tr><td>" . $deltables . "</td></tr></table><br><br><a class='plainlink' href='admin.php?deldb=1&amp;daba=" . $_REQUEST['daba'] . "&amp;Confirmed=1'>yes</a>");
	} else {
		SafeModeInterruptCheck();
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1[password];
		DB_Connect($_REQUEST['daba'], false);
		if ($table_prefix[$_REQUEST['daba']]=="") $table_prefix[$_REQUEST['daba']] = "CRM";
		$GLOBALS['TBL_PREFIX'] = $table_prefix[$_REQUEST['daba']];
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND administrator='Yes'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$foreignpassword = $result1[password];
		if ($curpassword<>$foreignpassword) {
				print "<tr><tr>";
				PrintAD("access to function denied");
				log_msg("Somebody (" . $GLOBALS['USERNAME'] . ") tried to delete this repository","");
				EndHTML();
				exit;
		}

		$sql = "SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='title'";
		$result= @mcq($sql,$db);
		$maxU1 = @mysql_fetch_array($result);
		$title = $maxU1[0];
//		$sql = "DROP DATABASE $database[$_REQUEST['daba']]";
		$deltables = array();
		$p = 0;
		SetTIU("");
		foreach ($GLOBALS['TABLES_IN_USE'] AS $table) {
			$deltables[$p++] = "DROP TABLE " . $database[$_REQUEST['daba']] . "." . $table;
		}
		for ($t=0;$t<$p;$t++) {
			//print "<br>$deltables[$t]";
			mcq($deltables[$t],$db);
		}
		printbox("Repository $title was deleted.<br><br>");

	}
} elseif ($_REQUEST['sessionscheck'] && $_REQUEST['daba']) {
		
		SafeModeInterruptCheck();
		MustBeAdmin();
		$_REQUEST['daba']--;

		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1['password'];
		if (DB_Connect($_REQUEST['daba'], false)) {
			$GLOBALS['TBL_PREFIX'] = $table_prefix[$_REQUEST['daba']];
			// all ok
		} else {
			printbox("<span class='noway'>This database doesn't exist and you knew it!</span>");
			EndHTML();
			exit;
		}
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND administrator='Yes'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$foreignpassword = $result1[password];
		if ($curpassword<>$foreignpassword) {
				print "<tr><tr>";
				PrintAD("access to function denied");
				log_msg("Somebody (" . $GLOBALS['USERNAME'] . ") tried to access the sessions delete function unauthorized","");
				EndHTML();
				exit;
		}

		if ($_REQUEST['sessionscheck']=="delsessions") {
				$sql="DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "sessions";
				mcq($sql,$db);
				printbox("Sessions table " . $_REQUEST['daba'] . ":" . $database[$_REQUEST['daba']] . " was emptied.");
				
				EndHTML();
				exit;
		}
		$sql = "SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "sessions";
		$result = mcq($sql,$db);
		$list = mysql_fetch_array($result);
		$list = $list[0];

		printbox("Repository " . $_REQUEST['daba'] . ":" . $database[$_REQUEST['daba']] . " has " . $list . " registered sessions.<br><br>Sessions are kept in the database when a user did not use the logout button when done using CRM. You can safely empty this table (to save disk space) <em>but all people currently using this repository will loose their session requiring them to login again.</em> Please mind that when you are working in this repository, you will loose your session too after emptying the session list.");
		$_REQUEST['daba']++;
		if ($list>0) {
			printbox("<a class='plainlink' href='admin.php?sessionscheck=delsessions&amp;daba=" . $_REQUEST['daba'] . "'>empty session table</a>");
		}

		exit;

} elseif ($_REQUEST['checkcfg'] && $_REQUEST['daba']) {

		

		MustBeAdmin();
		SafeModeInterruptCheck();
		$_REQUEST['daba']--;
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1['password'];
		$tbl = $table_prefix[$_REQUEST['daba']];
		if ($tbl=="") $tbl="CRM";
		if (DB_Connect($_REQUEST['daba'], false)) {
			// all ok
		} else {
			printbox("<span class='noway'>This database doesn't exist and you knew it!</span>");
			EndHTML();
			exit;
		}

		$sql = "SELECT password FROM " . $tbl . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND administrator='Yes'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$foreignpassword = $result1['password'];
		if ($curpassword<>$foreignpassword) {
				print "<tr><tr>";
				PrintAD("access to function denied");
				log_msg("Somebody (" . $GLOBALS['USERNAME'] . ") tried to access the Edit Extra Fields function unauthorized","");
				EndHTML();
				exit;
		}
		check_config($tbl);

} elseif ($_REQUEST['checkdb'] && $_REQUEST['daba'] && $_REQUEST['categoryvars']) { // Check repository for consistancy (mostly the custom fields)
		

		MustBeAdmin();
		SafeModeInterruptCheck();
		$_REQUEST['daba']--;
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1['password'];
		if (DB_Connect($_REQUEST['daba'], false)) {
			// all ok
		} else {
			printbox("<span class='noway'>This database doesn't exist and you knew it!</span>");
			EndHTML();
			exit;
		}
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND administrator='Yes'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$foreignpassword = $result1['password'];
		if ($curpassword<>$foreignpassword) {
				PrintAD("access to function denied");
				log_msg("Somebody (" . $GLOBALS['USERNAME'] . ") tried to access the Edit extra " . strtolower($lang['category']) . " fields function unauthorized","");
				EndHTML();
				exit;
		}


		if ($_REQUEST['EditExtraField']) {
				$sql = "SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='Category pulldown list'";
				$result = mcq($sql,$db);
				$list = mysql_fetch_array($result);
				$list = $list['value'];

				if ($_REQUEST['DeleteExtraField']) {
							$list = @explode(",",$list);
							for ($x=0;$x<sizeof($list);$x++) {
									if ($list[$x]==$_REQUEST['DeleteExtraField']) {
										$printbox_size = "100%";
										$o .= "Extra field " . $list[$x] . " deleted";
									} else {
										if ($x<sizeof($list)-2) {
											$newlist .= $list[$x] . ",";
										} else {
											$newlist .= $list[$x];
										}
									}
							}
				$sql2 = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres($newlist) . "' WHERE setting='Category pulldown list'";
				mcq($sql2,$db);
				printbox($o);
				$_REQUEST['daba']++;
				$query ="INSERT into " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('XXXXXX', 'admin.php', 'XXXXX' , 'Extra customer field " . mres($_REQUEST['DeleteExtraField']) . " in this repository deleted','" . mres($GLOBALS['USERNAME']) . "')";
				mcq($query,$db);
				printbox("<a class='plainlink' href='admin.php?checkdb=1&amp;daba=" . $_REQUEST['daba'] . "&amp;categoryvars=1'>back</a>");
				?>
				<script type="text/javascript">
				<!--
				document.location='admin.php?checkdb=1&daba=<?php echo $_REQUEST['daba'];?>&categoryvars=1';
				//-->
				</script>
				<?php
				EndHTML();
				exit;
		}
				if ($_REQUEST['newval']) {

						$sql2 = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET category='" . mres($_REQUEST['newval']) . "' WHERE category='" . mres($_REQUEST['EditExtraField']) . "'";
						mcq($sql2,$db);


						if ($_REQUEST['EditExtraField']=="ADDNEWFIELDCRMNOW") {
							if (stristr($_REQUEST['newval'],",")) {
								printbox("<span class='noway'>Commas are not allowed in field names</span><br><br>Press 'back' on your browser and try again.");
								EndHTML();
								$query ="INSERT into " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('XXXXXX', 'admin.php', 'XXXXX' , 'New extra field denied because it contained one or more commas','" . mres($GLOBALS['USERNAME']) . "')";
								mcq($query,$db);
								exit;
							}
							$newlist = $list . "," . $_REQUEST['newval'];
							$query ="INSERT into " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('XXXXXX', 'admin.php', 'XXXXX' , 'Extra customer field " . $_REQUEST['newval'] . " added in this repository ','" . mres($GLOBALS['USERNAME']) . "')";
							mcq($query,$db);
						} else {
							if (stristr($_REQUEST['newval'],",")) {
								printbox("<span class='noway'>Commas are not allowed in field names</span><br><br>Press 'back' on your browser and try again.");
								EndHTML();
								$query ="INSERT into " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('XXXXXX', 'admin.php', 'XXXXX' , 'Field customer conversion denied because it contained one or more commas ','" . mres($GLOBALS['USERNAME']) . "')";
								mcq($query,$db);
								exit;
							}
							$list = @explode(",",$list);
							for ($x=0;$x<sizeof($list);$x++) {
									if ($list[$x] == $_REQUEST['EditExtraField']) {
										$list[$x] = $_REQUEST['newval'];
									}
									if (trim($list[$x]) <> "") {
										if (($x<sizeof($list)-1) && (sizeof($list)>0)) {
											$newlist .= $list[$x] . ",";
										} else {
											$newlist .= $list[$x];
										}
									}
							}

						}
						if (substr($newlist,0,1) == ",") {
									$newlist = substr($newlist,1,strlen($newlist)-1);
						}
						$sql2 = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='" . mres($newlist) . "' WHERE setting='Category pulldown list'";
						mcq($sql2,$db);
						$_REQUEST['daba']++;
						$printbox_size = "100%";
						print "Extra " . strtolower($lang[category]) . " field added<br><a class='plainlink' href='admin.php?checkdb=1&amp;daba=" . $_REQUEST['daba'] . "&amp;refresh=889234&amp;categoryvars=1'>ok</a>";
						$query ="INSERT into " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('XXXXXX', 'admin.php', 'XXXXX' , 'Extra category field " . mres($_REQUEST['EditExtraField']) . " converted to " . mres($_REQUEST['newval']) . " in this repository ', '" . mres($GLOBALS['USERNAME']) . "')";
						mcq($query,$db);

						EndHTML();
						exit;
				}
				$_REQUEST['daba']++;
				$o = "Convert " . strtolower($lang['category']) . " field<br><br><form id='bla124' method='post' action=''><div class='showinline'><table border='0' style='width: 90%;'><tr><td>Original name:</td><td><input type='text' name='bla' value='" . htme($_REQUEST['EditExtraField']) . "' size='60' disabled='disabled'><input type='hidden' name='EditExtraField' value='" . htme($_REQUEST['EditExtraField']) . "'><input type='hidden' name='daba' value='" . $_REQUEST['daba'] . "'><input type='hidden' name='categoryvars' value='1'><input type='hidden' name='checkdb' value='1'><input type='hidden' name='categoryvars' value='1'></td></tr><tr><td>New name:</td><td><input type='text' value='" . htme($_REQUEST['EditExtraField']) . "' name='newval' size='60'></td></tr><tr><td colspan='2'><input type='submit' value='modify'></td></tr></table></div></form>";
				print $o;
				print "<a class='plainlink' href='admin.php?checkdb=1&amp;daba=" . $_REQUEST['daba'] . "&amp;categoryvars=1'>back</a>";
				EndHTML();
				exit;
		}
			$query ="INSERT into " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('XXXXXX', 'admin.php', 'XXXXX' , 'Extra customer field section accessed ','" . mres($GLOBALS['USERNAME']) . "')";
			mcq($query,$db);

			$legend = "Edit category (" . strtolower($lang['category']) . ") fields&nbsp;";
			$o = "<br>Working with repository " . $_REQUEST['daba'] . ":" . $database[$_REQUEST['daba']] . "<br>";

			$tmp = Getsetting("Category pulldown list");

			if (!$list = unserialize($tmp)) {
				$ok_efl = $list;
				$list = @explode(",",$tmp);
				}
			if (strtoupper($GLOBALS['ForceCategoryPulldown'])<>"YES") {
				$ot .= "<br><img src='images/info.gif' alt=''>&nbsp;Warning! " . $lang['category'] . " fields only work when you set the FORCECATEGORYPULLDOWN directive to 'Yes' - it's not enabled now!<br>";
			}
			$ot .= "<br><strong>" . $lang['category'] . " fields in this repository are:</strong><br><br><table border='1' width='90%'>";
			$dabapo = 4;
			for ($x=0;$x<sizeof($list);$x++) {


				$ot .= "<tr><td>" . $x . "</td><td>" . $list[$x] . "</td><td><a class='plainlink' href='admin.php?EditExtraField=" . urlencode($list[$x]) . "&amp;daba=" . $dabapo . "&amp;checkdb=1&amp;categoryvars=1'>edit</a>&nbsp;<a class='plainlink' href='admin.php?EditExtraField=" . urlencode($list[$x]) . "&amp;daba=" . $dabapo . "&amp;checkdb=1&amp;categoryvars=1&amp;DeleteExtraField=" . $list[$x] . "'>delete</a></td></tr>";
				if ($list[$x]<>"") {
					$teller++;
				}
			}
			if ($teller>0) {
				$o .= $ot;
				unset($ot);
			}
			$o .= "</table><br>";



		$printbox_size = "100%";
		print $o;
		$_REQUEST['daba']++;
		print "<strong>Add new " . strtolower($lang['category']) . " field:</strong><br>";
		$o = "<form id='bla234' method='post' action=''><div class='showinline'><input type='hidden' name='EditExtraField' value='ADDNEWFIELDCRMNOW'><input type='hidden' name='daba' value='" . $_REQUEST['daba'] . "'><input type='hidden' name='categoryvars' value='1'><input type='hidden' name='checkdb' value='1'>";
		$o .= "Name <input type='text' value='' name='newval' size='60'> <input type='submit' value='Add'></div></form><br><br>";
		$printbox_size = "100%";
		print $o;
		$printbox_size = "100%";

} elseif ($_REQUEST['checkcfg'] && $_REQUEST['daba']) {

		

		MustBeAdmin();
		$_REQUEST['daba']--;
		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1['password'];
		if (DB_Connect($_REQUEST['daba'], false)) {
			// all ok
		} else {
			printbox("<span class='noway'>This database doesn't exist and you knew it!</span>");
			EndHTML();
			exit;
		}

		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND administrator='Yes'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$foreignpassword = $result1['password'];
		if ($curpassword<>$foreignpassword) {
				print "<tr><tr>";
				PrintAD("access to function denied");
				log_msg("Somebody (" . $GLOBALS['USERNAME'] . ") tried to access the Edit Extra Fields function unauthorized","");
				EndHTML();
				exit;
		}
		check_config();

		EndHTML();
		exit;
} elseif ($_REQUEST['ts'] && $_REQUEST['daba']) {	// Show table status in plain text
		MustBeAdmin();
		$_REQUEST['daba']--;

		$sql = "SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$curpassword = $result1['password'];
		if (DB_Connect($_REQUEST['daba'])) {
			// all ok
		} else {
			printbox("<span class='noway'>This database doesn't exist and you knew it!</span>");
			EndHTML();
			exit;
		}
		$tbl = $table_prefix[$_REQUEST['daba']];
		if ($tbl=="") $tbl="CRM";
		$sql = "SELECT password FROM " . $tbl . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "' AND administrator='Yes'";
		$result= mcq($sql,$db);
		$result1= mysql_fetch_array($result);
		$foreignpassword = $result1['password'];
		if ($curpassword<>$foreignpassword) {
				print "<tr><tr>";
				PrintAD("access to function denied");
				log_msg("Somebody (" . $GLOBALS['USERNAME'] . ") tried to access the table status overview unauthorized","");
				EndHTML();
				exit;
		}
		$sql = "SELECT value FROM " . $tbl . "settings WHERE setting='title'";
		$result= @mcq($sql,$db);
		$maxU1 = @mysql_fetch_array($result);
		$title = $maxU1[0];
		print "<table><tr><td>Table status query for database $title</td></tr></table>";
		$sql = "SHOW TABLE STATUS";
		$result= @mcq($sql,$db);
		print "<table border='1'><tr><td>Name</td><td>Type</td><td>Row_format</td><td>Rows</td><td>Avg_row_length</td><td>Data_length</td><td>Max_data_length</td><td>Index_length</td><td>Data_free</td><td>Auto_increment</td><td>Create_time</td><td>Update_time</td><td>Check_time</td><td>Create_options</td><td>Comment</td>";
		while ($stat = @mysql_fetch_array($result))
			{
			print "<tr>";
			for ($g=0;$g<15;$g++) {
				print "<td>" . $stat[$g] . "&nbsp;</td>";
			}
			print "</tr>";
		}
		exit;
} elseif ($_REQUEST['dothefinejob_entities']==1 || ($_REQUEST['importbodyentity'] && $_REQUEST['DirectLoad'])) {

		$numofrecords = 0;
		if ($_REQUEST['DirectLoad']) {
			$import = array();
			$importbody = explode("\n",$_REQUEST['importbodyentity']);
			if ($_REQUEST['DELETEFIRST'] == "1") {
				$_REQUEST['DELETEFIRST'] = "confirmed";
			}
			if (is_file($_FILES['userfile']['tmp_name'])) {
				$importbody = file($_FILES['userfile']['tmp_name']);
			}
			foreach ($importbody AS $regel) {
					$a = explode($_REQUEST['separator'],$regel);
					$numofrecords++;
					foreach ($a AS $row) {
						array_push($import, $row);
					}
			}
		}


		// Okee, import[0-(numofrecords*9)] hebben we nu.
		$list = GetExtraFields();
		if ($_REQUEST['DELETEFIRST']=="confirmed") {
				$tmp = "Current entity list emptied";
				$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "entity";
				mcq($sql,$db);
				$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "accesscache";
				mcq($sql,$db);
				//$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "cu2stomaddons WHERE type='entity'";
				//mcq($sql,$db);

				log_msg("Entity table emptied by import","");
		}
		for ($tel=0;$tel<=($numofrecords*$_REQUEST['func_max']);$tel=$tel+$_REQUEST['func_max']) {

	//		Onderstaande omslachtige routine is de enige manier waarop ik het aan de praat kreeg.
	//		De array ( $import[point] ) opnemen in de SQL-query werkte niet in eerste instantie.
	//		Ik heb nu de query 'los', dwz eerst een $sql met de query vullen en dan pas uitvoeren.
	//		Wellicht zou ik de array nu wel in de $sql= op kunnen nemen maar daar heb ik nu geen
	//		zin meer in.
			$a = str_replace("\[CRLF\]","\n",$import[$tel]);
			$b = str_replace("\[CRLF\]","\n",$import[$tel+1]);
			$c = str_replace("\[CRLF\]","\n",$import[$tel+2]);
			$d = str_replace("\[CRLF\]","\n",$import[$tel+3]);
			$e = str_replace("\[CRLF\]","\n",$import[$tel+4]);
			$f = str_replace("\[CRLF\]","\n",$import[$tel+5]);
			$g = str_replace("\[CRLF\]","\n",$import[$tel+6]);
			$h = str_replace("\[CRLF\]","\n",$import[$tel+7]);
			$i = str_replace("\[CRLF\]","\n",$import[$tel+8]);
			$opendate = $import[$tel+9];

			if (trim($opendate)<>"") {
				$day = substr($opendate,0,2);
				$mon = substr($opendate,3,2);
				$yea = substr($opendate,6,4);
				$cdate = $yea . "-" . $mon . "-" . $day;
				$openepoch = mktime (0,0,0,$mon,$day,$yea);
				$sqldate = $cdate;
			} else {
				$openepoch = date('U');
				$cdate = date('Y-m-d');
				$sqldate = $cdate;
			}
			if (strlen($a)>0) {			// When the first field was not empty

				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "entity(category,content,status,priority,owner,assignee,CRMcustomer,deleted,duedate,cdate,openepoch,sqldate,formid) VALUES ('" . mres($a) . "','" . mres($b) . "','" . mres($c) . "','" . mres($d) . "','" . mres($e) . "','" . mres($f) . "','" . mres($g) . "','" . mres($h) . "','" . mres($i) . "','" . mres($cdate) . "','" . mres($openepoch) . "','" . mres($sqldate) . "','" . mres($GLOBALS['DefaultForm']) . "')";


				mcq($sql,$db);
				$qrs++;

				$id_i = mysql_insert_id();
				if (!$min_id) {
					$min_id = $id_i;
				}
				for ($tmp=10;$tmp<$_REQUEST['func_max'];$tmp++) {
					// Import extra fields

					//if (!$import[$tmp]=="") {
						$c = $tmp + $tel;
						$field = $list[$tmp-10];

						$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET EFID" . $field['id'] . "='" . mres(str_replace("\[CRLF\]","\n",$import[$c])) . "' WHERE eid=" . $id_i;
						mcq($sql,$db);
						$qrs++;
						//print $sql . "<br>";
					//}
				}

				journal($id_i,"Entity created (automated import)","");
				$imported++;
			} else {
				$skipped++;
			}
		}

		echo"<tr><td>";
		log_msg("Entity table imported ($imported imported, $skipped skipped, min_EID $min_id, max_EID $id_i)","");
		//echo $tmp;
		echo "<br>";
		//eval($lang[imported]);
		if ($skipped>=1) {
			print $skipped . " " . $lang['ignored'];
		}
		print "<br>Imported entity $min_id to $id_i.";
		print "<br>" . $qrs . " queries executed";

		print "<tr><td>";

		//menu(justhome,1);
} elseif ($_REQUEST['dothefinejob2']==1) {

		// Okee, import[0-(numofrecords*8)] hebben we nu.
		$list = GetExtraCustomerFields();
			if ($_REQUEST['DELETEFIRST']=="confirmed") {
					$tmp = "Current customer list emtied (including extra fields)";
					$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "customer";
					mcq($sql,$db);
					//$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "csustomaddons WHERE type='cus1t'";
					//mcq($sql,$db);
					log_msg("Customer table emptied (including extra fields)","");
			}
		for ($tel=0;$tel<=($numofrecords*$_REQUEST['func_max']);$tel=$tel+$_REQUEST['func_max']) {

	//		Onderstaande omslachtige routine is de enige manier waarop ik het aan de praat kreeg.
	//		De array ( $import[point] ) opnemen in de SQL-query werkte niet in eerste instantie.
	//		Ik heb nu de query 'los', dwz eerst een $sql met de query vullen en dan pas uitvoeren.
	//		Wellicht zou ik de array nu wel in de $sql= op kunnen nemen maar daar heb ik nu geen
	//		zin meer in.
			$a = $import[$tel];
			$b = $import[$tel+1];
			$c = $import[$tel+2];
			$d = $import[$tel+3];
			$e = $import[$tel+4];
			$f = $import[$tel+5];
			$g = $import[$tel+6];
			$h = $import[$tel+7];

			if (strlen($a)>0) {			// When the first field was not empty
				$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "customer (custname,contact,contact_title,contact_phone,contact_email,cust_address,cust_remarks,cust_homepage) VALUES ('" . mres($a) . "','" . mres($b) . "','" . mres($c) . "','" . mres($d) . "','" . mres($e) . "','" . mres($f) . "','" . mres($g) . "','" . mres($h) . "')";
				mcq($sql,$db);
				$qrs++;

				$id_i = mysql_insert_id();
				for ($tmp=8;$tmp<$_REQUEST['func_max'];$tmp++) {
					// Import extra fields

					if (!$import[$tmp]=="") {
						$c = $tmp + $tel;
						$field = $list[$tmp-8];
						$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "customer SET EFID" . $field['id'] . "='" . mres(str_replace("\[CRLF\]","\n",$import[$c])) . "' WHERE id=" . $id_i;
						mcq($sql,$db);
						$qrs++;
						//print $sql . "<br>";
					}
				}

				journal($id_i,"Customer added (automated import)","customer");
				$imported++;
			} else {
				$skipped++;
			}
		}

		echo"<tr><td>";
		log_msg("Customer table imported ($imported imported, $skipped skipped","");
		//echo $tmp;
		echo "<br>";
		eval($lang['imported']);
		print "<br>" . $qrs . " queries executed";
		if ($skipped>=1) {
			print $skipped . " " . $lang['ignored'];
		}

		print "<tr><td>";

		//menu(justhome,1);
		exit;

} elseif ($_REQUEST['manageres']) {
//		print "</table></table>";
		MainAdminTabs();
		AddBreadCrum("Repository management");
		$to_tabs = array("Current repositories","New repository");
		$tabbs["main"] = array("admin.php" => "<strong>Back</strong>", "comment" => "bla");
		$tabbs["Current repositories"] = array("admin.php?reposman=1&amp;resman=1&amp;manageres=1&amp;1156969438" => "Current repositories", "comment" => "View the list of currently configured repositories");
		$tabbs["New repository"] = array("install.php?AddRepository=1&amp;step=1" => "New repository", "comment" => "Create a shiny new repository");
		$navid = "Current repositories";
		InterTabs($to_tabs, $tabbs, $navid);
		MustBeAdmin();

		print "<strong>Repository management</strong><br>";
		print "In this section you can add and remove your Interleave repositories as well as edit your extra<br>fields of all repositories where you (" . $GLOBALS['USERNAME'] . ") have an <strong>admin</strong> account with the same password as in this repository.<br><br>Please note that when you run several repositories in one database, the displayed size will be the sum of all repository sizes!<br><br>";

		print "<table class='sortable' width='100%'>";
		print "<tr><td><strong>ResId</strong></td><td><strong>Host</strong></td><td><strong>Database</strong></td><td><strong>Table prefix</strong></td><td><strong>Repository title</strong></td><td><strong>Status</strong></td><td><strong>Size</strong></td><td><strong>DB version</strong></td><td><strong>Entities</strong></td><td><strong>Sessions</strong></td><td><strong>Total records</strong></td><td><strong>Fail-over</strong></td><td><strong>Maintenance mode</strong></td><td><strong></strong></td><td><strong></strong></td><td><strong></strong></td><td><strong></strong></td></tr>";
		// Get all possible Interleave repository titles from all possible databases
		$row = DB_GetRow("SELECT password FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'");
		$curpassword = $row['password'];
		for ($r=0;$r<64;$r++) {
			if ($host[$r]) {
					if (DB_Connect($r, false)) {
							// all ok
					} else {
						if ($slave[$r]) {
							$tmp = $host[$r];
							$host[$r] = $slave[$r];
							$slave[$r] = $tmp;
						} else {
							$host[$r] = $host[$r];
						}
					}
			}
		}
		require($GLOBALS['CONFIGFILE']);
		if (sizeof($pass)>0) {
				for ($r=0;$r<64;$r++) {
					if ($user[$r]) {
						if ($db = DB_Connect($r, false)) {
								$tbl = $table_prefix[$r];
								if ($tbl=="") $tbl="CRM";
								$row = DB_GetRow("SELECT password FROM " . $tbl . "loginusers WHERE name='" . mres($GLOBALS['USERNAME']) . "' AND administrator='Yes'");
								$foreignpassword = $row['password'];
								unset($on_remote);
								unset($on_local);
								unset($size);
								unset($DBVERSION);
								if ($curpassword==$foreignpassword) {

									//unset($tot_ent);
									$sql = "SELECT value FROM " . $tbl . "settings WHERE setting='title'";
									$result= mcq($sql,$db);
									$maxU1 = @mysql_fetch_array($result);
									$title = $maxU1[0];
									$sql = "SELECT value FROM " . $tbl . "settings WHERE setting='DBVERSION'";
									$result= mcq($sql,$db);
									$maxU1 = @mysql_fetch_array($result);
									$DBVERSION = $maxU1[0];
									$sql = "SELECT value FROM " . $tbl . "settings WHERE setting='MAINTENANCE_MODE'";
									$result= mcq($sql,$db);
									$maxU1 = @mysql_fetch_array($result);
									$maintenance_mode = $maxU1[0];

									$sql = "SELECT COUNT(*) FROM " . $tbl . "entity";
									$result= mcq($sql,$db);
									$res = @mysql_fetch_array($result);
									$enum = FormatNumber($res[0], 0);

									$tot_ent += $enum;

									$sql = "SELECT COUNT(*) FROM " . $tbl . "sessions";
									$result= mcq($sql,$db);
									$res = mysql_fetch_array($result);
									$sess = $res[0];
									$sql = "SHOW TABLE STATUS";
									$result= mcq($sql,$db);
									while ($stat = @mysql_fetch_array($result))
									{
										$size += $stat["Data_length"];
										$size += $stat["Index_lenght"];
									}

									$tot_size += (($size/1024)/1024);
									$size = FormatNumber((($size/1024)/1024),2) . " MB";
									if ($DBVERSION=="") {
										$DBVERSION="Prior to 1.9.0";
									}
									if ($DBVERSION == $GLOBALS['VERSION']) {
										$DBVERSION = "<span style='background-color: #33FF00'>" . $DBVERSION . "</span>";
									} elseif ($DBVERSION > $GLOBALS['VERSION']) {
									} elseif ($DBVERSION < $GLOBALS['VERSION']) {
										$DBVERSION = "<span style='background-color: #FF9966'>" . $DBVERSION . "</span>";
									}
									$bla = CountTotalNumOfRecords($tbl);
									if ($GLOBALS['FAULT']) {
										$ins = "<span class='noway'>ERROR</span><br>" . $GLOBALS['FAULT'];
										unset($GLOBALS['FAULT']);
									} else {
										$ins = "<span style='color: #33FF66;'>OK</span>";
									}
									print "<tr><td>$r</td><td>" . $host[$r] . "</td><td>$database[$r]</td><td>$table_prefix[$r]</td><td>$title</td><td>" . $ins . "</td><td>" . $size . "</td><td>$DBVERSION</td><td>$enum</td><td>$sess</td>";
									unset($size);

									if ($bla) {
										print "<td>" . FormatNumber($bla,0) . "</td>";
										$tot_rec = $tot_rec + $bla;
									} else {
										print "<td>n/a</td>";
									}
									print "<td>";
									if ($slave[$r]) {
										if (DB_Connect($r, true)) {
											$sql = "SELECT eid, category FROM " . $table_prefix[$r] . "entity ORDER BY eid DESC LIMIT 20";
											$res = mcq($sql, $db);
											unset($string1);
											unset($string2);
											while ($row = @mysql_fetch_array($res)) {
												$string1 .= $row['eid'] . $row['category'];
											}
											$num = db_GetRow("SELECT COUNT(*) FROM " . $table_prefix[$r] . "failoverquerystore WHERE targethost='" . mres($host[$r]) . "'");
											$on_remote = $num[0];

											$db = DB_Connect($r, false);
											$sql = "SELECT eid, category FROM " . $table_prefix[$r] . "entity ORDER BY eid DESC LIMIT 20";
											$res = mcq($sql, $db);
											while ($row = @mysql_fetch_array($res)) {
												$string2 .= $row['eid'] . $row['category'];
											}
											$num = db_GetRow("SELECT COUNT(*) FROM " . $table_prefix[$r] . "failoverquerystore WHERE targethost='" . mres($slave[$r]) . "'");
											$on_local = $num[0];


											if ($string1 <> $string2) {
												print "<span style='color: #6666FF;'>Compromised</span> ";
											} else {
												if ($on_local <> 0 && $on_remote<>0) {
													print "<span class='noway'>SYNC HALTED</span> ";
												} else {
													print "<span class='yesman'>OK</span> ";
												}
											}
											print "Jobs local: " . $on_local . " rem. " . $on_remote;
										} else {
											$db = DB_Connect($r, false);
											print "<span class='noway'>Down or unselectable</span> ";
											$num = db_GetRow("SELECT COUNT(*) FROM " . $table_prefix[$r] . "failoverquerystore WHERE targethost='" . mres($slave[$r]) . "'");
											$on_local = $num[0];
											print "Jobs local: " . $on_local . "";
										}
										@mysql_close($db);

										DB_Connect($GLOBALS['ORIGINAL_REPOSITORY'], false);

									} else {
										print "No";
									}
									print "</td>";

									if ($maintenance_mode == "Yes") {
										print "<td><span class='noway'>" . $maintenance_mode . "</span></td>";
									} else {
										print "<td><span class='yesman'>" . $maintenance_mode . "</span></td>";
									}

									print "<td " . PrintToolTipCode("View table status") . "><a href='admin.php?ts=1&amp;daba=" . ($r+1) . "&amp;nonavbar=1'  onclick=\"window.open(this.href); return false;\"><img src='images/table.png'  alt=''></a></td><td " . PrintToolTipCode("Check (and delete) session records") . "><a href='admin.php?sessionscheck=1&amp;daba=" . ($r+1) . "'><img src='images/sessions.png'  alt=''></a>&nbsp;</td><td " . PrintToolTipCode("Check repository configuration") . "><a href='admin.php?checkcfg=1&amp;daba=" . ($r+1) . "'><img src='images/config.png'  alt=''></a>&nbsp;</td><td " . PrintToolTipCode("Wipe out this repository") . "><a href='admin.php?deldb=1&amp;daba=" . ($r+1) . "'><img src='images/delete.gif'  alt=''></a></td>";

									print "</tr>";
								} else {
									print "<tr><td>$r</td><td>$host[$r]</td><td>$database[$r]</td><td>$tbl</td><td><span style='color: #FF3300;'>Access denied or non-existent </span></td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td></tr>";
								}

						} else {
							 print "<tr><td>$r</td><td>$host[$r]</td><td>$database[$r]</td><td>n/a</td><td><span style='color: #FF3300;'>Database host onreachable</span></td><td></td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td><td>n/a</td></tr>";
						}

						}
					}
					?>
			<?php
				}

		print "</table>";
		print "<br>Total entities: " . FormatNumber($tot_ent) . " - Total records: " . FormatNumber($tot_rec) . " - Total size: " . ceil($tot_size) . " MB<br><br>";

} elseif (!$_FILES['userfile']['tmp_name']=="" && !$_FILES['userfile']['name']=="" && !$_FILES['userfile']['size']=="" && !$_FILES['userfile']['type']=="" && $_REQUEST['importentities']) {

		//  A file was attached


		// Read contents of uploaded file into variable
					$fp=fopen($_FILES['userfile']['tmp_name'] ,"rb");

					//$filecontent=fread($fp,filesize($_FILES['userfile']['tmp_name'] ));
					while (!feof($fp)) {
						$a = fgets($fp,1600000);
						$q = base64_decode($a);
						if (!$q=="") {
							mcq($q,$db);
						}
						unset($q);
					}
					fclose($fp);
					print filesize($_FILES['userfile']['tmp_name'] );
					print $count . " queries where successfully imported, " . $skip . " were skipped<br>";


} elseif ($_REQUEST['checkdb']) {
	$web=1;
	MainAdminTabs("datman");
	CheckDB();
	SwitchToRepos($GLOBALS['ORIGINAL_REPOSITORY']);
	EndHTML();
	exit;
} elseif ($_REQUEST['syscon'] || $_REQUEST['failoverman'] || $_REQUEST['datman'] || $_REQUEST['ieb'] || $_REQUEST['webdavstat'] || $_REQUEST['actions']) {

	$dabaJeroen = $repository_nr + 1;

	MainAdminTabs();

	// Quadrant 1
	if ($_REQUEST['syscon']) {
		AddBreadCrum("System configuration");

		$toprint .= "<h1>System configuration</h1>";
		$toprint .= "<table>";

		$toprint .= "<tr ><td><a href='dictedit.php?packman=1&amp;tab=80' class='plainlink'" . PrintToolTipCode("Upload new language packs, create new packs, create new masks, translate Interleave.") . ">Configure language packs</a><br><div class='light'>Upload new language packs, create new packs, create new masks, translate Interleave.</div></td><td><a class='plainlink' href='admin.php?reposman=1&amp;resman=1&amp;manageres=1'  " . PrintToolTipCode("Add new repositories, remove repositories, detailed database information.") . ">Configure repositories</a><br><div class='light'>Add new repositories, remove repositories, detailed database information.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?ForcedFields=1' " . PrintToolTipCode("Select which standard entity fields <strong>must</strong> be entered. These fields are category, due-date, and the main text box.") . ">Configure required entity fields (std. fields only)</a><br><div class='light'>Select which standard entity fields <strong>must</strong> be entered. These fields are category, due-date, and the main text box.</div></td>";


		if ($_COOKIE['online_development_mode'] == "y") {
			$toprint .= "<td><a class='plainlink' onclick=\"setCookie('online_development_mode','n');setCookie('ef_inline_edit','');alert('Left online development mode.');\"  " . PrintToolTipCode("Leave online development mode") . ">Leave online development mode</a><br><div class='light'>Leave online development mode.</div></td></tr>";
		} else {
			$toprint .= "<td><a class='plainlink' onclick=\"setCookie('online_development_mode','y');setCookie('ef_inline_edit','yes');alert('You are now in online development mode.');\" " . PrintToolTipCode("This enables some admin links on each entity page enabling you to develop forms faster.") . ">Enable online development mode (only for you)</a><br><div class='light'>This enables some admin links on each entity page enabling you to develop forms faster.</div></td></tr>";
		}

		if ($_COOKIE['disable_triggers'] == "y") {
			$toprint .= "<tr ><td><a class='plainlink' onclick=\"setCookie('disable_triggers','n');alert('Triggers enabled.');\" " . PrintToolTipCode("Enable triggers again.") . "><span class='noway'>Enable triggers</span></a><br><div class='light'>Enable triggers</div></td>";
		} else {
			$toprint .= "<tr ><td><a class='plainlink' onclick=\"setCookie('disable_triggers','y');alert('Triggers are disabled for this session.');\" " . PrintToolTipCode("This will prevent triggers from going off. Very handy for administrative work.") . ">Disable triggers for this session</a><br><div class='light'>This will prevent triggers from going off. Very handy for administrative work.</div></td>";
		}

		$toprint .= "<td><a class='plainlink' href='admin.php?EditVars=1&amp;WhatVar=stat' " . PrintToolTipCode("Edit the names and colors of the status and priority values you see when add or editing an entity.") . ">Edit status and priority values</a><br><div class='light'>Edit the names and colors of the status and priority values you see when add or editing an entity.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?checkdb=1&amp;daba=" . $dabaJeroen . "&amp;categoryvars=1'  " . PrintToolTipCode("Edit the category fields. This is only useful when you have the FORCECATEGORYPULLDOWN directive set to 'Yes'. You can do so in the Global Systems Values section.") . ">Edit categories ";
		if (strtoupper($lang[category])<>"CATEGORY") {
			$toprint .= "(" . strtolower($lang['category']) . ")";
		}
		$toprint .= "</a><br><div class='light'>Edit the category fields. This is only useful when you have the FORCECATEGORYPULLDOWN directive set to 'Yes'. You can do so in the Global Systems Values section.</div></td>";

		$toprint .= "<td><a class='plainlink' href='choose_cols.php?dothis=global' " . PrintToolTipCode("Configure which fields are chown in the main entity list and the main customer list. The user can override this (by clicking 'edit' on top of the list) as long as you don't disable the LETUSERSELECTOWNLISTLAYOUT directive in the Global System Values section.") . ">Edit lists lay-out</a><br><div class='light'>Edit the layout of the entitylist, the dashboard shortlist and the customer list.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?log=1' " . PrintToolTipCode("Search the log table.") . ">View log</a> or &nbsp; <a class='plainlink' href='admin.php?ViewJournal=1'  " . PrintToolTipCode("View entity journals.") . ">view journals</a><br><div class='light'>Search through the system log and entity, customer or flextable journals.</div></td><td><a class='plainlink' href='admin.php?GeneralFiles' " . PrintToolTipCode("View/manage general purpose files") . ">General purpose files</a><div class='light'>Upload, download or delete files for general use.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='#' onclick=\"PopFancyBoxLarge('Interleave advanced selection builder', 'index.php?ShowAdvancedQueryInterface&ParentEntityListAjaxHandler=" . htme($_REQUEST['AjaxHandler']) . "&Scope=system');\">System-wide selections</a><br><div class='light'>Create and edit selections which are visible for everybody.</div></td><td></td></tr>";

		//" []  ";

		$toprint .= "</table>";
	} elseif ($_REQUEST['sysman']) {

	} elseif ($_REQUEST['datman']) {
	// Quadrant 3
		AddBreadCrum("Data management");
		$toprint .= "<h1>Data management</h1>";
		$toprint .= "<table>";
		$toprint .= "<tr ><td colspan='2'><a class='plainlink' href='dedup.php'  " . PrintToolTipCode("Deduplicate records in a table") . ">De-duplicate " . strtolower($lang['entities']) . ", " . strtolower($lang['customers']) . ", or flextable records.</a><br><div class='light'>Use this function to de-duplicate all records in any data table based on a comparison made on one or more fields with a certain match.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?FindAndReplace' " . PrintToolTipCode("Use this to find and replace a string in a specific field.") . ">Find &amp; replace</a><br><div class='light'>Find &amp; replace values in a specific field</div></td>";
		$toprint .= "<td><a class='plainlink' href='index.php?ShowEntityList=1&amp;filter=viewdel'  " . PrintToolTipCode("View the list of deleted entities.") . ">View deleted entities</a><br><div class='light'>View the list of deleted entities.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?fysdelete=1' " . PrintToolTipCode("Delete an entity, or a set of entities, from disk. You can only physically delete entities which are already logically deleted.") . ">Delete one or more entities physically (db cleanup)</a><br><div class='light'>Delete an entity, or a set of entities, from disk. You can only physically delete entities which are already logically deleted.</div></td>";

		$toprint .= "<td><a class='plainlink' href='admin.php?DropAllCache=1&amp;datman=1'  " . PrintToolTipCode("Drop *all* cache. Only do this if you think you are seeing cached results while you should not see.") . ">Drop all cache</a><br><div class='light'>Drop all cache. Only do this if you think you are seeing cached results while you should not see.";
		if ($_REQUEST['DropAllCache']) {
			DropAllCache();
			$toprint .= " <font color='#ff0000'>(done!)</font>";
		}
		$toprint .= "</div></td></tr>";

//		$to<td><a class='plainlink' href='admin.php?advquery=1'  " . PrintToolTipCode("Run a custom-made database (SQL) query. You can use the wizard, or just type the query yourself as long as you don't use DELETE, TRUNCATE or DROP.") . ">Advanced database query</a><br><div class='light'>Run a custom-made database (SQL) query. You can use the wizard, or just type the query yourself as long as you don't use DELETE, TRUNCATE or DROP.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?checkdb=1&amp;web=1' " . PrintToolTipCode("This function checks your database for inconsistencies. Run it regularely!") . ">Database integrity check</a> and <a class='plainlink' href='admin.php?excessfields=1' " . PrintToolTipCode("This function checks your database for excess tables and fields") . ">excess tables &amp; fields check</a><br><div class='light'>These functions check your database for inconsistencies.</div></td><td><a class='plainlink' href='admin.php?RecalculateComputedFields=1'  " . PrintToolTipCode("This will re-calculate all computed extra fields. Use this only when getting unexpected results.") . ">Recalculate computed extra fields</a><br><div class='light'>This will re-calculate all computed extra fields. Use this only when getting unexpected results.</div></td></tr>";


		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?RunCompression=1' " . PrintToolTipCode("This will compress all old files and old versions of files") . ">Compress old versions and old files</a><br><div class='light'>Compress all old files and old versions of files</div></td><td><a class='plainlink' href='admin.php?RemoveAllEntityLocks=1'  " . PrintToolTipCode("This will drop all existing entity locks. Not recommended!") . ">Remove all entity locks</a><br><div class='light'>Remove all entity locks. Use this only when a lock stalls.</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?generateentities=almost' " . PrintToolTipCode("This function creates empty entities for every customer in your database which doesn't have an entity yet.") . ">Create an entity for each customer which doesn't have one yet</a><br><div class='light'>This function creates empty entities for every customer in your database which doesn't have an entity yet.</div></td><td><a class='plainlink' href='admin.php?ViewRelTree=1'  " . PrintToolTipCode("With this function view the total tree of entities and their relations") . ">Root entity relationship tree</a><br><div class='light'>View the complete tree of entities and their relations</div></td></tr>";

		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?MassMigrateForms=1&amp;datman=1' " . PrintToolTipCode("Use this to alter the HTML-form of a lot of entities at the same time.") . ">Mass-migrate entity forms</a><br><div class='light'>Use this to alter the HTML-form of a lot of entities at the same time.</div></td>";


		$num = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entityformcache");
		$toprint .= "<td><a class='plainlink' href='admin.php?ExpireAllFormCache=1&amp;datman=1'  " . PrintToolTipCode("This will drop all build up form and template cache. Use this when experiencing problems with forms (displaying not the right information).") . ">Expire form &amp; template cache</a> <a class='plainlink' href='admin.php?ShowFormCacheDetails=1&amp;datman=1' " . PrintToolTipCode("Click to see a cache usage summary") . ">" . $num[0] . " forms cached</a><br><div class='light'>This will drop all build up form and template cache. Use this when experiencing problems with forms (displaying not the right information).</div>";

		if ($_REQUEST['ShowFormCacheDetails']) {
			$toprint .=  "Form/template cache usage<table width='100%' class='crm'><thead><tr><td>User</td><td>Form/Template</td><td>table/id</td><td>#cache records</td></tr></thead>";
			$sql = "SELECT id, ADDFORMS FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers";
			$res1 = mcq($sql, $db);
			while ($row2 = mysql_fetch_array($res1)) {
				$sql = "SELECT user,formid,tabletype,COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entityformcache WHERE user=" . $row2['id'] . " GROUP BY CONCAT(tabletype, ' - ' , formid)";
				$res = mcq($sql, $db);
				while ($row = mysql_fetch_array($res)) {
					$toprint .=  "<tr><td>" . htme(GetUserName($row[0])) . "</td><td>" . htme(GetTemplateSubject($row[1])) . "</td><td>" . htme($row['tabletype']) . "</td><td class=\"rightalign\">" . FormatNumber($row[3],0) . "</td></tr>";
				}
			}
			$toprint .=  "</table>";

			$toprintlater .=  "<br><br>Access cache usage<table width='100%' class='crm'><thead><tr><td>Result</td><td>Count</td></tr></thead>";

			$res = db_GetArray("SELECT user,type,result,COUNT(*) AS total FROM " . $GLOBALS['TBL_PREFIX'] . "accesscache GROUP BY CONCAT(type, ' - ' , user, ' - ', result) ORDER BY type, user, result");
			$count=0;
			$excel = array(array("Table", "User", "OK/NOK/READONLY", "Count"));
			foreach ($res AS $row) {
				if ($row['type'] == "e") {
					$table = $lang['entity'];
					if ($table == "") $table = "Entity";
				} elseif ($row['type'] == "c") {
					$table = $lang['customers'];
					if ($table == "") $table = "Customer";
				} else {
					$table = GetFlextableName(str_replace("ft", "", $row['type']));
				}
				if ($table != $lasttable) {
					$toprintlater .= "<tr><td colspan=\"2\"><center><h2>" . $table . "</h2></center></td></tr>";
				}
				if ($row['user'] != $lastuser || $table != $lasttable) {
					$toprintlater .= "<tr><td colspan=\"2\"><strong>" . htme(GetUserName($row['user']))  . "</strong></td></tr>";
				}
				$toprintlater .=  "<tr><td>" . $row['result'] . "</td><td class=\"rightalign\">" . FormatNumber($row['total'],0) . "</td></tr>";
				$count += $row['total'];
				$lasttable = $table;
				$lastuser = $row['user'];
				$excel[] = array($table, GetUserName($row['user']), $row['result'], $row['total']);
			}
			$toprintlater .= "<tr><td></td><td class=\"rightalign\">" . FormatNumber($count,0) . "</td></tr>";
			$toprintlater .=  "</table>";
			$toprintlater .= "<p>Hint: use the command line utility (cmd.php) to create access cache records for all users / all tables by issuing &quot;exec create cache&quot. Use the output above to check if your security settings are ok.</p>";
			
			$toprintlater = ExcelDownloadLink($excel, "Download in Excel-format") . $toprintlater;
		}

		$toprint .= "</td></tr>";
		$toprint .= "</table>";
		
		$toprint .= $toprintlater;

	// Quadrant 4
	} elseif ($_REQUEST['ieb']) {
		AddBreadCrum("Import/export");

		$toprint .= "<h1>Import and export of data &amp; settings</h1>";
		$toprint .= "<table>";
		$toprint .= "<tr ><td><a class='plainlink' href='import.php'  " . PrintToolTipCode("Import and update all tables") . ">Import/update " . strtolower($lang['entities']) . ", " . strtolower($lang['customers']) . ", flextable or user records.</a><br><div class='light'>Use this function to import or update all records in any data table based on a copy/paste of a spreadsheet.</div></td><td><a class='plainlink' href='snapshot.php?dldump=1' " . PrintToolTipCode("Download SQL dump of the entire database") . ">Download SQL dump of the entire database</a><br><div class='light'>Download SQL dump of the entire database (without configuration snapshots).</div></td></tr>";

		$toprint .= "<tr><td><a class='plainlink' href='admin.php?ExportExtraFields=1'>Export</a> or <a class='plainlink' href='admin.php?ImportExtraFields' >import</a> extra field definitions<br><div class='light'>Create or restore a copy of the extra field definition  table.</div></td><td><a class='plainlink' href='admin.php?ExportSettings=1'>Export</a> or <a class='plainlink' href='admin.php?ImportSettings=1'>import</a> global settings table<br><div class='light'>Create or restore a copy of the global settings table.</div></td></tr>";

//		$toprint .= "<tr ><td>exit</td><td>exit</td></tr>";


//		$toprint .= "<tr ><td><a class='plainlink' href='dump_to_disk.php?' " . PrintToolTipCode("This will export all entities to disk. Advanced users only!") . ">Export everything to disk</a><br><div class='light'>Dump all Interleave data to disk in a human-understandable way (directory structure).</div></td>
		$toprint .= "<tr><td><a class='plainlink' href='snapshot.php'  " . PrintToolTipCode("View, create or use configuration snap-shots") . ">Create/restore configuration snap-shots (restore points)</a><br><div class='light'>View, create or use configuration snapshots. Configuration snapshots are 'photos' of your entire configuration, excluding forms.</div></td></tr>";


		$toprint .= "</table>";

	} elseif ($_REQUEST['actions']) {
		$toprint .= "<h1>Actions</h1>";
		$toprint .= "<table>";
		$toprint .= "<tr ><td><a class='plainlink' href='admin.php?SendAdHocEmail=1'  " . PrintToolTipCode("Send an e-mail message to all users including a list of their entities") . ">Send e-mail to all users</a><div class='light'>Send an e-mail to all Interleave users, including a list of all entities assigned to that user (personalized).</div></td></tr>";
		if ($_REQUEST['emm']) {
			MustBeAdmin();
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='Yes' WHERE setting='MAINTENANCE_MODE'", $db);
			$GLOBALS['MAINTENANCE_MODE'] = "Yes";
		} elseif ($_REQUEST['dmm']) {
			MustBeAdmin();
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "settings SET value='No' WHERE setting='MAINTENANCE_MODE'", $db);
			$GLOBALS['MAINTENANCE_MODE'] = "No";
		}
		if ($GLOBALS['MAINTENANCE_MODE'] == "Yes") {
			$toprint .= "<tr ><td><a class='plainlink' href='admin.php?actions=1&amp;dmm=1'  " . PrintToolTipCode("Disable maintenance mode. Nobody but administrators will be able to log in.") . ">Disable maintenance mode</a><div class='light'>Disable maintenance mode. This will re-enable logins from non-admin users</div></td></tr>";
		} else {
			$toprint .= "<tr ><td><a class='plainlink' href='admin.php?actions=1&amp;emm=1'  " . PrintToolTipCode("Enable maintenance mode. Nobody but administrators will be able to log in.") . ">Enable maintenance mode</a><div class='light'>Disables logins from non-admin users. Use this when performing site maintenance.</div></td></tr>";
		}


		if ($GLOBALS['USE_APC']) {
			$toprint .= "<tr ><td><a class='plainlink' href='admin.php?actions=1&amp;showapc=1'  " . PrintToolTipCode("View APC memory status") . ">View APC statistics</a><div class='light'>View the current APC cache status.</div></td></tr>";

		}
		$toprint .= "<tr ><td><a class='plainlink' href='basicstats.php'  " . PrintToolTipCode("View basic statistics") . ">View basic statistics</a><div class='light'>View basic statistics about logins, hits and flextable use.</div></td></tr>";

		$toprint .= "</table>";
	}



	print "<div class='adminLinkTable'>";

	print $toprint;

	print "</div>";

	if ($GLOBALS['USE_APC'] && $_REQUEST['showapc']) {


		print "<object height=\"600\" width=\"100%\" data=\"apc.php\" style=\"border-color:black;border-style:solid;border-width:1px;\" id=\"apcmessages\" type=\"text/html\"></object>";

	}
		log_msg("Administrative section accessed","");



} elseif ($_REQUEST['chglansettings']) {
		log_msg("System values saved","");
		if ($_REQUEST['lan']) {
				$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='current-language'";
				mcq($sql,$db);
				$sql= "SELECT TEXT FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='current-language'";
				if ($debug) { print "\nSQL: $sql\n"; }
				$result= mcq($sql,$db);
				$result= mysql_fetch_array($result);

				if ($result['TEXTID']=="") {
				    $sql= "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID,TEXTID,TEXT) VALUES('GLOBAL','current-language','" . mres($_REQUEST['lan']) . "')";
						if ($debug) { print "\nSQL: $sql\n"; }
					mcq($sql,$db);

				} else {
					$sql= "UPDATE " . $GLOBALS['TBL_PREFIX'] . "languages SET TEXT='" . mres($_REQUEST['lan']) . "' WHERE TEXTID='current-language'";
						if ($debug) { print "\nSQL: $sql\n"; }
					mcq($sql,$db);
				}
		}
		if ($_REQUEST['lanmask']) {
				$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='current-language-mask'";
				mcq($sql,$db);
				$sql= "SELECT TEXT FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='current-language-mask'";
				if ($debug) { print "\nSQL: $sql\n"; }
				$result= mcq($sql,$db);
				$result= mysql_fetch_array($result);

				if ($result[TEXTID]=="") {
				    $sql= "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "languages(LANGID,TEXTID,TEXT) VALUES('GLOBAL','current-language-mask','" . mres($_REQUEST['lanmask']) . "')";
						if ($debug) { print "\nSQL: $sql\n"; }
					mcq($sql,$db);

				} else {
					$sql= "UPDATE " . $GLOBALS['TBL_PREFIX'] . "languages SET TEXT='" . mres($_REQUEST['lanmask']) . "' WHERE TEXTID='current-language-mask'";
						if ($debug) { print "\nSQL: $sql\n"; }
					mcq($sql,$db);

				}
		}
} elseif ($_REQUEST['PhysDelFileConfirmed']) {

	$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE fileid='" . mres($_REQUEST['kid']) . "'";
	mcq($sql,$db);
	$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "blobs WHERE fileid='" . mres($_REQUEST['kid']) . "'";
	mcq($sql,$db);
	$files = 1;
	print "<span style='color: #ff0000';'>File " . $_REQUEST['kid'] . " " . $lang['isdel'] . "!</span>";
	log_msg("File #" . $_REQUEST['kid'] . " physically deleted","");

} elseif ($_REQUEST['PhysFileDel']) {
	log_msg("File list view","");
	print "<tr><td colspan='3'><strong>" . $lang['delfile'] . " " . $_REQUEST['kid'] . "</strong><br><br></td></tr>";
	print "<tr><td colspan='12'><table border='1' width='100%' cellspacing='0' cellpadding='8' style='background-color: #F2F2F2;'>";
	print "<tr><td>FileID</td><td>Filename</td><td>Creation Date</td><td>Size</td><td>Type</td><td>Referenced</td><td>Parent</td></tr>";
	print "Viewing files";
	$sql = "SELECT fileid,koppelid,filename,timestamp_last_change,filesize,filetype FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE fileid='" . mres($_REQUEST['kid']) . "'";
	$result= mcq($sql,$db);
	 while ($files= mysql_fetch_array($result)) {

		$sql1 = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid=" . $files['koppelid'];
		$result1= mcq($sql1,$db);
		$count_e1 = mysql_fetch_array($result1);
		$count_e = $count_e1[0];
		if ($count_e=="") {
			$ref = "<span style='color: #ff0000';'>Unreferenced!</span>";
			$parent = "<span style='color: #ff0000';'>n/a</span>";
		} else {
			$ref = "<span style='color: #33FF00;'>OK</span>";
			if ($count_e1['status']=="close") {
				$parent .="<span class='noway'>closed</span> ";
			}
			if ($count_e1['status']=="open") {
				print "<tr><td colspan='7'><span class='noway'>You cannot delete a file with an open parent status!</td></tr>";
				print "</table>";
				EndHTML();
				exit;
			}
			if ($count_e1['status']=="awaiting closure") {
				$parent .="<span style='color: #6633FF;'>awaiting closure</span> ";
			}
			if ($count_e1['deleted']=="y") {
				$parent .="<span class='noway'>deleted</span> ";
			} else {
				print "<tr><td colspan='7'><span class='noway'>You cannot delete a file which has a non-deleted parent!</td></tr>";
				print "</table>";
				EndHTML();
				exit;
			}
		}
	print "<tr><td>" . $files['fileid'] . "</td><td><a class='plainlink' href='csv.php?fileid=" . $files['fileid'] . "'>" . $files['filename'] . "</a></td><td>" . $files['timestamp_last_change'] . "</td><td>" . $files['filesize'] . "</td><td>" . $files['filetype'] . "</td><td>" . $ref . "</td><td>" . $parent . "</td></tr>";
	$tot++;
	unset($parent);
	$totsize = $totsize + $files[filesize];
			 }

	print "<tr><td colspan='7'>";
	print "<form id='formulier2' method='post' action=''><div class='showinline'><input type='hidden' name='PhysDelFileConfirmed' value='1'><input type='hidden' name='kid' value='" . htme($_REQUEST['kid']) . "'>";
	print "<input type='submit' style='width: 250px' name='knoppie' value='Confirm delete'></div></form>";
	print "</table></td></tr>";
} elseif ($_REQUEST['sysval']) {
	MainAdminTabs("sys");
	AddBreadCrum("Global system values");
	log_msg("Change System Values section accessed","");
	
	print "<h1>Global system settings</h1>";
	print "<h2>These settings apply to this database. They will not affect other repositories.</h2>";

	print "<form id='bogusform' method='post' action=''><div class='showinline'>";
	print AttributeLink("system", 2) . " &nbsp;Search: <img src='images/searchbox.png' alt='' class='search_img'><input type='search' class='search_input' name='SettingSearchQuery' id='JS_SettingSearchQuery'  onchange=\"document.forms['bogusform'].submit();\" value='" . $_REQUEST['SettingSearchQuery'] . "'>";
	print "<input type='hidden' name='sysval' value='1'>";
	if ($_REQUEST['SettingSearchQuery']) {
		print " &nbsp;&nbsp;<a href='admin.php?sysval=1'>[all]</a>";
	}
	print "</div></form><br><br>";

	print "<form id='settings' method='post' action=''><div class='showinline'>";
	print "<input type='hidden' name='password' value='" . htme($_REQUEST['password']) . "'>";
	print "<input type='hidden' name='chglansettings' value='1'>";

	print "<table class='interleave-table' width='80%'>";


	print "<thead><tr><td>Setting</td><td>Current value</td><td>Description</td></tr></thead>";

	if ($_REQUEST['SettingSearchQuery']) {
		$sql= "SELECT settingid, setting, value, discription FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting NOT LIKE '%STATISTICIMAGES%' AND setting NOT LIKE '%STASH%' AND setting<>'STATISTICIMAGES' AND setting<>'CHAT_HISTORY' AND setting<>'REQUIREDDEFAULTFIELDS' AND setting<>'TABCOLORS' AND setting<>'TABSTOHIDE' AND setting<>'CalendarDefinitions'  AND setting<>'SYNC_DISABLED_UNTIL' AND setting LIKE '%" . mres($_REQUEST['SettingSearchQuery']) . "%' ORDER BY setting";
	} else {
		$sql= "SELECT settingid, setting, value, discription FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting NOT LIKE '%STASH%' AND setting<>'STATISTICIMAGES' AND setting<>'CHAT_HISTORY' AND setting<>'REQUIREDDEFAULTFIELDS' AND setting<>'TABCOLORS' AND setting<>'TABSTOHIDE' AND setting<>'SYNC_DISABLED_UNTIL' AND setting<>'CalendarDefinitions' ORDER BY setting";
	}
	$Returned_Rows = 0;
	$result= mcq($sql,$db);
		while ($resarr=mysql_fetch_array($result)) {
			$Returned_Rows++;
			$Num = $resarr['settingid'];
			if (($resarr['setting']<>"Extra fields list") && ($resarr['setting']<>"Extra customer fields list") && ($resarr['setting']<>"PersonalTabs") && ($resarr['setting']<>"Category pulldown list") && ($resarr['setting']<>"MainListColumnsToShow") && ($resarr['setting']<>"NextInvoiceNumberCounter") && ($resarr['setting']<>"CustomerListColumnsToShow") && ($resarr['setting']<>"TimestampLastHousekeeping") && ($resarr['setting']<>"TimestampLastDuedateCron")) {
				print "<tr><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>" . strtoupper($resarr['setting']) . "</td><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>";
				if (substr($resarr['setting'],0,4) == "BODY" || $resarr['setting'] == "STANDARD_TEXT"|| $resarr['setting'] == "EMAILINBOX" || stristr($resarr['setting'],"Subject") || $resarr['setting'] == "ShowMainPageLinks" || $resarr['setting'] == "RSS_FEEDS" || $resarr['setting'] == "ALLOWEDIPADRESSES") {
					print "&lt;click to edit&gt;</td><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>$resarr[discription]</td></tr>";

				} elseif (stristr($resarr['setting'],"password")){
					if ($resarr['value']=="" || $resarr['value'] == "*NONE*") {
						print " -- no password set --";
					} else {
						print "********";
					}
					print "</td><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>" . htme($resarr['discription']) . "</td></tr>";
				} elseif ($resarr['setting']=="EXTRAFIELDLOCATION") {
					if ($resarr['value']=="A") {
						print "Just above text field";
					} else {
						print "Just above file list";
					}
					print "</td><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>$resarr[discription]</td></tr>";
				} elseif (strstr($resarr['setting'],"COLOR")) {
					print "<table><tr><td style='background: ". $resarr['value'] . "'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table>";
					print "</td><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>" . $resarr['discription'] . "</td></tr>";
				} elseif (strstr($resarr['setting'],"_FORM")) {
						if ($resarr['value'] == "Default") {
							print "Default";
						} else {
							print $resarr['value'] . " (" . GetTemplateName($resarr['value']) . ")";
						}
						print "</td><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>" . htme($resarr['discription']) . "</td></tr>";
				} else {
					if (strtoupper($resarr['value'])=="YES" || strtoupper($resarr['value'])=="ON") {
						$i1 = "<span class='yesman'>";
						$i2 = "</span>";
					} elseif (strtoupper($resarr['value'])=="NO" || strtoupper($resarr['value'])=="OFF") {
						$i1 = "<span class='noway'>";
						$i2 = "</span>";
					} else {
						unset($i1);
						unset($i2);
					}
					print $i1 . htme($resarr['value']) . "&nbsp;" . $i2;
					print "</td><td style='cursor:pointer' onclick='gobla(" . $resarr['settingid'] . ");'>" . $resarr['discription'] . "</td></tr>";
				}
			}
	}
	if ($Returned_Rows == 1) {
			print "<tr><td>";
			?>
				<script type="text/javascript">
				<!--
				document.location = 'admin.php?EditSysVar=<?php echo $Num;?>';
				//-->
				</script>
			<?php
			print "</td></tr>";
	}
	if (!$_REQUEST['SettingSearchQuery']) {
		print "<tr><td>Main language:</td>";
		print "<td>";
		print "<select name='lan'>";
			$sql= "SELECT TEXT FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='current-language' AND LANGID='GLOBAL'";
			$result= mcq($sql,$db);
			$result= mysql_fetch_array($result);
			$language_overall = $result[TEXT]; // This is the system-wide language variable, now check the user's preference

			$sql= "SELECT DISTINCT LANGID FROM " . $GLOBALS['TBL_PREFIX'] . "languages";
			$result= mcq($sql,$db);
			while ($resarr=mysql_fetch_array($result)){
					if ((trim($resarr[LANGID])=="") || ($resarr[LANGID] == "GLOBAL")) {
				// GLOBAL is a global language setting which ought to be ignored
					} else {
						if ($language_overall==$resarr[LANGID]) {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						print "<option value='" . $resarr['LANGID'] . "' " . $ins . ">" . $resarr['LANGID'] . "</option>";
					}

			}
			print "</select>";
			print "</td><td>System-wide default language</td></tr>";
			print "<tr><td>Language mask:</td>";
			print "<td>";
			print "<select name='lanmask'>";
			print "<option value='-'>None</option>";
			$sql= "SELECT TEXT FROM " . $GLOBALS['TBL_PREFIX'] . "languages WHERE TEXTID='current-language-mask' AND LANGID='GLOBAL'";
			$result= mcq($sql,$db);
			$result= mysql_fetch_array($result);
			$mask_oa = $result[TEXT]; // This is the system-wide language variable, now check the user's preference

			$sql= "SELECT DISTINCT LANGID FROM " . $GLOBALS['TBL_PREFIX'] . "languages";
			$result= mcq($sql,$db);
			while ($resarr=mysql_fetch_array($result)){
					if ((trim($resarr[LANGID])=="") || ($resarr[LANGID] == "GLOBAL")) {
				// GLOBAL is a global language setting which ought to be ignored
					} else {
						if ($mask_oa==$resarr[LANGID]) {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						print "<option value='" . $resarr['LANGID'] . "' " . $ins . ">" . $resarr['LANGID'] . "</option>";
					}

			}
			print "</select>";
		print "</td><td>System-wide default language mask</td></tr>";
		print "<tr><td colspan='3'><br><br>&nbsp;&nbsp;<input type='submit' name='aplysettings' value='Apply language and mask'></td></tr>";
	}

	print "</table>";
	print "</div>";
	print "</form>";


} elseif ($_REQUEST['fysconfirmed']) {

		MustBeAdmin();

		print "<tr><td colspan='2'>The entity and all its references were physically deleted.</td><tr>";
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($_REQUEST['fysdelid']) . "'";
		mcq($sql,$db);
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE koppelid='" . mres($_REQUEST['fysdelid']) . "'";
		mcq($sql,$db);
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($_REQUEST['fysdelid']) . "'";
		mcq($sql,$db);
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE eid='" . mres($_REQUEST['fysdelid']) . "'";
		mcq($sql,$db);
		$sql = "DELETE FROM " . $GLOBALS['TBL_PREFIX'] . "ejournal WHERE eid='" . mres($_REQUEST['fysdelid']) . "'";
		mcq($sql,$db);
		log_msg("PHYSICAL ENTITY DELETE: " . $_REQUEST['fysdelid'],"");
		qlog(INFO, "PHYSICAL ENTITY DELETE: " . $_REQUEST['fysdelid']);
		$_REQUEST['fysdelid'] = 0;
		if ($fromcustlist) {
				?>
					<script type="text/javascript">
					<!--
						document.location='index.php?ShowEntityList=1&amp;filter=custinsert';
					//-->
					</script>
				<?php
		}
} elseif ($_REQUEST['fysdelid']) {
	
	$tot = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE eid='" . mres($eid) . "' AND deleted='y'");
	print GetEntityCategory($eid) . "<br>";

	if ($tot<>1) {
		MainAdminTabs("datman");
		print "<table>";
		print "<tr><td colspan='3'><strong>Nothing found. This could mean this entity is not yet deleted, or maybe it doesn't exist at all.</td></tr>";
		print "</table>";
		EndHTML();
		exit;
		}

		print "<tr><td colspan='4'><br><strong>Please confirm by clicking the button below</strong><br>";
		print "<form id='confirm' method='post' action=''><div class='showinline'><input type='hidden' name='fysdelid' value='" . $_REQUEST['fysdelid'] . "'>";
		print "<input type='hidden' name='fysconfirmed' value='1'><input type='hidden' name='password' value='" . htme($_REQUEST['password']) . "'>";
		print "</td></tr><tr><td colspan='3'><br><input type='submit' name='knopje' value='Confirm physical deletion'></td></tr></table>";
		print "</div></form></table>";
		EndHTML();
		exit;
} elseif ($_REQUEST['fysdelete']) {
	MainAdminTabs("datman");
	print "<table>";
	print "<tr><td><strong>You can delete a single entity here, or delete a whole set of entities (which<br>were closed before a given date) by using the <a class='plainlink' href='db_clean.php'>database cleanup</a> function</strong><br><br></td></tr>";
	print "<tr><td colspan='8'>Delete a single entity, please enter the ID of the (already deleted) entity you whish to delete physically:</td></tr>";
	print "<tr><td colspan='8'><form id='fd' method='post' action=''><div class='showinline'><input type='hidden' name='password' value='" . htme($_REQUEST['password']) . "'>";
	print "<input type='text' size='3' name='fysdelid'><br><br><input type='submit' name='knopje' value='Delete'></div></form></td></tr>";
	print "</table>";


} elseif ($_REQUEST['fysdelete']) {
	print "Physically delete deleted entities";
} else {
//	} elseif ($_REQUEST['info']) {
	MustBeAdmin();
	MainAdminTabs('info');
	print "<div class='adminLinkTable'>";
	print "<h1>System information</h1>";
	print "<table>";



	$maxC = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "customer");
	$maxU = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers");
	$maxo = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity");

	
	print "<tr><td><h2>System information</h2>This is Interleave " . $GLOBALS['CRM_VERSION'] . ". Support tickets and feature requests can be submitted in the <a class='plainlink' href='http://support.interleave.nl/?url_to_go_to=edit.php?e=_new_|ftu=21325' onclick=\"window.open(this.href); return false;\">Interleave Support &amp; Development repository</a>. When submitting a support request, always include the information in this box in your initial post. Also, a lot of documentation is <a class='plainlink' href='#' onclick=\"poplargewindow('admin.php?docbox=1&amp;nonavbar=1');\" >shipped with this installation</td>";
	
	print "<td><h2>Commercial support / consultancy / hosting</h2>For hosting, consultancy or support of a more commercial nature the boys at <a href=\"http://atomos.nl\">Atomos Applications</a> are always happy to help! <br><br> Visit the Interleave web site at <a class='plainlink' href='http://www.interleave.nl' onclick=\"window.open(this.href); return false;\">http://www.interleave.nl</a> for new releases and the latest documentation.</td></tr>";

	print "<tr><td colspan=\"2\">";
	print "<h2>Messages &amp; warnings</h2>";
	print "<form id='bogusform' method='get' action=''><div class='showinline'><textarea name='infota' rows='10' cols='100' class='terminal'>";
	print "--------------- Messages ----------------\n\n";
	print CheckDatabaseSettings();
	print "----- Interleave Database information ------\n\n";
	print "Software version            " . $GLOBALS['VERSION'] . "\n";
	print "Database version            " . $GLOBALS['DBVERSION'] . "\n";
	print "-----------------------------------------\n";
	print "Number of entities          " . FormatNumber($maxo,0) . "\n";
	print "Number of users             " . FormatNumber($maxU,0) . "\n";
	print "Number of customers         " . FormatNumber($maxC,0) . "\n";
	$list = GetExtraFields();
	print "Extra entity fields         " . sizeof($list) . "\n";
	$list = GetExtraCustomerFields();
	print "Extra customer fields       " . sizeof($list) . "\n";
	print "Flextables                  " . sizeof(GetFlextableDefinitions()) . "\n";
	print "Authentication method       " . $GLOBALS['AUTH_TYPE']. "\n";
	print "-----------------------------------------\n";
	$num = CountTotalNumOfRecords($GLOBALS['TBL_PREFIX']);
	print "Total database records      " . FormatNumber($num,0) . "\n";
	$sql = "SHOW TABLE STATUS";
	$result= @mcq($sql,$db);
	while ($stat = @mysql_fetch_array($result))
	{
		$size += $stat["Data_length"];
		$size += $stat["Index_lenght"];
	}

	$tot_size += (($size/1024)/1024);
	$size = ceil((($size/1024)/1024)) . " MB";
	print "-----------------------------------------\n";
	print "Database size               " . $size . "\n";

	print "\n-------- Environment information --------\n\n";
	print "Server id     : " . $_SERVER['SERVER_SOFTWARE'] . "\n";
	print "PHP Version   : " . phpversion() . "\n";
	$a = (get_loaded_extensions());
	if (!in_array("mysql",$a)) {
			print "MySQL Version : no MySQL support detected\n";
			$fatal = 1;
	} else {
			print "MySQL Version : ";
				$res = mcq("SHOW VARIABLES LIKE 'version'",$db);
				while ($resrow = mysql_fetch_array($res)) {
					print $resrow[1] . "\n";
				}
				}
	if (in_array("gd",$a)) {
			print "GD Library    : Yes\n";
		} else {
			print "GD Library    : No\n";
		}
	if (ini_get('register_globals')=="1" || strtolower(ini_get('register_globals'))=="on" || strtolower(ini_get('register_globals'))=="yes") {
			print "Reg_Globals   : On\n";
	} else {
			print "Reg_Globals   : Off\n";
			//$fatal = 1;
	}
	if (ini_get("magic_quotes_gpc") == 1) {
			print "Magic quotes  : On\n";
	} else {
			print "Magic quotes  : Off\n";
	}

	print "Max exec time : " . ini_get("max_execution_time") . " sec\n";
	print "Mem limit     : " . ini_get("memory_limit") . "\n";
	print "Max POST      : " . ini_get("post_max_size") . "\n";
	print "Max file upl. : " . ini_get("upload_max_filesize") . "\n";
	if ($GLOBALS['CMD_CONN_OVRW']) {
		print "MySQL conn.   : Regular connection\n";
	} else {
		print "MySQL conn.   : Persistent connection\n";
	}



	$dir = ini_get("session.save_path");
	if ($f = tempnam($dir,"BLA")) {
			print "Tmp dir       : " . $dir . "\n";
			unlink($f);
	} else {
			print "Tmp dir       : " . $dir . " -> it's not accessable!\n";
	}
	$sql = "SELECT setting,value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting NOT LIKE '%password%' AND setting NOT LIKE '%CHAT%' AND setting != 'STATISTICIMAGES' AND setting NOT LIKE '%body%' AND setting NOT LIKE '%standard_text%' AND setting NOT LIKE '%TABSTOHIDE%' AND setting NOT LIKE '%TABCOLORS%' AND setting NOT LIKE '%mainpagelinks%' AND setting NOT LIKE '%ToShow%' AND setting NOT LIKE '%subject%' AND setting<>'REQUIREDDEFAULTFIELDS' AND setting<>'PersonalTabs' AND setting<>'EMAILINBOX' AND setting<>'RSS_FEEDS' AND setting<>'MAILPASS' ORDER BY setting";
	$res = mcq($sql, $db);
	print "\n----------- Setting Information ---------\n";
	print "Global Settings dump (excluding passwords &amp; HTML-forms)\n\n";
	while ($row = mysql_fetch_array($res)) {
		if ($row['setting'] == "TimestampLastDuedateCron" || $row['setting'] == "TimestampLastHousekeeping") {
			$row['value'] = date('Y-m-d H:i:s', $row['value']);
		}
		print fillout(strtoupper(htme($row['setting'])),40) . "\t" . htme($row['value']) . "\n";
	}
	print "</textarea></div></form></td></tr>";

	print "<tr><td colspan=\"2\"><h2>Messages at Interleave.nl</h2>Below is a frame, in which the messages page at interleave.nl is shown. It will only appear if your PC currently has an internet connection.<br><br>";
	print "<object height=\"135\" width=\"660\" data=\"https://www.interleave.nl/message.php?version=" . $GLOBALS['VERSION'] . "\" style=\"border-color:black;border-style:solid;border-width:1px;\" id=\"interleavemessages\" type=\"text/html\"></object>";
	print "</td></tr>";
	print "<tr><td><img title='This image is hosted on the project site' alt='sf.net image' src='https://sourceforge.net/sflogo.php?group_id=61096&amp;type=1'></td></tr>";
	print "</table></div>";

}

EndHTML();
function printbox($msg)
{
		global $printbox_size,$legend;

		if (!$printbox_size) {
			$printbox_size = "100%";
		}

		print "<table border='0' width='" . $printbox_size . "'><tr><td colspan='2'><fieldset>";
		if ($legend) {
			print "<legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;" . $legend . "</legend>";
		}
		print $msg . "</fieldset></td></tr></table><br>";

		unset($printbox_size);
		$legend = "";
} // end func
?>