<?php
if(!empty($_GET['tag']))
	$title = l10n::_('News').': '.$_GET['tag'];
elseif(!empty($_GET['news_id']))
{
	$newsall = News::getNews(10, (!empty($_GET['tag']) ? $_GET['tag'] : null));
	$title = $newsall[0]['title'];
}
else
	$title = l10n::_('News');
include($this->template('header.tpl.php'));
?>
<script type="text/javascript" src="js/ratings.js"></script>
<script type="text/javascript" src="./FCKeditor/fckeditor.js"></script>
<?php
if(empty($_GET['news_id'])):
	if(empty($_GET['tag']))
		$pages = Utils::pages(Tags::getContentCount(null, ContentType::NEWS), 10, '?page=');
	else
		$pages =  Utils::pages(Tags::getContentCount($_GET['tag'], ContentType::NEWS), 10, 'news/tags/'.$_GET['tag'].'?page=');
?>
<h1><?php echo l10n::_('News').(!empty($_GET['tag']) ? ': '.htmlspecialchars($_GET['tag']) : ''); ?></h1>
<div class="openid">
	<div style="float: right;">
		<h3><a href="./"><?php echo l10n::_('Show all posts'); ?></a></h3>
		<div style="font-size: 0.75em;">
		<?php
		if(!empty($pages))
			echo $pages;
		?>
		</div>
	</div>
	<h3><?php echo l10n::_('Show only posts with this tag:'); ?></h3>
	<div class="tagcloud">
	<?php
	$newsTags = Tags::getTagsWithContentOfType(ContentType::NEWS);
	$max = sqrt($newsTags[0]['content']);
	$min = sqrt($newsTags[0]['content']);
	for($i = 0, $imax = count($newsTags); $i < $imax; $i++)
	{
		$newsTags[$i]['score'] = sqrt($newsTags[$i]['content']);
		if($newsTags[$i]['score'] > $max)
			$max = $newsTags[$i]['score'];
		if($newsTags[$i]['score'] < $min)
			$min = $newsTags[$i]['score'];
	}
	$max-= $min;
	foreach($newsTags as $newsTag):
	?>
	<a class="p<?php echo 10*ceil(10*($newsTag['score']-$min)/$max) ?>" href="news/tags/<?php echo $newsTag['name']; ?>" title="<?php printf(l10n::_('%d posts'),$newsTag['content']); ?>"><?php echo $newsTag['name']; ?></a>
	<?php endforeach; ?>
	</div>
</div>
<?php
endif; // empty(get news_id)
?>
<?php if($user->hasRight('news') && empty($_GET['news_id'])): ?>
<script type="text/javascript" src="js/news.js"></script>
<form action="" id="newsform">
	<div class="post">
		<div class="dateinfo">
			<span class="month"><?php echo utf8_encode(strftime('%B', time())); ?></span>
			<span class="day"><?php echo strftime('%d', time()); ?></span>
			<span class="year"><?php echo strftime('%Y', time()); ?></span>
		</div>
		<input type="submit" value="<?php echo l10n::_('Publish'); ?>" />
		<button id="newspreview"><?php echo l10n::_('Preview'); ?></button>
		<h2>
			<input type="text" id="newstitle" value="<?php echo l10n::_('Title'); ?>" />
			<span id="newstitlepreview"></span>
		</h2>
		<div class="postinfo">
			<input type="text" id="newstags" value="<?php echo l10n::_('tags'); ?>" />
			<span id="newstagspreview"><a></a></span>
			</div>
		<div class="postbody">
			<div id="newspreviewtext" style="display: none;">
			</div>
			<div>
				<textarea id="newstext" rows="10" cols="30"></textarea>
			</div>
		</div>
		<div class="warningbox" id="newswarningbox">
			<ul>
				<li id="newswarning">.</li>
			</ul>
		</div>
	</div>
</form>
<?php endif; ?>
<?php
if(empty($newsall))
	$newsall = News::getNews(10, (!empty($_GET['tag']) ? $_GET['tag'] : null));
