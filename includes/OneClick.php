<?php
 
$wgExtensionFunctions[] = "wfOneClick";
 
function wfOneClick() {
    global $wgParser;
    $wgParser->setHook("OneClick", "renderOneClick");
}

function getOneClickOptions(&$input,$name,$default,$isNumber=false) {
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

function renderOneClick($input) {
	global $wgOut, $wgParser, $wgUser;

	// disable parser cache
	$wgParser->disableCache();

	// load variables
	$jsfile = trim(getOneClickOptions($input, "javascriptfile",
		 "/js/oneclick.js"));

	// render header script
	$sc1 = "<script type=\"text/javascript\" src=\"$jsfile\"></script>\n";
    	$wgOut->addScript($sc1);
        return '';
}
?>
