<?php

if (!defined('MEDIAWIKI')) exit;

wfDebug("SpecialRedir: loaded class and init complete\n");

class Redir extends SpecialPage{

    function __construct() {
        parent::__construct( 'Redir' );
    }

    function addMonth($base, $date){
        global $wgOut, $wgUser;

	$month = substr($date, 0, 7);
        $name = "$base/$month";
        wfDebug("SpecialRedir::addMonth: $name\n");

        $t = Title::newFromText($name);
        if (!$t->exists() && $wgUser->isLoggedIn()){
            $text = '';
            $bt = Title::newFromText("$base/Monthly_Base");
            if ($bt->exists()){
                // Yes. Use Notebook's default!
                $ba = new Article($bt);
                $text = $ba->getContent();
            }else{
                // No. Use system default
                $bt = Title::newFromText("MediaWiki:Monthly_Base");
                if ($bt->exists()){
                    $ba = new Article($bt);
                    $text = $ba->getContent();
                }
            }
            $m = substr($date, 5, 2);
            $y = substr($date, 0, 4);
            $text = str_replace ("#last_month", $m - 1, $text);
            $text = str_replace ("#this_month", $m, $text);
            $text = str_replace ("#next_month", $m + 1, $text);
            $text = str_replace ("#year", $y, $text);
            $a = new Article($t);
            $a->doEdit($text, "Autocreate Month $month Entry for $base", EDIT_NEW);
            $this->addYear($base, $date);
       }
       return $t;
    }

    function addYear($base, $date){
        global $wgOut, $wgUser;

	$year = substr($date, 0, 4);
        $name = "$base/$year";
        wfDebug("SpecialRedir::addYear: $name\n");

        $t = Title::newFromText($name);
        if (!$t->exists() && $wgUser->isLoggedIn()){
            $text = '';
            $bt = Title::newFromText("$base/Yearly_Base");
            if ($bt->exists()){
                // Yes. Use Notebook's default!
                $ba = new Article($bt);
                $text = $ba->getContent();
            }else{
                // No. Use system default
                $bt = Title::newFromText("MediaWiki:Yearly_Base");
                if ($bt->exists()){
                    $ba = new Article($bt);
                    $text = $ba->getContent();
                }
            }
            $text = str_replace ("#year", $year, $text);

            $a = new Article($t);
            $a->doEdit($text, "Autocreate Year $year Entry for $base", EDIT_NEW);
       }
       return $t;
    }

    function addEntry($base, $date){
        global $wgOut, $wgUser;

        $name = "$base/$date";
        wfDebug("SpecialRedir::addEntry: $name\n");

        $t = Title::newFromText($name);
        if (!$t->exists() && $wgUser->isLoggedIn()){
            $text = '';
            $bt = Title::newFromText("$base/Entry_Base");
            if ($bt->exists()){
                // Yes. Use Notebook's default!
                $ba = new Article($bt);
                $text = $ba->getContent();
            }else{
                // No. Use system default
                $bt = Title::newFromText("MediaWiki:EntryContentDefault");
                if ($bt->exists()){
                    $ba = new Article($bt);
                    $text = $ba->getContent();
                }
            }
            $a = new Article($t);
            $a->doEdit($text, "Autocreate $date Entry for $base", EDIT_NEW);
            $this->addMonth($base, $date);
       }
       return $t;
    }

    function getBase($wikipage){
            // explode into parts
            $parts = explode("/", $wikipage);
            $baseParts = count($parts) - 3;
            if ($baseParts < 1 )
                return $wikipage;
            wfDebug ("SpecialRedir::getBase: baseParts: $baseParts\n");

            // isolate base
            $base = array();
            for ($i = 0; $i < $baseParts; $i++)
                $base[] = $parts[$i];
            $baseText = implode ("/", $base);
            wfDebug ("SpecialRedir::getbase: baseText: $baseText\n");
            return $baseText; 
    }

    function getDate($wikipage){
            // explode into parts
            $parts = explode("/", $wikipage);
            $baseParts = count($parts) - 3;
            if ($baseParts < 1 )
                return '';
            wfDebug ("SpecialRedir::getDate: baseParts: $baseParts\n");

            // isolate date
            $date = array();
            for ($i = $baseParts; $i < ($baseParts + 3); $i++)
               $date[] = $parts[$i];
            $dateText = implode ("/", $date);
            wfDebug ("SpecialRedir::getDate: dateText: $dateText\n");
            return $dateText; 
    }

    function execute($par) {
        global $wgOut, $wgRequest;

	wfDebug("SpecialRedir::execute: execute called on ($par)\n");
        $wikipage = $wgRequest->getText('wikipage') ? 
                $wgRequest->getText('wikipage') : $par;
        if ($wikipage){
            $base = $this->getBase($wikipage);
            $date = $this->getDate($wikipage);

            // create page if not present
            $title = $this->addEntry($base, $date);
            $url = $title->getFullURL();
            wfDebug ("SpecialRedir::execute: Redirecting to $url\n"); 
            $wgOut->setSquidMaxage( 1200 );
            $wgOut->redirect($url, '301');
        }
    }

    function loadMessages(){
        static $messagesLoaded = false; 
        global $wgMessageCache;
        if ( $messagesLoaded ) return true;
            $messagesLoaded = true;
        require( dirname( __FILE__ ) . '/SpecialRedir.i18n.php' );
        foreach ( $allMessages as $lang => $langMessages ) {
            $wgMessageCache->addMessages( $langMessages, $lang );
        }
        return true;
    }
}

$wgHooks['LoadAllMessages'][] = 'wfRedirLoadMessages';

function wfRedirLoadMessages() {
        (Redir::loadMessages());
        return true;
}


?>
