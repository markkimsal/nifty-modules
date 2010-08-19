<?php
/**
   HTTP/1.1 207 Multi-Status
   Content-Type: text/xml; charset="utf-8"
   Content-Length: xxxx

   <?xml version="1.0" encoding="utf-8" ?>
   <D:multistatus xmlns:D='DAV:'>
     <D:response>
          <D:href>http://www.foo.bar/container/</D:href>
          <D:propstat>
               <D:prop>
                    <D:lockdiscovery>
                         <D:activelock>
                              <D:locktype><D:write/></D:locktype>
                              <D:lockscope><D:exclusive/></D:lockscope>
                              <D:depth>0</D:depth>
                              <D:owner>Jane Smith</D:owner>
                              <D:timeout>Infinite</D:timeout>
                              <D:locktoken>
                                   <D:href>
               opaquelocktoken:f81de2ad-7f3d-a1b2-4f3c-00a0c91a9d76
                                   </D:href>
                              </D:locktoken>
                         </D:activelock>
                    </D:lockdiscovery>
               </D:prop>
               <D:status>HTTP/1.1 200 OK</D:status>
          </D:propstat>
     </D:response>
   </D:multistatus>
*/

class Cgn_Service_Webdav extends Cgn_Service {

	protected $headerStatus  = 'HTTP/1.1 200 OK';
	protected $defaultDavDir = 'non/existant';
	protected $responseList  = array();
	public    $presenter     = 'self';

	public function authorize($e, $u) {
		if ($u->isAnonymous() && isset($_SERVER['PHP_AUTH_USER'])) {
			$req  = Cgn_SystemRequest::getCurrentRequest();
			$req->getvars['email'] = $_SERVER['PHP_AUTH_USER'];
			$req->getvars['password'] = $_SERVER['PHP_AUTH_PW'];

			if ($u->login($req->cleanString('email'),
				$req->cleanString('password'))) {
				$u->bindSession();
				$this->user = $u;
				$this->emit('login_success_after');
				unset($this->user);
			}
		}

		if (!($u->belongsToGroup('admin') || $u->belongsToGroup('radmin')) || !isset($_SERVER['PHP_AUTH_USER'])) {
			$this->presenter = 'none';
			header('HTTP/1.0 401 UNAUTHORIZED');
			if (isset($_SERVER['HTTPS']))  {
				header('WWW-Authenticate: Basic realm="SSL Secure remote administration"');
			} else {
				header('WWW-Authenticate: Basic realm="NOT SECURE, submitting password not recommended"');
			}
			header('Content-Type: text/xml; charset="utf-8"');
			exit();
		}

		return TRUE;
	}

	public function processEvent($e, $req, &$t) {

		if (@$_SERVER['REQUEST_METHOD'] == 'PROPFIND') {
			$eventName = 'propfindEvent';
		} else if (@$_SERVER['REQUEST_METHOD'] == 'PUT') {
			$eventName = 'putEvent';
		} else if (@$_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			$eventName = 'optionsEvent';
		} else if (@$_SERVER['REQUEST_METHOD'] == 'GET') {
			$eventName = 'getEvent';
		} else if (@$_SERVER['REQUEST_METHOD'] == 'MKCOL') {
			$eventName = 'mkcolEvent';
		} else {
			$eventName  = strtolower($_SERVER['REQUEST_METHOD']).'Event';
		}

/*
$tmp = fopen('/tmp/ooodebug.txt', 'a');
fwrite($tmp, print_r($_SERVER,1));
//fwrite($tmp, print_r($_POST,1));
ob_start();
$f = fopen('php://input', 'r');
while (!feof($f)) {
//ob_get_contents()
	fwrite($tmp, fread($f, 46)."\n");
}
fwrite($tmp, "=========== calling: ".$eventName." =============\n\n");
ob_end_clean();

fwrite($tmp, str_repeat("=", 80)."\n");
fclose($tmp);
// */
		if (method_exists($this, $eventName) ) {
			$this->$eventName($req,$t);
		} else {
			$this->headerStatus = 'HTTP/1.1 503 Service Unavailable';
		//	Cgn_ErrorStack::throwError('no such event: '.$e, 480);
		}
	}

