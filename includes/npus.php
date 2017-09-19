<?php
class npusdo extends ApiBase{
    public function getAllowedParams() {
        return array(
            'n' => array(
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_DFLT => '5',
            ),
            'name' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_DFLT => 'Bob',
            ),
        );
    }
	public function isWriteMode() {
		return true;
	}
	public function execute() {
        $params = $this->extractRequestParams();
		$this->getResult()->addValue( null, 'npus', "the npus ".$params['name']." has ".$params['n']." tentacles");
        return $this->getResult();
	}
}
