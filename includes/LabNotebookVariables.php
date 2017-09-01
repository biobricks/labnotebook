<?php

$wgExtensionFunctions[] = 'wfLabNotebookFunctions';

$wgHooks['LanguageGetMagic'][] = 'wfLabNotebookFunctionsLanguageGetMagic';

function wfLabNotebookFunctions ( ) {
    global $wgParser, $wgLabNotebookFunctions;

    $wgLabNotebookFunctions = new LabNotebookFunctions ( );
    
    $wgParser->setFunctionHook ( 'lnnextentry', array ( &$wgLabNotebookFunctions, 'lnnextentry') );
    $wgParser->setFunctionHook ( 'lnpreventry', array ( &$wgLabNotebookFunctions, 'lnpreventry') );
    $wgParser->setFunctionHook ( 'lnnewbie', array ( &$wgLabNotebookFunctions, 'lnnewbie') );
    $wgParser->setFunctionHook ( 'lnvar',    array ( &$wgLabNotebookFunctions, 'lnvar') );
    $wgParser->setFunctionHook ( 'lnencode',    array ( &$wgLabNotebookFunctions, 'lnencode') );
    $wgParser->setFunctionHook ( 'lnproject',array ( &$wgLabNotebookFunctions, 'lnproject') );
    $wgParser->setFunctionHook ( 'lnuser',   array ( &$wgLabNotebookFunctions, 'lnuser') );
    $wgParser->setFunctionHook ( 'lnisdate', array ( &$wgLabNotebookFunctions, 'lnisdate') );
    $wgParser->setFunctionHook ( 'lndate', array ( &$wgLabNotebookFunctions, 'lndate') );
    $wgParser->setFunctionHook ( 'lnnewproject', array ( &$wgLabNotebookFunctions, 'lnnewproject') );
    $wgParser->setFunctionHook ( 'lnbase', array ( &$wgLabNotebookFunctions, 'lnbase') );
    $wgParser->setFunctionHook ( 'lnnewentry', array ( &$wgLabNotebookFunctions, 'lnnewentry') );
    $wgParser->setFunctionHook ( 'lnfilter', array ( &$wgLabNotebookFunctions, 'lnfilter') );
}

function wfLabNotebookFunctionsLanguageGetMagic( &$magicWords, $langCode = "en" ) {
    switch ( $langCode ) {
        default:
            $magicWords['lnnextentry']    = array ( 0, 'lnnextentry' );
            $magicWords['lnpreventry']    = array ( 0, 'lnpreventry' );
            $magicWords['lnnewbie']       = array ( 0, 'lnnewbie' );
            $magicWords['lnvar']          = array ( 0, 'lnvar' );
            $magicWords['lnencode']       = array ( 0, 'lnencode' );
	    $magicWords['lnproject']      = array ( 0, 'lnproject' );
            $magicWords['lnuser']         = array ( 0, 'lnuser' );
            $magicWords['lnisdate']       = array ( 0, 'lnisdate' );
            $magicWords['lndate']         = array ( 0, 'lndate' );
            $magicWords['lnnewentry']     = array ( 0, 'lnnewentry' );
            $magicWords['lnbase']         = array ( 0, 'lnbase' );
            $magicWords['lnnewproject']   = array ( 0, 'lnnewproject' );
            $magicWords['lnfilter']       = array ( 0, 'lnfilter' );
    }
    return true;
}

class LabNotebookFunctions{
    var $nsText = '';
    var $ns = '';
    var $name = '';
    var $sysProjectBase = "MediaWiki:ProjectContentDefault";
    var $sysEntryBase = "MediaWiki:EntryContentDefault";
    var $projectBase = "Project_Base";
    var $entryBase = "Entry_Base";
    var $projects = "Projects/";
    var $entries = "/";

    function LabNotebookFunctions(){
	global $wgUser, $wgContLang, $wgLabNotebookNamespace;
        $this->nsText = $wgContLang->getNSText($wgLabNotebookNamespace);
        $this->ns = $wgLabNotebookNamespace;
        $this->name = $wgUser->getName();
    }

