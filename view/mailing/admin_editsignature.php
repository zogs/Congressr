<div class="container">
	<?php echo Session::flash(); ?>

	<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
	<div class="clearfix">
		<form class='form w80' action="<?php echo Router::url('admin/mailing/editsignature');?>" method="POST" enctype="multipart/form-data">
			<div class="form-header">Créer une nouvelle signature</div>
		
			<?php echo $this->Form->input('name','Nom de la signature',array('type'=>'text')); ?>
						
			<div class="control-group">
				<label for="" class="control-label">Contenu</label>
				<div class="controls">
					<textarea id="content" name="content" class="wysiwyg"></textarea>					
				</div>
			</div>

			<?php echo $this->Form->input('id','hidden',array('value'=>0));?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
			<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn btn-info','value'=>'Enregistrer une nouvelle signature')); ?>


		</form>
	</div>
	


	<?php foreach ($signatures as $s):?>
		
		<div class="clearfix">
		<form class='form w80' action="<?php echo Router::url('admin/mailing/editsignature');?>" method="POST" enctype="multipart/form-data">
			<div class="form-header">Editer signature</div>
			<?php echo $this->Form->input('name','Nom de la signature',array('type'=>'text','value'=>$s->name)); ?>
			
			<div class="control-group">
				<label for="" class="control-label">Contenu</label>
				<div class="controls">					
					<textarea id="content<?php echo $s->id;?>" name="content<?php echo $s->id;?>" class="wysiwyg"><?php echo $s->content;?></textarea>					
				</div>
			</div>

			<?php echo $this->Form->input('id','hidden',array('value'=>$s->id));?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
			<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn btn-info','value'=>'Sauvegarder la signature')); ?>				

		</form>
	</div>	
	<?php endforeach;?>	

</div>