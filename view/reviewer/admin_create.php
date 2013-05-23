<h2>Create Reviewer</h2>
<p class="small em">
	A email will be send to the reviewer with his connexion information. <br />He will be able to log in Congressr and start reviewing articles he will be asked by the chairman.
</p>
<div class="span8">
	<form class="form" id="form_register" autocomplete="on" action="<?php echo Router::url('admin/reviewer/create'); ?>" method="post">

			<?php echo $this->Form->input('login','',array('icon'=>'icon-user','required'=>'required','placeholder'=>"login",'data-url'=>Router::url('users/check'))) ?>
			<?php echo $this->Form->input('email',"",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"email",'data-url'=>Router::url('users/check'))) ?>
			<?php echo $this->Form->input('password','',array('type'=>"password",'icon'=>'icon-lock','required'=>'required','placeholder'=>'mot de passe')) ?>
			<?php echo $this->Form->input('confirm','', array('type'=>'password','icon'=>'icon-lock','required'=>'required','placeholder'=>'Confirmer')) ?>		
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

			<?php echo $this->Form->input('mailcontent','',array('type'=>'textarea','class'=>'','placeholder'=>"Contenu du mail d'invitation ( Mentionner de cliquer le lien d'activation, pour insérer le lien, entrer {link} dans votre texte ) ",'rows'=>5)) ;?>
			
			<div class="actions">
				<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
				<?php echo $this->Form->input('role','hidden',array('value'=>'reviewer')) ;?>			
				<input class="pull-right btn btn-large btn-primary" type="submit" value="Create reviewer and Send email" />
				
			</div>

	</form>		
</div>