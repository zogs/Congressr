<?php echo Session::flash(); ?>

<?php if($type=='resume'): ?>
<form class="form w70pc fleft">

	<?php echo $this->Form->input('id','hidden',array('value'=>$resume->id)) ;?>
	<?php echo $this->Form->input('title','Titre de votre résumé',array('value'=>'','placeholder'=>'Titre de votre résumé','type'=>'textarea','rows'=>2,'value'=>$resume->title)) ;?>
	<?php echo $this->Form->input('text','Contenu de votre résumé',array('value'=>'','placeholder'=>'Contenu de votre résumé','type'=>'textarea','rows'=>15,'value'=>$resume->text)) ;?>
	<?php echo $this->Form->input('tags','Mots clefs',array('value'=>'','placeholder'=>'Ex: ','value'=>$resume->tags)) ;?>
	<?php echo $this->Form->radio('prefer','Communication',array('poster'=>'Poster','oral'=>"Communication Orale"),array('default'=>$resume->getCommPrefered()) );?>

	<?php foreach ($authors as $k => $author): ?>
		<?php echo '<div class="authors" id="author'.($k+1).'" data-id="'.($k+1).'">'; ?>
		<?php echo $this->Form->input('author'.($k+1).'_firstname','Auteur '.($k+1).':',array('placeholder'=>'Prénom','value'=>$author->firstname)) ;?>
		<?php echo $this->Form->input('author'.($k+1).'_lastname','',array('placeholder'=>'Nom','value'=>$author->lastname)) ;?>
		<?php echo $this->Form->input('author'.($k+1).'_institution','',array('placeholder'=>'Institution','value'=>$author->institution)) ;?>
		<?php echo '</div>';?>
	<?php endforeach ?>
</form>
<?php endif; ?>

<?php if($type=='extended'): ?>
<form class="form form-center w70pc fleft form-row">
	
	<?php echo $this->Form->input('id','hidden',array('value'=>$$type->id)) ;?>
	<?php echo $this->Form->input('resume_id','hidden',array('value'=>$$type->resume_id)) ;?>
	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
	<?php echo $this->Form->input('title','Titre de l\'article',array('placeholder'=>'Titre du résumé','type'=>'textarea','rows'=>2,'value'=>$$type->title)) ;?>
	<?php echo $this->Form->input('content','Contenu de l\'article',array('placeholder'=>'Contenu du l\'article','type'=>'textarea','rows'=>20,'value'=>$$type->content)) ;?>
	<?php echo $this->Form->input('status','Status',array('value'=>$$type->status)) ;?>
	<?php echo $this->Form->input('user_id','User_id',array('value'=>$$type->user_id)) ;?>

	<div class="figures">
		<?php 
		$count=1;
		foreach ($$type->figures as $fig):?>
			<div class="figure" id="figure_<?php echo $count;?>" data-id="<?php echo $count;?>">
				<strong>Figure n° <?php echo $count; ?></strong>
				<br>
				<img src="<?php echo Router::webroot($fig->path);?>" alt="La figure ne s'affiche pas">
				<br>
				<?php echo $fig->caption;?>				
			</div>		
		<?php 
		$count++;
		endforeach; ?>
	</div>
</form>
<?php endif; ?>

<?php if($type=='deposed'): ?>

<form class="form form-center w70pc fleft">
	<h4>Veuillez évaluer le document suivant :</h4>
	
	<p style="text-align:center">
		<a href="<?php echo Router::webroot($$type->filepath);?>">
			<img src="<?php echo Router::webroot('img/icon-ms-word.png');?>" alt="" height="200px">
			<br>Télécharger le document
		</a>
	</p>
</form>

<?php endif; ?>	

<?php if($type=='deposed'): ?>
<form class="form form-row w70pc fleft" name="request" action="<?php echo Router::url('reviewer/requestChange/'.$type.'/'.((isset($$type->resume_id)? $$type->resume_id : $$type->id)));?>" method="POST">

	<h2>Etape 1 : Demande éventuelle de modification à l'auteur</h2>
	<p>
		<strong>
			L'auteur de l'article recevra un email avec votre demande. Vous serez alors prévenu quand il aura re-déposé la version corrigée de son article, qui sera ré-évaluée par vos soins.	
			<br/>Pensez que la décision finale doit être rendu avant le <strong>15 Février 2014</strong> (indiquez à l'auteur une date limite de retour de l'article corrigé si nécessaire)		
		</strong>

	</p>
	<?php echo $this->Form->input('textEmail','Texte du mail',array('type'=>'textarea','rows'=>6,'style'=>"width:100%;",'placeholder'=>'Rédiger ici votre demande de modification'));?>
	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>	
	<input type="submit" name="review" value="Envoyer la demande" class="btn btn-inverse">
</form>
<?php 	endif; ?>

<form class="form form-row w70pc fleft" name="review" action="<?php echo Router::url('reviewer/review/'.$type.'/'.((isset($$type->resume_id)? $$type->resume_id : $$type->id)));?>" method="POST">
	
	<h2>Etape 2 : Decision finale</h2>
	<p>
		<strong>Entrez ici votre décision à destination des organisateurs (indiquez si accepté ou refusé). (Décision définitive, non-communiquée à l'auteur)</strong>
	</p>
	<?php echo $this->Form->input('prefer','hidden',array('value'=>'poster')); // default value for prefer ?>

	<div class="hide">
		<?php echo $this->Form->radio('prefer','Communication recommandé',array('poster'=>'Poster','oral'=>"Orale"),array('default'=>$$type->getCommPreferedByReviewer(Session::user()->getID())) );?>		
	</div>

	<div class="hide">
	<?php echo $this->Form->SelectNumber('note','Note',5,0,array("class"=>"hide",'default'=>$$type->getNoteByReviewer(Session::user()->getID()))); ?>
	</div>
	
	<?php echo $this->Form->input('comment','Commentaires',array('type'=>'textarea','rows'=>5,'style'=>"width:100%;",'value'=>$$type->getCommentByReviewer(Session::user()->getID()))); ?>

	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
	
	<input type="submit" name="review" value="Envoyer" class="btn btn-large btn-inverse">

</form>

<script type="text/javascript">		

	$(document).ready(function(){
		
		tinyMCE.init({
		mode : "exact",
		elements: "inputcontent",
		valid_elements : "em/i,strike,u,strong/b,div[align],br,#p[align],-ol[type|compact],-ul[type|compact],-li",
		menubar:"false",
		statusbar:"false",
		toolbar:"false",
		});
	});
	
</script>