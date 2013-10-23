<div class="formulaire">
	
	<?php echo Session::flash(); ?>

	<?php if($action=='' || $action=='show_form_email'): ?>

	<div class="form-block">
		<h3>Procédure de récupération de mot de passe</h3>
		<p>Vous avez oublié votre mot de passe ? Entrer votre adresse email ci-dessous et vous recevrez un mail vous permettant de changer de mot de passe</p>
		<form class="form" action="<?php echo Router::url('users/recovery'); ?>" method="POST">
				<?php echo $this->Form->input('email','Entrer votre adresse email',array('required'=>'required','icon'=>'icon-envelope','placeholder'=>'Entrer votre addresse email')) ;?>
				<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
				<input type="submit" class="btn btn-primary" value="Envoyer" />
		</form>	
	</div>
	<?php endif;?>


	<?php if($action=='show_form_password') : ?>

	<div class="form-block">	
		<form class="form" action="<?php echo Router::url('users/recovery'); ?>" method="POST">
			
			<?php echo $this->Form->input('code','hidden',array('value'=>$code)) ;?>
			<?php echo $this->Form->input('user','hidden',array('value'=>$user_id)) ;?>	
			<?php echo $this->Form->input('password','New password',array('type'=>'password','icon'=>'icon-lock','placeholder'=>'New password')) ;?>
			<?php echo $this->Form->input('confirm','Confirm password',array('type'=>'password','icon'=>'icon-lock','placeholder'=>'Confirm password')) ;?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			<input type="submit" value="Envoyer" />
		</form>
	</div>	


<?php endif ;?>
	
</div>