    function getPage($title){
	$text = '';
        $t = Title::newFromText($title);
        if ($t != '' || $t->exists()){
                $a = new Article($t);
                $text .= $a->GetContent();
	}
        return ($text);
    }

    function datechange($date, $days){
	if (!$this->isdate($date)){
	    return '';
	}
	$y = substr($date, 0,4);
	$m = substr($date, 5,2);
	$d = substr($date, -2) + $days;
	return date("Y/m/d", mktime(0, 0, 0, $m, $d, $y));	
    }

    function parse(&$parser, $text){
        $localParser = new Parser();
        $parseOutput = $localParser->parse($text, $parser->mTitle,
                $parser->mOptions, false);
        return $parseOutput->getText();
    }

    function getNSText(){
	return $this->nsText;
    }

    function getNS(){
        return $this->ns;
    }

    function getName(){
        return $this->name;
    }

    function setProject1($projectBase, $project){
        $name = $this->getNSText().":$projectBase/$this->projects$project";
        $t = Title::newFromText($name);
        if (!$t->exists()){
            $a = new Article($t);
            $a->doEdit($name, '', EDIT_NEW|EDIT_AUTOSUMMARY);
        }
        return $t->getText();
    }

    function setProject2($username, $project){
        $name = $this->getNSText().":$username/$this->projects$project";
        $t = Title::newFromText($name);
        if (!$t->exists()){
            $base = $this->getBase();
            $a = new Article($t);
            $a->doEdit($base, '', EDIT_NEW|EDIT_AUTOSUMMARY);
        }
	return $t->getText();
    }

   function setProject($notebookBase, $project){
        $ns = "Notebook";
        $name = "$ns:$notebookBase/projects/$project";
        $t = Title::newFromText($name);
        if (!$t->exists()){
            $text = '';
            $bt = Title::newFromText("$ns:$notebookBase/Project Base");
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
            $a->doEdit($text, "Autocreate New Project in Notebook $notebookBase",
                EDIT_NEW|EDIT_AUTOSUMMARY);
        }
        return $t->getText();
    }

    function setEntry($notebookBase, $date, $redirect=false){
        global $wgOut;

        $ns = "Notebook";
        $name = "$ns:$notebookBase/$date";
        $t = Title::newFromText($name);
        if (!$t->exists()){
            $text = '';
            $bt = Title::newFromText("$ns:$notebookBase/Entry Base");
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
            $a->doEdit($text, "Autocreate New Entry for Notebook $notebookBase", 
		EDIT_NEW|EDIT_AUTOSUMMARY);
        }
        $wgOut->setSquidMaxage( 1200 );
        $wgOut->redirect($t->getFullURL( '' ), '301');
    }

    function getBase($username=''){
	$tp = Title::newFromText($this->getNSText().":$username/$projectBase");
	if ($tp->exists()){
            $a = new Article($tp);
            return $a->getContent();
	}
        $tg = Title::newFromText($this->sysProjectBase);
        if ($tg->exists()){
            $a = new Article($tg);
            return $a->getContent();
        }
        return '';
    }

    function isdate($date){
        $blnValid = 1;
        if(!ereg ("^[0-9]{4}/[0-9]{2}/[0-9]{2}$", $date)){
            $blnValid = 0;
        }else{
             $v = explode("/", $date);
	     $y = $v[0];
	     $m = $v[1];
	     $d = $v[2];
             if (!checkdate($m, $d, $y)){
                 $blnValid = 0;
             }
        }
        return ($blnValid);
    }

