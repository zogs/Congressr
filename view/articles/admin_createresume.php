
	<?php echo Session::flash(); ?>
	<form class="form form-center w70pc" action="<?php echo Router::url('admin/articles/createresume');?>" method="POST">
		<div class="form-header">Créer un résumé</div>
		<?php echo $this->Form->input('id','hidden',array('value'=>$resume->id)) ;?>
		<?php echo $this->Form->input('user_id','ID du créateur du résumé',array('placeholder'=>'ex:143')) ;?>
		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
		<?php echo $this->Form->input('title','Titre de votre résumé',array('value'=>'','placeholder'=>'Titre de votre résumé','type'=>'textarea','rows'=>2,'value'=>$resume->title)) ;?>
		<?php echo $this->Form->input('text','Contenu de votre résumé',array('value'=>'','placeholder'=>'Contenu de votre résumé','type'=>'textarea','rows'=>15,'value'=>$resume->text)) ;?>
		<?php echo $this->Form->input('tags','Mots clefs',array('value'=>'','placeholder'=>'Ex: ','value'=>$resume->tags)) ;?>
		<?php echo $this->Form->radio('prefer','Communication',array('poster'=>'Poster','oral'=>"Communication Orale"),array('default'=>$resume->getCommPrefered()) );?>

		<?php foreach ($authors as $k => $author): ?>
			<?php echo '<div class="authors" id="author'.($k+1).'" data-id="'.($k+1).'">'; ?>
			<?php echo $this->Form->input('author'.($k+1).'_id','hidden',array('value'=>$author->id)) ;?>
			<?php echo $this->Form->input('author'.($k+1).'_firstname','Auteur '.($k+1).':',array('placeholder'=>'Prénom','value'=>$author->firstname)) ;?>
			<?php echo $this->Form->input('author'.($k+1).'_lastname','',array('placeholder'=>'Nom','value'=>$author->lastname)) ;?>
			<?php echo $this->Form->input('author'.($k+1).'_institution','',array('placeholder'=>'Institution','value'=>$author->institution)) ;?>
			<?php echo '</div>';?>
		<?php endforeach ?>


		<a href="" id="addAnAuthor">Ajouter un auteur</a>

		<?php if(empty($resume->status) || $resume->status=='pending'): ?>
		<?php echo $this->Form->input('Sauvegarder ce résumé','submit',array('class'=>'btn btn-large btn-primary')) ;?>
		<?php endif; ?>
	</form>

	<?php if(!empty($resume->id) && $resume->status=='pending'): ?>
	<form class="form form-center w70pc" action="<?php echo Router::url('articles/delete/resume/'.$resume->id);?>" method='POST'>
		<div class="form-header">Supprimer Le résumé</div>
		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
		<?php echo $this->Form->input('Cliquez ici pour supprimer ce résumé','submit',array('class'=>'btn btn-small')) ;?>

	</form>
	<?php endif;?>
	<script type="text/javascript">		

		$(document).ready(function(){

			$("#addAnAuthor").bind('click',function(){

				last = $(".authors:last").attr('data-id');

				html = $('<div>').append($('#author'+last).clone()).html(); 
				html = html.replace( new RegExp(last, 'g'),(parseInt(last)+1));
				//html = html.replace( new RegExp('value="(.+)"','g'),'value="" ');

				$('.authors:last').after(html);

				$("#author"+(parseInt(last)+1)).find('input').val('');
				
				return false;
			});
		

		});
		
	</script>