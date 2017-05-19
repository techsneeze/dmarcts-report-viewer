<?php
///////////////////////////////////////////////////////////////////////////

$dbhost="localhost";
$dbname="dmarc";
$dbuser="dmarc";
$dbpass="xxx";

/////////////// NO CHANGES BELOW //////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
// Make a MySQL Connection
mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());

$query_report = "SELECT * FROM report ORDER BY mindate"; 
	 
$result_report = mysql_query($query_report) or die(mysql_error());

echo "<title>DMARC Report</title>";
echo "<head>\n";
echo "</head>\n";
echo "<html>\n";
echo "<body>\n";

echo "<center><h1>DMARC Reports</h1></center>\n";

echo "<hr align=center width=90% noshade>\n";
	 
function format_date($date, $format){
                    $answer = date($format, strtotime($date));
                    return $answer;
        };

echo "<table align=center border=0 cellpadding=3>\n";
echo "<thead><tr><th>Start Date</th><th>End Date</th><th>Domain</th><th>Reporting Organization</th><th>Report ID</th><th>Messages</th></tr></thead><tbody>\n";

$result_report = mysql_query($query_report) or die(mysql_error());

while($row = mysql_fetch_array($result_report)){
	$array_report[] = $row;
	$message_query = "SELECT *, SUM(rcount) FROM rptrecord WHERE serial = {$row['serial']}";
	$message_process = mysql_query($message_query) or die(mysql_error());
	$message_result = mysql_fetch_array($message_process);
	$date_output_format = "r";
	echo "<tr align=center>";
	echo "<td align=right>". format_date($row['mindate'], $date_output_format). "</td><td align=right>". format_date($row['maxdate'], $date_output_format). "</td><td>". $row['domain']. "</td><td>". $row['org']. "</td><td><a href=?report=". $row['serial']. "#rpt". $row['serial']. ">". $row['reportid']. "</a></td><td>". $message_result['SUM(rcount)']. "</td>";
	echo "</tr>";
	echo "\n";
}
echo "</tbody>";
echo "</table>";
echo " <br />";
echo "\n";
//echo "-------------------------------------------------------------------------------------";
echo "<hr align=center width=90% noshade>";
echo " <br />";
echo "\n";
/////////Start Lower Section

// Get value (if it exists) from URL
$displayreport = 0;
if ($_GET) {
	$displayreport = $_GET["report"];
}

if($displayreport !== 0){

$current = 0;

$query_date = "SELECT * FROM report where serial = $displayreport";

$query_rptrecord = "SELECT * FROM rptrecord where serial = $displayreport";

$result_date = mysql_query($query_date) or die(mysql_error());
$showdate = mysql_fetch_array($result_date);
echo "<br/><center><strong>". format_date($showdate['mindate'], r ). "</strong></center><br />\n";

$result_rptrecord = mysql_query($query_rptrecord) or die(mysql_error());

echo "<table align=center border=0 cellpadding=2>";
echo "<th>IP Address</th><th>Host Name</th><th>Message Count</th><th>Disposition</th><th>Reason</th><th>DKIM Domain</th><th>DKIM Result</th><th>SPF Domain</th><th>SPF Result</th>\n";
while($row = mysql_fetch_array($result_rptrecord)){
	$rowcolor="FFFFFF";
	if (($row['dkimresult'] == "fail") && ($row['spfresult'] == "fail")){
	$rowcolor="FF0000"; //red
	} elseif (($row['dkimresult'] == "fail") || ($row['spfresult'] == "fail")){
	$rowcolor="FFA500"; //orange
	} elseif (($row['dkimresult'] == "pass") && ($row['spfresult'] == "pass")){
	$rowcolor="00FF00"; //lime
	} else {
	$rowcolor="FFFF00"; //yellow
	};
	echo "<tr align=center bgcolor=". $rowcolor. ">";
        echo "<td><a name=rpt". $row['serial'].">". long2ip($row['ip']). "</td><td>". gethostbyaddr(long2ip($row['ip'])). "</td><td>". $row['rcount']. "</td><td>". $row['disposition']. "</td><td>". $row['reason']. "</td><td>". $row['dkimdomain']. "</td><td>". $row['dkimresult']. "</td><td>". $row['spfdomain']. "</td><td>". $row['spfresult']. "</td>";
        echo "</tr>";
	echo "\n";
}
echo "</table>";

echo "<hr align=center width=90% noshade>";
echo "<center><h5>Brought to you by <a href=http://www.techsneeze.com>TechSneeze.com</a> - <a href=mailto:dave@techsneeze.com>dave@techsneeze.com</a></h5></center><br />\n";
}
echo "</body>";
echo "</html>";
//var_dump($array_report);
//var_dump($message_result);
//print_r(array_keys($array_report[5]));
?>
