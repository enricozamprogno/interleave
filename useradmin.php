<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
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

if ($_REQUEST['ActivityUserGraph']) {

} else {
	if (is_administrator()) {
		$_GET['SkipMainNavigation'] = true;
	}
	ShowHeaders();

}

if (CheckFunctionAccess("UserAdmin") <> "ok" && !is_administrator()) {
	PrintAD("Access to this page/function denied.");
} else {

	if (!$_REQUEST['ActivityUserGraph']) {
		if (is_administrator()) {
			admintabs("users");
			UserSectionTabs();
		}
	}

	if ($_REQUEST['CopyDashItemsLayoutToUser'] || $_REQUEST['CopyDashItemsLayoutToGroup']) {
		if ($_REQUEST['CopyDashItemsLayoutToGroup']) {
			$users = db_GetFlatArray("SELECT id FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE profile='" . mres($_REQUEST['CopyDashItemsLayoutToGroup']) . "'");
		} else {
			$users = array($_REQUEST['CopyDashItemsLayoutToUser']);
		}
		$tmp = db_GetRow("SELECT LASTFILTER FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($GLOBALS['USERID']) . "'");
		$curuser = unserialize($tmp['LASTFILTER']);


		foreach ($users AS $user) {
			$tmp = db_GetRow("SELECT LASTFILTER FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE id='" . mres($user) . "'");
			$el = unserialize($tmp['LASTFILTER']);
			$el['dashboard_element_positions_INTLV'] = $curuser['dashboard_element_positions_INTLV'];
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET LASTFILTER='" . mres(serialize($el)) . "' WHERE id='" . mres($user) . "'", $db);
		}
		print "Dashboard layout set.<br>";
	} elseif ($_REQUEST['delete'] == "Delete" && is_numeric($_REQUEST['EditUser'])) {

		if ((!is_administrator() && is_administrator($_REQUEST['EditUser']))) {
			PrintAD("You're not allowed to remove this user (user to be deleted is an administrator and you are not)");
		} elseif (!is_array(GetGroup($_REQUEST['EditUser'])) == "" && !is_administrator()) {
			PrintAD("You're not allowed to remove this user.  (user to be deleted has no group and you are not an administrator)");
		} else {
			log_msg("User " . GetUserName($GLOBALS['USERID']) . " deleted user " . $_REQUEST['EditUser'] . " (" . GetUserName($_REQUEST['EditUser']) . ")");
			journal($_REQUEST['EditUser'], "User " . GetUserName($GLOBALS['USERID']) . " deleted user " . $_REQUEST['EditUser'] . " (" . GetUserName($_REQUEST['EditUser']) . ")", "user");
			mcq("UPDATE " . $GLOBALS['TBL_PREFIX'] . "loginusers SET active='no',name='deleted_user_" . $epoch . "_" . mres(GetUserAccountName($_REQUEST['EditUser'])) . "' WHERE id='" . mres($_REQUEST['EditUser']) . "'", $db);

			DataJournal($_REQUEST['EditUser'], "n", "y", "user-deleted");
			
			$msg = "<span style='color: #ff0000';'>User deleted</span>";
			$_REQUEST['cur'] = true;
		}
		unset($_REQUEST['EditUser']);
		unset($_REQUEST['Saved']);


	} elseif ($_REQUEST['delete'] == "Delete" && is_numeric($_REQUEST['EditGroup'])) {
		if (!is_administrator()) {
			PrintAD("You're not allowed to delete groups.");
		} else {
			DeleteProfile($_REQUEST['EditGroup']);
			log_msg("User " . GetUserName($GLOBALS['USERID']) . " deleted group " . $_REQUEST['EditGroup'] . " (" . GetUserName($_REQUEST['EditUser']) . ")");
			$_REQUEST['profiles'] = true;
			journal($_REQUEST['EditUser'], "User " . GetUserName($GLOBALS['USERID']) . " deleted group " . $_REQUEST['EditUser'] . " (" . GetUserName($_REQUEST['EditUser']) . ")", "group");
			DataJournal($_REQUEST['EditGroup'], "n", "y", "group-deleted");
		}
		unset($_REQUEST['EditGroup']);
		unset($_REQUEST['Saved']);
	} elseif ($_REQUEST['EditGroup'] && !is_administrator()) {
		PrintAD("You're not allowed to change group properties");
		unset($_REQUEST['EditGroup']);
		unset($_REQUEST['Saved']);

	}
	if ($_REQUEST['ChooseCustomer']) {
		$t = db_GetArray("SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "customer ORDER BY custname");
		print "<table class=\"crm\"><thead><tr><td>id</td><td>Name</td></tr></thead>";
		foreach ($t AS $c) {
			print "<tr><td><a onclick=\"parent.document.forms['editprofileform'].elements['n_LIMITTOCUSTOMERS'].value = parent.document.forms['editprofileform'].elements['n_LIMITTOCUSTOMERS'].value + ';" . $c['id'] . "'; parent.$.fancybox.close();\">" . htme($c['id']) . "</a></td><td><a onclick=\"parent.document.forms['editprofileform'].elements['n_LIMITTOCUSTOMERS'].value = parent.document.forms['editprofileform'].elements['n_LIMITTOCUSTOMERS'].value + ';" . $c['id'] . "'; parent.$.fancybox.close();\">" . htme($c['custname']) . "</a></td></tr>";
		}
		print "</table>";

	} else {
		if ($_REQUEST['EditUser']) {
			$conc_table = $GLOBALS['TBL_PREFIX'] . "loginusers";
			$cl = GetClearanceLevel($_REQUEST['EditUser']);
		} elseif ($_REQUEST['EditGroup']) {
			$_REQUEST['EditUser'] = $_REQUEST['EditGroup'];
			$conc_table = $GLOBALS['TBL_PREFIX'] . "userprofiles";
			$EditingProfile = true;
			$cl = GetGroupClearanceLevel($_REQUEST['EditUser']);

		}


		if (!$EditingProfile && is_administrator($_REQUEST['EditUser']) && !is_administrator()) {
			PrintAD("You are not allowed to view/edit this profile.");
		} elseif (!is_administrator() && $_REQUEST['EditUser'] == $GLOBALS['USERID']) {
			PrintAD("You cannot edit your own account.");
		} else {

			if ($_REQUEST['Saved']) {
						if ($_REQUEST['HIDEFROMASSIGNEEANDOWNERLISTS'] == "") {
							$_REQUEST['HIDEFROMASSIGNEEANDOWNERLISTS'] = "n";
						} else {
							$_REQUEST['HIDEFROMASSIGNEEANDOWNERLISTS'] = "y";
						}
						if ($_REQUEST['FORCEPASSCHANGE'] == "") {
							$_REQUEST['FORCEPASSCHANGE'] = "n";
						} else {
							$_REQUEST['FORCEPASSCHANGE'] = "y";
						}

						if ($_REQUEST['dashboardtemplate'] == "Default") {
							$_REQUEST['dashboardtemplate'] = "0";
						}
						if ($_REQUEST['customtabmenu'] == "Default") {
							$_REQUEST['customtabmenu'] = "default";
						}

						if ($_REQUEST['accpass1']<>"") {
							if ($_REQUEST['accpass1'] == $_REQUEST['accpass2']) {
								$pwd = "password=PASSWORD('" . mres($_REQUEST['accpass1']) . "'), LASTPASSCHANGE=NOW(),";
							} else {
								$msg = "Password not saved; they are not the same.";
								$journal_add .= "\nMessage display: Password not saved; they are not the same.";
							}
						} else {
							unset($pwd);
						}

						$sql= "UPDATE " . $conc_table . " SET ";

						if (!in_array("MaySelectColumns", $_REQUEST['AccArr'])) {
							//$sql .= " ELISTLAYOUT='', ";
						}

						// Some important security (direct posting)
						if (!is_administrator()) {
							$adminfunctions = explode(",", "UserAdmin,ExtrafieldAdmin,TriggerAdmin,TemplateAdmin,Administrator");
							foreach ($adminfunctions AS $func) {
								$oldcl = GetClearanceLevel($_REQUEST['EditUser']);
								if (in_array($func, $oldcl)) { // Check if admin rights exists in former profile
									if (!in_array($func, $_REQUEST['AccArr'])) {
										array_push($_REQUEST['AccArr'], $func);
										// Make sure rights are preserved
										$msg_ins = "Original administrative rights ($func) were preserved";
										$journal_add .= "\nOriginal administrative rights ($func) were preserved";
									} else {
										// This is illegal
										$journal_add .= "\nMessage display: This is not good - incident reported.";
										PrintAD("This is not good - incident reported.");
										EndHTML();
										exit;
									}
								}
							}

						}

						$msg .= $msg_ins;

						if (in_array("Administrator", $_REQUEST['AccArr'])) {
							$admin = "yes";
							$t = unserialize('a:16:{i:0;s:9:"EntityAdd";i:1;s:6:"OwnSee";i:2;s:7:"OwnEdit";i:3;s:11:"AssignedSee";i:4;s:12:"AssignedEdit";i:5;s:8:"OtherSee";i:6;s:9:"OtherEdit";i:7;s:11:"CommentsAdd";i:8;s:16:"MaySelectColumns";i:9;s:20:"MayUseMainlistFilter";i:10;s:11:"CustomerAdd";i:11;s:14:"CustomerSeeOwn";i:12;s:15:"CustomerEditOwn";i:13;s:16:"CustomerSeeOther";i:14;s:17:"CustomerEditOther";i:15;s:13:"Administrator";}');
							$t[] = "AllFormsAllowed";

							

							$CLLEVEL = serialize($t);;
							$journal_add .= "\nUsed edited is admin";
						} else {
							$journal_add .= "\nUsed edited is not an admin";
							for ($i=0;$i<sizeof($_REQUEST['AccArr']);$i++) {
								if (strstr($_REQUEST['AccArr'][$i], "|E|")) {
									$_REQUEST['AccArr'][$i] = "";
								}
							}
							$_REQUEST['AccArr'] = FlattenArray($_REQUEST['AccArr']);
							$CLLEVEL = serialize($_REQUEST['AccArr']);
							unset($admin);
						}

						if (sizeof($_REQUEST['statusses']) == 0) {
							$_REQUEST['statusses'][0] = "All";
						}
						if (sizeof($_REQUEST['priorities']) == 0) {
							$_REQUEST['priorities'][0] = "All";
						}


						if (!$EditingProfile) {
							$sql .= $pwd . " PROFILE='" . mres($_REQUEST['profile']) . "', type='normal', administrator='" . mres($admin) . "', EMAIL='" . mres($_REQUEST['EMAIL']) . "',FULLNAME='" . mres($_REQUEST['FULLNAME']) . "', FORCEPASSCHANGE='" . mres($_REQUEST['FORCEPASSCHANGE']) . "', HIDEFROMASSIGNEEANDOWNERLISTS='" . mres($_REQUEST['HIDEFROMASSIGNEEANDOWNERLISTS']) . "',";
						} else {
							if ($_REQUEST['requireuserclimit'] != "y") $_REQUEST['requireuserclimit'] = "n";

							$sql .= " name='" . mres($_REQUEST['newgroupname']) . "', FORCEUSERCLLIMIT='" . mres($_REQUEST['requireuserclimit']) . "',";
						}


						$sql .= " CLLEVEL='" . mres($CLLEVEL) . "', active='yes', RECEIVEDAILYMAIL='" . mres($dailymail) . "', HIDEADDTAB='" . mres($_REQUEST['n_HIDEADDTAB']) . "', HIDECSVTAB='" . mres($_REQUEST['n_HIDECSVTAB']) . "', HIDEPBTAB='" . mres($_REQUEST['n_HIDEPBTAB']) . "', HIDESUMMARYTAB='" . mres($_REQUEST['n_HIDESUMMARYTAB']) . "', HIDEENTITYTAB='" . mres($_REQUEST['n_HIDEENTITYTAB']) . "', SHOWDELETEDVIEWOPTION='" . mres($_REQUEST['n_SHOWDELETEDVIEWOPTION']) . "', HIDECUSTOMERTAB='" . mres($_REQUEST['n_HIDECUSTOMERTAB']) . "', ENTITYEDITFORM='" . mres($_REQUEST['ENTITYEDITFORM']) . "', ENTITYADDFORM='" . mres($_REQUEST['ENTITYADDFORM']) . "', LIMITTOCUSTOMERS='" . mres($_REQUEST['n_LIMITTOCUSTOMERS']) . "', ADDFORMS='" . mres(serialize($_REQUEST['addforms'])) . "', ALLOWEDADDFORMS='" . mres(serialize($_REQUEST['allowedaddforms'])) . "', ALLOWEDSTATUSVARS='" . mres(serialize($_REQUEST['statusses'])) . "', ALLOWEDPRIORITYVARS='" . mres(serialize($_REQUEST['priorities'])) . "', FORCEFORM='" . mres($_REQUEST['ForceToForm']) . "', FORCESTARTFORM='" . mres($_REQUEST['ForceStartForm']) . "', BOSS='" . mres($_REQUEST['user_boss']) . "', DASHBOARDFILEID='" . mres($_REQUEST['dashboardtemplate']) . "', MENUTOUSE='" . mres($_REQUEST['customtabmenu']) . "', ENTITYACCESSEVALMODULE='" . mres($_REQUEST['ENTITYACCESSEVALMODULE']) . "', CUSTOMERACCESSEVALMODULE='" . mres($_REQUEST['CUSTOMERACCESSEVALMODULE']) . "', USERSPECTRUM='" . mres($_REQUEST['spectrum']) . "' WHERE id='" . mres($_REQUEST['EditUser']) . "'";

///						 print "<h1>" . $sql . "</h1>";

						if (!$EditingProfile) {
							$fields = GetExtraUserFields();
							foreach ($fields AS $field) {
								if ($field['underwaterfield'] != "y") {
									if (isset($_REQUEST['EFID' . $field['id']])) {
										$curval = GetExtraFieldValue($_REQUEST['EditUser'], $field['id'], false, true, false);
										if ($curval != $_REQUEST['EFID' . $field['id']]) {
											if (is_array($_REQUEST['EFID' . $field['id']])) {
												$tmp = array();
												foreach($_REQUEST['EFID' . $field['id']] AS $row) {
													if ($row <> "") {
														array_push($tmp, base64_encode($row));
													}
												}
												$_REQUEST['EFID' . $field['id']] = serialize($tmp);
											}
											SetExtraFieldValueSimple($field['id'], $_REQUEST['EditUser'], $_REQUEST['EFID' . $field['id']]);
											journal($_REQUEST['EditUser'], "Field EFID" . $field['id'] . " updated from [" . $curval . "] to [" . $_REQUEST['EFID' . $field['id']] . "]", "user");
										}
									}
								}
							}
							ClearAccessCache('','e',$_REQUEST['EditUser']);
							ClearAccessCache('','c',$_REQUEST['EditUser']);
							ExpireFormCacheByUser($_REQUEST['EditUser']);
							$journal_add .= "\nCache of this user cleared";
						} else {
							$fields = GetExtraGroupFields();
							foreach ($fields AS $field) {
								if ($field['underwaterfield'] != "y") {
									if (isset($_REQUEST['EFID' . $field['id']])) {
										$curval = GetExtraFieldValue($_REQUEST['EditGroup'], $field['id'], false, true, false);
										if ($curval != $_REQUEST['EFID' . $field['id']]) {
												if (is_array($_REQUEST['EFID' . $field['id']])) {
												$tmp = array();
												foreach($_REQUEST['EFID' . $field['id']] AS $row) {
													if ($row <> "") {
														array_push($tmp, base64_encode($row));
													}
												}
												$_REQUEST['EFID' . $field['id']] = serialize($tmp);
											}
											SetExtraFieldValueSimple($field['id'], $_REQUEST['EditGroup'], $_REQUEST['EFID' . $field['id']]);
											journal($_REQUEST['EditGroup'], "Field EFID" . $field['id'] . " updated from [" . $curval . "] to [" . $_REQUEST['EFID' . $field['id']] . "]", "group");
										}
									}
								}
							}
							$members = GetProfileMembers($_REQUEST['EditGroup']);
							$journal_add .= "\nCache of all group members cleared";
							foreach ($members AS $usr) {
								ClearAccessCache('','e',$usr);
								ClearAccessCache('','c',$usr);
								ExpireFormCacheByUser($usr);
								
							}
						}
						$result= mcq($sql,$db);
						CalculateComputedGroupAndUserFields();
						
					//	print $msg . "<br>";
						if ($EditingProfile) {
							//print "<div class=\"noway\" id=\"JS_groupandusersavemessage\">Group profile saved (member cache cleared)</div>";
							journal($_REQUEST['EditUser'], "Group properties of group " . GetGroupName($_REQUEST['EditUser']) . " saved" . $journal_add, "group");
						} else {
							//print "<div class=\"noway\" id=\"JS_groupandusersavemessage\">User profile saved (user cache cleared)</div>";
							journal($_REQUEST['EditUser'], "User properties of user " . GetUserName($_REQUEST['EditUser']) . " saved" . $journal_add, "user");
						}
			}

				
				if ($_REQUEST['AddProfile']) {
					AddBreadCrum("New group");
					if ($_REQUEST['NewGroupName'] != "") {
						mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "userprofiles(name) VALUES('" . mres($_REQUEST['NewGroupName']) . "')", $db);
						$t = mysql_insert_id();
						
						$fields = GetExtraGroupFields();
						foreach ($fields AS $field) {
							if (isset($_REQUEST['EFID' . $field['id']])) {
								$curval = GetExtraFieldValue($t, $field['id'], false, true, false);
								if ($curval != $_REQUEST['EFID' . $field['id']]) {
									SetExtraFieldValueSimple($field['id'], $t, $_REQUEST['EFID' . $field['id']]);
									journal($t, "Field EFID" . $field['id'] . " updated from [" . $curval . "] to [" . $_REQUEST['EFID' . $field['id']] . "]", "group");
								}
							}
						}
						print "<div class=\"noway\" id=\"JS_groupadded\">Group added. Go <a href='useradmin.php?EditGroup=" . $t . "'>here</a> to activate it.</div>";
					} else {
						print "<div class=\"noway\" id=\"JS_groupadderror\">Please enter at least a group name.</div>";
					}
				}

			// Process any buttons (must be the last action)s

				if ($_REQUEST['e_button']) {
					$x = GetAllButtons($_REQUEST['e_button']);
					if ($x[0]['fieldtype'] == "Button") {
							// So, a button was pressed (and the user has the rights to press it)
							qlog(INFO, "An extra field button was pressed. Processing triggers.");
							journal($usr, "User pressed button " . $x['id'] . "::" . $x['name'], "user");
							ProcessTriggers("ButtonPress" . $_REQUEST['e_button'],$eid,"");
						}
					CalculateComputedGroupAndUserFields();
				}
				

			if ($_REQUEST['options']) {
				MustBeAdmin();
				print "<table><tr><td><h1>Import/export users</h1><br>";
				print "<br><a class='plainlink' href='admin.php?ExportUsers=1'> Export accounts (Interleave layout, encrypted)</a><br><br>";
				print "<a class='plainlink' href='admin.php?ImportUsers=1'> Import accounts (Interleave layout, encrypted)</a><br><br>";
				print "<a class='plainlink' href='admin.php?ImportCSVUsers=1&amp;tib=users'> Import accounts (Plain text CSV file)</a><br><br>";
				print "</td></tr>";
				print "</table>";
			
				
			} elseif ($_REQUEST['AddUser']) {
				AddBreadCrum("New user");
				if ($_REQUEST['AddUserSave']) {
					if ($_REQUEST['newuser'] != "") {
						if ($_REQUEST['accpass1'] == $_REQUEST['accpass2']) {
							if (!is_administrator() && (!is_numeric($_REQUEST['newuserprofile']) || $_REQUEST['newuserprofile'] ==0)) {
								PrintAD("You're not allowed to add users without a group!");
							} else {
								if (ValidateEmail($_REQUEST['newemail'])) {
									mcq("INSERT INTO " . $GLOBALS['TBL_PREFIX'] . "loginusers(name,password,FULLNAME,EMAIL,USEDASHBOARDASENTRY,HIDEOVERDUEFROMDUELIST,FORCEPASSCHANGE,LASTPASSCHANGE,PROFILE) VALUES('" . mres($_REQUEST['newuser']) . "',PASSWORD('" . mres($_REQUEST['accpass1']) . "'),'" . mres($_REQUEST['newuserfullname']) . "','" . mres($_REQUEST['newemail']) . "','y','n','n',NOW(),'" . mres($_REQUEST['newuserprofile']) . "')", $db);
									$t = mysql_insert_id();
									if (!is_administrator() && $_REQUEST['newuserprofile'] > 0) {
										ProcessTriggers("user_add_p" . mres($_REQUEST['newuserprofile']), $t, "", "", "");
									}
									$fields = GetExtraUserFields();
									foreach ($fields AS $field) {
										if (isset($_REQUEST['EFID' . $field['id']])) {
											$curval = GetExtraFieldValue($t, $field['id'], false, true, false);
											if ($curval != $_REQUEST['EFID' . $field['id']]) {
												SetExtraFieldValueSimple($field['id'], $t, $_REQUEST['EFID' . $field['id']]);
												journal($t, "Field EFID" . $field['id'] . " updated from [" . $curval . "] to [" . $_REQUEST['EFID' . $field['id']] . "]", "user");
											}
										}
									}
									print "<h1>User added</h1>";
									?>
									<script type="text/javascript">
									<!--
										document.location = 'useradmin.php?EditUser=<?php echo $t;?>';
									//-->
									</script>
									<?php
								} else {
									PrintAD("The e-mail addres you entered is invalid. User not added");
								}
							}
						} else {
							print "Passwords do not match; user not added.";
						}
					} else {
						print "Please enter at least a user name.";
					}

				}
			} elseif ($_REQUEST['cur']) {

				
				
				print "<table class=\"admintable\"><tr><td><h1>Edit account</h1>";
				print "<form id='selectuser' name='selectuser' method='post' action='useradmin.php'><select name='EditUser' id='JS_EditUser'>";
				$sql_ins = "";
				if (!is_administrator()) {
					$sql_ins = " AND administrator!='yes'";
				}
				$sql= "SELECT * FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE active='yes' " . $sql_ins . " ORDER BY name";
				$result= mcq($sql,$db);
				while ($t= mysql_fetch_array($result)) {
					print "<option value='" . $t['id'] . "'>" . htme($t['name']) . " [" . htme($t['FULLNAME']) . "]</option>";
				}
				print "</select>";
				print ReturnDropDownSearchField("JS_EditUser");
				print "&nbsp;<input type='submit' value='Select'>";
				print "</form></td>";

				print "</tr>";
				print "<tr><td>&nbsp;</td></tr>";
				
				print "<tr><td><h1>Create a new account</h1>";
				print "<form name='adduserform' id='adduserform' method='post' action='useradmin.php'>";
				print "<table><tr><td>Login id (e.g. &quot;jdoe&quot;)</td><td><input type='text' name='newuser' id='JS_newuser' ></td></tr>";
				print "<tr><td>Person's full name (e.g. &quot;John Doe&quot;)</td><td><input type='text' size='50' name='newuserfullname' id='JS_newuserfullname'></td></tr>";
				print "<tr><td>E-mail address</td><td><input type='text' size='70' name='newemail' id='JS_newemail' onchange=\"CheckEmailLocal('JS_newemail');\"></td></tr>";
				print "<tr><td>" . $lang['password'] . "</td><td><input type='password' name='accpass1' id='accpass1id' onkeyup=\"InlineCheckPasswordStrength('accpass1id');\"></td></tr>";
				print "<tr><td>" . $lang['password'] . " (confirm) </td><td><input type='password' name='accpass2' id='accpass2id' onkeyup=\"InlineCheckPasswordStrength('accpass2id');\"> </td></tr>";

				$fields = GetExtraUserFields();
				
				foreach ($fields AS $field) {
					if ($field['underwaterfield'] != 'y') {
						print "<tr><td>" . $field['name'] . "</td><td>";
						print GetSingleExtraFieldFormBox("_new_",$field['id'],false,false,false,false,false);
						print "</td></tr>";
					}
				}
			
				$la = GetUserProfiles();
				print "<tr><td>" . $lang['group'] . "</td><td><select name='newuserprofile'>";
				if (is_administrator()) {
					print "<option value=''>- no group -</option>";
				} 
				foreach($la AS $profile) {
					if ($profile[0] != 0) {
						print "<option $ins value='" . $profile[0] . "'>" . $profile[1] . "</option>";
					}
				}
				print "</select> " . $link . "</td></tr>";

				print "</table>";
				print "\n<input type='hidden' name='AddUser' value='true'><input type='hidden' name='AddUserSave' value='true'><input type='button' onclick=\"CheckForm('adduserform');\" name='bla' value='Create user'></form>\n\n";
			

				print "</td></tr>";
				print "<tr><td>&nbsp;</td></tr>";

				
				if (is_administrator()) {

					print "<tr><td>";
					print "<h1>Edit a group</h1>";
					
					print "<form name='editprofileform' id='editprofileform' method='post' action='useradmin.php'>";
					print "<select name='EditGroup' id='JS_EditGroup'>";
					$la = GetUserProfiles();
					foreach($la AS $profile) {
						if ($result['PROFILE'] == $profile[0]) {
							$ins = "selected='selected'";
						} else {
							unset($ins);
						}
						if ($profile[0] != "0") {
							print "<option $ins value='" . $profile[0] . "'>" . $profile[0] . ": " . $profile[1] . "</option>";
						}
					}
					print "</select>";
					print ReturnDropDownSearchField("JS_EditGroup");
					print "&nbsp;<input type='submit' value='Select'>";
				
					print "</form>";
					print "</td></tr>";
					print "<tr><td>&nbsp;</td></tr>";
				
					print "<tr><td>";
						print "<h1>Create a new group</h1>";
					print "<form name='addprofileform' id='addprofileform' method='post' action='useradmin.php'>";
					print "<table>";
					print "<tr><td>New group name</td><td><input type='text' name='NewGroupName' id='JS_NewGroupName'></td></tr>";
					$fields = GetExtraGroupFields();
					
					foreach ($fields AS $field) {
						if ($field['underwaterfield'] != 'y') {
							print "<tr><td>" . $field['name'] . "</td><td>";
							print GetSingleExtraFieldFormBox("_new_",$field['id'],false,false,false,false,false);
							print "</td></tr>";
						}
					}
					print "</table>";
					print "<input type='button' onclick=\"CheckForm('addprofileform');\" name='bla' value='Create group'><input type='hidden' name='AddProfile' value='true'>";
					print "</form>";
					print "</td></tr>";
					print "<tr><td>&nbsp;</td></tr>";
				}
			



			} elseif ($_REQUEST['profiles']) {

				AddBreadCrum("Profiles");
				

			} elseif ($_REQUEST['EditGroup'] || $_REQUEST['EditUser']) {

				if (!$EditingProfile && is_numeric(GetGroup($_REQUEST['EditUser']))) {
					$to_tabs = array("Personals", "Customer limits");
				} elseif (!$EditingProfile && is_administrator($_REQUEST['EditUser'])) {
					$to_tabs = array("Personals", "Privileges", "Customer limits", "Custom code", "Allowed status and priority values", "Forms", "Misc");
				} else {
					$to_tabs = array("Personals", "Privileges", "Extended privileges", "Allowed pages", "Customer limits", "Custom code", "Allowed status and priority values", "Forms", "Misc");
				}

				foreach ($to_tabs AS $toptab) {
					$tabbs[$toptab] = array();
					$href = "javascript:";
					foreach ($to_tabs AS $hidetab) {
						
						if ($hidetab != $toptab) {
							$hidetab = str_replace(" ", "_", $hidetab);
							$href .= "hideLayer('" . $hidetab . "');";
						} else {
							$hidetab = str_replace(" ", "_", $hidetab);
							$href .= "showLayer('" . $hidetab . "');";
						}
					}
					$tabbs[$toptab] = array($href => $toptab);

				}

				print PlainNav($to_tabs, $tabbs, "personals");

				$sql= "SELECT * FROM " . $conc_table . " WHERE id='" . mres($_REQUEST['EditUser']) . "'";
				$result= mcq($sql,$db);
				$result= mysql_fetch_array($result);
				$AccArr = unserialize($result['CLLEVEL']);

				if ($EditingProfile) {
					AddBreadCrum("Edit group " . $result['FULLNAME']);
				} else {
					AddBreadCrum("Edit user " . $result['FULLNAME']);
				}


				print "<form id='editprofileform' name='editprofileform' method='post' action='useradmin.php'><div class='showinline'>";
				print "<input type='hidden' name='e_button' id='JS_e_button' value=''>";
				print "<table class=\"admintable\">";

				if ($EditingProfile) {
					print "<tr><td colspan='2'><a title='Journal' href='javascript:popgroupjournal(" . $_REQUEST['EditUser'] . ");'><img src='images/journal.gif'  alt=''></a>&nbsp;&nbsp;<input type='button' onclick=\"CheckForm('editprofileform')\" name='SubmitButton' value='" . $lang['apply'] . "'>";
					if (is_administrator()) {
						print "&nbsp;&nbsp;" . AttributeLink("group", $result['id']);
					}
					print "&nbsp;&nbsp;<a href='useradmin.php?delete=Delete&amp;EditGroup=" . $_REQUEST['EditGroup'] . "' class='arrow'>Delete</a>";
					print "</td></tr>";

				} else {
					
					

					print "<tr><td colspan='2'><a title='Journal' href='javascript:popuserjournal(" . $_REQUEST['EditUser'] . ");'><img src='images/journal.gif'  alt=''></a>&nbsp;&nbsp;";
					
					print "<input type='button' onclick=\"CheckForm('editprofileform')\" name='SubmitButton' value='" . $lang['apply'] . "'> ";
					
					if (is_administrator()) {
						print "&nbsp;&nbsp;" . AttributeLink("user", $result['id']);
					}

					if (is_administrator() && $_REQUEST['EditUser'] != $GLOBALS['USERID'] && !is_administrator($_REQUEST['EditUser'])) {
						print "&nbsp;&nbsp;<a class='arrow' href='admin.php?BeThisUser=". $_REQUEST['EditUser'] . "'>Log in as " . htme(GetUserName($_REQUEST['EditUser'])) . "</a>";
					} 

					if (!$EditingProfile) {
						print "&nbsp;&nbsp;<a href='useradmin.php?delete=Delete&amp;EditUser=" . $_REQUEST['EditUser'] . "' class='arrow'>Delete</a>";
					} else {
						
					}


				}

				print "</table>";
					
					
					
				//----------------------------------------------------------------
				//--------------------------------------------------------------------
				print "<div id=\"Personals\">";
				print "<table class=\"admintable\">";
				if (is_administrator() && !$EditingProfile) {

						print "<tr><td>Usage graph <a href='javascript:popUserActivityGraph(". $_REQUEST['EditUser'] . ")'><img src='images/graph.gif' alt=''></a></td><td>";
						$last_login = db_GetValue("SELECT timestamp_last_change FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE qs='Login " . mres(GetUserAccountName($_REQUEST['EditUser'])) . "' ORDER BY id DESC");
						print "Last login of this user: " . $last_login . "</td></tr>";
						$row = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE qs='Login " . mres(GetUserAccountName($_REQUEST['EditUser'])) . "'");
						print "<tr><td>Total no. of logins : " . $row[0];
						print "</td><td>";
						
						print "Entities assigned: " . db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE assignee='" . mres($_REQUEST['EditUser']) . "'") . ", owned: " . db_GetValue("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "entity WHERE owner='" . mres($_REQUEST['EditUser']) . "'") . ".</td></tr> ";
						
						$tmp = db_GetRow("SELECT COUNT(*) FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE (content LIKE '%draggable%' OR content LIKE '%hideable%')");
						if ($tmp[0] > 0 && $_REQUEST['EditUser'] != $GLOBALS['USERID']) {
							if (!$EditingProfile) {
								print "<tr><td colspan='2'><a href='useradmin.php?CopyDashItemsLayoutToUser=" . $_REQUEST['EditUser'] . "' class='plainlink'>Set dashboard dynamic items like your dashboard.</a></td></tr>"; // HIER
							} else {
								print "<tr><td colspan='2'><a href='useradmin.php?CopyDashItemsLayoutToGroup=" . $_REQUEST['EditGroup'] . "' class='plainlink'>Set dashboard dynamic items like your dashboard.</a></td></tr>"; // HIER
							}
						}
				}

				if (!$EditingProfile) {
					print "<tr><td colspan='2'><h2>Personals</h2></td></tr>";
					print "<tr><td class='nwrp'>Full name</td><td><input size='50' type='text' name='FULLNAME' value='" . htme($result['FULLNAME']) . "'></td></tr>";
					$type = "Account";
				} else {
					print "<tr><td colspan='2'><h2>Group details</h2></td></tr>";
					$type = "Group";
				}


				if (!$EditingProfile) {
					print "<tr><td class='nwrp'>" . $type . " " . $lang['name'] . "</td><td>" . $result['name'] . "</td></tr>";
					print "<tr><td class='nwrp'>" . $type . " " . $lang['password'] . ":</td><td><input type='password' name='accpass1' value='' id='accpass1id' onkeyup=\"InlineCheckPasswordStrength('accpass1id');\"></td></tr>";
					print "<tr><td class='nwrp'>" . $type . " " . $lang[password] . ": (confirm)</td><td><input type='password' name='accpass2' id='accpass2id' value='' onkeyup=\"InlineCheckPasswordStrength('accpass2id');\"></td></tr>";

					if ($result['HIDEFROMASSIGNEEANDOWNERLISTS'] == "y") {
						$ins = "checked='checked'";
					} else {
						unset($ins);
					}
					print "<tr><td class='nwrp'>Hide this user from owner/assignee lists:</td><td><input type='checkbox' " . $ins . " name='HIDEFROMASSIGNEEANDOWNERLISTS' value='y'></td></tr>";
					if ($result['FORCEPASSCHANGE'] == "y") {
						$ins = "checked='checked'";
					} else {
						unset($ins);
					}
					print "<tr><td class='nwrp'>Force this user to change his/her password at next page load:</td><td><input type='checkbox' " . $ins . " name='FORCEPASSCHANGE' value='y'></td></tr>";

					$fields = GetExtraUserFields();
					foreach ($fields AS $field) {
						if ($field['underwaterfield'] != "y") {
							print "<tr><td>" . $field['name'] . "</td><td>";
							print GetSingleExtraFieldFormBox($_REQUEST['EditUser'],$field['id'],false,false,false,false,false);
							print "</td></tr>";
						}
					}
				} else {
					print "<tr><td class='nwrp'>" . $type . " " . $lang['name'] . "</td><td><input type='text' name='newgroupname' value='" . htme($result['name']) . "'></td></tr>";

					$fields = GetExtraGroupFields();
					foreach ($fields AS $field) {
						print "<tr><td>" . $field['name'] . "</td><td>";
						print GetSingleExtraFieldFormBox($_REQUEST['EditGroup'],$field['id'],false,false,false,false,false);
						print "</td></tr>";
					}
				}
				print "<tr><td>Boss (hierarchical)</td><td><select name='user_boss'>";
				print "<option value='none'>- none -</option>";
				foreach (GetUserList() AS $user_array) {
					if ($result['BOSS'] == $user_array['id']) {
						$ins = "selected='selected'";
					} else {
						unset($ins);
					}
					print "<option " . $ins . " value='" . $user_array['id'] . "'>" . htme($user_array['FULLNAME']) . "</option>";
				}
				print "</select></td></tr>";

				if (!$EditingProfile) {
					print "<tr><td>E-mail:</td><td><input type='text' size='50' name='EMAIL' value='" . $result['EMAIL'] . "'></td></tr>";
//					if (!in_array("Administrator",$AccArr)) {
						$la = GetUserProfiles();
						print "<tr><td>Group:</td><td><select name='profile'>";
						if (is_administrator()) {
							print "<option value=''>- no group -</option>";
						} else {
							
						}
						foreach($la AS $profile) {
							if ($result['PROFILE'] == $profile[0]) {
								$ins = "selected='selected'";
								$link = "<a class='arrow' href='useradmin.php?EditGroup=" . $profile[0] . "'>Edit group</a>";
								$user_has_group = true;
								$user_group = GetProfileArray($profile[0]);
							} else {
								unset($ins);
							}
							if ($profile[0] != 0) {
								print "<option $ins value='" . $profile[0] . "'>" . $profile[1] . "</option>";
							}
						}
						print "</select> ";
						if (is_administrator()) {
							print $link;
						}
						print "</td></tr>";
//					}
				}
				if (!$user_has_group) {
						print "<tr><td colspan='2'><h2>Dashboard</h2></td></tr>";

						print "<tr><td>Dashboard template</td><td>";
						$sql = "SELECT templateid,templatename,templatetype FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_DASHBOARD'";
						$result1 = mcq($sql,$db);
						print "<select name='dashboardtemplate'><option value='Default'>Follow system default</option>";
						while ($row = mysql_fetch_array($result1)) {
							if ($result['DASHBOARDFILEID'] == $row['templateid']) {
								$ins = "selected='selected'";
							} else {
								unset($ins);
							}
							print "<option $ins value='" . $row['templateid'] . "'>" . $row['templatename'] . "</option>";
						}
						print "</select></td></tr>";
						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";

						print "<tr><td colspan='2'><h2>Navigation type</h2></td></tr>";

						print "<tr><td>Navigation</td><td>";
						$sql = "SELECT id, menu_name FROM " . $GLOBALS['TBL_PREFIX'] . "tabmenudefinitions ORDER BY menu_name";
						$result1 = mcq($sql,$db);
						print "<select name='customtabmenu'><option value='Default'>Follow system default</option>";
						while ($row = mysql_fetch_array($result1)) {
							if ($result['MENUTOUSE'] == $row['id']) {
								$ins = "selected='selected'";
							} else {
								unset($ins);
							}
							print "<option $ins value='" . $row['id'] . "'>" . $row['menu_name'] . "</option>";
						}
						print "</select></td></tr>";
				}
				print "</table>";
				
				print "</div>"; // end Personals div
				print "<div id=\"Privileges\" class=\"hide_content\">";
				print "<table class=\"admintable\">";
				print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
				//----------------------------------------------------------------
				//--------------------------------------------------------------------
// hierzo
				if (!$user_has_group || in_array("Administrator",$AccArr)) {

					print "<tr><td colspan='2'><h2>Privileges</h2></td></tr>";
					print "<tr><td colspan='2'><table width='100%'><tr><td valign='top'><table>";
					if (!in_array("Administrator",$AccArr)) {
						print "<tr><td>&nbsp;</td></tr>";
						print "<tr><td><strong>Entities</strong> (" . $lang['entities'] . ")</td></tr>";
						if (in_array("EntityAdd",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can add new entities</td><td><input type='checkbox' value='EntityAdd' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("OwnSee",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can see owned entities</td><td><input type='checkbox' value='OwnSee' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("OwnEdit",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can edit owned entities</td><td><input type='checkbox' value='OwnEdit' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("AssignedSee",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can see assigned entities</td><td><input type='checkbox' value='AssignedSee' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("AssignedEdit",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can edit assigned entities</td><td><input type='checkbox' value='AssignedEdit' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("OtherSee",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can see other entities</td><td><input type='checkbox' value='OtherSee' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("OtherEdit",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can edit other entities</td><td><input type='checkbox' value='OtherEdit' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("CommentsAdd",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can add comments to read-only entities</td><td><input type='checkbox' value='CommentsAdd' name='AccArr[]' " . $checked . "></td></tr>";


						if (in_array("NoOwnNoAssign",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Not allowed to assign or own entities (limited user)</td><td><input type='checkbox' value='NoOwnNoAssign' name='AccArr[]' " . $checked . " " . PrintToolTipCode("Entities added by this user will be treated as 'inserted' entities") . "></td></tr>";



						if (in_array("NoViewDeleted",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Not allowed to see deleted entities</td><td><input type='checkbox' value='NoViewDeleted' name='AccArr[]' " . $checked . "></td></tr>";
						if (in_array("NoEditDeleted",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Not allowed to edit deleted entities</td><td><input type='checkbox' value='NoEditDeleted' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("NoMassUpdate",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Not allowed to use mass-update</td><td><input type='checkbox' value='NoMassUpdate' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("CannotChangeCustomer",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Not allowed to change the customer of an entity</td><td><input type='checkbox' value='CannotChangeCustomer' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("CannotChangeOwner",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Not allowed to change owner of entity</td><td><input type='checkbox' value='CannotChangeOwner' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("CannotChangeAssignee",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Not allowed to change assignee of entity</td><td><input type='checkbox' value='CannotChangeAssignee' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("BlockAllEntityAccess",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Block access to all entities (overrules all)</td><td><input type='checkbox' value='BlockAllEntityAccess' name='AccArr[]' " . $checked . "></td></tr>";

						print "<tr><td>&nbsp;</td></tr>";
						print "<tr><td><strong>Lay-out</strong></td></tr>";
						if (in_array("MaySelectColumns",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>May select columns in lists</td><td><input type='checkbox' value='MaySelectColumns' name='AccArr[]' " . $checked . "></td></tr>";
						if (in_array("MayUseMainlistFilter",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>May use filter in lists</td><td><input type='checkbox' value='MayUseMainlistFilter' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("HideListExportIcons",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Hide <strong>all</strong> list export icons</td><td><input type='checkbox' value='HideListExportIcons' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("HideListExportIconsPDF",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Hide PDF-icon on main entity list</td><td><input type='checkbox' value='HideListExportIconsPDF' name='AccArr[]' " . $checked . "></td></tr>";

						//if (in_array("HideListExportIconsGANTT",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						//print "<tr><td>Hide gantt-chart icon on main entity list</td><td><input type='checkbox' value='HideListExportIconsGANTT' name='AccArr[]' " . $checked . "></td></tr>";
						
						//if (in_array("HideListExportIconsMI",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						//print "<tr><td>Hide management-information icon on main entity list</td><td><input type='checkbox' value='HideListExportIconsMI' name='AccArr[]' " . $checked . "></td></tr>";
						
						if (in_array("HideListExportIconsExcelDirect",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Hide direct-excel icon on main entity list</td><td><input type='checkbox' value='HideListExportIconsExcelDirect' name='AccArr[]' " . $checked . "></td></tr>";
						
						if (in_array("HideListExportIconsExcelCF",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Hide choose-fields-excel icon on main entity list</td><td><input type='checkbox' value='HideListExportIconsExcelCF' name='AccArr[]' " . $checked . "></td></tr>";
						
						if (in_array("HideListExportIconsEntityReport",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Hide entity-report icon on main entity list</td><td><input type='checkbox' value='HideListExportIconsEntityReport' name='AccArr[]' " . $checked . "></td></tr>";


						if (in_array("HideNavigationTabs",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Hide all navigation tabs</td><td><input type='checkbox' value='HideNavigationTabs' name='AccArr[]' " . $checked . "></td></tr>";
						$t = db_GetRow("SELECT ELISTLAYOUT FROM " . $conc_table . " WHERE id='" . mres($_REQUEST['EditUser']) . "'");
						if (is_array(unserialize($t['ELISTLAYOUT']))) {
							$ins = "[ <strong>set</strong> ]";
						} else {
							$ins = "[ inherit ]";
						}

						if ($EditingProfile) {
							print "<tr><td>Columns to show (entity list)</td><td>" . $ins . " [ <a onclick=\"popprofilechooser(" . $_REQUEST['EditUser'] . ", 'group');\">select</a> ]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>";
						} else {
							print "<tr><td>Columns to show (entity list)</td><td>" . $ins . " [ <a onclick=\"popprofilechooser(" . $_REQUEST['EditUser'] . ", 'user');\">select</a> ]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>";
						}

						print "<tr><td>&nbsp;</td></tr>";
						print "<tr><td><strong>Selections</strong></td></tr>";
						if (in_array("HideSelectionsSelectBox",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Hide saved-selections drop box</td><td><input type='checkbox' value='HideSelectionsSelectBox' name='AccArr[]' " . $checked . "></td></tr>";
						if (in_array("AddEditSelections",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Allowed to add/edit selections</td><td><input type='checkbox' value='AddEditSelections' name='AccArr[]' " . $checked . "></td></tr>";
						if (in_array("MayShareSelections",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Allowed to share selections with other users</td><td><input type='checkbox' value='MayShareSelections' name='AccArr[]' " . $checked . "></td></tr>";
						if (in_array("AllowedToEditSystemWideSelections",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Allowed to manage system-wide selections</td><td><input type='checkbox' value='AllowedToEditSystemWideSelections' name='AccArr[]' " . $checked . "></td></tr>";


					}
					if (!$EditingProfile && is_administrator()) {
						print "<tr><td>&nbsp;</td></tr>";
						print "<tr><td><strong>Administrator</strong></td></tr>";
						if ($_REQUEST['EditUser'] == $GLOBALS['USERID'] && is_administrator()) {
							$ins1 = "disabled='disabled'";
							$ins2 = "<tr><td colspan='2'><em>To withdraw your own administrative rights, login with another administrator account.</em></td></tr>";
							print "<tr><td>Is an administrator (overrules all)</td><td><input type='checkbox' value='Administrator' name='UnImp' disabled='disabled' checked='checked'><input type='hidden' name='AccArr[]' value='Administrator'></td></tr>";
						} else {
							if (is_administrator($_REQUEST['EditUser'])) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Is an administrator (overrules all)</td><td><input type='checkbox' value='Administrator' name='AccArr[]' " . $checked . "></td></tr>";
						}
						print $ins2;
					} 

					if ((!is_administrator($_REQUEST['EditUser']) || $EditingProfile) && is_administrator()) {
						if (in_array("ViewAllRecords",$AccArr)) { 
							$checked = "checked='checked'"; 
						} else { 
							unset($checked); 
						}
						
						print "<tr><td>Skip security checks for entity, customer and flextables records and for all extra fields</td><td><input type='checkbox' value='ViewAllRecords' name='AccArr[]' " . $checked . "></td></tr>";
					}	
					print "<tr><td>&nbsp;</td></tr>";
					print "<tr><td colspan='2'><strong>Interactive fields</strong></td></tr>";
					print "<tr><td colspan='2'>Interactive fields are fields which can be changed right in the list, without opening a record.</td></tr>";
					if ($EditingProfile) {
						print "<tr><td>Select fields</td><td><a href=\"javascript:PopRightsChooserInteractiveFields(" . $result['id'] . ", 'profile');\">[select]</a></td></tr>";
					} else {
						print "<tr><td>Select fields</td><td><a href=\"javascript:PopRightsChooserInteractiveFields(" . $result['id'] . ", 'user');\">[select]</a></td></tr>";
					}

					print "<tr><td style=\"max-width: 350px; white-space: wrap;\">Currently selected fields:<br><em>";
					$ins = "";
					foreach (unserialize($result['INTERACTIVEFIELDS']) AS $field) {
				
						if (is_numeric($field)) {
							print $ins . GetExtraFieldName($field);
						} else {
							print $ins . $lang[$field];
						}
						$ins = ", ";
					}
					if ($ins == "") print "None";
					print "</em></td></tr>";

					

	
					if (!in_array("Administrator",$AccArr)) {
						print "</table></td><td valign='top'><table>";
						print "<tr><td>&nbsp;</td></tr>";
						print "<tr><td><strong>Customers</strong> (" . $lang['customers'] . ")</td></tr>";
						if (in_array("CustomerAdd",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can add new customers</td><td><input type='checkbox' value='CustomerAdd' name='AccArr[]' " . $checked . "></td></tr>";


						if (in_array("CustomerSeeOwn",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can see owned customers</td><td><input type='checkbox' value='CustomerSeeOwn' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("CustomerEditOwn",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can edit owned customers</td><td><input type='checkbox' value='CustomerEditOwn' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("CustomerSeeOther",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can see other customers</td><td><input type='checkbox' value='CustomerSeeOther' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("CustomerEditOther",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Can edit other customers</td><td><input type='checkbox' value='CustomerEditOther' name='AccArr[]' " . $checked . "></td></tr>";

						if (in_array("DenyCustomerDownloads",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Deny customer downloads</td><td><input type='checkbox' value='DenyCustomerDownloads' name='AccArr[]' " . $checked . "></td></tr>";
						
						if (in_array("BlockAllCustomerAccess",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td>Block access to all customers (overrules all)</td><td><input type='checkbox' value='BlockAllCustomerAccess' name='AccArr[]' " . $checked . "></td></tr>";

						print "<tr><td>&nbsp;</td></tr>";
						print "<tr><td><strong>Privacy</strong></td></tr>";
						print "<tr><td>Hide all usernames</td><td>";

						if (in_array("HideUserNames",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }

						print "<input type='checkbox' value='HideUserNames' name='AccArr[]' " . $checked . "></td></tr>";
						print "<tr><td>Deny access to all journals</td><td>";
						if (in_array("DenyJournalAccess",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<input type='checkbox' value='DenyJournalAccess' name='AccArr[]' " . $checked . "></td></tr>";
						print "<tr><td>When showing journals, show only own updates</td><td>";
						if (in_array("ShowOnlyOwnJournalRecords",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<input type='checkbox' value='ShowOnlyOwnJournalRecords' name='AccArr[]' " . $checked . "></td></tr>";

						print "<tr><td>&nbsp;</td></tr>";
						//print "<tr><td><strong>Management information</strong></td></tr>";
						//if (in_array("ManagementInfo",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						//print "<tr><td>Can view management information</td><td><input type='checkbox' value='ManagementInfo' name='AccArr[]' " . $checked . "></td></tr>";

						print "<tr><td>&nbsp;</td></tr>";
						//print "<tr><td><strong>Locale settings</strong></td></tr>";
						//if (in_array("InputNumbersWithSeperators",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }



						if (is_administrator()) {
							print "<tr><td>&nbsp;</td></tr>";
							print "<tr><td><strong>Administrative tasks</strong></td></tr>";
							if (in_array("UserAdmin",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Can add/edit users</td><td><input type='checkbox' value='UserAdmin' name='AccArr[]' " . $checked . "></td></tr>";
							
							if (in_array("SendMessagesToAllUsers",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to send messages to all users at once</td><td><input type='checkbox' value='SendMessagesToAllUsers' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("ExtrafieldAdmin",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Can add/edit extra fields</td><td><input type='checkbox' value='ExtrafieldAdmin' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("TriggerAdmin",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Can add/edit triggers</td><td><input type='checkbox' value='TriggerAdmin' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("TemplateAdmin",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Can add/edit templates</td><td><input type='checkbox' value='TemplateAdmin' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("StatusAndPrioAdmin",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Can add/edit status &amp; priority values</td><td><input type='checkbox' value='StatusAndPrioAdmin' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("AllowedToAlterDiaryContents",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to change and delete previously added diary remarks</td><td><input type='checkbox' value='AllowedToAlterDiaryContents' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("AllowedToExportXML",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to export XML</td><td><input type='checkbox' value='AllowedToExportXML' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("AllowedToImportEntities",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to import entities</td><td><input type='checkbox' value='AllowedToImportEntities' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("AllowedToImportCustomers",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to import customers</td><td><input type='checkbox' value='AllowedToImportCustomers' name='AccArr[]' " . $checked . "></td></tr>";
							
							foreach (GetFlextableDefinitions() AS $ft) {
									$configname = "AllowedToImportFT" . $ft['recordid'];

									if (in_array($configname,$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
									print "<tr><td>Allowed to import records into flextable " . htme($ft['tablename']) . "</td><td><input type='checkbox' value='" . $configname . "' name='AccArr[]' " . $checked . "></td></tr>";
							}

							if (in_array("AllowedToImportUsers",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to import users</td><td><input type='checkbox' value='AllowedToImportUsers' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("AllowedTodedupEntities",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to deduplicate entities</td><td><input type='checkbox' value='AllowedTodedupEntities' name='AccArr[]' " . $checked . "></td></tr>";

							if (in_array("AllowedTodedupCustomers",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
							print "<tr><td>Allowed to deduplicate customers</td><td><input type='checkbox' value='AllowedTodedupCustomers' name='AccArr[]' " . $checked . "></td></tr>";
							
							foreach (GetFlextableDefinitions() AS $ft) {
									$configname = "AllowedTodedupFT" . $ft['recordid'];

									if (in_array($configname,$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
									print "<tr><td>Allowed to deduplicate flextable " . htme($ft['tablename']) . "</td><td><input type='checkbox' value='" . $configname . "' name='AccArr[]' " . $checked . "></td></tr>";
							}



							print "<tr><td colspan='2' style='white-space: wrap; width: 200px;'><p><em>Links to some administrative functions will appear on the dashboard - make sure to use the #NAV# tag to display them. The best way to point users to these functions is by using hyperlinks in your templates.</em></p></td></tr>";
						}
					}
					print "</table>";
					
					print "</td></tr>";
					
					print "</table></td></tr>";
					print "</table></div>";
					print "<div id=\"Extended_privileges\" class=\"hide_content\">";
					print "<table class=\"admintable\">";
					//----------------------------------------------------------------
					//--------------------------------------------------------------------
					if (!in_array("Administrator", $AccArr)) {

						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";

						if (in_array("ExtendedPrivileges",$AccArr)) { $ins = "This profile has extended privileges set!"; } else { $ins = "[ none set ]"; }

						print "<tr><td colspan='2'><h2>Enable / disable this setting</h2></td></tr>";
						if (in_array("ExtendedPrivileges",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						if ($EditingProfile) {
							print "<tr><td>Enable extended privileges for this group:</td><td><input type='checkbox' value='ExtendedPrivileges' name='AccArr[]' " . $checked . "></td></tr>";
						} else {
							print "<tr><td>Enable extended privileges for this user:</td><td><input type='checkbox' value='ExtendedPrivileges' name='AccArr[]' " . $checked . "></td></tr>";
						}
						if ($checked) {
							print "<tr><td colspan='2'><h2>Extended privileges</h2>&nbsp;" . $ins . "</td></tr>";
							print "<tr><td colspan='2'>";
							print "<div id='ExtendedPriv'>";
							print " [ <a onclick=\"toggleLayer('extendedprivsentity');\"> entity </a> ] ";
							$tables = array("entity");
							foreach (GetFlextableDefinitions() AS $ft) {
								print " [ <a onclick=\"toggleLayer('extendedprivs" . $ft['recordid'] . "');\"> " . $ft['tablename'] . " </a> ] ";
								$tables[] = $ft['recordid'];
							}


							foreach ($tables AS $table) {
								$stp = false;
								print "<div class=\"scroll\" id=\"extendedprivs" . $table . "\" style=\"display: none; width: 1000px; border: 1px solid #e4e4e4;\">";
								if ($table == "entity") {
									print "<h1>" . $lang['entities']. "</h1>";
								} else {
									print "<h1>" . GetFlextableName($table) . "</h1>";
								}
								print "<table class=\"crm\" style=\"width: 90%;\">";
								print "<thead><tr><td><strong>When field...</strong></td><td><strong>... has value ...</strong></td><td><strong>.. entity will be</strong></td></tr></thead>";
								
								if ($table == "entity") {
										$stp = true;

										$stat = GetStatusses();
										foreach ($stat AS $status) {

											$efname1 = "STATUS|V|" . base64_encode($status);
											$efname2 = "STATUS|E|" . base64_encode($status);
											$efname3 = "STATUS|B|" . base64_encode($status);

											$checked = "";
											$htmlins = "";
											if (in_array($efname2,$AccArr) && !in_array($efname2,$AccArr)) {
												$checked = "selected='selected'";
											}
											$statlus = "<option value='" . $efname2 . "' " . $checked . ">[no restriction]</option>";

											$checked = "";
											$htmlins = "";
											if (in_array($efname1,$AccArr)) {
												$checked = "selected='selected'";
												$htmlins = " style='background:#fde6bb'";
											}
											$statlus .= "<option value='" . $efname1 . "' " . $checked . ">Read-only</option>";

											$checked = "";
						
											if (in_array($efname3,$AccArr)) {
												$checked = "selected='selected'";
												$htmlins = " style='background-color: #FF9966;'";
											}

											$statlus .= "<option value='" . $efname3 . "' " . $checked . ">Invisible</option>";

											print "<tr><td align='left' " . $htmlins . ">Status&nbsp;&nbsp;&nbsp;</td><td align='left' " . $htmlins . ">" . htme($status) . "</td><td><select name='AccArr[]'>" . $statlus . "</select></td></tr>";
										}
										$prio = GetPriorities();
										unset($prios);
										foreach ($prio AS $priority) {

											$efname1 = "PRIORITY|V|" . base64_encode($priority);
											$efname2 = "PRIORITY|E|" . base64_encode($priority);
											$efname3 = "PRIORITY|B|" . base64_encode($priority);

											$checked = "";
											$htmlins = "";
											if (in_array($efname2,$AccArr)) {
												$checked = "selected='selected'";
											}
											$priolus = "<option value='" . $efname2 . "' " . $checked . ">[no restriction]</option>";

											$checked = "";
								
											if (in_array($efname1,$AccArr) && !in_array($efname2,$AccArr)) {
												$checked = "selected='selected'";
												$htmlins = " style='background:#fde6bb'";
											}
											$priolus .= "<option value='" . $efname1 . "' " . $checked . ">Read-only</option>";

											$checked = "";
											$htmlins = "";
											if (in_array($efname3,$AccArr)) {
												$checked = "selected='selected'";
												$htmlins = " style='background:#FF9966'";
											}
											$prios .= "<option value='" . $efname3 . "' " . $checked . ">Invisible</option>";
											print "<tr><td align='left' " . $htmlins . ">Priority&nbsp;&nbsp;&nbsp;</td><td align='left' " . $htmlins . ">" . htme($priority) .	"</td><td><select name='AccArr[]'>";
											print $priolus;
											print "</select></td></tr>";
										}
								}


								if ($table == "entity") {
									$ef = GetExtraFields();
								} else {
									$ef = GetExtraFlextableFields($table);
								}

								foreach ($ef AS $extrafield) {
									if ($extrafield['fieldtype'] == "drop-down") {
										$opt = unserialize($extrafield['options']);
										foreach ($opt AS $option) {

											$efname1 = "EFID" . $extrafield['id'] . "|V|" . base64_encode($option);
											$efname2 = "EFID" . $extrafield['id'] . "|E|" . base64_encode($option);
											$efname3 = "EFID" . $extrafield['id'] . "|B|" . base64_encode($option);

											$checked = "";
											$htmlins = "";
											if (in_array($efname2,$AccArr))
											{
												$checked = "selected='selected'";
											}
											$ef = "<option value='" . $efname2 . "' " . $checked . ">[no restriction]</option>";

											$checked = "";
											$htmlins = "";
											if (in_array($efname1,$AccArr) && !in_array($efname2,$AccArr)) {
												$checked = "selected='selected'";
												$htmlins = " style='background:#fde6bb'";
											}
											$ef .= "<option value='" . $efname1 . "' " . $checked . ">Read-only</option>";

											$checked = "";
									
											if (in_array($efname3,$AccArr)) {
												$checked = "selected='selected'";
												$htmlins = " style='background:#FF9966'";
											}
											$ef .= "<option value='" . $efname3 . "' " . $checked . ">Invisible</option>";

											print "<tr><td align='left' " . $htmlins . ">" . htme($extrafield['name']) . "&nbsp;&nbsp;&nbsp;</td><td align='left' " . $htmlins . ">" . htme($option) . "</td><td>";
					
											print "<select name='AccArr[]'>";
											print $ef;
											$stp = true;
											print "</select></td></tr>";
										}
									} elseif ($extrafield['fieldtype'] == "User-list of all CRM-CTT users" || $extrafield['fieldtype'] == "User-list of administrative CRM-CTT users" || strstr($extrafield['fieldtype'] , "Users of profile ")) {

											$efname1 = "EFID" . $extrafield['id'] . "|V|" . base64_encode("CURUSER");
											$efname2 = "EFID" . $extrafield['id'] . "|E|" . base64_encode("CURUSER");
											$efname3 = "EFID" . $extrafield['id'] . "|B|" . base64_encode("CURUSER");

											$checked = "";
											$htmlins = "";
											if (in_array($efname2,$AccArr))
											{
												$checked = "selected='selected'";
											}
											$ef = "<option value='" . $efname2 . "' " . $checked . ">[no restriction]</option>";

											$checked = "";
											$htmlins = "";
											if (in_array($efname1,$AccArr) && !in_array($efname2,$AccArr))
											{
												$checked = "selected='selected'";
												$htmlins = " style='background:#fde6bb'";
											}
											$ef .= "<option value='" . $efname1 . "' " . $checked . ">Read-only</option>";

											$checked = "";
						
											if (in_array($efname3,$AccArr))
											{
												$checked = "selected='selected'";
												$htmlins = " style='background:#FF9966'";
											}
											$ef .= "<option value='" . $efname3 . "' " . $checked . ">Invisible</option>";

											print "<tr><td align='left' " . $htmlins . ">" . $extrafield['name'] . "&nbsp;&nbsp;&nbsp;</td><td align='left' " . $htmlins . ">[when not current user]</td><td>";

											print "<select name='AccArr[]'>";
											print $ef;
											$stp = true;
											print "</select></td></tr>";
									} elseif ($extrafield['fieldtype'] == "List of all groups") {

											$efname1 = "EFID" . $extrafield['id'] . "|V|" . base64_encode("CURGROUP");
											$efname2 = "EFID" . $extrafield['id'] . "|E|" . base64_encode("CURGROUP");
											$efname3 = "EFID" . $extrafield['id'] . "|B|" . base64_encode("CURGROUP");

											$checked = "";
											$htmlins = "";
											if (in_array($efname2,$AccArr))
											{
												$checked = "selected='selected'";
											}
											$ef = "<option value='" . $efname2 . "' " . $checked . ">[no restriction]</option>";

											$checked = "";
							
											if (in_array($efname1,$AccArr) && !in_array($efname2,$AccArr))
											{
												$checked = "selected='selected'";
												$htmlins = " style='background:#fde6bb'";
											}
											$ef .= "<option value='" . $efname1 . "' " . $checked . ">Read-only</option>";

											$checked = "";
											$htmlins = "";
											if (in_array($efname3,$AccArr))
											{
												$checked = "selected='selected'";
												$htmlins = " style='background:#FF9966'";
											}
											$ef .= "<option value='" . $efname3 . "' " . $checked . ">Invisible</option>";

											print "<tr><td align='left' " . $htmlins . ">" . $extrafield['name'] . "&nbsp;&nbsp;&nbsp;</td><td align='left' " . $htmlins . ">[when current user not in selected group]</td><td>";

											print "<select name='AccArr[]'>";
											print $ef;
											$stp = true;
											print "</select></td></tr>";
									} elseif ($extrafield['fieldtype'] == "Reference to FlexTable") {
										$ft = GetFlextableDefinitions($extrafield['options']);
										if ($ft[0]['orientation'] == "many_entities_to_one") {
											//$sql = "SELECT DISTINCT(recordid) FROM " . $GLOBALS['TBL_PREFIX'] . "flextable" . $extrafield['options'] . " WHERE deleted='n'";
											//$res_ft = mcq($sql, $db);

											//$optionlist = array();
										//	while ($row_ft = mysql_fetch_array($res_ft)) {
										//		$bla = ParseFlexTableTemplate($extrafield['options'], $row_ft['recordid'], $ft[0]['refer_field_layout'], true, false, false,"plain");
										//		$optionlist[$row_ft['recordid']] = $bla;
										//	}

											asort($optionlist, SORT_STRING);

											foreach ($optionlist AS $record => $option) {

												$efname1 = "EFID" . $extrafield['id'] . "|V|" . base64_encode($record);
												$efname2 = "EFID" . $extrafield['id'] . "|E|" . base64_encode($record);
												$efname3 = "EFID" . $extrafield['id'] . "|B|" . base64_encode($record);

												$checked = "";
												$htmlins = "";
												if (in_array($efname2,$AccArr))
												{
													$checked = "selected='selected'";
												}
												$ef = "<option value='" . $efname2 . "' " . $checked . ">[no restriction]</option>";

												$checked = "";
												$htmlins = "";
												if (in_array($efname1,$AccArr) && !in_array($efname2,$AccArr)) {
													$checked = "selected='selected'";
													$htmlins = " style='background:#fde6bb'";
												}
												$ef .= "<option value='" . $efname1 . "' " . $checked . ">Read-only</option>";

												$checked = "";
												
												if (in_array($efname3,$AccArr)) {
													$checked = "selected='selected'";
													$htmlins = " style='background:#FF9966'";
												}
												$ef .= "<option value='" . $efname3 . "' " . $checked . ">Invisible</option>";

												print "<tr><td align='left' " . $htmlins . ">" . htme($extrafield['name']) . "&nbsp;&nbsp;&nbsp;</td><td align='left' " . $htmlins . ">". $record . ": " . htme($option) . "</td><td>";

												print "<select name='AccArr[]'>";
												print $ef;
												$stp = true;
												print "</select></td></tr>";
											}
										}
									} else {
											
											$optionlist = array("Empty / no value" => "@@@EMPTY@@@", "Contains value (any)" => "@@@NOT_EMPTY@@@");

											foreach ($optionlist AS $record => $option) {

												$efname1 = "EFID" . $extrafield['id'] . "|V|" . base64_encode($option);
												$efname2 = "EFID" . $extrafield['id'] . "|E|" . base64_encode($option);
												$efname3 = "EFID" . $extrafield['id'] . "|B|" . base64_encode($option);

												$checked = "";
												$htmlins = "";
												if (in_array($efname2,$AccArr))
												{
													$checked = "selected='selected'";
												}
												$ef = "<option value='" . $efname2 . "' " . $checked . ">[no restriction]</option>";

												$checked = "";
												$htmlins = "";
												if (in_array($efname1,$AccArr) && !in_array($efname2,$AccArr)) {
													$checked = "selected='selected'";
													$htmlins = " style='background:#fde6bb'";
												}
												$ef .= "<option value='" . $efname1 . "' " . $checked . ">Read-only</option>";

												$checked = "";
												
												if (in_array($efname3,$AccArr)) {
													$checked = "selected='selected'";
													$htmlins = " style='background:#FF9966'";
												}
												$ef .= "<option value='" . $efname3 . "' " . $checked . ">Invisible</option>";

												print "<tr><td align='left' " . $htmlins . ">" . htme($extrafield['name']) . "&nbsp;&nbsp;&nbsp;</td><td align='left' " . $htmlins . ">". $record . "</td><td>";

												print "<select name='AccArr[]'>";
												print $ef;
												$stp = true;
												print "</select></td></tr>";
											}
										
									}
								}
								if (!$stp) {
									print "<tr><td colspan=\"3\">No fields found which can be used for extended priviles</td></tr>";
								}

								print "</table></div>";
							} // end table cycle
							print "</div>";


							print "</td></tr>";
						}
						print "</table>";
					}
					print "</div>";
					print "<div id=\"Allowed_pages\" class=\"hide_content\"><table class=\"admintable\">";
	
					//----------------------------------------------------------------
					//--------------------------------------------------------------------
					if (!in_array("Administrator", $AccArr)) {
						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";

						print "<tr><td colspan='2'><h2>Allowed pages</h2> (secured)</td></tr>";

						if ($result['HIDEADDTAB']=='y') {
							$ins = "selected='selected'";
							unset($ins2);
						} elseif ($result['HIDEADDTAB']=='n') {
							$ins2 = "selected='selected'";
							unset($ins);
						} else {
							unset($ins);
							unset($ins2);
						}
						print "<tr><td>Add an new entity</td><td><select name='n_HIDEADDTAB' $disable_boxes1>$disable_boxes2<option value='1'>Follow system default</option><option value='n' " . $ins2 . ">Always allow</option><option value='y' " . $ins . ">Always disallow</option></select></td></tr>";

						if ($result['HIDECUSTOMERTAB']=='y') {
							$ins = "selected='selected'";
							unset($ins2);
						} elseif ($result['HIDECUSTOMERTAB']=='n') {
							$ins2 = "selected='selected'";
							unset($ins);
						} else {
							unset($ins);
							unset($ins2);
						}
						print "<tr><td>Customers page</td><td><select name='n_HIDECUSTOMERTAB' $disable_boxes1>$disable_boxes2<option value='1'>Follow system default</option><option value='n' " . $ins2 . ">Always allow</option><option value='y' " . $ins . ">Always disallow</option></select></td></tr>";

						if ($result['HIDECSVTAB']=='y') {
							$ins = "selected='selected'";
							unset($ins2);
						} elseif ($result['HIDECSVTAB']=='n') {
							$ins2 = "selected='selected'";
							unset($ins);
						} else {
							unset($ins);
							unset($ins2);
						}
						print "<tr><td>CSV-page (complete downloads):</td><td><select name='n_HIDECSVTAB'$disable_boxes1>$disable_boxes2<option value='1'>Follow system default</option><option value='n' " . $ins2 . ">Always allow</option><option value='y' " . $ins . ">Always disallow</option></select></td></tr>";

						/*if ($result['HIDEPBTAB']=='y') {
							$ins = "selected='selected'";
							unset($ins2);
						} elseif ($result['HIDEPBTAB']=='n') {
							$ins2 = "selected='selected'";
							unset($ins);
						} else {
							unset($ins);
							unset($ins2);
						}
						print "<tr><td>Contacts page</td><td><select name='n_HIDEPBTAB'$disable_boxes1>$disable_boxes2<option value='1'>Follow system default</option><option value='n' " . $ins2 . ">Always allow</option><option value='y' " . $ins . ">Always disallow</option></select></td></tr>";
						*/
						if ($result['HIDESUMMARYTAB']=='y') {
							$ins = "selected='selected'";
							unset($ins2);
						} elseif ($result['HIDESUMMARYTAB']=='n') {
							$ins2 = "selected='selected'";
							unset($ins);
						} else {
							unset($ins);
							unset($ins2);
						}
						print "<tr><td>Summary page:</td><td><select name='n_HIDESUMMARYTAB'$disable_boxes1>$disable_boxes2<option value='1'>Follow system default</option><option value='n' " . $ins2 . ">Always allow</option><option value='y' " . $ins . ">Always disallow</option></select></td></tr>";

						if ($result['HIDEENTITYTAB']=='y') {
							$ins = "selected='selected'";
							unset($ins2);
						} elseif ($result['HIDEENTITYTAB']=='n') {
							$ins2 = "selected='selected'";
							unset($ins);
						} else {
							unset($ins);
							unset($ins2);
						}
						print "<tr><td>Main entity list:</td><td><select name='n_HIDEENTITYTAB'$disable_boxes1>$disable_boxes2<option value='1'>Follow system default</option><option value='n' " . $ins2 . ">Always allow</option><option value='y' " . $ins . ">Always disallow</option></select></td></tr>";
						if ($result['SHOWDELETEDVIEWOPTION']=='n') {
							$ins = "selected='selected'";
							unset($ins2);
						} elseif ($result['SHOWDELETEDVIEWOPTION']=='y') {
							$ins2 = "selected='selected'";
							unset($ins);
						} else {
							unset($ins);
							unset($ins2);
						}
						print "<tr><td>Show deleted entities</td><td><select name='n_SHOWDELETEDVIEWOPTION' $disable_boxes1>$disable_boxes2<option value='1'>Follow system default</option><option value='y' " . $ins2 . ">Always allow</option><option value='n' " . $ins . ">Always disallow</option></select></td></tr>";

						//----------------------------------------------------------------
						//--------------------------------------------------------------------
					}
				}

				print "</table></div>";
				print "<div id=\"Customer_limits\" class=\"hide_content\"><table class=\"admintable\">";
					//edit Jeroen 2012-05-25
					//if (is_administrator()) {

						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";

						print "<tr><td colspan='2'><h2>Customer limitations</h2></td></tr>";
						print "<tr><td colspan='2'>Entities of other customers cannot be seen, all entities of these customers are at least read-only</td></tr>";

						if (is_array($GLOBALS['SAFE_MODE'])) {
							if (!in_array($GLOBALS['USERID'], $GLOBALS['SAFE_MODE']) && stristr($result['LIMITTOCUSTOMERS'], "SELECT")) {
								$ins = "READONLY' ";
							} else {
								unset($ins);
							}
						}

						print "<tr><td valign='top'>Limit to " . strtolower($lang['customer']) . "-numbers</td><td><textarea rows='3' cols='60' name='n_LIMITTOCUSTOMERS'>" . $result['LIMITTOCUSTOMERS'] . "</textarea> [ <a onclick=\"popcustomerchooser(" . $_REQUEST['EditUser'] . ", 'group');\">add</a> ]</td></tr>";
						if (is_administrator()) {
							print "<tr><td colspan='2'>Instead of numbers you can also enter an SQL query in this field which returns the allowed customer id's. Other numbers, separated by semicolons, will still be parsed.</td></tr>";
						}
					//}
					if (!$EditingProfile) {
						if (is_administrator()) {
							if ($user_group['FORCEUSERCLLIMIT'] == "y") {
								print "<tr><td colspan='2'>You <strong>must</strong> select a value in this field!</td></tr>";
							} else {
								print "<tr><td colspan='2'>This is the only setting which is <strong>not</strong> overruled by any group setting.</td></tr>";
							}
						}
					} else {
						print "<tr><td colspan='2'>This is the only setting which <strong>will be</strong> overruled by any personal setting.</td></tr>";
						if ($result['FORCEUSERCLLIMIT'] == "y") {
							$c = "checked='checked'";
						} else {
							unset($c);
						}
						print "<tr><td>Require members of this profile to have a customer limit</td><td><input " . $c . " type='checkbox' name='requireuserclimit' value='y'></td></tr>";
					}
			print "</table></div>";
			print "<div id=\"Custom_code\" class=\"hide_content\"><table class=\"admintable\">";
				if (!$user_has_group || in_array("Administrator",$AccArr)) {
					print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
					print "<tr><td colspan='2'><h2>Custom module to run for entity access</h2></td></tr>";
					print "<tr><td colspan='2'>";
					$tmp = db_GetArray("SELECT mid, module_name, module_description FROM " . $GLOBALS['TBL_PREFIX'] . "modules");

					print "<select name='ENTITYACCESSEVALMODULE'>";
					print "<option value=''>No extra access code evalutation</option>";
					foreach ($tmp AS $mod) {
						$ins = ($result['ENTITYACCESSEVALMODULE'] == $mod['mid']) ? " SELECTED" : "";
						print "<option " . $ins . " value='" . $mod['mid'] . "'>" . $mod['module_name'] . " (" . fillout($mod['module_description'],30,true) . ")</option>";
					}
					print "</select>";

					print "</td></tr>";

					print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
					print "<tr><td colspan='2'><h2>Custom module to run for customer access</h2></td>";
					print "<tr><td colspan='2'>";
					$tmp = db_GetArray("SELECT mid, module_name, module_description FROM " . $GLOBALS['TBL_PREFIX'] . "modules");

					print "<select name='CUSTOMERACCESSEVALMODULE'>";
					print "<option value=''>No extra access code evalutation</option>";
					foreach ($tmp AS $mod) {
						$ins = ($result['CUSTOMERACCESSEVALMODULE'] == $mod['mid']) ? " SELECTED" : "";
						print "<option " . $ins . " value='" . $mod['mid'] . "'>" . $mod['module_name'] . " (" . fillout($mod['module_description'],30,true) . ")</option>";
					}
					print "</select>";

					print "</td></tr>";
	
					print "</table></div>";
					print "<div id=\"Allowed_status_and_priority_values\" class=\"hide_content\"><table class=\"admintable\">";


					if (!$user_has_group) {

						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
						//----------------------------------------------------------------
						//--------------------------------------------------------------------

						$a = GetStatussesFull();
						print "<tr><td colspan='2'><h2>Allowed to set status variables:</h2></td></tr>";
							$cur = @unserialize($result['ALLOWEDSTATUSVARS']);
							if (@in_array("All", $cur)) {
								$check = "checked='checked'";
							} else {
								if (sizeof($cur) <1) {
									$check = "checked='checked'";
								} else {
									unset($check);
								}
							}
							print "<tr><td>";
						//		<td " . PrintToolTipCode("Check the status values this user may assign to an entity") . ">
							print "<strong>All</strong></td><td><input type='checkbox' " . $check . " name='statusses[]' value='All'></td></tr>";
							foreach ($a AS $status) {
								if (@in_array($status['id'], $cur)) {
									$check = "checked='checked'";
								} else {
									unset($check);
								}
								print "<tr><td><div style='background-color: " . $status['color'] . "'><span style='color: #000000;'>" . htme($status['varname']) . "</span></div></td><td><input " . $check . " type='checkbox' name='statusses[]' value='" . $status['id'] . "'> </td></tr>";
						}


						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";

						//----------------------------------------------------------------
						//--------------------------------------------------------------------

						print "<tr><td colspan='2'><h2>Allowed to set priority variables:</h2></td></tr>";

						$a = GetPrioritiesFull();
						$cur = @unserialize($result['ALLOWEDPRIORITYVARS']);
						if (@in_array("All", $cur)) {
								$check = "checked='checked'";
							} else {
								if (sizeof($cur) <1) {
									$check = "checked='checked'";
								} else {
									unset($check);
								}
							}
							print "<tr><td><strong>All</strong></td><td><input type='checkbox' " . $check . " name='priorities[]' value='All'></td></tr>";
							foreach ($a AS $prio) {
									if (@in_array($prio['id'], $cur)) {
										$check = "checked='checked'";
									} else {
										unset($check);
									}
									print "<tr><td><div style='background-color: " . $prio['color'] . "'><span style='color: #000000;'>" . htme($prio['varname']) . "</span></div></td><td><input " . $check . " type='checkbox' name='priorities[]' value='" . $prio['id'] . "'></td></tr>";
								}


						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
						//----------------------------------------------------------------
						//--------------------------------------------------------------------
					print "</table></div>";

					print "<div id=\"Forms\" class=\"hide_content\"><table class=\"admintable\">";
						if (in_array("AllFormsAllowed",$AccArr)) { $checked = "checked='checked'"; } else { unset($checked); }
						print "<tr><td colspan='2'><h2>Enable / disable form access control</h2></td></tr>";
						print "<tr><td>This user/group has view and edit access to all forms</td><td><input type='checkbox' value='AllFormsAllowed' name='AccArr[]' " . $checked . "></td></tr>";
						
						if (!$checked) {

							print "<tr><td colspan='2'><h2>Allowed to view entities with form: (entities composed in other forms will not be visible for this user)</h2></td></tr>";
							$cur = @unserialize($result['ADDFORMS']);

							$sql = "SELECT templateid,template_subject,templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
							$res = mcq($sql, $db);
							if (@in_array("default", $cur)) {
								$check = "checked='checked'";
							} else {
								unset($check);
							}
							//print "<input type='checkbox' " . $check . " name='addforms[]' value='default'> Interleave Default form<br>";
							while ($row = mysql_fetch_array($res)) {
								if (@in_array($row['templateid'], $cur)) {
									$check = "checked='checked'";
								} else {
									unset($check);
								}
								print "<tr><td>" . $row['templatename'] . " (" . $row['template_subject'] . ")</td><td><input " . $check . " type='checkbox' name='addforms[]' value='" . $row['templateid'] . "'></td></tr>";
							}
							print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
							//----------------------------------------------------------------
							print "<tr><td colspan='2'><h2>Allowed to add entities using form (other forms cannot be used to add entities by this user)</h2></td></tr>";
							$cur = @unserialize($result['ALLOWEDADDFORMS']);

							$sql = "SELECT templateid,template_subject,templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
							$res = mcq($sql, $db);
							if (@in_array("default", $cur)) {
								$check = "checked='checked'";
							} else {
								unset($check);
							}
							//print "<input type='checkbox' " . $check . " name='addforms[]' value='default'> Interleave Default form<br>";
							while ($row = mysql_fetch_array($res)) {
								if (@in_array($row['templateid'], $cur)) {
									$check = "checked='checked'";
								} else {
									unset($check);
								}
								print "<tr><td>" . $row['templatename'] . " (" . $row['template_subject'] . ")</td><td><input " . $check . " type='checkbox' name='allowedaddforms[]' value='" . $row['templateid'] . "'></td></tr>";
							}
						}
						
					print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
						// ===================== Force form form
						print "<tr><td colspan='2'><h2>Form forcing</h2></td></tr>";
						print "<tr><td colspan='2'>When enabled, the user will see all entities in this form regardless of the form the entity carries.</td></tr>";
						$cur = $result['FORCEFORM'];
						print "<tr><td>Force form:</td><td>";
						$sql = "SELECT templateid,template_subject,templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
						$res = mcq($sql, $db);
						if ($cur == "no_force") {
							$check = " selected='selected'";
						} else {
							unset($check);
						}
						//print "<input type='checkbox' " . $check . " name='addforms[]' value='default'> Interleave Default form<br>";
						print "<select name='ForceToForm'>";
						print "<option " . $check . " value='no_force'>Do not force forms</option>";
						while ($row = mysql_fetch_array($res)) {
							if ($cur == $row['templateid']) {
								$check = "selected='selected'";
							} else {
								unset($check);
							}
							print "<option " . $check . " value='" . $row['templateid'] . "'> " . $row['templatename'] . " (" . $row['template_subject'] . ")</option>";
						}
						print "</select>";
						print "</td></tr>";
						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";
						//----------------------------------------------------------------
						//--------------------------------------------------------------------

						// ===================== Default opening form

						print "<tr><td colspan='2'><h2>Form forcing when opening</h2></td></tr>";
						print "<tr><td colspan='2'>When enabled, the entity form will be set to this form when opening the entity from the list.</td></tr>";
						$cur = $result['FORCESTARTFORM'];
						print "<tr><td>Force start form:</td><td>";
						$sql = "SELECT templateid,template_subject,templatename FROM " . $GLOBALS['TBL_PREFIX'] . "templates WHERE templatetype='TEMPLATE_HTML_FORM'";
						$res = mcq($sql, $db);
						if ($cur == "no_force") {
							$check = "checked='checked'";
						} else {
							unset($check);
						}
						//print "<input type='checkbox' " . $check . " name='addforms[]' value='default'> Interleave Default form<br>";
						print "<select name='ForceStartForm'>";
						print "<option " . $check . " value='0'>No start form</option>";
						while ($row = mysql_fetch_array($res)) {
							if ($cur == $row['templateid']) {
								$check = "selected='selected'";
							} else {
								unset($check);
							}
							print "<option " . $check . " value='" . $row['templateid'] . "'> " . $row['templatename'] . " (" . $row['template_subject'] . ")</option>";
						}
						print "</select>";
						print "</td></tr>";
					print "</table></div>";
					print "<div id=\"Misc\" class=\"hide_content\"><table class=\"admintable\">";



						print "<tr><td colspan='2'><h2>User/co-worker scope (use this to limit the users this user can &quot;see&quot;)</h2></td></tr>";
						print "<tr><td>Others user to be seen by this user:</td><td>";
					
					
						print "<select name='spectrum'>";
						$ins = ($result['USERSPECTRUM'] == "all") ? " selected='selected'" : "";
						print "<option value='all' " . $ins . ">All (no limit)</option>";
						$ins = ($result['USERSPECTRUM'] == "in_group") ? " selected='selected'" : "";
						print "<option value='in_group' " . $ins . ">Users in same group</option>";
						$ins = ($result['USERSPECTRUM'] == "customer_related") ? " selected='selected'" : "";
						print "<option value='customer_related' " . $ins . ">Users who have access to the same customer</option>";
						$ins = ($result['USERSPECTRUM'] == "none") ? " selected='selected'" : "";
						print "<option value='none' " . $ins . ">None (can only see his/her own useraccount)</option>";
						print "</select></td></tr>";
						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";	

						
						//----------------------------------------------------------------
						//--------------------------------------------------------------------
						if ($result['RECEIVEDAILYMAIL']=="Yes") {
							$ins = "selected='selected'";
						}

					}
				}
				if (is_numeric($_REQUEST['EditGroup'])) {
					$members = GetProfileMembers($_REQUEST['EditGroup']);
					if (sizeof($members) > 0) {
						print "<tr><td style='height: 15px;' colspan='2'></td></tr>";

						print "<tr><td colspan='2'><h2>Current group members</h2></td></tr>";
						print "<tr><td colspan='2' style='width: 500px;'>";
						foreach ($members AS $user) {
							if ($tt) {
								print ", ";
							}
							print "<a href='useradmin.php?EditUser=" . $user . "'>" . htme(GetUserName($user)) . "</a>";
							$tt = true;
						}
						print ".";
						print "</td></tr>";
					}
				}
				if (is_administrator()) {
					print "<tr><td colspan='2'><h2>Compare with</h2></td></tr>";
					print "<tr><td><form name='compform' id='JS_compform' action='compareprofiles.php' method='post'>";
					if ($EditingProfile) {
						print "<input type='hidden' name='s1' value='P" . $_REQUEST['EditUser'] . "'>";
					} else {
						print "<input type='hidden' name='s1' value='U" . $_REQUEST['EditUser'] . "'>";
					}
					print "<select name='s2'>";
					foreach (db_GetArray("SELECT id, name FROM " . $GLOBALS['TBL_PREFIX'] . "userprofiles WHERE active='yes'") AS $profile) {
						print "<option value='P" . $profile['id'] . "'>Profile '" . htme($profile['name']) . "'</option>";
					}
					foreach (db_GetArray("SELECT id, name, FULLNAME FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE active='yes'") AS $user) {
						print "<option value='U" . $user['id'] . "'>User '" . htme($user['FULLNAME']) . "'</option>";
					}

					print "</select><input type='submit' value='Go!'></form></td></tr>";
				}
				print "</table></div>";

				print "<br><br>";
				if ($EditingProfile) {
					print "<input type='hidden' name='EditGroup' value='" . $_REQUEST['EditGroup'] . "'>";
				} else {
					print "<input type='hidden' name='EditUser' value='" . $_REQUEST['EditUser'] . "'>";
				}

				print "<input type='hidden' name='Saved' value='" . $_REQUEST['EditUser'] . "'>";

				print "</div></form>";
				

			} elseif ($_REQUEST['UserStatistics']) {
				MustBeAdmin();
				log_msg("DisplayUserActivityGraph Journal=$Journal","");

				print "<table width='60%'>";
				print "<tr><td><h1>All users activity graphs</h1><br>";


				if (!$_REQUEST['Journal'] && !$_REQUEST['Uselog']) {
						print "<br>Interleave logs in two ways:<br>";
						print "<ul><li>&nbsp;<a href='useradmin.php?UserStatistics=true&amp;Uselog=1'>The use-log</a><br>All actions of user are logged. These actions include every page reload, logins, errors etcetera. This log may contain very much records if you have a heavily used repository; the statistics may take a while to load.</li>";
						print "<li>&nbsp;<a href='useradmin.php?UserStatistics=true&amp;Journal=1'>Journal entries</a><br>The journal entries log more specific activity rather than browse-thru statistics. In the journal things like adding a file, saving an enity, and sending an e-mail are logged. The journal is the best to use for statistics to get an overview of <em>real</em> activity of your users.</li></ul>";

				} else {

						if (!$_REQUEST['Journal']) {
								print "These images are generated using the 'uselog' - e.g. they represent any action; may me <br>just viewing. To see the charts based on the entity journals, <a class='arrow' href='useradmin.php?UserStatistics=true&amp;Journal=1'>click here</a>.<br><br>";
						} else {
								print "User activity charts based on entity journals<br><br>";
						}


						$sql = "SELECT id,FULLNAME,name FROM " . $GLOBALS['TBL_PREFIX'] . "loginusers WHERE active<>'no' ORDER BY FULLNAME";
						$result = mcq($sql,$db);
						while ($row = mysql_fetch_array($result)) {
							if (trim($row['FULLNAME']=="")) { $row['FULLNAME'] = $row['name'];}
							print "<br><strong>" . htme(GetUserName($row['id'])) . "</strong><br>";
							if ($_REQUEST['Journal']==1) {
								
								print CreateHighChartFromSQL(GetUserName($row['id']), 'Entity, customer and flextable logs', "SELECT CONCAT(YEAR(timestamp_last_change), '-', MONTH(timestamp_last_change)) AS hits, COUNT(*) AS JournalEntries FROM " . $GLOBALS['TBL_PREFIX'] . "journal WHERE user=" . mres($row['id']) . " GROUP BY CONCAT(YEAR(timestamp_last_change), '-', MONTH(timestamp_last_change)) ORDER BY YEAR(timestamp_last_change), MONTH(timestamp_last_change)", 'line', 200, 1160);
								//print "<img src='useradmin.php?ActivityUserGraph=" . $row['id'] . "&amp;Journal=1' alt=''><br>";
							} else {
								print CreateHighChartFromSQL(GetUserName($row['id']), 'Uselog only', "SELECT CONCAT(YEAR(timestamp_last_change), '-', MONTH(timestamp_last_change)) AS hits, COUNT(*) AS LogEntries FROM " . $GLOBALS['TBL_PREFIX'] . "uselog WHERE user=" . mres($row['id']) . " GROUP BY CONCAT(YEAR(timestamp_last_change), '-', MONTH(timestamp_last_change)) ORDER BY YEAR(timestamp_last_change), MONTH(timestamp_last_change)", 'line', 200, 1160);

								//print "<img src='useradmin.php?ActivityUserGraph=" . $row['id'] . "&amp;Uselog=1' alt=''><br>";
							}
						}
				}
				print $link;
				print "</td></tr></table>";
			} elseif ($_REQUEST['ActivityUserGraph']) {

				$whom = GetUserName($_REQUEST['ActivityUserGraph']);

				$GLOBALS['CURFUNC'] = "ActivityUserGraph::";
				qlog(INFO, "Generating user activity graph for user " . $whom);
				if ($_REQUEST['Journal']) {
					DisplayUserActivityGraphJournal($_REQUEST['ActivityUserGraph']);
				} else {
					DisplayUserActivityGraph($_REQUEST['ActivityUserGraph']);
				}
				EndHTML(false);
				exit;
			}
		}
	}
}
if (!$_REQUEST['ActivityUserGraph']) {

	EndHTML();
} else {
	EndHTML(true);
}
exit();