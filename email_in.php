<?php

/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This is the "e-mail to entity" plugin for Interleave
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

//
// SENDMAIL SYNTAX; Create an alias in your /etc/mail/aliases called:
//
//	crm:  "|/path/to/php /path/to/email_in.php [reposnr] logger_user logger_pass new 'Full customer name'"
// e.g.
//  crm:  "|/usr/local/bin/php /webservers/htdocs/crmstage/email_in.php 2 loguser logpass new 'IBM'"
//
// The "new" in the line must be there.
$fp = fopen("php://stdin", "r");
while (!feof($fp)) {
	$input .= fgets($fp,1024);
}



function SL($msg) {
	qlog(INFO, $msg);
	// TO USE SYSTEM LOGGER: uncomment this line:
	//	system("echo 'INTERLEAVE: " . escapeshellcmd($msg) . "'| logger");
	//print "INTERLEAVE: " . $msg . "\n";;
}


$GLOBALS['CONFIGFILE'] = $GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php";
foreach ($argv AS $cmdlineargument) {
	if (substr($cmdlineargument,0,4) == "cfg=") {
			$cmdlineargument = str_replace("cfg=", "" , $cmdlineargument);
			if (is_file($cmdlineargument)) {
				$GLOBALS['CONFIGFILE'] = $cmdlineargument;
				//print "Using config file " . $GLOBALS['CONFIGFILE'] . "\n";
				continue;
			} else {
				die("Config file declaration is not correct. Fatal.");
			}
	}
}


include($GLOBALS['CONFIGFILE']);

require_once($GLOBALS['PATHTOINTERLEAVE'] . "functions.php");
//require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config.inc.php");
require_once($GLOBALS['PATHTOINTERLEAVE'] . "config/config-vars.php");
require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/mimeDecode.php");
require_once($GLOBALS['PATHTOINTERLEAVE'] . "lib/class.phpmailer.php");



// Check if this is done using the command line (e.g. not the web)
CheckIfShell();

foreach ($argv AS $ar) {

	if (substr($ar, 0, 4) == "cfg=") {
		$cfg = str_replace("cfg=", "", $ar);
		$GLOBALS['CONFIGFILE'] = $cfg;
		require_once($cfg);
	}

}

if ($argv[1]) {
	$repository = $argv[1];
}
if ($argv[2]) {
	$username = $argv[2];
}
if ($argv[3]) {
	$password = $argv[3];
}
if ($argv[4]) {
	$entity = $argv[4];
}
if ($argv[5]) {
	$customer = $argv[5];
}
if ($repository==0 || $repository=="") {
	// make this a string
	$repository = trim(" 0 ");
}
 if ($argv[1]=="-help" || $argv[1]=="--help" || $argv[1]=="help" || $argv[1]=="-h" ||  $username=="" || $password=="" || $entity=="" ) {
	print "\nInterleave Entity insert and update from e-mail\n\nUsage:\n\n";
	print "Add a new entity: (all fields are required)\n\n\tphp -q ./email_in.php [reposnr] [user] [pass] [new|parse] [\"customer name\"] [\"category text\"] [cfg=/path/to/config.inc.php\n";
	exit(0);
}
if (!$username || !$password || !$entity || !$customer) {
	echo "You MUST give all required information as arguments!\n\n";
	print "$username || !$password || !$repository || !$entity || !$customer";
	exit(1);
}
//require_once($config);


$silent = 1;
$noneedtobeadmin = 1;
if (!CommandlineLogin($username,$password,$repository)) {
	exit(1);
}



// Load all required local settings
SwitchToRepos($repository);

$lang = do_language();

//exit;
SL("EmailIn::Action/entity: " . $entity);

SL("EmailIn::Action/entity: " . $entity);

$args['include_bodies'] = true;
$args['decode_bodies'] = true;
$args['decode_headers'] = true;

$decode = new Mail_mimeDecode($input, "\r\n");

$structure = $decode->decode($args);

$from_full = $structure->headers['from'];
$arr = array();
preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i", $from_full, $arr);
$from_email = $arr[0];

$to_full = $structure->headers['to'];
$arr = array();
preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i", $to_full, $arr);
$to_email = $arr[0];

$GLOBALS['EMAIL_SENDER_ADDRESS'] = $from_email; // Essential for triggers!
$GLOBALS['EMAIL_TO_ADDRESS'] = $to_email; // Essential for triggers!

//$GLOBALS['DISABLEMAIL'] = true; // USE ONLY FOR TESTING

$body = "MAIL\n";
$body .= "\nFrom: " . $structure->headers['from'];
$body .= "\nReceived: " . $structure->headers['date'];
$body .= "\nTo: " . $structure->headers['to'];

