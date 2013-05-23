<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <?php $this->loadCSS();?>
        <?php $this->loadJS();?>         
        <script type="text/javascript" src="<?php echo Router::webroot('js/jquery/tiny_mce/tiny_mce.js'); ?>"></script>       
        <title><?php echo isset($title_for_layout)?$title_for_layout : 'Admin.';?></title>
        
</head>
<body>

        <div class="navbar navbar-inverse navbar-fixed-top">
          <div class="navbar-inner">
            <div   class="container">
              <a class="brand" href="<?php echo Router::url('pages/home'); ?>">
                                Admin.
                        </a>                       

                        <ul class="nav">
                                <li><a href="<?php echo Router::url('/'); ?>">Voir le site</a></li>                               
                                <li><a href="<?php echo Router::url('admin/pages/index'); ?>">Pages</a></li>
                                 <li><a href="<?php echo Router::url('admin/users/index'); ?>">Users</a></li>
                                 <li><a href="<?php echo Router::url('admin/articles/index/resume');?>">Resume</a></li>
                                <li><a href="<?php echo Router::url('admin/logout'); ?>">Deconnexion</a></li>
                                
                        </ul>

                        <ul class="nav pull-right">
                            <?php if (Session::user()): ?>
                                <li><a href="<?php echo Router::url('users/thread');?>">
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

                            <?php endif ?>
                        </ul>
                </div>
          </div>
        </div>

        <div class="container mainContainer">

                <?php echo Session::flash();?>
                <?php echo $content_for_layout;?>
        </div>
</body>

<script type="text/javascript">
tinyMCE.init({
        // General options
        mode : "specific_textareas",
        editor_selector : "wysiwyg",
        theme : "advanced",
        plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Skin options
        skin : "o2k7",
        skin_variant : "silver",

        // Absolute URL
        relative_urls : false,
        remove_script_host : false,
        document_base_url : "http://climatologie.u-bourgogne.fr/ecole/",

        // Example content CSS (should be your site CSS)
        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js",

        // Replace values for the template plugin
        template_replace_values : {
                username : "Some User",
                staffid : "991234"
        }
});
</script>

</html>