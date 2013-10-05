<?php

  require_once 'cache.php';
  
  require_once 'api/TwitterAPIExchange.php';
  
  if($_POST['busqueda']==1) $ocultarBusqueda=true;
  else $ocultarBusqueda=false;  
  
?>
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8"/>
		<title>En Cobán</title>
		<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.css" type="text/css"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script type="text/javascript" src="/js/jquery.js"></script>
		<script src="/js/jquerymobile.js" type="text/javascript"></script>
		<style>
			.keys {
				margin:5px 0 5px 0;
			}
		</style>
	</head>
<body>
<div data-role="page" data-theme="b" id="principal">
	<div data-role="header">
			<h1>Sistema de alertas</h1>
	</div>
	<div data-role="content">
		<div>
		<div data-role="collapsible" data-collapsed="<?php echo $ocultarBusqueda ?>"> 
		<h1>Búsqueda</h1>
<form method="post">
	<fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
	<legend><b>Diagnosticar</b></legend>
	<p>Selecciona el tipo de desastre a buscar</p>
	<input type="radio" name="tipodesastre" id="tipo1" value="terremoto"/>
	<label for="tipo1">Terremoto</label>
	<input type="radio" name="tipodesastre" id="tipo2" value="inundacion"/>
	<label for="tipo2">Inundación</label>
	<input type="radio" name="tipodesastre" id="tipo3" value="erupcion"/>
	<label for="tipo3">Erupción</label>
	</fieldset><br/>
	<fieldset>
		<legend><b>Localizar</b></legend>
		<p>Puedes hacer una búsqueda de un desastre cerca de una ubicación específica</p>
		<label for="lat">Latitud</label>
		<input name="lat" id="lat" value=""/>
		<label for="lon">Longitud</label>
		<input name="lon" id="lon" value=""/>
		<label for="rad">Radio</label>
		<input name="rad" value=""/>
	</fieldset>
	<input type="hidden" value="1" name="busqueda"/>
	<input type="submit" value="Explorar"/>
</form>
</div>  
		</div>
<?php
	if($_POST['busqueda']==1){
	$arrKeyWords = array(
		'terremoto'=>'terremoto',
		'inundacion'=>'inundacion',
		'erupcion'=>'erupcion%20OR%20volcanica'
	);
		
	$tag = $arrKeyWords[$_POST['tipodesastre']];
	
	$lat = $_POST['lat'];
	$lon = $_POST['lon'];
	$rad = $_POST['rad'];
	  
	if($tag!=""){
		$settings1= array(
			'oauth_access_token'=>'{access_token}',
			'oauth_access_token_secret'=>'{token_secret}',
			'consumer_key'=>'{consumer_key}',
			'consumer_secret'=>'{consumer_secret}');
	
		$twittReq = new TwitterAPIExchange($settings1);
		if($lat!=""&$lon!=""&$rad!="")
			$filtroLocal = '&geocode='.$lat.','.$lon.','.$rad.'km';
		$obj = $twittReq->setGetfield('?q='.$tag.$filtroLocal.'&lang=es&count=100')->buildOauth('https://api.twitter.com/1.1/search/tweets.json','GET')->performRequest();
		$arrResults5 = json_decode($obj);  
	
		cacheDB::guardarCache($arrResults5,$tagTweet);
	 
		if($arrResults5){
			echo '<div data-role="collapsible" data-collapsed="false">';
			echo "<h1>tweets</h1>";
			echo '<table data-role="table" data-mode="columntoggle" id="my-table" class="ui-body-d table-stripe">';
			echo '<thead class="ui-bar-d"><tr><th>Usuario</th><th data-priority="2">Fecha</th><th data-priority="1">Ubicación</th><th>Tweet</th><th>Accion</th></tr></thead>';
			echo '<tbody>';
			$arrTweets = $arrResults5->statuses;
			$arrCoors = array();
			foreach($arrTweets as $tweet){
				$fecha = date_create($tweet->created_at);
				if($tweet->geo){
					$geo_ = '<a href="http://maps.google.com/maps?q='.$tweet->geo->coordinates[0].','.$tweet->geo->coordinates[1].'">ubicacion</a>';
					array_push($arrCoors,array($tweet->geo->coordinates[0],$tweet->geo->coordinates[1]));
				}else {
					$geo_='';
				}
				$pos_ = ($tweet->user->location) ? $tweet->user->location : ''; 
				echo '<tr><td><img src="'.$tweet->user->profile_image_url.'"/></td><td>'.date_format($fecha,'d M').'</td><td>'.$pos_.'</td><td>'.$tweet->text.'</td><td><a target="_blank" href="http://twitter.com/home?status=@'.$tweet->user->screen_name.', si fue afectado por un desastre esta información puede ser de utilidad http://goo.gl/LPYwWX">Enviar</a></td>'; 
	  		}
			echo '</tbody>';
	  		echo "</table>";
	  		echo "</div>";
		}
	}
    }
?>

</div>
</div>

</body>
</html>