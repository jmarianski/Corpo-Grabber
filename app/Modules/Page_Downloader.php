<?

namespace Modules;

class Page_Downloader {
	
	function __construct(){}
	
	public function download1($path) {
		$ch = curl_init();
		$timeout = 1;
		curl_setopt($ch, CURLOPT_URL, $path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANYSAFE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	
}