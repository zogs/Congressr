<?php 
	//retrieve list of reviewer
	$reviewers = $this->call('reviewer','index');
	//prepare it for select box
	foreach ($reviewers as $key => $reviewer) {
		$reviewers[$reviewer->user_id] =$reviewer->login;
		unset($reviewers[$key]);

	}


 ?>
<?php echo Session::flash(); ?>

<div class="w70pc fleft">
		
		<form action="#" class="form">
			<div class="form-header">Evaluation des reviewers</div>
			<table class="table table-stripped">
				<thead>
					<th>Reviewer</th>
					<th>Note</th>
					<th>Poster/Oral</th>
					<th>Commentaire</th>
				</thead>
				<tbody>
					<?php if(!empty($$type->reviewed)):?>
					<?php foreach ($$type->reviewed as $r) :?>
					<tr>
						<td><?php echo $reviewers[$r->reviewer_id];?></td>
						<td><?php echo $r->note;?></td>
						<td><?php echo $r->comm_type;?></td>
						<td><?php echo $r->comment;?></td>
					</tr>
					<?php endforeach;?>
					<?php else: ?>
					<tr>
						<td><i>Pas encore d'évaluation</i></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			
		</form>

		<?php if($type=='resume'): ?>
		<form class="form form-center " action="<?php echo Router::url('admin/articles/view/resume/'.$resume->id);?>" method="POST">
			<div class="form-header">Resumé</div>
			<?php echo $this->Form->input('id','hidden',array('value'=>$resume->id)) ;?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			<?php echo $this->Form->input('title','Titre du résumé',array('placeholder'=>'Titre du résumé','type'=>'textarea','rows'=>2,'value'=>$resume->title)) ;?>
			<?php echo $this->Form->input('text','Contenu du résumé',array('placeholder'=>'Contenu du résumé','type'=>'textarea','rows'=>20,'value'=>$resume->text)) ;?>
			<?php echo $this->Form->input('tags','Mots clefs',array('value'=>'','placeholder'=>'Ex: ','value'=>$resume->tags)) ;?>
			<?php echo $this->Form->radio('prefer','Communication',array('poster'=>'Poster','oral'=>"Communication Orale"),array('default'=>$resume->getCommPrefered()) );?>
			<?php echo $this->Form->input('status','Status',array('value'=>$resume->status)) ;?>
			<?php echo $this->Form->input('user_id','User_id',array('value'=>$resume->user_id)) ;?>

			<?php foreach ($authors as $k => $author): ?>
				<?php echo '<div class="authors" id="author'.($k+1).'" data-id="'.($k+1).'">'; ?>
				<?php echo $this->Form->input('author'.($k+1).'_id','hidden',array('value'=>$author->id)) ;?>
				<?php echo $this->Form->input('author'.($k+1).'_firstname','Auteur '.($k+1).':',array('placeholder'=>'Prénom','value'=>$author->firstname)) ;?>
				<?php echo $this->Form->input('author'.($k+1).'_lastname','',array('placeholder'=>'Nom','value'=>$author->lastname)) ;?>
				<?php echo $this->Form->input('author'.($k+1).'_institution','',array('placeholder'=>'Institution','value'=>$author->institution)) ;?>
				<?php echo '</div>';?>
			<?php endforeach ?>


			<a href="" id="addAnAuthor">Ajouter un auteur</a>	
			<div class="clearfix"></div>
			<input type="submit" value="Sauver les changements" class="btn btn-warning" style="float:left">
		</form>
		<?php endif; ?>



		<?php if($type=='deposed'): ?>
		<form class="form form-center form-row" action="<?php echo Router::url('admin/articles/view/'.$type.'/'.$$type->resume_id);?>" method="POST">
			<div class="form-header">Article reviewé</div>
			<a href="<?php echo Router::url($$type->filepath);?>">
			<img src="<?php echo Router::webroot('img/icon-ms-word.png');?>" alt="">
			Téléchargez le document
			</a>
			
		</form>

		<form class="form form-row" name="request" action="<?php echo Router::url('reviewer/requestChange/'.$type.'/'.((isset($$type->resume_id)? $$type->resume_id : $$type->id)));?>" method="POST">
			<div class="form-header">Demande de modification</div>			
			<p>
				<strong>Envoyer une demande de modification à l'auteur de l'article. </strong>
				<br>
				<small>
					L'auteur de l'article recevra un email avec votre demande. Il devra alors obligatoirement déposer une nouvelle version de son article, qui sera ré-évaluer par les reviewers.
					<br>L'auteur peut répondre à l'email, vous recevrez sa réponse directement dans votre boite mail, et pourrez alors engager une discussion concernant les modifications demandées
				</small>

			</p>
			<?php echo $this->Form->input('textEmail','Texte du mail',array('type'=>'textarea','rows'=>6,'style'=>"width:100%;",'placeholder'=>'Rédiger ici votre demande de modification'));?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>	
			<input type="submit" name="review" value="Envoyer la demande" class="btn btn-primary">
		</form>		
		<?php endif; ?>		

