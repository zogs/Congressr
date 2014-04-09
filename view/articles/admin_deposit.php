<?php echo Session::flash(); ?>

<form class="form form-center w70pc fleft" action="" method="POST"  enctype="multipart/form-data">
	
	<h3>Déposer un résumé étendu</h3>
	<p>&nbsp;</p>
	
	<?php echo $this->Form->select('resume_id','Choisir l\'article correspondant',$resumes_accepted,array('class'=>'select2')); ?>	
	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
	
	<?php echo $this->Form->input('deposed','Sélectionner le fichier',array('type'=>'file','class'=>'input-file')); ?>
	<?php echo $this->Form->radio('review_request','Envoyer une demande aux reviewers',array(1=>'Oui',0=>'Non'),array('default'=>1)); ?>
	
	<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>"btn btn-large btn-primary",'value'=>"Envoyer le fichier")); ?>

</form>
