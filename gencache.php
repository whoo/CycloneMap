<?
header ("content-type: text/xml");

chdir(dirname($_SERVER['SCRIPT_FILENAME']));
$tb=parse_ini_file('config.php',true);
$CACHEFILE=(isset($tb['FILE']['cachefile']))?$tb['FILE']['cachefile']:0;

function getURL($BASE,$debug=0)
{
	if (!$debug) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$BASE."/index.asp");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		$res=curl_exec($ch); 
	} 
	else
		$res=file_get_contents('origin.html');

	$pattern = '/(storm.asp\?storm_identifier=.*)"/';
	$name = '/storm.asp\?storm_identifier=.*">(.*)/';
	preg_match_all($pattern, $res, $matches);
	preg_match_all($name, $res, $name);

	$url=$matches[1];
	$name=$name[1];

	foreach ($url as $key => $val)
	{
		$rep[$key]['url']=chop($val);
		$rep[$key]['name']=htmlentities(chop($name[$key]));
	}
	return $rep;
}

function getxml($url,$name)
{
	//echo "$name\n";
        $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	$res=curl_exec($ch);

	$array=explode("\r",$res);
	$tb=0;
	$TABLE="";
	foreach($array as $a)
	{

	//	if (preg_match('/<h2>(.*)<\/h2>/',$a,$m)) {$name=$m[1];}
		if ($tb==1) $TABLE.=$a;
		if (preg_match('/Time of Latest Forecast/',$a,$matches))  $tb=1;
		if (preg_match('/<\/table>/',$a,$matches))  $tb=0;

if (preg_match('@products/.*4KMIRIMG/(.*\.GIF)@',$a,$matches)) $URL=$matches[0];
	}
	$forcast=$TABLE;


	$tb=0;
	$TABLE="";
	foreach($array as $a)
	{
		if ($tb==1) $TABLE.=$a;
		if (preg_match('/<h3>Track History/',$a,$matches))  {$tb=1;  }
		if (preg_match('/<\/table>/',$a,$matches))  {$tb=0;  }
	}
	$status=$TABLE;


	$forcast= preg_replace("/table/","forecast",$forcast);
	$forcast= preg_replace("/tr/","plot",$forcast);
	$forcast= preg_replace("/td/","value",$forcast);

	$status= preg_replace("/td/","value",$status);
	$status= preg_replace("/table/","history",$status);
	$status= preg_replace("/tr/","plot",$status);
	$str="<all><info><name>$name</name></info>$forcast"."$status</all>";
	$Xml=simplexml_load_string($str);

	$Out = simplexml_load_string("<cyclone> <info><name>$name</name><url>$url</url><img>".BASEURL."/$URL</img></info></cyclone>");
	{
		$tb=null;
	if ($Xml->forecast->plot[0]->value)
		foreach ($Xml->forecast->plot[0]->value as $legend) { $tb[]=strtolower((string)$legend); }
		$tb[0]="time";
		$Forecast = $Out->addChild('forecast');
		$id=0;

	if ($Xml->forecast->plot[0]->value)
		foreach ($Xml->forecast->plot as $plot)
		{
			if ($id>1)      {
				$idx=0;
				$Pt=$Forecast->addChild('plot');
				foreach( $plot as $leg) { $Pt->addChild($tb[$idx],$leg); $idx++; }
			}
			$id++;
		}


		$tb=null;
		foreach ($Xml->history->plot[0]->value as $legend) { $tb[]=strtolower((string)$legend); }
		$tb[0]="time";
		$Forecast = $Out->addChild('history');
		$id=0;
		foreach ($Xml->history->plot as $plot)
		{
			if ($id>1)      {
				$idx=0;
				$Pt=$Forecast->addChild('plot');
				foreach( $plot as $leg) { $Pt->addChild($tb[$idx],$leg); $idx++; }
			}
			$id++;
		}
	}

	curl_close($ch);

	return	$Out->asXml();

}


///// MAIN
$BASE="http://rammb.cira.colostate.edu";
define('BASEURL','http://rammb.cira.colostate.edu/products/tc_realtime/');
$url=getURL(BASEURL,0);



$Out="<all>";
foreach ($url as $key => $u)
{
	$nom= $u['name'];
	$url= $u['url'];
$Out.=getxml(BASEURL.$url,$nom);
}
$Out.="</all>";

$Out=preg_replace("/<\?xml version=\"1.0\"\?>/","",$Out);

$XML=simplexml_load_string($Out);
$XML->asXml($CACHEFILE);
readfile($CACHEFILE);

?>
