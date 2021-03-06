<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<?php $this->loadCSS();?>
	<?php $this->loadJS();?>	
	<title><?php echo isset($title_for_layout)?$title_for_layout : Conf::$website;?></title>
	
</head>
<body data-user_id="<?php echo Session::user()->getID(); ?>">


	<header class="navbar navbar-inverse navbar-fixed-top">
	  <nav class="navbar-inner">
	    <div class="container">
      		<a class="brand" href="<?php echo Router::url('pages/home');?>">
	      	  	<?php echo Conf::$website;?>
			</a>

			<ul class="nav">
				
				<?php

				if(Session::user()->getRole()=='visitor'){
					//Recuperation du Menu
					//Appel de ma methode getMenu du controlleur Pages				
					$pagesMenu = $this->call('pages','getMenu',array('main'));				
					foreach ($pagesMenu as $v) : 
				?>				
					<li><a href='<?php echo Router::url("$v->slug");?>' ><?php echo $v->title; ?></a></li>
				<?php 
				
					endforeach;
				?>
					<li><a href="<?php echo Router::url('users/register');?>" >Inscription des auteurs</a></li>
				<?php
				}
				//Admin section button
				if(Session::user()->getRole()=='admin' || Session::user()->getRole()=='chairman'):?>
				<li><a href="<?php echo Router::url('admin/pages/index');?>">Administration</a></li>
				<?php endif;
				if(Session::user()->getRole()=='reviewer'):?>
				<li><a href="<?php echo Router::url('redactor/board');?>"><i class="icon icon-folder-open icon-white"></i> Mes résumés</a></li>
				<li><a href="<?php echo Router::url('articles/resume');?>"><i class="icon icon-file icon-white"></i> Déposer un resumé</a></li>
				<li><a href="<?php echo Router::url('reviewer/board');?>"><i class="icon icon-edit icon-white"></i> Evaluer les résumés</a></li>
				<?php endif;
				if(Session::user()->getRole()=='redactor'):?>
				<li><a href="<?php echo Router::url('redactor/board');?>"><i class="icon icon-folder-open icon-white"></i> Mes résumés</a></li>
				<li><a href="<?php echo Router::url('articles/resume');?>"><i class="icon icon-file icon-white"></i> Déposer un resumé</a></li>
				<?php endif;
				
				?>

				
			</ul>
		

			<ul class="nav pull-right">
				
				<?php /* ?>
				<li><a href="?lang=fr"><i class="flag flag-fr"></i></a></li>
				<li><a href="?lang=en"><i class="flag flag-uk"></i></a></li>
				*/ ?>
			
			
				<?php if (Session::user()->isLog()): ?>
					<li><a href="<?php echo Router::url('users/account');?>">
							<img class="nav-avatar" src="<?php echo Router::webroot(Session::user()->getAvatar()); ?>" />	
							<span class="nav-login"><?php echo Session::user()->getLogin(); ?></span>
					</a></li>
					<li class="dropdown">	
			
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li><a href="<?php echo Router::url('users/logout'); ?>">Déconnexion</a></li>
							<li class="divider"></li>
							<li><a href="<?php echo Router::url('users/account'); ?>">Mon Compte</a></li>						
						</ul>
					</li>
				<?php else: ?>

					<form class="loginForm" action="<?php echo Router::url('users/login'); ?>" method='post'>
						<input type="login" name="login" required="required" placeholder="Login or email" autofocus="autofocus" value=""/>
						<input type="password" name="password" required="required" placeholder="Password" value="" />
						<input type="hidden" name="token" value="<?php echo Session::token();?>" />
						<input type="submit" value="OK" />
					</form>
					<li><a href="<?php echo Router::url('users/login');?>">Connexion</a></li>	
					


				<?php endif ?>

			</ul>
		</div>
	  </nav>
	</header>

	<section class="container mainContainer">	
			
		<?php echo $content_for_layout;?>
	</section>


	<div class="modal fade" id="myModal"></div>

</body>



 <script type="text/javascript">


 	/*===========================================================
 		Set security token
 	============================================================*/
 	var CSRF_TOKEN = '<?php echo Session::token(); ?>';

 	/*===========================================================
 		GOOGLE FONTS
 	============================================================*/
      WebFontConfig = {
        google: { families: [ 'Bangers','Squada One','Oswald:300,400,700' ] },      
        fontinactive: function(fontFamily, fontDescription) { /*alert('Font '+fontFamily+' is currently not available'); */}
      };

      (function() {
        var wf = document.createElement('script');
        wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
            '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
        wf.type = 'text/javascript';
        wf.async = 'true';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(wf, s);
      })();
</script>





</html>