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
MustBeAdmin();

if ($_REQUEST['form']) {
	$template = ParseFormTemplate($_REQUEST['data'],$_REQUEST['x'],'ex_html_template.php',false);
} elseif ($_REQUEST['cform']) {
	$template = ParseCustomerFormTemplate($_REQUEST['data'],$_REQUEST['x'],'ex_html_template.php',false);
} else {
	$template = GetTemplate($_REQUEST['data']);
	$template = ParseTemplateEntity($template, $_REQUEST['x']);
	$template = ParseTemplateGeneric($template);
	$template = ParseTemplateCustomer($template, $_REQUEST['x']);
}
$template = ParseTemplateCleanUp($template);
print $template;
exit;
