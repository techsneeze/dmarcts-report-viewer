# dmarcts-report-viewer
A PHP viewer for DMARC records that have been parsed by [John Levine's rddmarc script](http://www.taugh.com/rddmarc/) or the [dmarcts-report-parser.pl](https://github.com/techsneeze/dmarcts-report-parser)
* Allow to view pass/fail data for the parsed reports
* Identify potential DMARC related issues through red/orange/green signals

## Screenshots

![alt text](https://cert.civis.net/images/main-600.png "Screenshot 1")

[Larger version of Screenshot 1](https://cert.civis.net/images/main.png)

![alt text](https://cert.civis.net/images/reportdata-600.png "Screenshot 2")

[Larger version of Screenshot 2](https://cert.civis.net/images/reportdata.png)

![alt text](https://cert.civis.net/images/reportxml-600.png "Screenshot 3")

[Larger version of Screenshot 3](https://cert.civis.net/images/reportxml.png)

## Installation and Configuration

NOTE: The viewer expects that you have already populated a database with data from [John Levine's rddmarc script](http://www.taugh.com/rddmarc/) or the [dmarcts-report-parser.pl](https://github.com/techsneeze/dmarcts-report-parser) script.

To install dependencies...

### on Debian/Unbuntu:

you need a webserver (apache, nginx, ...) with installed php and additionaly:

```
apt-get install  php-mysql
```

### Download the required files:
```
git clone https://github.com/ANewSmurf/dmarcts-report-viewer.git
```

Once the PHP files have been downloaded, you will need to copy `dmarcts-report-viewer-config.php.sample` to `dmarcts-report-viewer-config.php`.

```
cp dmarcts-report-viewer-config.php.sample dmarcts-report-viewer-config.php
```

### Configuration

Next, edit these basic configuration options at the top of the `dmarcts-report-viewer-config.php` file with the appropriate information:

```
$dbhost="localhost";
$dbname="dmarc";
$dbuser="dmarc";
$dbpass="xxx";
$dbport="3306";
$cssfile="default.css";

$default_lookup = 0;  # 1=on 0=off (on is old behaviour )
$default_sort = 0;  # 1=ASCdening 0=DESCending (ASCending is default behaviour )
```

Ensure that `dmarcts-report-viewer-config.php` and `dmarcts-report-viewer.php` are in the same folder.
## Usage

Navigate in your browser to the location of the `dmarcts-report-viewer.php` file.

You should be presented with the basic report view, allowing you to navigate through the reports that have been parsed.

### Legend of the Colors
* Green : DKIM and SPF = pass
* Red : DKIM and SPF = fail
* Orange : Either DKIM or SPF (but not both) = fail
* Yellow : Some other condition, and should be investigated (e.g. DKIM or SPF result were missing, "softfail", "temperror", etc.)

