
<?php echo Session::flash(); ?>

<form class="form form-row w70pc fleft" action="" method="POST"  enctype="multipart/form-data">
	
	<p>Merci de déposer le contenu de votre article étendu intitulé <strong><?php echo $resume->title;?></strong></p>

	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
	<?php echo $this->Form->input('id','hidden',array('value'=>$extended->id)); ?>
	<?php echo $this->Form->input('status','hidden',array('value'=>'pending')) ;?>
	<?php echo $this->Form->input('resume_id','hidden',array('value'=>$resume->id)); ?>
	<?php echo $this->Form->input('title','Titre finale de votre article',array('type'=>'textarea','rows'=>2,'value'=>((!empty($extented->title)? $extended->title : $resume->title))));?>
	<?php echo $this->Form->input('content','Le texte définitif de votre article',array('type'=>'textarea','placeholder'=>'Coller votre texte ici','value'=>$extended->content)); ?>

	<div class="figures">
	<?php 
	$count=1;
	foreach ($figures as $fig):?>
		<div class="figure" id="figure_<?php echo $count;?>" data-id="<?php echo $count;?>">
			<?php echo $this->Form->input('figure_number','hidden',array('value'=>$count)); ?>
			<?php echo $this->Form->input('figure_id_'.$count,'hidden',array('value'=>$fig->id));?>
			<?php echo $this->Form->input('figure_'.$count,'Figure n°'.$count.' de l\'article',array('type'=>'file','src'=>((!empty($fig->path))? Router::webroot($fig->path) : ''))); ?>
			<?php echo $this->Form->input('caption_'.$count,'Légende de cette figure',array('placeholder'=>'Légende de cette figure','value'=>$fig->caption)); ?>
		</div>		
	<?php 
	$count++;
	endforeach; ?>
	</div>

	<div class="btnAddFigure">
		<a href="" id="addFigure" class="btn">Ajouter une figure</a>		
	</div>
	<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>"btn btn-large btn-primary",'value'=>"Envoyer")); ?>

</form>

<script type="text/javascript">		

	$(document).ready(function(){

		$("#addFigure").bind('click',function(){

			last = $(".figures:last").attr('data-id');

			html = $('<div>').append($('#figure_'+last).clone()).html(); 
			html = html.replace( new RegExp(last, 'g'),(parseInt(last)+1));
			//html = html.replace( new RegExp('value="(.+)"','g'),'value="" ');

			$('.figures:last').after(html);

			$("#figure_"+(parseInt(last)+1)).find('input').not('input[name=figure_number]').val('');
			$("#figure_"+(parseInt(last)+1)).find('.input-file-thumbnail').hide();
			
			return false;
		});


		tinyMCE.init({
		mode : "exact",
		elements: "inputcontent",
		valid_elements : "em/i,strike,u,strong/b,div[align],br,#p[align],-ol[type|compact],-ul[type|compact],-li"
		});
	

	});
	
</script>