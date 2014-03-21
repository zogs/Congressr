
	<?php echo Session::flash(); ?>


	<div class="clearfix">
		<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
		<h3><?php echo ($mailing->exist())? "Mailing: ".$mailing->title : "Création d'un mailing"; ?></h3>		

		<form class='form w80' action="<?php echo Router::url('admin/mailing/editmailing');?>" method="POST" enctype="multipart/form-data">
			

			<?php echo $this->Form->input('title','Titre du mailing',array('type'=>'text','placeholder'=>"Titre du mailing")); ?>
			
			<?php echo $this->Form->select('userlist','Sélectionnez une liste d\'utilisateur',array(
										'resume_oral'=>'Liste des auteurs dont le résumé est accepté en comm. oral',
										'resume_poster'=>'Liste des auteurs dont le résumé est accepté en poster',
										'resume_refused'=>"Liste des auteurs dont le résumé est refusé",
										'resume_pending'=>"Liste des auteurs dont le résumé est en attente",
										'resume_reviewed'=>"Liste des auteurs dont le résumé a été reviewé",
										'reviewers'=>"Liste de tous les reviewers",
										'redactors'=>"Liste de tous les auteurs",
										'article_oral'=>"Liste des auteurs dont l'article est confirmé à l'oral",
										'article_poster'=>"Liste des auteurs dont l'article est confirmé en poster",
										'article_refused'=>"Liste des auteurs dont l'article est refusé",
										'article_pending'=>"Liste des auteurs dont l'article est en attente",
										'article_reviewed'=>"Liste des auteurs dont l'article a été reviewé",
										),array('placeholder'=>"Sélectionnez une liste d'utilisateur")) ;?>

			<?php echo $this->Form->select('list_id','et/ou Selectionnez une mailing-list',$mailingLists,array('default'=>$mailing->getMailingListId(),'helper'=>'<a href="'.Router::url("admin/mailing/editlist/").'">Créez une nouvelle liste</a>','placeholder'=>"Sélectionnez une mailing list")); ?>

			<?php echo $this->Form->input('emails_added','et/ou entrez une adresse mail',array('type'=>'text','placeholder'=>'Ajouter une ou plusieurs adresses mails ( séparez vos adresses par un ;)')) ?>

			<?php echo $this->Form->input('object','Objet de l\'email',array('type'=>'text','placeholder'=>"Object de l'email")); ?>

			<?php echo $this->Form->input('addpj','Pièce-jointe',array('type'=>'file','helper'=>(!empty($mailing->pj)? '<strong>Une pièce jointe est déjà associé à ce mail</strong> : '.$mailing->pj : ''))); ?>			
			<?php echo $this->Form->input('pj','hidden');?>

			<?php echo $this->Form->input('content','Contenu de l\'email',array('type'=>'textarea',"class"=>"wysiwyg","style"=>"width:100%;","rows"=>15)); ?>

			<?php echo $this->Form->select("signature","Ajouter signature",$signatures,array('default'=>0,'placeholder'=>'Choisir une signature')) ;?>

			<?php echo $this->Form->select('method','Méthode d\'envoi',array('allinone'=>'All in one (prefered)','cron'=>'Cron job','refresh'=>'Auto-refresh 1min'),array('default'=>$mailing->getMethod(),'placeholder'=>'Choisir une méthode d\'envoi')); ?>

			<?php echo $this->Form->input("grouped","Envois par tranche",array("type"=>"text",'placeholder'=>"optionnel (default:10)")) ;?>

			<?php echo $this->Form->input("recipients","Nombre de co-destinataires",array("type"=>"text","placeholder"=>"optionnel (defaut:1)")) ;?>


			<?php echo $this->Form->input('id','hidden');?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())); ?>
			<?php echo $this->Form->input('submit','',array('type'=>'submit','class'=>'btn btn-large btn-primary','value'=>'Enregistrer le mailing')); ?>


		</form>
	</div>	
<script type="text/javascript">


        CKEDITOR.replace( 'content', { filebrowserBrowseUrl: '/js/ckeditor_filemanager/index.html'});
        

</script>