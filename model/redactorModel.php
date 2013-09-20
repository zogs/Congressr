<?php 
class RedactorModel extends Model {

}

class Author {

	public $firstname = '';
	public $lastname = '';
	public $institution = '';
	public $job = '';
	public $email = '';

	public function __construct( $fields = array() ){

		foreach ($fields as $key => $value) {
			$this->$key = $value;
		}
	}
}

?>