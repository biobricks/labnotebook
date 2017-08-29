<?php

// Notebook storage object
require_once ('LabNotebook/LabNotebook.php');

class CreateNotebook4{

	var $oneClickPage = '/wiki/Help:Notebook/One_Click_Setup';
	var $categoryTag = '[[category:OWWLabNotebookV1]]';
	var $nbErrors = '';
        var $notebookContent = "MediaWiki:NotebookContentDefault";
        var $projectContent = "MediaWiki:ProjectContentDefault";
        var $entryContent = "MediaWiki:EntryContentDefault";
        var $IGEMProjectContent = "MediaWiki:IGEMProjectContentDefault";
        var $IGEMEntryContent = "MediaWiki:IGEMEntryContentDefault";
        var $notebookName = "Notebook";
        var $entryName = "Entry Base";
        var $error = '';
        var $message = '';
        var $loggingEnabled = true;
        var $testMode = false;
        var $project = '';
        var $base = '';
        var $type = '';
        var $username = '';
        var $lab = '';
        var $institution = '';
        var $year = '';
        var $page = '';
        var $nbContent = '';
	var $logFile = "/data/web/storage/labnotebook/oneclick.log";

	function renderGrid($headers, $cells, $jsLocation, $cssLocation, $pages, $types){
		global $wgOut;

		$script = "<html>\n<head>\n";
        	$script .= "<script src=\"$jsLocation\" type=\"text/javascript\"></script>\n";
	    	$script .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"$cssLocation\" />\n";
		$script .= "</head><body>\n";

		$script .= "<h2>Total pages: $pages</h2>\n";
		foreach ($types as $n => $v){
			$script .= "<h2>Total $n Notebooks: $v</h2>\n";
		}

		$hdrs =  implode("', '", $headers);
		$script .= "<div id=\"grid\"></div><script type=\"text/javascript\">var g = new OS3Grid ();\n";
    		$script .= "g.set_headers ('$hdrs');\n";
    		$script .= "g.set_scrollbars ( true );\n";
    		$script .= "g.set_border ( 1, \"solid\", \"#cccccc\" );\n";

		foreach ($cells as $row){
			$script .= "g.add_row ('" . implode("', '", $row) . "');\n";
		}

		$script .= "g.set_sortable ( true );\n" .
			"g.set_highlight ( true );\n" .
			"g.render ( 'grid' );</script>\n";
		$script .= "</body>\n</html>\n";
		return $script;	
	}

	function CreateNotebook4(){

                global $wgIGEMCurrentYear;
		$this->nbErrors = array(
			'nberrornoigemteam' => 'You are attempting to create a notebook in a ' .
                	'non-existing IGEM team page, $1.' .
                	'Please visit <a href="/wiki/$1">'.
			'here</a> and click "edit" to create your IGEM page.',
			'nberrornolab' => 'There is no Lab page called $1. ' .
			'Please visit <a href="/wiki/$1">'.
			'here</a> and click  "edit" to create your lab page. ' .
                	'Further instructions on setting up a lab page can be found here.',
			'nberrornouserpage' => 'There is no user page for $1. '.
			'Please create your user page '.
			'<a href="/wiki/$1">here</a>'.
                	'and click "edit" to create your user page. ' .
                	'Further instructions on setting up a lab page can be found here.',
			'nberrorpageexists' => 'The project you are attempting to create already exists.' .
                	'Please check the fields again and try again. Thanks!',
			'nberrorprojectexists' => 'The project you are attempting to create already exists.' .
                	'Please check the fields again and try again. Thanks!',
			'nberrornoprojectinreq' => 'No project name has been specified. '.
                	'Please check the fields again and try again. Thanks!',
			'nberrornolabinreq' => 'No lab name has been specified. '.
                	'Please check the fields again and try again. Thanks!',
			'nberrornoinstitutioninreq' => 'No institution has been specified. '.
                	'Please check the fields again and try again. Thanks!',
			'nberrornotloggedin' => 'You must be logged in to create a new Lab Notebook '.
                	'Please log in and try again. Thanks!',
			'nberrorinvalidtype' => 'Lab Notebooks must be USER, IGEM, or LAB. '.
                	'Please correct this and try again. Thanks!',
			'nberrorinvalidrequest' => 'This is not a valid request to create a Lab Notebooks. ' .
                	'Please use a valid request to try again. Thanks!',
			'nbsuccessigemteam' => 'Congratulations! Your IGEM Team ' .
                	'Notebook has been created with success. ' .
                	'You can visit it <a href="/wiki/$1">here</a>',
			'nbsuccesslab' => 'Congratulations! Your lab notebook ' .
                	'has been created with success. ' .
                	'You can visit it <a href="/wiki/$1">here</a>',
			'nbsuccesspersonal' => 'Congratulations! Your lab notebook ' .
                	'has been created with success. ' .
                	'You can visit it <a href="/wiki/$1">here</a>');

		$this->year = $wgIGEMCurrentYear;
	}

