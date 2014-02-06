<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file contains the "main" entity listing function
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */
// This function gets called from ShowEntityList in functions.php.port

$GLOBALS['DISABLE_DECISIONS'] = true;

function ShowEntityList2($given_query = false, $given_limit = false, $paginate, $CustomColumnLayout=false, $given_order=false, $list_title=false, $nofunctions=false, $includedeleted=false, $dontremembersort=false) {
	global $lang;

	// Set to true to enable query listings
	//$debug = true;
	if (isset($_REQUEST['debug']) && is_administrator()) {
		$debug = true;
	}

	if ($debug) {
		print "<h1>Listname: " . $CustomColumnLayout . ", title: " . $list_title . "</h1>";
		print "<h1>GivenQuery: " . $given_query. "</h1>";
		print "<h1>Session: " . $GLOBALS['session']. "</h1>";
	}

	// Convert the whole request array back
	foreach ($_REQUEST AS $tag => $value) {
		$_REQUEST[$tag] = html_entity_decode($value);
	}

	// Process in-line buttons
	if (is_numeric($_REQUEST['ProcessButton']) && IsValidEID($_REQUEST['ProcessButtonEid']) && CheckEntityAccess($_REQUEST['ProcessButtonEid']) != "nok") {

		// Check if the user has the rights to use this button. GetButtons() will only return allowed buttons by using GetExtraFields() as source.
			$x = GetButtons($_REQUEST['ProcessButton']);
			if ($x['fieldtype'] == "Button") {
				// OK the button is in the list so it is allowed.
				qlog(INFO, "An extra field button was pressed. Processing triggers.");
				journal($_REQUEST['ProcessButtonEid'], "User pressed button " . $x['id'] . "::" . $x['name'] . " (from entity list)");
				ProcessTriggers("ButtonPress" . $_REQUEST['ProcessButton'],$_REQUEST['ProcessButtonEid'],"");
				if ($GLOBALS['INTERRUPTMESSAGE'] != "") {
					print $GLOBALS['INTERRUPTMESSAGES'];
					$GLOBALS['INTERRUPTMESSAGE'] = false;
					$GLOBALS['INTERRUPTMESSAGES'] = "";
				}
			}

	}
	if (isset($_REQUEST['delElement']) && $_REQUEST['SavedSelection'] != "") {

		if ($_REQUEST['delElement'] != "" || $_REQUEST['delElement'] == 0) {
			$tmp = PopStashValue($_REQUEST['SavedSelection']);
			$tmp['name'] = "n/a";
			unset($tmp['selectionArray'][$_REQUEST['delElement']]);
			UpdateStashValue($_REQUEST['SavedSelection'], $tmp);
		}
	}

	if ($_REQUEST['loadSavedSelection'] == "none" || $_REQUEST['fs'] != "" || isset($_REQUEST['ClearFilter'])) {
		SetAttribute("user", "LastEntityListSelection", "", $GLOBALS['USERID']);
		$_GET['ClearFilter'] = 1;
		$grayedout = "";
		unset($_REQUEST['loadSavedSelection']);
		unset($_REQUEST['SavedSelection']);
	} elseif ($_REQUEST['loadSavedSelection'] == ""  && $_REQUEST['fs'] == "" && !isset($_REQUEST['ClearFilter']) && $_REQUEST['NoSelection'] == "") {
		$_REQUEST['loadSavedSelection'] = GetAttribute("user", "LastEntityListSelection", $GLOBALS['USERID']);
		if ($debug) print "<h2>Load selection from attributes: " . $_REQUEST['loadSavedSelection'] . "</h2>";
	}
	
	if ($_REQUEST['loadSavedSelection'] != "") {

		if (strstr($_SERVER['HTTP_REFERER'], "loadSavedSelection=")) {
			$dontshowselections = true;
		} else {
			SetAttribute("user", "LastEntityListSelection", $_REQUEST['loadSavedSelection'], $GLOBALS['USERID']);
		}

		$tmp = GetAttribute("system", "SavedEntityListSelections", 1);
		$add = GetAttribute("user", "SavedEntityListSelections", $GLOBALS['USERID']);

		

		if (!is_array($tmp)) $tmp = array();
		if (!is_array($add)) $add = array();

		$tmp = array_merge($tmp, $add);


		$given_query = CreateQueryFromSavedSelection($tmp[$_REQUEST['loadSavedSelection']], "SavedEntityListSelections");
		$st = PushStashValue(array("name" => $_REQUEST['loadSavedSelection'], "selectionArray" => $tmp[$_REQUEST['loadSavedSelection']]));
		$ss = "&SavedSelection=" . $st . "&";
		$_REQUEST['CustomColumnLayout'] = $_REQUEST['loadSavedSelection'];
		$func1 = "refresh_" . $_REQUEST['AjaxHandler'] . "('SavedSelection=" . $st . "&fs=" . $_REQUEST['fs'] . "&";
		$func2 = "');";
		$usingSavedSelection = $_REQUEST['loadSavedSelection'];

		$sortattribute = "LastListSort " . $_REQUEST['loadSavedSelection'];

	} elseif ($_REQUEST['SavedSelection'] != "" && !isset($_GET['ClearFilter'])) {

		$tmp = PopStashValue($_REQUEST['SavedSelection']);
		$given_query = CreateQueryFromSavedSelection($tmp['selectionArray'], "SavedEntityListSelections");
		$ss = "&SavedSelection=" . htme(jsencode($_REQUEST['SavedSelection'])) . "&";
		$usingSavedSelection = $tmp['name'];
		$func1 = "refresh_" . $_REQUEST['AjaxHandler'] . "('SavedSelection=" . htme(jsencode($_REQUEST['SavedSelection'])) . "&fs=" . $_REQUEST['fs']. "&";
		$func2 = "');";
		$_REQUEST['CustomColumnLayout'] = $tmp['name'];

		$sortattribute = "LastListSort " . $tmp['name'];


	} elseif ($CustomColumnLayout) {
		$sortattribute = "LastListSort " . $CustomColumnLayout;
	} else {
		$sortattribute = "LastListSort";
	}


	$selectionDescription = $tmp[$usingSavedSelection]['selectionDescription'];

	if (trim($selectionDescription) != "") {
		print "<h3>" . $selectionDescription . "</h3><br>";
	} elseif (GetAttribute("system", "EntityListMainHeaderHTML", 2) != "{{none}}" && GetAttribute("system", "EntityListMainHeaderHTML", 2) != "") {
		print "" . EvaluateTemplatePHP(GetAttribute("system", "EntityListMainHeaderHTML", 2), false, false, false) . "";
	}


	if ($_REQUEST['fs']) {
		$grayedout = " graytext";
	}
	if ($_REQUEST['querystash']) {
		$given_query = PopStashValue($_REQUEST['querystash']); // fetch query from stash
	}



	if ($given_query) { // a query was given which limits all results to the results in this query

		$res = db_GetArray(EvaluateTemplatePHP(ParseTemplateGeneric($given_query)));
		if ($debug) print "<h1>GIVEN: " . $given_query . "</h1>";
		$pregiven_query_ins = " AND " . $GLOBALS['TBL_PREFIX'] . "entity.eid IN(";
		$nf = "";
		foreach ($res AS $id) {
			if ($nf) $pregiven_query_ins .= ",";
			$pregiven_query_ins .= $id['eid'];
			$nf = true;
		}
		$pregiven_query_ins .= ")";
		if (!$nf) $pregiven_query_ins = " AND 1=0";
		$filter_active=true;
	} 

	if ($debug) print "<h1>Pregiven query extras: " . $pregiven_query_ins . "</h1>";

	// Load cache tables into memory (this is faster)
	qlog(INFO, "Loading access cache arrays... " . $GLOBALS['USE_EXTENDED_CACHE_WHAT'] . " (EXTENDED_CACHE)");
	$GLOBALS['CheckedEntityAccessArray'] = array();
	if ($GLOBALS['USE_EXTENDED_CACHE_WHAT'] == "all") {
		$res = mcq("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "accesscache WHERE user='" . mres($GLOBALS['USERID']) . "' AND type='e'", $db);
		while ($row = mysql_fetch_array($res)) {
			$GLOBALS['CheckedEntityAccessArray'][$row['eidcid']] = $row['result'];
		}
	}



	$random_header_string = randomstring(12,4);

	if ($debug) {
		print "<pre>";
		print_r($_REQUEST);
		print "</pre>";
	}

	if ($_REQUEST['sort']) { // Reset any given order when the user asks for another order
		unset($given_order);
	} elseif (is_numeric($given_order)) {
		$given_order = PopStashValue($given_order);
	} elseif ($given_order) {
		$given_order = $given_order;
	}



	if ($CustomColumnLayout) {
		$_REQUEST['CustomColumnLayout'] = $CustomColumnLayout;
		$_REQUEST['filter_id'] = $CustomColumnLayout;
	}


	if ($_REQUEST['deletedatefilter']) {
		$lf = GetLastUserFilter();
		unset($lf['datefilter']);
		SetUserFilter($lf);
	}

	if ($GLOBALS['CLIPLISTAT'] == "" || $GLOBALS['CLIPLISTAT'] == 0 || !$GLOBALS['CLIPLISTAT']) {
		$GLOBALS['CLIPLISTAT'] = 23784657823465729365238974;
	}

	$desc = $_REQUEST['desc'];
	$func = "refresh_" . htme($_REQUEST['AjaxHandler']) . "('" . $ss . "&fs=" . $_REQUEST['fs'] . "&";

	$highlight_color = $GLOBALS['DFT_FOREGROUND_COLOR'];
	$normal_color = "#939393";


	// Create filter options

	$datefilter = CreateDateFilterOptionsList();

	$last_filter = GetLastUserFilter();
	$FA_header = array();
	$FA_data = array();
	$FA_datat = array();
	$FA_datal = array();




	$filter = $_REQUEST['filter'];

	if ($_REQUEST['sort'] && !$dontremembersort) {
		$sort = $_REQUEST['sort'];
	}
	if ($dontremembersort) {
		$GLOBALS['ShowSortLink'] = "no";
	}
	$desc = $_REQUEST['desc'];
	unset($GLOBALS['From_Summary']);

	if (stristr($_SERVER['REQUEST_URI'],"index.php")) {
		$return_to_list = 1;
	} elseif (stristr($_SERVER['REQUEST_URI'],"summary.php")) {
		$return_to_list = $_REQUEST['fromlistAjaxHandler'];
	}

	if (($filter=="viewdel" || $filter=="showall") && (strtoupper($GLOBALS['ShowDeletedViewOption'])<>"YES") && !is_administrator()) {
		// security
		PrintAdminError();
		EndHTML();
		exit;
	}

//	print "<!-- Entering function ShowEntityList [mark] -->";

	if (!$sort && !$dontremembersort) {

		$sort = GetAttribute("user", $sortattribute, $GLOBALS['USERID']);

		if (stristr($sort," DESC")) {
			$sort = str_replace(" DESC","",$sort);
			$desc = 1;
		}
//		print "<!-- LAST SORT APPLIED -->";
		if ($debug) {
			print "<h1>Fetched sort from $sortattribute: $sort</h1>";
		}
	} elseif (!$dontremembersort) {

		$tmpsort = $sort;
		if ($desc) {
			$tmpsort .= " DESC";
		}

		SetAttribute("user", $sortattribute, $tmpsort, $GLOBALS['USERID']);
		if ($debug) print "SET $sortattribute $tmpsort";
//		print "<!-- THIS SORT SAVED -->";
		unset($_REQUEST['Pag_Moment']);
	}

	if ($_REQUEST['filter'] =="viewdel") {
		$filter_id = "viewdel";
	} elseif ($_REQUEST['filter'] =="custinsert") {
		$filter_id = "custinsert";
	} elseif ($_REQUEST['filter_id']) {
		$filter_id = $_REQUEST['filter_id'];
	} else {
		$filter_id = "open";
	}
//	print "<h2>Filter id: '" . $filter_id . "'</h2>";

	if (!$_REQUEST['ClearFilter']) {
		$last_filter = GetLastUserFilter();
		qlog(INFO, "Getting last filter");
	}


// Store current filter in database serialized array

	if (isset($_REQUEST['pdfiltercustomer']))	{
		$last_filter[$filter_id]['pdfiltercustomer'] = $_REQUEST['pdfiltercustomer'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterstatus']))		{
		$last_filter[$filter_id]['pdfilterstatus'] = $_REQUEST['pdfilterstatus'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterpriority']))	{
		$last_filter[$filter_id]['pdfilterpriority'] = $_REQUEST['pdfilterpriority'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterowner']))		{
		$last_filter[$filter_id]['pdfilterowner'] = $_REQUEST['pdfilterowner'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterownergroup']))		{
		$last_filter[$filter_id]['pdfilterownergroup'] = $_REQUEST['pdfilterownergroup'];
		$newfilter = true;
	}

	if (isset($_REQUEST['pdfilterassignee']))	{
		$last_filter[$filter_id]['pdfilterassignee'] = $_REQUEST['pdfilterassignee'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterassigneegroup']))		{
		$last_filter[$filter_id]['pdfilterassigneegroup'] = $_REQUEST['pdfilterassigneegroup'];
		$newfilter = true;
	}

	if (isset($_REQUEST['pdfilterstartdate']))	{
		$last_filter[$filter_id]['pdfilterstartdate'] = $_REQUEST['pdfilterstartdate'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterduedate']))	{
		$last_filter[$filter_id]['pdfilterduedate'] = $_REQUEST['pdfilterduedate'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterlastupdate']))	{
		$last_filter[$filter_id]['pdfilterlastupdate'] = $_REQUEST['pdfilterlastupdate'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfiltercreationdate']))	{
		$last_filter[$filter_id]['pdfiltercreationdate'] = $_REQUEST['pdfiltercreationdate'];
		$newfilter = true;
	}
	if (isset($_REQUEST['pdfilterclosedate']))	{
		$last_filter[$filter_id]['pdfilterclosedate'] = $_REQUEST['pdfilterclosedate'];
		$newfilter = true;
	}

	if (isset($_REQUEST['pdfilterrowsperpage']))	{
		$last_filter[$filter_id]['pdfilterrowsperpage'] = $_REQUEST['pdfilterrowsperpage'];
		$newfilter = true;
	}

	$ExtraFieldsList = GetExtraFields();
	foreach($ExtraFieldsList as $field) {
			$element = "EFID" . $field['id'];
			if (isset($_REQUEST[$element]) && $_REQUEST[$element]<>"all") {
				$last_filter[$filter_id]['pdfilterextrafield'][$element] = $_REQUEST[$element];
				$ExtraFieldSearched=true;
				$newfilter = true;
			} elseif ($last_filter[$filter_id]['pdfilterextrafield'][$element] && $_REQUEST[$element]<>"all") {
				$_REQUEST[$element] = $last_filter[$filter_id]['pdfilterextrafield'][$element];
				$ExtraFieldSearched=true;
			} elseif ($_REQUEST[$element]=="all") {
				unset($last_filter[$filter_id]['pdfilterextrafield'][$element]);
				$newfilter = true;
			}
	}
	$ExtraFieldsList = GetExtraCustomerFields();
	foreach($ExtraFieldsList as $field) {
			$element = "EFID" . $field['id'];
			if (isset($_REQUEST[$element]) && $_REQUEST[$element]<>"all") {
				$last_filter[$filter_id]['pdfilterextrafield'][$element] = $_REQUEST[$element];
				$ExtraFieldSearched=true;
				$newfilter = true;
			} elseif ($last_filter[$filter_id]['pdfilterextrafield'][$element] && $_REQUEST[$element]<>"all") {
				$_REQUEST[$element] = $last_filter[$filter_id]['pdfilterextrafield'][$element];
				$ExtraFieldSearched=true;
			} elseif ($_REQUEST[$element]=="all") {
				unset($last_filter[$filter_id]['pdfilterextrafield'][$element]);
				$newfilter = true;
			}
	}
	$tmp = GetFlextableDefinitions();
	foreach ($tmp AS $ft) {
		$ExtraFieldsList = GetExtraFlextableFields($ft[0]);
		foreach($ExtraFieldsList as $field) {
				$element = "EFID" . $field['id'];
				if (isset($_REQUEST[$element]) && $_REQUEST[$element]<>"all") {
					$last_filter[$filter_id]['pdfilterextrafield'][$element] = $_REQUEST[$element];
					$newfilter = true;
				} elseif ($last_filter[$filter_id]['pdfilterextrafield'][$element] && $_REQUEST[$element]<>"all") {
					$_REQUEST[$element] = $last_filter[$filter_id]['pdfilterextrafield'][$element];
				} elseif ($_REQUEST[$element]=="all") {
					unset($last_filter[$filter_id]['pdfilterextrafield'][$element]);
					$newfilter = true;
				}
				$element = "SUMEFID" . $field['id'];
				if (isset($_REQUEST[$element]) && $_REQUEST[$element]<>"all") {
					$last_filter[$filter_id]['pdfilterextrafield'][$element] = $_REQUEST[$element];
					$newfilter = true;
				} elseif ($last_filter[$filter_id]['pdfilterextrafield'][$element] && $_REQUEST[$element]<>"all") {
					$_REQUEST[$element] = $last_filter[$filter_id]['pdfilterextrafield'][$element];
				} elseif ($_REQUEST[$element]=="all") {
					unset($last_filter[$filter_id]['pdfilterextrafield'][$element]);
					$newfilter = true;
				}
		}

	}


	if ($_REQUEST['ClearFilter'] && !$_REQUEST['Pag_Moment']) {
		unset($last_filter[$_REQUEST['filter_id']]);
		unset($last_filter[$filter_id]);
		unset($_REQUEST['filter_id']);
		unset($_REQUEST['Pag_Moment']);
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET LASTFILTER='" . mres(serialize($last_filter)) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'";
		mcq($sql,$db);
		$newfilter = true;
	} elseif ($_REQUEST['newfilter'] && !$_REQUEST['Pag_Moment']) {
		$last_filter_s = serialize($last_filter);
		$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET LASTFILTER='" . mres($last_filter_s) . "' WHERE id='" . mres($GLOBALS['USERID']) . "'";
		mcq($sql,$db);
		unset($_REQUEST['Pag_Moment']);
	}

	$last_filter = $last_filter[$filter_id];

	if ($last_filter['pdfilterassignee']=="CURUSER") $last_filter['pdfilterassignee'] = $GLOBALS['USERID'];
	if ($last_filter['pdfilterowner']=="CURUSER")	 $last_filter['pdfilterowner'] = $GLOBALS['USERID'];


	// Determine pagination

	if (is_numeric($paginate) && $paginate!=0 && !$last_filter['pdfilterrowsperpage']) {
		$PAGINATEROWS = $paginate;
	} elseif (is_numeric($last_filter['pdfilterrowsperpage'])) {
		$PAGINATEROWS = $last_filter['pdfilterrowsperpage'];
	} else {
		$PAGINATEROWS = $GLOBALS['PaginateMainEntityList'];
	}



	//print "<h1>" . $query . "</h1>";

	$cl = GetClearanceLevel();

	if (in_array("NoOwnNoAssign", $cl)) {
		$GLOBALS['ShowFilterInMainList'] = "No";
	}
	if (!in_array("MayUseMainlistFilter", $cl)) {
		$GLOBALS['ShowFilterInMainList'] = "No";
	}

	$topost = "index.php";
	if (!stristr($_SERVER['QUERY_STRING'],"logout")) {
		$ins = $_SERVER['QUERY_STRING'];
	}
	$PrintedRowCounter=0;
	$outputbuffer = "";

	if ($_REQUEST['fs']) {
		unset($last_filter);
		$GLOBALS['ShowFilterInMainList'] = "No";

	}


	$outputbuffer .= "<form id='SuperForm' method='post' action='index.php'><div class='showinline'>";
	$outputbuffer .= "<input type='hidden' name='newfilter' value='1'>";
	$outputbuffer .= "<input type='hidden' name='ShowEntityList' value='1'>";
	$outputbuffer .= "<input type='hidden' name='filter' value='" . $filter . "'>";
	$outputbuffer .= "<input type='hidden' name='tab' value='" . $_REQUEST['tab'] . "'>";

	$outputbuffer .= "<input type='hidden' name='fromlistnow' value='1'>";
	$outputbuffer .= "<input type='hidden' name='fromlisturl' value='" . $_REQUEST['fromlistAjaxHandler'] . "'>";



	$date = date('d-m-Y');
	$sqldate = date('Y-m-d');

	// Check if this user may access this page...
	CheckPageAccess("entity");
	$cl = GetClearanceLevel();
	if (CheckFunctionAccess("MaySelectColumns") != "nok") { //selectcolumns.gif
		$CurURL = base64_encode(($_SERVER['PHP_SELF'] . "?" .$QUERY_STRING));
		if ($_REQUEST['CustomColumnLayout']) {
			$html_ins = "&amp;CustomColumnOverrule=" . $_REQUEST['CustomColumnLayout'];
			unset($tmp);
			$t1 = str_replace("Tab", "", $_REQUEST['CustomColumnLayout']);
			if (!is_array($GLOBALS['PersonalTabs']) && $GLOBALS['PersonalTabs'] != "") {
				$GLOBALS['PersonalTabs'] = unserialize($GLOBALS['PersonalTabs']);
			}
			if (!is_array($GLOBALS['PersonalTabs'][$t1])) {
				$GLOBALS['PersonalTabs'][$t1] = array();
			}
			$tmpar = $GLOBALS['PersonalTabs'][$t1]['ColumnsToShow'];

			$tmp = unserialize($tmpar);
			if ($tmp['NoPersonalOverrule']) {
				$notallowed = true;
				unset($customize);
				qlog(INFO, "DESCISION; Not showing 'choose columns for this list' link (no access)");
			} else {
				$customize = "<a " . PrintAltToolTipCode(htme(htme($lang['selectcolumns']) . " for " . htme($_REQUEST['CustomColumnLayout']))) . " onclick=\"popcolumnchooser('" . $_REQUEST['AjaxHandler'] . $html_ins . "');\"><img alt='" . htme($lang['selectcolumns']) . "' src='images/selectcolumns.gif'></a>";
			}
		} else {
			$customize = "<a " . PrintAltToolTipCode(htme($lang['selectcolumns'])) . " onclick=\"popcolumnchooser('" . htme($_REQUEST['AjaxHandler']) . "');\"><img alt='" . htme($lang['selectcolumns']) . "' src='images/selectcolumns.gif'></a>";
		}
	}


	if ($debug) {
		print "<h1>Start evaluation of $sort</h1>";
	}
	$tosort = $sort;
	switch ($tosort) {
		case "eid":
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.eid";
		break;
		case "" . $GLOBALS['TBL_PREFIX'] . "customer.custname";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "customer.custname";
		break;
		case "owner";
			$sort_on = "uj2.FULLNAME";
		break;
		case "assignee";
			$sort_on = "uj1.FULLNAME";
		break;
		case "ownergroup";
			$sort_on = "uj2.PROFILE";
		break;
		case "assigneegroup";
			$sort_on = "uj1.PROFILE";
		break;
		case "status";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.status";
		break;
		case "priority";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.priority";
		break;
		case "category";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.category";
		break;
		case "sqldate";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.sqldate";
		break;
		case "duedate";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.sqldate";
		break;
		case "sqlstartdate";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.sqlstartdate";
		break;
		case "lastupdate";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.timestamp_last_change";
		break;
		case "openepoch";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.openepoch";
		break;
		case "closeepoch";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.closeepoch";
		break;
		case "tp";
			$sort_on = $GLOBALS['TBL_PREFIX'] . "entity.timestamp_last_change";
		break;
		case "duration";
			$sort_on = "duration";
		break;
		default:
			if (substr($sort, 0, 4) == "EFID") {
				$field = str_replace("EFID", "", $sort);
				if (GetExtraFieldType($field) == "date" || (GetExtraFieldType($field) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field) == "Date")) {
					$sort_on = "STR_TO_DATE(EFID" . mres($field) . ",'%d-%m-%Y') ";
				} elseif (GetExtraFieldType($field) == "date/time") {
					$sort_on = "EFID" . mres($field);
				} elseif (GetExtraFieldType($field) == "numeric" || (GetExtraFieldType($field) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field) == "Numeric")) {
					$sort_on = "CAST(" . mres($sort) . " AS DECIMAL(15,3))";
				} else {
					$sort_on = " CONCAT(" . mres($sort) . ")";
				}
			}
		break;
	}

	if ($debug) {
		print "<h1>End evaluation of $sort : $sort_on</h1>";
	}

	if ($sort == "eid" && !$desc) {
		$link = "<a href='#' onclick=\"" . $func . "&sort=eid&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
	} elseif ($sort == "eid" && $desc) {
		$link = "<a href='#' onclick=\"" . $func . "&sort=eid');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
	} else {
		$link = "<a href='#' onclick=\"" . $func . "&sort=eid');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
	}
	if ($GLOBALS['ShowSortLink'] =="no") unset($link);

	$outputbuffer .= "<table width='100%' class='crm'><thead><tr><td class=\"th_eid\"><span class='" . $grayedout . "'>" . $link . "EID</span></td>";
	$interimheader = "<td class=\"ti_eid\"><span class='" . $grayedout . "'>" . $link . "EID</span></td>";

	array_push($FA_header,"eid");
	$backup = $GLOBALS['UC']['MainListColumnsToShow'];
	$zindex = 40;
	if ($_REQUEST['CustomColumnLayout']) {
		// Personal overrule here
		$tabnr = str_replace("Tab", "", $_REQUEST['CustomColumnLayout']);
		$cl = GetClearanceLevel();
		if (CheckFunctionAccess("MaySelectColumns") != "nok") {
			qlog(INFO, "DESCISION: Check for column layout for tab " . $tabnr);
			$tmp = unserialize($GLOBALS['PersonalTabs'][$tabnr]['ColumnsToShow']);
			if (is_array($GLOBALS['UC']['MainListColumnsToShow'][$_REQUEST['CustomColumnLayout']]) && !$tmp['NoPersonalOverrule']) {
				$GLOBALS['UC']['MainListColumnsToShow'] = $GLOBALS['UC']['MainListColumnsToShow'][$_REQUEST['CustomColumnLayout']];
				qlog(INFO, "DESCISION: Specific list layout, personal overrule");
			} elseif ($tmp['NoPersonalOverrule']) {
				$GLOBALS['UC']['MainListColumnsToShow'] = unserialize($GLOBALS['PersonalTabs'][$tabnr]['ColumnsToShow']);
				qlog(INFO, "DESCISION: Specific list layout, no overrule allowed (disabled for this list)");
			} elseif (is_array(unserialize($GLOBALS['PersonalTabs'][$tabnr]['ColumnsToShow']))) {
				$GLOBALS['UC']['MainListColumnsToShow'] = unserialize($GLOBALS['PersonalTabs'][$tabnr]['ColumnsToShow']);
				qlog(INFO, "DESCISION: Specific list layout, no overrule");
			} else {
				qlog(INFO, "DESCISION: No specific list layout, no overrule");
				// In most cases this will be fine, except when it's a called list
				// If so, we need to set it to a minumum column layout to avoid confusion
				$ar = array("cb_cust","cb_assignee","cb_status","cb_category","id");
				if ($CustomColumnLayout) {
					$GLOBALS['UC']['MainListColumnsToShow'] = unserialize(GetSetting("SHORTLISTLAYOUT"));
					if (sizeof($GLOBALS['UC']['MainListColumnsToShow']) == 0) {
						$GLOBALS['UC']['MainListColumnsToShow'] = array("cb_cust" => 1,"cb_assignee" => 1,"cb_status" => 1,"cb_category" => 1,"id" => 1);
					}

				}

			}
		} else {
			$GLOBALS['UC']['MainListColumnsToShow'] = unserialize($GLOBALS['PersonalTabs'][$tabnr]['ColumnsToShow']);
			qlog(INFO, "DESCISION: Specific list layout, no overrule allowed");
		}
	} else {
		qlog(INFO, "DESCISION: No specific list layout");
	}
	// Extra check
	if (sizeof($GLOBALS['UC']['MainListColumnsToShow']) == 0 || $GLOBALS['UC']['MainListColumnsToShow']=="") {
		$GLOBALS['UC']['MainListColumnsToShow'] = $backup;
		qlog(INFO, "DESCISION: Falling back to original column layout - specific setting contains no columns at all");
	}

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust']) {
		if ($last_filter['pdfiltercustomer'] <> "all" && $last_filter['pdfiltercustomer'] <> "") {
			$outputbuffer .= "<td class='nwrp highlightedtableheadercell th_customer'>";
			$showclass=" show_content";
			$hideclass=" hide_content ";

		} else {
			$outputbuffer .= "<td class='nwrp th_customer'>";
			$showclass="";
				$hideclass="";

		}

		if ($sort == "" . $GLOBALS['TBL_PREFIX'] . "customer.custname" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=" . $GLOBALS['TBL_PREFIX'] . "customer.custname&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == $GLOBALS['TBL_PREFIX'] . "customer.custname") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=" . $GLOBALS['TBL_PREFIX'] . "customer.custname');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=" . $GLOBALS['TBL_PREFIX'] . "customer.custname');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}

		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		array_push($FA_header,$lang['customer']);
		$random_header_string = randomstring(12,4);
		// FILTER PULLDOWN CUSTOMER
		if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES") {

			$outputbuffer .= $link . "";

			if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= htme($lang['customer']) . "<br>";

			$filtername = 'pdfiltercustomer';
			$outputbuffer .= "<div class='box_interactive_list_item" . $showclass . "' id='customerheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'customerheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . (htme($lang['customer'])) . " [" . htme($lang['all']) . "]</option>";
			$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE active<>'No' ORDER BY custname";
			$result= mcq($sql,$db);

			while ($UsersArray= mysql_fetch_array($result)) {
				$auth = CheckCustomerAccess($UsersArray['id']);
				if ($auth == "ok" || $auth == "readonly") {
					if ($UsersArray['id'] == $last_filter[$filtername]) {
							$a = "selected='selected'";
					} else {
							$a = "";
					}
					 $outputbuffer .= "<option value='" . htme($UsersArray['id']) . "' " . $a . ">" . htme($UsersArray['custname']) . "</option>";
				}
			}
			$outputbuffer .= "</select>";

		} else {
			$outputbuffer .= $link;
		}
	$interimheader.= "<td class=\"ti_customer\">" . $link . "<strong>" . htme($lang['customer']) . "</strong>";
	$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='customerheadertext' onmouseover=\"SwitchIAtableheader('customerheader" . $random_header_string . "','customerheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('customerheader" . $random_header_string . "','customerheadertext');\">" . htme($lang['customer']) . "</div></td>";
} // end MainListColumnsToShow[cust]

		if ($GLOBALS['UC']['MainListColumnsToShow']['cb_owner']) {

			if ($sort == "owner" && !$desc) {
				$link = "<a href='#' onclick=\"" . $func . "&sort=owner&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
			} elseif ($sort == "owner") {
				$link = "<a href='#' onclick=\"" . $func . "&sort=owner');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
			} else {
				$link = "<a href='#' onclick=\"" . $func . "&sort=owner&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
			}
			if ($GLOBALS['ShowSortLink'] =="no") unset($link);

			array_push($FA_header,$lang['owner']);

			// FILTER PULLDOWN OWNER
			if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES") {

				if ($last_filter['pdfilterowner'] <> "all" && $last_filter['pdfilterowner'] <> "") {
					$td = "<td class='nwrp highlightedtableheadercell th_owner'>";
					$showclass=" show_content";
					$hideclass=" hide_content ";

				} else {
					$td = "<td class='nwrp th_owner'>";
					$showclass="";
				$hideclass="";

				}

				$outputbuffer .= $td . $link;
				if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<strong>" . htme($lang['owner']) . "</strong><br>";

				$filtername = 'pdfilterowner';
				$outputbuffer .= "<div class='box_interactive_list_item" . $showclass . "' id='ownerheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'ownerheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

				$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE active='yes' AND HIDEFROMASSIGNEEANDOWNERLISTS='n' ORDER BY FULLNAME";
				$result= mcq($sql,$db);
				$outputbuffer .= "<option value='all' " . $a . ">" . ($lang['owner'] . " [" . $lang['all'] . "]") . "</option>";
				//if (strlen($lang['owner'] . " [" . $lang['all'] . "]") > 10) {
				//	$outputbuffer .= "<option value='all' " . $a . ">" . $lang['owner'] . " [" . $lang['all'] . "]</option>";
				//}
				while ($UsersArray= mysql_fetch_array($result)) {
					if ($UsersArray['id']== $last_filter[$filtername]) {
							$a = "selected='selected'";
					} else {
							$a = "";
					}

					 $outputbuffer .= "<option value='" . htme($UsersArray['id']) . "' " . $a . ">" . htme(GetUserName($UsersArray['id'])) . "</option>";
				}
				$outputbuffer .= "</select>";

			} else {
				$outputbuffer .= "<td class='nwrp th_owner'>" . $link . "";
			}
			$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='ownerheadertext' onmouseover=\"SwitchIAtableheader('ownerheader" . $random_header_string . "', 'ownerheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('ownerheader" . $random_header_string . "', 'ownerheadertext');\">" . htme($lang['owner']) . "</div></td>";
			$interimheader .= "<td class='nwrp ti_owner'><strong>" . $link . htme($lang['owner']) . "</strong>";
			unset($link);
		} // end MainList etc owner

		if ($GLOBALS['UC']['MainListColumnsToShow']['cb_ownergroup']) {

			if ($sort == "ownergroup" && !$desc) {
				$link = "<a href='#' onclick=\"" . $func . "&sort=ownergroup&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
			} else if ($sort == "ownergroup") {
				$link = "<a href='#' onclick=\"" . $func . "&sort=ownergroup');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
			} else {
				$link = "<a href='#' onclick=\"" . $func . "&sort=ownergroup&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
			}
			if ($GLOBALS['ShowSortLink'] =="no") unset($link);

			array_push($FA_header,"Group (" . $lang['owner'] . ")");

			// FILTER PULLDOWN OWNER
			if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES") {

				if ($last_filter['pdfilterownergroup'] <> "all" && $last_filter['pdfilterownergroup'] <> "") {
					$td = "<td class='nwrp th_ownergroup highlightedtableheadercell'>";
					$showclass = " show_content";
					$hideclass="hide_content";
				} else {
					$td = "<td class='nwrp th_ownergroup'>";
					$showclass="";
				$hideclass="";

				}

				$outputbuffer .= $td . $link;
				if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<strong>" . htme($lang['group']) . "(" . htme($lang['owner']) . ")</strong><br>";

				$filtername = 'pdfilterownergroup';
				$outputbuffer .= "<div class='box_interactive_list_item" . $showclass . "' id='ownergroupheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'ownergroupheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

				$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE active='yes' ORDER BY name";
				$result= mcq($sql,$db);
				$outputbuffer .= "<option value='all' " . $a . ">" . ("" . htme($lang['group']) . " (" . htme($lang['owner']) . ") [" . htme($lang['all']) . "]") . "</option>";

				while ($UsersArray= mysql_fetch_array($result)) {
					if ($UsersArray['id']== $last_filter[$filtername]) {
							$a = "selected='selected'";
					} else {
							$a = "";
					}

					 $outputbuffer .= "<option value='" . htme($UsersArray['id']) . "' " . $a . ">" . htme($UsersArray['name']) . "</option>";
				}
				$outputbuffer .= "</select>";

			} else {
				$outputbuffer .= "<td class='nwrp th_ownergroup'>" . $link . "";
			}
			$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='ownergroupheadertext' onmouseover=\"SwitchIAtableheader('ownergroupheader" . $random_header_string . "','ownergroupheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('ownergroupheader" . $random_header_string . "','ownergroupheadertext');\">" . $lang['group'] . " (" . $lang['owner'] . ")</div></td>";
			$interimheader .= "<td class='nwrp ti_ownergroup'><strong>" . $link . htme($lang['group']) . " (" . htme($lang['owner']) . ")</strong>";
			unset($link);
		} // end MainList etc ownergroup

		if ($GLOBALS['UC']['MainListColumnsToShow']['cb_assignee']) {
			if ($sort == "assignee" && !$desc) {
				$link = "<a href='#' onclick=\"" . $func . "&sort=assignee&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
			} elseif ($sort == "assignee") {
				$link = "<a href='#' onclick=\"" . $func . "&sort=assignee');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
			} else {
				$link = "<a href='#' onclick=\"" . $func . "&sort=assignee&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
			}
			if ($GLOBALS['ShowSortLink'] =="no") unset($link);

			array_push($FA_header,$lang['assignee']);

			if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES") {
				if ($last_filter['pdfilterassignee'] <> "all" && $last_filter['pdfilterassignee'] <> "") {
					$td = "<td class='nwrp th_assignee highlightedtableheadercell'>";
					$showclass = " show_content";
					$hideclass="hide_content";
				} else {
					$td = "<td class='nwrp th_assignee'>";
					$showclass="";
				$hideclass="";

				}

				$outputbuffer .= $td . $link;
				if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<strong>" . $lang['assignee'] . "</strong><br>";

				$filtername = 'pdfilterassignee';
				$outputbuffer .= "<div class='box_interactive_list_item" . $showclass . "' id='assigneeheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'assigneeheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

				$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE active='yes' AND HIDEFROMASSIGNEEANDOWNERLISTS='n' ORDER BY FULLNAME";
				$result= mcq($sql,$db);
				$outputbuffer .= "<option value='all' " . $a . ">" . (htme($lang['assignee']) . " [" . htme($lang['all']) . "]") . "</option>";

				while ($UsersArray= mysql_fetch_array($result)) {
					if ($UsersArray['id']== $last_filter[$filtername]) {
							$a = "selected='selected'";
					} else {
							$a = "";
					}

					$outputbuffer .= "<option value='" . $UsersArray['id'] . "' " . $a . ">" . htme(GetUserName($UsersArray['id'])) . "</option>";

				}
				$outputbuffer .= "</select>";

			} else {
				$outputbuffer .= "<td class='nwrp th_assignee'>" . $link . "";
			}
				$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='assigneeheadertext' onmouseover=\"SwitchIAtableheader('assigneeheader" . $random_header_string . "','assigneeheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('assigneeheader" . $random_header_string . "','assigneeheadertext');\">" . htme($lang['assignee']) . "</div></td>";
				$interimheader .= "<td class='nwrp ti_assignee'><strong>" . $link . htme($lang['assignee']) . "</strong>";
		} // end mainlistcolumn assignee

		if ($GLOBALS['UC']['MainListColumnsToShow']['cb_assigneegroup']) {

			if ($sort == "assigneegroup" && !$desc) {
				$link = "<a href='#' onclick=\"" . $func . "&sort=assigneegroup&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
			} elseif ($sort == "assigneegroup") {
				$link = "<a href='#' onclick=\"" . $func . "&sort=assigneegroup');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
			} else {
				$link = "<a href='#' onclick=\"" . $func . "&sort=assigneegroup&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
			}
			if ($GLOBALS['ShowSortLink'] =="no") unset($link);

			$interimheader .= "<td class='nwrp ti_assigneegroup'><strong>" . $link . htme($lang['group']) . " (" . htme($lang['assignee']) . ")</strong>";

			array_push($FA_header,"Group (" . $lang['assignee'] . ")");

			// FILTER PULLDOWN assignee
			if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES") {

				if ($last_filter['pdfilterassigneegroup'] <> "all" && $last_filter['pdfilterassigneegroup'] <> "") {
					$td = "<td class='nwrp th_assigneegroup highlightedtableheadercell'>";
					$showclass = " show_content";
					$hideclass="hide_content";
				} else {
					$td = "<td class='nwrp th_assigneegroup'>";
					$showclass="";
				$hideclass="";

				}

				$outputbuffer .= $td . $link;
				if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<strong>" . $lang['group'] . "(" . $lang['assignee'] . ")</strong><br>";

				$filtername = 'pdfilterassigneegroup';
				$outputbuffer .= "<div class='box_interactive_list_item" . $showclass . "' id='assigneegroupheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'assigneegroupheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

				$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE active='yes' ORDER BY name";
				$result= mcq($sql,$db);
				$outputbuffer .= "<option value='all' " . $a . ">" . ("" . htme($lang['group']) . " (" . htme($lang['assignee']) . ") [" . htme($lang['all']) . "]") . "</option>";

				while ($UsersArray= mysql_fetch_array($result)) {
					if ($UsersArray['id']== $last_filter[$filtername]) {
							$a = "selected='selected'";
					} else {
							$a = "";
					}

					 $outputbuffer .= "<option value='" . htme($UsersArray['id']) . "' " . $a . ">" . htme($UsersArray['name']) . "</option>";
				}
				$outputbuffer .= "</select>";

			} else {
				$outputbuffer .= "<td class='nwrp th_assigneegroup'>" . $link . "";
			}
			$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='assigneegroupheadertext' onmouseover=\"SwitchIAtableheader('assigneegroupheader" . $random_header_string . "','assigneegroupheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('assigneegroupheader" . $random_header_string . "','assigneegroupheadertext');\">" . htme($lang['group']) . " (" . htme($lang['assignee']) . ")</div></td>";
			unset($link);

		} // end MainList etc assigneegroup
