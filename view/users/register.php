
<div class="register">
<?php echo Session::flash();?>
	<form class="form" id="form_register" autocomplete="on" action="<?php echo Router::url('users/register'); ?>" method="post" <?php echo (isset($Success))? 'class="hide"':''; ?>>
		<h1>Inscription</h1>

		<?php echo $this->Form->input('login','',array('icon'=>'icon-user','required'=>'required','placeholder'=>"Votre login",'data-url'=>Router::url('users/check'))) ?>
		<?php echo $this->Form->input('email',"",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"Votre email",'data-url'=>Router::url('users/check'))) ?>
		<?php echo $this->Form->input('confirmEmail',"",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"Confirmer votre email")); ?>
		<?php echo $this->Form->input('password','',array('type'=>"password",'icon'=>'icon-lock','required'=>'required','placeholder'=>'Votre mot de passe')) ?>
		<?php echo $this->Form->input('confirmPassword','', array('type'=>'password','icon'=>'icon-lock','required'=>'required','placeholder'=>'Confirmer votre mot de passe')) ?>		
		<?php echo $this->Form->input('prenom',"",array('icon'=>'icon-user','placeholder'=>'Prénom')) ?>
		<?php echo $this->Form->input('nom',"",array('icon'=>'icon-user','placeholder'=>'Nom ')) ;?>
		<?php echo $this->Form->input('job','',array('icon'=>'icon-bookmark','placeholder'=>'Organisme')) ;?>
		<?php echo $this->Form->input('address','',array('icon'=>'icon-map-marker','placeholder'=>'Adresse postale')) ;?>
		<?php echo $this->Form->input('tel','',array('icon'=>'icon-info-sign','placeholder'=>'Téléphone')) ;?>

		<div class="control-group">
			<label for="pays" class="control-label"></label>
			<div class="controls">
			
					<?php 
					
					$this->call('world','locate',array(''));


					?>	

			</div>
		</div>	
		
		<div class="control-group">
			<label for="" class="control-label"></label>
			<div class="controls">
				<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>			
				<?php echo $this->Form->input('accept','hidden',array('value'=>1)); ?>
				<input class="pull-right btn btn-large btn-primary" type="submit" value="Envoyer l'inscription" />
			</div>				
		</div>
	</form>	
</div>	