        function getStrsBetween($s,$s1,$s2) {
                $pos_s = strpos($s,$s1) + strlen($s1);;
                $newS = substr($s, $pos_s);
                $pos_e = strpos($newS,$s2);
                return substr($newS, 0, $pos_e);
        }

	function setProjectText($content, $project){
		$this->log("Content before: $content");
		$content = str_replace('#PROJECT#',$project, $content);
		$this->log("Content after: $content");
		return $content;
	}

	function isActive($page){
		$t = Title::newFromText($page);
		if (!is_object($t))
			return false;
		if ($t->exists())
			return true;
		return false;
	}

        function lastUpdate($page){
                $dbr = wfGetDB( DB_SLAVE );
                $t = Title::newFromText($page);
                if (!is_object($t))
                        return 0;
                if (!$t->exists())
                        return 0;
                $title = str_replace(" ", "_", str_replace ("'", "\'", $t->getText()));
                $ns = $t->getNamespace();
                $sql = "select page_touched from page " .
			"where page_namespace=$ns and " .
			"page_title like '$title' " .
			"order by page_touched desc limit 0,1";
                $rs = $dbr->query($sql);
		$ro = $dbr->resultObject($rs);
		$pageRow = $dbr->fetchObject($ro);
                $date = $pageRow->page_touched;
                $dbr->freeResult($rs);
                return $date;
        }

        function pagesInNotebook($page){
		$dbr = wfGetDB( DB_SLAVE );
                
		$t = Title::newFromText($page);
		if (!is_object($t))
			return 0;
                if (!$t->exists())
                        return 0;
		$title = str_replace(" ", "_", str_replace ("'", "\'", $t->getText()));
		$ns = $t->getNamespace();
		$sql = "select page_id from page where page_namespace=$ns and page_title like '$title/200%'";
		$rs = $dbr->query($sql);
		$count = $dbr->numRows( $rs);
		$dbr->freeResult($rs);
                return $count;
        }
	
	function out($name, $value){
		echo ("$name: $value<br />\n");
	}

        function cvtTimeDate($output){
		if (strlen($output) != 14){
			return '';
      		}
	        $y = substr($output, 0, 4);
                $mo = substr($output, 4, 2);
                $d = substr($output, 6, 2);
                $h = substr($output, 8,2);
                $mi = substr($output, 10, 2);
                $s = substr($output, 12, 2);
                return "$mo/$d/$y $h:$mi:$s";
        }

	function cvtDate($output){
		$y = substr($output, 0, 4);
                $mo = substr($output, 5, 2);
                $d = substr($output, 8, 2);
                $h = substr($output, 11,2);
                $mi = substr($output, 14, 2);
                $s = substr($output, 17, 2);
		return "$mo/$d/$y $h:$mi:$s";
	}

        function renderLabInfo($cnt, $output){
                $OWWUser = $this->getStrsBetween($output, "[", "]");
		$created = $this->cvtDate($output);
                $project = $this->getStrsBetween($output, "project=", ",");
                $type = $this->getStrsBetween($output, "type=", "]]");
                $user = '';
                $lab = '';
                $team = '';
                if ($type == 'IGEM'){
                        $team = str_replace("IGEM:", "", $project);
                        $page = "IGEM:$lab/$team/2009/Notebook/$project";
                }else if ($type == 'USER'){
                        $user = $this->getStrsBetween($output, "base=", "/");
                        $page = "$user/Notebook/$project";
                }else if ($type == 'LAB'){
                        $lab = $this->getStrsBetween($output, "base=", ":");
                        $page = "$lab:Notebook/$project";
                }
                $url = "http://openwetware.org/wiki/$page";
                $link = "<a href=\"$url\">$url</a>";
                $ebUrl =
                $this->out ("Notebook Number", $cnt);
                $this->out ("Time Created", "$mo/$d/$y $h:$mi:$s");
                $this->out ("OWWUser", "$OWWUser");
                $this->out ("Notebook Exists", $this->isActive($page));
                $this->out ("User", $user);
                $this->out ("Number of pages", $this->pagesInNotebook($page));
                $this->out ("Last update", $this->lastUpdate($page));
                $this->out ("Type", $type);
                $this->out ("User", $user);
                $this->out ("Lab", $lab);
                $this->out ("Team", $team);
                $this->out ("Project", $project);
                $this->out ("Url", $url);
                $this->out ("Link", $link);
                $this->out ('', '');
                $this->out ('', '');
                //echo ("Log:<br />\n$output<br />\n<br />\n");
        }

