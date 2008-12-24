<?php
include($this->template('header.tpl.php'));

// TODO: resurrect browsing using newer posts / older posts links
// TODO: better tag cloud?
// TODO: resurrect posting
// TODO: implement voting as first RESTful service
// TODO: resurrect comment posting

$iter = new NewsRange();

foreach($iter as $post):
	include($this->template('post.tpl.php'));
endforeach;
include($this->template('footer.tpl.php'));
?>
