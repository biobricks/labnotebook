<?php

if (!defined('MEDIAWIKI')) { exit; }

$wgExtensionCredits['specialpage'][] = array(
    'path' => __FILE__,
    'name' => 'LabNotebook2',
    'version' => '0.1',
    'author' => 'Yardena Cohen',
    'url' => 'https://openwetware.org/',
    'descriptionmsg' => "labnotebook-desc",
    'license-name' => 'GPL3'
);

class SpecialLabNotebook2 extends SpecialPage {
    function __construct() {
        parent::__construct( 'LabNotebook2' );
    }

    function execute( $par ) {
        $request = $this->getRequest();
        $output = $this->getOutput();
        $this->setHeaders();
        $param = $request->getText( 'param' ); # Get request data from, e.g.
        $output->addWikiText('This will (soon) implement the Lab Notebook extension.');
    }
}
?>
