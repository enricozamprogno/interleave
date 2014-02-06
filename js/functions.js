var mytimer;

function countrowsum(row) {

	total = 0;

	for(i=0; i<document.getElementById('EditEntity').elements.length; i++)
	{
		var name = document.getElementById('EditEntity').elements[i].name;
	
		var ar = name.split("-row");
		var localrow = ar[ar.length-1];
		var classArray = document.getElementById('EditEntity').elements[i].className.split(" ");

		if (localrow == row && in_array("numeric", classArray) && ar[0] != "FLEXTABLEREFERFIELD" )
		{
			
			val = parseInt(document.getElementById('EditEntity').elements[i].value);
			if (val > 0 || val < 0) {
				total += val;
			}
			val = 0;
		}
		
	}
	if (document.getElementById('sumrow' + row))
	{
		document.getElementById('sumrow' + row).innerHTML = total;
	}


	var grandtotal = 0;
	for (i=0;i<4096;i++)
	{
		if (document.getElementById('sumrow' + i)) {
				var val = parseInt(document.getElementById('sumrow' + i).innerHTML);
				if (val > 0 || val < 0) {
					grandtotal += val;
				}
		}
	}
	if (document.getElementById('grandtotal'))
	{
		document.getElementById('grandtotal').innerHTML = grandtotal;
	}
	
}
function countsum(id) {
	id = id.replace('_new_', 'new');
	var ar = id.split("-");
	var ar2 = ar[0].split("_");
	var fieldnum = ar2[ar2.length-1];
	total = 0;

	for(i=0; i<document.getElementById('EditEntity').elements.length; i++)
	{
		var name = document.getElementById('EditEntity').elements[i].name;
		name = name.replace('_new_', 'new');
		
		var ar3 = name.split("-");
		var ar4 = ar3[0].split("_");
		var localfieldnum = ar4[ar2.length-1];

		if (localfieldnum == fieldnum)
		{
			val = parseInt(document.getElementById('EditEntity').elements[i].value);
			if (val > 0 || val < 0)
			{
				
				total += val;
			}
			val = 0;
		}
		
	}
	if (document.getElementById('sum' + fieldnum))
	{
		document.getElementById('sum' + fieldnum).innerHTML = total;
	}
	
}

function SubmitInlineFTForm(formname, divname) {
	document.getElementById('WaitImageDiv').style.visibility='visible';
	$.ajax({
		type: "POST",
		url: "assist.php",
		async: false,
		data: "Function=ReturnInlineFlextableForm&Run=true&AjaxHandler=" + divname + "&" + $("#" + formname).serialize(),
		success: function(data) {

			document.getElementById(divname).innerHTML = data;
			if (typeof UpdateAjaxFields == 'function') {
				UpdateAjaxFields('inlineftsubmitbutton');
			}
			document.getElementById('WaitImageDiv').style.visibility='hidden';
		}
	});
}


function CheckMessages(interval) {
	// alert('check events for ' + calId + ' interval is ' + interval);
	$.ajax({
		type: "POST",
		url: "assist.php",
		data: "checkUserEvents=1",
		success: ProcessMessageReply
	});
	setTimeout("CheckMessages(interval)", interval);
	
}

function ProcessMessageReply(xmlreply) {
	if (xmlreply.substr(0,4) == "MSG:")
	{
		alert(xmlreply.substr(4, (xmlreply.length - 4)));	
	} else {
		// alert('NOPRINT' + xmlreply);
	}
	
}

function UpdateFieldValue(eid, field, value, oldvalue) {
	if (typeof value == "undefined")
	{
		// do nothing
	} else {
		if (eid > 0)
		{

			if (value != oldvalue)
			{
				$.ajax({
				   type: "POST",
				   url: "populate.php",
				   async: false,
				   data: "single=1&efid=" + field + "&eid=" + eid + '&value=' + urlencodejs(value),
				   success: SetListItemValue
				}, [field]);
			}
		}
	}
}
function UpdateDiaryField(eid, field, value, handler, commenthash) {
	if (typeof value == "undefined")
	{
		// do nothing
	} else {
		$.ajax({
		   type: "POST",
		   url: "populate.php",
		   async: false,
		   data: "UpdateDiaryField=1&efid=" + field + "&eid=" + eid + '&value=' + urlencodejs(value) + '&commenthash=' + commenthash,
		   success: function(data) {
			eval('refresh_' + handler + '();');
			}
		}, [field]);
	}	 

}
function UpdateEntityField(eid, field, value, flextableid) {
	if (typeof value == "undefined")
	{
		// do nothing
	} else {
		$.ajax({
		   type: "POST",
		   url: "assist.php",
		   async: false,
		   data: "Function=AutoSaveSingleField&" + field + "=" + urlencodejs(value) + "&e=" + eid + "&SingleField=" + field + "&FlextableId=" + flextableid
		}, [field]);
	}

}
function SetListItemValue(xmlreply)
{
  if (xmlreply.length != 0)
  {
    var prezar = xmlreply.split("|||");
	prezelmt = document.getElementById('list_element_' + prezar[1] +  "_" + prezar[0]);
	if (prezelmt) {
		if (prezar[2].length != 0)
		{
			
			if (prezar[2] == "deleted")
			{
				$('#tr_list_element_' + prezar[1]).fadeOut('slow', function() {
				    // Animation complete
			    });

			} else {
				prezelmt.innerHTML = prezar[2];

			}


			if (prezar[3])
			{

				if (prezar[3] == "redunderline") {
					prezelmt.style.textDecoration = "underline";
					prezelmt.style.color = "#ff0000";
				} else if (prezar[3] == "green") {
					prezelmt.style.color = "#669933";
				} else if (prezar[3] == "red") {
					prezelmt.style.color = "#ff0000";
				} else if (prezar[3] == "normal") {
					prezelmt.style.textDecoration = "none";
					prezelmt.style.color = "#000000";
				} else {
					document.getElementById('td_list_element_' + prezar[0] + '_' + prezar[1]).style.background = prezar[3];
				}
			}
			if (prezar[4])
			{
				UpdateEntityListValue(prezar[4], prezar[1]);
			}

			SwitchIAback(prezar[1],prezar[0]);
				
		} else {
			prezelmt.innerHTML = '[error]';
			
		}
	}	
  }
  else
  {
    /* alert("Cannot handle the AJAX call (sIT)."); */
  }
}

function UpdateEntityListValue(ar, eid) {
	var changed = ar.split("$$$");
	for (var i in changed)
	{
		var el = changed[i].split("%%%");

		//alert('td_list_element_' + el[0] + '_' + prezar[1]);

		var cell    = document.getElementById('td_list_element_' + el[0] + '_' + eid);
		if (cell) {
		
			var newval   = el[1];
			var newcol	 = el[2];
			cell.innerHTML = newval;
			if (newcol)
			{
			
				if (newcol == "redunderline")
				{
					cell.style.textDecoration = "underline";
					cell.style.color = "#ff0000";
				} else if (newcol == "green") {
					cell.style.color = "#669933";
				} else if (newcol == "normal") {
					cell.style.textDecoration = "none";
					cell.style.color = "#000000";
				} else {
					cell.style.background = newcol;
				}
			} else {
				
			}

			cell.className='interactive_list_item';
			cell.onclick='SwitchIA("' + eid + '","' + el[0] + '");';

		} else {
			
		}
	}
	
}

/*function SwitchIAtableheader(field) {
	var div1 = document.getElementById(field);
	div1.style.display = 'inline';
}*/

var Jeroshowtimeoutids = new Array();
var Jerohidetimeoutids = new Array();

function SwitchIAtableheader(field, hidefield) {
	clearTimeout(Jerohidetimeoutids[field]);
	//400msec delay for show
	Jeroshowtimeoutids[field] = setTimeout("SwitchIAtableheaderDoIt('" + field + "', '" + hidefield + "')", 200);
}

function SwitchIAtableheaderDoIt(field, hidefield) {
	if (field && document.getElementById(field))
	{
//		document.getElementById(hidefield).style.display = 'none';
		document.getElementById(field).style.display = 'inline';
	}
}

function SwitchIAtableheaderBack(field, hidefield) {
	if (document.getElementById(field))
	{
		document.getElementById(field).style.display = 'none';
//		document.getElementById(hidefield).style.display = 'inline';
//		var field_zonder_random = field.substring(0,field.length-12);
//		document.getElementById(field_zonder_random + 'text').style.display = 'inline';
	}
}

function SwitchIAtableheaderBackForDelay(field) {
	if (document.getElementById(field))
	{
		clearTimeout(Jeroshowtimeoutids[field]);
	}
}


function SwitchIA(eid, field) {
	$.ajax({
	   type: "POST",
	   url: "populate.php",
	   async: false,
	   data: "ReturnInteractiveFieldBox=1&field=" + field + "&eid=" + eid,
	   success: function(msg) {

			document.getElementById('box_list_element_' + eid + '_' + field).innerHTML = msg;

			$('#list_element_' + eid + '_' + field).fadeOut(50, function() {
				var div2 = document.getElementById('box_list_element_' + eid + '_' + field);
				if (div2)
				{
					div2.style.display = 'inline';
					$('#INT_EL_' + eid + '_' + field).focus();
				} else {
					alert('Something went wrong. Please click OK and refresh the page. (0x020)');
				}

			});			

			InitDatePicker();
	   }
	}, [field]);
}
	

function SwitchIAback(eid, field) {

	$('#box_list_element_' + eid + '_' + field).fadeOut(250, function() {
        // Animation complete
		var div2 = document.getElementById('list_element_' + eid + '_' + field);
		if (div2)
		{
			div2.style.display = 'inline';
		} else {
			alert('Something went wrong. Please click OK and refresh the page. (0x021)');
		}

    });
}
function getRadioValue(name) {

for (var i=0; i < document.forms['EditEntity'].elements[name].length; i++) {
	   if (document.forms['EditEntity'].elements[name][i].checked) {
	      var val = document.forms['EditEntity'].elements[name][i].value;

      }
   }
	if (val != false)
	{
		return(val);
	} else {
		return('&');
	}


}
function getRadioValueSuperForm(name) {

for (var i=0; i < document.forms['SuperForm'].elements[name].length; i++) {
	   if (document.forms['SuperForm'].elements[name][i].checked) {
	      var val = document.forms['SuperForm'].elements[name][i].value;

      }
   }
	if (val != false)
	{
		return(val);
	} else {
		return('&');
	}


}

function PopFancyBoxDimensioned(header, url, height, width) {
	if (header === '')
	{
		// header = "<img src='images/crmlogo_transparant-150px.png'>";
	}
	//alert('width: ' + width + ',height: ' + height);
	$.fancybox({

		'autoSize'			: false,
		'autoDimensions'	: false,
		'href'				: url,
		'title'				: header,
		'height'			: height,
		'width'				: width,
        'transitionIn'		: 'fade',
		'transitionOut'		: 'fade',
		'type'				: 'iframe',
		'titlePosition'		: 'inside',
		'padding'			: '5',
		'minWidth'			: 800,
		'minHeight'			: 400,
		'fitToView'			: false,
		helpers : { 
		   overlay: {
			opacity: 0.4, // or the opacity you want 
			css: {'background-color': '#000000'} // or your preferred hex color value
		   } // overlay 
		  } // helpers

	});
	$(".fancybox-wrap").draggable(); 
}
function PopFancyBoxLarge(header, url) {
	PopFancyBoxDimensioned(header, url, '75%', '75%');
}
function PopFancyBoxSmall(header, url) {
	PopFancyBoxDimensioned(header, url, '25%', '25%');
}
function PopFancyBox(header, url) {
	PopFancyBoxDimensioned(header, url, '50%', '50%');
}

