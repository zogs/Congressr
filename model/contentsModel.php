<?php

class ContentsModel extends Model {
	
	public $table = 'contents';
	public $table_i18n = 'contents_i18n';
	public $key_i18n = 'id_i18n';

	public $validates = array(
		'title' => array(
			'rule'    => 'notEmpty',
			'message' => 'Vous devez préciser un titre'		
		),
		'content'=>array(
			'rule' => 'notEmpty',
			'message' => 'Vous devez remplir le contenu'
			),

	);

	public function findContentById( $id , $lang = '', $fields = ''){

		if(isset($id) && is_numeric($id)){

			$c = $this->findFirst(array('conditions'=>'id='.$id));
			if(empty($c)) {
				return i18nContents( $c , $lang, $fields);
			}
			else return $c;
		}
		else throw new zException('id must be numeric in findCOntentById', 1);
		
	}

	public function findContents($conditions, $lang, $fields = ''){


		$contents = $this->find(array('conditions'=>$conditions));
		$contents = $this->i18nContents( $contents, $lang, $fields);

		return $contents;
	}

	public function findContent($content_id, $lang, $fields = ''){

		if(!isset($content_id)) return false;
		$content = $this->findFirst(array('conditions'=>array('id'=>$content_id)));
		$content = $this->i18nContents($content,$lang,$fields);
		return $content;
	}

	public function findPage($id){

		return $this->findFirst(array('conditions'=>array('id'=>$id,'type'=>'page')));
	}

	public function findPageContent( $page_id, $lang=''){

		if(!isset($page_id)) return false;
		$c = $this->findPage($page_id);
		$c = $this->i18nContents( $c, $lang);
		return $c;
	}

	public function findPages($lang, $fields = ''){

		$pages = $this->find(array('conditions'=>array('type'=>'page')));		
		$pages = $this->i18nContents($pages, $lang, $fields);
		return $pages;
	}

	public function countContent( $type , $lang){

		return $this->findCount('type="'.$type.'"');
	}

	public function i18nContents( $contents, $lang = '' , $fields = ''){

		if(empty($contents)) return false;
		if(empty($lang)) $lang = Conf::$languageDefault;	
		return $this->JOIN($this->table_i18n,'',array('content_id'=>':id','lang'=>$lang),$contents);
	}

	public function i18n($contents, $params){

		if(empty($contents)) return false;
		$conditions = array('content_id'=>':id');
		$params['lang'] = (empty($params['lang']))? Conf::$languageDefault : $params['lang'];	
		$conditions = array_merge($conditions,$params);		
		return $this->JOIN($this->table_i18n,'',$conditions,$contents);
	}
	public function i18nExist( $content_id , $lang){

		$c = $this->findFirst(array('table'=>$this->table_i18n,'fields'=>'id','conditions'=>array('content_id'=>$content_id,'lang'=>$lang)));
		if(!empty($c)) return true;
		else return false;
	}

	public function findi18nContents($content_id,$options = array()){

		$conditions = array('content_id'=>$content_id);
		$conditions = array_merge($conditions,$options);
		return $this->find(array('table'=>$this->table_i18n,'conditions'=>$conditions));
	}

	public function saveContent($content){

		
		//Champs des tables
		$content_fields = array('id','position','type','date','online');
		$i18n_fields = array('id_i18n','content_id','lang','content','title','date','valid','slug');
		
		$c = new stdClass();

		//Si des données correspondent aux champs de la table contents
		foreach ($content_fields as $key) {
			
			if(!empty($content->$key)) $c->$key = $content->$key;
		}

		//Traduction à sauvegarder
		$c_i18n = new stdClass();
		$c_i18n->table = $this->table_i18n;
		$c_i18n->key = 'id_i18n';

		//Si des données correspondent aux champs de la table contents_i18n
		foreach ($i18n_fields as $key) {
			
			if(isset($content->$key)) $c_i18n->$key = $content->$key;
		}

		//On sauvegarde le contenu
		 if(!$this->save($c)) throw new zException("Error saving content", 1);
		 //On récupére d'id du contenu
		 if(isset($this->id) && !empty($this->id)&&is_numeric($this->id)) $c_i18n->content_id = $this->id;
		 //On sauvegarde la traduction
		 if(!$this->save($c_i18n)) throw new zException("Error saving i18n content", 1);
		 		
		return true;


	}

	public function deleteContent($id){

		if($this->delete($id)) return true;
		else return false;
	}

	public function deletei18nContents($i18ns){

		if(!is_array($i18ns)) $i18ns = array($i18ns);
		foreach ($i18ns as $i18n) {
					$primaryKey = $this->key_i18n;
					$i18n->table = $this->table_i18n;
					$i18n->key = $primaryKey;
					$i18n->id = $i18n->$primaryKey;
					$this->delete($i18n);
		}
		return true;
	}
}
?>