</div>

<div class="w30pc fright">
	
	<form class="form form-row label-left" action="<?php echo Router::url('admin/articles/assign/'.$type.'/'.((isset($$type->resume_id)? $$type->resume_id : $$type->id)));?>" method="POST">
		<div class="form-header">Demande de review</div>
		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
		<?php echo $this->Form->select('reviewer','Choisir le reviewer',$reviewers,array('style'=>'width:auto;padding-left:0;')) ;?>
		<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn-primary','value'=>'Envoyer la demande','style'=>'margin:0')); ?>
		
		<?php if(!empty($$type->assigned)): ?>
			<div class="alert alert-info" style="float:left;">
			<small>Demandes envoyés</small>
			<ul style="width:100%;float:left;">
				<?php foreach ($$type->assigned as $assign) {
					$user_id = $assign->user_id;
					$user_name = $reviewers[$user_id];
					foreach ($$type->reviewed as $review) {

						if($review->reviewer_id == $assign->user_id){
							$user_name .= '( done )';
						}
					}

					echo '<li><small>'.$user_name.'</small></li>';
				} ?>
			</ul>
			</div>
		<?php endif; ?>

	</form>

	<form class="form form-row label-left" action="<?php echo Router::url('admin/articles/decision/'.$type.'/'.((isset($$type->resume_id)? $$type->resume_id : $$type->id)));?>" method="POST">
		<div class="form-header">Decision</div>
		<?php echo $this->Form->select('decision','Choisir le statut',array('accepted'=>'accepted','refused'=>'refused','pending'=>'pending','reviewed'=>'reviewed'),array('style'=>'width:auto;padding-left:0;')); ?>
		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
		<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn-primary','value'=>'Sauvegarder','style'=>'margin:0')); ?>
		<div class="alert alert-<?php if($$type->status=='accepted') echo 'success'; elseif($$type->status=='pending') echo 'warning'; elseif($$type->status=='refused') echo 'error'; elseif($$type->status=='reviewed') echo 'info';?>" style="float:left">
			<small>Statut en cours</small>
			<ul style="width:100%;float:left;">
				<?php echo $$type->status;?>
			</ul>
		</div>

	</form>

	<form class="form form-row label-left" action="<?php echo Router::url('admin/articles/delete/'.$type.'/'.$$type->id);?>" method="POST">
		<div class="form-header">Supprimer</div>
		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
		<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn','value'=>'Supprimer','style'=>'margin:0')); ?>
		


	</form>
</div>




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
	
		tinyMCE.init({
		mode : "exact",
		elements: "inputcontent",
		valid_elements : "em/i,strike,u,strong/b,div[align],br,#p[align],-ol[type|compact],-ul[type|compact],-li"
		});
	});
	
</script>