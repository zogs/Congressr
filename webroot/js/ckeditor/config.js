/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.language = 'en';
	config.toolbar_Full =
		[
			{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
			{ name: 'document', items : [ 'Source'] },	
			{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-',
			'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
			'/',
			{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike' ] },							
			{ name: 'styles', items : [ 'FontSize' ] },
			{ name: 'colors', items : [ 'TextColor','BGColor' ] },
			{ name: 'links', items : [ 'Link','Unlink' ] },
			{ name: 'insert', items : [ 'Image','Table','HorizontalRule','Smiley', ] }
				
				
			
		];


};
