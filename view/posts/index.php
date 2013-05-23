
<div class="page-header">
	<h1>Le Blog</h1>
</div>
<?php foreach($posts as $k=>$v): ?>
	
		<h2><?php echo $v->name;?></h2>
		<p><?php echo $v->content;?></p>
		<a href='<?php echo Router::url("posts/view/id:{$v->id}/slug:$v->slug");?>' >Lire la suite &rarr;</a>
<?php endforeach;?>

<div class="pagination">
  <ul>

<?php for($i=1; $i <= $nbpage; $i++):?>
		<li <?php if($i==$this->request->page) echo 'class="active"';?>
		><a href="?page=<?php echo $i;?>"><?php echo $i;?></a></li>
<?php endfor; ?>
	
    
  </ul>
</div>