	function getLabInfo($cnt, $output){
		$OWWUser = $this->getStrsBetween($output, "[", "]");
		$created = $this->cvtDate($output);
		$project = $this->getStrsBetween($output, "project=", ",");
		$type = $this->getStrsBetween($output, "type=", "]]");
		$user = '';
		$lab = '';
		$team = '';

		if ($type == 'IGEM'){
			$team = $this->getStrsBetween($output, "base=", "/");
		        $team = str_replace("IGEM:", "", $team);
		        $page = "IGEM:$team/2009/Notebook/$project";
		}else if ($type == 'USER'){
		        $user = $this->getStrsBetween($output, "base=", "/");
		        $page = "$user/Notebook/$project";
		}else if ($type == 'LAB'){
			$lab = $this->getStrsBetween($output, "base=", ":");
		        $page = "$lab:Notebook/$project";
		}

		$url = "/wiki/".str_replace(" ", "_", $page);
		$exists = $this->isActive($page);
		$pages = $this->pagesInNotebook($page);
		$last_update = $this->cvtTimeDate($this->lastUpdate($page));

		$data = array();
		$data['number'] = substr("    ",0,4-strlen($cnt)).$cnt;
		$data['type'] = $type;
		$data['year'] = ($type == 'IGEM') ? '2009' : '';
		$data['pages'] = substr("    ",0,4-strlen($pages)).$pages;
		$data['time_created'] =  $created;

		$data['exists'] = $exists ? 'Yes' : 'No';
		$data['last_update'] = $last_update;

                $data['create_user'] = "<a href=\"/wiki/User:".
                        str_replace(" ", "_", $OWWUser)."\">$OWWUser</a>";

		$data['user'] = ($exists && !empty($user)) ? 
			"<a href=\"/wiki/".str_replace(" ", "_", $user)."\">".
				substr($user, (strpos($user, 'User:'))+ strlen('User:')).
				"</a>" : $user;
		$data['lab'] = ($exists && !empty($lab)) ? 
			"<a href=\"/wiki/".str_replace(" ", "_", $lab)."\">$lab</a>" : $lab;
		$data['team'] = ($exists && !empty($team)) ? 
			"<a href=\"/wiki/IGEM:".str_replace(" ", "_", $team)."/2009\">$team</a>" : $team;
                $data['project'] = ($exists) ?
                                "<a href=\"$url\">".$project."</a>" : $project;

		return $data;
	}

        function viewLog2(){
                $cnt = 1;
                $output = '';

                if ($this->logFile && is_file($this->logFile)){
                        $content = file($this->logFile);
                        foreach ($content as $line){
                                if (strpos($line, "createContent: base=") !== false){
                                        if (!empty($output)){
                                                $this->renderLabInfo($cnt, $output);
                                                $cnt++;
                                                $output = '';
                                        }
                                }
                                $output .= $line."<br />\n";
                        }
                        if (!empty($output)){
                                $this->renderLabInfo($cnt, $output);
                        }
                }else{
                        echo "Log file is empty.<br />";
                }
        }

	function viewLog5(){
		$cnt = 1;
		$data = array();
		$notebooks = array();
		if ($this->logFile && is_file($this->logFile)){
        		$content = file($this->logFile);
			foreach ($content as $line){
				if (strpos($line, "createContent: base=") !== false){
					if (!empty($output)){
						$data = $this->getLabInfo($cnt, $output);
						$cnt++;
						$notebooks[] = $data;
						$output = '';
					}
				}
				$output .= $line."<br />\n";
			}
                        if (!empty($output)){
                                $data = $this->getLabInfo($cnt, $output);
				$notebooks[] = $data;
                        }
			echo $this->renderTable($notebooks);
		}else{
			echo "Log file is empty.<br />";
        	}
	}

        function viewLog4(){
                $cnt = 1;
                $data = array();
                $notebooks = array();
                if ($this->logFile && is_file($this->logFile)){
                        $content = file($this->logFile);
                        foreach ($content as $line){
                                if (strpos($line, "createContent: base=") !== false){
                                        if (!empty($output)){
                                                $data = $this->getLabInfo($cnt, $output);
                                                $cnt++;
                                                $notebooks[] = $data;
                                                $output = '';
                                        }
                                }
                                $output .= $line."<br />\n";
                        }
                        if (!empty($output)){
                                $data = $this->getLabInfo($cnt, $output);
                                $notebooks[] = $data;
                        }
                        echo $this->renderTable2($notebooks);
                }else{
                        echo "Log file is empty.<br />";
                }
        }

