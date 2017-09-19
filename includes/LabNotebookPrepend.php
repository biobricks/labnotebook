<?php

class LNPrepend {

private function lnGetUser ($titleText){
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

private function lnisValidDate ($i_sDate){
    $blnValid = true;
    // check the format first (may not be necessary as we use checkdate() below)
    if(!ereg ("^[0-9]{4}/[0-9]{2}/[0-9]{2}$", $i_sDate)){
        $blnValid = false;
    }else{
        //format is okay, check that days, months, years are okay
        $arrDate = explode("/", $i_sDate); // break up date by slash
        $intYear = $arrDate[0];
        $intMonth = $arrDate[1];
        $intDay = $arrDate[2];
        $intIsDate = checkdate($intMonth, $intDay, $intYear);
        if(!$intIsDate){
            $blnValid = false;
        }
    }
    return ($blnValid);
}

public static function wfLabNotebookPrepend($editpage) {
    // EditFormPreloadText
    global $wgOut;
    global $wgUser;
    global $wgLabNotebookNamespace;

    if ($wgLabNotebookNamespace == '')
	return true;

    $wgOut->enableClientCache(false);
    
    // Get the text of the current page title
    $pageName = $editpage->mArticle->mTitle->getText();

    // Get the username
    $username = lnGetUser ($pageName);
 
    // Set the base URL entries
    $notebookBase = $username; 
    $entryBase = "$notebookBase/";
    $projectBase = "$notebookBase/Projects/";

    $entryBaseDef = "$notebookBase/Entry Base";
    $projectBaseDef = "$notebookBase/Project Base";

    // Get the default new page definiton files
    $entryBaseContent = "MediaWiki:EntryContentDefault";
    $projectBaseContent = "MediaWiki:ProjectContentDefault";
    $notebookBaseContent = "MediaWiki:Lab_Notebook_Base";
 
    // Clear page title 
    $basePageTitle = '';

    // Check for a valid notebook page to fill in...
    if (!$editpage->preview
            && !$editpage->mArticle->mContentLoaded
            && $editpage->mArticle->mTitle->mNamespace == 
			$wgLabNotebookNamespace){

        // Check for project base
        if ($pageName == $projectBaseDef){
            $basePageTitle = Title::newFromText($projectBaseContent);
        }

        // Check for entry base
        else if ($pageName == $entryBaseDef){
            $basePageTitle = Title::newFromText($entryBaseContent);
        }

        // Check for a new project
        else if ( (substr($pageName, 0, strlen($projectBase)) == $projectBase) &&
                        (strlen($pageName) > strlen($projectBase))){
            $t = Title::newFromText("Notebook:$projectBaseDef");
            if ($t->exists()){
                $basePageTitle = $t;
            }else{
                $basePageTitle = Title::newFromText($projectBaseContent);
            }
        }

        // Check for a new entry
        else if ( (substr($pageName, 0, strlen($entryBase)) == $entryBase) &&
                        (strlen($pageName) == (strlen($entryBase)+10))){
            if (lnisValidDate (substr($pageName, strlen($entryBase)))){
                $t = Title::newFromText("Notebook:$entryBaseDef");
                if ($t->exists()){
                    $basePageTitle = $t;
                }else{
                    $basePageTitle = Title::newFromText($entryBaseContent);
                }
            }
        }

        if ($basePageTitle){
            $basePageArticle = new Article($basePageTitle);
            $editpage->textbox1 = $basePageArticle->GetContent();
            $editpage->textbox2 = $editpage->textbox1;
        }
    }
    return true;
}
}
