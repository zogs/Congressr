<?php 
class Cache {

	public $dirname;
	public $duration;
	public $buffer;

	public function __construct($dirname,$duration){

		$this->dirname = $dirname;
		$this->duration = $duration;

	}	

	public function write($filename, $content){
		
		$file = $this->path($this->dirname.'/'.$filename);

		return $this->file_force_contents($file, utf8_encode($content)); //Ecrit fichier et dossier 
	}

	public function read($filename){

		$file = $this->path($this->dirname.'/'.$filename);

		if(!file_exists($file)){
			return false;
		}

		$lifetime = (time() - filemtime($file)) / 60;
		if($lifetime > $this->duration){
			return false;
		}
		return utf8_decode(file_get_contents($file));
	}

	public function delete($filename){

		$file = $this->path($this->dirname.'/'.$filename);
		if(file_exists($file)){
			unlink($file);
			
		}
	}

	public function clear(){

		$files =  glob($this->path($this->dirname.'/*'));
		foreach ($files as $file) {
			unlink($file);
		}
	}

	public function inc($file, $cachename = null){
		if(!$cachename){
			$cachename = basename($file);
		}

		if($content = $this->read($this->path($this->dirname.'/'.$cachename))){
			echo $content;
			return true;
		}

		ob_start();
		require $file;
		$content = ob_get_clean();
		$this->write($cachename, $content);
		echo $content;
		return true;
	}

	public function start($cachename){

		if($content = $this->read($cachename)){
			echo $content;
			$this->buffer = false;
			return true;
		}
		ob_start();
		$this->buffer = $cachename;
	}

	public function end(){

		if(!$this->buffer){
			return false;
		}
		$content = ob_get_clean();
		echo $content;
		$this->write($this->buffer, $content);
	}


	private function path($file){

		return $file = (DIRECTORY_SEPARATOR === '\\')? str_replace('/','\\',$file) : str_replace('\\','/',$file);
	}


 	private function file_force_contents($dir, $contents){
        	
        $parts = explode(DIRECTORY_SEPARATOR, $dir);
        $file = array_pop($parts);
        if(strpos($dir,DIRECTORY_SEPARATOR)===0) 
        	$dir = '/';
        else
        	$dir = '';
        foreach($parts as $part){
         
            if(!is_dir($dir .= $part)) mkdir($dir);
            $dir .= DIRECTORY_SEPARATOR;
        }
        file_put_contents("$dir".DIRECTORY_SEPARATOR."$file", $contents);
    }



} ?>