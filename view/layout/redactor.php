<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<?php $this->loadCSS();?>
	<?php $this->loadJS();?>	
	<title><?php echo isset($title_for_layout)?$title_for_layout : Conf::$website;?></title>
	
</head>
<body data-user_id="<?php echo Session::user()->getID(); ?>">


	<header class="navbar navbar-fixed-top">
	  <nav class="navbar-inner">
	    <div class="container">
      		<a class="brand" href="<?php echo Router::url('pages/view/4/homepage');?>">
	      	  	<?php echo Conf::$website;?>
			</a>
			

			<ul class="nav">		
				
				<?php
				//Admin section button
				if(Session::user('role')=='admin'):?>
				<li><a href="<?php echo Router::url('admin/posts/index');?>">Admin.</a></li>
				<?php endif;

				
				
				?>

				
			</ul>
		

			<ul class="nav pull-right">

				<li><a href="?lang=fr"><i class="flag flag-fr"></i></a></li>
				<li><a href="?lang=en"><i class="flag flag-uk"></i></a></li>

				<?php if (Session::user()->isLog()): ?>
					<li><a href="<?php echo Router::url('redactor/home');?>">
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
						<input type="login" name="login" required="required" placeholder="Login or email" autofocus="autofocus" value="admin"/>
						<input type="password" name="password" required="required" placeholder="Password" value="fatboy" />
						<input type="hidden" name="token" value="<?php echo Session::token();?>" />
						<input type="submit" value="OK" />
					</form>
					<li><a href="<?php echo Router::url('users/login');?>">Login</a></li>	
					<li><a href="<?php echo Router::url('users/register');?>" >Inscription</a></li>


				<?php endif ?>

			</ul>
		</div>
	  </nav>
	</header>

	<section>
		<div class="container mainContainer">	
			<?php echo Session::flash(); ?>
			
			<?php echo $content_for_layout;?>

		</div>
	</section>


	<div class="modal fade" id="myModal"></div>

	<footer class="footer">
	</footer>

</body>



 <script type="text/javascript">

 	/*===========================================================
 		Set security token
 	============================================================*/
 	var CSRF_TOKEN = '<?php echo Session::token(); ?>';

</script>





</html>