//} else {
//	$outputbuffer .= "</td>";
//}
	if ($sort == "status" && !$desc) {
		$link = "<a href='#' onclick=\"" . $func . "&sort=status&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
	} elseif ($sort == "status") {
		$link = "<a href='#' onclick=\"" . $func . "&sort=status');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
	} else {
		$link = "<a href='#' onclick=\"" . $func . "&sort=status&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
	}
	if ($GLOBALS['ShowSortLink']=="no") unset($link);

	unset($tmp); // safety first! :)

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_status']) {
		array_push($FA_header,$lang['status']);
		if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES") {
			if ($last_filter['pdfilterstatus'] <> "all" && $last_filter['pdfilterstatus'] <> "") {
				$td = "<td class='nwrp th_status highlightedtableheadercell'>";
				$showclass = " show_content";
					$hideclass="hide_content";
			} else {
				$td = "<td class='nwrp th_status'>";
				$showclass="";
				$hideclass="";

			}

			$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname";
			$result= mcq($sql,$db);

			$outputbuffer .= $td . $link;
			if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<strong>" . htme($lang['status']) . "</strong><br>";

			$filtername = 'pdfilterstatus';
			$outputbuffer .= "<div class='box_interactive_list_item" . $showclass . "' id='statusheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'statusheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . htme($lang['status']) . " [" . htme($lang['all']) . "]</option>";

			while ($result1= mysql_fetch_array($result)) {
						if ($result1['varname']== $last_filter[$filtername]) {
								$a = "selected='selected'";
						} else {
								$a = "";
						}
						if ("NOT" . $result1['varname']== $last_filter[$filtername]) {
								$b = "selected='selected'";
						} else {
								$b = "";
						}
						$outputbuffer .= "<option style='background-color: " . $result1['color'] . ";' value='" . htme($result1['varname']) . "' " . $a . ">" . htme($result1['varname']) . "</option>";
						if ($GLOBALS['DisplayNOToptioninfilters']<>"No") {
							$tmp .= "<option style='background-color: " . $result1['color'] . ";' value='NOT" . htme($result1['varname']) . "' " . $b . ">NOT " . htme($result1['varname']) . "</option>";
						}
				}
			$outputbuffer .= $tmp . "</select>";

			unset($tmp);
		} else {
			$outputbuffer .= "<td class='nwrp th_status'>" . $link . "";
		}
		$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='statusheadertext' onmouseover=\"SwitchIAtableheader('statusheader" . $random_header_string . "','statusheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('statusheader" . $random_header_string . "','statusheadertext');\">" . htme($lang['status']) . "</div></td>";
		$interimheader .= "<td class='nwrp ti_status'><strong>" . $link . htme($lang['status']) . "</strong>";
	}

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_priority']) {

		array_push($FA_header,$lang['priority']);
		if ($sort == "priority" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=priority&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "priority") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=priority');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=priority&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink']=="no") unset($link);

		if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES") {
			if ($last_filter['pdfilterpriority'] <> "all" && $last_filter['pdfilterpriority'] <> "") {
				$td = "<td class='nwrp th_priority highlightedtableheadercell'>";
				$showclass = " show_content";
					$hideclass="hide_content";
			} else {
				$td = "<td class='nwrp th_priority'>";
				$showclass="";
				$hideclass="";

			}

			$sql = "SELECT varname,id,color FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname";
			$result= mcq($sql,$db);

			$outputbuffer .= $td . $link;
			if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<strong>" . $lang['priority'] . "</strong><br>";

			$filtername = 'pdfilterpriority';
			$outputbuffer .= "<div class='box_interactive_list_item" . $showclass . "' id='priorityheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'priorityheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . (htme($lang['priority']) . " [" . $lang['all'] . "]") . "</option>";

			while ($result1= mysql_fetch_array($result)) {
						if ($result1['varname']== $last_filter[$filtername]) {
								$a = "selected='selected'";
						} else {
								$a = "";
						}
						if ("NOT" . $result1['varname']== $last_filter[$filtername]) {
								$b = "selected='selected'";
						} else {
								$b = "";
						}
						$outputbuffer .= "<option style='background-color: " . $result1['color'] . ";' value='" . htme($result1['varname']) . "' " . $a . ">" . htme($result1['varname']) . "</option>";
						if ($GLOBALS['DisplayNOToptioninfilters']<>"No") {
							$tmp .= "<option style='background-color: " . $result1['color'] . ";' value='NOT" . htme($result1['varname']) . "' " . $b . ">NOT " . htme($result1['varname']) . "</option>";
						}
				}
			$outputbuffer .= $tmp . "</select>";

			unset($tmp);
		} else {
			$outputbuffer .= "<td class='nwrp th_priority'>" . $link . "";
		}
		$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='priorityheadertext' onmouseover=\"SwitchIAtableheader('priorityheader" . $random_header_string . "','priorityheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('priorityheader" . $random_header_string . "','priorityheadertext');\">" . $lang['priority'] . "</div></td>";
		$interimheader .= "<td class='nwrp ti_priority'><strong>" . $link . htme($lang['priority']) . "</strong>";
	}

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_category']) {
		array_push($FA_header,$lang['category']);
		if ($sort == "category" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=category&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "category") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=category');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=category&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink']=="no") unset($link);
		$outputbuffer .= "<td class=\"th_category\"><span class='" . $grayedout . "'>" . $link . "" . $lang['category'] . "</span></td>";
		$interimheader .= "<td class=\"ti_category\"><span class='" . $grayedout . "'>" . $link . "<strong>" . $lang['category'] . "</strong></span></td>";
	}



	$ExtraFieldsList = GetExtraFields();

	if (sizeof($ExtraFieldsList)>0 && $ExtraFieldsList[0]<>"") {
		foreach ($ExtraFieldsList AS $field) {

			$element = "EFID" . $field['id'];
			if ($GLOBALS['UC']['MainListColumnsToShow'][$element] && CheckExtraFieldAccess($field['id']) != "nok") {

				$field['name'] = $field['displaylistname'];

				$toshow = $field['displaylistname'];

				$field['optioncolors'] = GetExtraFieldOptioncolors($field['id']);
				if ($last_filter['pdfilterextrafield'][$element] <> "all" && $last_filter['pdfilterextrafield'][$element] <> "") {
					$td = "<td class='nwrp th_EFID" . $field['id'] . " highlightedtableheadercell'>";
					$showclass=" show_content";
			$hideclass=" hide_content ";

				} else {
					$td = "<td class='nwrp th_EFID" . $field['id'] . "'>";
					$showclass="";
					$hideclass="";

				}
				array_push($FA_header,$toshow);
				if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES" && $field['excludefromfilters'] == "n") {
					$VarName = $element;

					if ($sort == "EFID" . $field['id'] && !$desc) {
						$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
					} elseif ($sort == "EFID" . $field['id'] && $desc && ($last_filter['pdfilterextrafield'][$element] == "all" || $last_filter['pdfilterextrafield'][$element] == "")) {
						$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
					} else {
						$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
					}
					if ($GLOBALS['ShowSortLink'] =="no") unset($sorttd);
					$td .= ($sorttd);
					$interimheader .= "<td class=\"nwrp ti_EFID" . $field['id'] . "\">" . $sorttd . " <strong>" . $toshow . "</strong></td>";


					$outputbuffer .= $td;
					if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<strong>" . $toshow . "</strong><br>";

					$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='EFID" . $field['id'] . "header" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $VarName . "' " . $dis . " onblur=\"SetWidth('', this, 'EFID" . $field['id'] . "header" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";


					$outputbuffer .= "<option value='all' " . $a . ">" . ($toshow . " [" . $lang['all'] . "]") . "</option>";

					if ($filter != "viewdel" && !$_REQUEST['From_Summary']) {
						$addtosql = " WHERE deleted!='y'";
					}
					if ($field['fieldtype'] == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {

						$outputbuffer .= ReturnNumericfieldRangeSelectOptions($field['id'], $last_filter['pdfilterextrafield'][$element], $includedeleted);

					} elseif ($field['fieldtype'] == "date" || ($field['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date")) {

						foreach ($datefilter AS $val => $text) {
							if ($last_filter['pdfilterextrafield'][$element] == $val) {
									$a = "selected='selected'";
							} else {
									$a = "";
							}
							$outputbuffer .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
						}

					} else {
						$sql = "SELECT eid, EFID" . $field['id'] . " AS value, result AS CachedResult FROM " . $GLOBALS['TBL_PREFIX'] . "entity LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "accesscache ON ((" . $GLOBALS['TBL_PREFIX'] . "entity.eid=" . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.user=" . $GLOBALS['USERID'] . " AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.type='e' AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.result != 'nok') OR " . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid IS NULL) " . $addtosql . "";
						$result = mcq($sql,$db);
						$CurField = $element;
						$shown = array();
						$values_associative = array();
						while ($row = mysql_fetch_array($result)) {

								if ($last_filter['pdfilterextrafield'][$CurField] == $row['value']) {
										$a = "selected='selected'";
								} else {
										$a = "";
								}
								if (!in_array($row['value'], $shown) && $row['value'] != "") {
									$ok = false;
									if ($row['CachedResult'] == "") {
										if (CheckEntityAccess($row['eid']) != "nok") {
											$ok = true;
										}
									} else {
										$ok = true;
									}
									if ($ok) {
										$value = GetExtraFieldValue($row['eid'], $field['id'], true, false, $row['value']);
										$values_associative[$value] .= "<option " . $a . " value='" . htme(urlencode($row['value'])) . "'>" . htme($value) . "</option>";
										array_push($shown, $row['value']);

									}
								}
						}
						ksort($values_associative);

						foreach ($values_associative as $key => $val)
						{
							$outputbuffer .= $val;
						}
					} // end if !date


					$outputbuffer .= "<option value=''>-----------</option>";

					if ($last_filter['pdfilterextrafield'][$element] == "@UNIQUE@") {
							$a = "selected='selected'";
					} else {
							$a = "";
					}

					$outputbuffer .= "<option " . $a . " value='@UNIQUE@'>Unique values</option>";
					if ($last_filter['pdfilterextrafield'][$element] == "@NOTUNIQUE@") {
							$a = "selected='selected'";
					} else {
							$a = "";
					}
					$outputbuffer .= "<option " . $a . " value='@NOTUNIQUE@'>Double values (non-unique)</option>";
					$outputbuffer .= "</select>";

				} else {
					$interimheader .= "<td class=\"nwrp ti_EFID" . $field['id'] . " nrwp\">" . $sorttd . " <strong>" . $toshow . "</strong></td>";
					$outputbuffer .= "<td class=\"nwrp th_EFID" . $field['id'] . " nwrp\">";
					if ($GLOBALS['ShowSortLink'] !="no") {
					if ($sort == "EFID" . $field['id'] && !$desc && $GLOBALS['ShowSortLink'] !="no") {
						$outputbuffer .= "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
					} elseif ($sort == "EFID" . $field['id'] && !$desc && $GLOBALS['ShowSortLink'] !="no") {
						$outputbuffer .= "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
					} elseif ($GLOBALS['ShowSortLink'] !="no") {
						$outputbuffer .= "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
					}
					}
				}
				$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='EFID" . $field['id'] . "headertext' onmouseover=\"SwitchIAtableheader('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\">" . htme($field['name']) . "</div></td>";
			}
		}
	}

	$showclass="";
	$hideclass="";


	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact']) {
			$outputbuffer .= "<td class=\"nwrp th_customercontact\"><span style='color: #3366CC'><strong>" . $lang['contact'] . "</strong></span></td>";
			$interimheader .= "<td class=\"nwrp ti_customercontact\"><span style='color: #3366CC'><strong>" . $lang['contact'] . "</strong></span></td>";
			array_push($FA_header,$lang['contact']);
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact_title']) {
			$outputbuffer .= "<td class=\"nwrp th_contact_title\"><span style='color: #3366CC'><strong>" . $lang['contacttitle'] . "</strong></span></td>";
			$interimheader .= "<td class=\"nwrp ti_contact_title\"><span style='color: #3366CC'><strong>" . $lang['contacttitle'] . "</strong></span></td>";
			array_push($FA_header,$lang['contacttitle']);
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact_phone']) {
			$outputbuffer .= "<td class=\"nwrp th_contact_phone\"><span style='color: #3366CC'><strong>" . $lang['contactphone'] . "</strong></span></td>";
			$interimheader .= "<td class=\"nwrp ti_contact_phone\"><span style='color: #3366CC'><strong>" . $lang['contactphone'] . "</strong></span></td>";
			array_push($FA_header,$lang['contactphone']);
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact_email']) {
			$outputbuffer .= "<td class=\"nwrp th_contact_email\"><span style='color: #3366CC'><strong>" . $lang['contactemail'] . "</strong></span></td>";
			$interimheader .= "<td class=\"nwrp ti_contact_email\"><span style='color: #3366CC'><strong>" . $lang['contactemail'] . "</strong></span></td>";
			array_push($FA_header,$lang['contactemail']);
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust_address']) {
			$outputbuffer .= "<td class=\"th_cust_address\"><span style='color: #3366CC'><strong>" . $lang['customeraddress'] . "</strong></span></td>";
			$interimheader .= "<td class=\"ti_cust_address\"><span style='color: #3366CC'><strong>" . $lang['customeraddress'] . "</strong></span></td>";
			array_push($FA_header,$lang['customeraddress']);
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust_remarks']) {
			$outputbuffer .= "<td class=\"th_cust_remarks\"><span style='color: #3366CC'><strong>" . $lang['custremarks'] . "</strong></span></td>";
			$interimheader .= "<td class=\"ti_cust_remarks\"><span style='color: #3366CC'><strong>" . $lang['custremarks'] . "</strong></span></td>";

			array_push($FA_header,$lang['custremarks']);
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust_homepage']) {
			$outputbuffer .= "<td class=\"th_cust_homepage\"><span style='color: #3366CC'><strong>" . $lang['custhomepage'] . "</strong></span></td>";
			$interimheader .= "<td class=\"ti_cust_homepage\"><span style='color: #3366CC'><strong>" . $lang['custhomepage'] . "</strong></span></td>";
			array_push($FA_header,$lang['custhomepage']);
	}
	$ExtraFieldsList = GetExtraCustomerFields();

	if (sizeof($ExtraFieldsList)>0 && $ExtraFieldsList[0]<>"") {
		foreach ($ExtraFieldsList AS $field) {
			$field['name'] = $field['displaylistname'];
			$element = "EFID" . $field['id'];
			if ($GLOBALS['UC']['MainListColumnsToShow'][$element] && CheckExtraFieldAccess($field['id']) != "nok") {

				$field['optioncolors'] = GetExtraFieldOptioncolors($field['id']);

				$toshow = CleanExtraFieldName($field['name']);

				if ($last_filter['pdfilterextrafield'][$element] <> "all" && $last_filter['pdfilterextrafield'][$element] <> "") {
					$td = "<td class=\"nwrp " . $insclass . " highlightedtableheadercell th_EFID" . $field['id'] . "\"><strong>";
					$showclass = " show_content";
					$hideclass="hide_content";
				} else {
					$td = "<td class=\"nwrp " . $insclass . " th_EFID" . $field['id'] . "\">";
					$showclass="";
					$hideclass="";

				}
				if ($sort == "EFID" . $field['id'] && !$desc && ($last_filter['pdfilterextrafield'][$element] == "all" || $last_filter['pdfilterextrafield'][$element] == "")) {
					$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
				} elseif ($sort == "EFID" . $field['id'] && ($last_filter['pdfilterextrafield'][$element] == "all" || $last_filter['pdfilterextrafield'][$element] == "")) {
					$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
				} else {
					$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
				}
				if ($GLOBALS['ShowSortLink'] =="no") unset($sorttd);

				$td .= $sorttd;

				$interimheader .= "<td class=\"nwrp ti_EFID" . $field['id'] . "\"'>" . $sorttd . " <span class='" . $grayedout . "'><strong>" . $field['name'] . "</strong></span></td>";

				$outputbuffer .= $td;

				if ($GLOBALS['PrintTableHeaders']) $outputbuffer .= "<span class='" . $grayedout . "'><strong>" . $toshow . "</strong></span><br>";

				array_push($FA_header,$toshow);
				if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES" && $field['excludefromfilters'] == "n") {
					$VarName = $element;

					$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='EFID" . $field['id'] . "header" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $VarName . "' " . $dis . " onblur=\"SetWidth('', this, 'EFID" . $field['id'] . "header" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

					$outputbuffer .= "<option value='all' " . $a . ">" . ($toshow . " [" . $lang['all'] . "]") . "</option>";

					//if (strlen($toshow)>10) {
					//	$outputbuffer .= "<option value='all' " . $a . ">[" . $toshow . "] [" . $lang['all'] . "]</option>";
					//}

					if ($field['fieldtype'] == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
						$outputbuffer .= ReturnNumericfieldRangeSelectOptions($field['id'], $last_filter['pdfilterextrafield'][$element], $includedeleted);
					} elseif ($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || ($field['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date")) {
						foreach ($datefilter AS $val => $text) {
							if ($last_filter['pdfilterextrafield'][$element] == $val) {
									$a = "selected='selected'";
							} else {
									$a = "";
							}
							$outputbuffer .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
						}
					} else {
						$sql = "SELECT DISTINCT(EFID" . $field['id'] . ") AS value FROM " . $GLOBALS['TBL_PREFIX'] . "customer ORDER BY EFID" . $field['id'] . "";
						$result = mcq($sql,$db);

						$CurField = $element;
						while ($result1= mysql_fetch_array($result)) {
								if (trim($result1['value']) <> "") {
									if ($last_filter['pdfilterextrafield'][$CurField] == $result1['value']) {
											$a = "selected='selected'";
									} else {
											$a = "";
									}

									if ($field['fieldtype'] == "User-list of all CRM-CTT users" || $field['fieldtype'] == "User-list of limited CRM-CTT users" || $field['fieldtype'] == "User-list of administrative CRM-CTT users" || substr($field['fieldtype'],0,16) == "Users of profile") {
										$outputbuffer .= "<option value='" . $result1['value'] . "' " . $a . ">" . GetUserName($result1['value']) . "</option>";
									} elseif ($field['fieldtype'] == "List of all groups") {
										$outputbuffer .= "<option value='" . $result1['value'] . "' " . $a . ">" . GetGroupName($result1['value']) . "</option>";
									} elseif ($field['fieldtype'] == "date") {
										$outputbuffer .= "<option value='" . $result1['value'] . "' " . $a . ">" . TransformDate($result1['value']) . "</option>";
									} elseif ($field['fieldtype'] == "date/time") {
										$outputbuffer .= "<option value='" . $result1['value'] . "' " . $a . ">" . SQLDateTimeToFormattedDateTime($result1['value']) . "</option>";
									} elseif ($field['fieldtype'] == "Booking calendar") {

									} elseif ($field['fieldtype'] == "drop-down") {
										if ($field['optioncolors'][$result1['value']]) {
											$outputbuffer .= "<option style='background-color: " . $field['optioncolors'][$result1['value']] . ";' value='" . htme($result1['value']) . "' " . $a . ">" . htme($result1['value']) . "</option>";
										} else {
											$outputbuffer .= "<option value='" . htme($result1['value']) . "' " . $a . ">" . htme($result1['value']) . "</option>";
										}
									} else {
										$outputbuffer .= "<option value='" . htme($result1['value']) . "' " . $a . ">" . $result1['value'] . "</option>";
									}
								}
							}
					} // else if ! date

					/*
					$outputbuffer .= "<option value=''>-----------</option>";
					if ($last_filter['pdfilterextrafield'][$CurField] == "@UNIQUE@") {
							$a = "selected='selected'";
					} else {
							$a = "";
					}

					$outputbuffer .= "<option " . $a . " value='@UNIQUE@'>Unique values</option>";

					if ($last_filter['pdfilterextrafield'][$CurField] == "@NOTUNIQUE@") {
							$a = "selected='selected'";

					} else {
							$a = "";
					}
					$outputbuffer .= "<option " . $a . " value='@NOTUNIQUE@'>Double values (non-unique)</option>";
					*/
					$outputbuffer .= "</select>";

				} else {

				}
				$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='EFID" . $field['id'] . "headertext' onmouseover=\"SwitchIAtableheader('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\">" . htme($field['name']) . "</div></td>";
			}
		}
	}
	$showclass="";
				$hideclass="";




	$fts = GetFlexTableDefinitions(false,"many-to-one", false, "entity");
	foreach ($fts AS $ft) {
		$list = GetExtraFlexTableFields($ft['recordid'], false, false);
		foreach ($list AS $field) {
			$varname = "EFID" . $field['id'];

			if ($GLOBALS['UC']['MainListColumnsToShow'][$varname]) {
				// START

				if ($last_filter['pdfilterextrafield'][$varname] <> "all" && $last_filter['pdfilterextrafield'][$varname] <> "") {
					$td = "<td class=\"nwrp " . $insclass . " highlightedtableheadercell th_EFID" . $field['id'] . "\"><strong>";
					$showclass = " show_content";
					$hideclass = " hide_content";
				} else {
					$td = "<td class=\"nwrp " . $insclass . " th_EFID" . $field['id'] . "\">";
					$showclass="";
					$hideclass="";

				}

				if ($sort == "EFID" . $field['id'] && !$desc && ($last_filter['pdfilterextrafield'][$element] == "all" || $last_filter['pdfilterextrafield'][$element] == "")) {
					$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
				} elseif ($sort == "EFID" . $field['id'] && ($last_filter['pdfilterextrafield'][$element] == "all" || $last_filter['pdfilterextrafield'][$element] == "")) {
					$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
				} else {
					$sorttd = "<a href='#' onclick=\"" . $func . "&sort=EFID" . $field['id'] . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
				}
				if ($GLOBALS['ShowSortLink'] =="no") unset($sorttd);
				$td .= $sorttd;

				$outputbuffer .= $td;

				if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES"  && $field['excludefromfilters'] == "n") {

					$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='EFID" . $field['id'] . "header" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $varname . "' " . $dis . " onblur=\"SetWidth('', this, 'EFID" . $field['id'] . "header" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

					$outputbuffer .= "<option value='all' " . $a . ">" . ($field['displaylistname'] . " [" . $lang['all'] . "]") . "</option>";
					// foreach loop through options
					

					$tmp = db_GetArray("SELECT DISTINCT(" . $varname . "),recordid FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE deleted!='y' AND " . $varname . "!=''");
					foreach ($tmp AS $opt) {
						if ($last_filter['pdfilterextrafield'][$varname] == $opt[0]) {
								$a = "selected='selected'";
						} else {
								$a = "";
						}
						$ts = GetExtraFieldValue(false, str_replace("EFID", "", $varname), false, false, $opt[0]);
						if (trim($ts) == "") $ts = $opt[0];
						$outputbuffer .= "<option " . $a . " value='" . htme($opt[0]) . "'> " . htme($ts) . "</option>";
					}
					$outputbuffer .= "<option value=''>-----------</option>";
					if ($last_filter['pdfilterextrafield'][$varname] == "@UNIQUE@") {
							$a = "selected='selected'";
					} else {
							$a = "";
					}

					$outputbuffer .= "<option " . $a . " value='@UNIQUE@'>Unique values</option>";
					if ($last_filter['pdfilterextrafield'][$varname] == "@NOTUNIQUE@") {
							$a = "selected='selected'";
					} else {
							$a = "";
					}
					$outputbuffer .= "<option " . $a . " value='@NOTUNIQUE@'>Double values (non-unique)</option>";
					$outputbuffer .= "</select>";

				} else {

				}
				// END

				$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='EFID" . $field['id'] . "headertext' onmouseover=\"SwitchIAtableheader('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('EFID" . $field['id'] . "header" . $random_header_string . "',,'EFID" . $field['id'] . "headertext');\">" . htme($field['displaylistname']) . "</div> </td>";

				$interimheader .= "<td class=\"ti_EFID" . $field['id'] . "\"><strong>" . $field['displaylistname'] . "</strong></td>";
				array_push($FA_header,"" . $field['name'] . "");
			}
		}
	}
	$fts = GetFlexTableDefinitions(false,"one-to-many", false, "entity");
	foreach ($fts AS $ft) {
		$sumshown = "";
		$list = GetExtraFlexTableFields($ft['recordid'], false, false);
		foreach ($list AS $field) {
			$varname = "EFID" . $field['id'];

			if ($GLOBALS['UC']['MainListColumnsToShow'][$varname] || $GLOBALS['UC']['MainListColumnsToShow']["SUM" . $varname]) {


				if ($GLOBALS['UC']['MainListColumnsToShow']["SUM" . $varname]) {
					$sum = true;
					$varname_ins = "SUM";
					$desc_ins1 = "SUM(";
					$desc_ins2 = ")";

				} else {
					$sum = false;
					$varname_ins = "";
					$desc_ins1 = "";
					$desc_ins2 = "";
				}

				// START
				if ($last_filter['pdfilterextrafield'][$varname_ins . $varname] <> "all" && $last_filter['pdfilterextrafield'][$varname_ins . $varname] <> "") {
					$td = "<td class=\"nwrp " . $insclass . " th_EFID" . $field['id'] . "\"><strong>";
					$showclass = " show_content";
					$hideclass="hide_content";
				} else {
					$td = "<td class=\"nwrp " . $insclass . " th_EFID" . $field['id'] . "\">";
					$showclass="";
				$hideclass="";

				}

				$outputbuffer .= $td;


				if (strtoupper($GLOBALS['ShowFilterInMainList'])=="YES"  && $field['excludefromfilters'] == "n") {


					$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='EFID" . $field['id'] . "header" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $varname_ins . $varname . "' " . $dis . " onblur=\"SetWidth('', this, 'EFID" . $field['id'] . "header" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

					$outputbuffer .= "<option value='all' " . $a . ">" . $desc_ins1 . $field['displaylistname'] . $desc_ins2 . " [" . $lang['all'] . "]" . "</option>";
					// foreach loop through options

					if ($sum) {
						$tmp = db_GetFlatArray("SELECT DISTINCT(SUM(" . $varname . ")) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE deleted!='y' AND " . $varname . "!='' GROUP BY refer");
					} else {
						$tmp = db_GetFlatArray("SELECT DISTINCT(" . $varname . ") FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE deleted!='y' AND " . $varname . "!='' ORDER BY " . $varname . "");
					}

					foreach ($tmp AS $opt) {
						if ($last_filter['pdfilterextrafield'][$varname_ins . $varname] == $opt) {
								$a = "selected='selected'";
						} else {
								$a = "";
						}
						$outputbuffer .= "<option " . $a . " value='" . htme($opt) . "'>" . htme($opt) . "</option>";
					}

					$outputbuffer .= "</select>";

				} else {

				}
				// END

				$outputbuffer .= "</div><div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='EFID" . $field['id'] . "headertext' onmouseover=\"SwitchIAtableheader('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\">" . $desc_ins1 . htme($field['displaylistname']) . $desc_ins2 . "</div> </td>";

				$interimheader .= "<td class=\"ti_EFID" . $field['id'] . "\"><strong>" . $desc_ins1 . $field['displaylistname'] . $desc_ins2 . "</strong></td>";
				array_push($FA_header,"" . $field['name'] . "");
			}
		}
	}


	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_numofattachments']) {
		$outputbuffer .="<td class=\"th_num_attm\"><strong><img src='images/attach.gif' alt=''></strong></td>";
		$interimheader .= "<td class=\"ti_num_attm\"><strong><img src='images/attach.gif' alt=''></strong></td>";
		array_push($FA_header,"<img src='images/attach.gif' alt=''>");
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_startdate']) {

		if ($sort == "sqlstartdate" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=sqlstartdate&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "sqlstartdate") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=sqlstartdate');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=sqlstartdate&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink']=="no") unset($link);

		if ($last_filter['pdfilterstartdate'] <> "all" && $last_filter['pdfilterstartdate'] <> "") {
			$outputbuffer .= "<td class=\"nwrp highlightedtableheadercell th_startdate\">" . $link;
		} else {
			$outputbuffer .= "<td class=\"nwrp th_startdate\">" . $link;
		}



		if ($GLOBALS['ShowFilterInMainList'] == "Yes") {
			$filtername = 'pdfilterstartdate';
			$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='startdateheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'startdateheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . ($lang['startdate'] . " [" . $lang['all'] . "]") . "</option>";

			foreach ($datefilter AS $val => $text) {
				if ($last_filter[$filtername] == $val) {
						$a = "selected='selected'";
				} else {
						$a = "";
				}
				$outputbuffer .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
			}
			$outputbuffer .= "</select></div>";


		} else {

		}


		$outputbuffer .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='startdateheadertext' onmouseover=\"SwitchIAtableheader('startdateheader" . $random_header_string . "','EFID" . $field['id'] . "headertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('startdateheader" . $random_header_string . "','EFID" . $field['id'] . "headertext');\">" . htme($lang['startdate']) . "</div></td>";

		$interimheader .= "<td class=\"nwrp ti_startdate\">" . $link . " <strong>" . $lang['startdate'] . "</strong></td>";
		array_push($FA_header,$lang['startdate']);
	}

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_duedate']) {

		if ($sort == "duedate" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=duedate&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "duedate") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=duedate');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=duedate&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink']=="no") unset($link);

		if ($last_filter['pdfilterduedate'] <> "all" && $last_filter['pdfilterduedate'] <> "") {
			$outputbuffer .= "<td class=\"nwrp highlightedtableheadercell th_duedate\">" . $link;
			$showclass = " show_content";
					$hideclass="hide_content";
		} else {
			$outputbuffer .= "<td class=\"nwrp th_duedate\">" . $link;
			$showclass = "";
		}



		if ($GLOBALS['ShowFilterInMainList'] == "Yes") {
			$filtername = 'pdfilterduedate';
			$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='duedateheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'duedateheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . ($lang['duedate'] . " [" . $lang['all'] . "]") . "</option>";

			foreach ($datefilter AS $val => $text) {
				if ($last_filter[$filtername] == $val) {
						$a = "selected='selected'";
				} else {
						$a = "";
				}
				$outputbuffer .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
			}
			$outputbuffer .= "</select></div>";

		} else {
		}
		$interimheader .= "<td class=\"ti_duedate\"><strong>" . $lang['duedate'] . "</strong></td>";

		$outputbuffer .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='duedateheadertext' onmouseover=\"SwitchIAtableheader('duedateheader" . $random_header_string . "', 'duedateheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('duedateheader" . $random_header_string . "','duedateheadertext');\">" . htme($lang['duedate']) . "</div></td>";
		array_push($FA_header,$lang['duedate']);
	}
	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_lastupdate']) {

		if ($sort == "lastupdate" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=lastupdate&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "lastupdate") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=lastupdate');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=lastupdate&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink']=="no") unset($link);



		if ($GLOBALS['ShowFilterInMainList'] == "Yes") {
			if ($last_filter['pdfilterlastupdate'] <> "all" && $last_filter['pdfilterlastupdate'] <> "") {
				$outputbuffer .= "<td class=\"nwrp highlightedtableheadercell th_lastupdate\">" . $link;
				$showclass = " show_content";
				$hideclass = " hide_content";
			} else {
				$outputbuffer .= "<td class=\"nwrp th_lastupdate\">" . $link;
				$showclass = "";
			}

			$filtername = 'pdfilterlastupdate';
			$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='lastupdateheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'lastupdateheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . ($lang['lastupdate'] . " [" . $lang['all'] . "]") . "</option>";

			foreach ($datefilter AS $val => $text) {
				if ($last_filter[$filtername] == $val) {
						$a = "selected='selected'";
				} else {
						$a = "";
				}
				$outputbuffer .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
			}
			$outputbuffer .= "</select></div>";
			$outputbuffer .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='lastupdateheadertext' onmouseover=\"SwitchIAtableheader('lastupdateheader" . $random_header_string . "',
		'lastupdateheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('lastupdateheader" . $random_header_string . "','lastupdateheadertext');\">" . htme($lang['lastupdate']) . "</div></td>";
		} else {
			$outputbuffer .= "<td class=\"th_lastupdate nwrp\"><strong>" . $link . $lang['lastupdate'] . "</a></strong></td>";
		}


		$interimheader .= "<td class=\"ti_lastupdate nrwp\">" . $link . "<strong>" . $lang['lastupdate'] . "</strong></td>";
		array_push($FA_header,$lang['lastupdate']);
	}

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_creationdate']) {
		if ($sort == "openepoch" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=openepoch&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "openepoch") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=openepoch');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=openepoch&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}

		if ($GLOBALS['ShowSortLink']=="no") unset($link);



		if ($GLOBALS['ShowFilterInMainList'] == "Yes") {
			if ($last_filter['pdfiltercreationdate'] <> "all" && $last_filter['pdfiltercreationdate'] <> "") {
				$outputbuffer .= "<td class=\"nwrp highlightedtableheadercell th_creationdate\">" . $link;
				$showclass = " show_content";
				$hideclass = " hide_content";
			} else {
				$outputbuffer .= "<td class=\"nwrp th_creationdate\">" . $link;
				$showclass = "";
			}

			$filtername = 'pdfiltercreationdate';
			$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='creationdateheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'creationdateheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . ($lang['creationdate'] . " [" . $lang['all'] . "]") . "</option>";

			foreach ($datefilter AS $val => $text) {
				if ($last_filter[$filtername] == $val) {
						$a = "selected='selected'";
				} else {
						$a = "";
				}
				$outputbuffer .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
			}
			$outputbuffer .= "</select></div>";
			$outputbuffer .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='creationdateheadertext' onmouseover=\"SwitchIAtableheader('creationdateheader" . $random_header_string . "',
		'creationdateheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('creationdateheader" . $random_header_string . "','creationdateheadertext');\">" . htme($lang['creationdate']) . "</div></td>";

		} else {
			$outputbuffer .= "<td class=\"nwrp th_creationdate\"><strong>" . $link . $lang['creationdate'] . "</a></strong></td>";
		}

		$interimheader .= "<td class=\"ti_creationdate nwrp\"><strong>" . $link . $lang['creationdate'] . "</a></strong></td>";
		array_push($FA_header,$lang['creationdate']);
	}

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_closedate']) {
		if ($sort == "closeepoch" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=closeepoch&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "closeepoch") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=closeepoch');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=closeepoch&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink']=="no") unset($link);
			if ($GLOBALS['ShowFilterInMainList'] == "Yes") {
			if ($last_filter['pdfilterclosedate'] <> "all" && $last_filter['pdfilterclosedate'] <> "") {
				$outputbuffer .= "<td class=\"nwrp highlightedtableheadercell th_closedate\">" . $link;
				$showclass = " show_content";
				$hideclass = " hide_content";
			} else {
				$outputbuffer .= "<td class=\"nwrp th_closedate\">" . $link;
				$showclass = "";
			}

			$filtername = 'pdfilterclosedate';
			$outputbuffer .= "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='closedateheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='" . $filtername . "' " . $dis . " onblur=\"SetWidth('', this, 'closedateheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

			$outputbuffer .= "<option value='all' " . $a . ">" . ($lang['closedate'] . " [" . $lang['all'] . "]") . "</option>";

			foreach ($datefilter AS $val => $text) {
				if ($last_filter[$filtername] == $val) {
						$a = "selected='selected'";
				} else {
						$a = "";
				}
				$outputbuffer .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
			}
			$outputbuffer .= "</select></div>";
			$outputbuffer .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='closedateheadertext' onmouseover=\"SwitchIAtableheader('closedateheader" . $random_header_string . "',
		'closedateheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('closedateheader" . $random_header_string . "','closedateheadertext');\">" . htme($lang['closedate']) . "</div></td>";

		} else {
			$outputbuffer .= "<td class=\"nwrp th_closedate\"><strong>" . $link . $lang['closedate'] . "</a></strong></td>";
		}

		$interimheader .= "<td class=\"ti_closedate nwrp\"><strong>" . $link . $lang['closedate'] . "</a></strong></td>";
		array_push($FA_header,$lang['closedate']);
	}

	if ($GLOBALS['UC']['MainListColumnsToShow']['cb_duration']) {
		if ($sort == "duration" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "&sort=duration&amp;desc=1');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "duration") {
			$link = "<a href='#' onclick=\"" . $func . "&sort=duration');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "&sort=duration&amp;desc=0');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink']=="no") unset($link);
		if ($GLOBALS['From_Summary']) {
				$outputbuffer .= "<td class=\"th_age_duration\ nwrp\"><strong>" . $link . "Age/duration</strong></td>";
				$interimheader .= "<td class=\"ti_age_duration\ nwrp\"><strong>" . $link . "Age/duration</strong></td>";
				array_push($FA_header,"Age/duration");
		} else {
			if ($filter=="viewdel") {
					$outputbuffer .= "<td class=\"th_age_duration\ nwrp\"><strong>" . $link . "Duration</strong></td>";
					$interimheader .= "<td class=\"ti_age_duration\ nwrp\"><strong>" . $link . "Duration</strong></td>";
					array_push($FA_header,"Duration");
			} else {
					$outputbuffer .= "<td class=\"th_age_duration\ nwrp\"><strong>" . $link . "Age/duration</strong></td>";
					$interimheader .= "<td class=\"ti_age_duration\ nwrp\"><strong>" . $link . "Age/duration</strong></td>";
					array_push($FA_header,"Age");
			}
		}
	}

	if ($filter=="custinsert") {
		$outputbuffer .= "<td></td>";
	}

	foreach (GetButtons() AS $button) {
		if (GetAttribute("extrafield", "ShowButtonInList", $button['id']) == "Yes") {
			if ($button['displaylistname'] != "") $button['name'] = $button['displaylistname'];
			$outputbuffer .= "<td class=\"th_EFID" . $button['id'] . "\">" . htme($button['name']) . "</td>";
			$interimheader .= "<td class=\"ti_EFID" . $button['id'] . "\">" . htme($button['name']) . "</td>";
		}
	}
