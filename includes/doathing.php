<?php

class doathing extends ApiBase{
    public function getAllowedParams() {
        return array(
            'secret' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_DFLT => 'blah',
            ),
        );
    }
	public function isWriteMode() {
		return true;
	}
	public function execute() {
		global $wgUser;
		if (!$wgUser->isLoggedIn()) return;

        $params = $this->extractRequestParams();
        if ($params['secret'] != 'hg4rt') {
            $this->getResult()->addValue( null, 'athing',"we didn't do a thing");
            return $this->getResult();
        }
        $this->getResult()->addValue( null, 'athing',"we're doing a thing");
        $page = WikiPage::factory(Title::newFromText('User:Fermenter_User/Notebook/MOLD_C2'));
        $content = $page->getContent();
        $content->__construct(str_replace('6sRu1,38@6c-bq%_','uVmvKkXrHBr',$content->getNativeData()));
        $page->doEditContent($content,'replace text in fucked up page that loads forever',EDIT_UPDATE);
        $this->getResult()->addValue( null, 'athing',"we did a thing");
        return $this->getResult();
    }
}
