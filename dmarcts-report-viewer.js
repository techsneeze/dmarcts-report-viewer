// dmarcts-report-viewer - A PHP based viewer of parsed DMARC reports.
// Copyright (C) 2016 TechSneeze.com, John Bieling and John P. New
// with additional extensions (sort order) of Klaus Tachtler.
//
// Available at:
// https://github.com/techsneeze/dmarcts-report-viewer
//
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of  MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.
//
//####################################################################
//### configuration ##################################################
//####################################################################

// Copy dmarcts-report-viewer-config.php.sample to
// dmarcts-report-viewer-config.php and edit with the appropriate info
// for your database authentication and location.
//
// Edit the configuration variables in dmarcts-report-viewer.js with your preferences.

// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
// Supplemental Configuration

var default_reportlist_height = 60; // Main Report List height as a percentage of browser window height (without the % mark)

// End Supplemental Configuration
// ----------------------------------------------------------------------------

var current_report;
const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
const comparer = (idx, asc) => (a, b) => ((v1, v2) => v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2))(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

var report_data_xml_width_last = 0;

// ----------------------------------------------------------------------------
//Functions
// ----------------------------------------------------------------------------

// Function to reset the <select> filters to show all records and refresh the data shown in the report_list_table.
function reset_report_list() {

	filter = document.getElementsByTagName("select");
	for (i = 0; i < filter.length; i++) {
		filter[i].value = "all";
	}
	refresh_report_list();
}

// Function to refesh the data shown in the report_list_table using the currently selected <select> filters.
function refresh_report_list() {

	showReportlist('reportlistTbl');
}


function sorttable (table_id) {

	document.getElementById(table_id).querySelectorAll('th').
		forEach(th =>
			th.addEventListener(
				'click',
				(() => {
					const th_idx = Array.from(th.parentNode.children).indexOf(th); // Index of <th> element clicked, i.e. Sort column
					const table = document.getElementById(table_id);
// 					const table = th.closest('table');
					const tbody = table.querySelector('tbody');
					Array.from(tbody.querySelectorAll('tr')).sort(
						comparer(
							th_idx, this.asc = !this.asc)).
								forEach(tr =>
									tbody.appendChild(tr)
								);
					Array.from(th.parentNode.children).forEach(th => {th.classList.remove("asc_triangle");th.classList.remove("desc_triangle"); } );
					this.asc ? th.parentNode.children[th_idx].classList.add("asc_triangle") : th.parentNode.children[th_idx].classList.add("desc_triangle");
				}
				)
			)
		);
}

function showReportlist(str) { // str is the name of the <div> to be filled

	var GETstring = "?";

	var domain = document.getElementById('selDomain').options[document.getElementById('selDomain').selectedIndex].value;
	var org = document.getElementById('selOrganisation').options[document.getElementById('selOrganisation').selectedIndex].value;
	var period = document.getElementById('selPeriod').options[document.getElementById('selPeriod').selectedIndex].value;
	var dmarc = document.getElementById('selDMARC').options[document.getElementById('selDMARC').selectedIndex].value;

	GETstring += "d=" + domain;
	GETstring += "&o=" + org;
	GETstring += "&p=" + period;
	GETstring += "&dmarc=" + dmarc;

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange =
		function() {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("report_list").innerHTML = this.responseText;
				document.getElementById("report_data").innerHTML = "";
				sorttable(str);
				set_title(domain);
				makeResizableDiv();
				document.getElementById('resizer_horizontal').style.display = 'none';
				document.getElementById('resizer_vertical').style.display = 'none';
			}
		};

	xhttp.open("GET", "dmarcts-report-viewer-report-list.php" + GETstring, true);
	xhttp.send();
}

function set_title(domain) {

	domain == 'all' ? document.getElementById('title').innerText = "DMARC Reports" : document.getElementById('title').innerText = "DMARC Reports for " + domain;
}

