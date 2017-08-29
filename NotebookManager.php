<?php
if (!defined('MEDIAWIKI')) die();

// for now only run on the main oww site and not in private wikis 
if (isset($wgOpenwetware) && $wgOpenwetware){
	require_once (dirname(__FILE__)."/CreateNotebook.php");
	$wgExtensionFunctions[] = 'wfNotebookManager';
	$wgExtensionCredits['specialpage'][] = array(
		'name' => 'NotebookManager',
		'author' => 'Bill Flanagan');
}

function wfNotebookManager() {
	global $IP, $wgMessageCache;
	global $wgOpenwetware;

	if (!$wgOpenwetware){
		return;
	}
	
	$wgMessageCache->addMessages(
		array(
			'notebookmanager' => 'NotebookManager',
		)
	);

	require_once "$IP/includes/SpecialPage.php";

	class NotebookManager extends SpecialPage {
		/**
		 * Constructor
		 */
		function NotebookManager() {
			SpecialPage::SpecialPage( 'NotebookManager' );
			$this->includable( true );
		}

		/**
		 * main()
		 */
		function execute( $par = null ) {
			global $wgOut;

			$cn = new CreateNotebook();
			$cn->viewLog();
		}

	}
	
	SpecialPage::addPage( new NotebookManager );
}
