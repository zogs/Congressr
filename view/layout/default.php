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

				
				//Recuperation du Menu
				//Appel de ma methode getMenu du controlleur Pages
				
				$pagesMenu = $this->call('Pages','getMenu');
				debug($pagesMenu);
				foreach ($pagesMenu as $v) : ?>				
					<li><a href='<?php echo Router::url("pages/view/$v->id/$v->slug");?>' ><?php echo $v->title; ?></a></li>
				<?php 
				endforeach;
				?>

				<?php

				//Admin section button
				if(Session::user()->getRole()=='admin'):?>
				<li><a href="<?php echo Router::url('admin/pages/index');?>">Admin.</a></li>
				<?php endif;
				if(Session::user()->getRole()=='reviewer'):?>
				<li><a href="<?php echo Router::url('reviewer/board');?>">Reviewer.</a></li>
				<?php endif;
				if(Session::user()->getRole()=='redactor'):?>
				<li><a href="<?php echo Router::url('redactor/board');?>">Redactor.</a></li>
				<?php endif;
				
				?>

				
			</ul>
		

			<ul class="nav pull-right">

				<li><a href="?lang=fr"><i class="flag flag-fr"></i></a></li>
				<li><a href="?lang=en"><i class="flag flag-uk"></i></a></li>

				<?php if (Session::user()->isLog()): ?>
					<li><a href="<?php echo Router::url('pages/home');?>">
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

	<section class="container mainContainer">	
			
		<?php echo $content_for_layout;?>
	</section>


	<div class="modal fade" id="myModal"></div>

	<footer id="footer">
		<div id="foo-one"></div>
		<div id="foo-sign"><img src="<?php echo Router::webroot('img/sign.png');?>" alt=""></div>
		<div id="foo-two">
			<div class="container foo-content">				
				<div class="fright">YouProtest</div>
			</div>
		</div>
	</footer>

	<style>
		#footer { position:fixed; bottom:-120px; height:150px; z-index:20;width:100%; background-color: rgba(0,0,0,0.1);}
		#foo-one { position:absolute; top:30px; height:120px;  z-index:-20; width:100%; background-color: rgba(0,0,0,0.5);}
		#foo-two { position:absolute; top:30px; z-index:-10; width:100%; background-color: rgba(0,0,0,1);}
		#foo-sign {position:absolute; top:30px; height: 250px; left:30%; z-index:-15;}
		#foo-sign img {height:200px;}

		.foo-content{ padding:10px 0;}
	</style>

</body>



 <script type="text/javascript">

 	$(document).ready(function(){


 		var timeout;
 		var opened = false;
 		$("#footer").hover(
			function () {

				timeout = setTimeout(function(){ 

					if(opened==false){
						$('#foo-two').animate({top:'-='+$('#foo-two').height()}, 200, 'linear');
						$('#foo-one').delay(100).animate({top:'-='+$('#foo-one').height()}, 200, 'linear');
						$('#foo-sign').delay(200).animate({top:'-='+$('#foo-sign').height()}, 300, 'linear',function(){ opened = true; });
					}
				},500);
			},
			function () {

				clearTimeout(timeout);
				if(opened==true){
					
					$('#foo-two').animate({top:'+='+$('#foo-two').height()}, 500);
					$('#foo-one').animate({top:'+='+$('#foo-one').height()}, 400);
					$('#foo-sign').animate({top:'+='+$('#foo-sign').height()}, 800, 'linear',function(){ opened=false });
				}
			}
			);

 	});

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