var readyruncounter = 0;
function jQueryInit() {
	$(document).ready(function() { 

		$('.checkall').click(function () {
			$('#MainContent').find(':checkbox').attr('checked', this.checked);
		});

		readyruncounter++;
		if (readyruncounter == 1)
		{
			$('.RequiredField').after(' *');
		}

		
		$('.FormattedNumberInput').each(function() {
			NumberAutoFormat(this.id);
		});
		
		SetAutoWidthOfAllElementsWithClass("selectlistfilter");

		SetDivOpen(); 
		if (document.forms['bogusform']) {
			if (document.forms['bogusform'].elements['SettingSearchQuery'])
			{
				 document.forms['bogusform'].elements['SettingSearchQuery'].focus();
			}
		}
		for (var formcounter = 0; formcounter < document.forms.length; formcounter++)
		{
			document.forms[formcounter].setAttribute('autocomplete', 'off');
		}

		$(".accordion").accordion();
		$(".tabs").tabs();


		$('.accordion .head').click(function() {
			$(this).next().toggle('slow');
			return false;
		}).next().hide();

		if (typeof UpdateAjaxFields == 'function') {
			UpdateAjaxFields();
		}

		$('.resizeable').resizable();


		$('.ColorPickerField').ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val('#' + hex);
					$(el).ColorPickerHide();
					$(el).css('backgroundColor', '#' + hex);
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				}
		});


		$("a[title]").each( function () {

			var tit = this.getAttribute('title');

			if (tit != null && tit != "")
			{
				$(this).tooltip({
					tip: '#tooltipdiv',
					predelay: 350, 
					position: "bottom right"
				}); 

			}	
		});

		


		$("td[title]").each( function () {

			var tit = this.getAttribute('title');

			if (tit != null && tit != "")
			{
				$(this).tooltip({
					tip: '#tooltipdiv',
					predelay: 100, 
					effect: "fade",
					position: "bottom right"
				}); 

			}	
		});

/*		$("#reposswitcher").tooltip({
				tip: '#reposswitchercontent',
				predelay: 50,
				delay: 500,
				position: "bottom right",
				effect: "fade"
		});
		$("#recentlist").tooltip({
				tip: '#recentlistcontent',
				predelay: 100, 
				delay: 500,
				position: "bottom right",
				effect: "fade"
		});
*/
		$("#lastlist").tooltip({
			tip: '#lastlistcontent',
			predelay: 100, 
			delay: 500,
			position: "bottom right",
			effect: "fade"
		});

		$(".imageThumbnailLink").each( function () {

			var tit = this.getAttribute('id');

			if (tit != null && tit != "")
			{
				$(this).tooltip({
					tip: '#content' + tit,
					predelay: 100, 
					position: "bottom right",
					effect: "fade"
				}); 

			}	
		});

		$('.autocomplete').each (function() {
			/* Apply AutoComplete */
			 a = $(this).autocomplete("autocomplete.php", { minChars:3,extraParams:{id:this.id}}); 
		});
		
		$('.autocompleteOLD').each (function(){

			var classList = $(this).attr('class').split(/\s+/);
			var ac = "";
			$.each( classList, function(index, item){
				/* Go through all classes. Class "ACdone" means that the object has already been processed, probably by a previous AJAX call */
				if (item==='ACdone') {
				   ac = "done";
				}

			});

			/* Don't process elements without a name */
			if (!this.name)
			{
				ac = "done";
			}

			if (ac == "done") {
				/* Skip this element */
			} else {
				/* Set options */
				options = { 
				serviceUrl:'autocomplete.php',
				minChars:1, 
				deferRequestBy: 350, 
				 params: { 
					id: this.id 
					}
				};
				/* Apply AutoComplete */
				a = $(this).autocomplete(options);

				/* Add class to mark it as processed */
				$(this).addClass("ACdone");
				$(this).removeClass("autocomplete");


			}			
		});


		if (document.getElementById("filter_list"))
		{
			document.getElementById("filter_list").focus();
		}

		if (document.getElementById("customersearch"))
		{
			document.getElementById("customersearch").focus();
		}

		if (document.getElementById("entitysearch"))
		{
			document.getElementById("entitysearch").focus();
		}
		if (document.getElementById("JS_SettingSearchQuery"))
		{
			document.getElementById("JS_SettingSearchQuery").focus();
		}

		
	});

	return(true);

}





function SetDivOpen() {
	if (document.forms['EditField']) 
	{
		document.forms['fsform1'].elements['fssearch'].focus();

		if (document.forms['EditField'].elements['newtype'].value == 'drop-down (multiselect)' || document.forms['EditField'].elements['newtype'].value == 'drop-down' || document.forms['EditField'].elements['newtype'].value == 'VAT drop-down') {
			showLayer('ddoptions');
			showLayer('numericsumcolumnoptions');
		} else {
			hideLayer('ddoptions');
			hideLayer('numericsumcolumnoptions');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'Booking calendar') {
			showLayer('planning-options');
		} else {
			hideLayer('planning-options');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'Calendar planning group') {
			showLayer('planning-group-options');
		} else {
			hideLayer('planning-group-options');
		}

		if (document.forms['EditField'].elements['newtype'].value == 'Reference to FlexTable') {
			showLayer('flextableoptions');
			showLayer('sizeoptions');
			showLayer('dd_searchboxdiv');
		} else if (document.forms['EditField'].elements['newtype'].value == 'Reference to FlexTable (multiselect)') {
			showLayer('flextableoptions');
			showLayer('sizeoptions');
			showLayer('dd_searchboxdiv');
		} else {
			hideLayer('flextableoptions');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'textbox' || document.forms['EditField'].elements['newtype'].value == 'numeric' || document.forms['EditField'].elements['newtype'].value == 'mail' || document.forms['EditField'].elements['newtype'].value == 'hyperlink' || document.forms['EditField'].elements['newtype'].value == 'invoice cost' || document.forms['EditField'].elements['newtype'].value == 'invoice cost including VAT' || document.forms['EditField'].elements['newtype'].value == 'invoice qty' || document.forms['EditField'].elements['newtype'].value == 'List of values') {

			showLayer('sizeoptions');
		} else {
			hideLayer('sizeoptions');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'comment') {
			showLayer('commentoptions');

		} else {
			hideLayer('commentoptions');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'SQL Query' || document.forms['EditField'].elements['newtype'].value == 'SQL Query (multiselect)') {
			showLayer('sqlquery');
			showLayer('numericsumcolumnoptions');
		} else {
			hideLayer('sqlquery');
			hideLayer('numericsumcolumnoptions');
		}

		if (document.forms['EditField'].elements['newtype'].value == 'Computation' || document.forms['EditField'].elements['newtype'].value == 'drop-down (populate by code)' || document.forms['EditField'].elements['newtype'].value == 'drop-down (populate by code multiselect)' || document.forms['EditField'].elements['newtype'].value == 'Computation (ajax autorefresh)') {
			showLayer('computation');
			showLayer('sizeoptions');
			showLayer('numericsumcolumnoptions');
		} else {
			hideLayer('computation');
			hideLayer('numericsumcolumnoptions');
		
		}
		if (document.forms['EditField'].elements['newtype'].value == 'drop-down (populate by code multiselect)')
		{
			showLayer('dd_searchboxdiv');
			hideLayer('numericsumcolumnoptions');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'text area') {
			showLayer('sizeoptionstextarea');

			if (document.forms['EditField'].elements['boxsize1'].value=='')
			{
				document.forms['EditField'].elements['boxsize1'].value = '100';
			}
			if (document.forms['EditField'].elements['boxsize2'].value=='')
			{
				document.forms['EditField'].elements['boxsize2'].value = '10';
			}
		} else {
			hideLayer('sizeoptionstextarea');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'text area (rich text)') {
			showLayer('sizeoptionstextarea_rt');

			if (document.forms['EditField'].elements['boxsize3'].value == '')
			{
				document.forms['EditField'].elements['boxsize3'].value = '100%';
			}
			if (document.forms['EditField'].elements['boxsize4'].value == '')
			{
				document.forms['EditField'].elements['boxsize4'].value = '400';
			}
		}
		if (document.forms['EditField'].elements['newtype'].value == 'drop-down based on customer list of values') {

			showLayer('ddoptions_LOV');
		} else {
			hideLayer('ddoptions_LOV');
		}

		if (document.forms['EditField'].elements['newtype'].options[document.forms['EditField'].elements['newtype'].selectedIndex].value.substring(0, 9) == 'User-list' || document.forms['EditField'].elements['newtype'].options[document.forms['EditField'].elements['newtype'].selectedIndex].value.substring(0, 16) == 'Users of profile' || document.forms['EditField'].elements['newtype'].value == 'SQL Query' || document.forms['EditField'].elements['newtype'].value == 'SQL Query (multiselect)' || document.forms['EditField'].elements['newtype'].value == 'drop-down' || document.forms['EditField'].elements['newtype'].value == 'drop-down (multiselect)' || document.forms['EditField'].elements['newtype'].value == 'Reference to FlexTable' || document.forms['EditField'].elements['newtype'].value == 'Reference to FlexTable (multiselect)' || document.forms['EditField'].elements['newtype'].value == 'drop-down based on customer list of values' || document.forms['EditField'].elements['newtype'].value == 'Customer contacts' || document.forms['EditField'].elements['newtype'].value == 'List of all active customers' || document.forms['EditField'].elements['newtype'].value == 'drop-down (populate by code multiselect)') {

			showLayer('dd_searchboxdiv');
		} else {
			hideLayer('dd_searchboxdiv');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'checkbox') {
			showLayer('ddoptions_CHECKBOX');
			document.forms['EditField'].elements['defaultval'].disabled = true;
			document.forms['EditField'].elements['forcing'].disabled = true;
		} else {
			hideLayer('ddoptions_CHECKBOX');
		}

		if (document.forms['EditField'].elements['newtype'].value == 'Button') {
			showLayer('dd_button');
			hideLayer('sizeoptions');
		} else {
			hideLayer('dd_button');
		}
		if (document.forms['EditField'].elements['newtype'].value == 'List of values') {
			document.forms['EditField'].elements['defaultval'].disabled = true;
			document.forms['EditField'].elements['forcing'].disabled = true;
		} else if (!document.forms['EditField'].elements['newtype'].value == 'checkbox') {

		}

		if (document.forms['EditField'].elements['newtype'].value == 'numeric' || document.forms['EditField'].elements['newtype'].value == 'Computation' || document.forms['EditField'].elements['newtype'].value == 'Computation (ajax autorefresh)') {
			showLayer('numericsumcolumnoptions');
		} else {
			hideLayer('numericsumcolumnoptions');
		}
		
	}
}

function AlertUser(msf) {

}


function refreshChat() {
	var DownWithMicrosoftInternetExplorersCacheMethod = Math.floor(Math.random()*11); /* If we don't add a random number, IE will cache the ajax call result */
	var bla = document.getElementById("INTLV_ChatBody").innerHTML;
	var textArray = bla.split("<br>");
	var len = textArray.length;

	var hash = document.getElementById("INTLV_ChatBody").innerHTML.length;
	  $.ajax({
		type: "POST",
		url: "chat.php",
		data: "check=" + hash + "&" + DownWithMicrosoftInternetExplorersCacheMethod,
		success: function(response) {
			if (response == "same")	{

			} else {
			  document.getElementById("INTLV_ChatBody").innerHTML = response;
			  document.getElementById("INTLV_ChatBody").scrollTop = document.getElementById("INTLV_ChatBody").scrollHeight;
			}
		},
		error: function(response) {

		}
	  });
}

