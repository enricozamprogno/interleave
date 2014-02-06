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

if (!$GLOBALS['MainPageCalendar'] && !$_REQUEST['weekdetail'] && !$nav) {
	$_REQUEST['nonavbar'] = 1;
	ShowHeaders();
} elseif ((!$GLOBALS['MainPageCalendar'] && $_REQUEST['weekdetail']) || $nav) {
	ShowHeaders();
} else {

}
if ($MainPageCalendar == true) {
	$GLOBALS['MainPageCalendar'] = true;
//	$GLOBALS['MonthsToShow'] = 1;
//	$MonthsToShow = 1;
} elseif ($_REQUEST['select']) {
	//qlog(INFO, "No main page calendar, select date function");
}

if ($_REQUEST['weekdetail'] == "this") {
	$y= date("Y");
	$year = $y;
	$_REQUEST['weekdetail'] = strtotime ("last monday");
	$y= date("Y");

	$year = $y;
	$d= date("D");
	if ($d == "Mon")
	{
	$_REQUEST['weekdetail'] = strtotime ("this monday");
	}
	else
	{
	$_REQUEST['weekdetail'] = strtotime ("last monday");
	}
	$bla=1;
}
$is_admin = is_administrator();
$stryear = $_REQUEST['stryear'];
if (!$stryear) {
	$stryear= date("Y");
}
$strmonth = $_REQUEST['strmonth'];

