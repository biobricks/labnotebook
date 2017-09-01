<?php

require_once ("includes/CreateNotebook.php");

function unescapeChars($str){
	$str = str_replace("%27", "''", $str);
	return $str;
}

$cn = new CreateNotebook();
if (isset($_POST['OneClickSubmit'])){
$cn->year = "2009";
$cn->type = isset($_POST['Type']) ? trim(unescapeChars(htmlentities(strip_tags($_POST['Type'])))): '';
        $cn->institution = isset($_POST['Institution']) ? trim(unescapeChars(htmlentities(strip_tags($_POST['Institution'])))): '';
        $cn->lab = isset($_POST['Lab']) ? trim(unescapeChars(htmlentities(strip_tags($_POST['Lab'])))): '';
        $cn->project = isset($_POST['Project']) ? trim(unescapeChars(htmlentities(strip_tags($_POST['Project'])))): '';
        $cn->username = isset($_POST['Username']) ? trim(unescapeChars(htmlentities(strip_tags($_POST['Username'])))): '';
        if (!$cn->project){
                $cn->error = "No project name specified.";
                return $redirect();
        }
        $cn->execute();
}else{
        $cn->error = "Invalid request";
        return $cn->redirect();
}
?>