	/**
	 * Return the requested directory from the current URL
	 */
	public function _parseRelativeFile($req) {
		$file = '';
		$requestUri = urldecode($_SERVER['REQUEST_URI']);
		$filename   = str_replace( $_SERVER['SCRIPT_NAME'], '', $requestUri);

		$filename = str_replace('//', '/', $filename);
		$filename = str_replace('..', '', $filename);

		if (substr($filename, -1, 1 ) == '/') {
			$filename = substr($filename, 0, -1);
		}
		$filedirs   = explode('/', $filename);
		//drop ''
		array_shift($filedirs);
		//drop 'm.s.e'
		array_shift($filedirs);

		$file .= implode('/', $filedirs);
		return $file;
	}

	public function mainEvent($req, &$t) {
	}

	public function getEvent($req, &$t) {

		$file = $this->_parseRelativeFile($req);
		$baseDir = $this->getBaseDir($file);
		$filename =  $baseDir.$file;

		$t['contentType'] = Cgn_Webdav_Response::_filenameToMime($filename);
		$t['sendFile']     = $filename;
		/*
		 * not used, propfind before get gets the filename
		$t['sendFileName'] = $file;
		 */
	}


	public function putEvent($req, &$t) {

		$filename = $this->_parseRelativeFile($req);
		$baseDir = $this->getBaseDir($filename);
//		$t['webdavDebug'] .= 'writing to '.$requestUri. ' -> '.$baseDir.$filename;
		$file = fopen( $baseDir.$filename, 'w');

		if (!$file) {
//			$t['webdavDebug'] .= 'canot open file.';
			$this->headerStatus = 'HTTP/1.1 500 Internal Server Error';
			return false;
		}
//		$t['webdavDebug'] .= 'writing to '.$requestUri. ' -> '.$baseDir.$filename;
		$raw = fopen('php://input', 'r');
		while (!feof($raw)) {
		//ob_get_contents()
			fwrite($file, fread($raw, 4096));
		}
		fclose($file);
		fclose($raw);
	}

	/**
	 * Create a directory on the filesystem
	 */
	public function mkcolEvent($req, &$t) {

		$filename = $this->_parseRelativeFile($req);
		$baseDir = $this->getBaseDir($filename);
//		$t['webdavDebug'] .= 'mkdir  '.$requestUri. ' -> '.$baseDir.$filename;
		$result = @mkdir( $baseDir.$filename);

		if (!$result) {
//			$t['webdavDebug'] .= 'canot open file.';
			$this->headerStatus = 'HTTP/1.1 500 Internal Server Error';
			return false;
		} else {
			$this->headerStatus = 'HTTP/1.1 201 Created';
		}
	}


	public function optionsEvent($req, &$t) {
		header('DAV: 1, 2'); //, access-control, workspace');
		header('Allow: OPTIONS, HEAD, GET, POST, PUT, PROPFIND< PROPPATCH, ACL');

		$t['webdavResponse'] = '<?xml version="1.0" encoding="utf-8" ?>
<D:options-response xmlns:D="DAV:"></D:options-response>';
	}

