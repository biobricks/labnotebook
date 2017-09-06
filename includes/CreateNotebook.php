<?php

class NewNotebookDo extends ApiBase{

	var $oneClickPage = '/wiki/Special:NewNotebook';
	var $categoryTag = '[[category:OWWLabNotebookV1]]';
	var $nbErrors = array();
        var $notebookContent = "MediaWiki:NotebookContentDefault";
        var $projectContent = "MediaWiki:ProjectContentDefault";
        var $entryContent = "MediaWiki:EntryContentDefault";
        var $notebookName = "Notebook";
        var $entryName = "Entry Base";
        var $error = '';
        var $message = '';
        var $testMode = false;
        var $project = '';
        var $base = '';
        var $nbtype = '';
        var $username = '';
        var $lab = '';
        var $page = '';
        var $nbContent = '';

    public function getAllowedParams() {
        return array(
            'nbtype' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_DFLT => 'User',
            ),
            'Project' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_DFLT => 'a project',
            ),
            'Lab' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_DFLT => 'a lab or user',
            ),
        );
    }

	public function isWriteMode() {
		return true;
	}

	function renderGrid($headers, $cells, $jsLocation, $cssLocation, 
			$pages='', $types=''){
		global $wgOut;

        	$wgOut->addScript("<script src=\"$jsLocation\" type=\"text/javascript\"></script>\n");
        	// $wgOut->addStyle("<link type=\"text/css\" rel=\"stylesheet\" href=\"$cssLocation\" />");
		$wgOut->addStyle($cssLocation);

		$script = "<div id=\"grid\"></div><script type=\"text/javascript\">var g = new OS3Grid ();" .
    			"g.set_headers ('" . implode("', '", $headers) . "');" .
    			"g.set_scrollbars ( true );" .
	    		"g.set_border ( 1, \"solid\", \"#cccccc\" );";

		foreach ($cells as $row){
			$script .= "g.add_row ('" . implode("', '", $row) . "');";
		}
		$script .= "g.set_sortable ( true );" .
			"g.set_highlight ( true );" .
			"g.render ( 'grid' );</script>";

		$wgOut->addHtml($script);
		return true;
	}

	function __construct(){

        parent::__construct();

		$this->nbErrors = array(
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
			'nberrornotloggedin' => 'You must be logged in to create a new Lab Notebook '.
                	'Please log in and try again. Thanks!',
			'nberrorinvalidtype' => 'Lab Notebooks must be USER or LAB. '.
                	'Please correct this and try again. Thanks!',
			'nberrorinvalidrequest' => 'This is not a valid request to create a Lab Notebooks. ' .
                	'Please use a valid request to try again. Thanks!',
			'nbsuccesslab' => 'Congratulations! Your lab notebook ' .
                	'has been created with success. ' .
                	'You can visit it <a href="/wiki/$1">here</a>',
			'nbsuccesspersonal' => 'Congratulations! Your lab notebook ' .
                	'has been created with success. ' .
                	'You can visit it <a href="/wiki/$1">here</a>');
	}

        function getStrsBetween($s,$s1,$s2) {
                $pos_s = strpos($s,$s1) + strlen($s1);;
                $newS = substr($s, $pos_s);
                $pos_e = strpos($newS,$s2);
                return substr($newS, 0, $pos_e);
        }

	function setProjectText($content, $project){
		wfDebug("Content before: $content");
		$content = str_replace('#PROJECT#',$project, $content);
		wfDebug("Content after: $content");
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
			"where page_title like '$title%' " .
			"order by page_touched desc limit 1";
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
		//echo ("$name: $value<br />\n");
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

	function getLabInfo($cnt, $output){
		$OWWUser = $this->getStrsBetween($output, "[", "]");
		$created = $this->cvtDate($output);
		$project = $this->getStrsBetween($output, "project=", ",");
		$nbtype = $this->getStrsBetween($output, "nbtype=", "]]");
		$user = '';
		$lab = '';

		if ($nbtype == 'USER'){
		        $user = $this->getStrsBetween($output, "base=", "/");
		        $page = "$user/Notebook/$project";
		}else if ($nbtype == 'LAB'){
			$lab = $this->getStrsBetween($output, "base=", ":");
		        $page = "$lab:Notebook/$project";
		}

		$url = "/wiki/".str_replace(" ", "_", $page);
		$exists = $this->isActive($page);
		$pages = $this->pagesInNotebook($page);
		$last_update = $this->cvtTimeDate($this->lastUpdate($page));

		$data = array();
		$data['number'] = substr("    ",0,4-strlen($cnt)).$cnt;
		$data['nbtype'] = $nbtype;
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
		$data['Lab'] = ($exists && !empty($lab)) ? 
			"<a href=\"/wiki/".str_replace(" ", "_", $lab)."\">$lab</a>" : $lab;
                $data['Project'] = ($exists) ?
                                "<a href=\"$url\">".$project."</a>" : $project;

		return $data;
	}

        function renderTable($nbs){
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
		$this->renderGrid($headers, $cells,
			 '/js/os3grid.js', '/css/os3grid.css', $pages, $types);
        }

	public function execute() {
		global $wgUser;

        $params = $this->extractRequestParams();
        $this->nbtype = $params['nbtype'];
        $this->project = str_replace("'", '', $params['Project']);
        $this->lab = str_replace("'", '', $params ['Lab']);

		// is user logged in?
		if (!$wgUser->isLoggedIn()){
			// no. exit.
			$name = $wgUser->getName();
	  		$this->error = $this->nbErrors['nberrornotloggedin'];
			return $this->redirect();
		}

		switch($this->nbtype){
		    case 'LAB':
			if (!$this->lab){
                                $this->error = $this->nbErrors["nberrornolabinreq"];
                                $this->getResult()->addValue( null, 'newnotebook', 'no lab, we are redirecting you' );
                                return $cn->redirect();
                        }
                        // Add the prefix and the current IGEM year
                        $labPage = Title::newFromText($this->lab);
                        if (!$labPage || !$labPage->exists()){
				$this->error = str_replace('$1', $this->lab, 
					$this->nbErrors['nberrornolab']);
                $this->getResult()->addValue( null, 'newnotebook', 'no lab, we are redirecting you' );
                                return $this->redirect();
                        }
                        $this->base = $this->lab.":";
			$this->page = $this->base.'Notebook'.'/'.$this->project;
			break;

		    case 'USER':
			// see if the user already has 
			// a personal notebook
			if (!$wgUser->getUserPage()){
				$this->error = str_replace('$1', $wgUser->getName(), 
					$this->nbErrors['nberrornouserpage']);
                $this->getResult()->addValue( null, 'newnotebook', 'no user page, we are redirecting you' );
				return $this->redirect();
 			}
			$userPageTitle = $wgUser->getUserPage();
			$this->base = "User:".$userPageTitle->getText()."/";
			$this->page = $this->base.'Notebook'.'/'.$this->project;
			break;

		    default:
			$this->error = str_replace('$1', $this->nbtype,
				$this->nbErrors['nberrorinvalidtype']);
			$this->error = "Invalid type specified";
            $this->getResult()->addValue( null, 'newnotebook', 'error or invalid type, we are redirecting you' );
			return $this->redirect();
		}

		// check to make sure the project doesn't already exist.
		$pendingPagename = $this->base."Notebook/$this->project";
		$pendingTitle = Title::newFromText($pendingPagename);
		if ($pendingTitle->exists()){
			$this->error = str_replace('$1', $this->project, 
				$this->nbErrors['nberrorprojectexists']);
			$this->error = str_replace('$2', $this->base, $this->error);
            $this->getResult()->addValue( null, 'newnotebook', 'duplicate, we are redirecting you' );
			return $this->redirect();
		}

		// good to go. create the pages.
		$this->createContent($this->base);
		$this->getResult()->addValue( null, 'newnotebook', 'success, we are redirecting you' );
		return $this->redirect();
	}

	function redirect(){
		if ($this->error){
			$this->message = "$this->error,Error=1";
		}else{	
		    switch ($this->nbtype){
                        case "USER":
                                $this->message = str_replace('$1', $this->page,
					$this->nbErrors['nbsuccesspersonal']);
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
		wfDebug("createContent: " .
			"base=$this->base, " .
			"project=$this->project, " .
			"nbtype=$this->nbtype");
		
		// create the notebook page
		$nb = $this->base.$this->notebookName;
		$t = Title::newFromText($nb);
		if (!$t->exists()){
			$this->setPage($nb, $this->notebookContent);
			wfDebug("created page $nb");
		}

		// see if the project page exists
		$p = $nb.'/'.$this->project;
		$projectContent = $this->projectContent;
                $projectTitle = Title::newFromText($p);
		wfDebug("ProjectContent: $projectContent.");
                if (!$projectTitle->exists()){
			$this->setPage($p, $projectContent);
			wfDebug("created page $p");
		}else{
			// exit: project already exists
			$this->error = "Project page exists";
			$this->redirect();
		}

		// see if the entry content page exists
		$e = $p.'/'.$this->entryName;
                $entryContent = $this->entryContent;
		$entryBaseTitle = Title::newFromText($e);
                if (!$entryBaseTitle->exists()){
			// save the page. 
			wfDebug("Setting $e with the contents of page $entryContent.");
			$this->setPage($e, $entryContent);
			wfDebug("created page $e");
		}
		$this->saveDetails($nb, $p);
	}

	function saveDetails($basePage, $projectPage){
		$ln = new LabNotebook();
		$ln->setType($this->nbtype);
		$ln->setProject($this->project);
		$ln->setLab($this->lab);
		$ln->setPageName($projectPage);
		$ln->setBasePageName($basePage);
		$ln->save();
	}

	// check to see if a page exists
	function checkPage($page){
		$t = Title::newFromText($page);
		$result = $t->exists();
		if (!$result)
			wfDebug("page $page doesn't exist.\n");
		return $result;
	}

	// create and fill a page if it's not already there.
	function setPage($name, $contentPage){
		wfDebug("setPage: called with $name and $contentPage");
		$t = Title::newFromText($name);
		if (!$t->exists()){
			wfDebug("attempting to create page $name");
			$content = $this->getContent($contentPage);
			$content->mText = str_replace($this->categoryTag, '', $content->mText);
            $a = WikiPage::factory($t);
			wfDebug("setPage: new article created for page $name");
			if ($this->testMode == false){
        			$output = $a->doEditContent($content, 
					"Autocreated Lab Notebook name=$name,".
					" content from $contentPage", 
					EDIT_NEW);
			}
			wfDebug("setPage: content added to page $name");
    		}
	}

	// get the default page content
	function getContent($page){
		// get the name
		wfDebug("getContent: reading page $page");
	
		$page = str_replace(" ", "_", $page);
		$t = Title::newFromText($page);

		// see if the page exists
		if ($t && $t->exists()){
	        	// it does. get the article
			wfDebug("getContent: page $page exists.");
			$a = WikiPage::factory($t);

			// retrieve the content
			wfDebug("getContent: read article $page");
			$wt = $a->getContent();
			return $wt;
		}
    		return '';
	}
}

?>
