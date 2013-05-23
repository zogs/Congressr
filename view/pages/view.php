<?php 

//Title for brwoser
$title_for_layout = $page->title;


echo Session::flash();

?>

<div class="hero-unit">	
	<?php echo $page->content; ?>
</div>