function set_heights() {

	var report_list_height_percentage = default_reportlist_height/100;
	var taken_height =
		parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
		+ parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-bottom'))
		+ document.getElementById('optionblock').offsetHeight
		+ document.getElementById('title').offsetHeight
		+ document.getElementById('footer').offsetHeight
		+ parseInt(window.getComputedStyle(document.getElementById('footer')).getPropertyValue('margin-top'))
	;
	var available_height = window.innerHeight - taken_height;
	var report_list_height = parseInt(report_list_height_percentage * available_height);
	var report_data_height = available_height - report_list_height;


	document.getElementById('report_list').style.height = report_list_height + "px";
	document.getElementById('report_data').style.height = report_data_height + "px";
}

function set_report_data_heights() {

	report_data_table_div_height =
		document.getElementById('report_data').offsetHeight - document.getElementById('report_desc_container').offsetHeight
		+ "px"
		;

	document.getElementById('report_data_table_div').style.height = report_data_table_div_height;
	document.getElementById('report_data_xml').style.height = report_data_table_div_height;

}
function showXML() {

	if (document.getElementById('report_data_xml').style.display == 'none') {
		var div_height = document.getElementById('report_data_table_div').style.height
		document.getElementById('report_data_xml').style.display = 'inline-block';
		document.getElementById('report_data_table_div').style.display = 'inline-block';
		document.getElementById('report_data_table_div').style.float = 'left';
		document.getElementById('xml_html_img').src = 'html.png';
		document.getElementById('xml_html_img').title = 'Hide Raw Report XML';
		document.getElementById('xml_html_img').alt = 'Hide Raw Report XML';
		document.getElementById('resizer_vertical').style.display = "block";
		set_report_data_widths("open_xml");
	} else {
		var div_height = document.getElementById('report_data_xml').style.height
		document.getElementById('report_data_xml').style.display = 'none';
		document.getElementById('report_data_table_div').style.display = 'block';
		document.getElementById('report_data_table_div').style.float = '';
		document.getElementById('xml_html_img').src = 'xml.png';
		document.getElementById('xml_html_img').title = 'Show Raw Report XML';
		document.getElementById('xml_html_img').alt = 'Show Raw Report XML';
		document.getElementById('resizer_vertical').style.display = "none";
		set_report_data_widths("close_xml");
	}
	showResizers();
}

function set_report_data_widths (open_close) {

	report_data_xml_width_percent = .20;

	// An allowance to accomodate Report Data table width expanding/contracting when sorting arrow is added to/removed from the column title
	// HTML5 doesn't seem to currently have a built-in onresize event for divs, so to do this automatically would take some decent JS library like https://github.com/marcj/css-element-queries
	allowance = 30;

	if ( open_close == "open_xml" ) {
		if ( report_data_xml_width_last == 0){
			report_data_xml_width = parseInt(document.getElementById('report_data').offsetWidth * report_data_xml_width_percent);
			report_data_table_div_width = document.getElementById('report_data').offsetWidth - report_data_xml_width - allowance;
		} else {
			report_data_xml_width = report_data_xml_width_last - 2;
			// Why '-2'? Because when the report_data_xml div is resized and another report is chosen, the handle and separator jump by 2 pixels
			// If someone can find out why, please fix it (it's probably because of all the other tiny fudges all over the place)
			report_data_table_div_width = document.getElementById('report_data').offsetWidth - report_data_xml_width - allowance;
		}
	} else {
		report_data_xml_width = 0;
		report_data_table_div_width = document.getElementById('report_data').offsetWidth;
	}

	document.getElementById('report_data_xml').style.width = report_data_xml_width + "px";
	document.getElementById('report_data_table_div').style.width = report_data_table_div_width + "px";
}

