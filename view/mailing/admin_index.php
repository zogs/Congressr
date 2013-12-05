
<h2>Mailing</h2>

<p><a class="btn btn-primary" href="<?php echo Router::url('admin/mailing/resumes');?>">Mailing décisionnel des résumés</a> Envoyer le mailing aux auteurs de résumés ( décision accepté/refusé, oral/poster)</p>

<p><a class="btn btn-info" href="<?php echo Router::url('admin/mailing/articles');?>">Mailing final des articles étendus</a> Envoyer le mailing aux auteurs d'articles étendus ( validation oral/poster )</p>

<p><a class="btn btn-warning" href="<?php echo Router::url('admin/mailing/listmailing');?>">Gestion des listes d'adresses</a> Créer ou gérer des listes d'adresses emails</p>

<p><a href="<?php echo Router::url('admin/mailing/mailings');?>" class="btn">Gestion des envois de mailing</a></p>

<p><a href="<?php echo Router::url('admin/mailing/editmailing');?>" class="btn">Création d'un envoi de mailing</a></p>

<p><a class="btn btn-danger" href="<?php echo Router::url('admin/mailing/freemailing');?>">Free mailing</a> Envoyer des emails aux listes d'adresses</p>

<p>
	<a class="btn btn-info btn-small" href="<?php echo Router::url('admin/mailing/editmailing');?>" class="btn">Créer un mailing</a>
	<a class="btn btn-info btn-small" href="<?php echo Router::url('admin/mailing/listmailing');?>">Gerér les listes d'adresses</a>
	<a class="btn btn-info btn-small" href="<?php echo Router::url('admin/mailing/editsignature');?>">Gerér les signatures</a>
</p>

<table class="table table-striped table-hover">
		<thead>
			<th>Status</th>
			<th>Methode</th>
			<th>Nom du mailing</th>
			<th>Date</th>
			<th>Emails</th>
			<th>Durée</th>
			<th>Action</th>

		</thead>
		<tbody>
			 <?php foreach ($mailings as $m): ?>	
			 	<tr class="<?php
			 			if($m->status=='pending') echo 'info';
			 			if($m->status=='current') echo 'warning';
			 			if($m->status=='finished') echo 'success';
			 		?>">
		 			<td>			 			
		 				<?php if($m->status=='pending'): ?>
		 					<a href="<?php echo Router::url('admin/mailing/launchmailing/'.$m->id);?>"><span class="badge badge-info"><i class="icon icon-play icon-white"></i> Commencer</span> </a>
		 				<?php elseif($m->status=='finished'):?>
		 					<span class="label label-success"><i class="icon icon-ok-circle icon-white"></i> Finished</span>
		 				<?php elseif($m->status=='current'): ?>
							<img src="<?php echo Router::webroot('img/ajax-loader.gif');?>"><span class="label"> En cours</span>
		 				<?php endif;?>
		 			</td>
		 			<td>
		 				<?php echo $m->getMethod(); ?>
		 			</td>	 			
		 			<td><a href="<?php echo Router::url('admin/mailing/editmailing/'.$m->id);?>"><?php echo $m->title; ?></a></td>
		 			<td><?php echo date('d F Y',strtotime($m->date_finished));?></td>
		 			<td><?php echo $m->total_count;?></td>
		 			<td><?php echo $m->duration;?>s</td>
			 		<td>
			 			<a href="<?php echo Router::url('admin/mailing/editmailing/'.$m->id);?>">Editer</a>			 			
			 			<a href="<?php echo Router::url('admin/mailing/deletemailing/'.$m->id);?>">Supprimer</a>
			 		</td>				
						
			 	</tr>
				 </form>
			 <?php endforeach ?>
		</tbody>
	</table>