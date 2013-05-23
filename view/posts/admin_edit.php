<div class="page-header">
	<h1>Editer un article</h1>
</div>

<form class="form-horizontal" action="<?php echo Router::url('admin/posts/edit/'.$id); ?>" method="post">

<?php echo $this->Form->input('name','Titre de l\'article');  ?>
<?php echo $this->Form->input('slug','url');  ?>
<?php echo $this->Form->input('id','hidden');  ?>
<?php echo $this->Form->input('content','Contenu de l\'article',array("type"=>"textarea","class"=>"wysiwyg","style"=>"width:100%;","rows"=>5));  ?>
<?php echo $this->Form->input('online','En ligne',array("type"=>"checkbox")); ?>
<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>

<div class="actions">
	<input type="submit" class="btn btn-primary" value="Envoyer" />
</div>
</form>