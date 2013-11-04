<?php echo Session::flash(); ?>


<div class="clearfix">
	<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
	<h3>Edition de mailing</h3>
	<p>Cet outil permet ...</p>

	<form class='form' action="<?php echo Router::url('admin/mailing/editmailing');?>" method="POST" enctype="multipart/form-data">
		

		<?php echo $this->Form->input('title','Objet de l\'email',array('type'=>'text','placeholder'=>"Titre du mailing")); ?>
		
		<?php echo $this->Form->select('list_id','Selectionnez une mailing list',$mailingLists,array('default'=>$mailing->mailinglist_id,'helper'=>'<a href="'.Router::url("admin/mailing/editlist/").'">Créez une nouvelle liste</a>','placeholder'=>"Sélectionnez une mailing list")); ?>

		<?php echo $this->Form->input('emails_added','Ou entrez une adresse mail',array('type'=>'text','placeholder'=>'Une ou plusieurs adresses mails ( séparez vos adresses par un ;)')) ?>

		<?php echo $this->Form->input('object','Objet de l\'email',array('type'=>'text','placeholder'=>"Object de l'email")); ?>

		<?php echo $this->Form->input('content','Contenu de l\'email',array('type'=>'textarea','class'=>"wysiwyg","style"=>"width:100%;","rows"=>15)); ?>

		<?php echo $this->Form->input('addpj','Pièce-jointe',array('type'=>'file','helper'=>(!empty($mailing->pj)? '<strong>Une pièce jointe est déjà associé à ce mail</strong> : '.$mailing->pj : ''))); ?>
		
		<?php echo $this->Form->input('pj','hidden');?>
		<?php echo $this->Form->input('id','hidden');?>
		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
		<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn-large btn-primary','value'=>'Enregistrer le mailing')); ?>


	</form>
</div>