function saveChat(x) {
	var DownWithMicrosoftInternetExplorersCacheMethod = Math.floor(Math.random()*11); /* If we don't add a random number, IE will cache the ajax call result */
	var text = x.value;
  $.ajax({
	type: "POST",
    url: "chat.php",
	async: false,
    data: "msg=" + text + "&" + DownWithMicrosoftInternetExplorersCacheMethod,
    success: function(response) {
      document.getElementById("INTLV_ChatBody").innerHTML = document.getElementById("INTLV_ChatBody").innerHTML + response;
	  document.getElementById("INTLV_ChatBody").scrollTop = document.getElementById("INTLV_ChatBody").scrollHeight;
	  document.getElementById("INTLV_ChatTextAdd").value = '';
	  document.getElementById("INTLV_ChatTextAdd").focus();
    },
    error: function(response) {
      alert(response.responseText);
    }
  });
}

function saveCoords(x, y, el, hidden, width, height) {
	var DownWithMicrosoftInternetExplorersCacheMethod = Math.floor(Math.random()*11); /* If we don't add a random number, IE will cache the ajax call result */
  $.ajax({
	type: "POST",
    url: "savecoords.php",
    data: "left=" + x + "&top=" + y + "&el=" + el + "&hidden=" + hidden + "&width=" + width+ "&height=" + height + "&" + DownWithMicrosoftInternetExplorersCacheMethod,
    success: function(response) {
      if (response != 'ok') {
        alert('Element location could not be saved.');
      } 
    },
    error: function(response) {
      alert(response.responseText);
    }
  });
}
function SetByAjax(dd1, extrafield, eidcid, type, f) {
	//alert('Update value of field ' + extrafield + ' by ajax');
	var formElements = "";
	  for (var n=0; n < f.elements.length; n++) {
		  formElements += "&" + f.elements[n].name + "=" + f.elements[n].value;
	  }
	$.ajax({
	   type: "POST",
	   url: "populate.php",
	   async: false,
	   data: "efid=" + extrafield + "&eidcid=" + eidcid + "&type=" + type + "&" + formElements,
	   success: setInnerSomething
	}, [dd1]);
}

function PopulateByAjax(dd1, extrafield, eidcid, type, f) {
	var formElements = "";
	  for (var n=0; n < f.elements.length; n++) {
		  formElements += "&" + f.elements[n].name + "=" + f.elements[n].value;
	  }
	$.ajax({
	   type: "POST",
	   url: "populate.php",
	   async: false,
	   data: "efid=" + extrafield + "&eidcid=" + eidcid + "&type=" + type + "&" + formElements,
	   success: setValues
	}, [dd1]);

}

function setValues(xmlreply)
{
  if (xmlreply)
  {
    var prezar = xmlreply.split("|||");
	var sel;
	var selset;

	prezelmt = document.getElementById('JS_EFID' + prezar[0]);
	if (prezelmt) {
		prezelmt.length = 1;
		prezelmt.length = prezar.length;
		for (o=1; o < prezar.length; o++)
		{
		  if (strstr(prezar[o] , "{selected}"))
		  {
				sel = o;
				selset = true;
				prezar[o] = prezar[o].replace("{selected}", "");
		  }
		  if (prezar[o].length > 0)
		  {
			if (prezelmt[o])
			{
			  prezelmt[o].text = prezar[o];
			}

		  } else {
					  
		  }
		}
		if (selset)
		{
			prezelmt.selectedIndex = sel;
		} else {
			prezelmt.selectedIndex = 1;
		}
	}
  }
  else
  {
    /* alert("Cannot handle the AJAX call (sV)."); */
  }
}
function setInnerSomething(xmlreply)
{
  if (xmlreply.length != 0)
  {
    var prezar = xmlreply.split("|||");
	prezelmt = document.getElementById('JS_EFID' + prezar[0]);
	if (prezelmt) {
		if (prezar[1].length != 0)
		{
			prezelmt.innerHTML = prezar[1];
			
			jQueryInit();
			InitDatePicker();
			
		} else {
			prezelmt.innerHTML = '[error]';
		}
	}	
  }
  else
  {
    /* alert("Cannot handle the AJAX call (sIT)."); */
  }
}

function TriggerOnchangeOnEnter(event,element) {
	
	if (typeof(element)=='object') {
		if (event.keyCode == 13) {
			if (document.getElementById(element) != null && document.getElementById(element).value != '')
			{
				document.getElementById(element).onchange(); 
			} 
			return(false);
		} else {
			return(false);
		}
	}

}
function strstr(haystack, needle, offset){
       var v = haystack.indexOf(needle, offset || 0);
       return (v == -1) ? false : haystack.slice(v);
}
function GetKeyCode(evt)    {
	var keyCode;
	if(evt.keyCode > 0)
	{
		keyCode = evt.keyCode;
	}
	else if(typeof(evt.charCode) != "undefined")
	{
		keyCode = evt.charCode;
	}
	return(keyCode);
   }
function testChatEnter(event, that) {
	if (event.keyCode == 13) {
		saveChat(that);
		return(true);
	}
}
function reenableTooltips()
{
    tt_Init(); //wz_tooltip required
}
function CheckNumericLocal(ref) {
	val = document.getElementById(ref).value;
	if (!is_numeric(val) && val != "")
	{
		//document.getElementById(ref).className = 'error';
		seterror(ref);
	} else {
		//document.getElementById(ref).className = 'input';
		removeerror(ref);
	}
}
function CheckEmailLocal(ref) {
	val = document.getElementById(ref).value;
	if (!isValidEmail(val) && val != "")
	{
		//document.getElementById(ref).className = 'error';
		seterror(ref);
	} else {
		//document.getElementById(ref).className = 'input';
		removeerror(ref);
	}
}
function CheckDateLocal(ref,format) {
	val = document.getElementById(ref).value;
	if (!validateDate(val, format) && val != "" )
	{
		//document.getElementById(ref).className = 'error';
		seterror(ref);
	} else {
		//document.getElementById(ref).className = 'input';
		removeerror(ref);
	}
}
function CheckDateTimeLocal(ref,format) {

	val = document.getElementById(ref).value;

	valAr = val.split(" ");

	date = valAr[0];
	time = valAr[1];
	
	if (document.getElementById(ref).value == '')
	{
		removeerror(ref);
	} else {

		if (!validateDate(date, format) && date != "" )
		{
			seterror(ref);
		} else {
			if (validateTime(time))
			{
				removeerror(ref);
			} else {
				seterror(ref);
			}
			
		}
	}
}

function validateTime(time) {
	if (time)
	{
	
		re = /^\d{1,2}:\d{2}([ap]m)?$/;
		if (time.match(re))
		{
			return(true);
		} else {
			return(false);
		}
	}
}

function urlencodejs(str) {
	return(encodeURIComponent(str));
}
function GoCustomer(i,dummy) {
	document.location="customers.php?editcust=1&custid=" + i;
}

// insertAdjacentHTML(), insertAdjacentText() and insertAdjacentElement()
// for Netscape 6/Mozilla by Thor Larholm me@jscript.dk
// Usage: include this code segment at the beginning of your document
// before any other Javascript contents.

if(typeof HTMLElement!="undefined" && !HTMLElement.prototype.insertAdjacentElement){
	HTMLElement.prototype.insertAdjacentElement = function (where,parsedNode)
	{
		switch (where){
		case 'beforeBegin':
			this.parentNode.insertBefore(parsedNode,this)
			break;
		case 'afterBegin':
			this.insertBefore(parsedNode,this.firstChild);
			break;
		case 'beforeEnd':
			this.appendChild(parsedNode);
			break;
		case 'afterEnd':
			if (this.nextSibling) this.parentNode.insertBefore(parsedNode,this.nextSibling);
			else this.parentNode.appendChild(parsedNode);
			break;
		}
	}

	HTMLElement.prototype.insertAdjacentHTML = function (where,htmlStr)
	{
		var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var parsedHTML = r.createContextualFragment(htmlStr);
		this.insertAdjacentElement(where,parsedHTML)
	}


	HTMLElement.prototype.insertAdjacentText = function (where,txtStr)
	{
		var parsedText = document.createTextNode(txtStr)
		this.insertAdjacentElement(where,parsedText)
	}

  insertHTML = function (thenode, htmlStr) {
    var r = thenode.ownerDocument.createRange();
    r.setStartBefore(thenode);
    var parsedHTML = r.createContextualFragment(htmlStr);
  //remove all children, add the new one.
    for (var i = 0; i < thenode.childNodes.length; i++) {
      thenode.removeChild(thenode.childNodes[i]);
    }
    thenode.appendChild(parsedHTML);
  }

} else {
  insertHTML = function (thenode, htmlStr) {
    thenode.insertAdjacentHTML('beforeEnd', htmlStr);
  //remove all children except for the new one.
    for (var i = 0; i < thenode.childNodes.length-1; i++) {
      thenode.removeChild(thenode.childNodes[i]);
    }
  }
}
//END prototyping stuff for stinking IE compatibility!!!
function alternateRowColors ()
{
	tables = document.getElementsByTagName ('table');
	for (x = 0; x < tables.length; x++)
	{
		if (tbody = tables[x].getElementsByTagName ('tbody')) rows = tbody[0].getElementsByTagName ('tr');
		else rows = tables[x].getElementsByTagName ('tr');

		for (i = 0; i < rows.length; i++)
		{
			if (i%2) rows[i].setAttribute ('class', rows[i].getAttribute ('class') + ' even');
			else rows[i].setAttribute ('class', rows[i].getAttribute ('class') + ' odd');
		}
	}
}
function alternateRowColorsById(tableid)
{
	tablebla = document.getElementById(tableid);
		if (tbody = tablebla.getElementsByTagName ('tbody')) rows = tbody[0].getElementsByTagName('tr');
		else rows = tablebla.getElementsByTagName ('tr');

		for (i = 0; i < rows.length; i++)
		{
			if (i%2) {
				rows[i].style.backgroundColor='#FFFFFF';
			} else {
				rows[i].style.backgroundColor='#999999';
			}
		}
	
}
function UpdateCategory(newval) {
	var el = document.getElementById('cat_span');
	if (el)
	{
		el.innerHTML=newval;
	}
	
}
function PopAddFieldValueWindow(fieldid) {
		PopFancyBox("", 'addfieldvalue.php?1&fieldid=' + fieldid + '&height=130&width=300&TB_iframe=true');
}
function PopAddRestorePointWindow() {
		PopFancyBox("", 'snapshot.php?popup=ndr&nonavbar=1&height=130&width=500&TB_iframe=true');
}
function AddOptionToSelectBox(fieldid,text,value)
{
	var selectbox = fieldid;
	if (selectbox)
	{
		var optn = document.createElement("OPTION");
		optn.text = text;
		optn.value = value;
		var aantal = selectbox.options.length;
		selectbox.options.add(optn);
		selectbox.options[aantal].selected = true;
	} else {
		alert('Failed to save value.');
	}
}
function is_numeric(str) {
	if (str)
	{
		if (str.length == 0)
		{
			return(true);
		} else {
			return /^[-+]?[0-9]+(\.[0-9]+)?$/.test(str);
		}
	}
}

function is_numeric_old(strString)
   //  check for valid numeric strings	
   {
   var strValidChars = "0123456789.-";
   var strChar;
   var blnResult = true;

   if (strString.length == 0) return true;

   //  test strString consists of valid characters listed above
   for (i = 0; i < strString.length && blnResult == true; i++)
      {
      strChar = strString.charAt(i);
      if (strValidChars.indexOf(strChar) == -1)
         {
         blnResult = false;
         }
      }
   return blnResult;
   }
function stat(message) {
  status=message;
  document.returnValue = true;
}
function CreateNewAliasName() {
	document.forms['EditField'].elements['AliasImmutable'].value = '#' + document.forms['EditField'].elements['newname'].value.toUpperCase().replace(/ /g, '_') + '#';
}

