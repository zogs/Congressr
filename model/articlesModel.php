<?php 
class ArticlesModel extends Model {

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
		,
		'deposit'=>array(
			'deposed'=>array(
					'rule'=>'file',
					'params'=>array(
						'destination'=>'media/deposed',
						'extentions'=>array('doc','docx'),
						'extentions_error'=>'Your document is not a .doc file',
						'max_size'=>15000000,
						'max_size_error'=>'Your document is too big',
						'ban_php_code'=>true
						),
					)
				)
		);


	

	public function deleteArticle($type,$id){
		if($type=='resume') $this->deleteResume($id);
		if($type=='deposed') $this->deleteDeposed($id);
	}

	public function deleteResume($id){

		$sql = "DELETE FROM resume WHERE id = :id";
		$val = array(':id'=>$id);

		$this->query($sql,$val);
	}

	public function deleteDeposed($id){

		$sql= "DELETE FROM deposed WHERE id= :id";
		$val = array(':id'=>$id);

		$this->query($sql,$val);
	}

	public function cancelArticle($type,$id){
		if($type=='resume') $this->cancelResume($id);
		if($type=='deposed') $this->cancelDeposed($id);
	}


	public function deleteAssignement($id){
		$sql= "DELETE FROM assignment WHERE id= :id";
		$val = array(':id'=>$id);
		$this->query($sql,$val);
	}

	public function cancelResume($id){

		$sql = "UPDATE resume SET status='canceled' WHERE id = :id";
		$val = array(':id'=>$id);
		$this->query($sql,$val);
	}

	public function cancelDeposed($id){

		$sql= "UPDATE deposed SET status='canceled' WHERE id= :id";
		$val = array(':id'=>$id);
		$this->query($sql,$val);
	}


	public function saveArticle($data,$type){

		if($type=='resume')
			return $this->saveResume($data);

		if($type=='extended')
			return $this->saveExtended($data);

		return false;
	}

	public function saveResume( $data ) {


		$resume = new stdClass();
		$resume->table   = 'resume';
		$resume->title   = $data->title;
		$resume->text    = $data->text;
		$resume->tags    = $data->tags;
		$resume->comm_type  = $data->prefer;
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

	public function saveExtended($data){

		$extended = new stdClass();
		$extended->table = 'extended';
		$extended->resume_id = $data->resume_id;
		$extended->content = $data->content;
		$extended->status = $data->status;

		if(!empty($data->id)){
			$extended->id = $data->id;
			$extended->key = 'id';
		}

		debug($extended);
		if($id = $this->save($extended)){

			$data->extended_id = $id;
			$this->saveFigures($data);

			return $extended->resume_id;
		}

	}

	public function saveFigures($data){

		$destdir = 'media/figures';

		if(!empty($_POST['figure_number'])){

			$number = $_POST['figure_number'];

			if(!empty($_FILES)){

			for($count=0;$count<=$number;$count++){

				if(!empty($_FILES['figure_'.$count])){

					$file = $_FILES['figure_'.$count];

					if($file['error'] == 'UPLOAD_ERR_OK'){
					$destname = 'resume'.$data->resume_id.'_'.$count.'_'.$file['name'];				
					$destination = $destdir.DIRECTORY_SEPARATOR.$destname;
					$destination = String::directorySeparation($destination);

					if(move_uploaded_file($file['tmp_name'], $destination)){

						//save figures
						$figure = new stdClass();
						$figure->resume_id = $data->resume_id;
						$figure->extended_id = $data->extended_id;
						$figure->caption = $_POST['caption_'.$count];
						$figure->number = $count;
						$figure->name = $destname;
						$figure->path = $destination;
						$figure->extension = substr(strrchr($file['name'], '.'),1);
						$figure->table = 'figures';

						if(isset($_POST['figure_id_'.$count])){
							$figure->id = $_POST['figure_id_'.$count];
							$figure->key = 'id';
						}
					
						if($this->save($figure))
							return $destination;
						else
							Session::setFlash('Erreur lors de la sauvegarde de la figure '.$count,'error');
					}
					else {
						throw new zException("Impossible to move the uploaded file", 1);
						
					}
				}
				}
			}		
 		}
		}
		
	}

	public function updateArticleStatus($id,$type,$status){

		//do not update "reviewed" if all reviewer have not review the article
		if($status=='reviewed'){
			$assigned = $this->findAssignmentByArticle($id,$type);
			$reviewed = $this->findReviewByArticle($id,$type);		
			if(count($assigned)!=count($reviewed)) return false;
		}

		$sql = "UPDATE $type SET status='$status' WHERE id=$id";					
		if($type=='deposed') $sql = "UPDATE $type SET status='$status' WHERE resume_id=$id";
		
		
		if($this->query($sql))
			return true;
		return false;

	}

	public function updatedArticleCommunication($id,$type){

		$reviewed = $this->findReviewByArticle($id,$type);
		$com = 'poster'; //default value
		$oral = 0;
		$poster = 0;
		foreach ($reviewed as $r) {
			
			if($r->comm_type=='oral') $oral++;
			if($r->comm_type=='poster') $poster++;
		}

		if($oral > $poster) $com = 'oral';
		if($poster > $oral) $com = 'poster';

		$sql = "UPDATE deposed SET comm_type='$com' WHERE resume_id=$id";

		if($this->query($sql))
			return $com;
		return false;
	}
	public function findArticleTypeID($type,$id){

		if($type=='resume'){
			$r = $this->findResumes(array('conditions'=>array('id'=>$id)));
		}

		if($type=='extended')
			$r = $this->findExtended(array('conditions'=>array('id'=>$id)));	

		if($type=='deposed')
			$r = $this->findDeposed(array('conditions'=>array('resume_id'=>$id)));		

		return $r[0];
	}
	public function findReviewByArticle($article_id,$type){

		return $this->find(array('table'=>'reviewed','conditions'=>array('article_id'=>$article_id,'article_type'=>$type)));
	}


	public function findAssignmentByUser($user_id,$type){

		return $this->find(array('table'=>'assignment','conditions'=>array('user_id'=>$user_id,'type'=>$type)));
	}
	public function findAssignmentByArticle($article_id,$type){

		return $this->find(array('table'=>'assignment','conditions'=>array('article_id'=>$article_id,'type'=>$type)));
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
			
			if(!empty($r)) $a[] = new Resume($r);
		}

		return $a;
	}

	public function findResumesAccepted()
	{
		return $this->findResumes(array('conditions'=>array('status'=>'accepted')));
	}

	public function findResumesRefused()
	{
		return $this->findResumes(array('conditions'=>array('status'=>'refused')));
	}

	public function findResumesPending()
	{
		return $this->findResumes(array('conditions'=>array('status'=>'pending')));
	}

	public function findResumesCanceled()
	{
		return $this->findResumes(array('conditions'=>array('status'=>'canceled')));
	}

	public function findExtended($options = array()){

		$params = array('table'=>'extended');
		$params = array_merge($params,$options);
		$res = $this->find($params);
		$a = array();
		foreach ($res as $r) {
			$a[] = new Extended($r);
		}
		return $a;
	}

	public function findDeposed($options = array()){

		$params = array('table'=>'deposed');
		$params = array_merge($params,$options);
		$res = $this->find($params);
		$a = array();
		foreach ($res as $r) {
			if(!empty($r)) $a[] = new Deposed($r);
		}
		return $a;
	}

	public function findDeposedByResumeId($resume_id){

		$params = array('table'=>'deposed','conditions'=>array('resume_id'=>$resume_id));
		$res = $this->findFirst($params);
		$res = new Deposed($res);
		return $res;
	}

	public function joinAssignments($articles,$type = 'resume'){
		
		foreach ($articles as $key => $article) {			
			$article->assigned = $this->findAssignment($article->id,$type);
		}
		
		return $articles;

	}

	public function findAssignment($article_id,$type){

		return  $this->find(array('table'=>'assignment','conditions'=>array('article_id'=>$article_id,'type'=>$type)));

	}
	public function joinReviews($articles, $type){

		foreach ($articles as $key => $article) {
			
			$this->joinReview($article,$type);
		}

		return $articles;
		
	}

	public function joinReview($article,$type){
		
		if($type=='resume')
			$article->reviewed = $this->find(array('table'=>'reviewed','conditions'=>array('article_id'=>$article->id,'article_type'=>$type)));
		if($type=='deposed')
			$article->reviewed = $this->find(array('table'=>'reviewed','conditions'=>array('article_id'=>$article->resume_id,'article_type'=>$type)));
		return $article;
	}

	public function joinFigures($article){

		$article->figures = $this->find(array('table'=>'figures','conditions'=>array('resume_id'=>$article->resume_id)));

		return $article;
	}

	public function findAuthors( $id, $type ){

		$authors = $this->find(array('table'=>'authors','conditions'=>array('id_article'=>$id,'type'=>$type)));
		$authors = $this->JOIN('author','',array('id'=>':id_author'),$authors);
		foreach ($authors as $key => $author) {
			
			$authors[$key] = new Author( $author );
		}
		return $authors;
	}

	public function joinAuthors($articles,$type="resume",$key="id"){

		foreach ($articles as $article) {
			
			$authors = $this->findAuthors($article->$key,$type);
			if(!empty($authors)) $article->authors = $authors;
			else $article->authors = array(new Author);
		}
		
		return $articles;
	}

	public function saveReview($article_id,$reviewer_id,$article_type,$data){

		$n = new stdClass();
		$n->table='reviewed';
		$n->article_id = $article_id;
		$n->reviewer_id = $reviewer_id;
		$n->article_type = $article_type;
		$n->note = $data->note;
		$n->comm_type = $data->prefer;
		$n->comment = $data->comment;

		//if exist
		if($exist = $this->findFirst(array('table'=>'reviewed','conditions'=>array('reviewer_id'=>$reviewer_id,'article_id'=>$article_id,'article_type'=>$article_type),'fields'=>'id'))){
			$n->id = $exist->id;
			$n->key = 'id';
		}
		//save it
		if($id = $this->save($n)){

			return true;
		}
		return false;

	}

	public function saveAssignment($type,$id,$reviewer_id){

		$check = $this->findFirst(array('table'=>'assignment','conditions'=>array('type'=>$type,'user_id'=>$reviewer_id,'article_id'=>$id)));
		if(!empty($check)) return false;	


		$n = new stdClass();
		$n->table = 'assignment';
		$n->type = $type;
		$n->user_id = $reviewer_id;
		$n->article_id = $id;
		$n->date = Date::MysqlNow();

		if($this->save($n)){

			$this->updateArticleStatus($id,$type,'pending');

			return true;
			
		}
		else return false;
	}

	public function isNotAFirstAuthor($data){

		if(!empty($data->id)) return true;

		$a = $this->findFirst(array('table'=>'author','conditions'=>array('firstname'=>$data->author1_firstname,'lastname'=>$data->author1_lastname)));

		if(empty($a)) return true;

		$f = $this->findFirst(array('table'=>'authors','conditions'=>array('id_author'=>$a->id,'no'=>1)));

		if(empty($f)) return true;

		return false;
	}

	public function saveAuthors ( $data, $article_id, $type ){

		$authors = array();
		foreach ($data as $key => $value) {							
			
			if( preg_match('/author([0-9]+)_([a-z]+)/', $key, $matches )){

				$authors[$matches[1]][$matches[2]] = $value;				
			}
		}

		foreach ($authors as $i => $attributes) {
			
			$author = new stdClass();
			$author->table = 'author';

			foreach ($attributes as $key => $value) {
				
				$author->$key = $value;				
				
			}

			// if ID is given, do an update
			if(isset($author->id)&&$author->id!=0) $author->key = 'id'; 
				
			//Save author			
			$author_id = $this->save( $author );

			//Save author/article
			if(isset($author->id) && $this->authorIsArticleAuthor($author_id,$article_id)) continue; //if author is already author for this article , dont save it and jump to the next author
			$authors = new stdClass();
			$authors->table = 'authors';
			$authors->id_article = $article_id;
			$authors->type = $type;
			$authors->id_author = $author_id;
			$authors->no = $i;

			$this->save( $authors );
			

		}
		return true;	
	}

	public function authorIsArticleAuthor($author_id,$article_id){

		$a = $this->findFirst(array('table'=>'authors','conditions'=>array('id_article'=>$article_id,'id_author'=>$author_id)));
		if(!empty($a)) return true;
		return false;
	}

	public function saveMailingContent($data,$lang){

		$check = $this->findFirst(array('table'=>'mailing',"fields"=>"id",'conditions'=>array('lang'=>$lang,'article'=>$data->article,'result'=>$data->result,'comm_type'=>$data->comm_type)));

		$save = new stdClass();
		$save->table = 'mailing';
		$save->key = 'id';
		if(!empty($check->id)) $save->id = $check->id;
		$save->lang = $lang;
		$save->article = $data->article;
		$save->result = $data->result;
		$save->content = $data->content;
		$save->comm_type = $data->comm_type;

		if($this->save($save)) return true;
		return false;
	}

	public function setResumeMailed($resume_id){

		$save = new stdClass();
		$save->table ='resume';
		$save->key = 'id';
		$save->id = $resume_id;
		$save->mailed = 1;

		if($this->save($save)) return true;
		return false;
	}

	public function setArticleMailed($id){

		$save = new stdClass();
		$save->table ='deposed';
		$save->key = 'id';
		$save->id = $id;
		$save->mailed = 1;

		if($this->save($save)) return true;
		return false;
	}
} 