if ($structure->body<>"") {
		$body .= "\n\n" . $structure->body;
} elseif($structure->parts[0]->body<>"") {
		$body .= "\n\n" . $structure->parts[0]->body;
} elseif($structure->parts[0]->parts[0]->body<>"") {
		$body .= "\n\n" . $structure->parts[0]->parts[0]->body;
} elseif($structure->parts[0]->parts[0]->parts[0]->body<>"") {
		$body .= "\n\n" . $structure->parts[0]->parts[0]->parts[0]->body;
} elseif($structure->parts[1]->body<>"") {
		$body .= "\n\n" . $structure->parts[1]->body;
} elseif($structure->parts[1]->parts[0]->body<>"") {
		$body .= "\n\n" . $structure->parts[1]->parts[0]->body;
} elseif($structure->parts[1]->parts[0]->parts[0]->body<>"") {
		$body .= "\n\n" . $structure->parts[1]->parts[0]->parts[0]->body;
} elseif($structure->parts[2]->body<>"") {
		$body .= "\n\n" . $structure->parts[2]->body;
}

$GLOBALS['EMAIL_BODY'] = $body;
$GLOBALS['EMAIL_SUBJECT'] = $structure->headers['subject'];

if ($entity=="new") {

	SL("New entity!");
	$sql = "SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "customer WHERE custname='" . mres($customer) . "'";
	$result = mcq($sql,$db);
	$row = mysql_fetch_array($result);
	$customer_id = $row[0];
	if ($customer_id == "") {
		print "Customer name could not be resolved. Fatal, quitting.\n";
		exit(1);
	}
	$cdate = date('Y-m-d');
	$epoch = date('U');



//	$attm = $decode->uudecode($input);
//	print_r($attm);
	$files_names = array();
	$files_blobs = array();
	$files_sizes = array();
	// Next three lines will attach the raw input as an attachment (for debug/test purposes)
	// (only when $logtext is set)
	if ($logtext) {
		array_push($files_names, "mime-source.txt");
		array_push($files_blobs, $input);
		array_push($files_sizes, strlen($input));
	}
	for ($i=0;$i<sizeof($structure->parts);$i++) {
		if (($structure->parts[$i]->d_parameters['filename'] <> "") && $structure->parts[$i]->body <> "") {
			array_push($files_names, $structure->parts[$i]->d_parameters['filename']);
			array_push($files_blobs, $structure->parts[$i]->body);
			array_push($files_sizes, strlen($structure->parts[$i]->body));
		}

	}
	SL("Number of files: " . sizeof($files_names));

	//print $body;
	//exit;
	$logtxt .= "Entity created\n";
	$cdate = date('Y-m-d');
	if (strstr($input, '$INTERLEAVE$')) {
		SL("Processing an email which will be added to an existing entity");


		$el = ExtractUniqueKeyElements($input);

		$eid = $el['eid'];
		$rep = $el['repository'];

		if ($rep <> $repository) {
			print "Error: this message is not destined for this repository\n";
			SL("Error: this message is not destined for this repository");
			log_msg("ERROR: A mail was processed containing a reference key, though it doensn't refer to this repository! (" . $rep . " is not " . $repository . ")");
		} else {
			SL("Reference found and checked: this e-mail will be added to eid " . $eid);
			$txt = GetBody($eid);
			SetBody($eid, $body . " ----------------------------------------------\n" . $txt);
			ProcessTriggers("entity_change",$eid,"");
			ProcessTriggers("entity_email_update",$eid,"");
			ExpireFormCache($eid);
		}


	} else { // No update, new entity

		$owner = $GLOBALS['USERID'];
		$assignee = $GLOBALS['USERID'];

		$res = db_GetRow("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatename='Default form' AND templatetype='TEMPLATE_HTML_FORM' AND username='Hidde Fennema'");
		$GLOBALS['DefaultForm'] = $res[0];
		$sql = "INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "entity(priority,category,content,owner,assignee,CRMcustomer,status,deleted,duedate,sqldate,obsolete,cdate,waiting,openepoch,formid) VALUES('[unknown]', '" . mres($structure->headers['subject']) . "', '" . mres($body) . "', '" . mres($owner) . "', '" . mres($assignee) ."', '" . mres($customer_id) . "','[unknown]','n','','','','" . $cdate . "','','" . $epoch . "','" . $GLOBALS['DefaultForm'] . "')";
		mcq($sql,$db);
		$eid = mysql_insert_id();
		journal($eid,$logtxt);
		// Process all triggers

		ProcessTriggers("entity_email_insert",$eid,"");
		ProcessTriggers("entity_add",$eid,"");

		ProcessTriggers("assignee",$eid,$assignee);
		ProcessTriggers("owner",$eid,$owner);
		ProcessTriggers("status",$eid,$status);
		ProcessTriggers("priority",$eid,$priority);
		ProcessTriggers("customer",$eid,$customer_id);




	}

	if (is_numeric($eid)) {
		for ($y=0;$y<sizeof($files_names);$y++) {
				SL("Attaching file " . $files_names[$y] . " to entity " . $eid);
				AttachFile($eid, $files_names[$y], $files_blobs[$y], "entity", "", "");
				$ins_attm++;
		}
	}


	// Assign if nescessary (but always AFTER triggers because of access rights)

	$sql = "SELECT value FROM " . $GLOBALS['TBL_PREFIX'] . "settings WHERE setting='AutoAssignIncomingEntities'";
	$result = mcq($sql,$db);
	$row = mysql_fetch_array($result);
	if (strtoupper($row[0])=="YES") {
		// This new entity must be auto-assigned to the customer owner
		SL("Auto-assigning this entity to the customer owner");
		$logtxt .= "Auto-assigning this entity to the customer owner (if found). ";
		$owner = GetCustomerOwner($customer_id);
		$assignee = GetCustomerOwner($customer_id);

		if ($owner == "" || $owner == 0) {
			$owner = $GLOBALS['USERID'];
		}
		if ($assignee == "" || $assignee == 0) {
			$assignee = $GLOBALS['USERID'];
		}

		$logtxt .= "Set o:$owner a:$assignee";

		mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET assignee='" . mres($assignee) . "', owner='" . mres($owner) . "' WHERE eid='" . mres($eid) . "'", $db);
	}


	if (is_numeric($eid)) {
		for ($y=0;$y<sizeof($files_names);$y++) {
				SL("Attaching file " . $files_names[$y] . " to entity " . $eid);
				AttachFile($eid, $files_names[$y], $files_blobs[$y], "entity", "", "");
				$ins_attm++;
		}
		$ManualEmailAddress = $from_email;


		$MailBody .= $ins_attm . " attachments";
		$MailBody = $GLOBALS['BODY_EMAILINSERT_REPLY'];
		$Subject  = $GLOBALS['SUBJECT_EMAILINSERT_REPLY'];

		if ($Subject && $MailBody) {

		$MailBody = ParseTemplateEntity($MailBody,$eid);
		$MailBody = ParseTemplateCustomer($MailBody,$customer_id);
		$MailBody = ParseTemplateGeneric($MailBody);

		$Subject  = ParseTemplateEntity($Subject,$eid);
		$Subject  = ParseTemplateCustomer($Subject,$customer_id);
		$Subject  = ParseTemplateGeneric($Subject);

			if ($to_email == $from_email) {
				log_msg("ERROR:: Circular e-mail configuration. Don't set your admin e-mailaddress to the same address as your administrative e-mail address.");
			} else {
			   if (sizeof($GLOBALS['BODY_EMAILINSERT_REPLY'])>10) {
					RealMail($MailBody,$eid,GetEntityCustomer($eid),"","",$ManualEmailAddress,false,$Subject,true,false,false);
				} else {
					SL("Not replying to this e-mail, because the BODY EMAILINSERT REPLY setting is empty. ($eid)");
				}
			}
		}

	} else {
		log_msg("ERROR: Unknown error occured, no EID was created or determined when processing incoming mail. ($eid)");
	}
	// Update any existing fail-over databases
	SynchroniseFailOverDatabase();

} else { // Don't create a new entity, process triggers only

	$GLOBALS['Attachments'] = array();
	$atmcounter=0;
	for ($i=0;$i<sizeof($structure->parts);$i++) {
		if (($structure->parts[$i]->d_parameters['filename'] <> "") && $structure->parts[$i]->body <> "") {
			
			$GLOBALS['Attachments'][$atmcounter] = array();
			$GLOBALS['Attachments'][$atmcounter]['data']		= $structure->parts[$i]->body;
			$GLOBALS['Attachments'][$atmcounter]['filename']	= $structure->parts[$i]->d_parameters['filename'];
			$GLOBALS['Attachments'][$atmcounter]['type']		= $structure->parts[$i]->ctype_primary . "/" . $structure->parts[$i]->ctype_secondary;
			$atmcounter++;
		}

	}
	ProcessTriggers("entity_email_insert",false,"");
}
//print $GLOBALS['tracelog'];
exit(0);

function uselogger($comment,$dummy_extra_not_used){
	global $REMOTE_ADDR, $HTTP_SERVER_VARS, $actuser, $username, $user, $HTTP_USER_AGENT,$name,$logqueries;

		// here comes the mail trigger

	 if (getenv(HTTP_X_FORWARDED_FOR)){
	   $ip=getenv(HTTP_X_FORWARDED_FOR);
	 }
	 else {
	   $ip=getenv(REMOTE_ADDR);
	 }

	if (!$comment) {
		$qs  = getenv("QUERY_STRING");
		$qs .= getenv("HTTP_POST_VARS");
		$qs .= $comment;
	} else {
		$qs = ($comment);
	}
	$url = $HTTP_SERVER_VARS["PHP_SELF"];

	$query ="INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "uselog (ip, url, useragent, qs, user) VALUES ('" . mres($ip) . "', '" . mres($url) . "', '" . mres($HTTP_USER_AGENT) . "' , '" . mres($qs) . "','" . mres($name) . "')";
	mcq($query,$db);
	if ($logqueries) {
		SL("'$ip', '$url', '$HTTP_USER_AGENT' , '$qs','$name'");
	}
}
?>