    function fixSectionTags($entryText){
        $startLength = 10;
        $startSection = '==========';
        $baseSection = 3;

        $entryText = "\n$entryText\n";
        for ($cnt = $startLength; $cnt > 0; $cnt--){
            // Set the section tags to compare with
            $startSectionTag = "\n".substr($startSection, 0, $cnt);
            $endSectionTag = substr($startSection, 0, $cnt)."\n";
            $entryText = str_replace("<h$cnt ", "<__FIX__".($cnt + $baseSection)." ", $entryText);
            $entryText = str_replace("<H$cnt ", "<__FIX__".($cnt + $baseSection)." ", $entryText);
            $entryText = str_replace("<h$cnt>", "<__FIX__".($cnt + $baseSection).">", $entryText);
            $entryText = str_replace("<H$cnt>", "<__FIX__".($cnt + $baseSection).">", $entryText);
            $entryText = str_replace("</h$cnt>", "</__FIX__".($cnt + $baseSection).">", $entryText);
            $entryText = str_replace("</H$cnt>", "</__FIX__".($cnt + $baseSection).">", $entryText);
            $entryText = str_replace($startSectionTag, "\n<__FIX__".($cnt + $baseSection).">", $entryText);
            $entryText = str_replace($endSectionTag, "</__FIX__".($cnt + $baseSection).">\n", $entryText);
        }
        $sectionTag = "==";
        //$entryText .= str_replace("<__FIX__", "<h", $entryText);
        //$entryText .= str_replace("</__FIX__", "</h", $entryText);
        for ($i = 3; $i < 10; $i++){
            $sectionTag .= "=";
            $entryText = str_replace("<__FIX__$i>",  $sectionTag, $entryText);
            $entryText = str_replace("</__FIX__$i>", $sectionTag, $entryText);
        }
        return trim($entryText);
    }

    function ismyDate($date){
        $y = substr($date, 0, 4);
        $m = substr($date, 5, 2);
        $d = substr($date, 8, 2);
        if(!checkdate($m,$d,$y)){
            return false;
        }
        return true;
    }

    function addHideShow($section){
	$id = mt_rand(1, 1000000);
	$section = "<span class=\"_toggler_hide-tog$id\">Hide</span> | " .
			"<span class=\"_toggler_show-tog$id\">Show</span>\n" .
			"<div class=\"tog$id\" style=\"display:block\">$section</div>\n";
	return $section;
    }

    function lnfilter(&$parser, $title){
	global $wgTitle;

	$p = explode("/", $wgTitle->getText());
	if (!isset($p[2]) || $p[1] != 'Projects' ||
			$wgTitle->getNamespace() != NS_LABNB){
		return '';
	}
	$project = $p[2];

        $output = '<br />';
        $text = $this->getPage($title);
        $text = str_replace('{{P|', '{{p|', $text);
        $text = str_replace('{{project|', '{{p|', $text);
        $text = str_replace('{{Project|', '{{p|', $text);
        $entries = explode ('{{p|', $text);
        foreach ($entries as $entry){
            if ($entry != ''){
                $entryBody = explode ('|', $entry);
                $projectName = isset($entryBody[0]) ? trim($entryBody[0]) : '';
                $rawContent = isset($entryBody[1]) ? trim($entryBody[1]) : '';
                if ($projectName == $project){
                    list($content) = explode('}}', $rawContent);
                    $output .= $this->addHideShow($content)."<br />";
                }
            }
        }
	return($this->fixSectionTags($output));
    }

    function lnbase ( &$parser, $current=''){
	if (strlen($current) <= 11){
		return '';
	}
	return substr($current, 0, strlen($current) - 11);
    }

    function lnnextentry ( &$parser, $current=''){
        if (strlen($current) <= 10){
            return '';
	}
	$date =  $this->lndate($parser, $current);
        if ($date == ''){
            return '';
	}
        $base = substr($current, 0, strlen($current) - 10);
        $y = substr($date, 0, 4);
        $m = substr($date, 5, 2);
        $dCurrent = substr($date, 8, 2);
        $maxdays = 365;
	$d = $dCurrent;
        for ($dc = 0; $dc < $maxdays; $dc++){
            $d++;
            $page = $base.date("Y/m/d", mktime (0, 0, 0, $m  ,$d, $y));
            $t = Title::newFromText($page);
            if ($t->exists()){
                return $page;
            }
         }
	 return '';
    }

