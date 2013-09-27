
<div class="register">
<?php echo Session::flash();?>
	<form class="form" id="form_register" autocomplete="on" action="<?php echo Router::url('users/register'); ?>" method="post" <?php echo (isset($Success))? 'class="hide"':''; ?>>
		<h1>Inscription</h1>
		<p>Pour devenir auteur et proposer des articles, veuillez remplir le formulaire suivant.</p>
		<?php echo $this->Form->input('login','Login',array('icon'=>'icon-user','required'=>'required','placeholder'=>"Choisissez votre login de connexion",'data-url'=>Router::url('users/check'))) ?>
		<?php echo $this->Form->input('email',"Email",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"Entrer votre email de contact",'data-url'=>Router::url('users/check'))) ?>
		<?php echo $this->Form->input('confirmEmail',"Confirmer",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"Confirmer votre email")); ?>
		<?php echo $this->Form->input('password','Mot de passe',array('type'=>"password",'icon'=>'icon-lock','required'=>'required','placeholder'=>'Entrer votre mot de passe')) ?>
		<?php echo $this->Form->input('confirmPassword','Confirmer', array('type'=>'password','icon'=>'icon-lock','required'=>'required','placeholder'=>'Confirmer votre mot de passe')) ?>		
		<?php echo $this->Form->input('prenom',"Prénom",array('icon'=>'icon-user','placeholder'=>'Prénom')) ?>
		<?php echo $this->Form->input('nom',"Nom",array('icon'=>'icon-user','placeholder'=>'Nom ')) ;?>
		<?php echo $this->Form->input('job','Institution',array('icon'=>'icon-bookmark','placeholder'=>'Organisme')) ;?>
		<?php echo $this->Form->input('address','Adresse',array('icon'=>'icon-map-marker','placeholder'=>'Adresse postale')) ;?>
		<?php echo $this->Form->input('tel','Téléphone',array('icon'=>'icon-info-sign','placeholder'=>'Téléphone')) ;?>

		<div class="control-group">
			<label for="pays" class="control-label">Pays / Région</label>
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
				<p> Un email vous sera envoyé pour valider votre inscription.<br/> Vous pourrez ensuite accéder à votre espace et déposer des articles.</p>
				<input class="pull-right btn btn-large btn-primary" type="submit" value="Envoyer l'inscription" />
			</div>				
		</div>
	</form>	
</div>	
