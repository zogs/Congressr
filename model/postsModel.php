<?php
class PostsModel extends Model{


	public $validates = array(
		'name' => array(
			'rule'    => 'notEmpty',
			'message' => 'Vous devez préciser un titre'		
		),
		'slug' => array(
			'rule' => '([a-z0-9\-]+)',
			'message' => "L'url n'est pas valide"
			)
	);

}
?>