	/**
	 * By default, it calls $this->dirList() with the 
	 * a directory derived from the URL, or 
	 * _propfindFile with the full path of the file in question.
	 */
	public function propfindEvent($req, &$t) {
		//parse out dir path
		$dir = $this->_parseRelativeFile($req);

		$baseDir = $this->getBaseDir($dir);
		/*
		$t['webdavDebug'] .= '========== propfind event ========'."\n";
		$t['webdavDebug'] .= print_r($dir, 1);
		$t['webdavDebug'] .= print_r($baseDir, 1);
		 */
		if (is_dir($baseDir.$dir.'/')) {
			$baseDir .= $dir.'/';
			$this->responseList = $this->dirList($baseDir);
		} else {
			//it's a file, propfind it
			$baseFile = $baseDir.$dir;
			$this->responseList[] = $this->_propfindFile($baseFile);
		}

		//$t['webdavResponse'] = file_get_contents( dirname(__FILE__).'/webdav_output.txt');
		/*
$t['webdavResponse'] = '<?xml version="1.0" encoding="utf-8" ?>
   <D:multistatus xmlns:D=\'DAV:\'>
     <D:response>
          <D:href>http://www.foo.bar/container/</D:href>
          <D:propstat>
               <D:prop>
				<D:resourcetype />
              </D:prop>
               <D:status>HTTP/1.1 200 OK</D:status>
          </D:propstat>
     </D:response>
   </D:multistatus>
';
		 */
	}

	protected function getBaseDir($relativeFile=NULL) {
		$b = BASE_DIR.$this->defaultDavDir;
		if (!is_dir($b)) {
			mkdir($b);
		}
		if (substr($b, -1, 1 ) !== '/') {
			$b .= '/';
		}
		return $b;
	}

	function dirList($dir) {
		$responseList = array();

		//only dirlist on dirs
		if (!is_dir($dir)) {
			$this->headerStatus = 'HTTP/1.1 503 Service Unavailable';
			return $resposneList;
		}

		$response = new Cgn_Webdav_Response('/');
		$response->addProp('displayName', '/');
		$response->setResourceType('collection');
		$responseList[] = $response;

		$d = dir($dir);
		while ($entry = $d->read()) {
			if (substr($entry, 0, 1) == '.') { continue; }
			$responseList[] = $this->_propfindFile($dir, $entry);
		}
		$d->close();
		return $responseList;
		/*
  <D:response>
    <D:href>webdav://192.168.2.7:80/~akuma/cognifty/src/index.php/webdav</D:href>
    <D:propstat>
      <D:prop>
        <D:getlastmodified>Thu, 26 Feb 2009 04:08:58
        GMT</D:getlastmodified>
        <D:getcontenttype>text/html;
        charset=UTF-8</D:getcontenttype>
        <D:creationdate>1970-01-01T00:00:00Z</D:creationdate>
        <D:getetag />
        <D:displayname />
        <D:supportedlock>
          <D:lockentry>
            <D:lockscope>
              <D:exclusive />
            </D:lockscope>
            <D:locktype>
              <D:write />
            </D:locktype>
          </D:lockentry>
        </D:supportedlock>
        <D:resourcetype>
          <D:collection />
        </D:resourcetype>
        <D:lockdiscovery />
      </D:prop>
      <D:status>HTTP/1.1 200 OK</D:status>
    </D:propstat>
    <D:propstat>
      <D:prop>
        <D:getcontentlength />
        <D:executable />
        <D:getcontentlanguage />
        <D:source />
      </D:prop>
      <D:status>HTTP/1.1 404 Not Found</D:status>
    </D:propstat>
  </D:response>
		 */

	}

