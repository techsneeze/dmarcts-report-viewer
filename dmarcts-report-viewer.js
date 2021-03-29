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
				set_heights();
				set_title(domain);
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
		+ document.getElementById('footer').offsetHeight + parseInt(window.getComputedStyle(document.getElementById('footer')).getPropertyValue('margin-top'))
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
	} else {
		var div_height = document.getElementById('report_data_xml').style.height
		document.getElementById('report_data_xml').style.display = 'none';
		document.getElementById('report_data_table_div').style.display = 'block';
		document.getElementById('report_data_table_div').style.float = '';
		document.getElementById('xml_html_img').src = 'xml.png';
		document.getElementById('xml_html_img').title = 'Show Raw Report XML';
		document.getElementById('xml_html_img').alt = 'Show Raw Report XML';
	}
	set_report_data_widths();
}

function set_report_data_widths() {
	report_data_xml_width = 
	document.getElementById('report_data').offsetWidth - document.getElementById('report_data_table_div').offsetWidth
	-5	// A fudge factor to accomodate Report Data table width expanding/contracting when sorting arrow is added to/removed from the column title
		// HTML5 doesn't seem to currently have a built-in onresize event for divs, so to do this automatically would take some decent JS library like https://github.com/marcj/css-element-queries
	+ "px"
	;
document.getElementById('report_data_xml').style.width = report_data_xml_width;

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
			}
		};

	xhttp.open("GET", "dmarcts-report-viewer-report-data.php?" + GETstring, true);
	xhttp.send();
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
	const resizers = document.querySelectorAll('.resizable');
	const minimum_size = 20;
	let original_width = 0;
	let original_height = 0;
	let original_x = 0;
	let original_y = 0;
	let original_mouse_x = 0;
	let original_mouse_y = 0;
alert(resizers.length);
	for (let i = 0; i < resizers.length; i++) {
		const currentResizer = resizers[i];
		currentResizer.addEventListener('mousedown', function (e) {
			e.preventDefault();
			original_width = parseFloat(getComputedStyle(element, null).getPropertyValue('width').replace('px', ''));
			original_height = parseFloat(getComputedStyle(element, null).getPropertyValue('height').replace('px', ''));
			original_x = element.getBoundingClientRect().left;
			original_y = element.getBoundingClientRect().top;
			original_mouse_x = e.pageX;
			original_mouse_y = e.pageY;
			window.addEventListener('mousemove', resize);
			window.addEventListener('mouseup', stopResize);
		});

		function resize(e) {
			if (currentResizer.classList.contains('bottom-right')) {
				const width = original_width + (e.pageX - original_mouse_x);
				const height = original_height + (e.pageY - original_mouse_y);
				if (width > minimum_size) {
					element.style.width = width + 'px';
				}
				if (height > minimum_size) {
					element.style.height = height + 'px';
				}
			} else if (currentResizer.classList.contains('bottom-left')) {
				const height = original_height + (e.pageY - original_mouse_y);
				const width = original_width - (e.pageX - original_mouse_x);
				if (height > minimum_size) {
					element.style.height = height + 'px';
				}
				if (width > minimum_size) {
					element.style.width = width + 'px';
					element.style.left = original_x + (e.pageX - original_mouse_x) + 'px';
				}
			} else if (currentResizer.classList.contains('top-right')) {
				const width = original_width + (e.pageX - original_mouse_x);
				const height = original_height - (e.pageY - original_mouse_y);
				if (width > minimum_size) {
					element.style.width = width + 'px';
				}
				if (height > minimum_size) {
					element.style.height = height + 'px';
					element.style.top = original_y + (e.pageY - original_mouse_y) + 'px';
				}
			} else {
				const width = original_width - (e.pageX - original_mouse_x);
				const height = original_height - (e.pageY - original_mouse_y);
				if (width > minimum_size) {
					element.style.width = width + 'px';
					element.style.left = original_x + (e.pageX - original_mouse_x) + 'px';
				}
				if (height > minimum_size) {
					element.style.height = height + 'px';
					element.style.top = original_y + (e.pageY - original_mouse_y) + 'px';
				}
			}
		}

		function stopResize() {
			window.removeEventListener('mousemove', resize);
		}
	}
}
