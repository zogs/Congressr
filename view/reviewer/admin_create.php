<h2>Create Reviewer</h2>
<p class="small em">
	A email will be send to the reviewer with his connexion information. <br />He will be able to log in Congressr and start reviewing articles he will be asked by the chairman.

</p>

	<form class="form" id="form_register" autocomplete="on" action="<?php echo Router::url('admin/reviewer/create'); ?>" method="post">

			<?php //echo $this->Form->input('login','',array('icon'=>'icon-user','required'=>'required','placeholder'=>"login",'data-url'=>Router::url('users/check'))) ?>
			<?php echo $this->Form->input('email',"Email",array('type'=>'email', 'icon'=>"icon-envelope","required"=>"required","placeholder"=>"email",'data-url'=>Router::url('users/check'))) ?>
			<?php //echo $this->Form->input('password','',array('type'=>"password",'icon'=>'icon-lock','required'=>'required','placeholder'=>'mot de passe')) ?>
			<?php //echo $this->Form->input('confirm','', array('type'=>'password','icon'=>'icon-lock','required'=>'required','placeholder'=>'Confirmer')) ?>		
			<?php echo $this->Form->input('prenom',"Prénom",array('icon'=>'icon-user','placeholder'=>'Prénom')) ?>
			<?php echo $this->Form->input('nom',"Nom",array('icon'=>'icon-user','placeholder'=>'Nom ')) ;?>
			<?php echo $this->Form->input('job','Institution',array('icon'=>'icon-bookmark','placeholder'=>'Organisme')) ;?>
			<?php echo $this->Form->input('address','Adresse',array('icon'=>'icon-map-marker','placeholder'=>'Adresse postale')) ;?>
			<?php echo $this->Form->input('tel','Téléphone',array('icon'=>'icon-info-sign','placeholder'=>'Téléphone')) ;?>

			<div class="control-group">
				<label for="pays" class="control-label">Pays / Region</label>
				<div class="controls">
				
						<?php 
						
						$this->call('world','locate',array(''));


						?>	

				</div>
			</div>	

			<?php echo $this->Form->input('mailcontent','Contenu du mail d\'invitation',array('type'=>'textarea','class'=>'','value'=>"
<p>Cher(e) Collègue,</p>

<p><strong>Le 27ème Colloque de l’Association  Internationale de Climatologie</strong> (AIC 2014) se tiendra à Dijon du 2 au 5 juillet 2014 (www.aic2014.com). Organisé par le Centre de Recherches de Climatologie / Biogéosciences (UMR 6282 CNRS / université de Bourgogne), il aura pour thème <strong>« Climat : système & interactions »</strong> avec les 5 sous-thèmes suivants :</p>

<ul>
<li>Interactions et changement d’échelles</li>
<li>États de surface</li>
<li>Changement climatique</li>
<li>Impacts du climat</li>
<li>Variabilité et aléas climatiques</li>
</ul>

<p>Nous vous sollicitons pour faire partie du comité scientifique de ce colloque. Si vous l'acceptez, vous serez contacté en mars-avril pour sélectionner et expertiser des communications scientifiques (résumés étendus) soumises à ce colloque. </p>

<p>Nous vous remercions de bien vouloir indiquer :<br/>
- d'ici à <strong>jeudi 26 septembre</strong> ;<br/>
- via le <strong>lien ci-dessous </strong>;<br/>
si vous acceptez de faire partie de ce comité.
</p> 

<p>Bien cordialement,</p>

<p>Yves Richard & Pierre Camberlin</p>

<strong>Si vous acceptez de faire partie du comité scientifique AIC 2014</strong>, activez votre compte en cliquant sur ce lien : {link} <br/>
<strong>Login</strong> : {login}<br/>
<strong>Password</strong> : {password}<br/>
<small>Vous pouvez modifier ces informations en vous connectant à votre compte</small>
<strong>Merci de conserver ces informations de connexion pour accéder ultérieurement à votre compte</strong>


						",'rows'=>10)) ;?>
			
			<div class="actions">
				<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
				<?php echo $this->Form->input('role','hidden',array('value'=>'reviewer')) ;?>			
				<input class="pull-right btn btn-large btn-primary" type="submit" value="Envoyer l'invitation" style="margin-bottom:0"/>
				<div class="clearfix"></div>
				<br/><a href="<?php echo Router::url('cockpit/reviewer/create');?>" >Créer un nouveau reviewer</a>
			</div>
	</form>		
				

