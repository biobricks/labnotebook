<?php
 
$wgExtensionFunctions[] = "Calendar";
 
class LabCalendar{

	var $page = '';
	var $css = '';
        var $id = '';
        var $fmt = '';
        var $month = '';
         $this->year = $this->getOptions($input, "year", "");
                $this->type = $this->getOptions($input, "type", "C");
                $this->jsfile = $this->getOptions($input, "javascriptfile", "/js/calendar.js");



	function LabCalendar(){
		$this->addCalendar();
	}

	function addCalendar() {
    		global $wgParser;
		$wgParser->setHook("LabCalendar", array('LabCalendar', 'renderCalendar');
	}

	function isDate($datestring) {
        	if (date('Y/m/d', strtotime($datestring)) == $datestring) {
                	return true;
        	} else {
                	return false;
        	}
	}
 
	function addScript($script) {
		global $wgOut;
		$wgOut->addScript($script);
	}

	function getOptions(&$input,$name,$default,$isNumber=false) {
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
		return trim($default);
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
	        $query = "SELECT page_title FROM " . $dbr->tableName('page') . " WHERE page_namespace = $ns " AND " . 
				"page_title LIKE '" . str_replace("'", "''", "$text/$yearText%"). "'";
		wfDebug("getDateList:0:q=$query\n");
        	$result = $dbr->query($query);
        	if ($result){
			while ($row = $dbr->fetchRow($result)) {
				$pageTitle = $row[0];
				if (strlen($pageTitle) > 10 && $this->isDate(substr($pageTitle, -10))){
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
 
	function renderCalendar($input) {
		global $wgOut, $wgTitle, $wgParser, $wgUser;

		$wgParser->disableCache();    
		if (!$wgTitle){
			return '';
		}
		$currentPage = $wgTitle->getNamespace() ? 	
				$wgTitle->getNsText().':'.$wgTitle->getText() : 
				$wgTitle->getText();
 
		// If the user isn't logged in, don't link to empty page
		$readOnly = $wgUser->isLoggedIn() ? 'N' : 'Y';

		wfDebug("renderCalendar:page = $currentPage\n");
		$this->page = $this->getOptions($input, "page", $currentPage);
		$this->css = $this->getOptions($input, "cssprefix", "OWWNB");
    		$this->id = $this->getOptions($input, "uniqueid", "lncal1");
    		$this->fmt = $this->getOptions($input, "format", "yyyy/MM/dd");
    		$this->month = $this->getOptions($input, "month", "");
    		$this->year = $this->getOptions($input, "year", "");
    		$this->type = $this->getOptions($input, "type", "C");
    		$this->jsfile = $this->getOptions($input, "javascriptfile", "/js/calendar.js");

		wfDebug("renderCalendar:page = $page\n");
    		$this->pageTitle = Title::newFromText($page);
    		if (is_object($this->pageTitle) && $pageTitle->exists()){
			$base = $this->getBasePage($this->page, $this->type);
			if ($this->type != 'C'){
				$this->page = $this->base;
			}
			wfDebug("renderCalendar:base = $this->base\n");

			$this->getDateList($this->base, $this->year, $this->month);
			$sc1 = $this->renderScript1();
			$this->addScript($sc1);

			$sc2 = $this->renderScript2();
        		$this->addScript($sc2);

			$sc3 = $this->renderScript3();
        		$this->addScript($sc3);

        		$html = $this->renderDiv();
        		return $html;
    		}
		return '';
	}

	function renderScript1(){
		$script = "<script type=\"text/javascript\" src=\"$this->jsfile\"></script>\n"; 
		return $script;
	}

	function renderScript2(){
		if ($this->dates != ''){
        		$dtext = implode(",", $this->dates);
		}
        	$script = "<script type=\"text/javascript\">\n" .	
				"function lnCalendar(){\n" .
				"  var cal" . $this->id . " = new CalendarPopup('" . $this->id . "');\n" .
				"  var wikiPage = '" . $this->page . "';\n" .
				"  var redirPage = '/wiki/Special:Redir/'+wikiPage;\n";
		$script .= "  var fullDates = new Array(" . $this->dtext . ");\n";
		$script .= "  cal$id.setDateFormat('" . $this->fmt . "');\n" .
                		"  cal$id.setCssPrefix('" . $this->css . "');\n";
		if ($this->month){
			$script .= "  cal" . $this->id . ".setSingleMonth('" . $this->month . "');\n";
		}
        	if ($this->year){
			$script .= "  cal" . $this->id . ".setYear('" . $this->year . "');\n";
        	}
		$script .= "  cal" . $this->id . ".setReadOnly('" . $this->readOnly . "');\n" .
                		"  cal" . $this->id . ".setTodayText('');\n" .
                		"  cal$id.setUrlPrefix(redirPage);\n";
		if ($dtext != ''){
			$script .= "  for(i = 0; i < fullDates.length; i++)cal" . $this->id . ".addFilledDates(fullDates[i]);\n";
		}
        	$script .=        "  cal$id.showCalendar('" . $this->id . "');\n" .
				"}\n" .
				"</script>\n";
        	return $script;
	}

	function renderScript3(){
		$script = "<script type=\"text/javascript\">addOnloadHook( lnCalendar )</script>\n";
        	return $script;
	}

	function renderDiv(){
 		$script = "<div id=\"" . $this->id . "\" style=\"border:0px;\"></div>\n";
        	return $script;
	}
}

?>
