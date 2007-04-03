<?php if(!empty($_error)): ?>
<div id="formbox">
	<div id="formbg">
		<div id="messagebox">
			<ul id="warningbox"><li>.</li></ul>
			<ul id="noticebox"><li>.</li></ul>
		</div>
	</div>
	<div id="buttonbg">
		<div id="opensubmitbutton"><?php echo l10n::_('Back'); ?></div>
	</div>
</div>
<script type="text/javascript"><![CDATA[
window.addEventListener('load', function () {
	addWarning('<?php echo $_error; ?>');
	$('opensubmitbutton').addEventListener('click', function () { history.go(-1) }, false);
}, false);
]]></script>
<?php endif; ?>