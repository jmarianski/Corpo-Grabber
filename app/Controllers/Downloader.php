<?php

namespace Controllers;

use Core\Controller;
use Core\View;
use Helpers\PageDownloader;
use Modules;
use PHPHtmlParser\Dom;

/**
 * Sample controller showing a construct and 2 methods and their typical usage.
 */
class Downloader extends Controller
{
    /**
     * Call the parent construct.
     */
    public function __construct()
    {
        parent::__construct();
        $this->language->load('Welcome');
    }

	private function getPage($url, $numWords = 0, $depth = 0) {
			$html = file_get_contents(self::getFileName($url));
			$chunks = PageDownloader::getChunks($html, $numWords, $depth);
			$page = "";
			foreach($chunks as $chunk) {
				$page .= "<div class=\"chunk\">".$chunk."</div>
";
			}
			return $page;
	}
	
	private function getFileName($url) {
		$file = self::removeCharacters($url);
		$file = "tmp/".$file.".html";
		return $file;
	}
        
        public static function removeCharacters($string) {
            $pos = strrpos($string, "http://");
            if($pos==0)
                $pos = 7;
            else
                $pos = 0;
            $file = substr($string, $pos);
            $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
            $file = mb_ereg_replace("([\.]{2,})", '', $file);
            return $file;
        }
	
	public function preview() {
		echo self::getPage($_POST['url'], $_POST['num_words'], $_POST['depth']);
	}
        
        public function download_httrack() {
            ini_set('max_execution_time', 1300);
            $URL = $_POST['url'];
            $path_local = ((($_POST['path']==""))?(date("Y-m-d")." ".self::removeCharacters($URL)):$_POST['path']);
            $path = "tmp/".$path_local;
            $exec_time = $_POST['exec_time'];
            if($exec_time==0)
                $exec_time = 10;
            if(strlen($URL)>0 && strrpos($URL, " ")===false && $exec_time<300) {
                $cmd = "httrack -p1 --max-time=$exec_time --stay-on-same-domain --can-go-down --clean --do-not-log --quiet --utf8-conversion -O \"$path\" -N1 -D \"$URL\"";
                exec($cmd." 2>&1", $string);
                self::checkHashes($path."/web/");
                self::removeScripts($path."/web/");
                echo $path_local.'<BR>';
                foreach($string as $s)
                    echo $s."<BR>";
            }
            else
                echo "error with params, URL: ".$URL.", exec_time = ".$exec_time." (< 300)";
        }
        
        private function checkHashes($path) {
            $array = scandir($path);
            $hashes = array();
            foreach($array as $file) {
                $file = $path.$file;
                if(!is_dir($file) && (pathinfo($file, PATHINFO_EXTENSION)=="html" 
                                        || pathinfo($file, PATHINFO_EXTENSION)=="htm")) {
                    $hash = hash_file("md5", $file);
                    if(in_array($hash, $hashes))
                        unlink ($file);
                    else
                        $hashes[] = $hash;
                }
            }
        }
        private function removeScripts($path) {
            // will remove scripts from html to present data without scripts
            $array = scandir($path);
            foreach($array as $file) {
                $file = $path.$file;
                if(!is_dir($file) && (pathinfo($file, PATHINFO_EXTENSION)=="html" 
                                        || pathinfo($file, PATHINFO_EXTENSION)=="htm")) {
                    $dom = new Dom();
                    $dom->loadFromFile($file, []);
                    file_put_contents($file, $dom->root->innerHTML());
                }
            }
        }
	
	public function download() {			
		$page = PageDownloader::download1($_POST['url']);
                if(strlen($page)==3) {
                    echo $page;
                    return;
                }
		$file = self::getFileName($_POST['url']);
                if(self::startsWith($_POST['url'], $_POST['prefix']))
                    file_put_contents($file, $page);
                $dom = new Dom();
                $dom->loadStr($page, []);
                $a = $dom->getElementsByTag("a");
                foreach($a as $url) {
                    echo $url->getTag()->getAttribute("href")['value']."\n";
                }
	} 
        
       private function startsWith($haystack, $needle) {
            return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
        }
	
    public function show()
    {
        $data['title'] = "Adres: ".$_GET['page'];
		if(strlen($_GET['page'])>0) {
			$page = PageDownloader::download1($_GET['page']);
			file_put_contents(self::getFileName($_GET['page']), $page);
			$data['url'] = stripslashes($_GET['page']);
		}
        View::renderTemplate('header', $data);
        View::render('main/show', $data);
        View::renderTemplate('footer', $data);
    }
	
    public function download_multiple()
    {
        $data['title'] = "Pobieranie zaawansowane";
        View::renderTemplate('header', $data);
        View::render('main/download', $data);
        View::renderTemplate('footer', $data);
    }
	
	public function load_project() {
        $data['title'] = "Utwórz wzorzec";
        $data['projects'] = glob("tmp/*", GLOB_ONLYDIR);
        
        View::renderTemplate('header', $data);
        View::render('main/load', $data);
        View::renderTemplate('footer', $data);
    }
        
    public function project() {
        $mode = $_POST['mode'];
        $path = utf8_decode($_POST['path']);
        switch($mode) {
            case "files":
                self::getFiles($_POST['project']);
                break;
            case "loadSkeleton":
                // do stuff
                self::loadSkeleton($path);
                break;
            case "loadPreview":
                if(!is_dir($path) && (pathinfo($path, PATHINFO_EXTENSION)=="html" 
                                    || pathinfo($path, PATHINFO_EXTENSION)=="htm"))
                        echo file_get_contents($path);
                    else {
                        echo "error";
                    }
                break;

        }
    }

    private function getFiles($project) {
                $project = utf8_decode($project);
                $html = "";
                if(file_exists($project)) {
                    $array = scandir($project);
                    if($array!==false) {
                        foreach($array as $file) {
                            $p = $project."/".$file;
                            if(!is_dir($p) && (pathinfo($p, PATHINFO_EXTENSION)=="html" 
                                    || pathinfo($p, PATHINFO_EXTENSION)=="htm"))
                                $html .= utf8_encode($file)."<BR>";
                        }
                    }
                    else
                        echo 'error';
                    if($html==="")
                        echo 'error';
                    echo $html;
                }
                else
                    echo 'error';
    }

    private function loadSkeleton($path) {
        $dom = new Dom();
        $dom->loadFromFile($path, ["whitespaceTextNode"=>false]);
        $tree = $dom->root;
        $head = $tree->find("head")[0];
        $head->delete();
        unset($head);
        echo self::skeletonBranch($tree, "root");
    }
        
    private function skeletonBranch($branch, $name) {
        if($name!="root")
            $string = "<div id='$name'>";
        else
            $string = "<div id='root' class='skeleton'>";
        $tag = $branch->getTag()->name();
        if(!($branch instanceof Dom\TextNode))
            $string.= "\n<B>$tag</B><BR>";
        if(!($branch instanceof Dom\TextNode) && $branch->hasChildren()) {
            $children = $branch->getChildren();
            for($i=0; $i<count($children); $i++) {
                $tag = $children[$i]->getTag()->name();
                $string.= "\n".self::skeletonBranch($children[$i], 
                        $name."-".$tag.":".$i);
            }
        }
        else {
            $string.= "\n".$branch->innerHtml();
        }
        $string .= "\n</div>";
        return $string;
    }
        

}