foreach($newsall as $news):
?>
	<div class="post">
		<div class="dateinfo">
			<span class="month"><?php echo utf8_encode(strftime('%B', $news['time'])); ?></span>
			<span class="day"><?php echo strftime('%d', $news['time']); ?></span>
			<span class="year"><?php echo strftime('%Y', $news['time']); ?></span>
			<?php /*<span class="time"><?php echo strftime('%H:%M', $news['time']); ?></span>*/ ?>
		</div>
		<?php
		$thisRating = Ratings::getRating($news['news_id'], ContentType::NEWS);
		$idStr = 'rating-'.$news['news_id'].'-'.ContentType::NEWS;
		$jsParams = '\''.$idStr.'\', '.$news['news_id'].', '.ContentType::NEWS;
		?>
		<div class="rating" id="<?php echo $idStr; ?>">
			<div style="width: <?php echo round($thisRating['average']/5*100); ?>px;"></div>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 1);">1</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 2);">2</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 3);">3</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 4);">4</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 5);">5</a>
		</div>
		<span class="ratingcaption"><?php printf(l10n::_('%s ratings / %s average'), $thisRating['ratings'], round($thisRating['average'], 1)); ?></span>
		<h2><a href="news/<?php echo $news['fancyurl']; ?>"><?php echo htmlspecialchars($news['title']); ?></a></h2>
		<div class="postinfo">
		<?php
		$tags = Tags::getTagsForContent($news['news_id'], ContentType::NEWS);
		foreach($tags as $tag):
		?>
		<a href="news/tags/<?php echo $tag; ?>"><?php echo $tag; ?></a>
		<?php
		endforeach;
		$commentNum = Comments::getCommentNum($news['news_id'], ContentType::NEWS);
		if($commentNum > 0):
			printf(l10n::_('→ %d comments'), $commentNum);
		endif;
		?>
		</div>
		<div class="postbody">
<?php echo Smilies::replace($news['text']); ?>
		</div>
	</div>
<?php endforeach; ?>
<?php if(!empty($_GET['news_id'])): ?>
<h1><?php echo l10n::_('Comments'); ?></h1>
<?php
$comments = Comments::getComments($news['news_id'], ContentType::NEWS);
foreach($comments as $comment):
?>
	<div class="post">
		<div class="dateinfo">
			<span class="month"><?php echo utf8_encode(strftime('%B', $comment['time'])); ?></span>
			<span class="day"><?php echo strftime('%d', $comment['time']); ?></span>
			<span class="year"><?php echo strftime('%Y', $comment['time']); ?></span>
			<!--<span class="time"><?php echo strftime('%H:%M', $comment['time']); ?></span>-->
		</div>
		<?php
		$thisRating = Ratings::getRating($comment['comment_id'], ContentType::COMMENT);
		$idStr = 'rating-'.$comment['comment_id'].'-'.ContentType::COMMENT;
		$jsParams = '\''.$idStr.'\', '.$comment['comment_id'].', '.ContentType::COMMENT;
		?>
		<div class="rating" id="<?php echo $idStr; ?>">
			<div style="width: <?php echo round($thisRating['average']/5*100); ?>px;"></div>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 1);">1</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 2);">2</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 3);">3</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 4);">4</a>
			<a href="javascript:doRating(<?php echo $jsParams; ?>, 5);">5</a>
		</div>
		<span class="ratingcaption"><?php printf(l10n::_('%s ratings / %s average'), $thisRating['ratings'], round($thisRating['average'], 1)); ?></span>
		<h2><a href="user/<?php echo $comment['user']->user_id; ?>"><?php echo htmlspecialchars($comment['user']->nick); ?></a></h2>
		<div class="postbody">
<?php echo Smilies::replace($comment['text']); ?>
		</div>
	</div>
<?php endforeach; ?>
<?php if($user->authed): ?>
<script type="text/javascript" src="js/comments.js"></script>
<form id="commentform" action="">
	<input type="hidden" id="content_id" value="<?php echo $news['news_id']; ?>" />
	<input type="hidden" id="content_type" value="<?php echo ContentType::NEWS; ?>" />
	<div class="post">
		<div class="dateinfo">
			<span class="month"><?php echo utf8_encode(strftime('%B', time())); ?></span>
			<span class="day"><?php echo strftime('%d', time()); ?></span>
			<span class="year"><?php echo strftime('%Y', time()); ?></span>
		</div>
		<input type="submit" value="<?php echo l10n::_('Publish'); ?>" />
		<button id="commentpreview"><?php echo l10n::_('Preview'); ?></button>
		<h2><a href="user/<?php echo $user->user_id; ?>"><?php echo htmlspecialchars($user->nick); ?></a></h2>
		<div class="postbody">
			<div id="commentpreviewtext" style="display: none;">
			</div>
			<div>
				<textarea id="commenttext" rows="10" cols="30"></textarea>
			</div>
		</div>
	</div>
</form>
<?php else: ?>
<ul class="warning">
	<li><?php echo l10n::_('You need to <a href="login?returnto=1">sign in</a> first.'); ?></li>
</ul>
<?php endif; ?>
<?php endif; ?>
<?php include($this->template('footer.tpl.php')); ?>