function showReport(str) {

	document.getElementsByTagName("HTML")[0].style.cursor = "wait";
	document.getElementById('reportlistTbl').querySelectorAll('tr').forEach(tr => tr.style.cursor = "wait");

	if (str == null) {
		alert('No report is selected.');
		return;
	}
	setSelected(str);	// setSelected function highlights the report row that is selcted

	current_report = str;

	var xhttp;
	if (str == "") {
		document.getElementById("report_data").innerHTML = "";
		return;
	}

	var GETstring = "report=" + str;

	var HostLookup = document.getElementsByName('selHostLookup');
	var HostLookup_value = false;
	for ( var i = 0; i < HostLookup.length; i++) {
		if(HostLookup[i].checked) {
			GETstring += "&hostlookup=" + HostLookup[i].value;
			break;
		}
	}

	GETstring += "&p=" + document.getElementById('selPeriod').value;
	GETstring += "&dmarc=" + document.getElementById('selDMARC').options[document.getElementById('selDMARC').selectedIndex].value;

	xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange =
		function() {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("report_data").innerHTML = this.responseText;
				sorttable('report_data_table');
				set_report_data_heights();
				document.getElementsByTagName("HTML")[0].style.cursor = "default";
				document.getElementById('reportlistTbl').querySelectorAll('tr').forEach(tr => tr.style.cursor = "pointer");
				showResizers();
				if ( typeof report_data_xml_width !== 'undefined' && report_data_xml_width > 0 ) {
					showXML("open_xml");
				}
			}
		}

	xhttp.open("GET", "dmarcts-report-viewer-report-data.php?" + GETstring, true);
	xhttp.send();
}

// Functions that allow resizing of the data and eaw xml divs
// Inspired by the code at https://medium.com/the-z/making-a-resizable-div-in-js-is-not-easy-as-you-think-bda19a1bc53d
function showResizers() {

	if ( document.getElementById('report_desc_container') || document.getElementById('report_data_xml') ) {
	document.getElementById('resizer_horizontal').style.display = "block";

	document.getElementById('resizer_horizontal').style.top =
		parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
		+ document.getElementById('optionblock').offsetHeight
		+ document.getElementById('title').offsetHeight
		+ document.getElementById('report_list').offsetHeight
		- (document.getElementById('resizer_horizontal').offsetHeight)/2
		+ "px";

		document.getElementById('resizer_vertical').style.top =
			parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
			+ document.getElementById('optionblock').offsetHeight
			+ document.getElementById('title').offsetHeight
			+ document.getElementById('report_list').offsetHeight
			+ document.getElementById('report_desc_container').offsetHeight
			+ document.getElementById('report_data_xml').offsetHeight/2
			- (document.getElementById('resizer_vertical').offsetHeight)/2
			+ "px";

		document.getElementById('resizer_vertical').style.left =
			+ document.getElementById('report_data_xml').offsetLeft
			- (document.getElementById('resizer_vertical').offsetWidth)/2
			+ 1	// Need to offset div by borderWidth/2 to centre the resizing handle on the border
				// But for some reason, some browsers (Google, Firefox; any others?) won't return the borderWidth (or even borderTopWidth) property if it set in a css file (but WILL return it if set inline with style="")
				// 		+ parseInt(document.getElementById('resizer_vertical').style.borderWidth)/2
			+ "px";
	}
}

function setSelected(str) {

	const table = document.getElementById("reportlistTbl");
	const rows = table.getElementsByTagName("tr");
	for (i = 0; i < rows.length; i++) {
		var currentRow = table.rows[i];
		currentRow.classList.remove("selected");
	}
	document.getElementById("report"+str).className += " selected";
}