	protected function multiStatus($responseList) {
		$this->headerStatus = 'HTTP/1.1 207 Multi-Status';

		//TODO:make less apache specific
		if (isset($_SERVER['HTTPS'])) 
			$proto = 'https://';
		else
			$proto = 'http://';

		$xml = '<'.'?xml version="1.0" encoding="utf-8"?'.'>
<D:multistatus xmlns:D="DAV:">';
		foreach ($responseList as $_r) {
			if (!is_object($_r)) { 
				continue;
			}
			//$fullHref = $proto.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].htmlspecialchars($_r->href);
			$fullHref = htmlspecialchars($_r->href);
			$xml .= '<D:response>'."\n";
			$xml .= '<D:href>'.$fullHref.'</D:href>'."\n";
			$xml .= '<D:propstat>'."\n";
			$xml .= '<D:prop>'."\n";
			foreach ($_r->simpleProperties as $_key => $_pval) {
				if ($_pval !== '' ) {
					$xml .= '<D:'.$_key.'>'.$_pval.'</D:'.$_key.'>'."\n";
				} else {
					$xml .= '<D:'.$_key.'/>'."\n";
				}
			}
			if ($_r->resourceType != '') {
				$xml .= '<D:resourcetype><D:'.$_r->resourceType.'/></D:resourcetype>'."\n";
			} else {
				$xml .= '<D:resourcetype/>'."\n";
			}
			$xml .= '</D:prop>'."\n";
      		$xml .= '<D:status>HTTP/1.1 200 OK</D:status>'."\n";
			$xml .= '</D:propstat>'."\n";
			$xml .= '</D:response>'."\n";
		}
		$xml .= '</D:multistatus>';
		return $xml;
	}

	/**
	 * Generally called from the output function
	 */
	public function generateResponse() {
		if (count($this->responseList) > 0) {
			return $this->multiStatus($this->responseList);
		} else {
			return '';
		}
	}

	/**
	 * Use $this->headerStatus and $['contentType'] to output a Webdav response
	 *
	 * This function calls $this->generateResponse to form a multi-status response
	 * using $this->responseList.
	 */
	public function output($req, &$t) {

		//any status code with 50x won't show output
		if (strstr($this->headerStatus, ' 50') !== FALSE) {
			header($this->headerStatus);
			return;
		}

		if (strstr($this->headerStatus, ' 40') !== FALSE) {
			header($this->headerStatus);
			return;
		}

		header('X-Dav-Powered-By: PHP WebDAV (+http://cognifty.com/)');
		header('MS-Author-Via: DAV');
		$webdavResponse = $this->generateResponse();
		if ($webdavResponse == '') {
			$webdavResponse = $t['webdavResponse'];
		}
		header($this->headerStatus);
		header('X-WebDAV-Status: '. substr($this->headerStatus, 9));

		if (isset($t['contentType'])) {
			header('Content-Type: '.$t['contentType']);
		} else {
			header('Content-Type: text/xml; charset=utf-8');
		}
		if (isset($t['sendFile'])) {
			header('Content-Length: '.filesize($t['sendFile']));
			/*
			if (isset($t['sendFileName'])) {
				header('Content-Disposition: attachment; filename='.htmlspecialchars($t['sendFileName']));
			}
			 */
			$f = fopen($t['sendFile'], 'r');
			fpassthru($f);
			fclose($f);
		} else {
			header('Content-Length: '.strlen($webdavResponse));
			print $webdavResponse;
		}
/*
$t['webdavDebug'] .= $webdavResponse;
$tmp = fopen('/tmp/ooodebug2.txt', 'a');
//fwrite($tmp, print_r($_SERVER,1));
//fwrite($tmp, print_r($_POST,1));
ob_start();
fwrite($tmp, $t['webdavDebug']."\n");
ob_end_clean();

fwrite($tmp, str_repeat("=", 80)."\n");
fclose($tmp);
//*/

	}

	public function eventAfter($req, &$t) {
	}


	protected function _propfindFile($dir, $entry) {
		/*
		 * <D:creationdate/>
		 * <D:getcontentlength/>
		 * <D:displayname/>
		 * <D:source/>
		 * <D:getcontentlanguage/>
		 * <D:getcontenttype/>
		 * <D:executable/>
		 * <D:getlastmodified/>
		 * <D:getetag/>
		 * <D:supportedlock/>
		 * <D:lockdiscovery/>
		 * <D:resourcetype/>
		 */

		$fullFile = $dir.'/'.$entry;

		if (! file_exists($fullFile)) {
			$response = new Cgn_Webdav_Response(basename($entry));
			$response->addProp('displayName', basename($entry));
			$this->headerStatus = 'HTTP/1.1 404 File Not Found';
			return;
		}
		$response = new Cgn_Webdav_Response(basename($entry));
		$response->addProp('displayName', basename($entry));

		if (is_dir($fullFile)) {
			$response->setResourceType('collection');
		} else {
			$stat = stat($fullFile);
			$response->addProp('getcontentlength', $stat[7]);
			$response->addProp('getcontenttype', Cgn_Webdav_Response::_filenameToMime($entry));
		}
		return $response;
	}

}