// end of main header row
	if (GetSetting("MASS_UPDATE") == "Yes" && !in_array("NoMassUpdate",GetClearanceLevel($GLOBALS['USERID'])) && !$nofunctions) {
		$outputbuffer .= "<td class=\"th_massupdate\"><input type=\"checkbox\" class=\"checkall\"></td>";
		$interimheader .= "<td class=\"ti_massupdate\"></td>";
	}

	$outputbuffer .= "</tr></thead>";

	// tot hier header

	$sort = $sort_on;
	if (!$sort && !$dontremembersort) {
		$sort = "" . $GLOBALS['TBL_PREFIX'] . "entity.sqldate," . $GLOBALS['TBL_PREFIX'] . "entity.status," . $GLOBALS['TBL_PREFIX'] . "entity.priority";
	} elseif ($sort=="duration") {
		if ($filter=="viewdel") {
			$sort = " (" . $GLOBALS['TBL_PREFIX'] . "entity.closeepoch-" . $GLOBALS['TBL_PREFIX'] . "entity.openepoch)";
		} else {
			$sort = " " . $GLOBALS['TBL_PREFIX'] . "entity.openepoch";
		}
	}
	if ($desc) {
			$sort .= " DESC";
	}
	$and_sql_ins = "";
	if (is_array($GLOBALS['UC']['LIMITTOCUSTOMERS'])) {
		$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer IN (";
		foreach($GLOBALS['UC']['LIMITTOCUSTOMERS'] AS $cid) {
			if ($fst) {
				$and_sql_ins .= ",";
			}
			$and_sql_ins .= $cid;
			$fst = true;
		}
		$and_sql_ins .= ")";
	}

	if ($_REQUEST['filter'] == "viewdel") {
		$deleted_value = " AND " . $GLOBALS['TBL_PREFIX'] . "entity.deleted='y' ";
	} elseif ($includedeleted) {
		// nothing
	} elseif (!$given_query) {
		$deleted_value = " AND " . $GLOBALS['TBL_PREFIX'] . "entity.deleted='n' ";
	}

	$tmp = GetLastUserFilter();
	if (is_array($tmp['datefilter']) && $GLOBALS['From_Summary']) {
		$aq = CreateSQLFromDateFilterArray($tmp['datefilter']);
		if ($aq != "") {
			$and_sql_ins .= " AND " . $aq;
			$date_filter_active = true;
		}
	}

	if (1==1) {
		if ($GLOBALS['EnableEntityRelations'] == "Yes" && $GLOBALS['HideChildsFromMainList'] == "Yes") {
			$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.parent=0 ";
			qlog(INFO, "EnableEntityRelations is set - not showing childs!");
		}
		if ($last_filter['pdfiltercustomer'] && $last_filter['pdfiltercustomer']<>"all") {
			$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer='" . mres($last_filter['pdfiltercustomer']) . "'";
			$filter_active = true;
		}
		if ($last_filter['pdfilterstatus'] && $last_filter['pdfilterstatus']<>"all") {
			$filter_active = true;
			if (substr($last_filter['pdfilterstatus'],0,3)=="NOT") {
				qlog(INFO, "LNOT in function!");
				$last_filter['pdfilterstatus'] = substr($last_filter['pdfilterstatus'],3,strlen($last_filter['pdfilterstatus'])-3);
				qlog(INFO, "LEFTOVER: " . $last_filter['pdfilterstatus']);
				$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.status<>'" . mres($last_filter['pdfilterstatus']) . "'";
			} else {
				$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.status='" . mres($last_filter['pdfilterstatus']) . "'";
			}
		}
		if ($last_filter['pdfilterpriority'] && $last_filter['pdfilterpriority']<>"all") {
			$filter_active = true;
			if (substr($last_filter['pdfilterpriority'],0,3)=="NOT") {
				$last_filter['pdfilterpriority'] = substr($last_filter['pdfilterpriority'],3,strlen($last_filter['pdfilterpriority'])-3);
				$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.priority<>'" . mres($last_filter['pdfilterpriority']) . "'";
			} else {
				$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.priority='" . mres($last_filter['pdfilterpriority']) . "'";
			}
		}
		// Owner and assignee
		if ($last_filter['pdfilterowner'] && $last_filter['pdfilterowner']<>"all") {
			$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.owner='" . mres($last_filter['pdfilterowner']) . "'";
			$filter_active = true;
		}
		if ($last_filter['pdfilterassignee'] && $last_filter['pdfilterassignee']<>"all") {
			$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.assignee='" . mres($last_filter['pdfilterassignee']) . "'";
			$filter_active = true;
		}
		// Owner and assignee GROUPS
		if ($last_filter['pdfilterownergroup'] && $last_filter['pdfilterownergroup']<>"all") {
			$and_sql_ins .= " AND uj2.PROFILE='" . mres($last_filter['pdfilterownergroup']) . "' AND uj2.PROFILE != ''";
			$filter_active = true;
		}
		if ($last_filter['pdfilterassigneegroup'] && $last_filter['pdfilterassigneegroup']<>"all") {
			$and_sql_ins .= " AND uj1.PROFILE='" . mres($last_filter['pdfilterassigneegroup']) . "' AND uj1.PROFILE != ''";
			$filter_active = true;
		}

		if ($last_filter['pdfilterstartdate'] && $last_filter['pdfilterstartdate']<>"all") {
			$and_sql_ins .= " " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "entity.startdate", $last_filter['pdfilterstartdate']);
			$filter_active = true;
		}
		if ($last_filter['pdfilterduedate'] && $last_filter['pdfilterduedate']<>"all") {
			$and_sql_ins .= " " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "entity.duedate", $last_filter['pdfilterduedate']);
			$filter_active = true;
		}
		if ($last_filter['pdfilterlastupdate'] && $last_filter['pdfilterlastupdate']<>"all") {
			$and_sql_ins .= " " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "entity.timestamp_last_change", $last_filter['pdfilterlastupdate']);
			$filter_active = true;
		}
		if ($last_filter['pdfiltercreationdate'] && $last_filter['pdfiltercreationdate']<>"all") {
			$and_sql_ins .= " " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "entity.cdate", $last_filter['pdfiltercreationdate']);
			$filter_active = true;
		}
		if ($last_filter['pdfilterclosedate'] && $last_filter['pdfilterclosedate']<>"all") {
			$and_sql_ins .= " " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "entity.closedate", $last_filter['pdfilterclosedate']);
			$filter_active = true;
		}
		if ($filter_id == "custinsert") {
			$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.owner='2147483647' AND " . $GLOBALS['TBL_PREFIX'] . "entity.assignee='2147483647'";
		} elseif (!$given_query) {
			$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "entity.owner<>'2147483647' AND " . $GLOBALS['TBL_PREFIX'] . "entity.assignee<>'2147483647'";
		}


		$joins = " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "accesscache ON ((" . $GLOBALS['TBL_PREFIX'] . "entity.eid=" . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.user=" . $GLOBALS['USERID'] . " AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.type='e' AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.result != 'nok') OR " . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid IS NULL)";
		$ftjoinsdone = array();
		$joincounter=100;
		// Flextable filters
		$tmp = GetFlextableDefinitions();
		foreach ($tmp AS $ft) {
			if ($ft['refers_to'] == "entity") {
				$efl = GetExtraFlextableFields($ft[0]);
				foreach($efl as $field) {
						$element = "EFID" . $field['id'];

						if ($ft['orientation'] == "one_entity_to_many") {

							if (!in_array($ft['recordid'], $ftjoinsdone)) {
								$joincounter++;
								$joins .= " LEFT JOIN " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " AS ftjoin" . $ft['recordid'] . $joincounter . " ON (" . $GLOBALS['TBL_PREFIX'] . "entity.eid=ftjoin" . $ft['recordid'] . $joincounter . ".refer)";
								array_push($ftjoinsdone, $ft['recordid']);
							}

							if ($last_filter['pdfilterextrafield'][$element] != "") {
								$and_sql_ins .= " AND ftjoin" . $ft['recordid'] . $joincounter . "." . $element . "='" . mres($last_filter['pdfilterextrafield'][$element]) . "' AND ftjoin" . $ft['recordid'] . $joincounter . ".deleted!='y'";
								$filter_active = true;
							} elseif ($last_filter['pdfilterextrafield']["SUM" . $element] != "") {
								$and_sql_ins .= " AND eid IN (SELECT refer FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE deleted!='y' GROUP BY refer HAVING SUM(" . $element . ") = " . mres($last_filter['pdfilterextrafield']["SUM" . $element]) . ")";
								$filter_active = true;
							}

							//print "skip join";

						} else { // many to one orientation

							$reffield = GetReferencesToTable($ft['recordid'], "entity");
							$reffield = GetReferencesToTable($ft['recordid'], "entity");

							if (is_numeric($reffield)) {

								if (!in_array($ft['recordid'], $ftjoinsdone)) {
									$joincounter++;
									$joins .= " LEFT JOIN " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " AS ftjoin" . $ft['recordid'] . $joincounter . " ON (" . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $reffield . "=ftjoin" . $ft['recordid'] . $joincounter . ".recordid)";

									array_push($ftjoinsdone, $ft['recordid']);
								}

								if ($last_filter['pdfilterextrafield'][$element] != "") {
									if ($last_filter['pdfilterextrafield'][$element] == "@NOTUNIQUE@") {
										$nf = false;
										$tsql = "SELECT DISTINCT(" . $element . ") from " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE deleted!='y' GROUP BY " . $element . " HAVING COUNT(*)>1";
										if ($debug) print "<h2>" . $tsql . "</h2>";
										$tmp = db_GetFlatArray($tsql);

										$tataa .= " AND ftjoin" . $ft['recordid'] . $joincounter . "." . $element . " IN (";
										foreach ($tmp AS $cal) {
											if ($nf) $tataa .= ",";
											$tataa .= "'" . mres($cal) . "'";
											$nf = true;
										}
										if (!$nf) {
											$tataa .= "''";
										}
										$tataa .= ") ";
										if (!$nf) {
											$tataa .= " AND 1=0 ";
										}

										$and_sql_ins .= " " . $tataa . " AND ftjoin" . $ft['recordid'] . $joincounter . ".deleted!='y'";

									} elseif ($last_filter['pdfilterextrafield'][$element] == "@UNIQUE@") {

										$nf = false;
										$tsql = "SELECT DISTINCT(" . $element . ") from " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE deleted!='y' GROUP BY " . $element . " HAVING COUNT(*)=1";
										if ($debug) print "<h2>" . $tsql . "</h2>";
										$tmp = db_GetFlatArray($tsql);
										$tataa .= " AND ftjoin" . $ft['recordid'] . $joincounter . "." . $element . " IN (";
										foreach ($tmp AS $cal) {
											if ($cal != "") {
												if ($nf) $tataa .= ",";
												$tataa .= "'" . mres($cal) . "'";
												$nf = true;
											}
										}
										if (!$nf) {
											$tataa .= "''";
										}
										$tataa .= ") ";
										if (!$nf) {
											$tataa .= " AND 1=0 ";
										}

										$and_sql_ins .= " " . $tataa . " AND ftjoin" . $ft['recordid'] . $joincounter . ".deleted!='y'";

									} else {
										$and_sql_ins .= " AND ftjoin" . $ft['recordid'] . $joincounter . "." . $element . "='" . mres($last_filter['pdfilterextrafield'][$element]) . "' AND ftjoin" . $ft['recordid'] . $joincounter . ".deleted!='y'";
									}

									$filter_active = true;
								}

							}

						}

				}
			}
		}
		if ($ExtraFieldSearched) {
			// AND
			//$tataa = " (";
			$table_alias = 1;
			$ExtraFieldsList = GetExtraFields();
			foreach ($ExtraFieldsList as $field) {
				$element = "EFID" . $field['id'];
				if ($last_filter['pdfilterextrafield'][$element]) { // array element contains a value
					if ($last_filter['pdfilterextrafield'][$element]=="") {
						$last_filter['pdfilterextrafield'][$element]="%";
					} elseif (isset($last_filter['pdfilterextrafield'][$element])) {
						$filter_active = true;
					}
					if ($last_filter['pdfilterextrafield'][$element] == "@NOTUNIQUE@") {
						$nf = false;
						$tsql = "SELECT DISTINCT(" . $element . ") from " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted!='y' GROUP BY " . $element . " HAVING COUNT(*)>1";
						if ($debug) print "<h2>" . $tsql . "</h2>";
						$tmp = db_GetFlatArray($tsql);
						$tataa .= $GLOBALS['TBL_PREFIX'] . "entity." . $element . " IN (";
						foreach ($tmp AS $cal) {
							if ($nf) $tataa .= ",";
							$tataa .= "'" . mres($cal) . "'";
							$nf = true;
						}
						if (!$nf) {
							$tataa .= "''";
						}
						$tataa .= ") AND ";
						if (!$nf) {
							$tataa .= " 1=0 AND ";
						}

					} elseif ($last_filter['pdfilterextrafield'][$element] == "@UNIQUE@") {

						$nf = false;
						$tsql = "SELECT DISTINCT(" . $element . ") from " . $GLOBALS['TBL_PREFIX'] . "entity WHERE deleted!='y' GROUP BY " . $element . " HAVING COUNT(*)=1";
						if ($debug) print "<h2>" . $tsql . "</h2>";
						$tmp = db_GetFlatArray($tsql);
						$tataa .= $GLOBALS['TBL_PREFIX'] . "entity." . $element . " IN (";
						foreach ($tmp AS $cal) {
							if ($cal != "") {
								if ($nf) $tataa .= ",";
								$tataa .= "'" . mres($cal) . "'";
								$nf = true;
							}
						}
						if (!$nf) {
							$tataa .= "''";
						}
						$tataa .= ") AND ";
						if (!$nf) {
							$tataa .= " 1=0 AND ";
						}


					} elseif ($field['fieldtype'] == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
						$call = $last_filter['pdfilterextrafield'][$element];
						$el = explode(":", $call);
						if (!is_numeric($el[1])) $el[1] = 0;
						if (!is_numeric($el[2])) $el[2] = 0;
						switch ($el[0]) {
							case "RA":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . " AS DECIMAL(15,3))>=" . mres($el[1]) . " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . " AS DECIMAL(15,3))<=" . mres($el[2]) . " AND ";
							break;
							case "GT":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . " AS DECIMAL(15,3))>=" . mres($el[1]) . " AND ";
							break;
							case "LT":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . " AS DECIMAL(15,3))<" . mres($el[1]) . " AND ";
							break;
							case "EQ":
								if ($el[1] == "") {
									$tataa .= " " . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . "='' AND ";
								} else {
									$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . " AS DECIMAL(15,3))=" . mres($el[1]) . " AND ";
								}
							break;
							case "GTNE":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . " AS DECIMAL(15,3))>" . mres($el[1]) . " AND ";
							break;


						}
					} elseif (($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || ($field['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date")) && strstr($last_filter['pdfilterextrafield'][$element], "@") ) {
						$tataa .= " 1=1 " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "entity." . $element, $last_filter['pdfilterextrafield'][$element]) . " AND ";
					} else {
						$tataa .= " " . $GLOBALS['TBL_PREFIX'] . "entity.EFID" . $field['id'] . "='" . mres($last_filter['pdfilterextrafield'][$element]) . "' AND ";
					}
				}
			}
			$ExtraFieldsList = GetExtraCustomerFields();
			foreach ($ExtraFieldsList as $field) {
				$element = "EFID" . $field['id'];
				if ($last_filter['pdfilterextrafield'][$element]) { // array element contains a value
					if ($last_filter['pdfilterextrafield'][$element]=="") {
						$last_filter['pdfilterextrafield'][$element]="%";
					} elseif (isset($last_filter['pdfilterextrafield'][$element])) {
						$filter_active = true;
					}
					if ($field['fieldtype'] == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
						$call = $last_filter['pdfilterextrafield'][$element];
						$el = explode(":", $call);
						if (!is_numeric($el[1])) $el[1] = 0;
						if (!is_numeric($el[2])) $el[2] = 0;

						switch ($el[0]) {
							case "RA":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))>='" . mres($el[1]) . "' AND CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))<='" . mres($el[2]) . "' AND ";
							break;
							case "GT":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))>='" . mres($el[1]) . "' AND ";
							break;
							case "LT":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))<'" . mres($el[1]) . "' AND ";
							break;
							case "GTNE":
								$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))>'" . mres($el[1]) . "' AND ";
							break;
							case "EQ":
								if ($el[1] == "") {
									$tataa .= " " . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . "='' AND ";
								} else {
									$tataa .= " CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))='" . mres($el[1]) . "' AND ";
								}
							break;


						}
					} elseif (($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time"|| ($field['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date")) && strstr($last_filter['pdfilterextrafield'][$element], "@") ) {
						$tataa .= " 1=1 " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "customer." . $element, $last_filter['pdfilterextrafield'][$element]) . " AND ";
					} else {
						$tataa .= " " . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . "='" . mres($last_filter['pdfilterextrafield'][$element]) . "' AND ";
					}
				}
			}
			$joins .= " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "loginusers AS uj1 ON (" . $GLOBALS['TBL_PREFIX'] . "entity.assignee = uj1.id OR uj1.id IS NULL) LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "loginusers AS uj2 ON (" . $GLOBALS['TBL_PREFIX'] . "entity.owner=uj2.id OR uj2.id IS NULL)";
			$sql= "SELECT " . $GLOBALS['TBL_PREFIX'] . "entity.*, " . $GLOBALS['TBL_PREFIX'] . "accesscache.result AS CachedResult, " . $GLOBALS['TBL_PREFIX'] . "customer.*, " . $GLOBALS['TBL_PREFIX'] . "entity.timestamp_last_change AS entity_lastchange FROM " . $GLOBALS['TBL_PREFIX'] . "entity " . $joins . "," . $GLOBALS['TBL_PREFIX'] . "customer ";

			$sql .= " WHERE " . $tataa . " ";
			if (!$sort) $sort = "eid";

			$sql .= " " . $GLOBALS['TBL_PREFIX'] . "customer.id=" . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer " . $deleted_value . " " . $and_sql_ins . "";


			$filter = "normal";
		} else {
			$joins = "LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "loginusers AS uj1 ON (" . $GLOBALS['TBL_PREFIX'] . "entity.assignee = uj1.id OR uj1.id IS NULL) LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "loginusers AS uj2 ON (" . $GLOBALS['TBL_PREFIX'] . "entity.owner = uj2.id OR uj2.id IS NULL) " . $joins;
			$sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "entity.*," . $GLOBALS['TBL_PREFIX'] . "accesscache.result AS CachedResult," . $GLOBALS['TBL_PREFIX'] . "customer.*, " . $GLOBALS['TBL_PREFIX'] . "entity.timestamp_last_change AS entity_lastchange FROM " . $GLOBALS['TBL_PREFIX'] . "entity " . $joins . "," . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $GLOBALS['TBL_PREFIX'] . "customer.id=" . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer " . $deleted_value . " " . $and_sql_ins . " ";
			$filter = "normal";

		}
	}

	if ($pregiven_query_ins != "") {
		$sql .= $pregiven_query_ins;
	}

	if (!stristr("order by" , $sql) && $sort != "" && !$given_order) {
		$sql .= " ORDER BY " . $sort . "";
	} elseif ($given_order) {
		$sql .= " " . $given_order;
	}


	$usernames = array(); // cache array

	if (!$_REQUEST['Pag_Moment']) {
		$_REQUEST['Pag_Moment'] = 0;
	}

	if ($_REQUEST['querystash']) {
		$query = PopStashValue($_REQUEST['querystash']);
	}
	if ($_REQUEST['del_selected']) { // The user changed his/her preferece whether or not to include deleted entities
		SetAttribute("user", "DontIncludeDeletedEntitiesWhenSearching", $_REQUEST['dontincludedeleted'], $GLOBALS['USERID'], array("Yes", "No"));
	}

	if (($_REQUEST['dontincludedeleted'] == "No" || GetAttribute("user", "DontIncludeDeletedEntitiesWhenSearching", $GLOBALS['USERID']) == "No") && $_REQUEST['fs'] != "" && !$_REQUEST['ClearFilter']) {
		$sql = NormalSearch(true, $_REQUEST['fs'], true);
		$_REQUEST['dontincludedeleted'] = "No";
		$filter_active = true;

	} elseif ($_REQUEST['fs'] != "" && !$_REQUEST['ClearFilter']) {
		$sql = NormalSearch(false, $_REQUEST['fs'], true);
		$filter_active = true;
	}

	if ($given_order) {
		$sort = " " . $given_order;
	} elseif ($sort == "" && !$nomoresort) {
		$sort = " ORDER BY eid";
	} elseif (!$nomoresort) {
		$sort = " ORDER BY " . $sort;
	} else {
		$sort = "";
	}


	if (!stristr($sql, "ORDER BY")) {
		$sql .= $sort;
	}


	if ($given_limit) {
		$sql .= " LIMIT " . $given_limit;
	}



	$sql = str_replace($GLOBALS['TBL_PREFIX'] . "entity.*", "DISTINCT(" . $GLOBALS['TBL_PREFIX'] . "entity.eid)", $sql);
	$entity_sql = $sql;
	$entity_sql = str_replace("," . $GLOBALS['TBL_PREFIX'] . "customer.*", "", $entity_sql);


	//$result = db_GetArray($sql,$db);
	$browse_array = array();
	$result = array();

	if ($PAGINATEROWS <> "" && $PAGINATEROWS <> 0) {

		$window_to_print_from = $_REQUEST['Pag_Moment'];
		$window_to_print_to = $_REQUEST['Pag_Moment'] + $PAGINATEROWS;

		$top = sizeof($result);

		for ($l=0;$l<$top;$l++) {
			if ($l < $window_to_print_from || $l > $window_to_print_to) {
				unset($result[$l]);
			}
		}
	}

	if ($debug) print "<h1> HIER " . htme($entity_sql) . "</h1>";

	// Actually execute the query here
	$res = mcq($entity_sql, $db);
	$passthru = 0;

	$result = array();
	$page_eid_list = array();
	$ok = false;

	while ($row = mysql_fetch_array($res)) {
		if ($row['CachedResult'] == "") {
			if (CheckEntityAccess($row['eid']) != "nok") {
				$ok = true;
			}
		} else {
			$ok = true;
		}
		if ($ok) {
			$passthru++;
			$atleastonereadwrite = true;
			if (($passthru > $window_to_print_from && $passthru <= $window_to_print_to) || $PAGINATEROWS == "0" || $PAGINATEROWS == "" && !in_array($row['eid'], $page_eid_list)) {
				array_push($page_eid_list, $row['eid']);
			}
			//if (!in_array($row['eid'], $browse_array)) {
				array_push($browse_array, $row['eid']);
			//}
		}
		$ok = false;
	}
	if ($debug) print "<h2>No. of results: " . $passthru . ", page only " . sizeof($page_eid_list) . "</h2>";
	$num_entities_on_this_page = sizeof($page_eid_list);



	if ($num_entities_on_this_page > 0)
	{
		$page_query = " " . $GLOBALS['TBL_PREFIX'] . "entity.eid IN (";
		$nf = false;
		foreach ($page_eid_list AS $page_entity) {

				$page_query .= (!$nf) ? "" : ",";
				$page_query .= $page_entity;
				$nf = true;
				//print "<h1> add $page_entity</h2>";

		}
		$page_query .= ")";
		unset($nf);



		if (!$nomoresort && $given_order == "") {

			$order = " ORDER BY FIELD(eid, ";
			foreach ($page_eid_list AS $page_entity) {
				$order .= (!$nf) ? "" : ",";
				$order .= $page_entity;
				$nf = true;
			}
			$order .= ")";
		} elseif ($given_order != "" && !$nomoresort) {
			$order = " " . $given_order;
		}
		//$sql = str_replace("ORDER BY", "AND " . $page_query . " ORDER BY", $sql);


		$sql = "SELECT DISTINCT(" . $GLOBALS['TBL_PREFIX'] . "entity.eid), " . $GLOBALS['TBL_PREFIX'] . "entity.*, " . $GLOBALS['TBL_PREFIX'] . "customer.*, " . $GLOBALS['TBL_PREFIX'] . "entity.timestamp_last_change AS entity_lastchange, " . $GLOBALS['TBL_PREFIX'] . "accesscache.result AS CachedResult FROM " . $GLOBALS['TBL_PREFIX'] . "entity " . $joins . ", " . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer=" . $GLOBALS['TBL_PREFIX'] . "customer.id AND " . $page_query . " GROUP BY " . $GLOBALS['TBL_PREFIX'] . "entity.eid " . $order;

		if ($debug) print "<h1>Actual run: $sql</h1>";

		$result = db_GetArray($sql);
	}


	if ($debug) print "<h2>No. of results 1: " . sizeof($result) . "</h2>";

	$TotalReturnedRows = sizeof($browse_array);