       function renderTable2($nbs){
                foreach ($nbs[0] as $n => $v){
               		$headers[] = str_replace("'", "\'", $n);
                }
		$cells = array();
		$types = array();
		$pages = 0;
                foreach ($nbs as $nb){
			$row = array();
                        foreach ($nb as $n => $v){
				if ($n == 'pages') $pages += $v;
                        	if ($n == 'type') $types[$v] += 1;
                                $row[] = str_replace("'", "\'", $v);
                        }
			$cells[] = $row;
			unset($row);
                }
		$grid = $this->renderGrid($headers, $cells, '/js/os3grid.js', '/css/os3grid.css', $pages, $types);
		echo $grid;
        }

	function renderTable($nbs){
		$output = "<table border=\"1\">";

		$output .= "<tr>";
		foreach ($nbs[0] as $n => $v){
                                $output .= "<th>$n</th>";
                }
		$output .= "</tr>";

		foreach ($nbs as $nb){
			$output .= '<tr>';
			foreach ($nb as $n => $v){
				$output .= "<td>$v</td>";
			}
			$output .= "</tr>";
		}
		$output .= "</table>";
		return $output;
	}

	function viewLog(){
		if ($this->logFile && is_file($this->logFile)){
                	$content = file_get_contents($this->logFile);
			$content = str_replace("\n", "<br />\n", $content);
			echo $content;
		}else{
			echo "Log file is empty.<br />";
		}
        }

	function log($l){
		global $wgUser;

		if ($this->loggingEnabled && $this->logFile){
			$u = $wgUser->getName();
			$f = fopen($this->logFile, "a");
			$d = date('c');
			fwrite($f, "$d:[$u] [[$l]]\n");
			fclose($f);
		}
	}

	function execute(){
		global $wgUser;

		$this->lab = str_replace("'", '', $this->lab);
		$this->project = str_replace("'", '', $this->project);
		$this->institution = str_replace("'", '', $this->institution);

		// is user logged in?
		if (!$wgUser->isLoggedIn()){
			// no. exit.
			$name = $wgUser->getName();
	  		$this->error = $this->nbErrors['nberrornotloggedin'];
			return $this->redirect();
		}

		switch($this->type){
		    case 'LAB':
			if (!$this->lab){
                                $this->error = $this->nbErrors["nberrornolabinreq"];
                                return $cn->redirect();
                        }
                        // Add the prefix and the current IGEM year
                        $labPage = Title::newFromText($this->lab);
                        if (!$labPage || !$labPage->exists()){
				$this->error = str_replace('$1', $this->lab, 
					$this->nbErrors['nberrornolab']);
                                return $this->redirect();
                        }
                        $this->base = $this->lab.":";
			$this->page = $this->base.'Notebook'.'/'.$this->project;
			break;

		   case 'IGEM':
                        if (!$this->institution){
				$this->error = str_replace('$1', $this->lab, 
					$this->nbErrors['nberrornoinstitutioninreq']);
                                return $cn->redirect();
                        }
			// add the prefix and the current IGEM year
			$team = "IGEM:$this->institution/$this->year";
			$teamPage = Title::newFromText($team);
			if (!$teamPage || !$teamPage->exists()){
				$this->error = str_replace('$1', $this->lab, 
					$this->nbErrors['nberrornoigemteam']);
				$this->error = str_replace('$2', 
					$this->team, $this->error);
				return $this->redirect();
			}
			$this->base = $team.'/';
			$this->page = $this->base.'Notebook'.'/'.$this->project;
			break;

		    case 'USER':
			// see if the user already has 
			// a personal notebook
			if (!$wgUser->getUserPage()){
				$this->error = str_replace('$1', $wgUser->getName(), 
					$this->nbErrors['nberrornouserpage']);
				return $this->redirect();
 			}
			$userPageTitle = $wgUser->getUserPage();
			$this->base = "User:".$userPageTitle->getText()."/";
			$this->page = $this->base.'Notebook'.'/'.$this->project;
			break;

		    default:
			$this->error = str_replace('$1', $this->type, 
				$this->nbErrors['nberrorinvalidtype']);
			$this->error = "Invalid type specified";
			return $this->redirect();
		}

		// check to make sure the project doesn't already exist.
		$pendingPagename = $this->base."Notebook/$this->project";
		$pendingTitle = Title::newFromText($pendingPagename);
		if ($pendingTitle->exists()){
			$this->error = str_replace('$1', $this->project, 
				$this->nbErrors['nberrorprojectexists']);
			$this->error = str_replace('$2', $this->base, $this->error);
			return $this->redirect();
		}

		// good to go. create the pages.
		$this->createContent($this->base);
		return $this->redirect();
	}