class Resume extends Article {

	public $id     = null;
	public $title  = '';
	public $text   = '';
	public $tags   = '';
	public $comm_type = 'oral';
	public $note = 0;
	public $comment = '';

	public function __construct( $fields = array() ){

		foreach ($fields as $key => $value) {
			$this->$key = $value;
		}
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
class Article {

	public function getAverageNote(){

		$total = 0;
		$nb = 0;
		if(!empty($this->reviewed)){
			if(is_array($this->reviewed)){
				foreach ($this->reviewed as $review) {
					$total += $review->note;
					$nb ++;					
				}

				return $total/$nb;
			}
			return 'not array';
		}
		return 0;
	}

	public function getNoteByReviewer($rid){
		if(!empty($this->reviewed)){
			foreach ($this->reviewed as $r) {
				if(isset($r->reviewer_id) && $r->reviewer_id==$rid) return $r->note;
			}
		}
		return 0;
	}

	public function getCommentByReviewer($rid){
		if(!empty($this->reviewed)){
			foreach ($this->reviewed as $r) {
				if(isset($r->reviewer_id) && $r->reviewer_id==$rid) return $r->comment;
			}
		}
		return '';
	}

	public function getCommPreferedByReviewer($rid){
		if(!empty($this->reviewed)){
			foreach ($this->reviewed as $r) {
				if(isset($r->reviewer_id) && $r->reviewer_id==$rid) return $r->comm_type;
			}
		}
		return '';
	}

	public function getCommPrefered(){
		if(!empty($this->comm_type)) return $this->comm_type;
		return '';
	}
}

class Deposed extends Article {
	public $id = '';
	public $resume_id = '';

	public function __construct( $fields = array() ){

		if(empty($fields)) return;
		foreach ($fields as $key => $value) {
			$this->$key = $value;
		}
	}
}


?>