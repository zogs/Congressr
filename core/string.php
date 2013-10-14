<?php 

class String {


	static function random($length = 10){

		return substr(str_shuffle(MD5(microtime())), 0, $length);

	}

	static function directorySeparation($string){

		return str_replace(array('/','\\'), DIRECTORY_SEPARATOR, $string);
	}

	static function slugify($text)
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

	static function findEmailsInString($str){

		$pattern = '/[a-zA-Z0-9\._%+-]+@[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,4}/';
	 	$mail = preg_match_all($pattern, $str,$matches);
	 	return $matches[0];
	}
} ?>