
<h2>Mailing</h2>

<p><a class="btn btn-primary" href="<?php echo Router::url('admin/mailing/resumes');?>">Mailing décisionnel des résumés</a> Envoyer le mailing aux auteurs de résumés ( décision accepté/refusé, oral/poster)</p>

<p><a class="btn btn-info" href="<?php echo Router::url('admin/mailing/articles');?>">Mailing final des articles étendus</a> Envoyer le mailing aux auteurs d'articles étendus ( validation oral/poster )</p>

<p><a class="btn btn-warning" href="<?php echo Router::url('admin/mailing/listmailing');?>">Gestion des listes d'adresses</a> Créer ou gérer des listes d'adresses emails</p>

<p><a href="<?php echo Router::url('admin/mailing/mailings');?>" class="btn">Gestion des envois de mailing</a></p>

<p><a href="<?php echo Router::url('admin/mailing/editmailing');?>" class="btn">Création d'un envoi de mailing</a></p>

<p><a class="btn btn-danger" href="<?php echo Router::url('admin/mailing/freemailing');?>">Free mailing</a> Envoyer des emails aux listes d'adresses</p>