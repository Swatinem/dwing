<?php
header('Content-Type: text/javascript; charset=utf-8');
// make the browser cache this file
header('Cache-Control: max-age=172800, public, must-revalidate');
header('Pragma: cache');
$oldLocale = setlocale(LC_ALL, 0);
setlocale(LC_ALL, 'C');
header(strftime('Expires: %a, %d %b %Y %T GMT', time()+172800));
setlocale(LC_ALL, $oldLocale);
?>
/*
 * Config for FCKeditor
 */
FCKConfig.ToolbarSets = {};
FCKConfig.ToolbarSets['dWing'] = [
	['Source','ShowBlocks','Save','-','SelectAll','RemoveFormat','-',
		'Bold','Italic','-',
		'OrderedList','UnorderedList','Blockquote','Rule','-',
		'Link','Unlink','-','About']
];
FCKConfig.ProcessHTMLEntities = false;
FCKConfig.BrowserContextMenuOnCtrl = true;
FCKConfig.FirefoxSpellChecker = true;
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/silver/';
FCKConfig.EditorAreaCSS = '/css';
FCKConfig.EditorAreaStyles = 'body { width: 33em; margin: auto; } p:first-child { margin-top: 0; }';
FCKConfig.StartupShowBlocks = false; // maybe want to enable this?
