<?php 
class articlesModel extends Model {

	public $validates = array(
		'resume'=> array(
			'title'=>array(
				'rule'=>'notEmpty',
				'message' => 'Veuillez remplir un titre'),
			'text'=>array(
				'rule'=>'notEmpty',
				'message'=>'Veuillez remplir le contenu de votre résumé'),
			'tags'=>array(
				'rule'=>'notEmpty',
				'message'=>'Veuillez remplir au moins un mot clef'),
			'author1_firstname'=>array(
				'rule'=>'notEmpty',
				'message'=>'Veuillez remplir au moins un auteur'),
			'author1_lastname'=>array(
				'rule'=>'notEmpty',
				'message'=>'Veuillez remplir au moins un auteur'),
			'author1_institution'=>array(
				'rule'=>'notEmpty',
				'message'=>'Veuillez remplir au moins un auteur'),
			)
		);

	public function saveResume( $data ) {


		$resume = new stdClass();
		$resume->table   = 'resume';
		$resume->title   = $data->title;
		$resume->text    = $data->text;
		$resume->tags    = $data->tags;
		$resume->prefer  = $data->prefer;
		$resume->user_id = $data->user_id;
		if(isset($data->status)) $resume->status = $data->status;

		if(!empty($data->id)) {
			$resume->id = $data->id;
			$resume->key = 'id';
		}

		//save resume
		$id = $resume_id = $this->save( $resume );		

		//save authors
		$this->saveAuthors( $data, $resume_id, 'resume' );

		return $id;
	}

	public function updateArticleStatus($id,$type,$status){

		$sql = "UPDATE $type SET status='$status' WHERE id=$id";
		return $this->query($sql);

	}
	public function findArticleTypeID($type,$id){

		if($type=='resume'){
			$r = $this->findResumes(array('conditions'=>array('id'=>$id)));
		}

		return $r[0];
	}
	public function findAssignmentByUser($user_id,$type){

		return $this->find(array('table'=>'assignment','conditions'=>array('user_id'=>$user_id,'type'=>$type)));
	}
	public function ifReviewerIsAssign($id,$user_id,$type){

		$r = $this->findFirst(array('table'=>'assignment','conditions'=>array('user_id'=>$user_id,'article_id'=>$id,'type'=>$type)));
		
		if(!empty($r)) return true;
		else return false;
	}

	public function findResumes( $options = array() ){

		$params = array('table'=>'resume');
		$params = array_merge($params,$options);
		$res = $this->find($params);

		$a = array();
		foreach ($res as $r) {
			
			$a[] = new Resume($r);		}

		return $a;
	}

	public function joinAssignment($articles){
		if(is_object($articles)) $articles = array($articles);

		foreach ($articles as $key => $article) {
			$article->reviewer_id = 0;
			$articles[$key] = $this->JOIN('assignment','user_id as reviewer_id',array('article_id'=>':id'),$article);
		}

		return $articles;
	}
	public function joinReviews($articles, $type){

		if(is_object($articles)) $articles = array($articles);

		foreach ($articles as $key => $article) {
			
			$articles[$key]  = $this->JOIN('reviewed','note, comment, prefer',array('article_id'=>':id','article_type'=>$type),$article);
		}
		return $articles;
	}

	public function findArticles( $options = array() ){

		$params = array('table'=>'article');
		$params = array_merge($params,$options);
		$c = $this->find($params);

		return $c;
	}
	public function findAuthors( $id, $type ){

		$authors = $this->find(array('table'=>'authors','conditions'=>array('id_article'=>$id,'type'=>$type)));
		$authors = $this->JOIN('author','',array('id'=>':id_author'),$authors);
		foreach ($authors as $key => $author) {
			
			$authors[$key] = new Author( $author );
		}
		return $authors;

	}

	public function saveReview($article_id,$reviewer_id,$article_type,$data){

		$n = new stdClass();
		$n->table='reviewed';
		$n->article_id = $article_id;
		$n->reviewer_id = $reviewer_id;
		$n->article_type = $article_type;
		$n->note = $data->note;
		$n->prefer = $data->prefer;
		$n->comment = $data->comment;

		//if exist
		if($exist = $this->findFirst(array('table'=>'reviewed','conditions'=>array('reviewer_id'=>$reviewer_id,'article_id'=>$article_id,'article_type'=>$article_type),'fields'=>'id'))){
			$n->id = $exist->id;
			$n->key = 'id';
		}
		//save it
		if($id = $this->save($n)){

			//udpate article status
			$sql = 'UPDATE '.$article_type.' SET status="reviewed" WHERE id='.$article_id; 
			$this->query($sql);

			return true;
		}
		return false;

	}

	public function saveAuthors ( $data, $article_id, $type ){

		$authors = array();
		foreach ($data as $key => $value) {							
			
			if( preg_match('/author([0-9]+)_([a-z]+)/', $key, $matches )){

				$authors[$matches[1]][$matches[2]] = $value;				
			}
		}

		foreach ($authors as $attributes) {
			
			$author = new stdClass();
			$author->table = 'author';

			foreach ($attributes as $key => $value) {
				
				$author->$key = $value;				
				
			}

			// if ID is given, do an update
			if(isset($author->id)&&$author_id!=0) $author->key = 'id'; 
				
			//Save author			
			$author_id = $this->save( $author );

			//Save author/article
			if(isset($author->id) && $this->authorIsArticleAuthor($author_id,$article_id)) continue; //if author is already author for this article , dont save it and jump to the next author
			$authors = new stdClass();
			$authors->table = 'authors';
			$authors->id_article = $article_id;
			$authors->type = $type;
			$authors->id_author = $author_id;

			$this->save( $authors );
			

		}
		return true;	
	}

	public function authorIsArticleAuthor($author_id,$article_id){

		$a = $this->findFirst(array('table'=>'authors','conditions'=>array('id_article'=>$article_id,'id_author'=>$author_id)));
		if(!empty($a)) return true;
		return false;
	}
} 


class Resume {

	public $id     = null;
	public $title  = '';
	public $text   = '';
	public $tags   = '';
	public $prefer = '';
	public $note = 0;
	public $comment = '';

	public function __construct( $fields = array() ){

		foreach ($fields as $key => $value) {
			$this->$key = $value;
		}
	}

	public function getAverageNote(){
		return $this->note;
	}

	public function getCommPrefered(){
		if(!isset($this->prefer)) return 'oral';
		return $this->prefer;
	}

}


class Author {

	public $id     = 0;
	public $firstname = '';
	public $lastname = '';
	public $institution = '';

	public function __construct( $fields = array() ){

		foreach ($fields as $key => $value) {
			$this->$key = $value;
		}
	}


}




?>