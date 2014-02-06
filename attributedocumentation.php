<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@crm-ctt.com)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file sets an array with attribute documentation items.
 * It is included in attribute.php
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
*/

// Attribute documentation

// ATDOCS = array
// Element key name will be STRSTR matched against processing attribute name (so documentaion key CustomValidationFunctionPHP will also be shown when working with attribute CategoryCustomValidationFunctionPHP)
// Each element should contain an array:
// atdocs[keyname][0] = <h1> description
// atdocs[keyname][1] = <p> details



$atdocs = array();

$atdocs["AddExtraSelectCondionToQueryLinkText"] =							array("AddExtraSelectCondionToQueryLinkText", "The text to show (as a link) to re-apply the ExtraSelectCondition.");
$atdocs["AllowReferChanges"] =												array("AllowReferChanges", "Whether or not the refer to a record in the table above can be changed once the record is added.");
$atdocs["AllowUserToEditParsedResultBeforeCreatingPDF"] =					array("AllowUserToEditParsedResultBeforeCreatingPDF", "When set, the user will have the opportunity to edit de already parsed document (in the rich text editor) just before it gets parsed to PDF, thus enabling the user to put in some remarks or to correct stuff.");
$atdocs["AllowUsersToSwitchThisTriggerOff"] =								array("AllowUsersToSwitchThisTriggerOff","When set to Yes, this trigger can be switched off by a user on the profile prferences page.");
$atdocs["AlwaysAttachAllAttachmentsWhenMailing"] =							array("AlwaysAttachAllAttachmentsWhenMailing","When set, all record attachments will be attached to the e-mail.");
$atdocs["AutoUpdateDiaryField"] =											array("AutoUpdateDiaryField","When set, the diary field will auto reload every 20 seconds. This enables realtime chat between multiple users using the diary field.");
$atdocs["BackToListAfterSave"] =											array("BackToListAfterSave","When set, the browser will be redirected back to the list after saving a record.");
$atdocs["BlindReferenceFieldLayout"] =										array("BlindReferenceFieldLayout", "The layout (template) to use for this field. Use tags. If left unset, the flextable general reference layout template will be used.");
$atdocs["ButtonClickConfirmMessage"] =										array("ButtonClickConfirmMessage","The (popup) message to display for confirmation, e.g. 'Are you sure?'.");
$atdocs["CSVExportBookmarks"] =												array("CSVExportBookmarks", "Remembering export column settings.");
$atdocs["ComputationOutputType"] =											array("ComputationOutputType","The type of data this computation outputs.");
$atdocs["CustomValidationFunctionPHP"] =									array("Custom PHP-enabled client side field validation", "Use PHP to check if an entered value is valid. This script will be called via AJAX, so the user will be informed immediately. To calculate use either \$eid, \$cid or \$recordid. When appliccable the flextable number is in \$flextable. The submitted value is stored in \$value. You need to return (which, in this case, means leave behind) a variable called \$result which can contain &quot;nok&quot; or &quot;ok&quot;. Additionally, the \$result variable may also be returned as an array, where \$result[0] contains &quot;nok&quot; or &quot;ok&quot; and \$result[1] contains the message to show to the user (in a popup). \$result[2] could even contain a correction on the value which will be put back in the form (e.g. uppercased). The module will be parsed so you can use tags. When this code ran by server side validator, the vairable \$serverside will be true.");
$atdocs["CustomerListAlwaysInPopup"] =										array("Whether or not to show all customers in a popup","Only when openend from the main customer list.");
$atdocs["CustomerListMainHeaderHTML"] =										array("CustomerListMainHeaderHTML","This HTML will be shown above the main customer list.");
$atdocs["DateFormat"] =														array("DateFormat","The format of the date.");
$atdocs["DefaultExcel2007Font"] =											array("DefaultExcel2007Font", "The default font for new MS Excel 2007-style spreadsheets.");
$atdocs["DefaultExcel2007FontSize"] =										array("DefaultExcel2007FontSize", "The default font size for new MS Excel 2007-style spreadsheets.");
$atdocs["DenyDownloads"] =													array("DenyDownloads", "Set this attribute to Yes to deny *all* downloads from this table.");
$atdocs["DontIncludeDeletedEntitiesWhenSearching"] =						array("DontIncludeDeletedEntitiesWhenSearching", "When set, deleted entities will nog be included in a search.");
$atdocs["DontRecalculateConnectedTablesOnRecalc"] =							array("DontRecalculateConnectedTablesOnRecalc", "Whether or not to re-calculate all computed extra fields in connected tables when a record in this table is being re-calculated.");
$atdocs["DontRecalculateOnRecalcOfParent"] =								array("DontRecalculateOnRecalcOfParent", "Whether or not to re-calculate records in this table when when a record in the parent table is being re-calculated.");
$atdocs["EditBeforeParseButtonText"] =										array("EditBeforeParseButtonText", "The text printed on the button which actually generates the PDF after the user edited the parsed HTML.");
$atdocs["EditBeforeParseHTML"] =											array("EditBeforeParseHTML", "The content of this attribute will be shown above the rich-text editor showing the pre-parsed document.");
$atdocs["EntityListAlwaysInPopup"] =										array("Whether or not to show all entities in a popup", "Only when openend from the main entity list.");
$atdocs["EntityListMainHeaderHTML"] =										array("EntityListMainHeaderHTML","This HTML will be shown above the main entity list.");
$atdocs["ExtraSelectCondition"] =											array("ExtraSelectCondition", "Extra condition when selecting table rows. This condition will be applied by default for all users viewing the list. Only when viewing it in HTML. The condition must be valid SQL , e.g. &quot;EFID134='Not yet processed' AND deleted!='y'&quot; Use attributes AddExtraSelectCondionToQueryLinkText and RemoveExtraSelectCondionFromQueryLinkText to set links for disabling and re-applying the extra select condition.");
$atdocs["FlexTableColumns"] =												array("FlexTableColumns","Columns to show.");
$atdocs["FlexTableFilters"] =												array("FlexTableFilters","Remembering set filters.");
$atdocs["FlexTableSort"] =													array("FlexTableSort","Remembering the sort order.");
$atdocs["HideButtonWhenNotClickable"] =										array("HideButtonWhenNotClickable","When set, this button will not be shown at all when clicking it is not possible. By default, the button will disabled, but visible.");
$atdocs["HideFooter"] =														array("HideFooter", "Disable the footer completely in the resulting PDF.");
$atdocs["HideHeader"] =														array("HideHeader", "Disable the header completely in the resulting PDF.");
$atdocs["IgnoreUniqueValues-CommaSeprated"] =								array("IgnoreUniqueValues-CommaSeprated","Values to ignore when checking if a value is unique.");
$atdocs["IgnoreValueChangesWhenRecalculating"] =							array("IgnoreValueChangesWhenRecalculating","When set, a recalculation of this field will not trigger other recalculations, even when it gives different results every time.");
$atdocs["ImportTableHTML"] =												array("HTML Shown on the import page","Place your own text in this field. It will be shown on the import page.");
$atdocs["IncludeInSystemWideSearches"] =									array("IncludeInSystemWideSearches", "Set to 'No' if you don't want this flextable to show on the search page (summary.php).");
$atdocs["IncludeParentTableInSearches"] =									array("IncludeParentTableInSearches","If set, a search query will include the parent table.");
$atdocs["IncludeThisTableInSearchesFromParentTable"] =						array("IncludeThisTableInSearchesFromParentTable","If set this table will also be searched through when a user searches in the parent table.");
$atdocs["InlineFormFieldsToShow"] =											array("InlineFormFieldsToShow","Comma-separated list of fields to show when using the Inline-Form. E.g. 12,45,112,7,145.");
$atdocs["InlineFormHeaderHTML"] =											array("InlineFormHeaderHTML","This HTML will be printed above the inline form.");
$atdocs["InlineFormNumOfSpareLines"] =										array("InlineFormNumOfSpareLines","The number of empty lines to pre-print when using inline forms.");
$atdocs["InputNumbersWithSeperators"] =										array("InputNumbersWithSeperators","Whether or not to allow formatted numbers as *input*. E.g. when set to yes, '1.002.123,24' is allowed as input.");
$atdocs["LanguageMask"] =													array("LanguageMask","The language mask.");
$atdocs["LanguagePack"] =													array("LanguagePack","The language pack.");
$atdocs["LastActivity"] =													array("LastActivity","Last activity of this user.");
$atdocs["LastCustomerListSort"] =											array("LastCustomerListSort","Remembering the customer list order.");
$atdocs["LastListSort"] =													array("LastListSort","Remembering the last list sort.");
$atdocs["LastLogin"] =														array("LastLogin","This user's last login.");
$atdocs["LastLogout"] =														array("LastLogout","This user's last logout.");
$atdocs["LastRecordDedup"] =												array("LastRecordDedup3", "Remembering where we left off the last time we de-duplicated this table.");
$atdocs["ListSelection"] =													array("LastEntityListSelection","Remembering the last selected selection.");
$atdocs["MaxNumOfRecordsPerParentRecord"] =									array("MaxNumOfRecordsPerParentRecord","The maximum number of records per parent record.");
$atdocs["MaximumLength"] =													array("MaximumLength","The maximum input lenght in characters (requires HTML5-enabled browser).");
$atdocs["MaximumValue"] =													array("MaximumValue","The maximum value (requires HTML5-enabled browser).");
$atdocs["MinimumValue"] =													array("MinimumValue","The minimum value (requires HTML5-enabled browser).");
$atdocs["ModuleRunCount"] =													array("ModuleRunCount","Remembering how many times a module has been ran.");
$atdocs["MustBeUnique"] =													array("MustBeUnique","When set, the system will only allow unique values in this field.");
$atdocs["NoResultsMessage"] =												array("NoResultsMessage","The message which is displayed when no records were found.");
$atdocs["ReferFieldSelectInPopup"] =										array("ReferFieldSelectInPopup", "Whether or not to select the referring record in a popup. Default is a drop-down field.");
$atdocs["ReferFieldSelectInPopup"] =										array("ReferFieldSelectInPopup","When set, the refer field (from the paren table) will be selected using a popup. Default is a drop-down box.");
$atdocs["RefreshByAjaxOnChangeOfField"] =									array("RefreshByAjaxOnChangeOfField","Enter the field number of a field which should trigger an ajax-reload of this field. Use &quot;All&quot; to refresh on any change, but be careful and watch your performance.");
$atdocs["RemoveExtraSelectCondionFromQueryLinkText"] =						array("RemoveExtraSelectCondionFromQueryLinkText", "The text to show (as a link) to disable the ExtraSelectCondition.");
$atdocs["RepopulateByAjax"] =												array("RepopulateByAjax", "When set, any change of any field in the same form will cause this field to reload in the background.");
$atdocs["RunAsUser"] =														array("RunAsUser","The module will be ran using the credentials of this user.");
$atdocs["RunWithSystemRights"] =											array("RunWithSystemRights","When set, the system will assume the first administrator account found before running this trigger.");
$atdocs["SavedEntityListSelections"] =										array("SavedEntityListSelections","Entity list selections");
$atdocs["SavedSelectionsFlextable"] =										array("SavedSelectionsFlextable15", "Remembering saved selections of this flextable.");
$atdocs["SavedSelectionsFlextable3"] =										array("SavedSelectionsFlextable","Flextable saved selections.");
$atdocs["SearchWords"] =													array("*SearchWords","Search history.");
$atdocs["SelectFromFlextableLinkText"] =									array("SelectFromFlextableLinkText", "The text printed in the link which needs to be clicked to select a value from a table above.");
$atdocs["ShowButtonInList"] =												array("ShowButtonInList","When set, the button is available to show (and use) inside list cells.");
$atdocs["ShowInlineDeleteLink"] =											array("ShowInlineDeleteLink","When set, a small red icon will be shown in the list enabling the user to delete records directly from the list.");
$atdocs["ShowInlineDuplicateLink"] =										array("ShowInlineDuplicateLink","When set, a small gray icon will be shown in the list enabling the user to duplicate records directly from the list.");
$atdocs["ShowSelectionsWhenInline"] =										array("ShowSelectionsWhenInline","Whether or not to show the selections when a list is displayed on a form.");
$atdocs["ShowSelectionsWhenNotInline"] =									array("ShowSelectionsWhenNotInline","Whether or not to show the selections when a list is *not* displayed on a form.");
$atdocs["ShowSortLinks"] =													array("ShowSortLinks","Whether or not to make the list table sortable.");
$atdocs["SummarySearchWords"] =												array("SummarySearchWords","Remembering summary search words.");
$atdocs["TemplateUseCount"] =												array("TemplateUseCount","Remembering the number of times a template is used.");
$atdocs["UsePopupAlsoWhenViewingPlainList"] =								array("UsePopupAlsoWhenViewingPlainList","When set, all records in this table will show in a popup, even when clicking on them from a plain list.");
$atdocs["UserIsAllowedToEditExtraField"] =									array("UserIsAllowedToEditExtraField","When set, a user can see & adjust the field on his/her profile page.");
$atdocs["ViewOnTable"] =													array("ViewOnTable","The number of the table this view is a view on.");
$atdocs["ViewOnTableSelectCondition"] =										array("ViewOnTableSelectCondition","An extra select condition on the view, e.g. &quot;deleted='y' AND EFID123='Yes'&quot;.");
$atdocs["WhenAttachingFilesToMailAlsoAttachEarlierMailAttachements"] = 		array("WhenAttachingFilesToMailAlsoAttachEarlierMailAttachements","See attribute name. Good luck.");
$atdocs["WhenAttachingMailToRecordAlsoAttachAnyGeneratedReports"] = 		array("WhenAttachingMailToRecordAlsoAttachAnyGeneratedReports","See attribute name. Good luck.");
$atdocs["CategoryPlaceholder"] =											array("Category Placeholder", "Gray text which is shown in the category field before a user clicks in it (HTML5 only). You can use XONLY and NEWONLY tags.");
$atdocs["Placeholder"] =													array("Placeholder", "Gray text which is shown in this field before a user clicks in it (HTML5 only). You can use XONLY and NEWONLY tags.");
$atdocs["DeleteConfirmationMessage"] =										array("DeleteConfirmationMessage", "The text which is displayed (as confirmation question) when a user clicks the inline delete icon or the in-form delete button.");
$atdocs["DownloadRules"] =													array("Download rules", "Syntax example: 'U1:0,G2:100,U5:x,U6:0' allows userid 1 to download all, group 2 to download max 100 lines, userid 5 can download nothing (even if this user belongs to group 2 or 6), group can download all and all other users and groups will be denied. User rules overrule group rules.");
$atdocs["UsePlanningCalendar"] =											array("UsePlanningCalendar", "When set to Yes, this field will show a large calendar popup to select the date. It will show other appointments of the same field in this calerdar to allow easy planning.");
$atdocs["UsePlanningCalendarMatchOnFields"] =								array("UsePlanningCalendarMatchOnFields", "When using the UsePlanningCalendar option, the current appointments which will be shown are by default based on the same field. To add more fields to match (e.g. a category, owner etc) add the field number to this setting. You can add as much as you want, comma separated.");
$atdocs["UsePlanningCalendarDescription"] =									array("UsePlanningCalendarDescription", "Template to be parsed as description (when shown in the calendar).");
$atdocs["NumberOfModuleToIncludeInAllPageloads"] =							array("NumberOfModuleToIncludeInAllPageloads", "The number of the module to include in *all* pageloads.");

if (isset($_REQUEST['smd'])) { // show missing documentation
	
	foreach (db_GetFlatArray("SELECT DISTINCT(attribute) FROM " . $GLOBALS['TBL_PREFIX'] . "attributes WHERE attribute NOT LIKE '%SaveAction%' AND attribute NOT LIKE '%SaveComments%'") AS $att) {
		$found = false;
		foreach ($atdocs AS $key => $value) {
			if (strstr($att, $key)) {
				$found = true;
			} else {
				
			}
		}
		if (!$found) print '$atdocs["' . $att . '"] =				array("' . $att . '", "No documenation yet");' . "<br>\n";
	}

	EndHTML();
	exit;
}

?>