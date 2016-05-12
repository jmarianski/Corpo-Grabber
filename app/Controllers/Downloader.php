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
            if(is_dir($path)) {
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
        }
        private function removeScripts($path) {
            // will remove scripts from html to present data without scripts
            
            if(is_dir($path)) {
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
            case "savePattern":
                self::savePattern($_POST['project'], $_POST['data']);
                break;

        }
    }

    private function savePattern($project, $data) {
        $project = utf8_decode($project);
        if(strlen($data['note']>0) ){
            if(file_exists($project)) {
                $array = scandir($project);
                if($array!==false) {
                    foreach($array as $file) {
                        if(file_exists($file)) {
                            echo self::applyPatternToFile($file, $data['fields'], $data['ignore'])."<BR>";
                        }
                        else
                            echo 'error nofile<BR>\n';
                    }
                }
                else
                    echo 'error3';
                if($html==="")
                    echo 'error2';
                echo $html;
            }
            else
                echo 'error project '.$project;
        }
        else {
            echo 'error nonote'; 
        }
    }
    
    private function applyPatternToFile($file, $fields, $ignore) {
        // by now, note exists
        $tree = self::prepareTreeForLoadFile($file);
        // delete ignored lines
        foreach($ignore as $value) {
            $branch = self::goToBranch($tree, $value);
            if($branch!=null && $branch!=false) {
                $branch->delete();
                unset($branch);
            }
        }
        if(true) { // check liczność
            $branches = [];
            foreach($fields as $key => $value) {
                $branches[$key] = self::goToBranch($tree, $value);
            }
            self::applyPatternToSection($branches);
        } else {
            $note = self::goToBranch($tree, $fields['note']);
            $sibling = $note.getParent().getChildren();
            $newfields = [];
            foreach($fields as $key => $field) {
                if($key!="note") {
                    $newfields[$key] = str_replace($fields['note'], "root", $field);
                }
            }
            foreach($sibling as $child) {
                // $child to nasz nowy root
                $branches = [];
                foreach($newfields as $key => $value) {
                    $branches[$key] = self::goToBranch($note, $value);
                }
                applyPatternToSection($branches);
            }
        }
        /*
         * 0. Wczytaj plik
         * 1. Go to notka in tree done
         * 2. Check liczność not done
         * 3. If liczność == 0..n lub 1..n rób pętlę dla dzieci rodzica done
         * 4. Dla wszystkich elementów z data znajdź elementy w drzewie i
         * zapamiętaj jako chunk 
         * // xces nie wymaga morfeusza
         * 5. Zaaplikuj słownik morfeusz, podziel na słowa ze słownika
         * 6. Zapisz plik ccl do katalogu N (gdzie N oznacza N-tą próbę zapisu)
         * 7. Spakuj do zipa
         * 8. Prześlij plik zip
         */
    }
    
    private function applyPatternToSection($branches) {
        $vals = array_values($branches);
        for($i = 0; $i<count($vals); $i++) {
            for($j = 0; $j<count($vals); $i++) {
                if($vals[$j].isAncestor($vals[$i].id())) {
                    $vals[$j]->delete();
                }
            }
        }
        // elementy zostały rozłączone
        
    }
    /**
     * 
     * @param type $root
     * @param type $string
     * @return HTMLNode|boolean
     */
    private function goToBranch($root, $string) {
        $current = $root;
        $array = split("-", $string);
        for($i = 1; $i<count($array); $i++) { // root jest pierwszy
            $pos = split(":", $array[$i]);
            $children = $branch->getChildren();
            $j = 1;
            $got = false;
            for($k=0; $i<count($children) && !$got; $k++) {
                $tag = $children[$k]->getTag()->name();
                if($tag===$pos[0]) {
                    if($j==$pos[1]) {
                        $current = $children[$k];
                        $got = true;
                    }
                    else
                        $j++;
                }
            }
            if(!$got && $i<count($array)-1) // nie znaleziono w drzewie
                return false;
        }
        return $current;
        
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
                        echo 'error3';
                    if($html==="")
                        echo 'error2';
                    echo $html;
                }
                else
                    echo 'error '.$project;
    }

    private function prepareTreeForLoadFile($path) {
        $dom = new Dom();
        $dom->loadFromFile($path, ["whitespaceTextNode"=>false]);
        $tree = $dom->root;
        $head = $tree->find("head")[0];
        if($head!=null) {
            $head->delete();
            unset($head);
        }
        return $tree;
    }
    
    private function loadSkeleton($path) {
        $tree = self::prepareTreeForLoadFile($path);
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
            $items = [];
            for($i=0; $i<count($children); $i++) {
                $tag = $children[$i]->getTag()->name();
                if(array_key_exists($tag, $items))
                    $items[$tag]++;
                else
                    $items[$tag] = 1;
                $string.= "\n".self::skeletonBranch($children[$i], 
                        $name."-".$tag.":".$items[$tag]);
            }
        }
        else {
            $string.= "\n".$branch->innerHtml();
        }
        $string .= "\n</div>";
        return $string;
    }
        

}
