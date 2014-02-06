<?php
/* ********************************************************************
 * Interleave
 * Copyright (c) 2001-2012 Hidde Fennema (info@interleave.nl)
 * Licensed under the GNU GPL. For full terms see http://www.gnu.org/
 *
 * This file can be used as an include for other PHP scripts
 * to add entities to repositories. This script will act as full
 * Interleave business logic processor; triggers will fire, checks
 * will be performed, etc.
 *
 * Check http://www.interleave.nl/ for more information
 *
 * BUILD / RELEASE :: 5.5.1.20121028
 *
 **********************************************************************
 */

/*
	USAGE : AS FOLLOWS:

	If you copy/paste the code between the lines in a PHP file and fill in the variables it will
	work. You need to have Interleave installed somewhere on you system.

	-------------------%<---------------------------------------------------------------------------
	include("class.interleave.php");

	$Interleave = new Interleave();
	$Interleave->ConnectToRepository("COMPLETE_PATH", REPOSNR, "USER", "PASSWORD");

	TO LOAD AN ENTITY

	$Interleave->LoadEntity($entity_nr);

	$Interleave->SetCustomer(1);
	$Interleave->SetCategory("Test category");
	$Interleave->SetOwner(1);
	$Interleave->SetAssignee(1);
	$Interleave->SetContent("This is the main body content added by the Interleave class");
	$Interleave->SetStatus("Test en bla");
	$Interleave->SetPriority("Low");
	$Interleave->SetDuedate("15-07-2008");
	$Interleave->SetDuetime("0800");
	$Interleave->SetReadonly("n");
	$Interleave->SetPrivate("n");
	$Interleave->SetFormID("");
	$Interleave->SetStartDate("18-02-2008");
	$Interleave->SetExtraField("19", "blah blah blah");
	$Interleave->SetDeleted("n");
	$Interleave->AttachFile($filename,$filebinarycontent);

	$Interleave->Execute();

	print "Status : " . $Interleave->Status;
	print "Entity : " . $Interleave->EntityID;


	-------------------%<---------------------------------------------------------------------------
*/



class Interleave {
	var $PathToInterleaveMainDirectory;
	var $Repository;
	var $InterleaveUsername;
	var $InterleavePassword;
	var $Status;
	var $CreateQuery;
	var $ExtraFieldUpdates = Array();
	var $FileAttachments   = Array();
	var $AlarmDate		   = Array();
	var $Connected;
	var $eid;
	var $EntityID;
	var $Update;
	var $Error;
	var $journal;



	function __construct()	{
		$this->Status = "Class called<br>\n\n";
	}

	function LoadEntity($eid) {

		$row = GetEntityArray($eid);

		$this->assignee		= $row['assignee'];
		$this->category		= $row['category'];
		$this->content		= $row['content'];
		$this->duedate		= $row['duedate'];
		$this->duetime		= $row['duetime'];
		$this->formid		= $row['formid'];
		$this->owner		= $row['owner'];
		$this->priority		= $row['priority'];
		$this->private		= $row['private'];
		$this->readonly		= $row['readonly'];
		$this->status		= $row['status'];
		$this->customer		= $row['CRMcustomer'];
		$this->sqldate		= $row['sqldate'];
		$this->closedate	= $row['closedate'];
		$this->closeepoch	= $row['closeepoch'];
		$this->deleted		= $row['deleted'];
		$this->openepoch	= $row['openepoch'];
		$this->lastupdate	= $row['tp'];
		$this->startdate	= $row['startdate'];

		$this->Update		= $eid;

		$list = GetExtraFields();
		foreach ($list AS $ef) {
			$this->EFID . $ef['id'] = GetExtraFieldValue($eid, $ef['id'], true, false);
		}



		$this->Status	   .= "Will update entity " . $eid . "<br>\n\n";
	}

	function ConnectToRepository($PathToInterleaveMainDirectory, $Repository, $InterleaveUsername, $InterleavePassword) {

		$GLOBALS['PATHTOINTERLEAVE'] = $PathToInterleaveMainDirectory . "/";

		$functions_file		= $PathToInterleaveMainDirectory . "/functions.php";
		$config_file		= $GLOBALS['CONFIGFILE'];
		$this->Status	   .= "Including " . $config_file . "<br>\n";
		require($config_file);
		$this->Status .= "Including " . $functions_file. "<br>\n";
		require_once($functions_file);
		SwitchToRepos($Repository);
		$this->Status .= "Trying to log into " . $GLOBALS['title'] . "<br>\n";
		if (OnlyAuth($InterleaveUsername, $InterleavePassword)) {
			InitUser();
			//print_r($GLOBALS);
			$this->Status .= "Login succeeded<br>\n";
			$this->Connected = true;
			$GLOBALS['USERID'] = GetUserID($InterleaveUsername);
			do_language();
		} else {
			$this->Status .= "Login failed\n\n" . $GLOBALS['tracelog'];
		}
	}

