<?php echo Session::flash(); ?>


<div class="clearfix">
	<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
	<h3>Envoi de mailing</h3>
	<p>Cet outil permet d'envoyer un par un des emails à une mailing list depuis l'adresse de contact du congrès</p>

	<form class='form' action="<?php echo Router::url('admin/mailing/freemailing');?>" method="POST" enctype="multipart/form-data">
		
		<?php echo $this->Form->select('list_id','Selectionnez une mailing list',$selectLists,array('helper'=>'<a href="'.Router::url("admin/mailing/editlist/").'">Créez une nouvelle liste</a>','placeholder'=>"Sélectionnez une mailing list")); ?>

		<?php echo $this->Form->input('emails','Ou entrez une adresse mail',array('type'=>'text','placeholder'=>'Une ou plusieurs adresses mails ( séparez vos adresses par un ;)')) ?>

		<?php echo $this->Form->input('title','Objet de l\'email',array('type'=>'text','placeholder'=>"Objet de l'email")); ?>

		<?php echo $this->Form->input('content','Contenu de l\'email',array('type'=>'textarea','class'=>"wysiwyg","style"=>"width:100%;","rows"=>15)); ?>

		<?php echo $this->Form->input('pj','Pièce-jointe',array('type'=>'file')); ?>

		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
		<p style='text-align:center;margin:30px 0 5px 0;float:left;width:100%'><strong>L'envoi peut être long. Ne pas cliquez plusieurs fois.</strong></p>
		<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn-large btn-primary','value'=>'Envoyer le mailing')); ?>


	</form>
</div>