
<?php echo Session::flash(); ?>

<form class="form form-row w70pc fleft" action="" method="POST"  enctype="multipart/form-data">
	
	<?php if(!empty($deposed->filename)): ?>
		<p class="alert">
			Vous avez déposé votre article le <?php echo $deposed->date;?>
		</p>
	<?php endif; ?>

	<p>Merci de déposer l'article étendu intitulé <strong><?php echo $resume->title;?></strong></p>

	<p>Votre article doit être au format <strong>.doc</strong> et faire moins de 6 pages</p>

	<p>Veillez à suivre les <a href="<?php echo Router::webroot('document/consignes.doc');?>">instructions de mise en page</a> en suivant le <a href="<?php echo Router::webroot('document/patron_article.doc');?>">patron d'article</a>

	
	
	<?php echo $this->Form->input('user_id','hidden',array('value'=>Session::user()->getID())); ?>
	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
	<?php echo $this->Form->input('resume_id','hidden',array('value'=>$resume->id)); ?>
	<?php echo $this->Form->input('title','hidden',array('value'=>$resume->title)); ?>	


	<?php echo $this->Form->input('deposed','<img src="'.Router::webroot('img/icon-ms-word.png').'" alt="">',array('type'=>'file','class'=>'input-file')); ?>
	
	<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>"btn btn-large btn-primary",'value'=>"Envoyer le fichier")); ?>

</form>

