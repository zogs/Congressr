<div class="register">
	
	<?php echo Session::flash(); ?>

	<div class="form-block">
		<form class="form" action="<?php echo Router::url('users/login'); ?>" method='post'>
		
				
				<?php echo $this->Form->input('login','',array('required'=>'required','placeholder'=>'Pseudo ou E-mail','icon'=>'icon-user')); ?>
				<?php echo $this->Form->input('password','',array('type'=>'password','required'=>'required','placeholder'=>'Mot de passe','icon'=>'icon-lock')); ?>		
				<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
				<input type="submit" class="btn btn-large btn-inverse" value="Se connecter"/>	
				<a href="<?php echo Router::url('users/recovery');?>">Forgot your password ?</a>	    			
		</form>	
	</div>
</div>
