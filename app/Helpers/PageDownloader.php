<?php

namespace Helpers;

use PHPHtmlParser\Dom;

class PageDownloader {
	
	function __construct(){}
	
	public function download1($path) {
		$dom = new Dom();
		$options = [
        'whitespaceTextNode' => false,
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
	
	public function getChunks($data, $numWords = 0, $depth = 0) {
		$array = array();
		$dom = new Dom;
		$dom->load($data);
		$array = self::getChunksHelper($dom->root, $numWords, $depth);
		return $array;
	}
	
	private function getChunksHelper($data, $numWords, $depth) {
		// allowable tags
		$tags = array("b", "i", "u", "a", "strong", "em", "mark", "ins", "sub", "sup", "img", "text", "span", "br", "small");
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
							}
							if(!$var2) {
								$var = false;
							}
						}
						if(!$var && count($children)>0) {
							$a = self::getChunksHelper($child, $numWords, $depth);
							if(count($a)>0)
								$array = array_merge($array, $a);
						}
						else {
							$string = trim(strip_tags($child->innerHTML()));
							if(str_word_count($string)>=$numWords && strlen($string)>0)
								$array[] = $string;
						}
					}
			}
		}
		return $array;
	}
	
	
}