//jero
//print_r($_REQUEST);
	$header_title = "&nbsp;<span class=\"toplistentryandsearch\"><div class=\"showinline entityinputbox\" id='JS_entityinputbox'>#: <input type='text' size='3' name='e' onchange=\"document.location='edit.php?e=' + this.value\"; ></div>&nbsp;<div class=\"showinline entitysearchbox\" id='JS_entitysearchbox'><img src='images/searchbox.png' alt='' class='search_img'><input onkeypress=\"TriggerOnchangeOnEnter(event,this);\" onchange=\"" . $func . "fs=' + document.getElementById('entitysearch').value);return(false);\" id=\"entitysearch\" class='search_input autocomplete' type='search' name='wildsearch' value='" . htme($_REQUEST['fs']) . "'></div>&nbsp;&nbsp;</span>";
	//
	$total_entities_found = sizeof($browse_array);
	if ($list_title) {
		$header_title .= "<div id=\"JS_listheadertext\" class=\"showinline\">". $list_title . ": " . $total_entities_found . " " . $lang['entitiesfound'] . "</div>";
	} elseif ($filter == "viewdel") {
		$header_title .= "<div id=\"JS_listheadertext\" class=\"showinline\">". $lang['delentities'] . ": " . $total_entities_found . " " . $lang['entitiesfound'] . "</div>";
	} elseif ($_REQUEST['fs']) {

		$tmp = GetAttribute("user", "EntitylistSearchWords", $GLOBALS['USERID']);
		if (!is_array($tmp)) {
			$tmp = array();
		}
		if (!in_array(trim($_REQUEST['fs']), $tmp)) {
			$tmp[] = trim($_REQUEST['fs']);
			SetAttribute("user", "EntitylistSearchWords", $tmp, $GLOBALS['USERID']);
		}

		$searched = "\"" . str_replace(" " , "\" + \"", htme($_REQUEST['fs']));
		$searched = str_replace("+ \"-", " <i>excluding</i> \"", $searched) . "\"";


		$header_title .= " <div id=\"JS_listheadertext\" class=\"showinline\"> ". $lang['briefover'] . ": " . $total_entities_found . " " . $lang['entitiesfound'] . " | " . $lang['search'] . ": <strong>" . $searched . "</strong></div>";

		$cl = GetClearanceLevel();

		if (in_array("NoViewDeleted", $cl)) {

		} else {

			if ($_REQUEST['dontincludedeleted'] == "No") {
				$header_title .= " | " . $lang['incldel'] . ": <input type='checkbox' class='noclass' name='includedeleted' id='incldel' checked='checked' onchange=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&dontincludedeleted=Yes&amp;del_selected=true');\" value='0'>";
			} else {
				$header_title .= " | " . $lang['incldel'] . ": <input type='checkbox' class='noclass' name='includedeleted' onchange=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;&dontincludedeleted=No&amp;del_selected=true');\" value='1'>";
				$func_includedel = "&amp;dontincludedeleted=Yes";
			}


		}

	} else {
		$header_title .= "<div id=\"JS_listheadertext\" class=\"showinline\">". $lang['briefover'] . ": " . $total_entities_found . " " . $lang['entitiesfound'] . "</div>";
	}

	if ($_REQUEST['HrFt'] != "") {
		$header_title .= " | " . $lang['search'] . ": <strong>" . htme($_REQUEST['HrFt']) . "</strong>";
	}


	if (isset($_POST['mainList'])) {

		$ss = "<div id=\"JS_savedselections\" class=\"showinline\"> " . $lang['savedselection'] . ": <select ";
		if ($usingSavedSelection != "") {
			$ss .= "class='highlightedselectbox'";
		}
		$ss .= " name='ssSelect' onchange=\"" . $func . "&loadSavedSelection=' + this.options[this.selectedIndex].value);\"><option value='none'>" . $lang['none'] . "</option>";
		if ($usingSavedSelection == "n/a") {
			$ss .= "<option selected='selected' value=''>n/a</option>";
		}
		$foundsome = false;

		$tmp = GetAttribute("system", "SavedEntityListSelections", 1);
		$add = GetAttribute("user", "SavedEntityListSelections", $GLOBALS['USERID']);
		if (!is_array($tmp)) $tmp = array();
		if (!is_array($add)) $add = array();
		$tmp = array_merge($tmp, $add);

		foreach ($tmp AS $savedSelectionName => $ignore) {
				if ($usingSavedSelection == $savedSelectionName) {
					$ins = "selected='selected'";
				} else {
					$ins = "";
				}
				$ss .= "<option " . $ins . " value='" . htme($savedSelectionName) . "'>" . htme($savedSelectionName) . "</option>";
				$foundsome = true;
		}
		$ss .= "</select></div>";

		if ($foundsome && CheckFunctionAccess("HideSelectionsSelectBox") != "ok" && !$dontshowselections) {
			$header_title .= $ss;
		} elseif ($dontshowselections && $usingSavedSelection != "") {
			$header_title .= "<div class=\"showinline\" id=\"JS_selectionname\"> " . $lang['savedselection'] . ": " . $usingSavedSelection . "</div>";
		}
		

		//$header_title .= $VisualQuery;
	}

	if (CheckFunctionAccess("AddEditSelections") != "nok" && isset($_POST['mainList']) && !$dontshowselections) {

		$header_title .= " <span class=\"selections\">[<a onclick=\"PopFancyBoxLarge('Interleave advanced selection builder', 'index.php?ShowAdvancedQueryInterface&ParentEntityListAjaxHandler=" . htme($_REQUEST['AjaxHandler']) . "');\">" . $lang['selections'] . "</a>]</span> ";
	}


	$ins = "";
	// ADD OPTION FOR DATE FILTER
	if ($date_filter_active && $listname=="Summary") {
		$txt = $lang['adjustdatefilter'];
		$ins = " <a class='arrow' onclick=\"" . $func . "deletedatefilter=true');\">" . $lang['deletedatefilter'] . "</a>";
	} elseif ($listname=="Summary") {
		$txt = $lang['setdatefilter'];
	} else {
		$ins = "";
		$txt = "";
	}
	if ($listname=="Summary") {
		$header_title .= " <a href=\"#\" class='arrow' onclick=\"popWidewindowWithBars('choose_datefilter.php?ParentAjaxHandler=" . htme($_REQUEST['AjaxHandler']) . "');\">" . $txt . " </a> ";
	}

	$header_title .= $ins;
	$nf = false;
	$direct_sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "accesscache ON ((" . $GLOBALS['TBL_PREFIX'] . "entity.eid=" . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.user=" . $GLOBALS['USERID'] . " AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.type='e' AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.result != 'nok') OR " . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid IS NULL)," . $GLOBALS['TBL_PREFIX'] . "customer WHERE " . $GLOBALS['TBL_PREFIX'] . "entity.CRMcustomer=" . $GLOBALS['TBL_PREFIX'] . "customer.id AND eid IN (";

	$order_by_field = " GROUP BY " . $GLOBALS['TBL_PREFIX'] . "entity.eid ORDER BY FIELD(eid, ";

	foreach ($browse_array AS $eid) {
		if ($nf) {
			$direct_sql .= ",";
			$order_by_field .= ",";
		}


			$direct_sql .= $eid;
			$order_by_field .= $eid;
			$nf = true;

	}
	$direct_sql .= ")";

	$direct_sql .= $order_by_field . ")";

	if (!$nf) {
		$direct_sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE 1=0";
	}


	$browsearray = PushStashValue($browse_array);
	$direct_query_stashid = PushStashValue($direct_sql);
	if ($debug) {
			print "Direct query: $direct_sql"; 
		}

	if ($debug) print "<h2>Found after pag_moment check: " . sizeof($result) . " (done)</h2>";

	$outputbuffer2 .= "<table width='100%' class=\"listheadertable\"><tr><td class=\"nwrp list_numbers\">";
	if ($PAGINATEROWS <> "" && $PAGINATEROWS <> 0) {

		$P_buffer2 .= "<button value='' onclick=\"" . $func . "&amp;fs=" . htme($_REQUEST['fs']) . "&amp;Pag_Moment=" . ($_REQUEST['Pag_Moment'] - $PAGINATEROWS) . $func_includedel . "');\"";
		if ($_REQUEST['Pag_Moment'] == 0) {
			$P_buffer2 .= " disabled='disabled'";
		}
		$P_buffer2 .= ' class="prevpage">&laquo</button>';

		$number_of_pages = ceil($total_entities_found / $PAGINATEROWS);

		$P_buffer2 .= '&nbsp;';
		$P_buffer2 .= '<select class="pageselector">';
		for ($x = 1; $x <= $number_of_pages; $x++) {
			$Pag_Moment_dd = ($x - 1) * $PAGINATEROWS;
			$P_buffer2 .= '<option value="' . $Pag_Moment_dd . '"';
			if ($Pag_Moment_dd == $_REQUEST['Pag_Moment']) {
				$P_buffer2 .= ' selected="selected"';
			}
			$P_buffer2 .= ' onclick="' . $func . '&amp;fs=' . htme($_REQUEST['fs']) . '&amp;Pag_Moment=' . $Pag_Moment_dd . $func_includedel . '\');">' . $x . '</option>';
		}
		$P_buffer2 .= '</select>';
		$P_buffer2 .= '&nbsp;';

		$P_buffer2 .= "<button value='' onclick=\"" . $func . "&amp;fs=" . htme($_REQUEST['fs']) . "&amp;Pag_Moment=" . ($_REQUEST['Pag_Moment'] + $PAGINATEROWS) . $func_includedel . "');\"";
		if ($TotalReturnedRows <= ($_REQUEST['Pag_Moment'] + $PAGINATEROWS)) {
			$P_buffer2 .= " disabled='disabled'";
		}
		$P_buffer2 .= ' class="nextpage">&raquo;</button>';

		if ($TotalReturnedRows > ($PAGINATEROWS + $_REQUEST['Pag_Moment'])) {
			$num = ($_REQUEST['Pag_Moment'] + $PAGINATEROWS);
		} else {
			$num = $total_entities_found;
		}

		$P_buffer2 .= "</td><td>";
		$P_buffer2 .= "<select onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true" . $func_includedel . "');\" name='pdfilterrowsperpage'>";
		$opti = explode(",", $GLOBALS['PAGINATEOPTIONSLIST']);
		foreach ($opti AS $rpp) {
			if ($PAGINATEROWS == $rpp) {
				$insuj = "selected='selected'";
			} else {
				$insuj = "";
			}
			$P_buffer2 .= "<option value='" . $rpp . "' " . $insuj . ">" . $rpp . "</option>";
		}

		$P_buffer2 .= "</select>";

		$P_buffer2 .= "</td><td class=\"nwrp\">&nbsp;" . ($_REQUEST['Pag_Moment'] + 1) . "-" . $num . " / " . $total_entities_found . "</td>";

		$P_buffer3 .= "<td></td><td style='width: 90%' class=\"nwrp header_title\">@HEADER_TITLE@</td>";
		$outputbuffer2 .= $P_buffer1 . $P_buffer2 . $P_buffer3; // $P_buffer2 is also used at the bottom (not)
	} else {
		$outputbuffer2 .= "</td><td class=\"header_title\">@HEADER_TITLE@</td>";

	}

	$outputbuffer2 .= "<td class=\"nwrp clearfilterlink\">" . " " . " @CLEARFILTERLINK@</td>";

	if (CheckFunctionAccess("HideListExportIcons") == "ok") {
		$GLOBALS['NoIconsInMainList'] = true;
	} elseif($nofunctions) {
		$GLOBALS['NoIconsInMainList'] = false;
	}

	$outputbuffer2 .= "<td class='nwrp rightalign icons'>";
	if (!$GLOBALS['NoIconsInMainList'] && !$nofunctions) {
		$tmp = db_GetArray("SELECT mid, module_name, module_list_html FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE module_list_html!='' AND for_table='entity'");
		foreach ($tmp AS $mid) {
			if (CheckModuleAccess($mid['mid']) == "ok") {
				$outputbuffer2 .= "<a href='modules.php?action=run&amp;noajax&amp;mid=" . $mid['mid'] . "&amp;dqs=" . $direct_query_stashid . "'>" . $mid['module_list_html'] . "</a>&nbsp;";
			}
		}
	}

	$outputbuffer2 .= $customize;



	if (!$GLOBALS['NoIconsInMainList'] && CheckFunctionAccess("HideListExportIconsPDF") != "ok") $outputbuffer2 .= "<a " . PrintAltToolTipCode($lang['downloadpdf']) . " onclick=\"poplittlewindow('parsepdf.php?stashid=" . $direct_query_stashid . "&amp;SingleEntity=true');\"><img src='images/pdf.gif' alt=''></a>";

//	if (!$GLOBALS['NoIconsInMainList'] && CheckFunctionAccess("HideListExportIconsGANTT") != "ok") $outputbuffer2 .= "<a " . PrintAltToolTipCode("View GANTT chart") . " onclick=\"popWidewindowWithBars('gantt.php?stashid=" . $direct_query_stashid . "')\"><img src='images/gantt_icon.gif' alt=''></a>";

//	if (CheckFunctionAccess("ManagementInfo") == "ok" || is_administrator()) {
//		if (!$GLOBALS['NoIconsInMainList'] && CheckFunctionAccess("HideListExportIconsMI") != "ok") {
//			$outputbuffer2 .= "<a href=\"stats.php?stashid=" . $direct_query_stashid . "\"><img src='images/graph_icon.jpg' alt=''></a>";
//		}
//	}

	$csv_url = "csv.php?stashid=" . $direct_query_stashid;
	if (!$GLOBALS['NoIconsInMainList'] && CheckFunctionAccess("HideListExportIconsEntityReport") != "ok") $a = "<a " . PrintAltToolTipCode($lang['createreports']) . " onclick=\"poplittlewindowWithBars('entityreport.php?stashid=" . $direct_query_stashid . "&amp;nonavbar=1&amp;tbbox=1');\"><img src='images/word.gif' alt=''></a>";


	$htmlins = "&amp;CustomColumnLayoutStash=" . PushStashValue($GLOBALS['UC']['MainListColumnsToShow']);
//	print PopStashValue($direct_query_stashid);

	if (!$GLOBALS['NoIconsInMainList'] && CheckFunctionAccess("HideListExportIconsExcelDirect") != "ok") $outputbuffer2 .= "<a " . PrintAltToolTipCode($lang['downloadsumcsv'] . " (direct)") . " href='csv.php?DlSs" . $htmlins . "&amp;QiD=" . $direct_query_stashid . "&amp;separator=RealExcel'><img src='images/excel_large.gif' alt=''></a>";

	//if (!$GLOBALS['NoIconsInMainList']) $outputbuffer2 .= "<a href='#' " . PrintAltToolTipCode($lang['downloadsumcsv'] . " (choose fields)") . " onclick=\"popslightlybiggerwindowWithBars('" . $csv_url . "&amp;nonavbar=1&amp;tbbox=1');\"><img src='images/excel_large_double.gif' alt=''></a>";
	if ($_REQUEST['CustomColumnLayout'] != "") {
		$cclo = $_REQUEST['CustomColumnLayout'];
	} else {
		$cclo = $lang['briefover'];
	}

	if (!$GLOBALS['NoIconsInMainList'] && CheckFunctionAccess("HideListExportIconsExcelCF") != "ok") $outputbuffer2 .= "<a " . PrintAltToolTipCode($lang['downloadsumcsv'] . " (choose fields)") . " onclick=\"popcolumnchooser('" . $_REQUEST['AjaxHandler'] . "','" . $direct_query_stashid . "','" . $cclo . " - " . $lang['downloadsumcsv'] . "','entity');\"><img src='images/excel_large_double.gif' alt=''></a>";



	$outputbuffer2 .= $a;
	$outputbuffer2 .= "</td></tr></table>";
	if ($_REQUEST['ListTemplate'] > 0) {
		$header_row = $outputbuffer2;
	} else {
		$header_row = "<table class=\"listmastertable\"><tr><td>" . $outputbuffer2 . "</td></tr><tr><td>" . $outputbuffer;
	}
	unset($outputbuffer);
	// Dim sum array (for numeric field totals)
	$sums = array();
	$ExtraFieldsList = GetExtraFields();
	$ExtraCustomerFieldsList = GetExtraCustomerFields();

	// **************************************** **************************************** **************************************** ****************************************
	// ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ********
	// **************************************** **************************************** **************************************** ****************************************
	// **************************************** **************************************** **************************************** ****************************************
	// ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ********
	// **************************************** **************************************** **************************************** ****************************************
	// **************************************** **************************************** **************************************** ****************************************
	// ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ********
	// **************************************** **************************************** **************************************** ****************************************
	// **************************************** **************************************** **************************************** ****************************************
	// ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ********
	// **************************************** **************************************** **************************************** ****************************************
	// **************************************** **************************************** **************************************** ****************************************
	// ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ********
	// **************************************** **************************************** **************************************** ****************************************
	// **************************************** **************************************** **************************************** ****************************************
	// ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ******** ****** Start displaying results ********
	// **************************************** **************************************** **************************************** ****************************************


	// STATISTICS

	if (!$browsearray) {
		$browsearray = "0";
	}

	$interimheader = str_replace("<td", "<td class=\"nwrp\"", $interimheader);

	//$outputbuffer .= "<tr height='1'><td height='1' colspan='122' style='background-color: " . $GLOBALS['DFT_FOREGROUND_COLOR'] . "'; border: 1px;></td></tr>";
	$windowcounter=0;
	$PrintedRowCounter = 0;
	$InterimHeaderCounter = 0;
	
	if ($_REQUEST['ListTemplate'] > 0) {
		$lte = GetTemplate($_REQUEST['ListTemplate']);
	}	
	
	foreach ($result AS $e) {
			if ($e['CachedResult'] == "") {

				$e['CachedResult'] = CheckEntityAccess($e['eid']);
				if ($e['CachedResult'] == "nok") {
					continue;
				}
			}
			if ($InterimHeaderCounter == $GLOBALS['SHOWTABLEHEADEREVERY'] && $GLOBALS['SHOWTABLEHEADEREVERY'] != 0 && $GLOBALS['SHOWTABLEHEADEREVERY'] != "") {
				$outputbuffer .= "<tr class='highlightedrow'>" . $interimheader . "</tr>";
				$InterimHeaderCounter=0;
			}
			$InterimHeaderCounter++;
			$windowcounter++;

			$teller++;
			$tab_depth = 0;
			array_push($FA_datal,"edit.php?e=" . $e['eid']);
			array_push($displayed,$e['eid']);
			$usernames[$e['assignee']] = GetUserName($e['assignee']);
			$owner = GetUserName($e['owner']);
			$e1['FULLNAME'] = $usernames[$e['assignee']];

			$to_pass = "&fs=" . htme($_REQUEST['fs']) . "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']);

			if ($e['FULLNAME']=="") { $e['FULLNAME'] = "n/a"; }
			if ($_REQUEST['fromlistAjaxHandler'] =="") {
				$_REQUEST['fromlistAjaxHandler'] = "0";
			}
			if (GetAttribute("system", "EntityListAlwaysInPopup", 2) != "Yes") {
				$td_select_ins = " onclick=\"OE(" . $e['eid'] . "," . $browsearray . "," . $_REQUEST['fromlistAjaxHandler'] . ",'" . htme($_REQUEST['fs']) . $to_pass  . "')\" ";
			} else {
				$td_select_ins = " onclick=\"PopEditEntityWindow('" . $e['eid'] . "&amp;ParentAjaxHandler=" . htme($_REQUEST['AjaxHandler']) . $to_pass . "')\" ";
			}
			
			if ($lte != "") {
				$totalbuffer .= "<tr><td>" . ParseTemplateEntity($lte, $e['eid'], false, false, false, "htme", false) . "</td></tr>";

			} else {
	
				$outputbuffer .= "<tr id='tr_list_element_" . $e['eid'] . "' onmouseover=\"HL(this)\" onmouseout=\"UL(this)\" class='pointer'>";
				$outputbuffer .= "<td class=\"nwrp td_eid\">";
				if (GetAttribute("system", "EntityListAlwaysInPopup", 2) != "Yes") {
					$outputbuffer .= "<div class=\"MainEntityListEntityPopupLink showinline\"><a onclick=\"PopEditEntityWindow('" . $e['eid'] . "&amp;ParentAjaxHandler=" . htme($_REQUEST['AjaxHandler']) . "')\"><img src='images/fullscreen_maximize.gif' width='14' height='12' alt='Click to open this entity in a new window'></a>&nbsp;</div>";
				}
				$lockedby = IsLocked($e['eid']);
				if ($lockedby<>"") {
					$lock_ins = "<img src='images/lock.png' title='This entity is locked for editing by " . GetUserName($lockedby) . "' alt=''>";
				} else {
					unset($lock_ins);
				}
				if ($e['deleted'] == "y") {
					$outputbuffer .= "<span style='color: #ff0000'>" . $e['eid'] . "</span>&nbsp;" . $lock_ins;
					array_push($FA_data,"<span style='color: #ff0000'>" . $e['eid'] . "</span>&nbsp;" . $lock_ins);
				} elseif ($e['CachedResult'] == "readonly") {
					$outputbuffer .= "<span style='color: #808080'>" . $e['eid'] . "</span>&nbsp;" . $lock_ins;
					array_push($FA_data,"<span style='color: #808080'>" . $e['eid'] . "</span>&nbsp;" . $lock_ins);
				} else {
					$outputbuffer .= $e['eid'] . "&nbsp;" . $lock_ins;
					array_push($FA_data,$e['eid'] . "&nbsp;" . $lock_ins);
				}
				$tab_depth++;
				$outputbuffer .= "</td>";
	
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust']) {
	
					$last_filter['pdfiltercustomer'] <> "all" && $last_filter['pdfiltercustomer'] <> "" ? $popins = " hlc" : $popins = false;
	
					if (in_array("customer", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
						$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "customer", GetCustomerName($e['CRMcustomer']));
						$tmp_select_ins = $td_select_ins;
	
						unset($td_select_ins);
					} else {
						$dspval = htme(GetCustomerName($e['CRMcustomer']));
					}
					$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
					$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp" . $popins . " " . $interact_css . " td_customer\" id='td_list_element_customer_" . $e['eid'] . "'>" . $dspval . "</td>";
	
					if ($tmp_select_ins) {
						$td_select_ins = $tmp_select_ins;
						unset($tmp_select_ins);
					}
	
					$tab_depth++;
					array_push($FA_data,$e['custname']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_owner']) {
	
						$last_filter['pdfilterowner'] <> "all" && $last_filter['pdfilterowner'] <> "" ? $popins = " hlc" : $popins = false;
	
						if (in_array("owner", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
							$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "owner", GetUserName($e['owner']));
							$tmp_select_ins = $td_select_ins;
							unset($td_select_ins);
						} else {
							$dspval = GetUserName($e['owner']);
						}
						$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
	
						$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp " . $popins . " " . $interact_css . " td_owner\" id='td_list_element_owner" . $e['eid'] . "'>" . $dspval . "</td>";
	
						if ($tmp_select_ins) {
							$td_select_ins = $tmp_select_ins;
							unset($tmp_select_ins);
						}
						$tab_depth++;
						array_push($FA_data,GetUserName($e['owner']));
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_ownergroup']) {
						$last_filter['pdfilterownergroup'] <> "all" && $last_filter['pdfilterownergroup'] <> "" ? $popins = " hlc" : $popins = false;
						$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp " . $popins . " td_ownergroup\">" . GetGroupName(GetGroup($e['owner'])) . "</td>";
						$tab_depth++;
						array_push($FA_data,GetUserName($e['owner']));
				}
	
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_assignee']) {
	
						$last_filter['pdfilterassignee'] <> "all" && $last_filter['pdfilterassignee'] <> "" ? $popins = " hlc" : $popins = false;
						if (in_array("assignee", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
							$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "assignee", GetUserName($e['assignee']));
							$tmp_select_ins = $td_select_ins;
							unset($td_select_ins);
						} else {
							$dspval = GetUserName($e['assignee']);
						}
	
						$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
						$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp" . $popins . " " . $interact_css . " td_assignee\" id='td_list_element_assignee_" . $e['eid'] . "'>" . $dspval . "</td>";
	
						if ($tmp_select_ins) {
							$td_select_ins = $tmp_select_ins;
							unset($tmp_select_ins);
						}
						$tab_depth++;
						array_push($FA_data,$e1['FULLNAME']);
				}
	
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_assigneegroup']) {
						$last_filter['pdfilterassigneegroup'] <> "all" && $last_filter['pdfilterassigneegroup'] <> "" ? $popins = " hlc" : $popins = false;
	
						$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp" . $popins . " td_assigneegroup\">" . GetGroupName(GetGroup($e['assignee'])) . "</td>";
						$tab_depth++;
						array_push($FA_data,GetUserName($e['owner']));
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_status']) {
					if (!$colortustat[$e['status']]) {
						$colornumstat = GetStatusNum($e['status']);
					}
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_status']) {
					$last_filter['pdfilterstatus'] <> "all" && $last_filter['pdfilterstatus'] <> "" ? $popins = " hlc" : $popins = false;
	
					if (in_array("status", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
						$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "status", $e['status']);
						$tmp_select_ins = $td_select_ins;
						unset($td_select_ins);
					} else {
						$dspval = htme($e['status']);
					}
					$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
					$outputbuffer .= "<td " . $td_select_ins . " class=\"SR" . $colornumstat . " nwrp" . $popins . " " . $interact_css . " td_status\" id='td_list_element_status_" . $e['eid'] . "'>";
					$outputbuffer .= "<span class=\"" . ReturnClassnameForTextColorBasedOnBackgroundColor(GetStatusColor($e['status'])) . "\">" . $dspval . "</span></td>";
					if ($tmp_select_ins) {
							$td_select_ins = $tmp_select_ins;
							unset($tmp_select_ins);
					}
					array_push($FA_data,$e['status']);
					$tab_depth++;
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_priority']) {
					if (!$colortuprio[$e['priority']]) {
						$colornumprio = GetPriorityNum($e['priority']);
					}
					$last_filter['pdfilterpriority'] <> "all" && $last_filter['pdfilterpriority'] <> "" ? $popins = " hlc" : $popins = false;
					if (in_array("priority", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
						$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "priority", $e['priority']);
						$tmp_select_ins = $td_select_ins;
						unset($td_select_ins);
					} else {
						$dspval = htme($e['priority']);
					}
					$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
					$outputbuffer .= "<td " . $td_select_ins . " class=\"PR" . $colornumprio . " nwrp" . $popins . " " . $interact_css . " td_priority\" id='td_list_element_priority_" . $e['eid'] . "'>";
					$outputbuffer .= "<span class=\"" . ReturnClassnameForTextColorBasedOnBackgroundColor(GetPriorityColor($e['priority'])) . "\">" . $dspval . "</span></td>";
					if ($tmp_select_ins) {
							$td_select_ins = $tmp_select_ins;
							unset($tmp_select_ins);
					}
					$tab_depth++;
					array_push($FA_data,$e['priority']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_category']) {
					$popins = false;
					if (in_array("category", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
						$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "category", $e['category']);
						$tmp_select_ins = $td_select_ins;
						unset($td_select_ins);
					} else {
						$dspval = htme($e['category']);
					}
					$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
					$outputbuffer .= "<td " . $td_select_ins . " class=\"" . $popins . " " . $interact_css . " td_category\">" . $dspval . "</td>";
					if ($tmp_select_ins) {
							$td_select_ins = $tmp_select_ins;
							unset($tmp_select_ins);
					}
					$tab_depth++;
					array_push($FA_data,$e['category']);
				}
	
				if (sizeof($ExtraFieldsList)>0 && $ExtraFieldsList[0]<>"") {
					/*
					foreach ($ef_array AS $row) {
						foreach ($row AS $line) {
							qlog(INFO, $line);
						}
					}
					*/
					foreach ($ExtraFieldsList AS $field) {
						$element = "EFID" . $field['id'];
	
						if ($GLOBALS['UC']['MainListColumnsToShow'][$element]) {
	
							//$field['optioncolors'] = GetExtraFieldOptioncolors($field['id']);
	
	
	
							$tab_depth++;
	
							$localalign="";
	
							$val = GetExtraFieldValue($e['eid'], $field['id'], true, true, $e['EFID' . $field['id']]);
	
							$CellColor = GetExtraFieldColor($field['id'], $e['EFID' . $field['id']]);
	
	
							$last_filter['pdfilterextrafield'][$element] <> "all" && $last_filter['pdfilterextrafield'][$element] ? $popins = " hlc" : $popins = false;
	
							if ((is_numeric($val) && GetExtraFieldType($field['id']) != "Reference to FlexTable" && GetExtraFieldType($field['id']) != "textbox" && !stristr(GetExtraFieldType($field['id']), "user") && !stristr(GetExtraFieldType($field['id']), "group")) || (GetExtraFieldType($field['id']) == "numeric" && ($val == 0 || trim($val) == ""))) {	// This is a numeric extra field, we'll add it up!
								$val = GetExtraFieldValue($e['eid'], $field['id'], true, true, $e['EFID' . $field['id']]);
								$dspval = FormatNumber($e['EFID' . $field['id']],2,$field['id']);
	
								//print " Add " . $val . " to " . $sums[$field['id']]['sum'] . " for field " . $sums[$field['id']]['name'] . " entity " . $e['eid'] . "<br>" ;
								$rawvalue = $e['EFID' . $field['id']];
	
								$element = "EFID" . $field['id'];
								if ($field['sum_column'] != "no") {
									if (!is_array($sums[$field['id']])) {
										$sums[$field['id']] = array();
										$sums[$field['id']]['name']			= $field['name'];
										$sums[$field['id']]['id']			= $field['id'];
										$sums[$field['id']]['tab_depth']	= $tab_depth;
									}
									if ((is_numeric($rawvalue) || $gh['value']=="0") && trim($rawvalue)!="") {
	
										$sums[$field['id']]['sum'] += $rawvalue;
										$sums[$field['id']]['to_sum'] = true;
	
									} else {
										// Skip this value
										if ($field['fieldtype'] == "numeric") {
											//$inst = "<span style='color: ff0000'>?</span>";
										} else {
											unset($sums[$field['id']]);
										}
									}
									$to_sum = true;
								}
								$popins .= " rightalign ";
	
								$val = htme(FormatNumber($rawvalue,2,$field['id'])) . "";
	
								if (trim($rawvalue)=="") {
									$val = "";
								}
	
							} else {
								$val = GetExtraFieldValue($e['eid'], $field['id'], true, false, $e['EFID' . $field['id']]);
	
								if (GetExtraFieldType($field['id']) == "diary") {
									$dspval = "<div class=\"scrolldiv\">" . $val . "</div>";
								} elseif ($field['israwhtml'] != "y") {
									$dspval = htme($val);
								} else {
									$dspval = $val;
								}
	
							}
	
							if (in_array($field['id'], $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckExtraFieldAccess($field['id'], false, $e['eid']) == "ok" && CheckEntityAccess($e['eid']) == "ok" ) {
								$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], $field['id'], $val);
								$tmp_select_ins = $td_select_ins;
								unset($td_select_ins);
	
	
							} else {
	
	
							}
	
							$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
	
	
							if ($CellColor) {
								$textColorClass = ReturnClassnameForTextColorBasedOnBackgroundColor($CellColor);
	
								$outputbuffer .= "<td class=\"td_EFID" . $field['id'] . " " . $popins . " " . $textColorClass . " " . $interact_css . "\" " . $td_select_ins . " " . $td_select_ins2 . " style='background-color: " . $CellColor . ";' id='td_list_element_" . $field['id'] . "_" . $e['eid'] . "'>" . $dspval . " " . $inst . "</td>";
							} else {
								$outputbuffer .= "<td class=\"td_EFID" . $field['id'] . " " . $popins . " " . $interact_css . "\" " . $td_select_ins . " " . $td_select_ins2 . " id='td_list_element_" . $field['id'] . "_" . $e['eid'] . "'>" . $dspval . " " . $inst . "</td>";
							}
	
							if ($tmp_select_ins) {
								$td_select_ins = $tmp_select_ins;
								unset($tmp_select_ins);
							}
							unset($inst);
							array_push($FA_data,$val);
							unset($val);
						}
					}
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_customer_contact\">" . htme($e['contact']) . "</td>";
					$tab_depth++;
					array_push($FA_data,$e['contact']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact_title']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_contact_title\">" . htme($e['contact_title']) . "</td>";
					$tab_depth++;
					array_push($FA_data,$e['contact_title']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact_phone']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_contact_phone\">" . htme($e['contact_phone']) . "</td>";
					$tab_depth++;
					array_push($FA_data,$e['contact_phone']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_contact_email']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_contact_email\">" . htme($e['contact_email']) . "</td>";
					$tab_depth++;
					array_push($FA_data,$e['contact_email']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust_address']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_cust_address\">" . htme($e['cust_address']) . "</td>";
					$tab_depth++;
					array_push($FA_data,$e['cust_address']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust_remarks']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_cust_remarks\">" . htme($e['cust_remarks']) . "</td>";
					$tab_depth++;
					array_push($FA_data,$e['cust_remarks']);
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_cust_homepage']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_cust_homepage\">" . htme($e['cust_homepage']) . "</td>";
					$tab_depth++;
					array_push($FA_data,$e['cust_homepage']);
				}
	
				if (sizeof($ExtraCustomerFieldsList)>0 && $ExtraCustomerFieldsList[0]<>"") {
					foreach ($ExtraCustomerFieldsList AS $field) {
						$field['optioncolors'] = GetExtraFieldOptioncolors($field['id']);
						$element = "EFID" . $field['id'];
						if ($GLOBALS['UC']['MainListColumnsToShow'][$element]) {
	
							$last_filter['pdfilterextrafield'][$element] <> "all" && $last_filter['pdfilterextrafield'][$element] ? $popins = " hlc" : $popins = false;

							if ($field['fieldtype'] == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
								$popins .= " rightalign";
							}
	
							$val = GetExtraFieldValue($e['CRMcustomer'], $field['id'], true, "dontformatnumbers");
	
							array_push($FA_data, $val);
							$tab_depth++;
	
							if ((is_numeric($val) && GetExtraFieldType($field['id']) != "Reference to FlexTable") || (GetExtraFieldType($field['id']) == "numeric" && $val == 0)) {	// This is a numeric extra field, we'll add it up!
								// This is a numeric extra field, we'll add it up!
	
								$val = $e['EFID' . $field['id']];
								$element = "EFID" . $field['id'];
	
								if ($field['sum_column'] != "no") {
									if (!is_array($sums[$field['id']])) {
										$sums[$field['id']] = array();
										$sums[$field['id']]['name'] = $field['name'];
										$sums[$field['id']]['id'] = $field['id'];
										$sums[$field['id']]['tab_depth'] = $tab_depth;
									}
									if (is_numeric($val) || $val == "0") {
										$sums[$field['id']]['sum'] += $val;
										$sums[$field['id']]['to_sum'] = true;
									}
									$to_sum = true;
								}
								$val = FormatNumber($val,2,$field['id']);
							}
							if ($field['fieldtype'] == "drop-down") {
								if ($field['optioncolors'][$val]) {
									$outputbuffer .= "<td class=\"" . $popins . " td_EFID" . $field['id'] . "\" " . $td_select_ins . " style='background-color: " . $field['optioncolors'][$val] . ";'>" . htme($val) . " " . $inst . "</td>";
								} else {
									$outputbuffer .= "<td class=\"" . $popins . " td_EFID" . $field['id'] . "\" " . $td_select_ins . ">" . htme($val) . " " . $inst . "</td>";
								}
							} else {
								if ($field['fieldtype'] != "diary" && $field['israwhtml'] != "y") {
									$val = htme($val);
								}
								$outputbuffer .= "<td class=\"" . $popins . " td_EFID" . $field['id'] . "\" " . $td_select_ins . ">" . $val . " " . $inst . "</td>";
							}
	
	
	
	
	
	
	
						}
					}
				}
	
				$fts = GetFlexTableDefinitions(false,"many-to-one", false, "entity");
				foreach ($fts AS $ft) {
					$list = GetExtraFlexTableFields($ft['recordid'], false, false);
					foreach ($list AS $field) {
						$varname = "EFID" . $field['id'];
						if ($GLOBALS['UC']['MainListColumnsToShow'][$varname]) {

							$last_filter['pdfilterextrafield'][$varname] <> "all" && $last_filter['pdfilterextrafield'][$varname] ? $popins = " hlc" : $popins = false;

							$reffield = GetReferencesToTable($ft['recordid'], "entity");
	
							if (is_numeric($reffield)) {
								$refrec = GetExtraFieldValue($e['eid'], $reffield, false, true, false);
								//$value = GetFlextableFieldValue($refrec, $field['id'], $ft['recordid'], "dontformatnumbers", true, false);
								$value = GetExtraFieldValue($refrec, $field['id'], true, "dontformatnumbers", false);
							} else {
								$value = "#REF! $reffield";
							}
	
							array_push($FA_data,"" . $field['name'] . "");
							$tab_depth++;
							if ((is_numeric($val) && GetExtraFieldType($field['id']) != "Reference to FlexTable") || (GetExtraFieldType($field['id']) == "numeric" && $value == 0)) {	// This is a numeric extra field, we'll add it up!
								// This is a numeric extra field, we'll add it up!
	
								$element = "EFID" . $field['id'];
	
								if ($field['sum_column'] != "no") {
									if (!is_array($sums[$field['id']])) {
										$sums[$field['id']] = array();
										$sums[$field['id']]['name'] = $field['name'];
										$sums[$field['id']]['id'] = $field['id'];
										$sums[$field['id']]['tab_depth'] = $tab_depth;
									}
									if (is_numeric($value) || $value == "0") {
										$sums[$field['id']]['sum'] += $value;
										$sums[$field['id']]['to_sum'] = true;
									}
									$to_sum = true;
								}
							}
	
							if (GetExtraFieldType($field['id']) == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
								$value = FormatNumber($value, 2, $field['id']);
								$class = " class=\"rightalign td_EFID" . $field['id'] . $popins . "\"";
							} else {
								$class = " class=\"td_EFID" . $field['id'] . $popins . "\"";
							}
	
							if ($field['fieldtype'] != "diary" && $field['israwhtml'] != "y") {
								$value = htme($value);
							}
							$outputbuffer .= "<td " . $class . " " . $td_select_ins . ">" . $value . "</td>";
	
						}
					}
				}
	
				$fts = GetFlexTableDefinitions(false,"one-to-many", false, "entity");
				$value = "";
				foreach ($fts AS $ft) {
					$list = GetExtraFlexTableFields($ft['recordid'], false, false);
	
					foreach ($list AS $field) {
						$varname = "EFID" . $field['id'];
						if ($GLOBALS['UC']['MainListColumnsToShow'][$varname] || $GLOBALS['UC']['MainListColumnsToShow']["SUM" . $varname]) {
							if ($GLOBALS['UC']['MainListColumnsToShow']["SUM" . $varname]) {
								
								$last_filter['pdfilterextrafield'][$varname] <> "all" && $last_filter['pdfilterextrafield'][$varname] ? $popins = " hlc" : $popins = false;

								$tmp = db_GetValue("SELECT SUM(" . $varname . ") FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE refer=" . $e['eid'] . " AND deleted!='y' AND " . $varname . " != ''");
								$outputbuffer .= "<td " . $td_select_ins . " class=\"rightalign nwrp td_EFID" . $field['id'] . "\">" . FormatNumber($tmp, false, $field['id']) . "</td>";
	
	//							FormatNumber($num, $decimals=2, $field_id=false, $dummy=false, $dummy=false, $dummy=false)
	
								$field['fieldtype'] = "numeric";
								$field['sum_column'] = "yes";
								$value = $tmp;
							} else {
								$tmp = db_GetArray("SELECT DISTINCT(" . $varname . "), COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft['recordid'] . " WHERE refer=" . $e['eid'] . " AND deleted!='y' AND " . $varname . " != '' GROUP BY " . $varname);
								foreach ($tmp AS $val) {
									if (strtolower(substr(GetExtraFieldType($field['id']), 0, 9)) == "user-list") {
										$val[0] = GetUserName($val[0]);
									}
									$value .= htme($val[0]) . " (" . $val[1] . ")<br />";
								}
								$outputbuffer .= "<td " . $td_select_ins . " class=\"rightalign nwrp " . $popins . " td_EFID" . $field['id'] . "\">" . ($value) . "</td>";
	
							}
							
							

							$tab_depth++;
							if (is_numeric($value) || (GetExtraFieldType($field['id']) == "numeric" && $value == 0)) {	// This is a numeric extra field, we'll add it up!
								// This is a numeric extra field, we'll add it up!
	
								$val = $e['EFID' . $field['id']];
								$element = "EFID" . $field['id'];
	
								if ($field['sum_column'] != "no") {
									if (!is_array($sums[$field['id']])) {
										$sums[$field['id']] = array();
										$sums[$field['id']]['name'] = $field['name'];
										$sums[$field['id']]['id'] = $field['id'];
										$sums[$field['id']]['tab_depth'] = $tab_depth;
									}
									if (is_numeric($value) || $value == "0") {
										$sums[$field['id']]['sum'] += $value;
										$sums[$field['id']]['to_sum'] = true;
	
									}
									$to_sum = true;
								}
							}
	
							array_push($FA_data,"" . $field['name'] . "");
							unset($value);
						}
					}
				}
	
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_numofattachments']) {
					$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp rightalign td_numofattm\">" . GetNumOfAttachments($e['eid']) . "</td>";
					array_push($FA_data,GetNumOfAttachments($e['eid']));
					$tab_depth++;
				}
	
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_startdate']) {
	
					$td1 = explode("-",$e['startdate']); // dd-mm-yyyy
					$startdate_EPOCH1 = @mktime(0,0,0,$td1[1],$td1[0],$td1[2]);
	
					$td2 = explode("-",$date); // dd-mm-yyyy
					$startdate_EPOCH2 = @mktime(0,0,0,$td2[1],$td2[0],$td2[2]);
					if (in_array("startdate", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
						$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "startdate", TransformDate($e['startdate']));
						$tmp_select_ins = $td_select_ins;
						unset($td_select_ins);
					} else {
						$dspval = TransformDate($e['startdate']);
					}
					$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
	
					$last_filter['pdfilterstartdate'] <> "all" && $last_filter['pdfilterstartdate'] <> "" ? $popins = " hlc" : $popins = false;
	
					 if ($e['startdate']==$date) {
									$tmp = "</span>";
									$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp" . $popins . " " . $interact_css . " td_startdate\" id='td_list_element_startdate_" . $e['eid'] . "'><span style='color: #ff3300'>";
								}
								elseif ($startdate_EPOCH1>$startdate_EPOCH2) {
									$tmp = "</span>";
									$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp" . $popins ." " . $interact_css . " td_startdate\" id='td_list_element_startdate_" . $e['eid'] . "'><span style='color: #669933; text-decoration: underline;'>";
								}
								else
								{
									$tmp = "";
									$outputbuffer .= "<td " . $td_select_ins . " style='width: 70px' class=\"nwrp" . $popins . " " . $interact_css . " td_startdate\">";
								}
					if ($e['startdate']=="") {
							$e['startdate'] = "<span style='color: #ff3300'>n/a</span>";
					}
					$outputbuffer .= $dspval . $tmp . "</td>";
					if ($tmp_select_ins) {
						$td_select_ins = $tmp_select_ins;
						unset($tmp_select_ins);
					}
					array_push($FA_data,TransformDate($e['startdate']));
					$tab_depth++;
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_duedate']) {
	
					$last_filter['pdfilterduedate'] <> "all" && $last_filter['pdfilterduedate'] <> "" ? $popins = " hlc" : $popins = false;
	
					$td1 = explode("-",$e['duedate']); // dd-mm-yyyy
					$DUEDATE_EPOCH1 = @mktime(0,0,0,$td1[1],$td1[0],$td1[2]);
	
					$td2 = explode("-",$date); // dd-mm-yyyy
					$DUEDATE_EPOCH2 = @mktime(0,0,0,$td2[1],$td2[0],$td2[2]);
	
					if (in_array("duedate", $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckEntityAccess($e['eid']) == "ok") {
						$dspval = ReturnInteractiveAjaxListFieldElement($e['eid'], "duedate", TransformDate($e['duedate']));
						$tmp_select_ins = $td_select_ins;
						unset($td_select_ins);
					} else {
						$dspval = TransformDate($e['duedate']) . "";
					}
					$interact_css = ($tmp_select_ins) ? "interactive_cell" : "";
	
					if ($e['duedate']==$date) {
						$classadd = " today";
	 				} elseif ($DUEDATE_EPOCH1<$DUEDATE_EPOCH2 && $e['duedate'] != "") {
						$classadd = " toolate";
					} else {
						$classadd = "";
					}
	
					$outputbuffer .= "<td " . $td_select_ins . " id=\"td_list_element_duedate_" . $e['eid'] . "\" style=\"width: 70px\" class=\"td_duedate nwrp " . $popins . " " . $interact_css . " " . $classadd . "\">";
	
					$outputbuffer .= $dspval . "</td>";
	
					if ($tmp_select_ins) {
						$td_select_ins = $tmp_select_ins;
						unset($tmp_select_ins);
					}
					array_push($FA_data,TransformDate($e['duedate']));
					$tab_depth++;
				}
				$classadd = "";
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_lastupdate']) {
	
					$last_filter['pdfilterlastupdate'] <> "all" && $last_filter['pdfilterlastupdate'] <> "" ? $popins = " hlc" : $popins = false;
	
					$t = $e['entity_lastchange']; // timestamp last edit
					$t = str_replace("-","",$t);
					$t = str_replace(" ","",$t);
					$t = str_replace(":","",$t);
					$tp['jaar'] = substr($t,0,4);
					$tp['maand'] = substr($t,4,2);
					$tp['dag'] = substr($t,6,2);
					$tp['uur'] = substr($t,8,2);
					$tp['min'] = substr($t,10,2);
					$cdate = explode("-",$ea['cdate']);
					// Add zero's ie. 2-7-2003 must become 02-07-2003
					if (strlen($cdate[0])==1) {
							$cdate[0] = "0" . $cdate[0];
					}
					if (strlen($cdate[1])==1) {
							$cdate[1] = "0" . $cdate[1];
					}
					// TransformDate($tp[dag]-$tp[maand]-$tp[jaar])
	
					$dspval = TransformDate($tp['dag'] . "-" . $tp['maand'] . "-" . $tp['jaar']) . " " . $tp['uur'] . ":" . $tp['min'] . "h.";
	
					//$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp td_lastupdate\">" . $lastupdate . "</td>";
					$outputbuffer .= "<td " . $td_select_ins . " id=\"td_list_element_lastupdate_" . $e['eid'] . "\" style=\"width: 70px\" class=\"td_lastupdate nwrp " . $popins . " " . $interact_css . " " . $classadd . "\">";
	
					$outputbuffer .= $dspval . "</td>";
					array_push($FA_data,$lastupdate);
					$tab_depth++;
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_creationdate']) {
					$last_filter['pdfiltercreationdate'] <> "all" && $last_filter['pdfiltercreationdate'] <> "" ? $popins = " hlc" : $popins = false;
	
	
					$outputbuffer .= "<td " . $td_select_ins . " id=\"td_list_element_creationdate_" . $e['eid'] . "\" style=\"width: 70px\" class=\"td_creationdate nwrp " . $popins . " " . $interact_css . " " . $classadd . "\">";
					$outputbuffer .= TransformDate(date('d-m-Y', $e['openepoch'])) . " " . date('H:i', $e['openepoch']) . "h.</td>";
					array_push($FA_data, TransformDate(date('d-m-Y', $e['openepoch'])) . " " . date('H:i', $e['openepoch']) . "h.");
					$tab_depth++;
				}
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_closedate']) {
					$last_filter['pdfilterclosedate'] <> "all" && $last_filter['pdfilterclosedate'] <> "" ? $popins = " hlc" : $popins = false;
					$outputbuffer .= "<td " . $td_select_ins . " class=\"nwrp td_closedate\">";
					if ($e['closeepoch'] > 0)
					{
						$outputbuffer .= TransformDate(date('d-m-Y', $e['closeepoch'])) . " " . date('H:i', $e['closeepoch']) . "h.";
					}
					$outputbuffer .= "</td>";
	
					array_push($FA_data,TransformDate(date('d-m-Y', $e['closeepoch'])) . " " . date('H:i', $e['closeepoch']) . "h.");
					$tab_depth++;
				}
	
				if ($GLOBALS['UC']['MainListColumnsToShow']['cb_duration']) {
					$tab_depth++;
					// age/duration calculation
					if ($e['closeepoch']==0) {
						$nowepoch = date('U');
						$txt = "Age";
					} else {
						$nowepoch = $e['closeepoch'];
						$txt = "Duration";
					}
	
					if ($e['openepoch']==0) {
						$age = "";
					} else {
						$age_in_seconds = $nowepoch - $e['openepoch'];
	
						$sums['duration']['tab_depth'] = $tab_depth;
						$sums['duration']['sum'] += $age_in_seconds;
						$sums['duration']['to_sum'] = true;
						$sums['duration']['count']++;
						$to_sum = true;
	
						if ($age_in_seconds>86400) {
							$age = "" . round($age_in_seconds/86400,2) . " days";
						} elseif ($age_in_seconds>3600) {
							$age = " " . round($age_in_seconds/3600,2) . " hrs";
						} elseif ($age_in_seconds>60) {
							$age = "" . round($age_in_seconds/60,2) . " min";
						} elseif ($age_in_seconds<>$nowepoch) {
							$age = "" . round($age_in_seconds,0) . " sec";
						}
					}
					$outputbuffer .= "<td " . $td_select_ins . " class=\"td_age_duration\">" . $age . "</td>";
					array_push($FA_data,$age);
	
				}
				foreach (GetButtons() AS $button) {
					if (GetAttribute("extrafield", "ShowButtonInList", $button['id']) == "Yes") {
						if ($button['displaylistname'] != "") $button['name'] = $button['displaylistname'];
	
	//					$tmp = GetTriggers("ButtonPress" . $button['id'], "", $e['formid'], $e['eid'], false, false);
						$tmp = db_GetValue("SELECT tid FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE onchange='ButtonPress" . $button['id'] . "'");
	
						if ($tmp[0] != "") { // there is a trigger valid with an action
	
							if (CheckExtrafieldConditions($e['eid'], $button['id'], "entity")) {
								$outputbuffer .= "<td class=\"td_EFID" . $button['id'] . "\" style=\"cursor: default\"><button id=\"JS_EFID" . $button['id'] . "\" onclick=\"" . $func . "&ProcessButton=" . $button['id'] . "&ProcessButtonEid=" . $e['eid'] . "&Pag_Moment=" . $_REQUEST['Pag_Moment'] . "');return(false);\">" . htme($button['name']) . "</button></td>";
							} else {
								$outputbuffer .= "<td style=\"cursor: default\" class=\"td_EFID" . $button['id'] . "\"><button disabled=\"disabled\" id=\"JS_EFID" . $button['id'] . "\" onclick=\"return(false);\">" . htme($button['name']) . "</button></td>";
							}
						} else {
							$outputbuffer .= "<td class=\"td_EFID" . $button['id'] . "\" style=\"cursor: default\"><button disabled=\"disabled\" id=\"JS_EFID" . $button['id'] . "\" onclick=\"return(false);\">" . htme($button['name']) . "</button></td>";
						}
					}
				}
				if ($GLOBALS['Mass_Update'] == "Yes" && !in_array("NoMassUpdate",GetClearanceLevel($GLOBALS['USERID'])) && !$nofunctions) {
					$outputbuffer .= "<td class=\"td_massupdate\"><input type='checkbox' onclick=\"document.forms['SuperForm'].elements['nolink'].value='1';\" name='AlterObjectProperty[]' value='" . $e['eid'] . "'></td>";
					$tab_depth++;
				} else {
					//print "NONO: " . $GLOBALS['Mass_Update'];
				}
	
				$outputbuffer .= "</tr>";
	
				$totalbuffer .= $outputbuffer;
	
				if ($PrintedRowCounter==0) { $header = $outputbuffer; }
				unset($outputbuffer);
	
			array_push($FA_datat, $FA_data);
			unset($FA_data);
			$FA_data = array();
		} // end if !ListTemplate
	} // end foreach
	$td_select_ins = "";
	// Print sum of numeric fields
	$max_depth = $tab_depth;
	if ($to_sum) {

		$sumbuffer .= "<tr>";
		for ($i=1;$i<$max_depth+1;$i++) {

			$sts = "";

			foreach ($sums AS $field => $sum) {

				if ($sum['tab_depth'] == $i && $sum['to_sum'] == 1) { // Sum location matches tab depth

					if ($field == "duration") {

						$age_in_seconds = $sum['sum'] / $sum['count'];

						if ($age_in_seconds>86400) {
							$age = "" . round($age_in_seconds/86400,2) . "&nbsp;days";
						} elseif ($age_in_seconds>3600) {
							$age = " " . round($age_in_seconds/3600,2) . "&nbsp;hrs";
						} elseif ($age_in_seconds>60) {
							$age = "" . round($age_in_seconds/60,2) . "&nbsp;min";
						} elseif ($age_in_seconds<>$nowepoch) {
							$age = "" . round($age_in_seconds,0) . "&nbsp;sec";
						}

						$sts = "<img src='images/pixel.gif' width='40' height='1' alt=''>&lt;&gt;<br><strong>" .$age . "</strong>";

					} else {
						$sts = "<img src='images/pixel.gif' width='40' height='1' alt=''>+<br><strong>" . FormatNumber($sum['sum'],2,$field) . "</strong>";
					}
					$somethingdone = true;
				}
			}
			$sumbuffer .= "<td class=\"rightalign\">" . $sts . "</td>";
		}

		$sumbuffer .= "</tr>";
	}

	if ($somethingdone) $totalbuffer .= $sumbuffer;



	if (strtoupper($GLOBALS['Mass_Update']) == "YES" && $atleastonereadwrite && !in_array("NoMassUpdate",GetClearanceLevel($GLOBALS['USERID'])) && !$nofunctions) {

		$totalbuffer .= "<tr><td colspan='" . ($tab_depth + 2) . "' class='rightalign'><input type=\"checkbox\" class=\"checkall\"> [" . $lang['all'] . "]</div> " . $lang['withselected'] . ": ";
		$totalbuffer .= "<input type='hidden' name='FromURL' value='" . htme($_SERVER['HTTP_REFERER']) . "'>";

		$totalbuffer .= "<select name='SelectedAction' id='JS_SelectedAction'><option>-</option>";




		foreach (db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "statusvars ORDER BY listorder, varname") AS $status) {
			if ($GLOBALS['UC']['USER_ALLOWED_STATUSSES'][0] == "All" || in_array($status, $GLOBALS['UC']['USER_ALLOWED_STATUSSES'])) {
				$totalbuffer .= "<option style='background-color: " . GetStatusColor(GetStatusName($status)) . ";' value='s_" . $status . "'>" . htme($lang['mu_set'] . " " . $lang['status'] . " " . $lang['mu_to'] . " " . GetStatusName($status)) . "</option>";
				}
		}

		foreach (db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "priorityvars ORDER BY listorder, varname") AS $prio) {
			if ($GLOBALS['UC']['USER_ALLOWED_PRIORITIES'][0] == "All" || in_array($status, $GLOBALS['UC']['USER_ALLOWED_PRIORITIES'])) {
				$totalbuffer .= "<option style='background-color: " . GetPriorityColor(GetPriorityName($status)) . ";' value='s_" . $prio . "'>" . htme($lang['mu_set'] . " " . $lang['priority'] . " " . $lang['mu_to'] . " " . GetPriorityName($prio)) . "</option>";
				}
		}


		$totalbuffer .= "<option value='del'>" . $lang['delete'] . "</option>";

		$totalbuffer .= "<option value='undel'>" . $lang['undelete'] . "</option>";

		$tmp = ReturnListOfAllowedUsers(false, false, false, false);
		foreach ($tmp AS $user) {
			if ($user['HIDEFROMASSIGNEEANDOWNERLISTS'] == "n") {
				$totalbuffer .= "<option value='a_" . $user['id'] . "'>" . htme($lang['mu_set'] . " [" . strtolower($lang['assignee']) . "] " . $lang['mu_to'] . " [" . $user['FULLNAME']) . "]</option>";
			}
		}
		foreach ($tmp AS $user) {
			if ($user['HIDEFROMASSIGNEEANDOWNERLISTS'] == "n") {
				$totalbuffer .= "<option value='o_" . $user['id'] . "'>" . htme($lang['mu_set'] . " [" . strtolower($lang['owner']) . "] " . $lang['mu_to'] . " [" . $user['FULLNAME']) . "]</option>";
			}
		}
		$a = GetExtraFields();
		foreach ($a AS $ef) {
			if ($ef['fieldtype'] == "drop-down" || $ef['fieldtype'] == "checkbox") {
				$options = unserialize($ef['options']);
				foreach ($options AS $option) {
					if ($ef['fieldtype'] == "drop-down") {
							$ef['optioncolors'] = GetExtraFieldOptioncolors($field['id']);
							$t = $option;
							if ($ef['optioncolors'][$t]) {
								$template =" style='background-color: " . $ef['optioncolors'][$t] . "' ";
							} else {
								unset ($template);
							}
					}
					$totalbuffer .= "<option " . $template . " value='EFID_" . $ef['id'] . "_" . base64_encode($option) . "'>" . htme($lang['mu_set'] . " [" . $ef['name'] . "] " . $lang['mu_to'] . " [" . $option . "]") . "</option>";
				}
			}
		}

		foreach (LoadCustomerCache() AS $cust) {
			$totalbuffer .= "<option value='c_" . $cust['id'] . "'>" . htme($lang['mu_set'] . " [" . $lang['customer'] . "] " . $lang['mu_to'] . " [" . $cust['custname'] . "]") . "</option>";
		}

		foreach (GetExtraFields(false, true) AS $field) {
			if ($field['fieldtype'] == "Button") {
				$totalbuffer .= "<option value='pressButton_" . $field['id'] . "'>" . htme($lang['mu_pressbutton'] . " [" . $field['name'] . "]") . "</option>";
			}
		}
		$totalbuffer .= "</select>&nbsp;" . ReturnDropDownSearchField("JS_SelectedAction") . "&nbsp;<input type='submit' name='MassUpdateButton' value='Go!'></td></tr>";
	} else {
		qlog(INFO, "DESCISION: Not show MassUpdate end form: global: " . $GLOBALS['Mass_Update'] . " no-rw: " . $atleastonereadwrite);
	}