function CheckMailTrigger() {
	y = document.forms['TriggerAddForm'].elements['action'].options[document.forms['TriggerAddForm'].elements['action'].selectedIndex].value.substring(0, 4);
	if (document.forms['TriggerAddForm'].elements['action'][document.forms['TriggerAddForm'].elements['action'].selectedIndex].value == 'set startdate' || document.forms['TriggerAddForm'].elements['action'].options[document.forms['TriggerAddForm'].elements['action'].selectedIndex].value == 'set closedate')
	{
		y = '';
	}
	if (y == 'mail' || y == 'Mail')
	{
		if (document.forms['TriggerAddForm'].elements['template_fileid'])
		{
			document.forms['TriggerAddForm'].elements['template_fileid'].disabled = false;
		}
		if (document.forms['TriggerAddForm'].elements['attach'])
		{
			document.forms['TriggerAddForm'].elements['attach'].disabled = false;
		}
		if (document.forms['TriggerAddForm'].elements['report_fileid'])
		{
			document.forms['TriggerAddForm'].elements['report_fileid'].disabled = false;
		}	
		if (document.forms['TriggerAddForm'].elements['mailmethod'])
		{
			document.forms['TriggerAddForm'].elements['mailmethod'].disabled = false;
		}
		
	} else {
				if (document.forms['TriggerAddForm'].elements['template_fileid'])
		{
			document.forms['TriggerAddForm'].elements['template_fileid'].disabled = true;
		}
		if (document.forms['TriggerAddForm'].elements['attach'])
		{
			document.forms['TriggerAddForm'].elements['attach'].disabled = true;
		}
		if (document.forms['TriggerAddForm'].elements['report_fileid'])
		{
			document.forms['TriggerAddForm'].elements['report_fileid'].disabled = true;
		}	
		if (document.forms['TriggerAddForm'].elements['mailmethod'])
		{
			document.forms['TriggerAddForm'].elements['mailmethod'].disabled = true;
		}
	}
	
	if (y == 'set ') {
		showLayer('EF_Update');
	} else {
		hideLayer('EF_Update');
	}

	var sel = document.forms["TriggerAddForm"].elements["action"].options[document.forms["TriggerAddForm"].elements["action"].selectedIndex].value;
	if (strstr(sel, "mail") || strstr(sel, "Mail"))
	{
		showLayer("mailoptionsdiv");
	} else {
		hideLayer("mailoptionsdiv");
	}

}

