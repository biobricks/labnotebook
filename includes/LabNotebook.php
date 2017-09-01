<?php

require_once("Title.php");
require_once("User.php");

class LabNotebook {
	var $mID = '';
	var $mNamespace = '';
	var $mTitle = '';
	var $mBaseTitle = '';
	var $mBaseNamespace = '';
	var $mType = '';
	var $mLab = '';
	var $mInstitution = '';
	var $mProject = '';
	var $mUser = '';
	var $mUser_text = '';
	var $mTimestamp = '';

	function LabNotebook(){
		global $wgIP, $wgTitle, $wgUser;
			
		// is the user logged in?
		if (is_object($wgUser) && $wgUser->isLoggedIn()){
			// yes. set user id and name
			$this->mUser_text = $wgUser->getName();
			$this->mUser = $wgUser->getId();						
		}else{
			// set the name (IP address)
			$this->mUser_text = $wgIP;
		}	
	}
	
	function setLab($lab){
		// set the message 
		$this->mLab = $lab;
	}

        function setType($type){
                // set the message
                $this->mType = $type;
        }

        function setProject($project){
                // set the message
                $this->mProject = $project;
        }

        function setInstitution($inst){
                // set the message
                $this->mInstitution = $inst;
        }

	function setPageTitle($title){
		// only do this if called with a title object
		if (is_object($title)){
			// get the title text
			$this->mTitle = $title->getText();
			
			// get the namespace (not the namespace text)
			$this->mNamespace = $title->getNamespace();
		}
	}
	
	function setPageName($name){
		// pull the page name from the title
		$title= Title::newFromText($name);

		// call setPageTitme method to store text and namespace
		$this->setPageTitle($title);
	}


        function setBasePageTitle($title){
                // only do this if called with a title object
                if (is_object($title)){
                        // get the title text
                        $this->mBaseTitle = $title->getText();

                        // get the namespace (not the namespace text)
                        $this->mBaseNamespace = $title->getNamespace();
                }
        }

        function setBasePageName($name){
                // pull the page name from the title
                $title= Title::newFromText($name);

                // call setPageTitme method to store text and namespace
                $this->setBasePageTitle($title);
        }

	function save(){
		$fname="LabNotebook::save";
		
		// set the timestamp
		$this->mTimestamp = gmdate( 'YmdHis' );

		// get the db reference object
		$dbw = wfGetDB( DB_MASTER );

		// create the query
		$sql = "INSERT INTO labnotebook (ln_namespace, ln_title, ln_base_namespace, ln_base_title, " .
				"ln_type, ln_project, ln_lab, ln_institution, ln_user, " .
				"ln_user_text, ln_timestamp) " .
				"VALUES (" .
					$dbw->addquotes($this->mNamespace).", " .
					$dbw->addquotes($this->mTitle).", " .
					$dbw->addquotes($this->mBaseNamespace).", " .
                                        $dbw->addquotes($this->mBaseTitle).", " .
					$dbw->addquotes($this->mType).", " .
					$dbw->addquotes($this->mProject).", " .
					$dbw->addquotes($this->mLab).", " .
					$dbw->addquotes($this->mInstitution).", " .
					$dbw->addquotes($this->mUser).", " .
					$dbw->addquotes($this->mUser_text).", " .
					$dbw->addquotes($this->mTimestamp).")";
		// execute the query
		$dbw->query($sql, $fname);
	}
}
?>