//	print "<!-- Header title -->";

	//$header_title = str_replace("@NUM_FOUND@",$Total_Entity_Found_Matching_Query . " " . $lang['entitiesfound'],$header_title);

	//$header_row = str_replace("@NUM_FOUND@",$TotalReturnedRows,$header_row);
	if ($filter_active == true) {
		$header_row = str_replace("@CLEARFILTERLINK@","<a class='arrow' onclick=\"" . $func . "ClearFilter=true&amp;fs=&amp;filter_id=" . $filter_id . "');\">" . strtolower($lang['clearfilter']) . "</a>&nbsp;",$header_row);
	} else {
		$header_row = str_replace("@CLEARFILTERLINK@","",$header_row);
	}
	$header_row = str_replace("@HEADER_TITLE@", $header_title . "&nbsp;&nbsp;", $header_row);

	print $before_outputbuffer;
	//print $header_title;
	// Print the header row
//	print "<!-- Header row -->";
	print $header_row;
	// Print the table itself
	print $totalbuffer;

	if ($total_entities_found<1) {
		print "<tr><td colspan='50'><br>" . $lang['noresults'] . "";
		if ($filter_active) {
			print "<br><br><a class='lock' onclick=\"" . $func . "ClearFilter=true&amp;filter_id=" . $filter_id . "');\">" . $lang['filterisactive'] . "</a>.";
		}
		print "</td></tr>";
	}

	if ($clipped) {
		print "<tr><td colspan='50'>No more than " . $GLOBALS['CLIPLISTAT'] . " results will be shown (var. CLIPLISTAT). Export this list to have all results.</td></tr>";
	}

	log_msg("Open entity list viewed","");
	print "</table>";
	print "</td></tr></table>"; // close listmastertable




	// End the MassUpdate form (always)
	print "<span><input type='hidden' name='nolink' value=''></span></div></form>";


}


