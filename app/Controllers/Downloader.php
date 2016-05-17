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
            if($pos===0)
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
        
        private function execInBackground($cmd) {
            if (substr(php_uname(), 0, 7) == "Windows"){
                pclose(popen("start /B ". $cmd, "r")); 
            }
            else {
                exec($cmd . " > /dev/null &");  
            }
        } 
        
        public function download_wget() {
            $URL = $_POST['url'];
            $path_local = ((($_POST['path']==""))?(date("Y-m-d")." ".self::removeCharacters($URL)):$_POST['path']);
            $path = "tmp/".$path_local."/web/";
            $exec_time = $_POST['exec_time'];
            if($exec_time==0)
                $exec_time = 10;
            if(strlen($URL)>0 && strrpos($URL, " ")===false) {
                if(!is_dir($path))
                    mkdir($path, "0777", true);
                if(strpos($URL, "http://")===0)
                        $URL = substr($URL, 7);
                if(strpos($URL, "www.")===0)
                        $URL = substr($URL, 4);
                $path_log = $path."log.txt";
                $cmd = "wget --recursive -nd --page-requisites --html-extension --convert-links "
                        . "--restrict-file-names=windows -P \"$path\" -A html,htm  -w 1 --random-wait "
                        . "--domains $URL --no-parent $URL -o \"$path_log\" -nv";
                self::execInBackground($cmd);
                echo "OK ".$path_local;
            }
            else
                echo "error with params, URL: ".$URL.", exec_time = ".$exec_time." (< 300)";
        }
        
        public function post_process_wget() {
            $path = $_POST['path'];
            $path = "tmp/".$path."/web";
            self::postprocess($path);
        }
        
        public function get_wget_download_status() {
            $path = $_POST['path'];
            $path = "tmp/".$path."/web/log.txt";
            if(file_exists($path))
                echo str_replace("\n", "<BR>", file_get_contents($path));
            else
                echo "error no log file";
        }
        
        private function postprocess($project) {
            self::checkHashes($project);
            self::removeScripts($project);
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
                self::postprocess($path."/web");
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

    private function savePattern($project, $data_before_decode) {
        set_time_limit(300); // TODO: remove later
        $data = json_decode($data_before_decode, true);
        $project = utf8_decode($project);
        if(strlen($data['fields']['note'])>0 ){
            if(file_exists($project)) {
                $array = scandir($project);
                if($array!==false) {
                    $result = [];
                    $i = 0;
                    foreach($array as $file) {
                        if(!is_dir($project.$file) && (pathinfo($project.$file, PATHINFO_EXTENSION)=="html" 
                                    || pathinfo($project.$file, PATHINFO_EXTENSION)=="htm")) {
                            $result[$file] = self::applyPatternToFile($project.$file, $data['fields'], $data['ignore']);
                        }
                    }
                    echo self::encode_results($result, $project, "");
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
    
    private function encode_results($array, $project, $method) {
        switch($method) {
            case "xces":
                break;
            case "ccl":
                break;
            default:
                $text = json_encode($array, true);
                if(strlen($text)<2) {
                    echo "error ".json_last_error_msg();
                    ob_start();
                    var_dump($array);
                    $text = ob_get_clean();
                }
                    $file = $project."results/";
                    if(!is_dir($file))
                        mkdir($file);
                    $i = 0;
                    while(is_file($file.$i.".txt"))
                            $i++;
                    $file = $file.$i.".txt";
                    file_put_contents($file, $text);
                    return "/corpo-grabber/".$file;
        }
        return "";
    }
    
    private function applyPatternToFile($file, $fields, $ignore) {
        // by now, note exists
        $array = [];
        if(!is_dir($file) && (pathinfo($file, PATHINFO_EXTENSION)=="html" 
                                    || pathinfo($file, PATHINFO_EXTENSION)=="htm")) {
            $tree = self::prepareTreeForLoadFile($file);
            // delete ignored lines
            if(true) { // check liczność
                $branches = [];
                foreach($fields as $key => $value) {
                    if($key!="note")
                        $branches[$key] = self::goToBranch($tree, $value);
                }
                self::deleteIgnores($tree, "root", $ignore);
                $array = self::applyPatternToSection($branches);
            } else {
                $note = self::goToBranch($tree, $fields['note']);
                $siblings = $note.getParent().getChildren();
                $newfields = [];
                foreach($fields as $key => $field) {
                    if($key!="note") {
                        $newfields[$key] = str_replace($fields['note'], "root", $field);
                    }
                }
                $i = 0;
                foreach($siblings as $child) {
                    // $child to nasz nowy root
                    $papa = $child->getTag()->getAttribute("id");
                    $branches = [];
                    foreach($newfields as $key => $value) {
                        $branches[$key] = self::goToBranch($note, $value);
                    }
                    self::deleteIgnores($papa, "root", $ignore);
                    $array["note-".($i++)] = self::applyPatternToSection($branches);
                }
            }
            unset($tree);
        }
        return $array;
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
    
    private function deleteIgnores($root, $rootstring, $ignores) {
        $ign = [];
        foreach($ignores as $value) {
            $ign[] = str_replace($rootstring, "root", $value);
        }
        foreach($ign as $value) {
            $branch = self::goToBranch($root, $value);
            if($branch!=null && $branch!=false) {
                $branch->delete();
                unset($branch);
            }
        }
    }
    
    private function applyPatternToSection($branches) {
        $vals = array_values($branches);
        for($i = 0; $i<count($vals); $i++) {
            for($j = $i; $j<count($vals); $j++) {
                if($vals[$j]!== false && $vals[$i]!== false && 
                        $vals[$j]!== null && $vals[$i]!== null && 
                        $vals[$j]->isAncestor($vals[$i]->id())) {
                    $vals[$j]->getParent()->removeChild($vals[$j]->id());
                }
            }
        }
        $result = [];
        // elementy zostały rozłączone
        foreach($branches as $key => $value) {
            $array = self::getChunksHelper($value);
            if(count($array)>0)
                $result[$key] = $array;
        }
        return $result;
		
    }
    
    	
    private function getChunksHelper($data) {
            // allowable tags
            $tags = array("b", "i", "u", "a", "strong", "em", "mark", "ins", "sub", "sup", "img", "text", "span", "br", "small");
            $array = array();
            if($data!==null && !($data instanceof Dom\TextNode) && $data->hasChildren()) {
                    if(count($data->getChildren())>0) {
                        foreach($data->getChildren() as $child) {
                            if($child instanceof Dom\TextNode) {
                                $string = trim(strip_tags($child->innerHTML()));
                                        if(strlen($string)>0)
                                                $array[] = $string;
                            }
                            else {
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
            }
            else if ($data!==null) {
                if(strlen(trim($data->text()))>0)
                    $array[] = trim($data->text());
            }
            return $array;
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
        for($i = 1; $i<count($array) && !($current instanceof Dom\TextNode); $i++) { // root jest pierwszy
            $pos = split(":", $array[$i]);
            $children = $current->getChildren();
            $j = 1;
            $got = false;
            for($k=0; $k<count($children) && !$got; $k++) {
                $tag = self::getTag($children[$k]);
                if($tag===$pos[0]) {
                    if($j==$pos[1]) {
                        $current = $children[$k];
                        $got = true;
                    }
                    else
                        $j++;
                }
            }
            if(!$got && $i<count($array)-1) {
                // echo $string." not found<BR>";
                return null;
            }
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
        $tag = self::getTag($branch);
        if(!($branch instanceof Dom\TextNode))
            $string.= "\n<B>$tag</B><BR>";
        if(!($branch instanceof Dom\TextNode) && $branch->hasChildren()) {
            $children = $branch->getChildren();
            $items = [];
            for($i=0; $i<count($children); $i++) {
                $tag = self::getTag($children[$i]);
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
    
    private function getTag($branch) {
        $tag = $branch->getTag();
        if($tag->getAttribute("class")!=false) {
            $tag = $tag->name() . "." . str_replace(["-", ".", ":"], "_", $tag->getAttribute("class")['value']);
        } else {
            $tag = $tag->name();
        }
        return $tag;
    }
        

}
