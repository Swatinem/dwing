<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
Screenshot::incrementViews($_GET['pic_id']);
?>
<results>
<?php
$tags = Tags::getTagsForContent($_GET['pic_id'], ContentType::IMAGE);
foreach($tags as $tag):
?>
	<result tag_id="<?php echo $tag['tag_id']; ?>" name="<?php echo htmlspecialchars($tag['name']); ?>"></result>
<?php
endforeach;
?>
</results>