function ShowCustomerList2($query=false, $searchword) {
	global $lang;

	$zindex = 500;
	$debug = false;
	if (isset($_REQUEST['debug']) && is_administrator()) {
		$debug = true;
	}

	$random_header_string = randomstring(12,4);
	// Populate date options list
	$datefilter = CreateDateFilterOptionsList();
	if ($debug) {
		print "<pre>";
		print_r($_REQUEST);
		print "</pre>";
	}
	//$last_filter['pdfilterextrafield']



	$cl = GetClearanceLevel();
	if (in_array("HideListExportIcons", $cl)) {
		$GLOBALS['NoIconsInMainList'] = true;
	} elseif($nofunctions) {
		$GLOBALS['NoIconsInMainList'] = true;
	}
	if (in_array("CustomerAdd", $cl) || is_administrator()) $displayAddLink = true;

	if ($_REQUEST['loadSavedSelection'] == "none" || $_REQUEST['fs'] != "" || isset($_REQUEST['ClearFilter'])) {
		SetAttribute("user", "LastCustomerListSelection", "", $GLOBALS['USERID']);
		$_GET['ClearFilter'] = 1;
		$grayedout = "";
		unset($_REQUEST['loadSavedSelection']);
		unset($_REQUEST['SavedSelection']);
	} elseif ($_REQUEST['loadSavedSelection'] == ""  && $_REQUEST['fs'] == "" && $searchword == "" && !isset($_REQUEST['ClearFilter']) && $_REQUEST['NoSelection'] == "") {
		$_REQUEST['loadSavedSelection'] = GetAttribute("user", "LastCustomerListSelection", $GLOBALS['USERID']);
		if ($debug) print "<h2>Load selection from attributes: " . $_REQUEST['loadSavedSelection'] . "</h2>";
	}


	if ($_REQUEST['loadSavedSelection'] != "") {

		if (strstr($_SERVER['HTTP_REFERER'], "loadSavedSelection=")) {
			$dontshowselections = true;
		} else {
			SetAttribute("user", "LastCustomerListSelection", $_REQUEST['loadSavedSelection'], $GLOBALS['USERID']);
		}

		
		$tmp = GetAttribute("system", "SavedCustomerListSelections", 1);
		$add = GetAttribute("user", "SavedCustomerListSelections", $GLOBALS['USERID']);

		if (!is_array($tmp)) $tmp = array();
		if (!is_array($add)) $add = array();

		if (is_array($add)) {
			$tmp = array_merge($tmp, $add);
		}
		$query = CreateQueryFromSavedSelection($tmp[$_REQUEST['loadSavedSelection']], "SavedCustomerListSelections");
		$st = PushStashValue(array("name" => $_REQUEST['loadSavedSelection'], "selectionArray" => $tmp[$_REQUEST['loadSavedSelection']]));
		$ss = "&SavedSelection=" . $st . "&";
		if (is_array($GLOBALS['UC']['CustomerListColumnsToShow'][$_REQUEST['loadSavedSelection']])){
			$GLOBALS['UC']['CustomerListColumnsToShow'] = $GLOBALS['UC']['CustomerListColumnsToShow'][$_REQUEST['loadSavedSelection']];
		}
		$func1 = "refresh_" . $_REQUEST['AjaxHandler'] . "('SavedSelection=" . $st . "&";
		$func2 = "');";
		$usingSavedSelection = $_REQUEST['loadSavedSelection'];
		//$VisualQuery = CreateVisualQueryRepresentation($tmp[$_REQUEST['loadSavedSelection']], $func1, $func2);
		$SelectColumnsInc = "&amp;CustomColumnOverrule=" . $usingSavedSelection;
		$sortattribute = "LastCustomerListSort " . $_REQUEST['loadSavedSelection'];
		$GLOBALS['ShowInactiveCustomers'] = "Yes";
	} elseif ($_REQUEST['SavedSelection'] != "" && !isset($_GET['ClearFilter'])) {
		$tmp = PopStashValue($_REQUEST['SavedSelection']);
		$query = CreateQueryFromSavedSelection($tmp['selectionArray'], "SavedCustomerListSelections");
		$ss = "&SavedSelection=" . htme(jsencode($_REQUEST['SavedSelection'])) . "&";
		$usingSavedSelection = $tmp['name'];
		$func1 = "refresh_" . $_REQUEST['AjaxHandler'] . "('SavedSelection=" . htme(jsencode($_REQUEST['SavedSelection'])) . "&";
		$func2 = "');";
		//$VisualQuery = CreateVisualQueryRepresentation($tmp['selectionArray'], $func1, $func2);
		$SelectColumnsInc = "&amp;CustomColumnOverrule=" . $usingSavedSelection;
		if (is_array($GLOBALS['UC']['CustomerListColumnsToShow'][$usingSavedSelection])){
			$GLOBALS['UC']['CustomerListColumnsToShow'] = $GLOBALS['UC']['CustomerListColumnsToShow'][$usingSavedSelection];
		}

		$sortattribute = "LastCustomerListSort " . $tmp['name'];

		$GLOBALS['ShowInactiveCustomers'] = "Yes";
	} else {
		$sortattribute = "LastCustomerListSort";
	}
	
	$selectionDescription = $tmp[$usingSavedSelection]['selectionDescription'];

	if (trim($selectionDescription) != "") {
		print "<h3>" . $selectionDescription . "</h3><br>";
	} elseif (GetAttribute("system", "CustomerListMainHeaderHTML", 2) != "{{none}}" && GetAttribute("system", "CustomerListMainHeaderHTML", 2) != "") {
		print "" . EvaluateTemplatePHP(GetAttribute("system", "CustomerListMainHeaderHTML", 2), false, false, false) . "";
	}

	if ($query) { // a query was given which limits all results to the results in this query
		$res = db_GetArray($query);
		if (count($res) > 0) {
			$pregiven_query_ins = " AND " . $GLOBALS['TBL_PREFIX'] . "customer.id IN(";
			$nf = "";
			foreach ($res AS $id) {
				if ($nf) $pregiven_query_ins .= ",";
				$pregiven_query_ins .= $id['id'];
				$nf = true;
			}
			$pregiven_query_ins .= ")";
		} else {
			// No results, show nothing (in a kinda rude way)
			$pregiven_query_ins .= " AND 1=0";
		}

	}




	log_msg("Customer overview accessed","");
	AddBreadCrum($lang['customer'], "index.php?ShowCustomerList");
	$highlight_color = "#939393";

	if ($func1) {
		$func = $func1;
	} else {
		$func = "refresh_" . htme($_REQUEST['AjaxHandler']) . "('";
	}

	$join = 0;

	$cjoins = "";

	if ($_REQUEST['fs'] || $searchword) {
		if (!$_REQUEST['fs']) {
			$_REQUEST['fs'] = $searchword;
		}
		$query = NormalCustomerSearch($_REQUEST['fs'], true);
		if ($debug) print "NormalCustomerSearch: " . $query . "<br>";

		$tmp = GetAttribute("user", "CustomerlistSearchWords", $GLOBALS['USERID']);
		if (!in_array(trim($_REQUEST['fs']), $tmp)) {
			$tmp[] = trim($_REQUEST['fs']);
			SetAttribute("user", "CustomerlistSearchWords", $tmp, $GLOBALS['USERID']);
		}
	} else {
		$query = "SELECT " . $GLOBALS['TBL_PREFIX'] . "customer.* FROM " . $GLOBALS['TBL_PREFIX'] . "customer LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "loginusers AS uj1 ON (" . $GLOBALS['TBL_PREFIX'] . "customer.customer_owner = uj1.id OR uj1.id IS NULL) WHERE ";
		if ($GLOBALS['ShowInactiveCustomers'] == "Yes" || $_REQUEST['fs'] != "") {
			$query .= "(" . $GLOBALS['TBL_PREFIX'] . "customer.active='yes' OR " . $GLOBALS['TBL_PREFIX'] . "customer.active='no')";
		} else {
			$query .= "(" . $GLOBALS['TBL_PREFIX'] . "customer.active='yes')";
		}
	}
	if (!$_REQUEST['ClearFilter']) {

		$tmp = GetAttribute("user", "LastCustomerListFilter", $GLOBALS['USERID']);

		if ($debug) {
			print "<pre>";
			print_r($tmp);
			print "</pre>";
		}

		if (is_array($tmp)) {
			if (!isset($_REQUEST['filter_owner'])) $_REQUEST['filter_owner']= $tmp['filter_owner'];
			if (!isset($_REQUEST['filter_active'])) $_REQUEST['filter_active']= $tmp['filter_active'];

			foreach (GetExtraCustomerFields() AS $field) {
				if (!isset($_REQUEST['filter_EFID' . $field['id']])) $_REQUEST['filter_EFID' . $field['id']] = $tmp['filter_EFID' . $field['id']];
			}

		}

		$tmp = GetExtraCustomerFields();
		foreach ($tmp AS $field) {
			$val = $_REQUEST['filter_EFID' . $field['id']];
			if ($_REQUEST['filter_EFID' . $field['id']] && ($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || ($field['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date"))) {
				$query .= RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'], $_REQUEST['filter_EFID' . $field['id']]);
				$filter_in_use = true;
				$last_filter['filter_EFID' . $field['id']] = $val;
			} else {
				if ($val != "" && $val != "all") {
					if ($field['fieldtype'] == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
						$call = $val;
						$el = explode(":", $call);
						if (!is_numeric($el[1])) $el[1] = 0;
						if (!is_numeric($el[2])) $el[2] = 0;
						$filter_in_use = true;
						switch ($el[0]) {
							case "RA":
								$query .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))>=" . mres($el[1]) . " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))<=" . mres($el[2]) . " ";
							break;
							case "GT":
								$query .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))>=" . mres($el[1]) . " ";
							break;
							case "LT":
								$query .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))<" . mres($el[1]) . " ";
							break;
							case "EQ":
								if ($el[1] == "") {
									$tataa .= " " . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . "='' AND ";
								} else {
									$query .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))=" . mres($el[1]) . " ";
								}
							break;
							case "GTNE":
								$query .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " AS DECIMAL(15,3))>" . mres($el[1]) . " ";
							break;
						}
						$filter_in_use = true;
						$last_filter['filter_EFID' . $field['id']] = $val;
					} else {
						$query .= " AND " . $GLOBALS['TBL_PREFIX'] . "customer.EFID" . $field['id'] . " = '" . mres($val) . "' ";
						$last_filter['filter_EFID' . $field['id']] = $val;
						$filter_in_use = true;
					}
				}
			}
		}



		$val = $_REQUEST['filter_active'];
		if ($val != "" && $val != "all") {
			$query .= " AND " . $GLOBALS['TBL_PREFIX'] . "customer.active = '" . mres($val) . "' ";
			$last_filter['filter_active'] = $val;
			$filter_in_use = true;
		}
		$val = $_REQUEST['filter_owner'];
		if ($val != "" && $val != "all") {
			$query .= " AND " . $GLOBALS['TBL_PREFIX'] . "customer.customer_owner = '" . mres($val) . "' ";
			$last_filter['filter_owner'] = $val;
			$filter_in_use = true;
		}
		if ($_REQUEST['ShowInlineSelectTable']) {
			$query .= " AND " . $GLOBALS['TBL_PREFIX'] . "customer.active='yes'";
		}

		if ($filter_in_use) {
			SetAttribute("user", "LastCustomerListFilter", $last_filter, $GLOBALS['USERID']);
		}
	} else {
		SetAttribute("user", "LastCustomerListFilter", array(), $GLOBALS['USERID']);
	}



	if (is_array($GLOBALS['UC']['LIMITTOCUSTOMERS'])) {
		$and_sql_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "customer.id IN (";
		foreach($GLOBALS['UC']['LIMITTOCUSTOMERS'] AS $cid) {
			if ($fst) {
				$and_sql_ins .= ",";
			}
			$and_sql_ins .= $cid;
			$fst = true;
		}
		$and_sql_ins .= ")";
		$query .= $and_sql_ins;
	}

	if ($pregiven_query_ins != "") {
		$query .= $pregiven_query_ins;
	}

	$type = GetExtraFieldType(str_replace("EFID", "", $_REQUEST['sort']));


	if ($type == "date" || $type == "date/time" || ($type == "Computation" && GetAttribute("extrafield", "ComputationOutputType", str_replace("EFID", "", $_REQUEST['sort'])) == "Date")) {
		//$s = " UNIX_TIMESTAMP(CONCAT(SUBSTR(" . mres($_REQUEST['sort']) . ",7,4), SUBSTR(" . mres($_REQUEST['sort']) . ",4,2), SUBSTR(" . mres($_REQUEST['sort']) . ", 1,2)))";
		$s = " STR_TO_DATE(" . mres($_REQUEST['sort'] ). ", '%d-%m-%Y') "
		;
	} elseif ($type == "numeric" || (GetExtraFieldType(str_replace("EFID", "", $_REQUEST['sort'])) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", str_replace("EFID", "", $_REQUEST['sort'])) == "Numeric")) {
		$s= "CAST(" . mres($_REQUEST['sort']) . " AS DECIMAL(15,3))";
	} else {
		$s = mres($_REQUEST['sort']);
	}



	if (trim($s) != "" && $_REQUEST['sort'] && !$_REQUEST['desc'] && IsValidCustomerField($_REQUEST['sort'])) {
		$query .= " ORDER BY " . $s;
		$sort = $_REQUEST['sort'];
		SetAttribute("user", $sortattribute, "" . $s, $GLOBALS['USERID']);
	} elseif (trim($s) != "" && $_REQUEST['sort'] && $_REQUEST['desc'] && IsValidCustomerField($_REQUEST['sort'])) {
		$query .= " ORDER BY " . $s . " DESC";
		$sort = $_REQUEST['sort'];
		$desc = true;
		SetAttribute("user", $sortattribute, "" . $s . " DESC", $GLOBALS['USERID']);
	} else {
		$getsort = GetAttribute("user", $sortattribute, $GLOBALS['USERID']);
		if (IsValidCustomerField(str_replace(" DESC", "", $getsort))) {
			$sort = " ORDER BY " . $getsort;
			//print "<h1>" . $getsort . "</h1>";
			$query .= $sort;
		}

	}


	if ($_REQUEST['sort'] == "customer_owner") {
		$query = str_replace("ORDER BY customer_owner", "ORDER BY uj1.FULLNAME", $query);
	}



	if ($debug) print "<h1>" . ($query) . "</h1><br>";

	$base_query = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE id IN (";
	$base_sort = " ORDER BY FIELD(id,";
	unset($nf);

	$results = db_GetArray($query);
	$TotalReturnedRows = count($results);

	// For security, cycle through all found customers and check if access is at least read-only (e.g. not "nok")
	for ($i=0;$i<$TotalReturnedRows;$i++) {
		if (CheckCustomerAccess($results[$i]['id']) == "nok") {
			unset($results[$i]);
		} else {
			if ($nf) {
				$base_query .= ",";
				$base_sort .= ",";

			}
			$base_query .= $results[$i]['id'];
			$base_sort .= $results[$i]['id'];

			$nf = true;
		}
	}
	$base_query .= ")" . $base_sort . ")";

	if ($debug) print "<h1>" . ($base_query) . "</h1><br>";
	$ActualResultRows = count($results);
	$FilteredResultRows = 0;

	for ($i=0;$i<$ActualResultRows;$i++) {
		if ($FilteredResultRows > $GLOBALS['PAGINATECUSTOMERLIST'] || $i < $_REQUEST['Pag_Moment']) {
			unset($results[$i]);
		} else {
			$FilteredResultRows++;
		}
	}

	// Use the new base query as input for other functions (PDF, Excel etc)
	$stashid = PushStashValue($base_query);

	$ExtraFieldsList = GetExtraCustomerFields();

	// Build up new GET array
	$req_ar = "";
	foreach ($ExtraFieldsList AS $pf) {
		if ($_REQUEST['filter_EFID' . $pf['id']]) {
			$req_ar .= "&amp;filter_EFID" . $pf['id'] . "=' + document.getElementById('id_filter_EFID" . $pf['id'] . "').options[getElementById('id_filter_EFID" . $pf['id'] . "').selectedIndex].value + '";
		}
	}
	if ($_REQUEST['filter_active']) {
		$req_ar .= "&amp;filter_active=' + document.getElementById('id_filter_active').options[getElementById('id_filter_active').selectedIndex].value + '";
	}
	if ($_REQUEST['filter_owner']) {
		$req_ar .= "&amp;filter_owner=' + document.getElementById('id_filter_owner').options[getElementById('id_filter_owner').selectedIndex].value + '";
	}
	if ($_REQUEST['sort']) {
		$req_ar_sort .= "&amp;sort=" . htme($_REQUEST['sort']);
		if ($_REQUEST['desc']) {
			$req_ar_sort .= "&amp;desc=1";
		}
	}
	if ($_REQUEST['fs']) {
		$req_ar .= "&amp;fs=" . htme($_REQUEST['fs']);
	}
	if ($_REQUEST['tab']) {
		$req_ar .= "&amp;tab=" . htme($_REQUEST['tab']);
	}
	print "<table class=\"listmastertable\"><tr><td>";
	print "<table width=\"100%\" class=\"listheadertable\"><tr><td class=\"nwrp\">";
	if ($GLOBALS['PAGINATECUSTOMERLIST'] <> "" && $GLOBALS['PAGINATECUSTOMERLIST'] <> 0) {
		$P_buffer2 .= "<button value='' onclick=\"" . $func . "&amp;Pag_Moment=" . ($_REQUEST['Pag_Moment'] - $GLOBALS['PAGINATECUSTOMERLIST']) . $req_ar . $req_ar_sort . "');\"";
		if ($_REQUEST['Pag_Moment'] == 0) {
			$P_buffer2 .= " disabled='disabled'";
		}
		$P_buffer2 .= ' class="prevpage">&laquo;</button>';

		$number_of_pages = ceil($ActualResultRows / $GLOBALS['PAGINATECUSTOMERLIST']);

		$P_buffer2 .= '&nbsp;';
		$P_buffer2 .= '<select class="pageselector">';
		for ($x = 1; $x <= $number_of_pages; $x++) {
			$Pag_Moment_dd = ($x - 1) * $GLOBALS['PAGINATECUSTOMERLIST'];
			$P_buffer2 .= '<option value="' . $Pag_Moment_dd . '"';
			if ($Pag_Moment_dd == $_REQUEST['Pag_Moment']) {
				$P_buffer2 .= ' selected="selected"';
			}
			$P_buffer2 .= ' onclick="' . $func . '&amp;Pag_Moment=' . $Pag_Moment_dd . $req_ar . $req_ar_sort . '\');">' . $x . '</option>';
		}
		$P_buffer2 .= '</select>';
		$P_buffer2 .= '&nbsp;';

		$P_buffer2 .= "<button value='' onclick=\"" . $func . "&amp;Pag_Moment=" . ($_REQUEST['Pag_Moment'] + $GLOBALS['PAGINATECUSTOMERLIST']) . $req_ar . $req_ar_sort . "');\"";
		if ($ActualResultRows > ($_REQUEST['Pag_Moment'] + $GLOBALS['PAGINATECUSTOMERLIST'])) {
		} else {
			$P_buffer2 .= " disabled='disabled'";
		}
		$P_buffer2 .= ' class="nextpage">&raquo;</button>';


		if ($ActualResultRows > ($GLOBALS['PAGINATECUSTOMERLIST'] + $_REQUEST['Pag_Moment'])) {
			$num = ($_REQUEST['Pag_Moment'] + $GLOBALS['PAGINATECUSTOMERLIST']);
		} else {
			$num = $ActualResultRows;
		}

		$P_buffer2 .= "&nbsp;" . ($_REQUEST['Pag_Moment'] + 1) . "-" . $num . " / " . $ActualResultRows . "&nbsp;";
	}

	print $P_buffer2;
	print "<form id='searchcustomerform' method='post' action='' onsubmit=\"" . $func . "fs=' + document.forms['searchcustomerform'].elements['fs'].value);return(false);\"><div class='showinline'><img src='images/searchbox.png' alt='' class='search_img'><input type='search' class='search_input search_input_wide autocomplete' onkeypress=\"TriggerOnchangeOnEnter(event,this);\" name='fs' value='" . htme($_REQUEST['fs']) . "' id='customersearch' onchange=\"" . $func . "fs=' + this.value);\"></div></form>&nbsp;&nbsp;" . $ActualResultRows . " " . $lang['resfound'];
	if (!$ref) {

		$ss = "<div id=\"JS_savedselections\" class=\"showinline\"> " . $lang['savedselection'] . ": <select ";
		if ($usingSavedSelection != "") {
			$ss .= "class='highlightedselectbox'";
		}
		$ss .= " name='ssSelect' onchange=\"" . $func . "&loadSavedSelection=' + this.options[this.selectedIndex].value);\"><option value='none'>" . $lang['none'] . "</option>";
		if ($usingSavedSelection == "n/a") {
			$ss .= "<option selected='selected' value=''>n/a</option>";
		}
		$foundsome = false;


		$tmp = GetAttribute("system", "SavedCustomerListSelections", 1);
		if (!is_array($tmp)) $tmp = array();
		$add = GetAttribute("user", "SavedCustomerListSelections", $GLOBALS['USERID']);

		if (is_array($add)) {
			$tmp = array_merge($tmp, $add);
		}
		foreach ($tmp AS $savedSelectionName => $ignore) {
				if ($usingSavedSelection == $savedSelectionName) {
					$ins = "selected='selected'";
				} else {
					$ins = "";
				}
				$ss .= "<option " . $ins . " value='" . htme($savedSelectionName) . "'>" . htme($savedSelectionName) . "</option>";
				$foundsome = true;
		}
		$ss .= "</select></div>";

		if ($foundsome && !$dontshowselections) {
			print $ss;
		} else {

		}


	}
	if (CheckFunctionAccess("AddEditSelections") != "nok" && isset($_POST['mainList']) && !$_REQUEST['ShowInlineSelectTable'] & !$dontshowselections) {
		print " [<a onclick=\"PopFancyBoxLarge('Interleave advanced selection builder', 'index.php?ShowAdvancedQueryInterface&ListId=SavedCustomerListSelections&ParentEntityListAjaxHandler=" . htme($_REQUEST['AjaxHandler']) . "');\">" . $lang['selections'] . "</a>] ";
	}
	if ($filter_in_use) {
		print "&nbsp;&nbsp;<a class='arrow' onclick=\"" . $func . "ClearFilter=true&amp;filter_id=" . $filter_id . "');\">" . strtolower($lang['clearfilter']) . "</a>";
	}

	if ($displayAddLink && !$_REQUEST['ShowInlineSelectTable']) print "&nbsp;&nbsp;<a class='arrow' href='customers.php?add=1&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "'>" . $lang['addcust'] . "</a>";

	print "</td>";

	if (!$_REQUEST['ShowInlineSelectTable']) {
		$export_t = "";
		$cl = GetClearanceLevel();

		if (strtoupper($GLOBALS['LetUserSelectOwnListLayout'])=="YES" && (is_administrator() || in_array("MaySelectColumns", $cl))) {
			$CurURL = base64_encode("index.php?ShowCustomerList");
			//$export_t = "<a href='choose_cols.php?dothis=personal&amp;what=CUST&amp;cur=" . $CurURL . "'><img alt='" . htme($lang['selectcolumns']) . "' title='" . htme($lang['selectcolumns']) . "' src='images/selectcolumns.gif'></a>";
			$html_ins = "&what=CUST" . $SelectColumnsInc;

			$export_t .= "<a onclick=\"popcolumnchooser('" . $_REQUEST['AjaxHandler'] . $html_ins . "');\"><img alt='" . htme($lang['selectcolumns']) . "' title='" . htme($lang['selectcolumns']) . "' src='images/selectcolumns.gif'></a>";

			$htmlins = "&amp;CustomColumnLayoutStash=" . PushStashValue(serialize($GLOBALS['UC']['CustomerListColumnsToShow']));
		}

			if (!$GLOBALS['NoIconsInMainList']) $export_t .= "<a " . PrintAltToolTipCode($lang['downloadsumcsv'] . " (direct)") . " href='csv.php?DlSsC" . $htmlins . "&amp;QiD=" . $stashid . "&amp;separator=RealExcel'><img src='images/excel_large.gif' alt=''></a>";

				if ($_REQUEST['CustomColumnLayout'] != "") {
					$cclo = $_REQUEST['CustomColumnLayout'];
				} else {
					$cclo = $lang['customers'];
				}

			if (!$GLOBALS['NoIconsInMainList']) $export_t .= "<a " . PrintAltToolTipCode($lang['downloadsumcsv'] . " (choose fields)") . " onclick=\"popcolumnchooser('" . $_REQUEST['AjaxHandler'] . "','" . $stashid . "','" . $cclo . " - " . $lang['downloadsumcsv'] . "','customer');\"><img src='images/excel_large_double.gif' alt=''></a>";


//	if (!$GLOBALS['NoIconsInMainList']) $export_t .= "<a href=\"#\" onclick=\"popPDFwindow('customers.php?pdf=1&amp;close=1&amp;stashid=" . $stashid . "');\" title='Use results for PDF report'><img src='images/pdf.gif' alt=''></a>&nbsp;&nbsp;";
	}
	print "<td class='nowrp rightalign icons'>" . $export_t . "</td><tr>";
	print "</table>";
	
	print "</td></tr><tr><td>"; // listmastertable

	$header ="<table class='crm'><thead><tr>";
	// Counter to count the no. of printed columns
	$columnindex = array();

	if ($sort == "id" && !$desc) {
		$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=id&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
	} elseif ($sort == "id") {
		$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=id" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
	} else {
		$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=id" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
	}
	if ($GLOBALS['ShowSortLink'] =="no") unset($link);

	$header .="<td><strong>" . $link . "CID</strong></td>";

	if (!is_array($GLOBALS['UC']['CustomerListColumnsToShow']) || sizeof($GLOBALS['UC']['CustomerListColumnsToShow'])==0) {
		$GLOBALS['UC']['CustomerListColumnsToShow']['cb_custname'] = true;
	}



	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_custname']) {
		if ($sort == "custname" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=custname&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "custname") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=custname" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=custname" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$header .="<td>";
		$header .=$link . "<strong>" . $lang['customer'] . "</strong>";
		$header .="</td>";
		$columnindex['CRMcustomer'] = "set";
	}
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact']) {
		if ($sort == "contact" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "contact") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$header .="<td class=\"nwrp\">" . $link . $lang['contact'] . "</td>";
		$columnindex['contact'] = "set";
	}
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_title']) {
		if ($sort == "contact_title" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_title&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "contact_title") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_title" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_title" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$header .="<td class=\"nwrp\">" . $link . $lang['contacttitle'] . "</td>";
		$columnindex['contacttitle'] = "set";

	}
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_phone']) {
		if ($sort == "contact_phone" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_phone&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "contact_phone") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_phone" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_phone" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$header .="<td class=\"nwrp\">" . $link . $lang['contactphone'] . "</td>";
		$columnindex['contactphone'] = "set";
	}
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_email']) {
		if ($sort == "contact_email" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_email&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "contact_email") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_email" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=contact_email" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$header .="<td class=\"nwrp\">" . $link . $lang['contactemail'] . "</td>";
		$columnindex['contactemail'] = "set";
	}
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_address']) {
		if ($sort == "cust_address" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_address&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "cust_address") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_address" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_address" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$header .="<td class=\"nwrp\">" . $link . $lang['customeraddress'] . "</td>";
		$columnindex['cust_address'] = "set";
	}
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_remarks']) {
		if ($sort == "cust_remarks" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_remarks&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_remarks" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		$header .="<td class=\"nwrp\">" . $link . $lang['custremarks'] . "</td>";
		$columnindex['cust_remarks'] = "set";
	}
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_homepage']) {
		if ($sort == "cust_homepage" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_homepage&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "cust_homepage") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_homepage" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=cust_homepage" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$header .="<td class=\"nwrp\">" . $link . $lang['custhomepage'] . "</td>";
		$columnindex['cust_homepage'] = "set";
	}

	$showclass = "";
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_active']) {

		if ($sort == "active" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=active&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "active") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=active" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=active" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$filtername = 'filter_active';

		$hl = "";
		$ins_link .= "<option value='all' " . $a . ">Active [" . $lang['all'] . "]" . "</option>";
		if ($_REQUEST[$filtername] == "yes") {
			$a = "selected='selected'";
			$showclass = " show_content";
					$hideclass="hide_content";
		} else {
			unset($a);
		}
		$ins_link .= "<option " . $a . " value='yes'>yes</option>";
		if ($_REQUEST[$filtername] == "no") {
			$a = "selected='selected'";
			$showclass = " show_content";
					$hideclass="hide_content";
		} else {
			unset($a);
		}

		$ins_link .= "<option " . $a . " value='no'>no</option>";
		$ins_link .= "</select></div>";


		$link .= "<div class='box_interactive_list_item" . $showclass . "' id='activeheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true&amp;fs=" . htme($_REQUEST['fs']) . $req_ar . "');\" name='" . $filtername . "' id='id_filter_active' " . $dis . " onblur=\"SetWidth('', this);\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>" . $ins_link;
		//Jeroen bug 20111830, fixed by removing fs=' + document.getElementById('fsid').value

		$link .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='activeheadertext' onmouseover=\"SwitchIAtableheader('activeheader" . $random_header_string . "','activeheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('activeheader" . $random_header_string . "','activeheadertext');\">" . htme("Active") . "</div>";

		$header .="<td class=\"nwrp\">" . $link . "</td>";
		$columnindex['active'] = "set";
	}
	$showclass = "";
	if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_owner']) {

		if ($sort == "customer_owner" && !$desc) {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=customer_owner&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
		} elseif ($sort == "customer_owner") {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=customer_owner" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
		} else {
			$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=customer_owner" . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
		}
		if ($GLOBALS['ShowSortLink'] =="no") unset($link);

		$filtername = 'filter_owner';

		$hl = "";
		$ins_link = "";
		$ins_link .= "<option value='all' " . $a . ">" . $lang['owner'] . " [" . $lang['all'] . "]" . "</option>";

		$sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "customer.id, customer_owner FROM " . $GLOBALS['TBL_PREFIX'] . "customer," . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE " . $GLOBALS['TBL_PREFIX'] . "customer.customer_owner = " . $GLOBALS['TBL_PREFIX'] . "loginusers.id ORDER BY FULLNAME";
		$result= mcq($sql,$db);

		$shown = array();
		$hl = "";
		while ($row= mysql_fetch_array($result)) {
			if ($row[1] == $_REQUEST['filter_owner']) {
					$a = "selected='selected'";
					$td = "<td class='nwrp highlightedtableheadercell'>";
					$showclass=" show_content";
			$hideclass=" hide_content ";


			} else {
					$a = "";

			}

			$realvalue = GetUserName($row['customer_owner']);
			if (!in_array($row[1], $shown) && $realvalue != "") {
				$ins_link .= "<option value='" . htme($row[1]) . "' " . $a . ">" . htme($realvalue) . "</option>";
				array_push($shown, $row[1]);
			}

		}
		$realvalue = "";
		$ins_link .= "</select></div>";


		$link .= "<div class='box_interactive_list_item" . $showclass . "' id='ownerheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true&amp;fs=" . htme($_REQUEST['fs']) . "&amp;" . $req_ar . "');\" name='" . $filtername . "' id='id_filter_owner' " . $dis . " onblur=\"SetWidth('', this);\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>" . $ins_link;

		$link .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='ownerheadertext' onmouseover=\"SwitchIAtableheader('ownerheader" . $random_header_string . "','ownerheadertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('ownerheader" . $random_header_string . "','ownerheadertext');\">" . htme($lang['owner']) . "</div>";

		$header .="<td class=\"nwrp\">" . $link . "</td>";
		$columnindex['customer_owner'] = "set";
	}
	foreach ($ExtraFieldsList AS $field) {
		$element = "EFID" . $field['id'];

		if ($GLOBALS['UC']['CustomerListColumnsToShow'][$element]) {
			$link = "";
			if ($sort == "EFID" . $field['id'] && !$desc) {
				$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=EFID" . $field['id'] ."&amp;desc=1" . $req_ar . "');return false;\"><img src='images/sorted_down.gif' width='11' height='13' alt='List is sorted ascending'></a>&nbsp;";
			} elseif ($sort == "EFID" . $field['id']) {
				$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=EFID" . $field['id'] ."" . $req_ar . "');return false;\"><img src='images/sorted_up.gif' width='11' height='13' alt='List is sorted descending'></a>&nbsp;";
			} else {
				$link = "<a href='#' onclick=\"" . $func . "fs=" . htme($_REQUEST['fs']) . "&amp;sort=EFID" . $field['id'] . $req_ar . "');return false;\"><img src='images/sort.gif' width='11' height='13' alt='Click to sort the list on this column'></a>&nbsp;";
			}
			if ($GLOBALS['ShowSortLink'] =="no") unset($link);

			if ($last_filter['pdfilterextrafield'][$element] <> "all" && $last_filter['pdfilterextrafield'][$element] <> "") {
				$td = "<td class='nwrp highlightedtableheadercell'>";
				$showclass=" show_content";
				$hideclass=" hide_content ";

			} else {
				$td = "<td class=\"nwrp\">";
				$showclass="";
				$hideclass="";

			}

			$locloc = "";
			
			if ($field['excludefromfilters'] != "y") {

				if ($field['fieldtype'] == "numeric" || (GetExtraFieldType($field['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
					$locloc = ReturnNumericfieldRangeSelectOptions($field['id'], $_REQUEST['filter_' . $element], $includedeleted);
					if ($_REQUEST['filter_' . $element] != "" && $_REQUEST['filter_' . $element] != "all") {
						$showclass=" show_content";
				$hideclass=" hide_content ";

					}
				} elseif ($field['fieldtype'] == "date" || $field['fieldtype'] == "date/time" || ($field['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Date")) {
					foreach ($datefilter AS $val => $text) {
						if ($_REQUEST['filter_' . $element] == $val) {
								$td = "<td class='nwrp highlightedtableheadercell'>";
								$showclass=" show_content";
				$hideclass=" hide_content ";

								$a = "selected='selected'";
						} else {
								$a = "";
						}
						$locloc .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
					}
				} else {
					$sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "customer.id, EFID" . $field['id'] . " FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE EFID" . $field['id'] . "!='' ORDER BY EFID" . $field['id'] . "";
					$result= mcq($sql,$db);

					$shown = array();
					$hl = "";
					$values_associative = array();


					while ($row= mysql_fetch_array($result)) {
						//if ($row[1]== $last_filter['EFID' . $field['id']]) {
						if ($_REQUEST['filter_EFID' . $field['id']] == $row[1] && $_REQUEST['filter_EFID' . $field['id']] != "all") {
								$a = "selected='selected'";
								$td = "<td class='nwrp highlightedtableheadercell'>";
								$showclass=" show_content";
								$hideclass=" hide_content ";


						} else {
								$a = "";

						}


						if (!in_array($row[1], $shown)) {
							$realvalue = GetExtraFieldValue($row['id'], $field['id'], false, false, $row[1]);
							$values_associative[$realvalue] = "<option value='" . htme($row[1]) . "' " . $a . ">" . htme($realvalue) . "</option>";
							array_push($shown, $row[1]);
						}

					}

					ksort($values_associative);
					$locloc = "";
					foreach ($values_associative as $key => $val)
					{
						$locloc .= $val;
					}
				}


				$filtername = 'filter_EFID' . $field['id'];
				$loc = "&nbsp;<div class='box_interactive_list_item" . $showclass . "' id='EFID" . $field['id'] . "header" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true" . $req_ar . "');\" name='" . $filtername . "' id='id_filter_EFID" . $field['id'] . "' onblur=\"SetWidth('', this, 'EFID" . $field['id'] . "header" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";

				$loc .= "<option value='all' " . $a . ">" . $field['name'] . " [" . $lang['all'] . "]" . "</option>";
				$loc .= $locloc;
				$loc .= "</select></div>";
				$header .=$td . $link . $loc . "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='EFID" . $field['id'] . "headertext' onmouseover=\"SwitchIAtableheader('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\" onmouseout=\"SwitchIAtableheaderBackForDelay('EFID" . $field['id'] . "header" . $random_header_string . "','EFID" . $field['id'] . "headertext');\" >" . htme($field['displaylistname']) . "</div>";

				//$header .="<td " . $hl . ">" . $link;
			} else {
				$header .= $td . $link . $loc . htme($field['displaylistname']);

			}
			unset($td);
			unset($link);
			unset($loc);
			$header .="</td>";
			$columnindex['EFID' . $field['id']] = $field['id'];
		}
	}
	if (!$_REQUEST['ShowInlineSelectTable']) {
		if (GetAttribute("system", "CustomerListAlwaysInPopup", 2) != "Yes") {
			$ActionFunc = "GoCustomer(";
		} else {
			$ActionFunc = "PopEditCustomerWindow(";
		}
		$header .="<td></td>";
		$columnindex['n/a'] = "set";
		$header .="<td></td>";
		$columnindex++;
		$columnindex['n/a'] = "set";
	} else {
		if ($_REQUEST['Table'] == "Cust") {
			$ActionFunc = "PutCustomerInFlextableForm('" . htme($_REQUEST['SelectField']) . "','" . htme($_REQUEST['ShowField']) . "',";
		} elseif ($_REQUEST['Table'] == "Entity") {
			$ActionFunc = "PutCustomerInEntityForm('" . htme($_REQUEST['SelectField']) . "','" . htme($_REQUEST['ShowField']) . "',";
		} elseif ($_REQUEST['Table'] > 0) {
			$ActionFunc = "PutReferInFlextableForm('" . htme($_REQUEST['SelectField']) . "','" . htme($_REQUEST['ShowField']) . "',";
		}
	}

	$header .="</tr></thead>";

	$numof = array();
	$sql = "SELECT CRMcustomer, count(*) from " . $GLOBALS['TBL_PREFIX'] . "entity group by CRMcustomer";
	$res = mcq($sql, $db);
	while ($row = mysql_fetch_array($res)) {
		$numof[$row['CRMcustomer']] = $row[1];
	}

	$totval = array();

	if ($_REQUEST['ListTemplate'] > 0) {
		$lte = GetTemplate($_REQUEST['ListTemplate']);
	} else {
		print $header;
	}

	foreach ($results AS $pb) {
			if ($lte != "") {
				print "" . ParseTemplateCustomer($lte, $pb['id'], false, "htme", false) . "";

			} else {	

				$localcolumncounter = 0;
				$a = "<tr onmouseover=\"style.background='#CCCCCC';\" onmouseout=\"style.background='#FFFFFF';\">";

				if ($pb['active'] == "no") {
					$a .= "<td><span style='color: #ff0000'>" . $pb['id'] . "</span></td>";
				} elseif (CheckCustomerAccess($pb['id']) == "readonly") {
					$a .= "<td><span style='color: #808080'>" . $pb['id'] . "</span></td>";
				} else {
					$a .= "<td>" . $pb['id'] . "</td>";
				}

				
				$localcolumncounter++;
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_custname']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['custname']) . "</td>";
					$localcolumncounter++;
				}
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['contact']) . "</td>";
					$localcolumncounter++;
				}
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_title']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['contact_title']) . "</td>";
					$localcolumncounter++;
				}
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_phone']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['contact_phone']) . "</td>";
					$localcolumncounter++;
				}
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_contact_email']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['contact_email']) . "</td>";
					$localcolumncounter++;
				}
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_address']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['cust_address']) . "</td>";
					$localcolumncounter++;
				}
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_remarks']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['cust_remarks']) . "</td>";
					$localcolumncounter++;
				}
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_cust_homepage']) {
					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['cust_homepage']) . "</td>";
					$localcolumncounter++;
				}
				$class = "";
				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_active']) {
					if ($_REQUEST['filter_active'] != "" && $_REQUEST['filter_active'] != "all") {
						$class .= " hlc";
					} else {
						$class = "";
					}

					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme($pb['active']) . "</td>";
					$localcolumncounter++;
				}
				$class = "";

				if ($GLOBALS['UC']['CustomerListColumnsToShow']['cb_owner']) {
					if ($_REQUEST['filter_owner'] != "" && $_REQUEST['filter_owner'] != "all") {
						$class .= " hlc";
					} else {
						$class = "";
					}


					$a .= "<td class='pointer " . $class . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\">" . htme(GetUserName($pb['customer_owner'])) . "</td>";
					$localcolumncounter++;
					$class = "";
				}
				if (sizeof($ExtraFieldsList)>0 && $ExtraFieldsList[0]!="") {
						foreach ($ExtraFieldsList AS $field) {
							$element = "EFID" . $field['id'];

							if ($GLOBALS['UC']['CustomerListColumnsToShow'][$element]) {

								$class = "pointer";

								$CellColor = GetExtraFieldColor($field['id'], $pb['EFID' . $field['id']]);

								$value = GetExtraFieldValue($pb['id'], $field['id'], true, "dontformatnumbers", $pb['EFID' . $field['id']]);

								if (is_numeric($value) && GetExtraFieldType($field['id']) != "textbox" && $field['sum_column'] != "no") {
									$totval[$localcolumncounter] += $value;
									$somenumstoshow = true;
								}
								$style = "";
								if ($CellColor) {
									$style = "style='background-color: " . $CellColor . ";' ";
									$class .= " " . ReturnClassnameForTextColorBasedOnBackgroundColor($CellColor);
								}

								if ($field['fieldtype'] == "numeric" || ($field['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $field['id']) == "Numeric")) {
									$class .= " rightalign";
									$value = FormatNumber($value,2,$field['id']);
								} elseif ($field['fieldtype'] == "diary") {
									$value = "<div class=\"scrolldiv\">" . $value . "</div>";
								}

								if ($_REQUEST['filter_EFID' . $field['id']] != "" && $_REQUEST['filter_EFID' . $field['id']] != "all") {
									$class .= " hlc";
								}

								if (in_array($field['id'], $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckCustomerAccess($pb['id']) == "ok") {
									$a .= "<td class='" . $class . " interactive_cell' " . $style . "'>" . ReturnInteractiveAjaxListFieldElement($pb['id'], $field['id'], $value) . "</td>";

								} else {
									if ($field['israwhtml'] != 'y') {
										$value = htme($value);
									}
									$a .= "<td " . $style . " id='td_list_element_" . $field['id'] . "_" . $pb['id'] . "' onclick=\"" . $ActionFunc . $pb['id'] . ",'" . htme(jsencode($pb['custname'])) . "');\" class='" . $class . "'>" . $value . "</td>";
								}





								$localcolumncounter++;
								$class = "";

							}
						}
				}
				if (!$_REQUEST['ShowInlineSelectTable']) {
					$a .= "<td><a href='edit.php?e=_new_&amp;SetCustTo=" . $pb['id'] . "'><img src='images/icon-add.gif' alt=''></a></td>";
					$a .= "<td><a href='index.php?ShowEntityList&amp;pdfiltercustomer=" . $pb['id'] . "&amp;includedeleted=true'><img src='images/list.png' alt=''></a> " . $numof[$pb['id']] . "</td>";
				}

				$a .= "</tr>";
				print $a;
				unset($a);
			
			} // end if templaye
				$teller++;
		}

	print "<tr>";
	$cc = 1;
	print "<td></td>";
	foreach ($columnindex AS $name => $field) {
		if (is_numeric($totval[$cc])) {
			print "<td class='rightalign'><img src='images/pixel.gif' width='40' height='1' alt=''>+<br><strong>" . FormatNumber($totval[$cc], 2, $field) . "</strong></td>";
		} else {
			print "<td></td>";
		}
		$cc++;
	}
	print "</tr>";

	if ($teller==0) {
		print "<tr><td colspan='20'>" . $lang['noresults'] . "</td></tr>";

	}
	print "</table>";
	print "</td></tr></table>"; // close listmastertable
	if ($filter_in_use && $teller==0) {
		print "<br><br><a class='lock' onclick=\"" . $func . "ClearFilter=true&amp;filter_id=" . $filter_id . "');\">" . $lang['filterisactive'] . "</a>.";
	}

}


