<?php
if (rex_get('osmtype', 'string')) {
	
	if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != $_SERVER['SERVER_NAME'])
{
	die();
}	


	
$type = rex_get('osmtype', 'string');		
$dir = $this->getDataPath();
foreach (glob($dir."*") as $file) {
if(time() - filectime($file) > 3600){
    unlink($file); 
    }
}
	rex_response::cleanOutputBuffers();
    $ttl = 86400; //cache timeout in seconds
    $x = intval($_GET['x']);
    $y = intval($_GET['y']);
    $z = intval($_GET['z']);
    $r = 'mapnik';
    $file = $this->getDataPath()."/${z}_${x}_$y.png";
    if (!is_file($file) || filemtime($file)<time()-(86400*30))
    {
	  $server = array();
      if ($type == 'german')
	  {	  
	  $server[] = 'a.tile.openstreetmap.de/tiles/osmde/';
      $server[] = 'b.tile.openstreetmap.de/tiles/osmde/';
      $server[] = 'c.tile.openstreetmap.de/tiles/osmde/';
	  }
	  
	  else 
	  {
		  $server[] = 'a.tile.openstreetmap.org/';
          $server[] = 'b.tile.openstreetmap.org/';
          $server[] = 'c.tile.openstreetmap.org/';
	  }
		  $url = 'https://'.$server[array_rand($server)];
          $url .= $z."/".$x."/".$y.".png";
      $ch = curl_init($url);
      $fp = fopen($file, "w");
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      curl_close($ch);
      fflush($fp);    // need to insert this line for proper output when tile is first requested
      fclose($fp);
    }
    $exp_gmt = gmdate("D, d M Y H:i:s", time() + $ttl * 60) ." GMT";
    $mod_gmt = gmdate("D, d M Y H:i:s", filemtime($file)) ." GMT";
    header("Expires: " . $exp_gmt);
    header("Last-Modified: " . $mod_gmt);
    header("Cache-Control: public, max-age=" . $ttl * 60);
    header ('Content-Type: image/png');
    readfile($file);
}	
	
?>