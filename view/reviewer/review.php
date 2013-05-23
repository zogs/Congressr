<?php echo Session::flash(); ?>
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

<form class="form form-row w30pc fright" name="review" action="<?php echo Router::url('reviewer/review/'.$type.'/'.$resume->id);?>" method="POST">
	

	<?php echo $this->Form->radio('prefer','Communication',array('poster'=>'Poster','oral'=>"Orale"),array('default'=>$resume->getCommPrefered()) );?>

	<?php echo $this->Form->SelectNumber('note','Note',5,0,array('default'=>$resume->note)); ?>

	<?php echo $this->Form->input('comment','Commentaires',array('type'=>'textarea','rows'=>5,'style'=>"width:100%;",'value'=>$resume->comment)); ?>

	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
	
	<input type="submit" name="review" value="SUBMIT" class="btn btn-large btn-inverse">

</form>
