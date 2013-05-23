<?php 
class zHandlingErrors {

	public static function handler($Exception){

		if(DEBUG==0){
			$controller = new Controller(false,false);
			$controller->e404('un pti bout de code est surement mal écrit...','Oups une erreur !');
		}

		if(DEBUG==1){
			
		}

		if(DEBUG==2){
			
			self::display($Exception);
		}

		
	}

	public static function display($e){

		echo '<pre>';
		print_r($e);
		echo '</pre>';
		
		// $error = new stdClass();
		// $error->msg = $e->getMessage();
		// $error->code = $e->getCode();
		// $error->line = $e->getLine();
		// $error->file = $e->getFile();
		// $error->context = $e->getTraceAsString();

		// $controller = new Controller(false,false);
		// $controller->exception($error);


	}
}

class zErrorException extends ErrorException {



	public function __construct($errno, $errstr, $errfile, $errline){
		
		$this->message = $errstr;
		$this->code = $errno;
		$this->line = $errline;
		$this->file = $errfile;
		zHandlingErrors::handler($this);
	}


}

class zException extends Exception {

	public function __construct($msg='Errors class default message' ,$code = 0, Exception $previous = NULL){

		$this->message = $msg;
		$this->code = $code;
		$this->previous = $previous;
		zHandlingErrors::handler($this);
			
	}

} 



?>