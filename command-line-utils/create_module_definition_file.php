<?php
/* ********************************************************************
 * CRM 
 * Copyright (c) 2001-2004 Hidde Fennema (hidde@it-combine.com)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file parses a module to a module definition
 *
 * Check http://www.crm-ctt.com/ for more information
 **********************************************************************
 */
require_once("../functions.php");
$c = array();

print "Module name > ";
$c['module_name'] = readln();
print "Module description > ";
$c['module_description'] = readln();
print "Module code input filename > ";
$m_code_fn = readln();
print "Module definition output filename > ";
$m_code_def_fn = readln();

$c['module_code'] = file_get_contents($m_code_fn);

$fp = fopen($m_code_def_fn, "w");
fputs($fp, serialize($c));
fclose($fp);

print "Done. Output is in " . $m_code_def_fn . "\n";
?>
