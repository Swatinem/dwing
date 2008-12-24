<?php
include($this->template('header.tpl.php'));

// TODO: resurrect browsing using newer posts / older posts links
// TODO: better tag cloud?
// TODO: resurrect posting
// TODO: implement voting as first RESTful service
// TODO: resurrect comment posting

$displayPerPage = 10;
$page = Utils::getPage();

$iter = new NewsRange(($page-1)*$displayPerPage, $displayPerPage);

foreach($iter as $post):
	include($this->template('post.tpl.php'));
endforeach;

if(($count = count($iter)) > $displayPerPage):
?>
<hr />
<div class="area">
	<div class="col2">
		<?php if($page > 1): ?>
		<a href="?page=<?php echo $page-1; ?>">neuere Einträge</a>
		<?php endif; ?>
	</div>
	<div class="col2 right">
		<?php if($count > $page*$displayPerPage): ?>
		<a href="?page=<?php echo $page+1; ?>">ältere Einträge</a>
		<?php endif; ?>
	</div>
</div>
<?php
endif;
include($this->template('footer.tpl.php'));
?>
