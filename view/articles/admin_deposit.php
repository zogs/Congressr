
<?php echo Session::flash(); ?>

<form class="form form-center w70pc fleft" action="" method="POST"  enctype="multipart/form-data">
	
	<p class="alert alert-info">Vous allez déposer un résumé étendu <strong>en le liant</strong> à un résumé existant.</p>
	<p>&nbsp;</p>
	
	
	<?php echo $this->Form->select('resume_id','Résumé correspondant',$resumes_accepted,array('class'=>'select2')); ?>	
	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>


	<?php echo $this->Form->input('deposed','<img src="'.Router::webroot('img/icon-ms-word.png').'" alt="">',array('type'=>'file','class'=>'input-file')); ?>

	
	<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>"btn btn-large btn-primary",'value'=>"Envoyer le fichier")); ?>

</form>

