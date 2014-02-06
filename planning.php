<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This script handles the use of the extra field of type "date/time (planning)"
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

require_once("initiate.php");

ShowHeaders();

print ReturnPlanning($_REQUEST['id'], $_REQUEST['table'], $_REQUEST['this']);

?>
