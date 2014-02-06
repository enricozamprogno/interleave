<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 info@interleave.nl
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * Handles new entity forms (e=_new_) and the edit of existing entities (e=[entity_nr])
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
print "<table width='100%' height='85%'><tr><td valign='center'><center>" . $_REQUEST['msg'] . "</center></td></tr></table></div></body></html>";
EndHTML(false);
?>