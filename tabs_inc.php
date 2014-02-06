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


function tabs($sections, $pages, $navid)
{
	echo "<table border='0' cellpadding='0' cellspacing='0' style='width: 100%; background-image: url(images/stab-bg.gif);'><tr style='height: 24px;'>";
	echo "<td style='width: 40px;'><img src='images/stab-bg.gif' width='40' height='24' alt='' class='dispblck'></td><td>";
	echo "<table border='0' cellpadding='0' cellspacing='0' class='tabsbartbl'><tr style='height: 24px;'>";
	// Prepare Navigation
	//echo $pages	= $phpAds_nav;
	echo "<td></td>";

	for ($i=0; $i<count($sections);$i++)
	{
		list($sectionUrl, $sectionStr) = each($pages["$sections[$i]"]);

		$tmpnav = $navid - 20;
		$navid2 = $tmpnav+20;
		$navid3 = "$tmpnav+20";

		unset($tmpnav);

		if ($navid2 == $sections[$i] || $navid3 == $sections[$i])
		{
			$selected = true;
		} else {
			unset($selected);
		}

		if ($_REQUEST['CT'] == $sections[$i])
		{
			$selected = true;
		} elseif ($_REQUEST['CT'])
		{
			unset($selected);
		}


		if ($selected)
		{
			echo "<td style='background-image: url(images/stab-sb.gif);'>";

			if ($i > 0)
			{
				echo "<img src='images/stab-mus.gif' alt='' class='dispblck'>";
			}
			else
			{
				echo "<img src='images/stab-bs.gif' alt='' class='dispblck'>";
			}
			echo "</td>";

			echo "<td style='background-image: url(images/stab-sb.gif);' class='nwrp tabscel'>";
			$closetag = 0;
			//dit is slordig, maar er kunnen div's of form's in customtabs staan en daar moeten geen <a> en <span> tags om heen
			if ((!stristr($sectionStr, "<form")) && (!stristr($sectionStr, "<div")))
			{
			    echo "<a href='" . htme($sectionUrl) . "'><span class='tabsbarSelected'>";
			    $closetag = 1;
			}
			echo $sectionStr;
			if ($closetag == 1)
			{
			    echo "</span></a>";
			}
			echo "</td>";
		}
		else
		{
			echo "<td style='background-image: url(images/stab-ub.gif);'>";
		
			if ($i > 0)
			{
				if ($previousselected)
				{
					echo "<img src='images/stab-msu.gif' alt='' class='dispblck'>";
				}
				else
				{
					echo "<img src='images/stab-muu.gif' alt='' class='dispblck'>";
				}
			}
			else
			{
				echo "<img src='images/stab-bu.gif' alt='' class='dispblck'>";
			}
			echo "</td>";

			echo "<td style='background-image: url(images/stab-ub.gif);' class='nwrp tabscel'>";
			$closetag = 0;
			//dit is slordig, maar er kunnen div's of form's in customtabs staan en daar moeten geen <a> en <span> tags om heen
			if ((!stristr($sectionStr, "<form")) && (!stristr($sectionStr, "<div")))
			{
				echo "<a href='" . htme($sectionUrl) . "'><span class='tabsbar'>";
				$closetag = 1;
			}
			echo $sectionStr;
			if ($closetag == 1)
			{
			    echo "</span></a>";
			}
			echo "</td>";
		}

		$previousselected = $selected;
	}

	if ($previousselected)
	{
		echo "<td><img src='images/stab-es.gif' alt='' class='dispblck'></td>";
	}
	else
	{
		echo "<td><img src='images/stab-eu.gif' alt='' class='dispblck'></td><td style='background-image: url(images/stab-bg.gif);'></td>";
	}
	echo "</tr></table>";
	echo "</td><td>&nbsp;</td></tr></table>";
}
?>