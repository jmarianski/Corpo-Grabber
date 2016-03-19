<?

namespace Helpers;

use PHPHtmlParser\Dom;

class PageDownloader {
	
	function __construct(){}
	private static $taggs;
	
	public function download1($path, $redirects = 5) {
		/*
		$ch = curl_init();
		$timeout = 1;
		curl_setopt($ch, CURLOPT_URL, $path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		if($redirects>0) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
			curl_setopt($ch, CURLOPT_MAXREDIRS, $redirects);
		}
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANYSAFE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
		*/
		$dom = new Dom();
		$options = [
        'whitespaceTextNode' => true,
        'strict'             => false,
        'enforceEncoding'    => "utf-8",
        'cleanupInput'       => true,
        'removeScripts'      => true,
        'removeStyles'       => true,
        'preserveLineBreaks' => false,
		];
		$dom->loadFromUrl($path, $options);
		return $dom->root->innerHTML();
	}
	
	public function getText($data) {
		
		$this->taggs = array();
		$dom = new Dom;
		$dom->load($data);
		$array = self::getChunks($data);
		$text;
		foreach($array as $a)
			$text .= "<p>"
			.$a."</p>";
		return $text;
	}
	
	public function getChunks($data) {
		$array = array();
		$dom = new Dom;
		$dom->load($data);
		$array = self::getChunksHelper($dom->root);
		return $array;
	}
	
	
	private function getChunksHelper($data) {
		$tags = array("b", "i", "u", "a", "strong", "em", "mark", "ins", "sub", "sup", "img", "text", "span");
		$array = array();
		if($data->hasChildren()) {
			if(count($data->getChildren())>0) {
					foreach($data->getChildren() as $child) {
						$children = $child->getChildren();
						$var = true;
						foreach($children as $c) {
							$var2 = false;
							foreach($tags as $tag) {
								$t = strtolower($c->getTag()->name());
								if($t==$tag)
									$var2 = true;
								if(!in_array($t, $this->taggs))
									$this->taggs[] = $t;
							}
							if(!$var2) {
								$var = false;
							}
						}
						if(!$var && count($children)>0) {
							$a = self::getChunksHelper($child);
							if(count($a)>0)
								$array = array_merge($array, $a);
						}
						else {
							$string = trim(strip_tags($child->innerHTML()));
							if(strlen($string)>0)
								$array[] = $string;
						}
					}
			}
		}
		return $array;
	}
	
	
}