if (!$strmonth) {
	$strmonth = date("m") -2;
	if ($strmonth == 0) {
		$strmonth = 12;
		$stryear--;
	} elseif ($strmonth == -1) {
		$strmonth = 11;
		$stryear--;
	}
}
if ($_REQUEST['weekdetail']) {
		DisplayCSS();
		ShowSelectWeek($_REQUEST['weekdetail'],$_REQUEST['year'],$_REQUEST['select']);
	} else {
	//print "Jaar: $stryear -- Maand: $strmonth -- toshow: $MonthsToShow pred: $pred<br>";
	// Calculate numer of months to show - $MonthsToShow config value
	// To do so, we need the # of seconds in the # of given months
	// 2592000 = avg. # seconds in one month
	// First, capture any missing params:
	if (!$GLOBALS['MonthsToShow']) {
		log_msg("WARNING: Configuration fault - MTS Adjusted to 6MTS (non-fatal)","");
		$MonthsToShow = 6;
	}
	global $pred;
	if (!$pred) {
		$Seconds = (($MonthsToShow-1) * 2509280);
		$Seconds += 12960000;
		$pred=time()+$Seconds;
	}
	$prevmonth = $strmonth-3;
	if ($prevmonth<1) {
		$prevmonth = 12;
		$prevyear = $stryear-1;
	} else {
		$prevyear = $stryear;
	}
	$nextmonth = $strmonth+1;
	$nextpred = $pred + 12960000;
	if ($nextmonth>12) {
		$nextmonth = 1;
		$nextyear = $stryear+1;
	} else {
		$nextyear = $stryear;
	}
	if (!$GLOBALS['MainPageCalendar']) {
		print $GLOBALS['doctype'];
		print $GLOBALS['htmlopentag'];
		print "<head><title>" . $GLOBALS['title'] . "</title>";
		DisplayCSS();
		print "</head>";
	}


	if ($_REQUEST['this'] && $_REQUEST['Cust']) {
		print "<body><div>";
		?>
		<script type="text/javascript">
		<!--
		function applydate(e1)
			{

					parent.document.getElementById('<?php echo $_REQUEST['this']; ?>').value = e1;
					parent.$.fancybox.close();
			}

		//-->
		</script>
		<?php
	} elseif ($_REQUEST['this']) {
		print "<body><div>";
		?>
		<script type="text/javascript">
		<!--
		function applydate(e1)
			{
					parent.document.getElementById('<?php echo $_REQUEST['this']; ?>').value = e1;
					parent.document.getElementById('<?php echo $_REQUEST['this']; ?>HF').value = AdjustDate(e1);
					parent.$.fancybox.close();
			}

		//-->
		</script>
		<?php
	} elseif (!$GLOBALS['MainPageCalendar']) {

		if (!$_REQUEST['Alarm']) {
			print "<body><div>";
			?>
			<script type="text/javascript">
			<!--
			function applydate(e1)
				{

						parent.document.forms['EditEntity'].elements['duedate'].value = e1;
						parent.document.forms['EditEntity'].elements['displayDate'].value = AdjustDate(e1);
						parent.$.fancybox.close();

				}

			//-->
			</script>
			<?php
		} else {
			print "<body><div>";
			?>
			<script type="text/javascript">
			<!--
			function applydate(e1)
				{

						window.opener.document.forms['EditEntity'].elements['duedate'].value = e1;
						window.opener.document.forms['EditEntity'].elements['displayDate'].value = AdjustDate(e1);
						window.close();

				}

			//-->
			</script>
			<?php
		}
	}
//function prCalendar($fromyear,$frommonth,$fromday,$href,$username,$session) {
	if (!$GLOBALS['MainPageCalendar']) {
		//print "<table><tr><td >Click on a date to set the due date for this entity. Click <a onclick='parent.$.fancybox.close();'>here</a> to cancel.</td></tr></table><br>";
		print "\n\n<table width='100%'><tr><td><a href='calendar.php?strmonth=" . $prevmonth . "&stryear=" . $prevyear . "&this=" . $this . "'><img src='images/larrow.gif'  alt=''></a></td><td align='right'><a href='calendar.php?strmonth=" . $nextmonth . "&stryear=" . $nextyear . "&pred=" . $nextpred . "&this=" . $this . "'><img src='images/rarrow.gif'  alt=''></a></td></tr></table>";
	}
		prCalendar($stryear,$strmonth,"1","bla","","");
	if (!$GLOBALS['MainPageCalendar']) {
		print "\n\n<table width='100%'><tr><td><a href='calendar.php?strmonth=" . $prevmonth . "&stryear=" . $prevyear . "&this=" . $this . "'><img src='images/larrow.gif'  alt=''></a></td><td align='right'><a href='calendar.php?strmonth=" . $nextmonth . "&stryear=" . $nextyear . "&pred=" . $nextpred . "&this=" . $this . "'><img src='images/rarrow.gif'  alt=''></a></td></tr></table>";
//		print "<table width='100%' ><tr><td><a href='calendar.php?strmonth=" . $prevmonth . "&stryear=" . $prevyear . "&this=" . $this . "'>prev</a></td><td align='right'><a href='calendar.php?strmonth=" . $nextmonth . "&stryear=" . $nextyear . "&pred=" . $nextpred . "&this=" . $this . "'>next</a></td></tr></table>";
	}
	?>
	<script type="text/javascript">
			<!--
				function AdjustDate(date) {
				if ('<?php echo $GLOBALS['UC']['DateFormat'];?>'=='mm-dd-yyyy') {
						day = date.substring(0,2);
						mon = date.substring(3,5);
						yer = date.substring(6,10);
						NewDate = mon + "-" + day + "-" + yer;
				} else if ('<?php echo $GLOBALS['UC']['DateFormat'];?>'=='yyyy-mm-dd') {
						day = date.substring(0,2);
						mon = date.substring(3,5);
						yer = date.substring(6,10);
						NewDate = yer + "-" + mon + "-" + day;
						} else {
						NewDate = date;
				}
				return(NewDate);
			}

			//-->
			</script>
	<?php
	// 				parent.document.forms['EditEntity'].elements['duedate'].value = document.forms['townselect'].elements['town'].value;
}
function makelinks2($input)
{
	// first get http:// and etc
	$input = eregi_replace("[^\"](http://[[:alnum:]#?/&=.,-~]*)",
	" <a href=\"\\1\" >\\1</a>",
	$input);
	// and at the beginning of a line
	$input = eregi_replace("(^[a-z]*://[[:alnum:]#?/&=.,-~]*)",
	" <a href=\"\\1\" >\\1</a>",
	$input);
	// then get the email@hosts
	$input = eregi_replace("(([a-z0-9_]<br>\\-<br>\\.)+@([^[:space:]]*)([[:alnum:]-]))",
	"<a href=\"mailto:\\1\">\\1</a>", $input);
	return($input);
}
$functions_included = yes;
function is_leap_year($year) {
	if ((($year % 4) == 0 and ($year % 100)!=0) or ($year % 400)==0) {
		return 1;
	} else {
		return 0;
	}
}
function iso_week_days($yday, $wday) {
	return $yday - (($yday - $wday + 382) % 7) + 3;
}
function get_week_number($timestamp) {
	$d = getdate($timestamp);
	$days = iso_week_days($d["yday"], $d["wday"]);
	if ($days < 0){
		$d["yday"] += 365 + is_leap_year(--$d["year"]);
		$days = iso_week_days($d["yday"], $d["wday"]);
	}
	else {
		$d["yday"] -= 365 + is_leap_year($d["year"]);
		$d2 = iso_week_days($d["yday"], $d["wday"]);
		if (0 <= $d2) {
			/* $d["year"]++; */
			$days = $d2;
		}
	}
	return (int)($days / 7) + 1;
}
function prCalendar($fromyear,$frommonth,$fromday,$href,$username,$session) {
	global $pred;
	(int)$fromyear;
	(int)$frommonth;
	(int)$fromday;
	global $maincolumn,$maxcolumn,$bdarray,$bdresult;
	$maincolumn=1;
	$maxcolumn=3;
	echo "\n\n<table border='0' cellpadding='2' cellspacing='3' width='500'>";
	//daynumber of the year-
	$yearday= date (z);
	$curyear=(int)date("Y",$pred);
	$curmonth=(int)date("m",$pred);
	$curday=(int)date("d",$pred);
	do{
		//$bdarray= mysql_fetch_array($bdresult);
	} while ($bdarray[dofy]<=$yearday && $bdarray[dofy]!="");
	if ($curyear==$fromyear){
		if ($curmonth==$frommonth){
			prMonth($curyear,$curmonth,$href,$fromday,$curday);
		}
		else {
			prMonth($fromyear,$frommonth,$href,$fromday,32);
			for ($m=$frommonth+1;$m<$curmonth;$m++){
				prMonth($fromyear,$m,$href,0,32);
			}
			prMonth($curyear,$curmonth,$href,0,$curday);
		}
	}
	else {
		prMonth($fromyear,$frommonth,$href,$fromday,32);
		if ($frommonth != 12 ){
			for ($m=$frommonth+1;$m<=12;$m++){
				prMonth($fromyear,$m,$href,0,32);
			}
			for ($y=$fromyear+1;$y<$curyear;$y++){
				for ($m=1;$m<=12;$m++){
					prMonth($y,$m,$href,0,32);
				}
			}
			for ($m=1;$m<$curmonth;$m++){
				prMonth($curyear,$m,$href,0,32);
			}
		} else {
			for ($y=$fromyear+1;$y<$curyear;$y++){
				for ($m=1;$m<=12;$m++){
					prMonth($y,$m,$href,0,32);
				}
			}
			for ($m=1;$m<$curmonth;$m++){
				prMonth($curyear,$m,$href,0,32);
			}
    }
		prMonth($curyear,$curmonth,$href,0,$curday);
	}
	?>
	</table>
	<?php
}
function prMonth($year,$month,$href,$fd,$cd){
	global $lang,$monthcounter,$MonthsToShow,$select,$NoClickToWeek,$maincolumn,$maxcolumn,$bdarray,$bdresult, $color1, $color2, $nav;


	if ($monthcounter<($MonthsToShow+1)) {
		$todayd= date(d);
		$todaym= date(m);
		$todayy= date(Y);
		if ($maincolumn == 1 ){
			print "<tr>\n";
		}
		$first=@mktime(0,0,0,$month,1,$year);
		//print cel waar maandnaam in staat

		print "<td>\n\t\t<table><tr class='calheader'><td colspan='9'>";

		$maandnummer = date("n",$first);
		if ($maandnummer == date("n")) {
			print "<strong>";
			print $lang['month' . $maandnummer] . " " . date('Y', $first);
			print "</strong>";
		} else {
			print $lang['month' . $maandnummer] . " " . date('Y', $first);
		}


		print "</tr>\n";
		print "<tr><td class='weekdays'>" . $lang['week-NOSHOW'] . "</td>\n<td class='weekdays'>" . $lang['monday_short'] . "</td><td class='weekdays'>" . $lang['tuesday_short'] . "</td><td class='weekdays'>" . $lang['wednesday_short'] . "</td><td class='weekdays'>" . $lang['thursday_short'] . "</td><td class='weekdays'>" . $lang['friday_short'] . "</td><td class='weekdays'>" . $lang['saturday_short'] . "</td><td class='weekdays'>" . $lang['sunday_short'] . "</td></tr>\n";
		$wd=@date("w",$first);
		if ($wd==0){
			$wd=7;
		}
		$wdtimeout=date("w");
		if ($wd==0){
			$wd=7;
		}
		$lastday=@date("d",@mktime(0,0,0,$month+1,0,$year));
		$cur=-$wd+1;
		$timeoutdone=0;
		for ($k=1;$k<7;$k++){
			$weekcur= $cur+1;
			$oldtimestamp= @mktime(0,0,0,$month,$weekcur,$year)-604800;
			$oldweek= (get_week_number($oldtimestamp));
			$newtimestamp= time();
			$newweek= (get_week_number($newtimestamp));
			$curmonth= date (m);
			$curhour= date (H);
			if ($cur<=0){
				$timestamp= @mktime(0,0,0,$month,1,$year);
				$weeknum= (get_week_number($timestamp));
				if (!$GLOBALS['MainPageCalendar'] && !$nav) {
						$nonavins = "&nonavbar=1";
				} else {
						$nonavins = "&bla=1";
				}

				echo "<tr>\n<td class='weeknumbers'>" . $weeknum . "\n";

			} elseif ($weekcur>$lastday) {

				echo "<tr><td>\n";

			} else {

				if (!$GLOBALS['MainPageCalendar'] && !$nav) {
						$nonavins = "&nonavbar=1";
				} else {
						$nonavins = "&bla=1";
				}
				$timestamp= @mktime(0,0,0,$month,$weekcur,$year);
				$weeknum= (get_week_number($timestamp));

				echo "<tr>\n<td class='weeknumbers'>" . $weeknum . "</td>\n";

			}
			for ($i=0;$i<7;$i++ ){
				$cur++;
				if (($cur<=0) || ($cur>$lastday)){
					//print dit voor de opvulling van de tabel, zodat de datum onder de goede dag staat
					// dagen van voorgaande en nakomende maanden
					print "<td>&nbsp;</td>";
				} elseif ($cur==$todayd && $month==$todaym && $year==$todayy){
					// vandaag
					$timestamp= @mktime(0,0,0,$month,$cur,$year);
					$maandnaam= (date("F",$timestamp));
						$cur1 = $cur;
						if (strlen($cur1)==1) $cur1 = "0" . $cur1;

						if ($GLOBALS['MainPageCalendar']) {

						//$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE duedate='$cur1-$month-$year' AND deleted<>'y'";
                            //Aggiunto da Daniele Lembo
                            $sql = "SELECT *, " . $GLOBALS['TBL_PREFIX'] . "loginusers.fullname FROM " . $GLOBALS['TBL_PREFIX'] . "entity, " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE  " . $GLOBALS['TBL_PREFIX'] . "entity.assignee =  " . $GLOBALS['TBL_PREFIX'] . "loginusers.id AND duedate='" . mres($cur1 . "-" . $month . "-" . $year) . "'";
							//qlog(INFO, $sql);

							$result= mcq($sql,$db);

							  while ($today= mysql_fetch_array($result)) {
									if ($today['category']<>"" && (CheckEntityAccess($today['eid']) == "ok" || CheckEntityAccess($today['eid']) == "readonly")) {
										$html .= "<a href='edit.php?e=" . $today['eid'] . "' class='smallsort'>";

										$html.= $lang['customer'] . ": " . htme(GetCustomerName($today['CRMcustomer'])) . "<br>" . htme($lang['status']) . ": " . htme($today['status']) . "<br>";
                                        //Aggiunta da Daniele Lembo
                                        $html.= $lang['assignee'] . ": " . htme($today['FULLNAME']) . "<br>";
										$html.= $lang['category'] . ": " . htme($today['category']) . "</a><br>";
										$html.= "-------------------------------<br>";
										$ins = "<span class='noway'>";
										$ins2= PrintToolTipCode($html);
									}
							  }

							  print "<td><a $ins2 href='summary.php?owner=all&assignee=all&CRMcustomer=all&duedate=$cur-$month-$year&csv=onscreenfull&extended=1'  >$ins$cur</span></a></td>\n";
							  unset($html);
							  unset($ins);
							  unset($ins2);
						} else {
							if (strlen($cur)==1) $cur = "0" . $cur;
							if ((strlen($month)==1) && $month<10) {
								$month = "0" . $month;
							}
							print "<td style='cursor: pointer' onclick=\"applydate('$cur-$month-$year')\">" . $cur ."</td>\n";

						}

				} else {
					//Dit wordt geprint als er geen link achter zit. (niet vandaag)
					$timestamp= @mktime(0,0,0,$month,$cur,$year);
					$maandnaam= (date("F",$timestamp));
					$todaynumberofyear= date("z",$timestamp);
					if ($bdarray[1]==$month && $bdarray[2]==$cur){
						//echo "<td><a href='javascript:applydate($timestamp)' onmouseover=\"Statusbalk('$cur $maandnaam wordt";
						$doen=0;
						if (strlen($cur1)==1) $cur1 = "0" . $cur1;

						//echo " jaar');return document.returnValue\">$cur</a></td>\n";
					} else {
						$cur1 = $cur;
						if (strlen($cur1)==1) $cur1 = "0" . $cur1;
						if (strlen($month)==1) $month = "0" . $month;
						if ($GLOBALS['MainPageCalendar']) {
							//$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE duedate='$cur1-$month-$year' AND deleted<>'y'";
                            //Aggiunto da Daniele Lembo
                            $sql = "SELECT " . $GLOBALS['TBL_PREFIX'] . "entity.*, " . $GLOBALS['TBL_PREFIX'] . "loginusers.fullname FROM " . $GLOBALS['TBL_PREFIX'] . "entity,  " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE  " . $GLOBALS['TBL_PREFIX'] . "entity.assignee =  " . $GLOBALS['TBL_PREFIX'] . "loginusers.id  AND  duedate='" . mres($cur1 . "-" . $month . "-" . $year) . "'";
							$result= mcq($sql,$db);

							$ins = "<span style='color: #000000;'>";
							//print "<td>$sql</td>";

							  while ($today= mysql_fetch_array($result)) {
									if ($today['category']<>"" && (CheckEntityAccess($today['eid']) == "ok" || CheckEntityAccess($today['eid']) == "readonly")) {

										$html .= "<a href='edit.php?e=" . $today['eid'] . "' class='smallsort'>";
										$html.= $lang['customer'] . ": " . htme(GetCustomerName($today['CRMcustomer'])) . "<br>" . htme($lang['status']) . ": " . htme($today['status']) . "<br>";
                                        //Aggiunta da Daniele Lembo
                                        $html.= $lang['assignee'] . ": " . htme($today['fullname']) . "<br>";
										$html.= $lang['category'] . ": " . htme($today['category']) . " </a><br>";
										$html.= "-------------------------------<br>";
										$ins = "<span class='noway'>";
										$ins2= PrintToolTipCode($html);
									}
							  }

							print "<td class='monthdays'><a $ins2 href='summary.php?owner=all&assignee=all&CRMcustomer=all&duedate=$cur-$month-$year&csv=onscreenfull&extended=1'  >$ins$cur</span></a></td>\n";
							// Bovenstaande: als geen entity gevonden!
							unset($html);
							unset($ins);
							unset($ins2);
						} else {
							if (strlen($cur)==1) $cur = "0" . $cur;
							if ((strlen($month)==1) && $month<10) {
								$month = "0" . $month;
							}
							print "<td class='monthdays' onmouseover=\"this.style.backgroundColor='#D3D3D3';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" onclick=\"applydate('$cur-$month-$year')\">" . $cur ."</td>\n";
						}
					}
				}
			}
			print "</tr>\n";
		}
		print "</table>\n\n</td>";


		if ($maincolumn == $maxcolumn){
			$maincolumn=1;
			echo "<tr><td colspan=\"$maxcolumn\">\n";
		}
		else{
			$maincolumn++;
		}
	} else {// end if monthcounter < monthstoshow
//		print "fout";
	}
}
function ShowSelectWeek($weeknum,$year,$select) {

	global $lang,$nonavbar;

	$is_admin = is_administrator();

	// weeknum contains timestamp
	$nextweek = $weeknum + 604800;
	$prevweek = $weeknum - 604800;
	?>
	<script type="text/javascript">
	<!--
				function applydate(e1,t1)
			{
					parent.document.forms['EditEntity'].elements['displayDate'].value = AdjustDate(e1);
					parent.$.fancybox.close();
					parent.document.AlertUser('IsChanged');
			}
			function AdjustDate(date) {

				if ('dd-mm-yyyy'=='mm-dd-yyyy') {
					day = date.substring(0,2);
					mon = date.substring(3,5);
					yer = date.substring(6,10);
					NewDate = mon + "-" + day + "-" + yer;
				} else {
					NewDate = date;
				}

				return(NewDate);

			}
	//-->
	</script>
	<?php

	print "<table><tr><td><fieldset><legend>&nbsp;<img src='images/crmlogosmall.gif' alt=''>&nbsp;&nbsp;Calendar&nbsp;</legend><table>";
	print "<tr><td><a href='calendar.php?weekdetail=" . $prevweek . "&year=" . $year. "&nonavbar=" . $nonavbar . "'><img src='images/larrow.gif'  alt=''></a>&nbsp;&nbsp;<a href='calendar.php?weekdetail=" . $nextweek . "&year=" . $year. "&nonavbar=" . $nonavbar . "'><img src='images/rarrow.gif'  alt=''></a></td></tr>";
	print "<tr><td>&nbsp;</td><td>";

	if (strtoupper($GLOBALS['CAL_USEWEEKEND'])=="NO") {
		$NUMDAYS = 5;
	} else {
		$NUMDAYS = 7;
	}
	print "<table border='1' width='100%' cellspacing='0' cellpadding='4'>";

	print "<tr><td>&nbsp;</td>";

	$tmp = $weeknum;
	for ($i=0;$i<$NUMDAYS;$i++) {
		$a = date('l,d M Y',$tmp);
		$tmp += 86400;
		$today = date('l,d M Y');
		$basictoday = date('dMY');
		if ($a == $today) {
			$a = "<span class='noway'>" . $a . "</span>";
		}

		print "<td><strong>" . $a . "</strong></td>";
	}

	print "</tr>";
	print "<tr><td>&nbsp;</td>";

	$tmpr = $weeknum;
	for ($i=0;$i<$NUMDAYS;$i++) {

		$today = date('l,d M Y');
		$basictoday = date('Ymd',$tmpr);
		$tmpr += 86400;
		$sql = "SELECT eid FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE sqldate='" . $basictoday . "' ORDER BY eid LIMIT 5";
		$result = mcq($sql,$db);
		while ($row = mysql_fetch_array($result)) {
				if (CheckEntityAccess($row['eID']) == "ok" || CheckEntityAccess($row['eID']) == "readonly") {
					$b .= "&nbsp;<img src='images/info.gif' alt=''>&nbsp; $num " . $lang['duedate'] . " " . strtolower($lang['entity']) . " " . $row['eID'] . "<br>";
				}
		}
		if (!$b) { $b = "&nbsp;"; }
		print "<td>" . $b . "</td>";
		unset($b);
	}

	print "</tr>";
	$sqla=array();
	$printed = array();

	for ($i=$GLOBALS['CAL_MINHOUR'];$i<$GLOBALS['CAL_MAXHOUR']+1;$i+=.5) {
		if ($i<10) {
			$val = "0";
			$txt = "0";
		}
		if ($ch) {
			$val .= floor($i) . "30";
			$txt .= floor($i) . ":30 h";
			$s30 = 1;
			unset($ch);
		} else {
			$val .= floor($i) . "00";
			$txt .= floor($i) . ":00 h";
			$ch=1;
		}


			if (substr($txt,0,2) == date('G')) {
				print "<tr onmouseover=\"style.background='#FFFFCC';\" onmouseout=\":style.background='#FBFDFF';\"><td valign='top' class='nwrp'><strong><span class='noway'>"  . $txt . "</span></strong></td>";
			} else {
				print "<tr onmouseover=\"style.background='#FFFFCC';\" onmouseout=\"style.background='#FBFDFF';\"><td valign='top' class='nwrp'><strong>"  . $txt . "</strong></td>";
			}

		$tmp = $weeknum;
		$today = date('d-m-Y');



		for ($c=0;$c<$NUMDAYS;$c++) {
			$p=0;
			unset($prt);
			$a = date('l,d M Y',$tmp);
			$dd = date('d-m-Y',$tmp);
			$tmp += 86400;
			// duedate format is DD-MM-YYYYY


			$sql = "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE (LEFT(duetime,2)='07' OR duetime IS NULL OR duetime='0' OR duetime='') AND duedate='" . $dd . "'";

			unset($ins);
			unset($prt);
			unset($p);

			$bgc = "#D4D4D4";

			if ((substr($val,0,2) == date('G')) && ($dd == $today)) {
				$ovwr = 1;
			} else {
				unset($ovwr);
			}
			$result = mcq($sql,$db);
			while ($row = mysql_fetch_array($result)) {

				if ($p<6) {
					if (CheckEntityAccess($row['eid']) != "nok" && !in_array($row['eid'], $printed)) {
						if ($prt) {
							$ins .= "<hr>";
							unset($prt);
						}
						if (strlen($cat)>25) {
							$cat = substr($cat,0,22) . "...";
						}

						$ins .= "<a  style='background: " . GetStatusColor($row['status']) . "'><span style='color: #000000;'>&nbsp;" . htme($row['status']) . "&nbsp;</span></a>:<a href=\"javascript:popupdater(" . $row['eid'] .");\">" . htme($row['category']) ."</a><br>" . htme(GetCustomerName($row['CRMcustomer'])) . "";
						$prt=1;
						$p++;
					}
				}
			array_push($printed, $row['eid']);
			}
			if ($p>5) {
				$ins .= "<br><a href='summary.php?owner=all&assignee=all&CRMcustomer=all&duedate=$dd&csv=short' >&lt;more&gt;</a>";
				unset($p);
			}
			print "<td style='background-color: $bgc;' class='nwrp' valign='top'>";
			if ($ins) {
				print "<table border='1' cellpadding='2' cellspacing='2' style='width: 100%; height: 100%;'><tr><td style='border: 0; background-color: #ffffff;'>";
				if ($_REQUEST['select']) {
					print "<a href=\"javascript:applydate('$dd','$val');\"><img  src='images/timedate.gif' title='Select' alt=''></a><br>";
				} else {
					if (!$GLOBALS['UC']['HIDEADDTAB'] || $is_admin) {
						//print "<a href='edit.php?e=_new_&SetDateTo=" . $dd . "&SetTimeTo=" . $val . "' class='arrow'>" . $lang['add'] . "</a><br>";
					}
				}
				print $ins;
				print "</td></tr></table>";
			}
			print "</td>";
			unset($ins);

//			print "</td>";

		}
		unset($prt);
		unset($val);
		unset($txt);
		print "</tr>";
	}
	print "</table><br></td><td>&nbsp;</td></tr></table></fieldset></td></tr></table>";
}
if (!$GLOBALS['MainPageCalendar']) {
	EndHTML();
}
?>