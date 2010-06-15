<?php
$title = l10n::_('Legal Notice');
include($this->template('header.tpl.php'));
?>
<div class="area">
	<h1><?php echo $title; ?></h1>
<?php
switch(l10n::$lang['general'])
{
	case 'de':
?>
<h2>Eingesetzte Software</h2>
<p>
<abbr title="die Welt ist nicht gerecht">dWing</abbr> <abbr title="Content Management System">CMS</abbr> als
<abbr title="General Public License">GPL</abbr> frei erhältlich unter <a href="http://dwing.swatinem.de">dwing.swatinem.de</a><br />
dWing benutzt den <a href="http://fckeditor.net">FCKeditor</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) um uneingeschränkte
Freiheit bei der Gestaltung der Beiträge zu gewährleisten.<br />
dWing benutzt den <a href="http://htmlpurifier.org/">HTMLPurifier</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) um dennoch Schadcode rauszufiltern.<br />
dWing benutzt <a href="http://jquery.com/">jQuery</a>
(<abbr title="Massachusetts Institute of Technology">MIT</abbr> und
<abbr title="General Public License">GPL</abbr>) als ECMAScript Hilfsbibliothek.<br />
dWing benutzt <a href="http://www.json.org/json2.js">JSON2</a>
(Public Domain) als Hilfsbibliothek für Browser die noch kein natives JSON beherrschen.
</p>
<?php
	break;
	default: // en
?>
<h2>Software in use</h2>
<p>
<abbr title="die Welt ist nicht gerecht">dWing</abbr> <abbr title="Content Management System">CMS</abbr> is freely available under
the terms of the <abbr title="General Public License">GPL</abbr> from <a href="http://dwing.swatinem.de">dwing.swatinem.de</a><br />
dWing uses the <a href="http://fckeditor.net">FCKeditor</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) to provide unlimited freedom in the styling of the postings<br />
dWing uses <a href="http://htmlpurifier.org/">HTMLPurifier</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) to protect against malicious code inside user postings.<br />
dWing uses <a href="http://jquery.com/">jQuery</a>
(<abbr title="Massachusetts Institute of Technology">MIT</abbr> and
<abbr title="General Public License">GPL</abbr>) as ECMAScript helper library.<br />
dWing uses <a href="http://www.json.org/json2.js">JSON2</a>
(Public Domain) as helper library for browsers which to not have native JSON support.
</p>
<?php
	break;
}
?>
</div>
<?php
include($this->template('footer.tpl.php'));
?>