class Cgn_Webdav_Response {

	public $simpleProperties = array();
	public $href             = '';
	public $resourceType     = '';
	public $oobProperties    = array();
	public $lockStatus       = '';

	public function Cgn_Webdav_Response($href) {
		$this->href = $href;
	}

	public function addProp($p, $v) {
		$this->simpleProperties[$p] = $v;
	}

	public function setResourceType($t) {
		$this->resourceType = $t;
	}

	public static function _filenameToMime($f) {
		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}

/*
		// archives
		'zip' => 'application/zip',
		'rar' => 'application/x-rar-compressed',
		'exe' => 'application/x-msdownload',
		'msi' => 'application/x-msdownload',
		'cab' => 'application/vnd.ms-cab-compressed',

		// audio/video
		'mp3' => 'audio/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',

		// ms office
		'doc' => 'application/msword',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',

		// open office
		'odt' => 'application/vnd.oasis.opendocument.text',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
*/
		$ext = strtolower(array_pop(explode('.',$f)));

        switch($ext)
         {
			case 'js' :
			return 'application/x-javascript';

			case 'json' :
			return 'application/json';

			// images
			case 'jpg' :
			case 'jpeg' :
			case 'jpe' :
			return 'image/jpg';

			case 'png' :
			case 'gif' :
			case 'bmp' :
			case 'tiff' :
			case 'tif' :
			return 'image/'.$ext;

			case 'ico' :
			return 'image/vnd.microsoft.icon';

			case 'svg' :
			case 'svgz' :
			return 'image/svg+xml';
			// images

			case 'css' :
			return 'text/plain';

			case 'xml' :
			return 'application/xml';

			case 'doc' :
			case 'docx' :
			return 'application/msword';

			case 'xls' :
			case 'xlt' :
			case 'xlm' :
			case 'xld' :
			case 'xla' :
			case 'xlc' :
			case 'xlw' :
			case 'xll' :
			return 'application/vnd.ms-excel';

			case 'ppt' :
			case 'pps' :
			return 'application/vnd.ms-powerpoint';

			case 'rtf' :
			return 'application/rtf';

			// adobe
			case 'pdf' :
			return 'application/pdf';
			case 'psd' :
			return 'image/vnd.adobe.photoshop';
			case 'ai' :
			return 'application/postscript';
			case 'eps' :
			return 'application/postscript';
			case 'ps' :
			return 'application/postscript';


			case 'html' :
			case 'htm' :
			case 'php' :
			return 'text/html';

			case 'txt' :
			return 'text/plain';

			case 'mpeg' :
			case 'mpg' :
			case 'mpe' :
			return 'video/mpeg';

			case 'mp3' :
			return 'audio/mpeg3';

			case 'wav' :
			return 'audio/wav';

			case 'aiff' :
			case 'aif' :
			return 'audio/aiff';

			case 'avi' :
			return 'video/msvideo';

			case 'wmv' :
			return 'video/x-ms-wmv';

			case 'mov' :
			return 'video/quicktime';

			case 'zip' :
			return 'application/zip';

			case 'tar' :
			return 'application/x-tar';

			case 'swf' :
			return 'application/x-shockwave-flash';

			case 'flv' :
			return 'video/x-flv';
		 }

		return 'application/octet-stream';
	}
}
