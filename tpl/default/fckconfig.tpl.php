<?php header('Content-Type: text/javascript; charset=utf-8'); ?>
/*
 * Config for FCKeditor
 */
FCKConfig.ToolbarSets = {};
FCKConfig.ToolbarSets['dWing'] = [
	['Source','ShowBlocks','Save',/*'Preview',*/'-','SelectAll','RemoveFormat','-',
		'Bold','Italic','Subscript','Superscript','-',
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
