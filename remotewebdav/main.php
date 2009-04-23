<?php
Cgn::loadModLibrary('Remotewebdav::Cgn_Service_Webdav', 'admin');

class Cgn_Service_Remotewebdav_Main extends Cgn_Service_Webdav {

	protected $defaultDavDir = 'templates/metro01';


	/**
	 * create a virtual directory showing boot/ templates/ and local-modules
	 */
	protected function getBaseDir($relativeFile=NULL) {

		if ($relativeFile == '' || $relativeFile == NULL) {
			return 'virtual/dir';
		}

		if (strpos($relativeFile, 'boot') ===0) {
			$b = BASE_DIR;
			return $b;
		}

		if (strpos($relativeFile, 'templates') === 0) {
			$b = BASE_DIR;
			return $b;
		}

		if (strpos($relativeFile, 'local-modules') === 0) {
			$b = BASE_DIR.'cognifty/';
			return $b;
		}

		if (strpos($relativeFile, 'local-admin') === 0) {
			$b = BASE_DIR.'cognifty/';
			return $b;
		}

		return 'nonexitent/dir';
	}


	/**
	 * By default, it calls $this->dirList() with the 
	 * a directory derived from the URL, or 
	 * _propfindFile with the full path of the file in question.
	 *
	 *
	 * Return a virutal file system if the $dir == 'virtual/dir'
	 */
	public function propfindEvent($req, &$t) {
		//parse out dir path
		$dir = $this->_parseRelativeFile($req);

		//$t['webdavDebug'] = print_r($dir, 1);

		$baseDir = $this->getBaseDir($dir);

		if ($baseDir == 'virtual/dir') {
			$this->responseList = $this->virtualDirList();
		} else {
			//parent::propfindEvent($req, $t);
			if (is_dir($baseDir.$dir.'/')) {
				$baseDir .= $dir.'/';
				$this->responseList = $this->dirList($baseDir);
			} else {
				//it's a file, propfind it
				$baseFile = $baseDir.$dir;
				$this->responseList[] = $this->_propfindFile($baseDir, $dir);
			}
		}
	}


	/**
	 * Return a virutal directory list of local-modules, templates, etc.
	 */
	function virtualDirList() {
		$responseList = array();
		$response = new Cgn_Webdav_Response('templates');
		$response->addProp('displayName', 'templates');
		$response->setResourceType('collection');
		$responseList[] = $response;

		$response = new Cgn_Webdav_Response('local-modules');
		$response->addProp('displayName', 'local-modules');
		$response->setResourceType('collection');
		$responseList[] = $response;

		$response = new Cgn_Webdav_Response('local-admin');
		$response->addProp('displayName', 'local-admin');
		$response->setResourceType('collection');
		$responseList[] = $response;

		$response = new Cgn_Webdav_Response('boot');
		$response->addProp('displayName', 'boot');
		$response->setResourceType('collection');
		$responseList[] = $response;

		return $responseList;
	}

}
