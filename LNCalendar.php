<?php
 
$wgExtensionFunctions[] = "wfLNCalendar";
 
function wfLNCalendar() {
    global $wgParser;
    $wgParser->setHook("LNCalendar", "renderLNCalendar");
}


function isLNDate($datestring) {
        if (date('Y/m/d', strtotime($datestring)) == $datestring) {
                return true;
        } else {
                return false;
        }
}

function wfaddStyle($cssfile) {
    global $wgOut;
    $wgOut->addStyle($cssfile);
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

	// get the db read pointer
	$dbr =& wfGetDB(DB_SLAVE);

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
                        		$dates[] = "'$m/$d/$y'";
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

function renderLNCalendar($input) {
    global $wgOut, $wgTitle, $wgParser, $wgUser;

    $wgParser->disableCache();
    if (!$wgTitle){
        return '';
    }
    $currentPage = $wgTitle->getNamespace() ? 
	$wgTitle->getNsText().':'.$wgTitle->getText() : 
	$wgTitle->getText();

    // If the user isn't logged in, don't link to empty pages
    $readOnly = $wgUser->isLoggedIn() ? 'N' : 'Y';

    wfDebug("renderLNCalendar:page = $currentPage\n");
    $page = trim(getLNCalendarOptions($input, "page", $currentPage));
    $css = trim(getLNCalendarOptions($input, "cssprefix", "OWWNB"));
    $id = trim(getLNCalendarOptions($input, "uniqueid", "lncal1"));
    $fmt = trim(getLNCalendarOptions($input, "format", "yyyy/MM/dd"));
    $month = trim(getLNCalendarOptions($input, "month", ""));
    $year = trim(getLNCalendarOptions($input, "year", ""));
    $type = trim(getLNCalendarOptions($input, "type", "C"));
    $jsfile = trim(getLNCalendarOptions($input, "javascriptfile", "/js/calendar.js"));
    $cssfile = trim(getLNCalendarOptions($input, "cssfile", "owwnotebook.css"));

    wfDebug("renderLNCalendar:page = $page\n");
    $pageTitle = Title::newFromText($page);
    if (is_object($pageTitle) && $pageTitle->exists()){
        $base = getBasePage($page, $type);
        if ($type != 'C'){
            $page = $base;
        }
	wfDebug("renderLNCalendar:base = $base\n");
        $dates = getDateList($base, $year, $month);

	wfaddStyle($cssfile);

	$sc1 = renderScript1($jsfile);
	wfaddScript($sc1);

	$sc2 = renderScript2($page, $css, $id, $fmt, $dates, $readOnly, $month, $year);
        wfaddScript($sc2);

	$sc3 = renderScript3();
        wfaddScript($sc3);

        $sc4 = renderScript4();
        wfaddScript($sc4);

        $html = renderDiv($id);
        return $html;
    }
    return '';
}

function renderScript1($jsfile){
        $script = "<script type=\"text/javascript\" src=\"$jsfile\"></script>\n"; 
        return $script;
}

function renderScript2($page, $css, $id, $fmt, $dates, $readOnly, $month, $year){
	if ($dates != ''){
        	$dtext = implode(",", $dates);
	}
        $script = "<script type=\"text/javascript\">\n" .
		"function lnCalendar(){\n" .
                "  var cal$id = new CalendarPopup('$id');\n" .
                "  var wikiPage = '" . str_replace("'", "\\'", $page) . "';\n" .
                "  var redirPage = '/wiki/Special:Redir/'+wikiPage;\n";
	$script .= "  var fullDates = new Array($dtext);\n";

	$script .= "  cal$id.setDateFormat('$fmt');\n" .
                "  cal$id.setCssPrefix('$css');\n";
	if ($month){
		$script .= "  cal$id.setSingleMonth('$month');\n";
	}
        if ($year){
                $script .= "  cal$id.setYear('$year');\n";
        }
	$script .= "  cal$id.setReadOnly('$readOnly');\n" .
                "  cal$id.setTodayText('');\n" .
                "  cal$id.setUrlPrefix(redirPage);\n";
	if ($dtext != ''){
		$script .= "  for(i = 0; i < fullDates.length; i++)cal$id.addFilledDates(fullDates[i]);\n";
	}
        $script .=        "  cal$id.showCalendar('$id');\n" .
			"}\n" .
		"</script>\n";
        return $script;
}

function renderScript3(){
	$script = "<script type=\"text/javascript\">addOnloadHook( lnCalendar )</script>\n";
        return $script;
}

function renderScript4(){
        $script = "<script type=\"text/javascript\">function CalendarPageConfirmCreate(date, url){var answer = confirm(\"Create entry for \"+date+\"?\");if (answer){window.location = url;}}</script>\n";
        return $script;
}

function renderDiv($id){
 	$script = "<div id=\"$id\" style=\"border:0px;\"></div>\n";
        return $script;
}

?>
