<?php
$title = l10n::_('Legal Notice');
include($this->template('header.tpl.php'));
?>
<div class="area">
	<h1><?php echo $title; ?></h1>
<?php
switch(l10n::$lang)
{
	case 'de':
		// TODO: something that is not dWing specific?
?>
<h2>Eingesetzte Software</h2>
<p>
<abbr title="die Welt ist nicht gerecht">dWing</abbr> <abbr title="Content Management System">CMS</abbr> als
<abbr title="General Public License">GPL</abbr> frei erhältlich unter <a href="http://swatinemz.sf.net">swatinemz.sf.net</a><br />
dWing benutzt den <a href="http://fckeditor.net">FCKeditor</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) um uneingeschränkte
Freiheit bei der Gestaltung der Beiträge zu gewährleisten.<br />
dWing benutzt den <a href="http://hp.jpsband.org/">HTMLPurifier</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) um dennoch Schadcode rauszufiltern.<br />
dWing benutzt <a href="http://mootools.net/">jQuery</a>
(<abbr title="Massachusetts Institute of Technology">MIT</abbr> und
<abbr title="General Public License">GPL</abbr>) als ECMAScript Hilfsbibliothek.<br />
dWing benutzt <a href="http://www.json.org/json2.js">JSON2</a>
(Public Domain) als Hilfsbibliothek für Browser die noch kein natives JSON beherrschen.
</p>

<h2>Betrieben/Verwaltet und Erstellt von</h2>
<p>
Arpad Borsos aka "Swatinem"<br />
Mozartstraße 4<br />
D-83435 Bad Reichenhall<br />
<a href="mailto:arpad.borsos_at_googlemail_dot_com">arpad.borsos_at_googlemail_dot_com</a><br />
Alle Quellcodes (sofern nicht extra angegeben) und zur Seite gehörenden Grafiken unterliegen dem Copyright des Erstellers.
</p>

<h2>Haftungsausschluss / Disclaimer</h2>
<p>
1. Inhalt des Online-Angebotes<br />
Der Autor übernimmt keinerlei Gewähr für die Aktualität, Korrektheit, Vollständigkeit oder Qualität der bereitgestellten Informationen. Haftungsansprüche gegen den Autor, welche sich auf Schäden materieller oder ideeller Art beziehen, die durch die Nutzung oder Nichtnutzung der dargebotenen Informationen bzw. durch die Nutzung fehlerhafter und unvollständiger Informationen verursacht wurden, sind grundsätzlich ausgeschlossen, sofern seitens des Autors kein nachweislich vorsätzliches oder grob fahrlässiges Verschulden vorliegt. Alle Angebote sind freibleibend und unverbindlich. Der Autor behält es sich ausdrücklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ankündigung zu verändern, zu ergänzen, zu löschen oder die Veröffentlichung zeitweise oder endgültig einzustellen.
</p>
<p>
2. Verweise und Links<br />
Bei direkten oder indirekten Verweisen auf fremde Internetseiten ("Links"), die außerhalb des Verantwortungsbereiches des Autors liegen, würde eine Haftungsverpflichtung ausschließlich in dem Fall in Kraft treten, in dem der Autor von den Inhalten Kenntnis hat und es ihm technisch möglich und zumutbar wäre, die Nutzung im Falle rechtswidriger Inhalte zu verhindern. Der Autor erklärt hiermit ausdrücklich, dass zum Zeitpunkt der Linksetzung keine illegalen Inhalte auf den zu verlinkenden Seiten erkennbar waren. Auf die aktuelle und zukünftige Gestaltung, die Inhalte oder die Urheberschaft der gelinkten/verknüpften Seiten hat der Autor keinerlei Einfluss. Deshalb distanziert er sich hiermit ausdrücklich von allen Inhalten aller gelinkten /verknüpften Seiten, die nach der Linksetzung verändert wurden. Diese Feststellung gilt für alle innerhalb des eigenen Internetangebotes gesetzten Links und Verweise sowie für Fremdeinträge in vom Autor eingerichteten Gästebüchern, Diskussionsforen und Mailinglisten. Für illegale, fehlerhafte oder unvollständige Inhalte und insbesondere für Schäden, die aus der Nutzung oder Nichtnutzung solcherart dargebotener Informationen entstehen, haftet allein der Anbieter der Seite, auf welche verwiesen wurde, nicht derjenige, der über Links auf die jeweilige Veröffentlichung lediglich verweist.
</p>
<p>
3. Urheber- und Kennzeichenrecht<br />
Der Autor ist bestrebt, in allen Publikationen die Urheberrechte der verwendeten Grafiken, Tondokumente, Videosequenzen und Texte zu beachten, von ihm selbst erstellte Grafiken, Tondokumente, Videosequenzen und Texte zu nutzen oder auf lizenzfreie Grafiken, Tondokumente, Videosequenzen und Texte zurückzugreifen. Alle innerhalb des Internetangebotes genannten und ggf. durch Dritte geschützten Marken- und Warenzeichen unterliegen uneingeschränkt den Bestimmungen des jeweils gültigen Kennzeichenrechts und den Besitzrechten der jeweiligen eingetragenen Eigentümer. Allein aufgrund der bloßen Nennung ist nicht der Schluss zu ziehen, dass Markenzeichen nicht durch Rechte Dritter geschützt sind! Das Copyright für veröffentlichte, vom Autor selbst erstellte Objekte bleibt allein beim Autor der Seiten. Eine Vervielfältigung oder Verwendung solcher Grafiken, Tondokumente, Videosequenzen und Texte in anderen elektronischen oder gedruckten Publikationen ist ohne ausdrückliche Zustimmung des Autors nicht gestattet.
</p>
<p>
4. Datenschutz<br />
Sofern innerhalb des Internetangebotes die Möglichkeit zur Eingabe persönlicher oder geschäftlicher Daten (E-Mail-Adressen, Namen, Anschriften) besteht, so erfolgt die Preisgabe dieser Daten seitens des Nutzers auf ausdrücklich freiwilliger Basis. Die Inanspruchnahme und Bezahlung aller angebotenen Dienste ist - soweit technisch möglich und zumutbar - auch ohne Angabe solcher Daten bzw. unter Angabe anonymisierter Daten oder eines Pseudonyms gestattet.
</p>
<p>
5. Rechtswirksamkeit dieses Haftungsausschlusses<br />
Dieser Haftungsausschluss ist als Teil des Internetangebotes zu betrachten, von dem aus auf diese Seite verwiesen wurde. Sofern Teile oder einzelne Formulierungen dieses Textes der geltenden Rechtslage nicht, nicht mehr oder nicht vollständig entsprechen sollten, bleiben die übrigen Teile des Dokumentes in ihrem Inhalt und ihrer Gültigkeit davon unberührt.
</p>
<?php
	break;
	default: // en
?>
<h2>Software in use</h2>
<p>
<abbr title="die Welt ist nicht gerecht">dWing</abbr> <abbr title="Content Management System">CMS</abbr> is freely available under
the terms of the <abbr title="General Public License">GPL</abbr> from <a href="http://swatinemz.sf.net">swatinemz.sf.net</a><br />
dWing uses the <a href="http://fckeditor.net">FCKeditor</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) to provide unlimited freedom in the styling of the postings<br />
dWing uses <a href="http://hp.jpsband.org/">HTMLPurifier</a>
(<abbr title="Lesser General Public License">LGPL</abbr>) to protect against malicious code inside user postings.<br />
dWing uses <a href="http://mootools.net/">jQuery</a>
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
