
<?php echo Session::flash(); ?>

<form class="form form-row w70pc fleft" action="" method="POST"  enctype="multipart/form-data">
	
	<p>Merci de déposer l'article étendu intitulé <strong><?php echo $resume->title;?></strong></p>

	<p>Votre article doit être au format <strong>.doc</strong> et faire moins de 6 pages</p>

	<p>Veillez à suivre les <a href="">instructions de mise en page</a> en suivant le <a href="">patron d'article</a>

	<?php if(!empty($deposed->filename)): ?>
		<p class="alert">
			You have deposed your article the <?php echo $deposed->date;?> for the last time
		</p>
	<?php endif; ?>
	
	<?php echo $this->Form->input('user_id','hidden',array('value'=>Session::user()->getID())); ?>
	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
	<?php echo $this->Form->input('resume_id','hidden',array('value'=>$resume->id)); ?>
	<?php echo $this->Form->input('title','hidden',array('value'=>$resume->title)); ?>	
	
	<label for="inputdeposed">
		<img src="<?php echo Router::webroot('img/icon-ms-word.png');?>" alt="">
		<input type="file" class="input-file" id="inputdeposed" name="deposed">
	</label>

	
	<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>"btn btn-large btn-primary",'value'=>"Envoyer le fichier")); ?>

</form>