function ShowFlexTableList($flextable, $ref=false, $filter=false, $excel=false, $customlink=false, $pdf=false, $allfields=false, $showaddlink=true, $dontpaginate=false, $given_query=false) {
	global $lang;

	if (!$flextable) {
		return(false);
	}
	$debug = false;
	if (isset($_REQUEST['debug']) && is_administrator()) {
		$debug = true;
	}

	if ($debug) {
		print "ShowFlexTableList($flextable, $ref=false, $filter=false, $excel=false, $customlink=false, $pdf=false, $allfields=false, $showaddlink=true, $dontpaginate=false, $given_query=false)";
	}

	$tmp = GetFlexTableDefinitions($flextable);
	$ft = GetFlexTableDefinitions($flextable, false);

	if ($tmp[0]['recordid'] == "") { // Non-existing table
		PrintAD("This table does not exist or you don't have access to it.");
		log_msg("ERROR: Somebody tried to view table $flextable but it doesn't exist!");
	} else {

		if ($debug) {
			print "<pre>";
			print_r($_REQUEST);
			print "</pre>";
		}

		if ($filter != "" || $_REQUEST['fs'] != "") {
			if ($filter == "") $filter = $_REQUEST['fs'];
			$GLOBALS['SesMem']['LastFT' . $flextable . 'FilterText'] = $filter;
		} elseif (!isset($_REQUEST['ClearFilter']) && !isset($_REQUEST['fs']) && $ft['0']['compact_view'] != "y") {
			$filter = $GLOBALS['SesMem']['LastFT' . $flextable . 'FilterText'];
		}
		if (isset($_REQUEST['ClearFilter'])) {
			$GLOBALS['SesMem']['LastFT' . $flextable . 'FilterText'] = "";
		}

	// Process in-line buttons
		if (is_numeric($_REQUEST['ProcessButton']) && IsValidFlexTableRecord($_REQUEST['ProcessButtonEid'], $flextable) && CheckFlextableRecordAccess($flextable, $_REQUEST['ProcessButtonEid']) != "nok") {

			// Check if the user has the rights to use this button. GetFlextableButtons() will only return allowed buttons by using GetExtraFlextableFields() as source.
				$x = GetFlextableButtons($_REQUEST['ProcessButton'], $flextable);
				if ($x['fieldtype'] == "Button") {
					// OK the button is in the list so it is allowed.
					qlog(INFO, "An extra field button was pressed. Processing triggers.");
					journal($_REQUEST['ProcessButtonEid'], "User pressed button " . $x['id'] . "::" . $x['name'] . " (from flextable list)", "flextable" . $flextable);
					ProcessTriggers("ButtonPress" . $_REQUEST['ProcessButton'],$_REQUEST['ProcessButtonEid'],"", false, $flextable);
					if ($GLOBALS['INTERRUPTMESSAGE'] != "") {
						print $GLOBALS['INTERRUPTMESSAGES'];
						$GLOBALS['INTERRUPTMESSAGE'] = false;
						$GLOBALS['INTERRUPTMESSAGES'] = "";
					}
				} else {

				}
		} else {

		}

		$ShowSortLinks = GetAttribute("flextable", "ShowSortLinks", $flextable);

		if (!$_REQUEST['SelectField']) {
			$ShowInlineDuplicateLink = GetAttribute("flextable", "ShowInlineDuplicateLink", $flextable);
			$ShowInlineDeleteLink = GetAttribute("flextable", "ShowInlineDeleteLink", $flextable);


			if ($ShowInlineDeleteLink == "Yes" && is_numeric($_REQUEST['deleteRow'])) {
				// User clicked inline delete button and has access to this record
				// DeleteFlexTableRow is secured by itself
				DeleteFlexTableRow($_REQUEST['deleteRow'], $flextable);
			}
		} else {

			$field = str_replace("EFID", "", $_REQUEST['SelectField']);

		}



		$ViewOnTable = GetAttribute("flextable", "ViewOnTable", $flextable);

		if ($_REQUEST['loadSavedSelection'] == "none" || $_REQUEST['fs'] != "" || $filter != "" || isset($_REQUEST['ClearFilter']) && $_REQUEST['NoSelection'] == "") {
			SetAttribute("user", "LastFT" . $flextable . "ListSelection", "", $GLOBALS['USERID']);
			$_GET['ClearFilter'] = 1;
			$grayedout = "";
			if ($_REQUEST['loadSavedSelection'] == "") {
				unset($_REQUEST['loadSavedSelection']);
			}
			unset($_REQUEST['SavedSelection']);
			SetAttribute("user", "FlexTableFilters FT" . $flextable, "", $GLOBALS['USERID']);
			if ($debug) DA("Unset selections: " . $filter);
		} elseif ($_REQUEST['loadSavedSelection'] == ""  && $_REQUEST['fs'] == "" && !isset($_REQUEST['ClearFilter']) && !$ref && $_REQUEST['NoSelection'] == "") {
			$_REQUEST['loadSavedSelection'] = GetAttribute("user", "LastFT" . $flextable . "ListSelection", $GLOBALS['USERID']);
			if ($debug) print "<h2>Load selection from attributes: " . $_REQUEST['loadSavedSelection'] . "</h2>";
		}
	
		$datefilter = CreateDateFilterOptionsList();

		if ($debug) DA("loadSavedSelection: " . $_REQUEST['loadSavedSelection']);


		if ($_REQUEST['loadSavedSelection'] != "" && $_REQUEST['loadSavedSelection'] != "none") {

			if (strstr($_SERVER['HTTP_REFERER'], "loadSavedSelection=")) {
				$dontshowselections = true;
			} else {
				SetAttribute("user", "LastFT" . $flextable . "ListSelection", $_REQUEST['loadSavedSelection'], $GLOBALS['USERID']);
			}
			


			$tmp = GetAttribute("system", "SavedSelectionsFlextable" . $flextable, 1);
			if (!is_array($tmp)) $tmp = array();
			$add = GetAttribute("user", "SavedSelectionsFlextable" . $flextable, $GLOBALS['USERID']);
			if (is_array($add)) {
				$tmp = array_merge($tmp, $add);
			}
			$given_query = CreateQueryFromSavedSelection($tmp[$_REQUEST['loadSavedSelection']], "SavedSelectionsFlextable" . $flextable);

			if ($debug) DA("Loaded saved selection " . $_REQUEST['loadSavedSelection'] . ": " . CreateQueryFromSavedSelection($tmp[$_REQUEST['loadSavedSelection']], "SavedSelectionsFlextable" . $flextable));
			


			$st = PushStashValue(array("name" => $_REQUEST['loadSavedSelection'], "selectionArray" => $tmp[$_REQUEST['loadSavedSelection']]));
			$ss = "&SavedSelection=" . $st . "&";
			$_REQUEST['CustomColumnLayout'] = $_REQUEST['loadSavedSelection'];

			$func = "refresh_" . $_REQUEST['AjaxHandler'] . "('SavedSelection=" . $st . "&";
			$func2 = "');";
			$usingSavedSelection = $_REQUEST['loadSavedSelection'];

			$sortattribute = "FlexTableSort FT" . $flextable . " " . $usingSavedSelection;


		} elseif ($_REQUEST['SavedSelection'] != "" && !isset($_GET['ClearFilter'])) {
			if ($debug) DA("Numeric saved selection");
			$tmp = PopStashValue($_REQUEST['SavedSelection']);

			$given_query = CreateQueryFromSavedSelection($tmp['selectionArray'], "SavedSelectionsFlextable". $flextable);
			$ss = "&SavedSelection=" . htme(jsencode($_REQUEST['SavedSelection'])) . "&";
			$usingSavedSelection = $tmp['name'];

			$func = "refresh_" . $_REQUEST['AjaxHandler'] . "('SavedSelection=" . htme(jsencode($_REQUEST['SavedSelection'])) . "&";
			$func2 = "');";

			$_REQUEST['CustomColumnLayout'] = $tmp['name'];

			$sortattribute = "FlexTableSort FT" . $flextable . " " . $usingSavedSelection;

		} else {

			$sortattribute = "FlexTableSort FT" . $flextable;
			$func = "refresh_" . htme($_REQUEST['AjaxHandler']) . "('";
			if ($debug) DA("No saved selection");
			
		}


		$selectionDescription = $tmp[$usingSavedSelection]['selectionDescription'];


		$ExtraSelectCondition = PopStashValue($_REQUEST['ExtraSelectCondition']);


		$RemoveExtraSelectCondionFromQueryLinkText = GetAttribute("flextable", "RemoveExtraSelectCondionFromQueryLinkText", $flextable);

		if ($ExtraSelectCondition != "" && $ExtraSelectCondition != "None") {
			$ExtraSelectCondition = "AND " . $ExtraSelectCondition;
			$_REQUEST['showaddlink'] = "false";
			if ($debug) DA("Addlink set to false (1)");
		} elseif ($_REQUEST['SkipExtraSelectCondition'] && $RemoveExtraSelectCondionFromQueryLinkText != "None" && $RemoveExtraSelectCondionFromQueryLinkText != "") {
			// Do nothing
			$ExtraSelectCondition = "";

			

			$AddExtraSelectCondionToQueryLinkText = GetAttribute("flextable", "AddExtraSelectCondionToQueryLinkText", $flextable);

			if ($AddExtraSelectCondionToQueryLinkText != "" && $AddExtraSelectCondionToQueryLinkText != "None") {
				$RemoveExtraSelectCondionFromQueryLink = "[ <a onclick=\"" . $func . "&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\">" . $AddExtraSelectCondionToQueryLinkText .  "</a> ]";
			}
			$func .= "&amp;SkipExtraSelectCondition=1&amp;";

		} else {
			$ExtraSelectCondition = GetAttribute("flextable", "ExtraSelectCondition", $flextable);
			if (strstr($ExtraSelectCondition, "<?php")) {

				if ($debug) DA("Eval ESC " . $ExtraSelectCondition);
				$ExtraSelectCondition = EvaluateTemplatePHP($ExtraSelectCondition);
				
			}
			if ($debug) DA("ESC: " . $ExtraSelectCondition);

			if ($ExtraSelectCondition != "" && $ExtraSelectCondition != "None") {
				$ExtraSelectCondition = "AND " . $ExtraSelectCondition;
				if ($RemoveExtraSelectCondionFromQueryLinkText != "None" && $RemoveExtraSelectCondionFromQueryLinkText != "") {
					$RemoveExtraSelectCondionFromQueryLink = "[ <a onclick=\"" . $func . "&amp;SkipExtraSelectCondition=1&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\">" . $RemoveExtraSelectCondionFromQueryLinkText	. "</a> ]";

				}

			} else {
				$ExtraSelectCondition = "";
			}

		}


		if ($given_query != "") { // a query was given which limits all results to the results in this query

			$res = db_GetArray($given_query);
			if (count($res) > 0) {
				$pregiven_query_ins = " AND " . $GLOBALS['TBL_PREFIX'] . "flextable" . $flextable . ".recordid IN(";
				$nf = "";
				foreach ($res AS $id) {
					if ($nf) $pregiven_query_ins .= ",";
					$pregiven_query_ins .= $id['BASE_RECORD'];
					$nf = true;
				}
				$pregiven_query_ins .= ")";
			} else {
				$pregiven_query_ins = " AND 1=0";
			}
		} 


		$random_header_string = randomstring(12,4);

		$AllowReferChanges = GetAttribute("flextable", "AllowReferChanges", $flextable);

		if ($_REQUEST['showaddlink'] == "false" || ($AllowReferChanges == "No" && !$ref && $ft[0]['orientation'] == "one_entity_to_many")) {
			$showaddlink = false;
			if ($debug) DA("Addlink set to false (2)");
		} else {
			if ($debug) DA("ARC: $AllowReferChanges OR: " . $ft[0]['orientation']);
		}

		if ($_REQUEST['customlink'] && !$customlink) {
			$customlink = $_REQUEST['customlink'];
		}

		$acc = CheckFlextableRecordAccess($flextable, "_new_", true);
		if ($acc != "ok") {
			$showaddlink = false;
			if ($debug) DA("Addlink set to false (3)");
		}



		if (is_numeric($ref)) {
			$cnt = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] ."flextable" . $flextable . " WHERE (deleted='n' OR deleted IS NULL) AND refer = " . mres($ref) . " " . $ExtraSelectCondition);

		} else {
			$cnt = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] ."flextable" . $flextable . " WHERE (deleted='n' OR deleted IS NULL)" . " " . $ExtraSelectCondition);
		}

		$cntfiles = db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] ."binfiles WHERE type='flextable" . $flextable . "'");




		qlog(INFO, "ShowFlexTableContents In Func");
		$excelsheet = array();
		$sep = "@@@@REALEXCEL@@@@";

		if ($customlink == "NOLINK") {
			$nolink = true;
		}


		if (CheckFlexTableAccess($flextable) == "ok" || CheckFlexTableAccess($flextable) == "readonly") {
			$st = $flextable;

			if ($_REQUEST['refer']) {
				$ref = $_REQUEST['refer'];
			}

			$filter = $_REQUEST['FilterTable'];

			$table_header_repeat = $ft[0]['tableheaderrepeat'];

			$fts = unserialize($ft[0]['table_layout']);

			if ($ft[0]['users_may_select_columns'] == "y") {
				$tmp = GetAttribute("user", "FlexTableColumns FT" . $flextable, $GLOBALS['USERID']);
				if (count($tmp) > 0) {
					$fts = $tmp;
				}
				if ($_REQUEST['CustomColumnLayout']) {
					$ccl = GetAttribute("user", "FlexTableColumns FT" . $flextable . " " . $_REQUEST['CustomColumnLayout'], $GLOBALS['USERID']);
					if (!is_array($ccl)) {
						$ccl = GetAttribute("system", "FlexTableColumns FT" . $flextable . " " . $_REQUEST['CustomColumnLayout'], 1);
					}
					if (is_array($ccl)) {
						$fts = $ccl;
					}
				}

			}

			if ($_REQUEST['sort'] == "" && !$_REQUEST['loadSavedSelection']) {

				$_REQUEST['sort'] = GetAttribute("user", $sortattribute, $GLOBALS['USERID']);
				if (strstr($_REQUEST['sort'], " DESC")) {
					$_REQUEST['desc'] = 1;
					$_REQUEST['sort'] = str_replace(" DESC", "", $_REQUEST['sort']);
				}
			} else {
				$toset = $_REQUEST['sort'];
				if ($_REQUEST['desc']) {
					$toset .= " DESC";
				}
				SetAttribute("user", $sortattribute, $toset, $GLOBALS['USERID']);
			}

			if (sizeof($fts)>0 && !$allfields) {
				// Table layout is given
				// print "Table layout is given";
			} else {
				// Just print all fields
				$paf = true;
			}
			$tret = "<table class=\"listmastertable\"><tr><td>@@@@LISTHEADER@@@@@";
			$tret .= "</td></tr><tr><td>"; // listmastertable
			$tret .= "<table class=\"crm\">";
			$tret .= "<thead><tr>";



			if (in_array("recordid", $fts) || $paf) {
				if ($_REQUEST['sort'] == "recordid" && !$_REQUEST['desc'] && $ShowSortLinks!="No" && !$pdf) {
					$link = "<a onclick=\"" . $func . "&sort=recordid&amp;desc=1&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sorted_down.gif' width='11' height='13' alt=''></a>&nbsp;";
				} elseif ($_REQUEST['sort'] == "recordid" && $ShowSortLinks!="No" && !$pdf) {
					$link = "<a onclick=\"" . $func . "&sort=recordid&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sorted_up.gif' width='11' height='13' alt=''></a>&nbsp;";
				} elseif ($ShowSortLinks!="No" && !$pdf) {
					$link = "<a onclick=\"" . $func . "&sort=recordid&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sort.gif' width='11' height='13' alt=''></a>&nbsp;";
				}
				if ($GLOBALS['ShowSortLink'] =="no") unset($link);
				if ($pdf) $link = "#";
				$tableheader .= "<td class=\"th_recordid\">" . $link . "</td>";
				$exceltmp .= "ID" . $sep;
			}

				if ($ft[0]['orientation'] == "many_entities_to_one") {

				} else {

					if ($ref) {
						qlog(INFO, "A Refer request was found, only showing concerning entities");
						$refer = $ref;
					} else if (in_array("refer", $fts)) {

						if ($_REQUEST['sort'] == "refer" && !$_REQUEST['desc'] && $ShowSortLinks!="No" && !$pdf) {
							$link = "<a onclick=\"" . $func . "&sort=refer&amp;desc=1&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sorted_down.gif' width='11' height='13' alt=''></a>&nbsp;";
						} elseif ($_REQUEST['sort'] == "refer" && $ShowSortLinks!="No" && !$pdf) {
							$link = "<a onclick=\"" . $func . "&sort=refer&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sorted_up.gif' width='11' height='13' alt=''></a>&nbsp;";
						} elseif ($ShowSortLinks!="No" && !$pdf) {
							$link = "<a onclick=\"" . $func . "&sort=refer&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sort.gif' width='11' height='13' alt=''></a>&nbsp;";
						}
						if ($GLOBALS['ShowSortLink'] =="no") unset($link);

						if ($_REQUEST['pdfilterreferfield'] != "" && $_REQUEST['pdfilterreferfield'] != "all") {
							$showclass=" show_content";
							$hideclass=" hide_content ";
						} else {
							$showclass=" ";
							$hideclass=" ";
						}
						$tableheader .= "<td class=\"th_refer\">";

						if ($ft[0]['refers_to'] == "customer") {
							//$tableheader .= $link . $lang['customer'];
							$myname = $lang['customer'];
							$exceltmp .= $lang['customer'] . $sep;
						} elseif ($ft[0]['refers_to'] == "entity") {
							//$tableheader .= $link . $lang['entity'] ;
							$myname = $lang['entity'] ;
							$exceltmp .= $lang['entity'] . $sep;
						} elseif (substr($ft[0]['refers_to'], 0,9) == "flextable") {
							//$tableheader .= $link . $ft[0]['tablename'];
							$exceltmp .= $ft[0]['tablename'] . $sep;
							$myname = $ft[0]['tablename'];
							$refs_to_flextable = str_replace("flextable", "", $ft[0]['refers_to']);
						}

						// REFER FILTER

						if ($ft[0]['showfilters'] == "y" && $header['excludefromfilters'] != "y" && $cnt < 100000 && !$skip_filter && !$pdf) {

							$maxlen = strlen($myname . " [" . $lang['all'] . "]");
							$leng = ($maxlen < 10) ? $maxlen : $maxlen = 10;

							$tableheader .= "<div class='box_interactive_list_item" . $showclass . "' id='referheader" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='pdfilterreferfield' " . $dis . " onblur=\"SetWidth('auto', this, 'referheader" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";
							$tableheader .= "<option value='all' " . $a . ">" . ($myname) . " [" . $lang['all'] . "]</option>";

							$tmp = db_GetFlatArray("SELECT DISTINCT(refer) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft[0]['recordid'] . " WHERE deleted!='y' ORDER BY refer");

							foreach ($tmp AS $referOption) {
								if ($ft[0]['refers_to'] == "customer") {
									$ret = CheckCustomerAccess($referOption);
									$value = GetCustomerName($referOption);
								} elseif ($ft[0]['refers_to'] == "entity") {
									$ret = CheckEntityAccess($referOption);
									$value = GetEntityCategory($referOption);
									$layout = $ft[0]['refer_field_layout'];
									$value = ParseTemplateEntity($layout, $referOption, false, false, false, "plain", false);

								} elseif (substr($ft[0]['refers_to'], 0,9) == "flextable") {
									$refs_to_flextable = str_replace("flextable", "", $ft[0]['refers_to']);
									$ret = CheckFlextableRecordAccess($refs_to_flextable, $referOption);
									//$value = GetParsedFlexRef($refs_to_flextable, $referOption, false);
									$layout = $ft[0]['refer_field_layout'];
									$value = ParseFlexTableTemplate($refs_to_flextable, $referOption, $layout, false, false, false, "plain") . " {}";

								}
								if ($ret == "ok" || $ret == "readonly") {

									$tableheader .= "<option value=\"" . $referOption . "\"";
									if ($_REQUEST['pdfilterreferfield'] == $referOption) {
											$tableheader .= " selected=\"selected\"";
									}
									$tableheader .= ">" . htme($value) . "</option>";
								}

							}
							$tableheader .= "</select></div>";
						}
						$tableheader .= $link . "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='referheadertext' onmouseover=\"SwitchIAtableheader('referheader" . $random_header_string . "','referheadertext');\" >" . htme($myname) . "";
						$tableheader .= "</td>";
					}

				}



				$zindex = 500;
				$highlight_color = $GLOBALS['DFT_FOREGROUND_COLOR'];


				$ftf = GetExtraFlexTableFields($st);
				$filter_id = "flextable" . $st;

				if ($ref) {
					$listfilter = GetAttribute("user", "FlexTableFilters FT" . $st . " Refer" . $ref, $GLOBALS['USERID']);
				} else {
					$listfilter = GetAttribute("user", "FlexTableFilters FT" . $st, $GLOBALS['USERID']);
				}

				if (!is_array($listfilter)) {
					$listfilter = array();
				}

				foreach($ftf as $field) {
					$element = "pdfilterextrafield" . $field['id'];
					if (isset($_REQUEST[$element])) {
						if ($_REQUEST[$element] == "all" || trim($_REQUEST[$element]) == "") {
							$listfilter[$field['id']] = "";
							$newfilter = true;

						} else {
							$listfilter[$field['id']] = $_REQUEST[$element];
							$newfilter = true;
						}
					}
				}

				if ($_REQUEST['ClearFilter']) {
					unset($listfilter);
				}



				if ($ref && is_array($listfilter) && $newfilter) {
					SetAttribute("user", "FlexTableFilters FT" . $st . " Refer" . $ref, $listfilter, $GLOBALS['USERID']);
				} elseif (is_array($listfilter) && $newfilter) {
					SetAttribute("user", "FlexTableFilters FT" . $st, $listfilter, $GLOBALS['USERID']);
				}

				if ($_REQUEST['ListTemplate'] > 0) {
					$lte = GetTemplate($_REQUEST['ListTemplate']);
				} else {
					foreach ($ftf AS $header) {
						if ($paf || in_array($header['id'], $fts)) {

							if ($_REQUEST['sort'] == "EFID" . $header['id'] && !$_REQUEST['desc'] && $ShowSortLinks!="No" && !$pdf) {
								$link = "<a onclick=\"" . $func . "&sort=EFID" . $header['id'] . "&amp;desc=1&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sorted_down.gif' width='11' height='13' alt=''></a>&nbsp;";
							} elseif ($_REQUEST['sort'] == "EFID" . $header['id'] && $ShowSortLinks!="No" && !$pdf) {
								$link = "<a onclick=\"" . $func . "&sort=EFID" . $header['id'] . "&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sorted_up.gif' width='11' height='13' alt=''></a>&nbsp;";
							} elseif ($ShowSortLinks!="No" && !$pdf) {
								$link = "<a onclick=\"" . $func . "&sort=EFID" . $header['id'] . "&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "');\"><img src='images/sort.gif' width='11' height='13' alt=''></a>&nbsp;";
							} else {
								$link = "";
							}
							if ($GLOBALS['ShowSortLink'] =="no") unset($link);

							if ($listfilter[$header['id']] != "" && $listfilter[$header['id']] != "all") {
								$tableheader .= "<td class=\"nwrp highlightedtableheadercell th_EFID" . $header['id'] . "\">";
								$showclass=" show_content";
								$hideclass=" hide_content ";

							} else {
								$tableheader .= "<td class=\"nwrp th_EFID" . $header['id'] . "\">";
								$showclass="";
								$hideclass="";

							}

							if ($ft[0]['showfilters'] == "y" && $header['excludefromfilters'] != "y" && $cnt < 100000 && !$skip_filter && !$pdf) {
								$tableheader .= $link;
								$maxlen = strlen($header['displaylistname'] . " [" . $lang['all'] . "]");
								$leng = ($maxlen < 10) ? $maxlen : $maxlen = 10;

								$tableheader .= "<div class='box_interactive_list_item" . $showclass . "' id='EFID" . $header['id'] . "header" . $random_header_string . "'><select style='z-index: " . $zindex-- . ";' onchange=\"" . $func . "&' + this.name + '=' + this.options[this.selectedIndex].value + '&amp;newfilter=true');\" name='pdfilterextrafield" . $header['id'] . "' " . $dis . " onblur=\"SetWidth('auto', this, 'EFID" . $header['id'] . "header" . $random_header_string . "');\" onmouseover=\"SetWidthDelayed('auto', this);this.focus();\" class='selectlistfilter' onmouseout='StopCount();'>";
								$tableheader .= "<option value='all' " . $a . ">" . ($header['displaylistname']) . " [" . $lang['all'] . "]</option>";

								$shown_values = array();
								$values_associative = array();

								if (GetExtraFieldType($header['id']) == "numeric" || (GetExtraFieldType($header['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $header['id']) == "Numeric")) {

									$tableheader .= ReturnNumericfieldRangeSelectOptions($header['id'], $listfilter[$header['id']], false, $refer);

									if ($listfilter[$header['id']] != "" && $listfilter[$header['id']] != "all") {
										$showclass=" show_content";
										$hideclass=" hide_content ";
									} else {
										$showclass=" ";
										$hideclass=" ";
									}
								} elseif (GetExtraFieldType($header['id']) == "date" || GetExtraFieldType($header['id']) == "date/time" || ($header['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $header['id']) == "Date")) {
									foreach ($datefilter AS $val => $text) {
										if ($listfilter[$header['id']] == $val) {
												$a = "selected='selected'";
										} else {
												$a = "";
										}
										$tableheader .= "<option value='" . htme($val) . "' " . $a . ">" . htme($text) . "</option>";
									}


								} else {

									$sql= "SELECT " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft[0]['recordid'] . ".recordid, EFID" . $header['id'] . ", " . $GLOBALS['TBL_PREFIX'] . "accesscache.result AS CachedResult FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft[0]['recordid'] . " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "accesscache ON ((" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".recordid=" . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.user=" . $GLOBALS['USERID'] . " AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.type='ft" . $ft[0]['recordid'] . "' AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.result != 'nok') OR " . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid IS NULL) WHERE (deleted='n' OR deleted IS NULL) " . " " . $ExtraSelectCondition;

									if ($refer) {
										$sql .= " AND refer='" . mres($refer) . "' ";
									}
									$sql .= " AND EFID" . $header['id'] . "!=''";
									//$sql .= " GROUP BY EFID" . $header['id'];

									//$sql .= " ORDER BY CONCAT(EFID" . $header['id'] . ")";
									// User-list en customer list moeten gesorteerd worden op user resp klant naam


									$result = mcq($sql,$db);

									while ($thisrow = mysql_fetch_array($result)) {
											//if (trim($thisrow['EFID' . $header['id']]) != "" && !in_array(trim($thisrow['EFID' . $header['id']]), $shown_values)) {
											if (!in_array($thisrow['EFID' . $header['id']], $shown_values)) {

												if ($ft[0]['skip_security'] == "y") {
													$auth = "ok";
												} else {

													if ($thisrow['CachedResult'] == "") {
														$auth = CheckFlextableRecordAccess($ft[0]['recordid'], $thisrow['recordid']);
													} else {
														$auth = $thisrow['CachedResult'];
													}

												}

												if ($auth == "ok" || $auth == "readonly") {

													//$value_to_show = GetFlextableFieldValue($thisrow['recordid'], $header['id'], $flextable, false, true, $thisrow['EFID' . $header['id']]);
													$value_to_show = GetExtraFieldValue($thisrow['recordid'], $header['id'], true, false, $thisrow['EFID' . $header['id']]);


													$optionpart = '<option value="' . htme($thisrow['EFID' . $header['id']]) . '"';
													if ($thisrow['EFID' . $header['id']] == $listfilter[$header['id']]) {
														$optionpart .= ' selected="selected"';
													}
													$optionpart .= '>' . htme($value_to_show) . '</option>';

													$values_associative[$value_to_show] = $optionpart;

													//$shown_values[] = trim($thisrow['EFID' . $header['id']]);
													$shown_values[] = $thisrow['EFID' . $header['id']];

												}
												// else {
													// No access to this record

												//}

										}
										// else {
											// Skip check for performance, value was already shown

										//}
									}
								}
								ksort($values_associative);

								foreach ($values_associative as $key => $val)
								{
									$tableheader .= $val;
								}
								$tableheader .= "</select></div>";
							} else {
								$tableheader .= $link;
							}
							if ($pdf) {

								$tableheader .= "<div class='headerdivoverlay'>" . htme($header['displaylistname']) . "";

							} else {

								$tableheader .= "<div class='headerdivoverlay " . $hideclass . " " . $grayedout . "' id='EFID" . $header['id'] . "headertext' onmouseover=\"SwitchIAtableheader('EFID" . $header['id'] . "header" . $random_header_string . "','EFID" . $header['id'] . "headertext');\" >" . htme($header['displaylistname']) . "";
							}

							$tableheader .= "</div></td>";


							$exceltmp .= $header['displaylistname'] . $sep;
						}
					}

					//$tableheader = str_replace("<td", "<td class=\"nwrp\"", $tableheader);

					$tret .= $tableheader;
					array_push($excelsheet, strip_tags($exceltmp));
					unset($exceltmp);
					if (!$pdf) {
						foreach (GetFlextableButtons(false, $st) AS $button) {
							if (GetAttribute("extrafield", "ShowButtonInList", $button['id']) == "Yes") {
								if ($button['displaylistname'] != "") $button['name'] = $button['displaylistname'];
								$tret .= "<td>" . htme($button['name']) . "</td>";
							}
						}

						if ($ShowInlineDeleteLink == "Yes" && $ViewOnTable == "") {
							$tret .= "<td class=\"th_delete\">" . $lang['delete'] . "</td>";
						}
						if ($ShowInlineDuplicateLink == "Yes" && $ViewOnTable == "") {
							$tret .= "<td class=\"th_duplicate\">" . $lang['duplicate'] . "</td>";
						}
					}
					$tret .= "</tr></thead>";
				}

				if (!$pdf) {
					$tret .= "</thead>";
					//$tret .= "<tr height='1'><td height='1' colspan='122' style='background-color: " . $GLOBALS['DFT_FOREGROUND_COLOR'] . "'; border: 1px;></td></tr>";
				}

				if (is_numeric($ft[0]['sort_on']) && $ft[0]['sort_on'] != 0) {

					if ($ft[0]['sort_direction'] == "Descending") {
						$desc = "DESC";
					} else {
						unset($desc);
					}
					$type = GetExtraFieldType($ft[0]['sort_on']);

					if ($type == "date" || $type == "date/time" || ($type == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $ft[0]['sort_on']) == "Date")) {
						$sort = " ORDER BY UNIX_TIMESTAMP(CONCAT(SUBSTR(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . mres($ft[0]['sort_on']) . ",7,4), SUBSTR(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . mres($ft[0]['sort_on']) . ",4,2), SUBSTR(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . mres($ft[0]['sort_on']) . ", 1,2))) " . $desc;
					} elseif ($type == "numeric" || (GetExtraFieldType($ft[0]['sort_on']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $ft[0]['sort_on']) == "Numeric")) {
						$sort = " ORDER BY CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ft[0]['sort_on'] . " AS DECIMAL(15,3)) " . $desc;
					} else {
						$sort = " ORDER BY CONCAT(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ft[0]['sort_on'] . ") " . $desc;
					}

				} else {

					if ($ft[0]['sort_direction'] == "Descending") {
						$desc = "DESC";
					} else {
						unset($desc);
					}

					$sort = " ORDER BY " . $GLOBALS['TBL_PREFIX'] . "flextable" . $ft[0]['recordid'] . ".recordid " . $desc;
				}



				unset($nf);
				$and_ins = "";
				$joins = "";
				$join = 0;
				$firstdone = "";
				if ($filter) {

					$origfilter = $filter;

					$tmp = GetAttribute("user", "Flextable" . $flextable . "SearchWords", $GLOBALS['USERID']);
					if (!in_array(trim($filter), $tmp)) {
						$tmp[] = trim($filter);
						SetAttribute("user", "Flextable" . $flextable . "SearchWords", $tmp, $GLOBALS['USERID']);
					}


					 $filterlist = explode(" ", trim($filter));
					foreach ($filterlist AS $filter) {
						if (substr($filter,0,1) == "-") {
							$invert = " NOT ";
							$filter = substr($filter, 1, strlen($filter)-1);
						} else {
							$invert = "";
						}
						unset($nf);
						if ($firstdone) $and_ins .= " AND ";

						$and_ins .= " ((";

						$firstdone = true;
						foreach ($ftf AS $field) {

							if ($nf) {
								if ($invert) {
									$and_ins .= " AND ";
								} else {
									$and_ins .= " OR ";
								}
							}
							$join++;
							if ($field['fieldtype'] == "User-list of all CRM-CTT users" || $field['fieldtype'] == "User-list of administrative CRM-CTT users" || strstr($field['fieldtype'] , "Users of profile ")) {
								$joins .= " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "loginusers AS tb" . $join . " ON (" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $field['id'] . "=tb" . $join . ".id OR tb" . $join . ".id IS NULL)";
								$and_ins .= " tb" . $join . ".FULLNAME LIKE '%" . mres($filter) . "%'";
								$nf = true;
							} elseif ($field['fieldtype'] == "List of all active customers") {
								$joins .= " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "customer AS tb" . $join . " ON (" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $field['id'] . "=tb" . $join . ".id OR tb" . $join . ".id IS NULL)";
								$and_ins .= " tb" . $join . ".custname " . $invert . " LIKE '%" . mres($filter) . "%'";
								$nf = true;
							} elseif ($field['fieldtype'] == "Reference to FlexTable") {

								$joins .= " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "flextable" . $field['options'] . " AS tb" . $join . " ON (" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $field['id'] . "=tb" . $join . ".recordid OR tb" . $join . ".recordid IS NULL)";
								$nf = false;
								foreach (GetExtraFlexTableFields($field['options'], false, false, false) AS $ef_field) {

									if ($nf) {
										if ($invert) {
											$and_ins .= " AND ";
										} else {
											$and_ins .= " OR ";
										}
									}
									$and_ins .= " tb" . $join . ".EFID" . $ef_field['id'] . " " . $invert . " LIKE '%" . mres($filter) . "%'";
									$nf = true;
								}
								$nf = true;
							} else {
								$and_ins .= " EFID" . $field['id'] . " " . $invert . " LIKE '%" . mres($filter) . "%'";
								$nf = true;
							}

						}


						$join++;

						if (GetAttribute("flextable", "IncludeParentTableInSearches", $ft[0]['recordid']) == "Yes" && $ft[0]['refers_to'] != "no_refer") {
							if ($debug) print "<h1> check in " . $ft[0]['refers_to'] . " also!</h1>";

							$joins .= " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . $ft[0]['refers_to'] . " AS tb" . $join . " ON (" . $GLOBALS['TBL_PREFIX'] ."flextable" . $st . ".refer = tb" . $join . "";
							if ($ft[0]['refers_to'] == "entity") {
								$joins .= ".eid OR tb" . $join . ".eid IS NULL)";
								foreach (GetExtraFields(false, false, false, false) AS $ef_field) {

									if ($nf) {
										if ($invert) {
											$and_ins .= " AND ";
										} else {
											$and_ins .= " OR ";
										}
									}
									$and_ins .= " tb" . $join . ".EFID" . $ef_field['id'] . " " . $invert . " LIKE '%" . mres($filter) . "%'";
									$nf = true;
								}
								$nf = true;
							} elseif ($ft[0]['refers_to'] == "customer") {
								$joins .= ".id OR tb" . $join . ".id IS NULL)";
								foreach (GetExtraCustomerFields() AS $ef_field) {

									if ($nf) {
										if ($invert) {
											$and_ins .= " AND ";
										} else {
											$and_ins .= " OR ";
										}
									}
									$and_ins .= " tb" . $join . ".EFID" . $ef_field['id'] . " " . $invert . " LIKE '%" . mres($filter) . "%'";
									$nf = true;
								}
								$nf = true;
							} elseif (substr($ft[0]['refers_to'],0,9) == "flextable") {
								$joins .= ".recordid OR tb" . $join . ".recordid IS NULL)";
								foreach (GetExtraFlexTableFields(str_replace("flextable", "", $ft[0]['refers_to']), false, false, false) AS $ef_field) {

									if ($nf) {
										if ($invert) {
											$and_ins .= " AND ";
										} else {
											$and_ins .= " OR ";
										}
									}
									$and_ins .= " tb" . $join . ".EFID" . $ef_field['id'] . " " . $invert . " LIKE '%" . mres($filter) . "%'";
									$nf = true;
								}
								$nf = true;
							}



						} else {
							if ($debug) print "<h1> don't check " . $ft[0]['refers_to'] . " : " . GetAttribute("flextable", "IncludeParentTableInSearches", $ft[0]['recordid']) . "  (" . $ft[0]['recordid'] . " " . $ft[0]['refers_to']  . ")</h1>";
						}
						$and_ins .= ")";

						if ($cntfiles > 0) { // This flextable has file attachements
							$and_ins .= " OR " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".recordid " . $invert . " IN (SELECT koppelid FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.filename LIKE '%" . mres($filter) . "%' AND " . $GLOBALS['TBL_PREFIX'] . "binfiles.type='flextable" . $st . "')";

							if ($GLOBALS['DISABLE_BINARY_SEARCH'] != "Yes" && !$force_speed) {
								$and_ins .= " OR " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".recordid " . $invert . " IN (SELECT koppelid FROM " . $GLOBALS['TBL_PREFIX'] . "binfiles WHERE " . $GLOBALS['TBL_PREFIX'] . "binfiles.type='flextable" . $st . "' AND extractedascii LIKE '%" . mres($filter) . "%')";

							}
						} else {
							//print "<h2>Skip binfiles</h2>";
						}
						$and_ins .= ")";
					}

				}




				$sql = "SELECT *," . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".recordid AS BASE_RECORD, " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".refer AS BASE_REFER, " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".readonly AS BASE_READONLY, " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".timestamp_last_change AS BASE_TIMESTAMP_LAST_CHANGE, " . $GLOBALS['TBL_PREFIX'] . "accesscache.result AS CachedResult FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . " LEFT OUTER JOIN " . $GLOBALS['TBL_PREFIX'] . "accesscache ON ((" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".recordid=" . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.user=" . $GLOBALS['USERID'] . " AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.type='ft" . $st . "' AND " . $GLOBALS['TBL_PREFIX'] . "accesscache.result != 'nok') OR " . $GLOBALS['TBL_PREFIX'] . "accesscache.eidcid IS NULL)" . $joins;

				//$sql = "SELECT *," . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".recordid AS BASE_RECORD, " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".refer AS BASE_REFER, " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".readonly AS BASE_READONLY, " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".timestamp_last_change AS BASE_TIMESTAMP_LAST_CHANGE FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . $joins;

				if ($ref && $and_ins) {
					$and_ins .= " AND " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".refer='" . mres($ref) . "'";
				} elseif ($ref) {
					$and_ins .= " " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".refer='" . mres($ref) . "'";
				}
				if ($and_ins) {
					$sql .= " WHERE " . $and_ins . " AND (" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".deleted='n' OR " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".deleted IS NULL)" . " " ;
				} else {
					$sql .= " WHERE (" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".deleted='n' OR " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".deleted IS NULL) " . " ";
				}


				if ($pregiven_query_ins) {
					$sql .= $pregiven_query_ins;
				}

				$sql .= " " . $ExtraSelectCondition;






				// hier
				foreach (GetExtraFlexTableFields($st) AS $ftfield) {
					if (trim($listfilter[$ftfield['id']]) != "" && $listfilter[$ftfield['id']] != "all") {

						if (GetExtraFieldType($ftfield['id']) == "numeric" || (GetExtraFieldType($ftfield['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $ftfield['id']) == "Numeric")) {

							$call = $listfilter[$ftfield['id']];



							$el = explode(":", $call);

							if (!is_numeric($el[1])) $el[1] = 0;
							if (!is_numeric($el[2])) $el[2] = 0;


							switch ($el[0]) {
								case "RA":
									$sql .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . " AS DECIMAL(15,3))>=" . mres($el[1]) . " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . " AS DECIMAL(15,3))<=" . mres($el[2]) . " ";
								break;
								case "GT":
									$sql .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . " AS DECIMAL(15,3))>=" . mres($el[1]) . " ";
								break;
								case "LT":
									$sql .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . " AS DECIMAL(15,3))<" . mres($el[1]) . " ";
								break;
								case "GTNE":
									$sql .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . " AS DECIMAL(15,3))>" . mres($el[1]) . " ";
								break;
								case "EQ":
									if ($el[1] == "") {
										$tataa .= " " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . "='' AND ";
									} else {
										$sql .= " AND CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . " AS DECIMAL(15,3))=" . mres($el[1]) . " ";
									}
								break;


								$listfilterapplied = true;

							}

						} elseif ((GetExtraFieldType($ftfield['id']) == "date" || GetExtraFieldType($ftfield['id']) == "date/time" || (GetExtraFieldType($ftfield['id']) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $ftfield['id']) == "Date")) && strstr($listfilter[$ftfield['id']], "@")) {
							$sql .= " " . RelativeEnglishDateToSQL($GLOBALS['TBL_PREFIX'] . "flextable" . $flextable . ".EFID" . $ftfield['id'], $listfilter[$ftfield['id']]) . " ";

						} else {
							$sql .= " AND " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".EFID" . $ftfield['id'] . " = '" . mres($listfilter[$ftfield['id']]) . "'";

							$listfilterapplied = true;
						}
					}
				}



				if (is_numeric($_REQUEST['pdfilterreferfield'])) {
					$sql .= " AND " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".refer = " . mres($_REQUEST['pdfilterreferfield']);
				}

				// Dirty hack, to avoid doublures
				$sql .= " GROUP BY " . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . ".recordid ";


				if ($_REQUEST['sort'] && $ShowSortLinks!="No") {
					if (GetExtraFieldType(str_replace("EFID", "", $_REQUEST['sort'])) == "date" || (GetExtraFieldType(str_replace("EFID", "", $_REQUEST['sort'])) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", str_replace("EFID", "", $_REQUEST['sort'])) == "Date")) {
						$sql .= " ORDER BY UNIX_TIMESTAMP(CONCAT(SUBSTR(" . mres($_REQUEST['sort']) . ",7,4), SUBSTR(" . mres($_REQUEST['sort']) . ",4,2), SUBSTR(" . mres($_REQUEST['sort']) . ", 1,2)))";
					} elseif (GetExtraFieldType(str_replace("EFID", "", $_REQUEST['sort'])) == "date/time") {
						$sql .= " ORDER BY " . mres($_REQUEST['sort']);
					} elseif (GetExtraFieldType(str_replace("EFID", "", $_REQUEST['sort'])) == "numeric" || $_REQUEST['sort'] == "recordid" || (GetExtraFieldType(str_replace("EFID", "", $_REQUEST['sort'])) == "Computation" && GetAttribute("extrafield", "ComputationOutputType", str_replace("EFID", "", $_REQUEST['sort'])) == "Numeric")) {
						$sql .= " ORDER BY CAST(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . "." . mres($_REQUEST['sort']) . " AS DECIMAL(15,3))";
					} else {
						$sql .= " ORDER BY CONCAT(" . $GLOBALS['TBL_PREFIX'] . "flextable" . $st . "." . mres($_REQUEST['sort']) . ")";
					}
					if ($_REQUEST['desc']) {
						$sql .= " DESC";
					}

				} else {
					$sql .= $sort;
				}

				$selection_without_limit = $sql;

				if ($debug) {
					print "Direct query: $selection_without_limit"; 
				}
				$query_without_limit = PushStashValue($sql);



				if ($ft[0]['skip_security'] == "y" && $ft[0]['maxrowsperpage'] > 0 && !$filter && !$excel && !$usingSavedSelection) {

					$limit_from = 0;
					if ( $_REQUEST['Pag_Moment'] > 0) {
						$limit_from = mres($_REQUEST['Pag_Moment']);
					}
					$sql .= " LIMIT " . $limit_from . "," . $ft[0]['maxrowsperpage'];
					$override_valid_rows = $cnt;
					$valid_rows = $cnt;
				} else {
					$valid_rows = 0;
				}

				
				$res = mcq($sql, $db);

				if ($debug) print "<h1>$sql -- " . ($selection_without_limit) . "</h1>";


				$selection_without_limit = PushStashValue($selection_without_limit);

				$rows_returned = 0;
				$count_rows = 0;

				if (!$dontpaginate) {
					$num = $ft[0]['maxrowsperpage'];
				}


				if (!$_REQUEST['Pag_Moment']) {
					$_REQUEST['Pag_Moment'] = 0;
				}

				$table_header_counter = 0;
				$printed_rows = 0;


				$ftss = GetExtraFlexTableFields($st);

				// start displaying results


				while ($row = mysql_fetch_array($res)) {

						$ret = "";
						$rows_returned++;
						$count_rows++;
						//qlog(INFO, "Flextable" . $st . " :: process row " . $count_rows);
						if ((($printed_rows >= $num) || ($valid_rows < $_REQUEST['Pag_Moment'])) && !$excel && $num != 0) {
							if ($cnt < 4000) {
								if ($ft[0]['skip_security'] == "y") {
									$auth = "ok";
								} else {
									if ($row['CachedResult'] == "") {
										$auth = CheckFlextableRecordAccess($st, $row['recordid']);
									} else {
										$auth = $row['CachedResult'];
									}
									if ($auth != "nok") {
										$valid_rows++;
									} else {
										if ($debug) print "Row " . $row['BASE_RECORD'] . " access denied because of cache record: " . $row['CachedResult'] . "<br>";
									}

								}


							} else {
								$valid_rows++;
							//	$dontshowtotal = true;
							}

						} else {
							if ($ft[0]['skip_security'] == "y") {
								$auth = "ok";
							} else {
								if ($row['CachedResult'] == "") {
									$auth = CheckFlextableRecordAccess($st, $row['recordid']);
								} else {
									$auth = $row['CachedResult'];
								}

							}
							if ($auth != "nok") {
								if ($table_header_counter == $table_header_repeat && $table_header_repeat != "" && $table_header_repeat != 0) {
									$aret = "<tr class='highlightedrow'>" . $tableheader . "</tr>";
									$table_header_counter = 1;
								} else {
									$table_header_counter++;
								}
								$printed_rows++;

								$valid_rows++;

								$printed = true;

								if ($_REQUEST['ListTemplate'] > 0) {
									$ret .= ParseFlexTableTemplate($flextable, $row['recordid'], $lte, false, true, false, "htme");
									$printed = true;
								} else {

									if ($_REQUEST['SelectField']) {
										
										$tmp = GetAttribute("extrafield", "BlindReferenceFieldLayout", str_replace("EFID", "", $_REQUEST['SelectField']));
										if ($tmp != "" && $tmp != "-- set blind reference field layout in extra field attributes --") {
											$tag = ParseFlextableTemplate($st, $row['BASE_RECORD'], $tmp, true, true, false, "htme")  . "";
										} else {
											$tag = GetParsedFlexRef($st, $row['BASE_RECORD'], true);
										}
										if ($pdf) {
											$ret = "<tr>";
										} elseif ($_REQUEST['PlainField']) {
											$ret = "<tr class=\"flextablerow\" style='cursor: pointer; background-color: #ffffff;' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\" onclick=\"SetFieldValFlexTable(parent.document.getElementById('JS_" . $_REQUEST['SelectField'] . "'),'" . $row['BASE_RECORD']. "',parent.document.getElementById('JS_" . $_REQUEST['SelectField'] . "ts'),'" . htme(jsencode($tag)) . "');parent.$.fancybox.close();parent.document.getElementById('JS_" . $_REQUEST['SelectField'] . "').onchange();\">";

										} else {
											$ret = "<tr class=\"flextablerow\" style='cursor: pointer; background-color: #ffffff;' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\" onclick=\"SelectField(parent.document.getElementById('JS_" . $_REQUEST['SelectField'] . "'),'" . $row['BASE_RECORD']. "','" . htme($tag) . "');parent.document.getElementById('JS_" . $_REQUEST['SelectField'] . "').onchange();parent.$.fancybox.close();\">";
										}
									} else {
										if ($pdf) {
											$ret = "<tr>";
										} elseif (!$customlink) {

											 if (($ft[0]['add_in_popup'] == "y" && $ref) || GetAttribute("flextable", "UsePopupAlsoWhenViewingPlainList", $flextable) == "Yes") {
												if ($refer == "") $refer = 0;
												$ret = "<tr class=\"flextablerow\" style='cursor: pointer; background-color: #ffffff;' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\" >";

												$td_onclick = " onclick='popflextableEditwindow(" . $flextable . "," . $refer . "," . $row['BASE_RECORD'] . ",\"" . htme($_REQUEST['AjaxHandler']) . "\"); return false;'";
											} elseif ($ViewOnTable != "") {
												$link = "#";
												$ret = "<tr class=\"flextablerow\" style='background-color: #ffffff;' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\" >";
											 } else {
												$link = "flextable.php?EditRecord=" . $row['BASE_RECORD'] . "&amp;refer=" . $refer . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;FlexTable=" . $st . "&amp;fromlist=" . htme($_REQUEST['fromlist']). "&Pag_Moment=" . htme($_REQUEST['Pag_Moment']) . "&FilterTable=" . htme($_REQUEST['FilterTable']);
												$ret = "<tr class=\"flextablerow\" style='cursor: pointer; background-color: #ffffff;' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\" >";

												$td_onclick = " onclick=\"document.location='" . $link . "'\"";
											 }
										} elseif (!$nolink) {

											$link = $customlink . "&amp;EditRecord=" . $row['BASE_RECORD'] . "&amp;refer=" . $refer . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;FlexTable=" . $st . "&amp;fromlist=" . htme($_REQUEST['fromlist']);
											$ret = "<tr class=\"flextablerow\" style='cursor: pointer;background-color: #ffffff;' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\" >";

											$td_onclick = " onclick=\"document.location='" . $link . "'\"";

										} elseif ($nolink) {

											$ret = "<tr class=\"flextablerow\" style='background-color: #ffffff;' onmouseover=\"style.background='#E9E9E9';\" onmouseout=\"style.background='#FFFFFF';\">";
										} else {
											print "{{ERROR AE726E0C}}";
										}


									}
									if (in_array("recordid", $fts) || $paf) {
										$ret .= "<td " . $td_onclick . " class=\"td_recordid\">" . $row['BASE_RECORD'] . "</td>";
										$exceltmp .= $row['BASE_RECORD'] . $sep;
									}

									if ($ft[0]['orientation'] == "many_entities_to_one") {

										// nothin'

									} elseif ((!$refer) && (in_array("refer", $fts))) {
											$extratd = true;
											if ($ft[0]['refers_to'] == "customer") {
													$reflay = ParseTemplateCustomer($ft[0]['refer_field_layout'], htme($row['BASE_REFER']), false, "htme", $flextable);
													$ret .= "<td " . $td_onclick . ">" . $reflay . "</td>";
													$exceltmp .= $reflay . $sep;
											} elseif ($ft[0]['refers_to'] == "entity") {
													$reflay = ParseTemplateEntity($ft[0]['refer_field_layout'], htme($row['BASE_REFER']), false, true, false, "htme");
													$ret .= "<td " . $td_onclick . ">" . $reflay . "</td>";
													$exceltmp .= $reflay . $sep;
											} elseif (substr($ft[0]['refers_to'], 0,9) == "flextable") {
													$reflay = ParseFlexTableTemplate(str_replace("flextable", "", $ft[0]['refers_to']), htme($row['BASE_REFER']), $ft[0]['refer_field_layout'], true, false, false, "htme");
													$ret .= "<td " . $td_onclick . ">" . $reflay . "</td>";
													$exceltmp .= $reflay . $sep;
											}

									}

									foreach ($ftss AS $ef) {
											if ($paf || in_array($ef['id'], $fts)) {
												$localalign = "";

												$val = $row['EFID' . $ef['id']];

												if (trim($listfilter[$ef['id']]) != "" && $listfilter[$ef['id']] != "all") {
													$popins = " hlc";
												} else {
													$popins = false;
												}


												//$htmlval = GetFlextableFieldValue($row['recordid'], $ef['id'], $st, "dontformatnumbers", true, $val);
												$htmlval = GetExtraFieldValue($row['BASE_RECORD'], $ef['id'], true, "dontformatnumbers", $val);


												if (1 == 1) {

													$origval = $htmlval;


													if ($ef['fieldtype'] == "numeric" || ($ef['fieldtype'] == "Computation" && GetAttribute("extrafield", "ComputationOutputType", $ef['id']) == "Numeric")) {
														$totval[$ef['id']] += $val;
														$somenumstoshow = true;
														$insclass = " rightalign";
														$htmlval = FormatNumber($htmlval,2,$ef['id']);
													} else {
														$insclass = "";
													}
													$interactive = false;

													$CellColor = GetExtraFieldColor($ef['id'], $val);

													$insclass .= " " . ReturnClassnameForTextColorBasedOnBackgroundColor($CellColor);
													if (in_array($ef['id'], $GLOBALS['UC']['INTERACTIVEFIELDSLIST']) && CheckFlextableRecordAccess($flextable, $row['BASE_RECORD']) == "ok") {
														$insclass .= " interactive_cell";
														$interactive = true;
													}
													$id = "id='td_list_element_" . $ef['id'] . "_" . $row['BASE_RECORD'] . "' ";
													if ($CellColor) {
														$ret .= "<td " . $id . $localalign . " ";
														if (!$interactive) $ret .= $td_onclick;
														$ret .= " class=\"" . $popins . $insclass . " td_EFID" . $ef['id'] . "\" style='background-color: " . $CellColor . "'>";
													} else {
														$ret .= "<td " . $id . $localalign . " ";
														if (!$interactive) $ret .= $td_onclick;
														$ret .= " class=\"" . $popins . $insclass . " td_EFID" . $ef['id'] . "\">";
													}


													//else {
													//	$ret .= "<td " . $id . $localalign . " class=\"" . $popins . $insclass . " td_EFID" . $ef['id'] . "\">";
													//}
													if (GetExtraFieldType($ef['id']) == "diary") {
														$ret .= "<div class=\"scrolldiv\">" . $htmlval . "</div>";
													} else {

														if ($interactive) {
															$ret .= ReturnInteractiveAjaxListFieldElement($row['BASE_RECORD'], $ef['id'], trim($htmlval));

														} else {
															if ($ef['israwhtml'] == "y") {
																$ret .= $htmlval;
															} else {
																$ret .= htme($htmlval);
															}
														}

													}
		// HIER

													//$ret .= $listfilter[$ef['id']];
													$ret .= "</td>";
													if ($pdf) {
														$exceltmp .= $htmlval . $sep;
													} else {
														$exceltmp .= $origval . $sep;
													}
												} else {
													$ret .= "<td " . $td_onclick . "></td>";
													$exceltmp .= "" . $sep;
												}

											}
										}
									if (!$pdf) {
										foreach (GetFlextableButtons(false, $st) AS $button) {
											if (GetAttribute("extrafield", "ShowButtonInList", $button['id']) == "Yes") {
												if ($button['displaylistname'] != "") $button['name'] = $button['displaylistname'];

												//$tmp = GetTriggers("ButtonPress" . $button['id'], "", $e['formid'], $e['eid'], false, false);
												$tmp = db_GetValue("SELECT tid FROM " . $GLOBALS['TBL_PREFIX'] . "triggers WHERE onchange='ButtonPress" . $button['id'] . "'");
												if ($tmp[0] != "") { // there is a trigger valid with an action

													if (CheckExtrafieldConditions($row['BASE_RECORD'], $button['id'], $st) && $auth == "ok") {
														$ret .= "<td class=\"td_EFID" . $button['id'] . "\" style=\"cursor: default\"><button id=\"JS_EFID" . $button['id'] . "\" onclick=\"" . $func . "&ProcessButton=" . $button['id'] . "&ProcessButtonEid=" . $row['BASE_RECORD'] . "');return(false);\">" . htme($button['name']) . "</button></td>";
													} else {
														$ret .= "<td class=\"td_EFID" . $button['id'] . "\" style=\"cursor: default\"><button disabled=\"disabled\" id=\"JS_EFID" . $button['id'] . "\" onclick=\"return(false);\">" . htme($button['name']) . "</button></td>";
													}
												} else {
													$ret .= "<td class=\"td_EFID" . $button['id'] . "\" style=\"cursor: default\"><button disabled=\"disabled\" id=\"JS_EFID" . $button['id'] . "\" onclick=\"return(false);\">" . htme($button['name']) . "</button></td>";
												}
											}
										}
										if ($ShowInlineDeleteLink == "Yes" && $ViewOnTable == "") {
											if (GetAttribute("flextable", "DeleteConfirmationMessage", $flextable) != "") {
												$insert1 = " if (confirm('" . addslashes(GetAttribute("flextable", "DeleteConfirmationMessage", $flextable)) . "')) { ";
												$insert2 = "}";
											} elseif ($GLOBALS['DELETE_CONFIRMATIONMSG']) {
												$insert1 = " if (confirm('" . addslashes($GLOBALS['DELETE_CONFIRMATIONMSG']) . "')) { ";
												$insert2 = "}";
											}
											$ret .= "<td class=\"centered td_delete\" style=\"width: 20px;\"><a onclick=\"" . $insert1 . $func . "&deleteRow=" . $row['BASE_RECORD'] . "&Pag_Moment=" . $_REQUEST['Pag_Moment'] . "&FilterTable=" . htme($_REQUEST['FilterTable']) . "');" . $insert2 . "\"><img src='images/deletes.gif' alt='" . $lang['delete'] . "'></a></td>";
										}
										if ($ShowInlineDuplicateLink == "Yes" && $ViewOnTable == "") {
											if ($ft[0]['add_in_popup'] == "y" && $ref) {

												$dup_onclick = " onclick=\"PopFancyBoxLarge('', 'flextable.php?AddToTable=" . $flextable . "&refer=" . $refer . "&nonavbar=1&AddInPopup=1&ParentAjaxHandler=" . $_REQUEST['AjaxHandler'] . "&templateRecord=" . $row['BASE_RECORD'] . "')\"";

												$ret .= "<td class=\"centered td_duplicate\" style=\"width: 20px;\"><img ". $dup_onclick . "src='images/duplicate.gif' alt='duplicate'></td>";
											 } else {
												$ret .= "<td class=\"centered td_duplicate\" style=\"width: 20px;\"><a href='flextable.php?AddToTable=" . $ft[0]['recordid'] . "&templateRecord=" . $row['BASE_RECORD'] . "');\"><img src='images/duplicate.gif' alt='duplicate'></a></td>";
											 }

										}
									}



									$ret .= "</tr>";
								} // end if ! template

								$aret .= $ret;
//								print "ADD " . htme($ret) . "<br><br>";
								array_push($excelsheet, strip_tags($exceltmp));
								unset($exceltmp);
								$tret .= $aret;
								$aret = "";

							} else {// SLUIT
								if ($debug) print "Row " . $row['BASE_RECORD'] . " access denied because of cache record: " . $row['CachedResult'] . "<br>";
							}
						}
				}
				
				if ($debug) DA("Returned rows: $rows_returned");
				if ($debug) DA("Valid rows: $valid_rows");

				if ($ft[0]['sumnumrows'] == "y" && $somenumstoshow == true && $printed) {
					
					$tret .= "<tr id=\"tr_summ\" class=\"nwrp\">";

					if (in_array("recordid", $fts) || $paf) {
						$tret .= "<td class=\"th_sum\"></td>";
						$exceltmp .= "" . $sep;
					}

					if ($extratd) {
						$tret .= "<td></td>";
						$exceltmp .= "" . $sep;
					}
					foreach (GetExtraFlexTableFields($st) AS $ef) {
						if (in_array($ef['id'], $fts) || $paf) {
							if (is_numeric($totval[$ef['id']]) && $ef['sum_column'] != "no") {
	
								$tret .= "<td class=\"rightalign\" id=\"summary_EFID" . $ef['id'] . "\"><span class=\"th_summary\" id=\"num_sum_EFID" . $ef['id'] . "\">" .htme(FormatNumber($totval[$ef['id']],2,$ef['id'])) . "</span></td>";

							} else {
								$tret .= "<td ></td>";
								$exceltmp .= "" . $sep;
							}
						} else {

						}
					}
					if ($ShowInlineDuplicateLink == "Yes") {
						$tret .= "<td></td>";
					}
					if ($ShowInlineDeleteLink == "Yes") {
						$tret .= "<td></td>";

					}

					$tret .= "</tr>";
					array_push($excelsheet, str_replace("^^^LINEBREAK^^^", "<br />", strip_tags($exceltmp)));
				}


				if (!$printed) {
					$noresults = GetAttribute("flextable", "NoResultsMessage", $st); // {{language default}}
					if ($noresults == "" || $noresults == "{{language default}}") {
						$noresults = $lang['noresults'];
					}
					$tret .= "<tr style='background-color: #ffffff;'><td colspan='22'>" . $noresults . "</td></tr>";
					//log_msg("WARNING: Flextable " . $flextable . " has empty table field layout (or no records at all)");
				}
				$tret .= "</table>"; // close "crm" table
				
				$tret .= "</td></tr></table>"; // close listmastertable;

				//$tret .= "</td></tr></table>";

				if (($ref && GetAttribute("flextable", "ShowSelectionsWhenInline", $st) == "Yes") || (!$ref && GetAttribute("flextable", "ShowSelectionsWhenNotInline", $st) != "No")) {

					$ss = "<div id=\"JS_savedselections\" class=\"showinline\"> " . $lang['savedselection'] . ": <select ";
					if ($usingSavedSelection != "") {
						$ss .= "class='highlightedselectbox'";
					}
					$ss .= " name='ssSelect' onchange=\"" . $func . "&loadSavedSelection=' + this.options[this.selectedIndex].value);\"><option value='none'>" . $lang['none'] . "</option>";
					if ($usingSavedSelection == "n/a") {
						$ss .= "<option selected='selected' value=''>n/a</option>";
					}
					$foundsome = false;


					$tmp = GetAttribute("system", "SavedSelectionsFlextable" . $flextable, 1);
					if (!is_array($tmp)) $tmp = array();
					$add = GetAttribute("user", "SavedSelectionsFlextable" . $flextable, $GLOBALS['USERID']);

					if (is_array($add)) {
						$tmp = array_merge($tmp, $add);
					}
					foreach ($tmp AS $savedSelectionName => $ignore) {
							if ($usingSavedSelection == $savedSelectionName) {
								$ins = "selected='selected'";
							} else {
								$ins = "";
							}
							$ss .= "<option " . $ins . " value='" . htme($savedSelectionName) . "'>" . htme($savedSelectionName) . "</option>";
							$foundsome = true;
					}
					$ss .= "</select></div>";

					if ($foundsome) {
						$savedSelections = $ss;
					} else {

					}

					if (CheckFunctionAccess("AddEditSelections") != "nok" && !$ref) {
						$savedSelections .= " [<a onclick=\"PopFancyBoxLarge('Interleave advanced selection builder', 'index.php?ListId=SavedSelectionsFlextable" . $flextable . "&ShowAdvancedQueryInterface&ParentEntityListAjaxHandler=" . htme($_REQUEST['AjaxHandler']) . "');\">" . $lang['selections'] . "</a>] ";
					}

				}

				$listarrows = "<input type=\"hidden\" name=\"last_page_moment\" id=\"JS_last_page_moment\" value=\"" . htme($_REQUEST['Pag_Moment']) . "\"><input type=\"hidden\" name=\"last_FilterTable\" id=\"JS_last_FilterTable\" value=\"" . htme($_REQUEST['FilterTable']) . "\">";

				if ($ft[0]['maxrowsperpage'] != "" && $ft[0]['maxrowsperpage'] != 0) {

					$prevpm = $_REQUEST['Pag_Moment'] - $ft[0]['maxrowsperpage'];

					if ($prevpm < 0) {
						$prevpm = 0;
					}

					if ($filter) {
						$link_ins = "&amp;FilterTable=" . htme($filter);
					}

					$newpm = $_REQUEST['Pag_Moment'] + $ft[0]['maxrowsperpage'];

					$listarrows .= "<button onclick=\"" . $func . "&amp;Pag_Moment=" . $prevpm ."&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "&amp;sort=" . htme($_REQUEST['sort']) . "&amp;desc=" . $_REQUEST['desc'] . "'); return false;\"";
					if ($_REQUEST['Pag_Moment'] < 1) {
						$listarrows .= " disabled='disabled'";
					}
					$listarrows .= ' class="prevpage">&laquo;</button>';
					$listarrows .= '&nbsp;';

					if (!$dontshowtotal) {
						if (is_numeric($override_valid_rows)) {
							$number_of_pages = ceil($override_valid_rows/$ft[0]['maxrowsperpage']);
						} else {
							$number_of_pages = ceil($valid_rows/$ft[0]['maxrowsperpage']);
						}

						$listarrows .= '<select class="pageselector">';
						for ($x = 1; $x <= $number_of_pages; $x++) {
							$Pag_Moment_dd = ($x - 1) * $ft[0]['maxrowsperpage'];

							$listarrows .= '<option value="' . $Pag_Moment_dd . '"';
							if ($Pag_Moment_dd == $_REQUEST['Pag_Moment']) {
								$listarrows .= ' selected="selected"';
							}
							$listarrows .= ' onclick="' . $func . '&amp;Pag_Moment=' . $Pag_Moment_dd . '&amp;FilterTable=' . htme($_REQUEST['FilterTable']) . '&amp;sort=' . $_REQUEST['sort'] . '&amp;desc=' . htme($_REQUEST['desc']) . '\');">' . $x . '</option>';
						}
						$listarrows .= '</select>';
						$listarrows .= '&nbsp;';
					}

					$listarrows .= "<button onclick=\"" . $func . "&amp;Pag_Moment=" . $newpm . "&amp;FilterTable=" . htme($_REQUEST['FilterTable']) . "&amp;sort=" . $_REQUEST['sort'] . "&amp;desc=" . htme($_REQUEST['desc']) . "'); return false;\"";

					$to_num = $_REQUEST['Pag_Moment'] + $ft[0]['maxrowsperpage'];

					if ($to_num >= $valid_rows) {
						$to_num = $valid_rows;
						$listarrows .= " disabled='disabled'";
						$prt = true;
					}
					$listarrows .= ' class="nextpage">&raquo;</button>';
				}

				$countsection = ($_REQUEST['Pag_Moment'] + 1) . "-" . ($printed_rows + $_REQUEST['Pag_Moment']);
				if (!$dontshowtotal) {
					if (is_numeric($override_valid_rows)) {
						$countsection .= "/" . $override_valid_rows;
					} else {
						$countsection .= "/" . $valid_rows;
					}
				}






				// listarrows done


				if (!isset($_REQUEST['compact_view'])) {
					if ($ft['0']['compact_view'] != "y" || $ref == "") {
						if ($ref == "") {
							$sid = "filter_list";
						} else {
							$sid = "filter_list_reffed";
						}

						$searchform = "&nbsp;&nbsp;<div id=\"ftheaderform" . $flextable . "\" class=\"showinline\"<img src='images/searchbox.png' alt='' class='search_img'><input id='flextablesearch" . $flextable . "' type='search' class='search_input search_input_wide autocomplete' name='" . $sid . "' id='JS_" . $sid . "' onkeypress=\"TriggerOnchangeOnEnter(event,this);\" onchange=\"" . $func . "FilterTable=' + urlencodejs(this.value));\" value='" . htme($origfilter) . "'><div class=\"ft_directinput showinline\">&nbsp;&nbsp;ID: <input type='text' style='width: 25px;' name='EditRecord' onkeypress=\"TriggerOnchangeOnEnter(event,this);\" onchange='document.location=\"flextable.php?EditRecord=\" + this.value + \"&amp;FlexTable=" . $flextable . "\"'></div></div> ";
 
						if ($filter) {
							$searched = "\"" . str_replace(" " , "\" + \"", htme($filter));
							$searched = str_replace("+ \"-", " <i>excluding</i> \"", $searched) . "\"";
							$searchform .= "&nbsp;&nbsp;" . $lang['search'] . ": <strong>" . $searched . "</strong>";
						}

						$searchform .= "";

						if ($filter || $listfilterapplied) {
								$clearfilterlink .= "<a class='arrow' onclick=\"" . $func . "ClearFilter=true&amp;filter_id=" . $filter_id . "');\">" . strtolower($lang['clearfilter']) . "</a>";
						}

						$actionicons = "";
						if (is_administrator()) {
							$url = $_SERVER['REQUEST_URI'];

							$urli = explode("/", $url);
							if ($urli[count($urli)-1] == "assist.php") {
								$url = "flextable.php?ShowTable=" . $flextable;
							}
							$req_url = base64_encode($url);

							$actionicons .= "&nbsp;&nbsp;<a href=\"flextable.php?EditFlexTable=" . htme($ft[0]['recordid']) . "&amp;req_url=" . $req_url . "\"><img alt='Table properties' title='Table properties' src='images/properties.gif'></a>";
						}

						$tmp = db_GetArray("SELECT mid, module_name, module_list_html FROM " . $GLOBALS['TBL_PREFIX'] . "modules WHERE module_list_html!='' AND for_table='flextable" . mres($flextable) . "'");
						foreach ($tmp AS $mid) {
							$actionicons .= "&nbsp;&nbsp;<a href='modules.php?action=run&amp;noajax&amp;mid=" . $mid['mid'] . "&amp;dqs=" . htme($selection_without_limit) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "'>" . $mid['module_list_html'] . "</a>";
						}
						if ($ft[0]['users_may_select_columns'] == "y") {
							
							$actionicons .= "&nbsp;&nbsp;<a onclick=\"popflextablecolumnchooser('" . htme($_REQUEST['AjaxHandler']) . "'," . $ft[0]['recordid'] . ",'" . $_REQUEST['CustomColumnLayout'] . "'" . ");\"><img alt='" . htme($lang['selectcolumns']) . "' title='" . htme($lang['selectcolumns']) . "' src='images/selectcolumns.gif'></a>";
						}

						$htmlins = "&amp;CustomColumnLayoutStash=" . PushStashValue(serialize($fts));
						if (GetAttribute("flextable", "DenyDownloads", $flextable) != "Yes") {
							$actionicons .= "&nbsp;&nbsp;<a " . PrintAltToolTipCode($lang['downloadsumcsv'] . " (direct)") . " href='csv.php?DlSsFT=" . $flextable . $htmlins . "&amp;QiD=" . $query_without_limit . "&amp;separator=RealExcel'><img src='images/excel_large.gif' alt=''></a>";


							if ($_REQUEST['CustomColumnLayout'] != "") {
								$cclo = $_REQUEST['CustomColumnLayout'];
							} else {
								$cclo = $ft[0]['tablename'];
							}

							$actionicons .= "&nbsp;&nbsp;<a " . PrintAltToolTipCode($lang['downloadsumcsv'] . " (choose fields)") . " onclick=\"popcolumnchooser('" . $_REQUEST['AjaxHandler'] . "','" . $query_without_limit . "','" . $cclo . " - " . $lang['downloadsumcsv'] . "','flextable" . $flextable . "');\"><img src='images/excel_large_double.gif' alt=''></a>";
						}

					} else {

					}
				}


				if ($showaddlink) {

					if ($ft[0]['refers_to'] == "entity") {
						if (IsValidEID($ref) && $ft[0]['add_in_popup'] == "y" && !$pdf && CheckFlextableRecordAccess($flextable, "_new_") == "ok") {
							if ($ft[0]['addlinktext'] == "" ) {
								//$addlinktoshow = "<a href='javascript:popflextableAddwindow(" . $flextable . "," . $ref . ",\"" . htme($_REQUEST['AjaxHandler']) . "\");' class='arrow'>" . $lang['add'] . "</a>&nbsp;&nbsp;";
							} else {
								if (!$ref) {
									$refi = "false";
								} else {
									$refi = $ref;
								}
								$addlinktoshow = "<a onclick='popflextableAddwindow(" . $flextable . "," . $refi . ",\"" . htme($_REQUEST['AjaxHandler']) . "&PlainField=" . htme($_REQUEST['PlainField']) . "\"); return false;' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
							}
						} elseif (!IsValidEID($ref) && GetAttribute("flextable", "UsePopupAlsoWhenViewingPlainList", $flextable) == "Yes" && $customlink == "" && $_REQUEST['nonavbar'] == "") { // Show in popup, but in this case from main list

							$addlinktoshow = "<a onclick='popflextableAddwindow(" . $flextable . ",0,\"" . htme($_REQUEST['AjaxHandler']) . "\"); return false;'  class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";

						} elseif (IsValidEID($ref) && $ft[0]['add_in_popup'] == "n" && !$pdf && CheckFlextableRecordAccess($flextable, "_new_") == "ok") {
							if ($ft[0]['addlinktext'] == "" ) {
								if ($customlink) {
									$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "'  class='flextableaddlink'>" . $lang['add'] . "</a>&nbsp;&nbsp;";
								} else {
									$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "'  class='flextableaddlink'>" . $lang['add'] . "</a>&nbsp;&nbsp;";
								}
							} elseif (CheckFlextableRecordAccess($flextable, "_new_") == "ok") {
								if ($customlink) {
									$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "'  class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								} else {
									$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "'  class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								}
							}

						} elseif (!$pdf) {

								if ($customlink && CheckFlextableRecordAccess($flextable, "_new_") == "ok") {
									$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "'&amp;PlainField=" . htme($_REQUEST['PlainField']) . "  class='flextableaddlink'>" . ($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								} elseif (CheckFlextableRecordAccess($flextable, "_new_") == "ok") {
									$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "'  class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								}
						}
					} elseif ($ft[0]['refers_to'] == "customer") {

							if (IsValidCID($ref) && $ft[0]['add_in_popup'] == "y" && !$pdf && CheckFlextableRecordAccess($flextable, "_new_" == "ok")) {
								if ($ft[0]['addlinktext'] == "" ) {

								} else {
									if (!$ref) {
										$refi = "false";
									} else {
										$refi = $ref;
									}
									$addlinktoshow = "<a onclick='popflextableAddwindow(" . $flextable . "," . $refi . ",\"" . htme($_REQUEST['AjaxHandler']) . "&PlainField=" . htme($_REQUEST['PlainField']) . "\"); return false;' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								}
							} elseif (!IsValidCID($ref) && GetAttribute("flextable", "UsePopupAlsoWhenViewingPlainList", $flextable) == "Yes" && $customlink == "" && $_REQUEST['nonavbar'] == "") { // Show in popup, but in this case from main list
	
								$addlinktoshow = "<a onclick='popflextableAddwindow(" . $flextable . ",0,\"" . htme($_REQUEST['AjaxHandler']) . "&PlainField=" . htme($_REQUEST['PlainField']) . "\"); return false;' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";

							} elseif (IsValidCID($ref) && $ft[0]['add_in_popup'] == "n" && !$pdf && CheckFlextableRecordAccess($flextable, "_new_" == "ok")) {
								if ($ft[0]['addlinktext'] == "" ) {
									if ($customlink) {
										$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "' class='flextableaddlink'>" . $lang['add'] . "</a>&nbsp;&nbsp;";
									} else {
										$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "' class='flextableaddlink'>" . $lang['add'] . "</a>&nbsp;&nbsp;";
									}
								} elseif (CheckFlextableRecordAccess($flextable, "_new_" == "ok")) {
									if ($customlink) {
										$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
									} else {
										$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
									}
								}

							} elseif (!$pdf) {

									if ($customlink && CheckFlextableRecordAccess($flextable, "_new_") == "ok") {
										$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "' class='flextableaddlink'>" . ($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
									} elseif (CheckFlextableRecordAccess($flextable, "_new_") == "ok") {
										$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
									}
							}
					} elseif (substr($ft[0]['refers_to'], 0,9) == "flextable") {

						$reftotable = str_replace("flextable", "", $ft[0]['refers_to']);

						if (CheckFlextableRecordAccess($reftotable, "_new_") == "ok") {

							if ($ft[0]['add_in_popup'] == "y" || (GetAttribute("flextable", "UsePopupAlsoWhenViewingPlainList", $flextable) == "Yes"  && $customlink == "" && $_REQUEST['nonavbar'] == "")) { // Show in popup, but in this case from main list

								if (!$ref) {
									$refi = "false";
								} else {
									$refi = $ref;
								}
								$addlinktoshow = "<a onclick='popflextableAddwindow(" . $flextable . "," . $refi . ",\"" . htme($_REQUEST['AjaxHandler']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "\"); return false;' class='flextableaddlink'>" . $ft[0]['addlinktext'] . "</a>&nbsp;&nbsp;";
							} else {
								if ($customlink) {
									$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								} else {
									$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								}
							}
						} else {
							if ($debug) DA("Access to add-new record denied, not printing add-link");
							if ($debug) DA($GLOBALS['AccessDeniedReason']);
						}
					} elseif ($ft[0]['refers_to'] == "no_refer") {

							$reftotable = str_replace("flextable", "", $ft[0]['refers_to']);
							if (GetAttribute("flextable", "UsePopupAlsoWhenViewingPlainList", $flextable) == "Yes" && $customlink == "" && $_REQUEST['nonavbar'] == "") { // Show in popup, but in this case from main list
								
								$addlinktoshow = "<a onclick='popflextableAddwindow(" . $flextable . ",0,\"" . htme($_REQUEST['AjaxHandler']) . "\"); return false;' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";

							} else {

								if ($customlink) {
									$addlinktoshow = "<a href='" . $customlink . "&amp;AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								} else {
									$addlinktoshow = "<a href='flextable.php?AddToTable=" . $flextable . "&amp;refer=" . $ref . "&amp;Table=" . htme($_REQUEST['Table']) . "&amp;SelectField=" . htme($_REQUEST['SelectField']) . "&amp;PlainField=" . htme($_REQUEST['PlainField']) . "&amp;nonavbar=" . htme($_REQUEST['nonavbar']) . "' class='flextableaddlink'>" . htme($ft[0]['addlinktext']) . "</a>&nbsp;&nbsp;";
								}
							}
					} else {
						if ($debug) DA("Addlink NOT printed: " . htme($ft[0]['addlinktext']) . " (no match)");
					}

				} else {
					// don't show add-record link
					if ($debug) DA("Addlink not printed");
				}

			if (!$pdf) {
				$lh = "<div id=\"listheadertable" . $flextable . "\">";
					$lh .= "<table width=\"100%\" class=\"listheadertable\">";
						$lh .= "<tr>";
							$lh .= "<td colspan=\"2\">";
								if (trim($selectionDescription) != "") {
									$lh .= "<h3>" . $selectionDescription . "</h3>";
								} elseif (!isset($_REQUEST['no_headerhtml']) && !$refer) {
									$lh .= EvaluateTemplatePHP($ft[0]['headerhtml']) . "";
								}
								
							$lh .= "</td>";
						$lh .= "</tr>";
						$lh .= "<tr>";
							$lh .= "<td class=\"nwrp\"><div id=\"arrowsandcount\" class=\"nwrp showinline\">";
								$lh .= "<div id=\"JS_listarrows\" class=\"showinline\">" . $listarrows . "</div>";
								$lh .= "<div id=\"countflextable" . $flextable . "\" class=\"showinline\">" . $countsection . "</div>";
								$lh .= "<div id=\"JS_addlink\" class=\"JS_addlink_" . $flextable . " showinline\">&nbsp;";
								$lh .= $addlinktoshow;
								$lh .= "</div>";
								if ($RemoveExtraSelectCondionFromQueryLink != "") {
									$lh .= "<div  class=\"showinline\" id=\"RemoveExtraSelectCondionFromQueryLink\">" . $RemoveExtraSelectCondionFromQueryLink . "</div>";
								}
								$lh .= $searchform;
								if (!$dontshowselections) {
									$lh .= $savedSelections;
								} 

								$lh .= "<div class=\"showinline\" id=\"JS_clearfilterlink_ft_" . $flextable . "\">" . $clearfilterlink . "</div>";
							$lh .= "</td>";
							$lh .= "<td class=\"rightalign nwrp\">";
								$lh .= $actionicons;
							$lh .= "</td>";
						$lh .= "</tr>";
					$lh .= "</table>";
				$lh .= "</div>";
			}
				$tret = str_replace("@@@@LISTHEADER@@@@@", $lh, $tret);
				return($tret);

		} else{
			$ret = "Access denied";
			return($ret);
		}
	}
}
?>