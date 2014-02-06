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
require_once("initiate.php");
$_REQUEST['nonavbar'] = 1;
ShowHeaders();


if (CheckExtraFieldAccess($_REQUEST['fieldid']) <> "ok") {
	PrintAD("You're not allowed to add fields to this table");
} else {


	if ($_REQUEST['NewFieldValue']) {

		$ef = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "extrafields WHERE id='" . mres($_REQUEST['fieldid']) . "'");
		if ($ef['allowuserstoaddoptions'] == "y") {
			$ol = unserialize($ef['options']);

			array_push($ol, $_REQUEST['NewFieldValue']);

			$sql = "UPDATE " . $GLOBALS['TBL_PREFIX'] . "extrafields SET options='" . mres(serialize($ol)) . "' WHERE id='" . mres($_REQUEST['fieldid']) . "'";
			mcq($sql, $db);
			ConvertDDEFToENUM();

			log_msg("User " . GetUserName($GLOBALS['USERID']) . " add value option " . $_REQUEST['NewFieldValue'] . " to extra field " . $_REQUEST['fieldid']);


			print "saved";
			?>
			<script type="text/javascript">
			<!--
				parent.$.fancybox.close();
			//-->
			</script>
			<?php
		} else {
			print "Adding values to this field is not allowed (reported)";
			log_msg("WARNING: A user tried to add values to a field which does not permit this! (field: " . $_REQUEST['fieldid'] . ")");
		}

		} else {

		print "<table><tr><td><h1>Add option to field &quot;" . htme(GetExtraFieldName($_REQUEST['fieldid'])) . "&quot;</h1>";
		print "<table><tr><td><form id='Addfieldvalue' onsubmit='AddOptionAndSubmit();' method='get' action=''><div class='showinline'>";
		print "New value: <input type='text' name='NewFieldValue' size='40'>";
		print "<input type='hidden' name='field' value='" . htme($_REQUEST['field']) . "'>";
		print "<input type='hidden' name='fieldid' value='" . htme($_REQUEST['fieldid']) . "'>";
		print "<br><br><input type='button' onclick='AddOptionAndSubmit();' value='" . $lang['go'] ."'>";
		print "</div></form>";

		print '<script type="text/javascript">' . "\n";
		print '<!--' . "\n";
		print 'function AddOptionAndSubmit() {' . "\n";
		print "\tAddOptionToSelectBox(parent.document.getElementById('JS_EFID" . htme($_REQUEST['fieldid']) . "'),document.forms['Addfieldvalue'].elements['NewFieldValue'].value,document.forms['Addfieldvalue'].elements['NewFieldValue'].value);" . "\n";
		print "document.forms['Addfieldvalue'].submit();\n";
		print '}'. "\n";
		print '//-->' . "\n";
		print '</script>' . "\n";
		print "</td></tr></table></td></tr></table>";
		EndHTML();

	}
}
?>