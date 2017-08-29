<?php

// Notebook storage object
require_once ('LabNotebook/LabNotebook.php');

class CreateNotebook{

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

        function CreateNotebook(){
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
			return 'False';
		if ($t->exists())
			return 'True';
		return 'False';
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

	function renderLabInfo($cnt, $output){
		$OWWUser = $this->getStrsBetween($output, "[", "]");
		$y = substr($output, 0, 4);
		$mo = substr($output, 5, 2);
		$d = substr($output, 8, 2);
		$h = substr($output, 11,2);
		$mi = substr($output, 14, 2);
		$s = substr($output, 17, 2);
		$project = $this->getStrsBetween($output, "project=", ",");
		$type = $this->getStrsBetween($output, "type=", "]]");
		$user = '';
		$lab = '';
		$team = '';
		if ($type == 'IGEM'){
		        $team = str_replace("IGEM:", "", $project);
		        $page = "IGEM:$team/2009/Notebook/$project";
		}else if ($type == 'USER'){
		        $user = $this->getStrsBetween($output, "base=", "/");
		        $page = "$user/Notebook/$project";
		}else if ($type == 'LAB'){
			$lab = $this->getStrsBetween($output, "base=", ":");
		        $page = "$lab:Notebook/$project";
		}
		$url = "http://openwetware.org/wiki/$page";
		$link = "<a href=\"$url\">$url</a>";
	
		$this->out ("{|");
                $this->out ("!Notebook Number");
                $this->out ("!Time Created");
                $this->out ("!OWWUser");
                $this->out ("!Notebook Exists");
                $this->out ("!User");
                $this->out ("!Number of pages");
                $this->out ("!Last update");
                $this->out ("!Type");
                $this->out ("!User");
                $this->out ("!Lab");
                $this->out ("!Team");
                $this->out ("!Project");
                $this->out ("!Url");
                $this->out ("!Link");
		$this->out ("|-");

		$this->out ("$cnt|");
		$this->out ("$mo/$d/$y $h:$mi:$s|");
		$this->out ("$OWWUser|");
		$this->out ($this->isActive($page)."|");
		$this->out ("$user|");
		$this->out ($this->pagesInNotebook($page)."|");
		$this->out ($this->lastUpdate($page)."|");
		$this->out ("$type|");
		$this->out ("$user|");
		$this->out ("$lab|");
		$this->out ("$team|");
		$this->out ("$project|");
		$this->out ("$url|");
		$this->out ("$link|");
		$this->out ("|-");
		$this->out ("|}");
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
