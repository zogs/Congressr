<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
<h3>Articles Finaux</h3>
<h4>Contenu des mails</h4>
<div class="clearfix">
	<div style="float:left;width:45%;margin:0 1%">
		<h5>Accepted Poster</h5>
		<form action="<?php echo Router::url('admin/articles/mailing');?>" method="POST">
			<?php echo $this->Form->input('content','',array('type'=>'textarea','style'=>"height:200px;width:100%",'value'=>(isset($articleAcceptedPoster->content))? $articleAcceptedPoster->content : 'Add your translate for "'.$this->getLang().'" language'));?>
			<?php echo $this->Form->input('lang','hidden',array('value'=>$this->getLang())) ;?>
			<?php echo $this->Form->input('article','hidden',array('value'=>'deposed')) ;?>
			<?php echo $this->Form->input('result','hidden',array('value'=>'accepted')) ;?>
			<?php echo $this->Form->input('comm_type','hidden',array('value'=>'poster')) ;?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			<input type="submit" value="Save">
		</form>
	</div>
	<div style="float:left;width:45%;margin:0 1%">
		<h5>Accepted Oral</h5>
		<form action="<?php echo Router::url('admin/articles/mailing');?>" method="POST">
			<?php echo $this->Form->input('content','',array('type'=>'textarea','style'=>"height:200px;width:100%",'value'=>(isset($articleAcceptedOral->content))? $articleAcceptedOral->content : 'Add your translate for "'.$this->getLang().'" language'));?>
			<?php echo $this->Form->input('lang','hidden',array('value'=>$this->getLang())) ;?>
			<?php echo $this->Form->input('article','hidden',array('value'=>'deposed')) ;?>
			<?php echo $this->Form->input('result','hidden',array('value'=>'accepted')) ;?>
			<?php echo $this->Form->input('comm_type','hidden',array('value'=>'oral')) ;?>
			<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			<input type="submit" value="Save">
		</form>
	</div>
</div>
	<h4>Envoi des mails</h4>	

<p>
	<a href="<?php echo Router::url('admin/articles/sendTestArticleMailing');?>" class="btn">Test des emails</a>
	Envoi un exemple d'email de chaque type et de chaque langue aux admins et aux chairmans du Congrès
</p>
<p>
	<a href="<?php echo Router::url('admin/articles/sendArticleMailing');?>" class="btn">Envoyer le mailing de confirmation</a>
	Envoi un email à tous les auteurs acceptés (oral et poster) 
</p>
<p>&nbsp;</p>
<p>&nbsp;</p>