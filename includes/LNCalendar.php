<?php

class LabNotebookCalendar {
    public static function onParserSetup( Parser $parser ) {
        $parser->setHook("LNCalendar","LabNotebookCalendar::renderLNCalendar");
    }
    public static function renderlnCalendar( $input, array $args, Parser $parser, PPFrame $frame ) {
        return htmlspecialchars(renderLNCalendarDo($input));
    }
}

function isLNDate($datestring) {
        if (date('Y/m/d', strtotime($datestring)) == $datestring) {
                return true;
        } else {
                return false;
        }
}

function wfaddScript($script) {
    global $wgOut;
    $wgOut->addScript($script);
}

function getLNCalendarOptions(&$input,$name,$default,$isNumber=false) {
	$inputs = explode("|", $input);
	foreach($inputs as $inp){
		if(preg_match("/^\s*$name\s*=\s*(.*)/mi",$inp,$matches)) {
			if($isNumber){
				return intval($matches[1]);
			}else{
				return htmlspecialchars($matches[1]);
			}
		}
        }
	return $default;
}

function getDateList($base, $year, $month){
	wfDebug("getDateList2:base=$base, year=$year, month=$month\n");
	if ($year != 0){
        	$yearText = $year;
	}else if ($month != 0){
		$year = date("Y");
		$yearText = $year.'/'.substr("0$month",-2);
	}else{
		$yearText = "20";
	} 

        // find all date pages in the same namespace with base as a prefix. 
        $dates = array();

	// see if the base page exists.
	$t = Title::newFromText($base);
	if (!is_object($t))
		return $dates;

	if (!$t->exists())
		return $dates;

	// get the namespace and text from the title
	$ns = $t->getNamespace();
	$text = $t->getText();
        $text = str_replace(' ', '_', $text);

	// get the db read not-a-pointer
	$dbr = wfGetDB(DB_REPLICA);

	// format and execute the query
        $query = "SELECT page_title FROM " . $dbr->tableName('page') . "  where page_namespace = $ns AND " . 
			"page_title LIKE '" . str_replace("'", "''", "$text/$yearText%") . "'";
	wfDebug("getDateList:0:q=$query\n");
        $result = $dbr->query($query);
        if ($result){
		while ($row = $dbr->fetchRow($result)) {
			$pageTitle = $row[0];
			if (strlen($pageTitle) > 10 && isLNDate(substr($pageTitle, -10))){
                		$date = substr($pageTitle, -10);
                		$dlist = explode("/", $date);
                		$y = isset($dlist[0]) ? $dlist[0] : '';
                		$m = isset($dlist[1]) ? $dlist[1] : '';
                		$d = isset($dlist[2]) ? $dlist[2] : '';
                		if ($y && $m && $d){
					wfDebug("getDateList:1:$m/$d/$y\n");
                    $dates[] = "$m/$d/$y";
				}
			}
        	}
	}
        return $dates;
}

function getBasePage($page, $type){
	switch($type){
		case 'M':
			$base = substr($page, 0, strlen($page) - 8);
			break;

		case 'Y':
                        $base = substr($page, 0, strlen($page) - 5);
			break;

		case 'C':
		default:
                       	$base = $page;
			break;
	}
	return $base;
}

function renderLNCalendarDo($input) {
    global $wgOut, $wgTitle, $wgUser;

    $wgOut->addModules("ext.LabNotebook.calendar");

    $parser = \MediaWiki\MediaWikiServices::getInstance()->getParser();
    $parser->getOutput()->updateCacheExpiry( 60 );

    if (!$wgTitle){
        return '';
    }
    $currentPage = $wgTitle->getNamespace() ? 
	$wgTitle->getNsText().':'.$wgTitle->getText() : 
	$wgTitle->getText();

    wfDebug("renderLNCalendar:page = $currentPage\n");
    $page = trim(getLNCalendarOptions($input, "page", $currentPage));
    $css = trim(getLNCalendarOptions($input, "cssprefix", "OWWNB"));
    $id = trim(getLNCalendarOptions($input, "uniqueid", "lncal1"));
    $fmt = trim(getLNCalendarOptions($input, "format", "yyyy/MM/dd"));
    $month = trim(getLNCalendarOptions($input, "month", ""));
    $year = trim(getLNCalendarOptions($input, "year", ""));
    $type = trim(getLNCalendarOptions($input, "type", "C"));

    wfDebug("renderLNCalendar:page = $page\n");
    $pageTitle = Title::newFromText($page);
    if (is_object($pageTitle) && $pageTitle->exists()){
        $base = getBasePage($page, $type);
        if ($type != 'C'){
            $page = $base;
        }
	wfDebug("renderLNCalendar:base = $base\n");

            return '<!-- sibboleth -->'
                .'<div id="'.$id.'" style="border:0px;">'
                .'<div style="display:none;" id="id">'.$id.'</div>'
                .'<div style="display:none;" id="dtext">'.implode(",",getDateList($base, $year, $month)).'</div>'
                .'<div style="display:none;" id="page">'.str_replace("'", "\\'", $page).'</div>'
                .'<div style="display:none;" id="fmt">'.$fmt.'</div>'
                .'<div style="display:none;" id="css">'.$css.'</div>'
                .'<div style="display:none;" id="month">'.$month.'</div>'
                .'<div style="display:none;" id="year">'.$year.'</div>'
                .'<div style="display:none;" id="readonly">'.($wgUser->isLoggedIn() ? 'N' : 'Y').'</div>'
                .'</div>';
        }
    return '';
}
