<?

function save($buffer)
{
chdir(dirname($_SERVER['SCRIPT_FILENAME']));
$tb=parse_ini_file('config.php',true);
$file=(isset($tb['FILE']['kmlfile']))?$tb['FILE']['kmlfile']:"export.kml";
file_put_contents($file,$buffer);
return $buffer;
}

header('Content-type: application/vnd.google-earth.kml+xml');
$tb=parse_ini_file('config.php',true);
$CACHEFILE=(isset($tb['FILE']['cachefile']))?$tb['FILE']['cachefile']:0;
if (!is_file($CACHEFILE)) exit();
$Xml=simplexml_load_file($CACHEFILE);
header("Content-Disposition: attachment; filename=\"cyclone.kml\"");
ob_start(save);

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<kml xmlns="http://www.opengis.net/kml/2.2">
<Document>
<Style id="red"> <IconStyle> <Icon> <href>http://maps.google.com/mapfiles/kml/paddle/red-blank.png</href> </Icon> </IconStyle> </Style>
<Style id="green"><IconStyle> <Icon> <href>http://maps.google.com/mapfiles/kml/paddle/grn-blank.png</href> </Icon> </IconStyle> </Style>
<Style id="nuage"><IconStyle><Icon><href>http://maps.google.com/mapfiles/kml/shapes/rainy.png</href></Icon></IconStyle></Style>
<?
foreach($Xml as $Cyclone)
{
	$name = htmlentities($Cyclone->info->name);
	$url = $Cyclone->info->url;
	$img = $Cyclone->info->img;
	$img="<img width='320px' height='240px' src=$img />";
	echo "<Folder><name>$name</name>";
	$a=0;
	$max=$Cyclone->history->plot->count();
	$style="red";
	$tb=null;
	foreach ($Cyclone->history->plot as $node)
	{
		$a++;
		$time=$node->time;
		$lat=$node->latitude;
		$lng=$node->longitude;
		$int=$node->intensity;
	//	echo "$time";
		$tb[$a]['time']=$time;
		$tb[$a]['lat']=$lat;
		$tb[$a]['lng']=$lng;
		$tb[$a]['int']=$int;

	}
	krsort($tb);
	$a=0;
	foreach($tb as  $node)
	{
		$time=$node['time'];
		$lat=$node['lat'];
		$lng=$node['lng'];
		$int=$node['int'];

		$a++;

		echo "
			<Placemark>
			<name>$name $time</name>
			<description> <![CDATA[Tracking $time ($a/$max)<br> intensite $int <a href='$url'>link</a><br>";
		if ($a==count($tb)) echo "$img";
		if ($a==count($tb)) $style="nuage";
		echo " ]]>
			</description>
			<styleUrl>#$style</styleUrl>
			<gx:balloonVisibility>0</gx:balloonVisibility>
			<Point><coordinates>$lng,$lat,0</coordinates></Point>
			</Placemark>";
	}
	foreach($Cyclone->forecast->plot as $node)
	{
		$time=$node->time;
		$lat=$node->latitude;
		$lng=$node->longitude;
		$int=$node->intensity;
		if ($lat!=0) echo "
			<Placemark>
				<name>$name $time</name>
				<description>Prevision $time, force $int</description>
				<styleUrl>#green</styleUrl>
				<gx:balloonVisibility>0</gx:balloonVisibility>
				<Point><coordinates>$lng,$lat,0</coordinates></Point>
				</Placemark>";
	}




	echo "</Folder>\n";
}
?>
</Document>
</kml>
