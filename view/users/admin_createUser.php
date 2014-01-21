


<h2>
	Créer un utilisateur
</h2>
<form class="form" id="form_register" autocomplete="on" action="<?php echo Router::url('admin/users/createUser/'.$role); ?>" method="post">
	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
	<?php echo $this->Form->input('role','Role',array('value'=>$role)) ;?>	
	<?php echo $this->Form->input('login','Login',array('icon'=>'icon-user','required'=>'required','placeholder'=>"login",'data-url'=>Router::url('users/check'))) ?>
	<?php echo $this->Form->input('email',"Email",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"email",'data-url'=>Router::url('users/check'))) ?>
	<?php echo $this->Form->input('password','Mot de passe',array('type'=>"password",'icon'=>'icon-lock','required'=>'required','placeholder'=>'mot de passe')) ?>
	<?php echo $this->Form->input('confirm','Confirmer mot de passe', array('type'=>'password','icon'=>'icon-lock','required'=>'required','placeholder'=>'Confirmer')) ?>		
	<?php echo $this->Form->input('prenom',"Prénom",array('icon'=>'icon-user','placeholder'=>'Prénom')) ?>
	<?php echo $this->Form->input('nom',"Nom",array('icon'=>'icon-user','placeholder'=>'Nom ')) ;?>
	<?php echo $this->Form->input('job','Institution',array('icon'=>'icon-bookmark','placeholder'=>'Organisme')) ;?>
	<?php echo $this->Form->input('address','Adresse',array('icon'=>'icon-map-marker','placeholder'=>'Adresse postale')) ;?>
	<?php echo $this->Form->input('tel','Téléphone',array('icon'=>'icon-info-sign','placeholder'=>'Téléphone')) ;?>

	<div class="control-group">
		<label for="pays" class="control-label">Pays / Region</label>
		<div class="controls"><?php $this->call('world','locate',array('')); ?></div>
	</div>	

			
	<div class="actions">		
		<input class="pull-right btn btn-large btn-primary" type="submit" value="Enregister l'utilisateur" style="margin-bottom:0"/>
	</div>
	</form>	