function CopyToClipboard(text) {
	document.forms['clipboardform'].elements['clipboardvalue'].value = text;
	temp1 = document.forms['clipboardform'].elements['clipboardvalue'].createTextRange();
	temp1.execCommand( 'copy' );
}
function ExecuteButton(buttonid) {
	
	document.getElementById("JS_e_button").value = buttonid;
	var name = document.getElementById('JS_EFID' + buttonid).form.name;
	if (name == "")
	{
		name = "EditEntity";
	}
	CheckForm(name);
}
function popupdater(i)	{	
	newWindow = window.open('edit.php?NoMenu=1&e=' + i, 'Update' + i,'width=680,height=630,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
}	
	
function PopRightsChooser(i) {
	//newWindow = window.open('choose_rights.php?1&field=' + i, 'Update' + i,'width=600,height=400,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
	PopFancyBox("", 'choose_rights.php?1&field=' + i + '&height=400&width=700&TB_iframe=true');
}
function PopMenuRightsChooser(i)	{	
	//newWindow = window.open('choose_rights.php?1&field=' + i, 'Update' + i,'width=600,height=400,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
	PopFancyBox("", 'choose_menuitemrights.php?1&item=' + i + '&height=400&width=700&TB_iframe=true');
}	
function PopEditEntityWindow(eid)	{	
	PopFancyBoxLarge("", 'edit.php?nonavbar=1&e=' + eid + '&height=500&width=880&TB_iframe=true');
}
function PopEditCustomerWindow(cid, dummy)	{	
	PopFancyBoxLarge("", 'customers.php?nonavbar=1&editcust=1&close_on_next_load=true&ParentAjaxHandler=MainCustomerList&custid=' + cid);
}

function PopAddCustomerWindow(urlargs)	{	
	PopFancyBox("", 'customers.php?nonavbar=1&close_on_next_load=true&&add=1&' + urlargs + '&height=500&width=880&TB_iframe=true');
}
function PopRightsChooserFlexTable(i)	{	
	PopFancyBox("", 'choose_flextablerights.php?1&field=' + i + '&height=400&width=700&TB_iframe=true');
	}
function PopRightsChooserModules(i)	{	
	PopFancyBox("", 'choose_modulerights.php?1&field=' + i + '&height=400&width=700&TB_iframe=true');
}
function PopRightsChooserInteractiveFields(i, type)	{	
	PopFancyBox("", 'choose_interactivefieldrights.php?1&account=' + i + '&type=' + type + '&height=400&width=700&TB_iframe=true');
}
function PopEditAttributesWindow(item, record, opendirect)	{	
	PopFancyBoxLarge("", 'attribute.php?nonavbar=1&EditAttributes=' + record + '&ParentReference=' + item + '&OpenDirect=' + opendirect + '&height=400&width=700&TB_iframe=true');
}
function PopEFDDColorChooser(efid, tabletype) {
	PopFancyBox("", 'efcolors.php?1&efid=' + efid + '&tabletype=' + tabletype + '&height=400&width=700&TB_iframe=true');
}
function PopTriggerConditionsChooser(i)	{	
	PopFancyBox("", 'triggerconditions.php?1&triggerid=' + i + '&height=400&width=750&TB_iframe=true');
}
function PopExtrafieldRequiredConditionsChooser(i)	{	
	PopFancyBox("", 'extrafieldrequiredconditions.php?1&efid=' + i + '&height=400&width=750&TB_iframe=true');
}
function PopExtrafieldConditionsChooser(i)	{	
	PopFancyBox("", 'extrafieldconditions.php?1&efid=' + i + '&height=400&width=750&TB_iframe=true');
}
function popExportWindow(q)	{	
	ExportWindow = window.open('customers.php?nonavbar=1&export=1&stashid=' + q, 'Export','width=370,height=150,directories=0,status=1,menuBar=0,scrollBars=1,resizable=1');
	}		
function popcustomereditscreen(i)	{	
	newWindow = window.open('customers.php?keeplocked=1&editcust=1&nonavbar=1&custid=' + i, 'Update' + i,'width=640,height=630,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
	}		
function hideLayer(whichLayer) {
	$('#' + whichLayer).hide(300);
}
function showLayer(whichLayer) {
	$('#' + whichLayer).show(300);
}

function toggleLayer(whichLayer) {
	var classList = document.getElementById(whichLayer).className.split(/\s+/);
	var ac = "";
	$.each( classList, function(index, item){
		/* Go through all classes. Class "hidden" means that the object is hidden */
		if (item==='hidden') {
		   ac = "hidden";
		}

	});
	if (document.getElementById(whichLayer).style.display == "none" || document.getElementById(whichLayer).style.visibility == "hidden" || (ac === "hidden" && document.getElementById(whichLayer).style.display != "block"))
	{
		$('#' + whichLayer).show(300);
	} else {
	
		$('#' + whichLayer).hide(300);
	}
}

function isValidEmail (emailStr) {
	/* The following pattern is used to check if the entered e-mail address
	   fits the user@domain format.  It also is used to separate the username
	   from the domain. */
	var emailPat=/^(.+)@(.+)$/
	/* The following string represents the pattern for matching all special
	   characters.  We don't want to allow special characters in the address. 
	   These characters include ( ) < > @ , ; : \ " . [ ]    */
	var specialChars="\\(\\)<>@,;:\\\\\\\"\\.\\[\\]"
	/* The following string represents the range of characters allowed in a 
	   username or domainname.  It really states which chars aren't allowed. */
	var validChars="\[^\\s" + specialChars + "\]"
	/* The following pattern applies if the "user" is a quoted string (in
	   which case, there are no rules about which characters are allowed
	   and which aren't; anything goes).  E.g. "jiminy cricket"@disney.com
	   is a legal e-mail address. */
	var quotedUser="(\"[^\"]*\")"
	/* The following pattern applies for domains that are IP addresses,
	   rather than symbolic names.  E.g. joe@[123.124.233.4] is a legal
	   e-mail address. NOTE: The square brackets are required. */
	var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/
	/* The following string represents an atom (basically a series of
	   non-special characters.) */
	var atom=validChars + '+'
	/* The following string represents one word in the typical username.
	   For example, in john.doe@somewhere.com, john and doe are words.
	   Basically, a word is either an atom or quoted string. */
	var word="(" + atom + "|" + quotedUser + ")"
	// The following pattern describes the structure of the user
	var userPat=new RegExp("^" + word + "(\\." + word + ")*$")
	/* The following pattern describes the structure of a normal symbolic
	   domain, as opposed to ipDomainPat, shown above. */
	var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$")


	/* Finally, let's start trying to figure out if the supplied address is
	   valid. */
	if (emailStr == '')
	{
		return true;
	}

	/* Begin with the coarse pattern to simply break up user@domain into
	   different pieces that are easy to analyze. */
	var matchArray=emailStr.match(emailPat)
	if (matchArray==null) {
	  /* Too many/few @'s or something; basically, this address doesn't
		 even fit the general mould of a valid e-mail address. */
		//alert("Email address seems incorrect (check @ and .'s)")
		return false
	}
	var user=matchArray[1]
	var domain=matchArray[2]

	// See if "user" is valid 
	if (user.match(userPat)==null) {
		// user is not valid
		//alert("The username doesn't seem to be valid.")
		return false
	}

	/* if the e-mail address is at an IP address (as opposed to a symbolic
	   host name) make sure the IP address is valid. */
	var IPArray=domain.match(ipDomainPat)
	if (IPArray!=null) {
		// this is an IP address
		  for (var i=1;i<=4;i++) {
			if (IPArray[i]>255) {
				//alert("Destination IP address is invalid!")
			return false
			}
		}
		return true
	}

	// Domain is symbolic name
	var domainArray=domain.match(domainPat)
	if (domainArray==null) {
		//alert("The domain name doesn't seem to be valid.")
		return false
	}

	/* domain name seems valid, but now make sure that it ends in a
	   three-letter word (like com, edu, gov) or a two-letter word,
	   representing country (uk, nl), and that there's a hostname preceding 
	   the domain or country. */

	/* Now we need to break up the domain to get a count of how many atoms
	   it consists of. */
	var atomPat=new RegExp(atom,"g")
	var domArr=domain.match(atomPat)
	var len=domArr.length
	if (domArr[domArr.length-1].length<2 || 
		domArr[domArr.length-1].length>4) {
	   // the address must end in a two letter or three letter word.
	   //alert("The address must end in a three-letter domain, or two letter country.")
	   return false
	}

	// Make sure there's a host name preceding the domain.
	if (len<2) {
	   var errStr="This address is missing a hostname!"
	   //alert(errStr)
	   return false
	}

	// If we've gotten this far, everything's valid!
	return true;
}
function isValidEmail2(str) {
   return (str.indexOf(".") > 3) && (str.indexOf("@") > 1);
}

function leaveUnlock() {
		if (document.forms['EditEntity'] &&  document.getElementById('JS_unlock') && document.getElementById('JS_unlock').value == '1') {
			PopUnlockWindow(1);
		}
}
function stripHTML(str)	{ 
	return str.replace(/<[^>]*>/g, "");
}
function PopUnlockWindow(e) {
	if (event.clientY < 0) {
			newWindow = window.open('index.php?unlock=' + e, 'UnLockWindow','width=1,height=1,directories=0,status=1,menuBar=0,scrollBars=1,resizable=1');
	}
}
function PopCalendarSelectDay(fieldnum, recordnum)	{
	PopFancyBoxLarge("", 'index.php?ShowCalendar&nonavbar=1&calObjId=selectOnly&selectField=' + fieldnum + '&recordnum=' + recordnum);
}

function popcalendar()	{
				document.forms['EditEntity'].elements['duedate'].blur();
				PopFancyBox("", 'calendar.php?select=1');
		}
function popcalendarSelect(field,dummy)	{
				PopFancyBox("", 'calendar.php?NoClickToWeek=1&select=1&this=' + field + '');
}
function popPlanning(field,tabletype,id)	{
				PopFancyBox("", 'planning.php?nonavbar=1&this=' + field + '&table=' + tabletype + '&id=' + id + '');
}

function popcalendarforalarmdate()	{
				document.forms['EditEntity'].elements['duedate'].blur();
				newWindow = window.open('calendar.php?Alarm=1&NoClickToWeek=1', 'myWindow2','width=570,height=400,directories=0,status=1,menuBar=0,scrollBars=1,resizable=1');
				newWindow.focus();
		}		
function popEmailToCustomerScreen(cid)	{
				newWindow = window.open('edit.php?SendEmailToCustomer=1&nonavbar=1&EntityID=Customer&CustID=' + cid, 'SendMailWindow' + cid,'width=570,height=485,directories=0,status=1,menuBar=0,scrollBars=1,resizable=0');
				newWindow.focus();
		}

function popEmailToCustomerScreenCust(cust)	{
				newWindow = window.open('edit.php?SendEmailToCustomer=1&nonavbar=1&CustID=' + cust, 'SendMailWindow' + cust,'width=570,height=485,directories=0,status=1,menuBar=0,scrollBars=1,resizable=0');
				newWindow.focus();
		}
function popEmailToEFScreen(eid,email) {
				PopFancyBox("", 'edit.php?SendEmailToOtherUsers=1&nonavbar=1&SendToEmailAddr=' + email + '&EntityID=' + eid + '');		
		}
function popEmailToCustomerScreen(cid,email) {
				PopFancyBox("", 'edit.php?SendEmailToOtherUsers=1&nonavbar=1&SendToEmailAddr=' + email + '&CustID=' + cid + '');		
		}

function popEmailNotifyScreen(eid) {
//				newWindow = window.open('edit.php?SendEmailToOtherUsers=1&nonavbar=1&EntityID=' + eid, 'NotifyWindow' + eid,'width=870,height=585,directories=0,status=1,menuBar=0,scrollBars=1,resizable=0');
//				newWindow.focus();
				PopFancyBox("", 'edit.php?SendEmailToOtherUsers=1&nonavbar=1&EntityID=' + eid + '');
		}
function popcolorchooser(statvar, element)	{

				//newWindow = window.open('choose_col.php?var=' + statvar, 'myWindow2','width=680,height=300,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
				//newWindow.focus();
				PopFancyBox("", "choose_col.php?var=" + statvar + "&elementname=" + element + "");
					
		}
function popcolumnchooser(AjaxHandler, DownloadSpreadSheetStashId, ListName, BaseList)	{
		if (is_numeric(DownloadSpreadSheetStashId))
		{	
			if (!BaseList)
			{
				var BaseList = '';
			}
			var DlSs = '&DownloadSpreadSheetStashId=' + DownloadSpreadSheetStashId + '&BaseList=' + BaseList;
		} else {
			var DlSs = '';
		}
		if (!ListName)
		{
			var ListName='';
		}
		PopFancyBox("", "choose_cols.php?dothis=personal&CustomColumnOverrule=" + ListName + "&ParentAjaxHandler=" + AjaxHandler + DlSs);
}
function popcustomtabcolumnchooser(TabId)	{
		PopFancyBox("", "choose_cols.php?dothis=CustomTab&TabId=Tab" + TabId + "");
			
}
function popflextablecolumnchooser(AjaxHandler,flextable,Tab)	{
		// PopFancyBox("", "choose_cols.php?flextable=" + flextable + "&ParentAjaxHandler=" + AjaxHandler + "");
		// Thanks Theodore Cowan
		PopFancyBox("", "choose_cols.php?flextable=" + flextable + "&ParentAjaxHandler=" + AjaxHandler + "&CustomColumnOverrule=" + Tab);
}

function popprofilechooser(i, type)	{
		PopFancyBox("", "choose_cols.php?dothis=profile&profile=" + i + "&type=" + type + "&nonavbar=true");
			
}
function popcustomerchooser()	{
		PopFancyBox("", "useradmin.php?ChooseCustomer=true&nonavbar=1&height=400&width=250&TB_iframe=true");
}


function popActivityGraph(i)	{
	//			newWindow = window.open('edit.php?ActivityGraph=' + i , 'MyGraphWindow' + i,'width=340,height=220,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
				PopFancyBox("", 'edit.php?ActivityGraph=' + i + '&height=300&width=700&TB_iframe=true');
		}
function popActivityCustomerGraph(i)	{
				newWindow = window.open('customers.php?ActivityCustomerGraph=' + i , 'MyGraphWindow' + i,'width=340,height=220,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
		}
function popUserActivityGraph(i)	{
				newWindow = window.open('useradmin.php?ActivityUserGraph=' + i , 'MyGraphWindow' + i,'width=620,height=310,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
		}
function pophelp(i)	{
				newWindow = window.open('help.php?id=' + i, 'HelpWindow' + i,'width=350,height=300,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
		}
function popjournal(eid) {

				PopFancyBox("", 'edit.php?journal=1&eid=' + eid + '&height=300&width=850&TB_iframe=true');
		}
function popcustomerjournal(custid) {
				PopFancyBox("", 'edit.php?journal=1&custid=' + custid);
}
function popflextablejournal(recid, flextable) {
			PopFancyBox("", 'edit.php?journal=1&FlexTable=' + flextable + '&recordid=' + recid + '&height=300&width=850&TB_iframe=true');
}
function popuserjournal(recid) {
			PopFancyBox("", 'edit.php?journal=1&type=user&recordid=' + recid + '&height=300&width=850&TB_iframe=true');
}
function popgroupjournal(recid) {
			PopFancyBox("", 'edit.php?journal=1&type=group&recordid=' + recid + '&height=300&width=850&TB_iframe=true');
}
function poptriggerjournal(tid) {
			PopFancyBox("", 'edit.php?journal=1&recordid=' + tid + '&trigger=true&height=300&width=850&TB_iframe=true');
}
function popPDFprintwindow(i) {
			PopFancyBoxSmall("", 'frame.php?target=' + i);
}
function popPDFwindow(i) {
			pdfwin = window.open(i, 'pdfwin','width=640,height=480,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
//				PopFancyBox("", i + '&height=480&width=640&TB_iframe=true');
}
function poplittlewindowstats(i) {
//				pdfwin = window.open(i, 'littlewin','width=500,height=150,directories=0,status=0,menuBar=0,scrollBars=0,resizable=1');
				PopFancyBoxSmall("", i);
}
function popContactForm(i) {
				PopFancyBox("", i);
}

function poplittlewindow(i) {
//				pdfwin = window.open(i, 'littlewin','width=500,height=150,directories=0,status=0,menuBar=0,scrollBars=0,resizable=1');
				PopFancyBoxSmall("", i);
}
function poplargewindow(i) {
				PopFancyBoxLarge("", i);
}
function popdiarywindow(i) {
				PopFancyBox("", i);
}
function popAAEwindow(i) {
				pdfwin = window.open(i, 'littlewin','width=700,height=200,directories=0,status=0,menuBar=0,scrollBars=0,resizable=1');
}
function poplittlewindowWithBars(i) {
				PopFancyBox("", i);
}
function popslightlybiggerwindowWithBars(i) {
				PopFancyBox("", i);
}
function popWidewindowWithBars(i) {
				PopFancyBox("", i);
}
function popflextablewindow(flextable, field) {
				PopFancyBoxLarge("", "flextable.php?ShowTable=" + flextable + "&SelectField=" + field + "&nonavbar=1");
}
function popflextableAddwindow(flextable, field, AjaxHandler) {
				PopFancyBoxLarge("", "flextable.php?AddToTable=" + flextable + "&refer=" + field + "&nonavbar=1&AddInPopup=1&ParentAjaxHandler=" + AjaxHandler);
}
function popflextableEditwindow(flextable, field, EditRecord, AjaxHandler) {
				PopFancyBoxLarge("", "flextable.php?FlexTable=" + flextable + "&refer=" + field + "&nonavbar=1&AddInPopup=1&EditRecord=" + EditRecord + "&ParentAjaxHandler=" + AjaxHandler);
}

function popflextablewindowPlainField(flextable, field) {
				PopFancyBox("", "flextable.php?ShowTable=" + flextable + "&PlainField=true&SelectField=EFID" + field + "&nonavbar=1");

}
function popFlextableInlineSelectTable(flextable, idfield, showfield) {
				PopFancyBox("", "flextable.php?ShowTable=" + flextable + "&ShowInlineSelectTable=1&PlainField=true&Table=" + flextable + "&SelectField=" + idfield + "&ShowField=" + showfield + "&nonavbar=1");
}
function popCustomerInlineSelectTable(idfield, showfield) {
				PopFancyBox("", "index.php?ShowCustomerList&ShowInlineSelectTable=1&SelectField=" + idfield + "&ShowField=" + showfield + "&nonavbar=1&Table=Cust");
}
function popCustomerInlineAddScreen(idfield, showfield) {
				PopFancyBox("", "customers.php?add=1&ShowInlineAddScreen=1&SelectField=" + idfield + "&ShowField=" + showfield + "&nonavbar=1&Table=Entity");
}

function escapeHTML (str)
{
   var div = document.createElement('div');
   var text = document.createTextNode(str);
   div.appendChild(text);
   return div.innerHTML;
}

function PutCustomerInEntityForm(selectfield,showfield,id,name) {
	parent.document.getElementById(showfield).innerHTML = escapeHTML(name);
	parent.document.getElementById(selectfield).value = id;
	if (typeof parent.AutoSaveField == 'function') {
		parent.AutoSaveField(parent.document.getElementById(selectfield));
	}
	if (typeof parent.document.getElementById(selectfield).onchange == 'function')
	{
		parent.document.getElementById(selectfield).onchange();
	}
	
	parent.$.fancybox.close();
}
function PutReferInFlextableForm(selectfield,showfield,id,name) {
	parent.document.getElementById(showfield).innerHTML = escapeHTML(name);
	parent.document.getElementById(selectfield).value = id;
	if (typeof parent.AutoSaveField == 'function') {
		parent.AutoSaveField(parent.document.getElementById(selectfield));
	} 	
		if (typeof parent.document.getElementById(selectfield).onchange == 'function')
	{
		parent.document.getElementById(selectfield).onchange();
	}
	parent.$.fancybox.close();
}
function PutCustomerInFlextableForm(selectfield,showfield,id,name) {

	parent.document.getElementById(showfield).innerHTML = escapeHTML(name);
	parent.document.getElementById(selectfield).value = id;
	parent.AutoSaveField(parent.document.getElementById(selectfield));
	if (typeof parent.document.getElementById(selectfield).onchange == 'function')
	{
		parent.document.getElementById(selectfield).onchange();
	}
	parent.$.fancybox.close();
}
function SetFieldValFlexTable(field1,val1,field2,val2) {
	f1 = field1;
	f2 = field2;
	f1.value = val1;
	f2.innerHTML = val2;
}
function SelectField(field,value,tag) {
	sel = field;
	str = value;
	var done;

	for (i=0; i<sel.options.length; i++) {
		if (sel.options[i].value == str) {
			sel.selectedIndex = i;
			done=1;
		}
	}

	if (!done)
	{
		AddOptionToSelectBox(field,tag,value);
		sel.selectedIndex = i;
	}
}

function poplittleLogwindowWithBars(i) {
	pdfwin = window.open(i, 'logwin','width=500,height=400,directories=0,status=0,menuBar=0,scrollBars=1,resizable=1');
}

function increaseNotesHeight(thisTextarea, add) {
	if (thisTextarea) {
		newHeight = parseInt(thisTextarea.style.height) + add;
		thisTextarea.style.height = newHeight + "px";
	}
}

function decreaseNotesHeight(thisTextarea, subtract) {
	if (thisTextarea) {
		if ((parseInt(thisTextarea.style.height) - subtract) > 20) {
			newHeight = parseInt(thisTextarea.style.height) - subtract;
			thisTextarea.style.height = newHeight + "px";
		}
		else {
			newHeight = 30;
			thisTextarea.style.height = "30px";
		}			
	}
}
 
function autoCompleteJero(field, forcematch)
{
	var found = false;
    alert(allecats);
	alert('ja');

	for (var i = 0; i < allecats.length; i++)
	{
		if (allecats[i].toUpperCase().indexOf(field.value.toUpperCase()) == 0)
		{
			found = true;
			break;
		}
	}

	if (field.createTextRange)
	{
		if (forcematch && !found)
		{
			field.value = field.value.substring(0,field.value.length-1); 
			return;
		}
		var cursorKeys = "8;46;37;38;39;40;33;34;35;36;45;";
		if (cursorKeys.indexOf(event.keyCode+";") == -1)
		{
			var r1 = field.createTextRange();
			var oldValue = r1.text;
			var newValue = found ? allecats[i] : oldValue;
			if (newValue != field.value)
			{
				field.value = newValue;
				var rNew = field.createTextRange();
				rNew.moveStart('character', oldValue.length) ;
				rNew.select();
			}
		}
	}
}
function autoComplete (field, select, property, forcematch) {
	var found = false;
    var select;
    if (!select)
    {
	    return true; 
    } else {

		for (var i = 0; i < select.options.length; i++) {
		if (select.options[i][property].toUpperCase().indexOf(field.value.toUpperCase()) == 0) {
			found=true; break;
			}
		}
		if (found) { select.selectedIndex = i; }
		else { select.selectedIndex = -1; }
		if (field.createTextRange) {
			if (forcematch && !found) {
				field.value=field.value.substring(0,field.value.length-1); 
				return;
				}
			var cursorKeys ="8;46;37;38;39;40;33;34;35;36;45;";
			if (cursorKeys.indexOf(event.keyCode+";") == -1) {
				var r1 = field.createTextRange();
				var oldValue = r1.text;
				var newValue = found ? select.options[i][property] : oldValue;
				if (newValue != field.value) {
					field.value = newValue;
					var rNew = field.createTextRange();
					rNew.moveStart('character', oldValue.length) ;
					rNew.select();
					}
				}
			}
	}
}
function setChecked ( chkBoxObj, state )
{
	  if ( state == 'y' )
		 chkBoxObj.checked=1;
	  else
		 chkBoxObj.checked=0;
}

function setCheckboxes(form)
{

	if (document.forms['SuperForm'].elements['check_status'].value == 'on')
	{
		for (var i = 0; i < document.forms['SuperForm'].elements.length; i++){    
			eval("document.forms['SuperForm'][" + i + "].checked = false");  
		} 
		document.forms['SuperForm'].elements['check_status'].value = 'off';
	} else {
		for (var i = 0; i < document.forms['SuperForm'].elements.length; i++){    
			eval("document.forms['SuperForm'][" + i + "].checked = true");  
		} 
		document.forms['SuperForm'].elements['check_status'].value = 'on';
	}
} // end of the 'setCheckboxes()' function
function HL(id)
{
	id.style.background = '#e9e9e9';
}
function UL(id)
{
	id.style.background = '#ffffff';

}

function stateChanged(req, id)  { 
	if (req.readyState==4 || req.readyState=="complete") { 
		//alert(req.responseText);
		 if (document.getElementById(id)) {
			 document.getElementById(id).innerHTML=req.responseText;
		 } else {
			//alert('Wrong id or id nonexistent: ' + id);
		 }
	
		 document.getElementById("WaitImageDiv").style.visibility="hidden";
	//	 SubtractOneAndCheckIfDivCanBeClosed();
	} else {

	}
	
}

function AddOne() {
	t = parseInt(document.forms['DivUpdateForm'].elements['NumberOfAjaxClients'].value);
	alert('t is nu ' + t);
	t = t + 1;
	document.forms['DivUpdateForm'].elements['NumberOfAjaxClients'].value = t;
	document.getElementById("WaitImageDiv").style.visibility="visible";}

function SubtractOneAndCheckIfDivCanBeClosed() {
	t = parseInt(document.forms['DivUpdateForm'].elements['NumberOfAjaxClients'].value);
	t = t - 1;
	if (t == 0)
	{
		document.getElementById("WaitImageDiv").style.visibility="hidden";
	} else {
		document.forms['DivUpdateForm'].elements['NumberOfAjaxClients'].value = t;

	}
	
}


function GetXmlHttpObject()
{
var xmlHttp=null;
try
 {
 // Firefox, Opera 8.0+, Safari
 xmlHttp=new XMLHttpRequest();
 }
catch (e)
 {
 //Internet Explorer
 try
  {
  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
  }
 catch (e)
  {
  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
 }
return xmlHttp;
}

var TimerCount;

function SetWidthDelayed(width, element) {
//alert('delay');
	//el=element;
//	wi=width;
//	TimerCount=setTimeout("SetWidth(wi, el)",0, false);	
	element.style.width = width;
}
function StopCount() {
	clearTimeout(TimerCount);
}
function SetAutoWidthOfAllElementsWithClass(klas) {
	$('.' + klas).each(function() {
		SetWidth('auto', this, false);
  });
}
function SetWidth(width, el, divtohide)
{
	if (width == 'auto')
	{
		//alert('autowidth');
		width = '';
	}
	if (width == '')
	{
		width = (el.parentNode.parentNode.offsetWidth - 22) + 'px';
	}
	if (el && el.parentNode.parentNode.offsetWidth!=0 && el.parentNode.parentNode.offsetWidth!='') {
	{

		el.style.width = width;
		
		if (divtohide) {
			var klasse = document.getElementById(divtohide).className;
			if (klasse == "box_interactive_list_item show_content") {
				// Do nothing; this element has class show_content which means it must NOT be hidden because the filter is active
			} else {
				SwitchIAtableheaderBack(divtohide);
			}
		}
	}
	}
}

/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/

var Base64 = {

	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},

	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},

	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}

var JSearch_selects = new Array();
var JSearch_select_contents = new Array();
var JSearch_lastsearch = new Array();

function JSearch(selectfield, searchstr)
{
	var x = 0;
	var selectid = -1;

	var number_of_selects = JSearch_selects.length;

	for (x = 0; x < number_of_selects; x++)
	{
		if (JSearch_selects[x] == selectfield)
		{
			selectid = x;
		}
	}

	if ((searchstr != '') || (selectid > -1))
	{
		var selectfield_obj = document.getElementById(selectfield);

		if (selectid == -1)
		{
			JSearch_selects[number_of_selects] = selectfield;
			selectid = number_of_selects;

			JSearch_select_contents[selectid] = new Array();

			for (x = 0; x < selectfield_obj.options.length; x++)
			{
				JSearch_select_contents[selectid][x] = new Array();
				JSearch_select_contents[selectid][x][0] = selectfield_obj.options[x].value;
				JSearch_select_contents[selectid][x][1] = selectfield_obj.options[x].text;
			}
		}
	
		if (searchstr != JSearch_lastsearch[selectid])
		{
			selectfield_obj.options.length = 0;

			var needles = searchstr.toLowerCase().split(' ');

			var number_of_options = JSearch_select_contents[selectid].length;
			var recordsfound = 0;
			for (x = 0; x < number_of_options; x++)
			{
				var value = JSearch_select_contents[selectid][x][0];
				var text = JSearch_select_contents[selectid][x][1];
				var haystack = text.toLowerCase();

				var match = true;

				for (var y = 0; y < needles.length; y++)
				{
					if (haystack.indexOf(needles[y]) == -1)
					{
						match = false;
						break;
					}
				}

				if (match)
				{
					selectfield_obj.options[recordsfound++] = new Option(text, value);
				}
			}
			JSearch_lastsearch[selectid] = searchstr;
		}
	}
}

function Jsearchtable(tableid, searchstr)
{
	var tableobj = document.getElementById(tableid).tBodies[0];

	var trs = tableobj.getElementsByTagName('TR');

	var needles = searchstr.toLowerCase().split(' ');

	for (var i = 0; i < trs.length; i++)
	{
		var ToDisplay = ''; //browser default, table-row on modern browsers
		var haystack = trs[i].innerHTML.replace(/<.*?>/gm, ' ').toLowerCase();
		
		for (var x = 0; x < needles.length; x++)
		{
			if (haystack.indexOf(needles[x]) == -1)
			{
				ToDisplay = 'none';
				break;
			}
		}
		trs[i].style.display = ToDisplay;
	}
}

function nothing(dummy1, dummy2) {

}
function UnCheckCell(tdval, tdid, divid, ef, id, table, myname, HighlightBGColor) {
	var func = 'refresh_' + divid;
	document.getElementById(tdval).className = "calselect";
	document.getElementById(tdval).onclick = function() {CheckCell(this.id, this, divid, ef, id, table, myname, HighlightBGColor);}
	document.getElementById(tdval).onmouseover = function() {nothing(this.id, this);}
	document.getElementById(tdval).innerHTML = "";
	eval(func + "('&ef=" + ef + "&id=" + id + "&table=" + table + "&DeleteAppointment=" + tdval + "')");
}
function CheckCell(tdval,tdid, divid, ef, id, table, myname, HighlightBGColor) {
	var func = 'refresh_' + divid;
	document.getElementById(tdval).className = "calselected";
	document.getElementById(tdval).onclick = function() {UnCheckCell(this.id, this, divid, ef, id, table, myname, HighlightBGColor);}
	document.getElementById(tdval).onmouseout = function() {nothing(this.id, this);}
	document.getElementById(tdval).backgroundColor = "#FFFFFF";
	document.getElementById(tdval).innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + myname;
	eval(func + "('&ef=" + ef + "&id=" + id + "&table=" + table + "&NewAppointment=" + tdval + "')");
}
function InlineCheckPasswordStrength(field) {

	var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
	var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
	var enoughRegex = new RegExp("(?=.{6,}).*", "g");
	var pwd = document.getElementById(field);
	if (pwd.value.length==0) {
		pwd.style.background='#ffffff';
	} else if (false == enoughRegex.test(pwd.value)) {
		pwd.style.background='#FFB9B9';
	} else if (strongRegex.test(pwd.value)) {
		pwd.style.background='#CFFECD';
	} else if (mediumRegex.test(pwd.value)) {
		pwd.style.background='#FFCC00';
	} else {
		pwd.style.background='#FFB9B9';
	}
}

function checkPasswordStrength(field) {

	var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
	var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
	var enoughRegex = new RegExp("(?=.{6,}).*", "g");
	var pwd = document.getElementById(field);
	if (pwd.value.length==0) {
		return(4);
	} else if (false == enoughRegex.test(pwd.value)) {
		return(1);
	} else if (strongRegex.test(pwd.value)) {
		return(3);
	} else if (mediumRegex.test(pwd.value)) {
		return(2);
	} else {
		return(1);
	}
}
function encode64(input) {
	 var keyStr = "ABCDEFGHIJKLMNOP" +
                "QRSTUVWXYZabcdef" +
                "ghijklmnopqrstuv" +
                "wxyz0123456789+/" +
                "=";
    input = escape(input);
    var output = "";
    var chr1, chr2, chr3 = "";
    var enc1, enc2, enc3, enc4 = "";
    var i = 0;

    do {
       chr1 = input.charCodeAt(i++);
       chr2 = input.charCodeAt(i++);
       chr3 = input.charCodeAt(i++);

       enc1 = chr1 >> 2;
       enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
       enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
       enc4 = chr3 & 63;

       if (isNaN(chr2)) {
          enc3 = enc4 = 64;
       } else if (isNaN(chr3)) {
          enc4 = 64;
       }

       output = output +
          keyStr.charAt(enc1) +
          keyStr.charAt(enc2) +
          keyStr.charAt(enc3) +
          keyStr.charAt(enc4);
       chr1 = chr2 = chr3 = "";
       enc1 = enc2 = enc3 = enc4 = "";
    } while (i < input.length);

    return output;
}

var MD5 = function (string) {
 
	function RotateLeft(lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}
 
	function AddUnsigned(lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			} else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		} else {
			return (lResult ^ lX8 ^ lY8);
		}
 	}
 
 	function F(x,y,z) { return (x & y) | ((~x) & z); }
 	function G(x,y,z) { return (x & z) | (y & (~z)); }
 	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }
 
	function FF(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function GG(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function HH(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function II(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};
 
	function ConvertToWordArray(string) {
		var lWordCount;
		var lMessageLength = string.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	};
 
	function WordToHex(lValue) {
		var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			WordToHexValue_temp = "0" + lByte.toString(16);
			WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
		}
		return WordToHexValue;
	};
 
	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	};
 
	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;
 
	string = Utf8Encode(string);
 
	x = ConvertToWordArray(string);
 
	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
 
	for (k=0;k<x.length;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=AddUnsigned(a,AA);
		b=AddUnsigned(b,BB);
		c=AddUnsigned(c,CC);
		d=AddUnsigned(d,DD);
	}
 
	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
 
	return temp.toLowerCase();
}

function Tip() {
	return true;
}
function gobla(i) {
		document.location="admin.php?EditSysVar=" + i;
}
function validateDate(txtDate, format){  
  var objDate;  // date object initialized from the txtDate string  
  var mSeconds; // milliseconds from txtDate  
  
  // date length should be 10 characters - no more, no less  
  if (txtDate.length != 10) return false;  
  
  // extract day, month and year from the txtDate string  
  // expected format is in var format
  // subtraction will cast variables to integer implicitly  
  if (format == "mm-dd-yyyy") {
	  var day   = txtDate.substring(3,5)  - 0;  
	  var month = txtDate.substring(0,2)  - 1; // because months in JS start with 0  
	  var year  = txtDate.substring(6,10) - 0;  
  } else if (format == "dd-mm-yyyy") {
 	  var month   = txtDate.substring(3,5)  - 1;  
	  var day = txtDate.substring(0,2)  - 0; // because months in JS start with 0  
	  var year  = txtDate.substring(6,10) - 0;  
  } else if (format == "yyyy-mm-dd") {
  	  var month = txtDate.substring(5,7)  - 1;  
	  var day	= txtDate.substring(8,10)  - 0; // because months in JS start with 0  
	  var year  = txtDate.substring(0,4) - 0;  
  } else {
	return(false);
  }


	 
  // This instruction will create a date object
  source_date = new Date(year,month,day);

  if(year != source_date.getFullYear())
  {
	  return false;
  }

  if(month != source_date.getMonth())
  {
   
	 return false;
  }

  if(day != source_date.getDate())
  {
   
	 return false;
  }
   
   return true;
}
function PlaceHiddenItemsOnTaksbar() {
	/* re-build the taskbar */
	$('#closedItemsBar').html("");
	$('#closedItemsBar').append("<a href='#' class='closeEditMode'>[x]</a>");
	$('.hideable').each(function() {
		if ($(this).css("visibility") == 'hidden' || $(this).css("display") == 'none') {
			var toAdd = '<a href="#" class="unhideBarElement" tounhide="' + $(this).attr("id") + '"> [' + $(this).attr("id") + '] </a>';
			$('#closedItemsBar').append(toAdd);
		}
		
	});
}




$(document).ready(function() { 
	jQueryInit();
	jQueryOnlyOnce();

	if (document.getElementById("INTLV_ChatBody")) {
		if (document.getElementById("INTLV_ChatBody").innerHTML) {
			setInterval("refreshChat()", 3500);
		}
	}
});



function jQueryOnlyOnce() {
			
		

	var anchors = document.getElementsByTagName("a");  
	 for (var i=0; i<anchors.length; i++) {  
	   var anchor = anchors[i];  
	   if (anchor.getAttribute("href") &&  
		   anchor.getAttribute("rel") == "external")  
		 anchor.target = "_blank";  
	 }   
	
	$('img.expand').click(function() {
		if ($('#' + this.title).is(":hidden")) {
		  $('#' + this.title).slideDown("fast");
		  $(this).attr('src','images/t_minus.jpg');
		} else {
		  $('#' + this.title).slideUp("fast");
		  $(this).attr('src','images/t_plus.jpg');
		}
	});



	$('.closeEditMode').live('click', function() {
		$('#closedItemsBar').slideUp("slow");
		DisableEditMode();
		$('#editDashboardBar').slideDown("slow");
	});

	$('img.expandMeImage').live('click', function() {

		var el = $(this.parentNode);

		el.slideUp("slow");

		saveCoords(el.position.left, el.position.top, $(this.parentNode).attr('id'), "hidden");
		var toAdd = '<a href="#" class="unhideBarElement" tounhide="' + $(this.parentNode).attr("id") + '"> [' + $(this.parentNode).attr("id") + '] </a>';
		$('#closedItemsBar').append(toAdd);
		$('#closedItemsBar').slideDown("slow");

	});

	$('.unhideBarElement').live('click', function() {
		var ele = document.getElementById($(this).attr("tounhide"));
		var el = $('.' + $(this).attr("tounhide"));

		/*ele.style.visibility="visible";*/
		ele.style.display="block";

	/*	$(this).attr("tounhide").slideDown("fast"); */

		saveCoords('', '', $(this).attr("tounhide"), "show");
		
		PlaceHiddenItemsOnTaksbar();

	 });

	if ($(".draggable").length || $(".hideable").length) {
		$("#editDashboardBar").slideDown("slow");
	}


	$("#editDashboardBar").click(function() {
		$("#editDashboardBar").slideUp("slow");
		EnableEditMode();
		$('#closedItemsBar').slideDown("slow");
	});


	$('.hideable').each(function() {
		$(this).prepend("<div class='elementName'>" + $(this).attr("id") + "</div><img src='images/close.gif' class='expandMeImage'><br><div class='innerStuffToHide'>");  
		$(this).append("</div>");

		if ($(this).css("display") == 'none') {
			var toAdd = '<a href="#" class="unhideBarElement" tounhide="' + $(this).attr("id") + '"> [' + $(this).attr("id") + '] </a>';
			$('#closedItemsBar').append(toAdd);
		}

		$('#closedItemsBar').html("<a href='#' class='closeEditMode'>[x]</a>");
	});


}

function DisableEditMode() {
	$('.draggable').resizable('destroy');
	$('.draggable').draggable('destroy');
	$('.draggable').css("cursor", "");
	$('.draggable').css("border", "0px dashed #FFFFFF");
	$('.hideable').css("border", "0px dashed #FFFFFF");
	$('.hideable').children(".expandMeImage").css('visibility', 'hidden');
	$('.hideable').children(".elementName").css('display', 'none');
}

function EnableEditMode() {
	$('.draggable').resizable({
		  start: function(event, ui) {
			$(this).css("opacity", "0.6"); // Semi-transparent when dragging
		  },
		  stop: function(event, ui) {
			saveCoords('','',ui.helper.attr('id'),'',ui.size.width, ui.size.height);
			$(this).css("opacity", "1.0"); // Full opacity when stopped
		  }
	});


	$('.draggable').draggable({
		drag: function(event, ui) {
			$(this).css("opacity", "0.6"); // Semi-transparent when dragging
		  },
		  stop: function(event, ui) {
			saveCoords(ui.offset.left, ui.offset.top, ui.helper.attr('id'), false, ui.offset.width, ui.offset.height);
			$(this).css("opacity", "1.0"); // Full opacity when stopped
		  },
		cursor: "move"
	});
	
	$('.draggable').css("border", "1px dashed #000000");
	$('.draggable').css("cursor", "move");

	$('.hideable').css("border", "1px dashed #000000");
	$('.hideable').children(".expandMeImage").css('visibility', 'visible');
	$('.hideable').children(".elementName").css('display', 'inline');
	PlaceHiddenItemsOnTaksbar();
}

function refresh_dummydiv() {
	// Leave this function in place!
	document.getElementById('WaitImageDiv').style.visibility='hidden';
	document.getElementById('dummydiv').style.visibility='visible';

	// This function is for when you're using a file upload box called from a module. Add a div called dummydiv (style visibility: hidden)
	// containing the text to show when uploading succeeded. Use DisplayFileUploadBox to show the upload box.
}

function setCookie(cookieName,cookieValue)
{
	var today = new Date();
	var expire = new Date();
	nDays = 1;
	if (nDays==null || nDays==0) nDays=1;
	expire.setTime(today.getTime() + 3600000*24*nDays);

	var cookie_to_set = cookieName + "=" + escape(cookieValue)+ "; expires=" + expire.toGMTString();
	if (window.location.protocol == "https:")
	{
		cookie_to_set += "; secure";
	}

	document.cookie = cookie_to_set;
}


function getCookie(Name)
{
	var search = Name + "=";
	if (document.cookie.length > 0) // if there are any cookies
	{
		offset = document.cookie.indexOf(search);
		if (offset != -1) // if cookie exists
		{
			offset += search.length;
			// set index of beginning of value
			end = document.cookie.indexOf(";", offset);
			// set index of end of cookie value
			if (end == -1)
				end = document.cookie.length;
			return unescape(document.cookie.substring(offset, end));
		}
	}
}

function seterror(formfieldid)
{
	
	
	if (element = document.getElementById(formfieldid + 'TS')) {
		var errorid = formfieldid + 'TS_error';
	} else {
		var element = document.getElementById(formfieldid);
		var errorid = formfieldid + '_error';
	}

	if (document.getElementById(errorid) == null)
	{
		var error = document.createElement('img');
		error.setAttribute('id', errorid);
		error.setAttribute('src', 'images/exclamation.gif');
		element.parentNode.insertBefore(error,element);
		//dit kan alleen maar zo omdat IE het anders niet snapt
		document.getElementById(errorid).style.paddingRight = '5px';
	}
}
function removeerror(formfieldid)
{
	var errorid = formfieldid + '_error';
	var error = document.getElementById(errorid);
	if (error != null)
	{
		error.parentNode.removeChild(error);
	}
}



function computernumber2humannumber(number)
{
	//number must be a valid computer readable number; "-123.34843" of "1900"

	var negative = false;
	if (number.match(/^\-/))
	{
		negative = true;
		//removes negative sign: "-34" -> "34"
		number = number.replace(/^\-/, "");
	}
	var parts = number.split(".");
	var whole = parts[0];
	var decimal = "";
	if (parts.length == 2)
	{
		decimal = parts[1];
	}

	if (default_thousandsseparator != "")
	{
		for (var x = whole.length - 3; x > 0; x -= 3)
		{
			whole = whole.substr(0, x) + default_thousandsseparator + whole.substr(x);
		}
	}

	var formattednumber = "";
	if (negative)
	{
		formattednumber += "-";
	}
	formattednumber += whole;
	if (decimal != "")
	{
		formattednumber += default_decimalseperator + decimal;
	}

	return formattednumber;
}


function humannumber2computernumber(number)
{
	//number can be in any string format, with both comma's or points as decimal and thousands seperator. both negative (-) and positive (+) numbers are accepted

	var valid_number = true;

	var negative = false;
	var thousandsseparator = "";
	var decimalseperator = "";


	//check if number is negative
	if (number.match(/^\-/))
	{
		negative = true;
		//removes negative sign: "-34" -> "34"
		number = number.replace(/^\-/, "");
	}
	else
	{
		//removes positive sign: "+75" -> "75"
		number = number.replace(/^\+/, "");
	}

	var number_of_points = 0;
	var number_of_commas = 0;

	//we count the number of points and comma's
	for (var x = 0; x < number.length; x++)
	{
		if (number.substr(x, 1) == ".")
		{
			//we have a found a point
			number_of_points++;
			if (number_of_commas > 0)
			{
				//if a comma had been used previously, the point MUST be the decimalseperator and the comma the thousandsseparator
				thousandsseparator = ",";
				decimalseperator = ".";
			}
		}
		else if (number.substr(x, 1) == ",")
		{
			//we have a found a comma
			number_of_commas++;
			if (number_of_points > 0)
			{
				//if a point had been used previously, the comma MUST be the decimalseperator and the point the thousandsseparator
				thousandsseparator = ".";
				decimalseperator = ",";
			}
		}
	}


	//if there is more than 1 point in the number, this MUST be the thousandsseparator
	if (number_of_points > 1)
	{
		thousandsseparator = ".";
		//if there is also more than 1 comma, this is not a valid number
		if (number_of_commas > 1)
		{
			valid_number = false;
		}
	}

	//if there is more than 1 comma in the number, this MUST be the thousandsseparator
	if (number_of_commas > 1)
	{
		thousandsseparator = ",";
		//if there is also more than 1 point, this is not a valid number
		if (number_of_points > 1)
		{
			valid_number = false;
		}
	}


	var whole = "";
	var decimals = "";


	/*
	if there is only 1 point and no comma's, of 1 comma and no points, we have difficulty in deciding what the user means. Is the point (or comma) the decimal seperator or the thousandsseperator?
	the following checks try to determine this

	in the case of 1-3 digits followed by a comma or point followed by 3 digits, we are not sure what the user meant. these conditions could produce unwanted results, so numbers like:
	1.234
	23,500
	900.999
	are special cases

	*/

	if ((number_of_points == 1) && (number_of_commas == 0))
	{
		/*
		The number could now be in any of the following notations
		123.4
		123.45
		123.456
		123.4567
		*/

		//we check against the default decimalseperator. this could be set regionally. for example, in The Neterlands, the default decimalseperator is the comma
		//if the point is the default_decimalseperator, we ASSUME the user 
		if (default_decimalseperator == ".")
		{
			decimalseperator = ".";
		}
		else
		{
			var parts = number.split(".");
			whole = parts[0];
			decimals = parts[1];
			//remove leading zeroes
			whole = whole.replace(/^0+/, "");

			//we ASSUME that if there are 3 decimals following the point, this means the point is the thousandsseparator, so "123.456" -> "123456" HOWEVER this notations could also have meant "123.456" i.e. 123 456/1000
			//if there are more than 3 characters for the whole part of the number, then it CAN'T be the thousandsseparator, ie "1234.567" MUST be 1234 567/1000
			if ((decimals.length == 3) && (whole.length <= 3))
			{
				/*
				4.567
				34.567
				234.567
				*/

				thousandsseparator = ".";
			}
			//in all other cases (1, 2, >4 decimals, or >4 whole digits) the point MUST be the decimalseperator
			else
			{
				decimalseperator = ".";
			}
		}
	}

	if ((number_of_points == 0) && (number_of_commas == 1))
	{
		/*
		The number could now be in any of the following notations
		123,4
		123,45
		123,456
		123,4567
		*/

		if (default_decimalseperator == ",")
		{
			decimalseperator = ",";
		}
		else
		{
			var parts = number.split(",");
			whole = parts[0];
			decimals = parts[1];
			//remove leading zeroes
			whole = whole.replace(/^0+/, "");

			//we ASSUME that if there are 3 decimals following the comma, this means the comma is the thousandsseparator, so "123,456" -> "123456" HOWEVER this notations could also have meant "123.456" i.e. 123 456/1000
			//if there are more than 3 characters for the whole part of the number, then it CAN'T be the thousandsseparator, ie "1234.567" MUST be 1234 567/1000
			if ((decimals.length == 3) && (whole.length <= 3))
			{
				thousandsseparator = ",";
			}
			//in all other cases (1, 2, >4 decimals, or >4 whole digits) the comma MUST be the decimalseperator
			else
			{
				decimalseperator = ",";
			}
		}
	}



	//check if number ends with a comma followed by a dash ",-"
	//this is a currency format without cents (in The Netherlands)
	if (number.match(/\,\-$/))
	{
		//removes comma dash from number: "100,-" -> "100"
		number = number.replace(/\,\-$/, "");
		//we removed the comma, so this number has no more decimal places
		decimalseperator = "";
		//"232,123,-" is invalid
		if (number_of_commas > 1)
		{
			valid_number = false;
		}
	}


	whole = "";
	decimals = "";

	if (decimalseperator != "")
	{
		var parts = number.split(decimalseperator);
		if (parts.length != 2)
		{
			//malformed number, for example: "123.452.23.23,45.2"
			valid_number = false;
		}
		whole = parts[0];
		decimals = parts[1];

		//decimal part must now only contain digits
		if (!decimals.match(/^\d+$/))
		{
			valid_number = false;
		}
	}
	else
	{
		whole = number;
	}

	if (thousandsseparator != "")
	{
		//whole is now "10.345.987" or "10,345,987"
		var parts = whole.split(thousandsseparator);
		whole = "";
		for (var x = 0; x < parts.length; x++)
		{
			var partlength = parts[x].length;
			if (x == 0)
			{
				//the first part must be between 1 and 3 characters
				if ((partlength < 1) || (partlength > 3))
				{
					valid_number = false;
				}
			}
			//each following part must be 3 characters
			else if (partlength != 3)
			{
				valid_number = false;
			}
			//add the part (without seperators) to the whole number
			whole += parts[x];
		}
	}

	//whole number must now only contain digits
	if (!whole.match(/^\d+$/))
	{
		valid_number = false;
	}

	//remove leading zeroes
	whole = whole.replace(/^0+/, "");
	//if all leading zeroes are removed, place one back
	if (whole == "")
	{
		whole = "0";
	}

	//now rebuild new number
	var newnumber = "";
	if (negative)
	{
		newnumber += "-";
	}
	newnumber += whole;
	if (decimalseperator != "")
	{
		newnumber += "." + decimals;
	}

	if (!valid_number)
	{
		newnumber = Number.NaN;
	}

	return newnumber;
}

function NumberAutoFormat(displayfield, back) {

	var displayfield_obj = document.getElementById(displayfield);
	var updatefield = displayfield.replace(/_displayonly/, "");
	var updatefield_obj = document.getElementById(updatefield);

	if (displayfield_obj.value != '') {
		var number = humannumber2computernumber(displayfield_obj.value);
		
		updatefield_obj.value = number;

		if (isNaN(number)) {
			seterror(displayfield);
		} else {
			if (!back) {
				displayfield_obj.value = computernumber2humannumber(number);
			}
			removeerror(displayfield);
		}

		
	} else {
		removeerror(displayfield);
		updatefield_obj.value = '';
	}

	
}

function ValidateByAjaxSimple(eid, fieldid, value, referfield) {
	if (value != '') {
		var toReturn = true;
		var request = GetXmlHttpObject();
		var url = "populate.php?validatebyajax=1&efid=" + fieldid + "&eid=" + eid + '&value=' + urlencodejs(value) + '&refer=' + referfield;

		request.open("GET", url, false);
		request.onreadystatechange = function() {
			if (request.readyState == 4) {
				if (request.status == 200) {
					var res = request.responseText.split("|||");
					if (res[0] == "nok") {
						toReturn = false;
					} else if (res[0] == "ok") {
						toReturn = true;
					} else {
						alert("Unrecognized response while validating field value " + tmp);
						toReturn = false;
					}
				} else {
					alert("The server was unable to process your validation request");
					toReturn = false;
				}
			}
		};
		request.send(null);
		return toReturn;
	}
}
function ValidateByAjax(eid, fieldid, value, oldvalue, referfield) {
	if (value != '') {
		$.ajax({
		   type: "POST",
		   url: "populate.php",
		   data: "validatebyajax=1&efid=" + fieldid + "&eid=" + eid + '&value=' + urlencodejs(value) + '&oldvalue=' + urlencodejs(oldvalue) + '&refer=' + referfield,
		   success: ParseValidateByAjaxResult
		}, [fieldid]);
	}
}
function ParseValidateByAjaxResult(reply) {
    var res = reply.split("|||");
	if (is_numeric(res[1]))
	{
		res[1] = "JS_EFID" + res[1];
	}
	if (res[0] == "nok") {
		seterror(res[1]);
	} else {
		removeerror(res[1]);
	}
	if (typeof res[3] != "undefined" && res[3] != "") {
		alert(res[3]);
	}
	if (typeof res[2] != "undefined" && res[2] != "" && document.getElementById(res[1])) {
		document.getElementById(res[1]).value = res[2];
	}

}
function GetAndSetFieldValueByAjax(field, record, formelementToSet, newvalue) {
	if (field != '' && record != '') {
		$.ajax({
		   type: "POST",
		   url: "populate.php",
		   data: "getfieldvalue=1&formatted=1&field=" + field + "&record=" + record + "&formelementToSet=" + formelementToSet + '&newvalue=' + newvalue,
		   success: GetFieldValueByAjaxHelper
		}, [field]);
	}
}
function GetFieldValueByAjaxHelper(reply) {
	var res = reply.split("|||");
	if (document.getElementById(res[1]))
	{
		document.getElementById(res[1]).innerHTML = res[2];
	}
}

function CheckUniqueness(eid, fieldid, value, oldvalue) {
	if (value != '')
	{

		$.ajax({
		   type: "POST",
		   url: "populate.php",
		   data: "checkuniqueness=1&efid=" + fieldid + "&eid=" + eid + '&value=' + urlencodejs(value) + '&oldvalue=' + urlencodejs(oldvalue),
		   success: ParseCheckUniquenessResult
		}, [fieldid]);
	}
}

function ParseCheckUniquenessResult(reply) {
    var res = reply.split("|||");
	if (res[0] == "nok")
	{
		seterror('JS_EFID' + res[1]);
		document.getElementById('JS_EFID' + res[1]).value = res[2];
		alert('Values in this field must be unique. There is already another record having this value.');
	} else {
		removeerror('JS_EFID' + res[1]);
	}
}
function in_array (needle, haystack, argStrict) {
    // Checks if the given value exists in the array  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/in_array    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: vlado houba
    // +   input by: Billy
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);    // *     returns 1: true
    // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
    // *     returns 2: false
    // *     example 3: in_array(1, ['1', '2', '3']);
    // *     returns 3: true    // *     example 3: in_array(1, ['1', '2', '3'], false);
    // *     returns 3: true
    // *     example 4: in_array(1, ['1', '2', '3'], true);
    // *     returns 4: false
    var key = '',        strict = !! argStrict;
 
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {                return true;
            }
        }
    } else {
        for (key in haystack) {            if (haystack[key] == needle) {
                return true;
            }
        }
    } 
    return false;
}
function PutSelectedDateInParentForm(id, date, baseid) {
	parent.document.getElementById(id + "TS").innerHTML = '<a onclick="PopCalendarSelectDay(' + baseid + ');">' + date + '</a>';
	parent.document.getElementById(id).value = date;
	parent.document.getElementById(id).onchange();
	parent.$.fancybox.close();
}