	function SetCustomer($customer) {
		$this->customer			= $customer;
		$this->TriggerCustomer	= true;
		$changed = true;
	}
	function SetCategory($category) {
		$this->category			= $category;
		$this->TriggerCategory	= true;
		$changed = true;
	}
	function SetOwner($owner) {
		$this->owner			= $owner;
		$this->TriggerOwner		= true;
		$changed = true;
	}
	function SetAssignee($assignee) {
		$this->assignee			= $assignee;
		$this->TriggerAssignee	= true;
		$changed = true;
	}
	function SetContent($content) {
		$this->content			= $content;
		$changed = true;
	}
	function SetStatus($status) {
		$this->status			= $status;
		$this->TriggerStatus	= true;
		$changed = true;
	}
	function SetPriority($priority) {
		$this->priority			= $priority;
		$this->TriggerPriority	= true;
		$changed = true;
	}
	function SetDuedate($duedate) {
		$this->duedate			= $duedate;
		$this->sqldate			= NLDate2INTLDate($duedate);
		$changed = true;
	}
	function SetDuetime($duetime) {
		$this->duetime			= $duetime;
		$changed = true;
	}
	function SetReadonly($readonly = "n") {
		$this->readonly			= $readonly;
		$changed = true;
	}
	function SetDeleted($deleted = "n") {
		if ($this->deleted <> "y" && $deleted == "y") {
			$this->closedate	= date("Y-m-d");
			$this->closeepoch	= date("U");
			$this->deleted		= "y";
		}
		$changed = true;
	}
	function SetPrivate($private = "n") {
		$this->private			= $private;
		$changed = true;
	}
	function SetFormID($formid) {
		if (!is_numeric($formid)) {
			$formid = $GLOBALS['DefaultForm'];
		}
		$this->formid			= $formid;
		$changed = true;
	}
	function SetStartDate($startdate) {
		$this->startdate	= $startdate;
		$changed = true;
	}
	function AttachFile($filename, $filecontent) {
		array_push($this->FileAttachments, array($filename, $filecontent));
		$this->Status .= "Prepared file " . $filename . " for attaching<br>\n";
		$changed = true;
	}

	function __destruct() {
		$this->sCompilatie = "";
	}

	function SetExtraField($field, $value) {
		array_push($this->ExtraFieldUpdates, array($field, $value));
		$changed = true;
	}

	function SetAlarm($AlarmDate, $Email) {
		$this->Alarmdate = array($AlarmDate, $Email);
		$changed = true;
	}

	function Execute() {
		if (!$this->Update) {
			$eid = AddEntity($this->customer, $this->category, $this->owner, $this->assignee, $this->content, $this->status, $this->priority, $this->duedate, $this->duetime, $this->readonly, $this->private, $this->formid, $this->startdate, true);
		} else {
			$eid = $this->Update;
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "entity SET CRMcustomer='" . mres($this->customer) . "', category='" . mres($this->category) . "', owner='" . mres($this->owner) . "', assignee='" . mres($this->assignee) . "', content='" . mres($this->content) . "', status='" . mres($this->status) . "', priority='" . mres($this->priority) . "', duedate='" . mres($this->duedate) . "', duetime='" . mres($this->duetime) . "', readonly='" . mres($this->readonly) . "', private='" . mres($this->private) . "', formid='" . mres($this->formid) . "', startdate='" . mres($this->startdate) . "', sqldate='" . mres($this->sqldate) . "', closedate='" . mres($this->closedate) . "', closeepoch='" . mres($this->closeepoch) . "', deleted='" . $this->deleted . "' WHERE eid='" . mres($eid) . "'", $db);

		}

		foreach ($this->ExtraFieldUpdates AS $ExtraFieldToUpdate) {
			SetExtraFieldValue($ExtraFieldToUpdate[0], $eid, mres(trim($ExtraFieldToUpdate[1])), true);
		}
		foreach ($this->FileAttachments AS $File) {
			AttachFile($eid,$File[0],$File[1],"entity");
			$this->Status .= "Attached file " . $File[0] . " to this entity<br>\n";
		}


		$this->Status .= "Executed\n";
		$this->EntityID = $eid;

		if (!$this->Update) {
			ProcessTriggers("entity_add",$eid,"");
		} else {
			if ($changed) {
				ProcessTriggers("entity_change",$eid,"");
			}
		}

		if ($this->TriggerStatus) {
			ProcessTriggers("status",$eid,$this->status);
		}
		if ($this->TriggerPriority) {
			ProcessTriggers("priority",$eid,$this->priority);
		}
		if ($this->TriggerOwner) {
			ProcessTriggers("owner",$eid,$this->owner);
		}
		if ($this->TriggerAssignee) {
			ProcessTriggers("assignee",$eid,$this->assignee);
		}
		if ($this->TriggerCustomer) {
			ProcessTriggers("customer",$eid,$this->customer);
		}

		foreach ($this->ExtraFieldUpdates AS $ExtraFieldToUpdate) {
			ProcessTriggers("EFID" . $ExtraFieldToUpdate[0],$eid,mres(trim($ExtraFieldToUpdate[1])));
		}


	}

}

?>