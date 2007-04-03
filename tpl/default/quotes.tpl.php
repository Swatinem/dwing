<?php
$title = l10n::_('Quotes');
include($this->template('header.tpl.php'));
?>
<?php if($user->hasright('quotes')): ?>
<div id="formbox">
	<div id="formbg">
		<div id="formcontent">
			<form action="" id="form">
			<fieldset>
				<legend id="legend"><?php echo l10n::_('new Quote'); ?></legend>
				<label><input type="text" id="quote" /><?php echo l10n::_('Quote'); ?></label>
				<label><input type="text" id="source" /><?php echo l10n::_('Source'); ?></label>
				<input type="submit" class="fake" />
			</fieldset>
			</form>
		</div>
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="closeform"></div>
		<div id="opensubmitbutton"><?php echo l10n::_('new Quote'); ?></div>
	</div>
</div>
<script type="text/javascript" src="js/quotes.js"></script>
<?php endif; ?>
<h1><?php echo l10n::_('Quotes'); ?></h1>
<?php
$quotes = Quote::getQuotes();
foreach($quotes as $quote):
?>
<p id="quote<?php echo $quote['quote_id']; ?>">
	<?php echo htmlspecialchars($quote['quote']); ?>
	<br /><em>- <?php echo htmlspecialchars($quote['source']); ?></em>
	<?php if($user->hasright('quotes')): ?>
	<a class="image" href="javascript:editQuote(<?php echo $quote['quote_id']; ?>);"><img src="images/tango-edit.png" alt="edit" /></a>
	<a class="image" href="javascript:deleteQuote(<?php echo $quote['quote_id']; ?>);"><img src="images/tango-delete.png" alt="delete" /></a>
	<?php endif; ?>
</p>
<?php endforeach; ?>
<?php include($this->template('footer.tpl.php')); ?>