    function lnpreventry ( &$parser, $current=''){
	if (strlen($current) <= 10){
            return '';
	}
        $date =  $this->lndate($parser, $current);
        if ($date == ''){
            return '';
	}
        $base = substr($current, 0, strlen($current) - 10);
        $y = substr($date, 0, 4);
        $m = substr($date, 5, 2);
        $dSave = substr($date, 8, 2);
        $maxdays = 365;
	$d = $dSave;
        for ($dc = 0; $dc < $maxdays; $dc++){
            $d--;
            $page = $base.date("Y/m/d", mktime (0, 0, 0, $m, $d, $y));
            $t = Title::newFromText($page);
            if ($t->exists()){
                return $page;
            }
         }
	 return '';
    }

    function lnnewproject ( &$parser, $entryBase='', $project=''){
        if ($entryBase == '' || $project == ''){
            return '';
        }
        return $this->setProject($entryBase, $project);
    }

    function lnnewentry ( &$parser, $entryBase='', $date='', $redirect=""){
        if ($entryBase == '' || $date == ''){
            return '';
        }
        return $this->setEntry($entryBase, $date, $redirect);
    }

    function lnuser (&$parser, $titleText){
        if (($pos = strpos($titleText, ':'))!= false){
            $titleText = substr($titleText, $pos + 1);
        }
        if (($pos = strpos($titleText, '/'))!= false){
            $user = substr($titleText, 0, $pos);
        }else{
            $user = $titleText;
	}
	return $user;
    }

    function lnisdate (&$parser, $date){
	return $this->isDate($date);
    }

    function lndate (&$parser, $entryPage){
	$date = substr($entryPage, -10);
        if ($this->isDate($date)){
	    return $date;
	}
	return '';
    }

    function lnproject ( &$parser, $username='', $project='' ){
        if ($username == '' || $project == ''){
            return '';
        }
        return $this->getNSText().':'.
		str_replace("_", " ", $username."/$this->projects$project");
    }

    function lnnewbie( &$parser){
        global $wgNoCookies;

        if ($wgNoCookies)
	    return "Y";
        return "N";
    }

    function lnencode ( &$parser, $var){
	return urlencode($var);
    }

    function lnvar ( &$parser, $var){
	global $wgUser;
        global $wgContLang;
        global $wgLabNotebookNamespace;
	global $wgTitle;
	global $wgOut;

	switch(strtoupper($var)){
                case "PUBGETPREFIX":
                        $p = $wgUser->getOption('pubgetprefix');
                        break;

                case "THISDATE":
                        $p = $this->lndate($parser, $wgTitle->getText());
                        break;

                case "LOGGEDIN":
                        $p = $wgUser->isLoggedIn() ? "YES" : "";
                        break;

                case "THISUSER":
                        $p = $this->lnuser($parser, $wgTitle->getText());
                        break;

                case "PREVDAY":
			$date = $this->lndate($parser, $wgTitle->getText());
			if ($date)
			    $p = $this->datechange ($date, -1);
			else
			    $p = '';
                        break;

                case "NEXTDAY":
                        $date = $this->lndate($parser, $wgTitle->getText());
                        if ($date)
                            $p = $this->datechange ($date, 1);
                        else
                            $p = '';
                        break;

                case "ICON":
                        $p = "OWWLabNotebookIcon.png";
                        break;

		case "USERPAGE":
			$t = $wgUser->getUserPage();
        		$p = $t->getText();
			break;

                case "ENTRIES":
                        $p = $this->entries;
                        break;

                case "PROJECTS":
                        $p = $this->projects;
                        break;

                case "SYSENTRYBASE":
                        $p = $this->sysEntryBase;
                        break;

               case "SYSPROJECTBASE":
                        $p = $this->sysprojectBase;
                        break;

                case "ENTRYBASE":
                        $p = $this->entryBase;
                        break;

                case "PROJECTBASE":
                        $p = $this->projectBase;
                        break;

        	case "USERNAME":
			$p = $this->getName();
    			break;

                case "LABNBNS":
                	$p = $this->getNSText();
                        break;

               case "LABNBNSNUMBER":
                        $p = $this->getNS();
                        break;

                case "LABNBDATE":
                        $p = date('Y/m/d');
                        break;

		default: 
			$p = '';
	}
	$p = str_replace(" ", "_", $p);
	return $p;
    }
}

?>