function makeResizableDiv() {

	document.getElementById('resizer_horizontal').addEventListener(
		'mousedown',
			function (e) {
				e.preventDefault();
				original_width = document.getElementById('resizer_vertical').style.top;
				original_height_report_data = document.getElementById('report_data').offsetHeight;
				original_height_report_list = document.getElementById('report_list').offsetHeight;
				if ( document.getElementById('report_data_table_div') ) {
					original_height_report_data_table_div = document.getElementById('report_data_table_div').offsetHeight;
				}
				original_height_resizer_vertical = 30; // Should be equal to the in css file
				original_x = document.getElementById('resizer_horizontal').getBoundingClientRect().left;
				original_y = document.getElementById('resizer_horizontal').getBoundingClientRect().top;
				original_mouse_x = e.pageX;
				original_mouse_y = e.pageY;
				window.addEventListener('mousemove', resize);
				window.addEventListener('mouseup', stopResize);
			}
		);

	function resize(e) {

		const height =	original_height_report_list
						- document.getElementById('optionblock').offsetHeight
						;
		if ( (original_mouse_y - e.pageY) < height && e.pageY < (window.innerHeight - document.getElementById('footer').offsetHeight - (document.getElementById('resizer_horizontal').offsetHeight / 2)) ) {
			document.getElementById('resizer_horizontal').style.top = original_y + (e.pageY - original_mouse_y) + 'px';
			document.getElementById('resizer_vertical').style.top =
				document.getElementById('report_data_xml').offsetTop
				+ document.getElementById('report_data_xml').offsetHeight/2
				- document.getElementById('resizer_vertical').offsetHeight/2
				+ 'px'
			;
			document.getElementById('report_list').style.height =+ original_height_report_list + (e.pageY - original_mouse_y) + 'px';
			document.getElementById('report_data_xml').style.height =+ original_height_report_data_table_div + (original_mouse_y - e.pageY) + 'px';
			document.getElementById('report_data_table_div').style.height =+ original_height_report_data_table_div + (original_mouse_y - e.pageY) + 'px';
			document.getElementById('report_data').style.height =+ original_height_report_data + (original_mouse_y - e.pageY) + 'px';
			document.getElementById('body').style.height =+
				window.innerHeight
				- parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-top'))
				- parseInt(window.getComputedStyle(document.getElementById('body')).getPropertyValue('margin-bottom'))
				+ 'px'
			;

			if ( document.getElementById('report_data_xml').offsetHeight > original_height_resizer_vertical) {
				document.getElementById('resizer_vertical').style.height = original_height_resizer_vertical + 'px';
			} else {
				document.getElementById('resizer_vertical').style.height = document.getElementById('report_data_xml').offsetHeight - 4 + 'px';
				if ( document.getElementById('report_data_xml').offsetHeight <= 4) {
					document.getElementById('resizer_vertical').style.display = 'none';
				} else {
					document.getElementById('resizer_vertical').style.display = 'block';
				}
			}
		}
	}

		function stopResize() {

			window.removeEventListener('mousemove', resize);
		}

	document.getElementById('resizer_vertical').addEventListener('mousedown', function (e) {
		e.preventDefault();// ;
		original_width_report_data_xml = document.getElementById('report_data_xml').offsetWidth;
		original_width_report_data_table_div = document.getElementById('report_data_table_div').offsetWidth;
		original_x = document.getElementById('resizer_vertical').getBoundingClientRect().left;
		original_y = document.getElementById('resizer_vertical').getBoundingClientRect().top;
		original_mouse_x = e.pageX;
		original_mouse_y = e.pageY;
		window.addEventListener('mousemove', resize_vertical);
		window.addEventListener('mouseup', stopResize_vertical);
	});

	function resize_vertical(e) {

		if ( e.pageX > document.getElementById('resizer_vertical').offsetWidth && e.pageX < window.innerWidth - document.getElementById('resizer_vertical').offsetWidth ) {
			mouse_movement = e.pageX - original_mouse_x;
			document.getElementById('report_data_xml').style.width =+ original_width_report_data_xml - mouse_movement + 'px';
			document.getElementById('report_data_table_div').style.width =+ original_width_report_data_table_div + mouse_movement -5 + 'px';
			document.getElementById('resizer_vertical').style.left =
				document.getElementById('report_data_xml').offsetLeft
				- document.getElementById('resizer_vertical').offsetWidth/2
				+ 1
				+ 'px'
			;
		}

		report_data_xml_width_last = document.getElementById('report_data_xml').offsetWidth;
	}

		function stopResize_vertical() {

			window.removeEventListener('mousemove', resize_vertical);
		}
}
