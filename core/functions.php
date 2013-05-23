<?php

function debug($var){

	if(DEBUG>=1){
		$debug = debug_backtrace();
		echo '<p>&nbsp;</p><p><a href="#" onclick="$(this).parent().next(\'ol\').slideToggle(); return false;" ><strong>'.$debug[0]['file'].'</strong> Ligne .'.$debug[0]['line'].'</a></p>';
		

		echo '<ol style="display:none">';
		foreach ($debug as $key => $value) {
			if($key>0){
			echo '<li>'.$value['file'].' ligne '.$value['line'].'</li>';
			}
		}
		echo '</ol>';

		echo '<pre>';
		print_r($var);
		echo '</pre>';

	}

}



function day($lang,$day){

	

	$days = array(
		'fr'=>array('Today'=>"Aujourd'hui",'Mon'=>'Lundi','Tue'=>'Mardi','Wed'=>'Mercredi','Thu'=>'Jeudi','Fri'=>'Vendredi','Sat'=>'Samedi','Sun'=>'Dimanche')
		);

	return $days[$lang][$day];

}

function datefr($date) { 

//tableau des mois de l'année en francais
$type_mois['00']='00';
$type_mois['01']='Janvier';   $type_mois['02']='Février';
$type_mois['03']='Mars';      $type_mois['04']='Avril';
$type_mois['05']='Mai';       $type_mois['06']='Juin';
$type_mois['07']='Juillet';   $type_mois['08']='Août';
$type_mois['09']='Septembre'; $type_mois['10']='Octobre';
$type_mois['11']='Novembre';  $type_mois['12']='Décembre';
//on separe la date en jour mois annee
	$split = explode("-",$date); 
	$annee = $split[0]; 
	$num_mois = $split[1]; 
	$jour = $split[2]; 
	//on associe le numero du mois au nom correspondant dans le tableau
	$mois = $type_mois[$num_mois];
	//on retourne la valeur
	return "$jour"." "."$mois"." "."$annee"; 
} 


function ageFromBY($year){

	$age = 0;
	$current = Date('Y');
	for($i=$year; $i<$current; $i++){
		$age++;
	}
	return $age;
}

function make_path($path) {
        //Test if path exist
        if (is_dir($path) || file_exists($path)) return $path;
        //No, create it
        if(mkdir($path, 0777, true))
        	return $path;
        else
        	return false;
}
	
function unescape($obj){


	foreach ($obj as $k => $v) {
		
		if(is_string($v)) {
			$obj->$k = htmlentities($v);
		}

	}
	return $obj;
}
function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
 
    // trim
    $text = trim($text, '-');
 
    // transliterate
    if (function_exists('iconv'))
    {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
 
    // lowercase
    $text = strtolower($text);
 
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
 
    if (empty($text))
    {
        return 'n-a';
    }
 
    return $text;
}

function randomString($length=10){

	return substr(str_shuffle(MD5(microtime())), 0, $length);
}

function unixToMySQL($timestamp)
{
    return date('Y-m-d H:i:s', $timestamp);
}

function make_valid_youtube_embed_code($video_id){

	$html = '<div style="text-align: center; margin: auto">
			<object type="application/x-shockwave-flash" style="width:450px; height:366px;" data="http://www.youtube.com/v/'.$video_id.'">
			<param name="movie" value="http://www.youtube.com/v/'.$video_id.'" />
			<param name="allowFullScreen" value="true" />
			<param name="allowscriptaccess" value="always" />
			</object>
			</div>';
	return $html;
}

function make_valid_vimeo_embed_code($video_id){

	$html = '<div style="text-align: center; margin: auto">
			<object type="application/x-shockwave-flash" style="width:450px; height:366px;" data="http://vimeo.com/moogaloop.swf?clip_id='.$video_id.'&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=ff9933&amp;fullscreen=1" allowfullscreen="true" allowscriptaccess="always">
			<param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id='.$video_id.'&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=ff9933&amp;fullscreen=1" 
			allowfullscreen="true" 
			allowscriptaccess="always" />
			</object>
			</div>';
	return $html;
}

function make_valid_dailymotion_embed_code($video_id){

	$html = '<object width="560" height="315">
			    <param name="movie" value="http://www.dailymotion.com/swf/video/'.$video_id.'?background=493D27&foreground=E8D9AC&highlight=FFFFF0"></param>
			    <param name="allowFullScreen" value="true"></param>
			    <param name="allowScriptAccess" value="always"></param>
			    <embed type="application/x-shockwave-flash" src="http://www.dailymotion.com/swf/video/'.$video_id.'?background=493D27&foreground=E8D9AC&highlight=FFFFF0" width="560" height="315" allowfullscreen="true" allowscriptaccess="always"></embed>
			</object>';
	return $html;
}

function file_get_contents_curl($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'YouProtest.Net (BetaTest) contact:admin@manifeste.info');

	$data = curl_exec($ch);
	$info = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

	//checking mime types
	if(strstr($info,'text/html')) {
		curl_close($ch);
		return $data;
	} else {
		return false;
	}
}


function getUrlDomain($_url, $_extension = false, $_scheme = false){
    

	$parsed_url = parse_url($_url);

	if($_scheme)	
		$domain = $parsed_url['host'];
	else
		$domain = str_replace('www.','',$parsed_url['host']);

	$ext = pathinfo($_url, PATHINFO_EXTENSION);

	$return = '';
	if($_scheme)
		$return = $parsed_url['scheme'].'://';
	
	$return .= $domain;

	if($_extension)
		$return .= $ext;

	return $return;

	}



     /*
    * Retrieve the video ID from a YouTube video URL
    * @param $ytURL The full YouTube URL from which the ID will be extracted
    * @return $ytvID The YouTube video ID string
    */
    function getYTid($ytURL) {
     
    $ytvIDlen = 11; // This is the length of YouTube's video IDs
     
    // The ID string starts after "v=", which is usually right after
    // "youtube.com/watch?" in the URL
    $idStarts = strpos($ytURL, "?v=");
     
    // In case the "v=" is NOT right after the "?" (not likely, but I like to keep my
    // bases covered), it will be after an "&":
    if($idStarts === FALSE)
    $idStarts = strpos($ytURL, "&v=");
    // If still FALSE, URL doesn't have a vid ID
    if($idStarts === FALSE)
    die("YouTube video ID not found. Please double-check your URL.");
     
    // Offset the start location to match the beginning of the ID string
    $idStarts +=3;
     
    // Get the ID string and return it
    $ytvID = substr($ytURL, $idStarts, $ytvIDlen);
     
    return $ytvID;
     
    }

    function getDMid($url){

    	$id = strtok(basename($url), '_');

    	return $id;
    }

    function getVIMEOid($url){

		preg_match('/vimeo\.com\/([0-9]{1,10})/',$url,$matches);
		if(!empty($matches)){
			return $matches[1];
		}
		else return 'wrong vimeo url';
    }
