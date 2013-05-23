<div class="page-header">
	<h1>Editer un contenu</h1>
</div>

<form class="form-horizontal" action="<?php echo Router::url('admin/contents/edit/'.$id); ?>" method="post">
<a href="?lang=fr">Francais</a> <a href="?lang=en">English</a>

<?php echo $this->Form->select('lang','Language',Conf::$languageAvailable,array('default'=>$this->getLang()));?>
<?php echo $this->Form->input('title','Titre du contenu');  ?>
<?php echo $this->Form->input('id','hidden');  ?>
<?php echo $this->Form->input('content_id','hidden') ;?>
<?php echo $this->Form->input('id_i18n','hidden') ;?>
<?php echo $this->Form->input('content','Contenu',array("type"=>"textarea","class"=>"wysiwyg","style"=>"width:100%;","rows"=>5));  ?>
<div class="control-group">
	<label for="" class="control-label"></label>
	<div class="controls">
		<input type="submit" class="btn btn-primary" value="Envoyer" />
	</div>
</div>

<?php echo $this->Form->input('valid','Traduction valide',array("type"=>"checkbox")) ;?>
<?php echo $this->Form->input('type','type de contenu',array("value"=>$this->request->get('type'))) ;?>
<?php echo $this->Form->input('position','Position',array()) ;?>

<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>

<div class="control-group">
	<label for="" class="control-label"></label>
	<div class="controls">
		<input type="submit" class="btn btn-primary" value="Envoyer" />
	</div>
</div>
</form>

<script type="text/javascript">
	$(document).ready(function(){

		$("select#lang").on('change',function(){

			var url = window.location.href;
			if (url.indexOf('?') > -1){
			   url += '&lang='+$(this).val();
			}else{
			   url += '?lang='+$(this).val();
			}
			window.location.href = url;
		});


	});	
</script>