	function redirect(){
		if ($this->error){
			$this->message = "$this->error,Error=1";
		}else{	
		    switch ($this->type){
                        case "USER":
                                $this->message = str_replace('$1', $this->page,
					$this->nbErrors['nbsuccesspersonal']);
                                break;
                        case "IGEM":
                                $this->message = str_replace('$1', $this->page, 
					$this->nbErrors['nbsuccessigemteam']);
                                break;
                        case "LAB":
                                $this->message = str_replace('$1', $this->page, 
					$this->nbErrors['nbsuccesslab']);
                                break;
                    }
		}
        	header("Location: ".$this->oneClickPage.'?Message='.$this->message);
	}

	function createContent(){
		$this->log("createContent: " .
			"base=$this->base, " .
			"project=$this->project, " .
			"type=$this->type");
		
		// create the notebook page
		$nb = $this->base.$this->notebookName;
		$t = Title::newFromText($nb);
		if (!$t->exists()){
			$this->setPage($nb, $this->notebookContent);
			$this->log("created page $nb");
		}

		// see if the project page exists
		$p = $nb.'/'.$this->project;
		if ($this->type == "IGEM"){
			$projectContent = $this->IGEMProjectContent;
		}else{
			$projectContent = $this->projectContent;
		}
		//$projectContent = $this->setProjectText($projectContent, $this->project); 
                $projectTitle = Title::newFromText($p);
		$this->log("ProjectContent: $projectContent.");
                if (!$projectTitle->exists()){
			$this->setPage($p, $projectContent);
			$this->log("created page $p");
		}else{
			// exit: project already exists
			$this->error = "Project page exists";
			$this->redirect();
		}

		// see if the entry content page exists
		$e = $p.'/'.$this->entryName;
                if ($this->type == "IGEM"){
                        $entryContent = $this->IGEMEntryContent;
                }else{
                        $entryContent = $this->entryContent;
                }
		//$entryContent = $this->setProjectText($entryContent, $this->project);
		$entryBaseTitle = Title::newFromText($e);
                if (!$entryBaseTitle->exists()){
			// save the page. 
			$this->log("Setting $e with the contents of page $entryContent.");
			$this->setPage($e, $entryContent);
			$this->log("created page $e");
		}
		$this->saveDetails($nb, $p);
	}

	function saveDetails($basePage, $projectPage){
		$ln = new LabNotebook();
		$ln->setType($this->type);
		$ln->setProject($this->project);
		$ln->setInstitution($this->institution);
		$ln->setLab($this->lab);
		$ln->setPageName($projectPage);
		$ln->setBasePageName($basePage);
		$ln->save();
	}

	// check to see if a page exists
	function checkPage($page){
		echo "checking page $page\n";
		$t = Title::newFromText($page);
		$result = $t->exists();
		if (!$result)
			$this->log("page $page doesn't exist.\n");
		return $result;
	}

	// create and fill a page if it's not already there.
	function setPage($name, $contentPage){
		$this->log("setPage: called with $name and $contentPage");
		$t = Title::newFromText($name);
		if (!$t->exists()){
			$this->log("attempting to create page $name");
			$content = $this->getContent($contentPage);
			//$content='this is a test';
        		$a = new Article($t);
			$this->log("setPage: new article created for page $name");
			// $this->log("setPage: content=$content");
			if ($this->testMode == false){
        			$output = $a->doEdit($content, 
					"Autocreated Lab Notebook name=$name,".
					" content from $contentPage", 
					EDIT_NEW);
			}
			$this->log("setPage: content added to page $name");
    		}
	}

	// get the default page content
	function getContent($page){
		// get the name
		$this->log("getContent: reading page $page");
	
		$name = str_replace(" ", "_", $name);
		$t = Title::newFromText($page);

		// see if the page exists
		if ($t && $t->exists()){
	        	// it does. get the article
			$this->log("getContent: page $page exists.");
			$a = new Article($t);

			// retrieve the content
			$this->log("getContent: read article $page");
			$wt = $a->getContent();

			// remove the category tag from the template
			$wt = str_replace($this->categoryTag, '', $wt);

			// return the filtered page text
			return $wt;
		}
    		return '';
	}
}

?>
