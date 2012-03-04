<?
$tb=parse_ini_file('config.php',true);
$lat =(isset($tb['ORIGIN']['lat']))?$tb['ORIGIN']['lat']:0;
$lng =(isset($tb['ORIGIN']['lng']))?$tb['ORIGIN']['lng']:0;
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<title>Cyclone Map from RAMMB </title>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"> </script>


<style type="text/css">
	html { height: 100% }
	body { height: 100%; margin: 0px; padding: 0px }
	#map_canvas { height: 100% }
</style>


<script type="text/javascript">
function initialize() {
	var mauritius = new google.maps.LatLng(<?=$lat?>,<?=$lng?>);
	var myOptions = {
	zoom: 4,
      	center: mauritius,
      	mapTypeId: google.maps.MapTypeId.ROADMAP
	}

	var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	var ctaLayer = new google.maps.KmlLayer('exportkml.php?<?=rand();?>',{preserveViewport: true});
	ctaLayer.setMap(map);
}

</script>
</head>

<body onload="initialize()">
<div id="map_canvas" style="width: 100%; height: 100%"></div>
</body>
</html>
