<?php

namespace Controllers;

use Core\Controller;
use Core\View;
use Helpers\PageDownloader;
use Helpers\Encoding;
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
                $URL_short = $URL;
                if(!is_dir($path))
                    mkdir($path, "0777", true);
                if(strpos($URL_short, "http://") === 0)
                        $URL_short = substr($URL_short, 7);
                if(strpos($URL_short, "www.") === 0)
                        $URL_short = substr($URL_short, 4);
                $URL_short = split("/", $URL_short);
                $URL_short = $URL_short[0];
                $path_log = $path."log.txt";
                $cmd = "wget --recursive -nd --page-requisites --html-extension --convert-links "
                        . "--restrict-file-names=windows -P \"$path\" -A html,htm  -w 1 --random-wait "
                        . "--domains $URL_short --no-parent $URL -o \"$path_log\" -nv";
                self::log($cmd."\n");
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
            $p = "tmp/".$path."/web/";
            $path = $p."/log.txt";
            if(file_exists($path)) {
                self::checkHashes($p);
                $file = file_get_contents($path);
                $file = explode("\n", $file);
                $file = array_reverse($file);
                $file = implode("\n", $file);
                echo str_replace("\n", "<BR>", $file);
            }
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
        
        private function log($string) {
            $file = "tmp/log.txt";
            file_put_contents($file, date("D M d, Y G:i:s")." ".$string, FILE_APPEND);
        }
        
        private function checkHashes($path) {
            if(is_dir($path)) {
                $array = scandir($path);
                $hashes = array();
                foreach($array as $file) {
                    $file = $path.$file;
                    if(!is_dir($file) && (pathinfo($file, PATHINFO_EXTENSION)=="html" 
                                            || pathinfo($file, PATHINFO_EXTENSION)=="htm")) {
                        $file = getcwd()."/".$file;
                        $hash = filesize($file);
                        if(in_array($hash, $hashes))
                            unlink ($file);
                        else {
                            $hashes[] = $hash;
                            $hash = sha1_file($file);
                            if(in_array($hash, $hashes))
                                unlink ($file);
                            else
                                $hashes[] = $hash;
                        }
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
                        $html = file_get_contents($file);
                        $html2 = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
                        if(strlen($html2)!=strlen($html))
                            file_put_contents($file, $html2);
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
                self::postprocess($_POST['project']);
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

    private $methods = [];
    
    private function savePattern($project, $data_before_decode) {
        // set_time_limit(300); // TODO: remove later
        ini_set('max_execution_time', 60000); // 1000 minut
        session_write_close();
        $data = json_decode($data_before_decode, true);
        $project = utf8_decode($project);
        $research = false;
        foreach($data['research'] as $r=>$v) {
            if(strlen($v)>0)
                $research = true;
        }
        if(strlen($data['fields']['note'])>0 ){
            if(file_exists($project)) {
                $array = scandir($project);
                if($array!==false) {
                    if(!$research) {
                        $result = [];
                        foreach($array as $file) {
                            if(!is_dir($project.$file) && (pathinfo($project.$file, PATHINFO_EXTENSION)=="html" 
                                        || pathinfo($project.$file, PATHINFO_EXTENSION)=="htm")) {
                                $result[$file] = self::applyPatternToFile($project.$file, $data);
                            }
                        }
                        echo self::encode_results($result, $project, "premorph");
                    }
                    else {
                        $fields = $data['fields'];
                        $research = $data['research'];
                        $this->methods[0] = $fields;
                        $this->methods[1] = $research;
                        $array2 = [];
                        for($j=0; $j<100; $j++) {
                            $file = $array[rand(0, count($array)-1)];
                            if(!is_dir($project.$file) && !in_array($file, $array2) &&
                                    (pathinfo($project.$file, PATHINFO_EXTENSION)=="html" 
                                            || pathinfo($project.$file, PATHINFO_EXTENSION)=="htm")) {
                                $array2[] = $file;
                            }
                        }
                        for($i=0; $i<4; $i++) {
                            $this->phase = $i;
                            $data['researchphase'] = $i;
                            foreach($array2 as $file) {
                                if(!is_dir($project.$file) && (pathinfo($project.$file, PATHINFO_EXTENSION)=="html" 
                                            || pathinfo($project.$file, PATHINFO_EXTENSION)=="htm")) {
                                    $result[$i][$file] = self::applyPatternToFileResearch($project.$file, $data);
                                }
                            }
                        }
                        echo self::encode_results($result, $project, "research");
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
        
    session_start();
    }
    
    private function premorph_array($array) {
        $result = "";
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
                . "<!DOCTYPE cesAna SYSTEM \"xcesAnaIPI.dtd\">"
        ."<cesAna version=\"WROC-1.0\" type=\"pre_morph\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">"
                . "<chunkList>"
                . "</chunkList>"
                . "</cesAna>");
        $chunkList = $xml->chunkList[0];
        if(count($array)===0)
            return "";
        foreach($array as $key => $value) {
            if(is_string($value)) {
                $chunk = $chunkList->addChild("chunk", $value);
                $chunk->addAttribute("type", $key);
            }
            else if(is_array($value)) {
                foreach($value as $v) {
                    $chunk = $chunkList->addChild("chunk", $v);
                    $chunk->addAttribute("type", $key);
                }
            }
        }
        return $xml->asXML();
    }
    
    private function encode_premorph($array) {
        if(strpos(array_keys($array)[0], "note")===0) {
            $result = [];
            foreach($array as $key => $value) {
                $r = self::premorph_array($value);
                if(strlen($r)>0)
                    $result[$key] = $r;
            }
            return $result;
        }
        else {
            return self::premorph_array($array);
        }
    }
    
    private function encode_results($array, $project, $method) {
        switch($method) {
            case "xces":
                break;
            case "ccl":
                break;
            case "premorph":
                $results = [];
                foreach($array as $key => $value) {
                    $r = self::encode_premorph($value);
                    if(is_string($r))
                        $results[$key] = $r;
                    else if(is_array($r)) {
                        $i = 1;
                        foreach($r as $v) {
                            if(is_string($v))
                                $results[$key.".".($i++)] = $v;
                        }
                    }
                }
                // results po kolei zawiera potencjalne nazwy plików i treść
                $file = $project."premorph/";
                if(!is_dir($file))
                    mkdir($file, "0777", true);
                $i = 0;
                while(is_file($file."package.$i.zip"))
                        $i++;
                $filezip = $file."package.$i.zip";
		$zip = new \ZipArchive();
		if($zip->open($filezip,\ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
                foreach($results as $key => $value) {
                    if(strlen($key)>0) {
                        file_put_contents($file.$key.".xml", $value);
			$zip->addFile($file.$key.".xml", $key.".xml");
                    }
                    else
                        echo 'error';
                }
                $zip->close();
                return "/corporanet/".$filezip;
                break;
            case "research":
                $result = [[]];
                foreach($array as $value) {
                    $result['xml'][] = self::encode_results($value, $project, "premorph");
                    $result['size'][] = filesize($_SERVER["DOCUMENT_ROOT"].$result['xml'][count($result['xml'])-1]);
                }
                $string = "PODSUMOWANIE\n";
                self::log( "error PODSUMOWANIE\n");
                for($i = 0; $i<count($result['xml']); $i++) {
                    $string .= "Badanie $i: Rozmiar pliku: ".$result['size'][$i]."\n";
                    self::log( "Badanie $i: Rozmiar pliku: ".$result['size'][$i]."\n");
                    $string .= $result['xml'][$i]."\n";
                    self::log( $result['xml'][$i]."\n");
                }
                $string = str_replace("\n", "<BR>\n", $string);
                $string.= "Do ektrakcji pozycyjnej uzyto nastepujacych atrybutow:<BR>\n";
                foreach($this->methods[0] as $key=>$val) {
                    $string .= $key. " => ".$val. "<BR>\n";
                }
                $string.= "Do ektrakcji selektorem uzyto nastepujacych atrybutow:<BR>\n";
                foreach($this->methods[1] as $key=>$val) {
                    $string .= $key. " => ".$val. "<BR>\n";
                }
                $rep = [];
                foreach($this->report as $phase=>$files) {
                    $notes = 0;
                    $valids = 0;
                    foreach($files as $file=>$branches) {
                        $note = $branches['note'];
                        while(is_array($note))
                            $note = $note[0];
                        $notes += $note;
                        $valid = 0;
                        for($i=0; $i<$note; $i++) {
                            $var = true;
                            foreach($branches as $branch=>$value) {
                                if(is_array($value)) {
                                    // not note
                                    if($value[$i]===0)
                                        $var = false;
                                }
                            }
                            if($var)
                                $valid++;
                        }
                        $valids += $valid;
                        $rep[0][$phase][$file][0] = $note;
                        $rep[0][$phase][$file][1] = $valid;
                        if($note!=0)
                            $rep[0][$phase][$file][2] = $valid/$note;
                    }
                    $rep[1][$phase][0] = $notes;
                    $rep[1][$phase][1] = $valids;
                    if($notes!=0)
                        $rep[1][$phase][2] = $valids/$notes;
                }
                $formatter = new \NumberFormatter('en_US', \NumberFormatter::PERCENT);
                $string .= "Faza / Notek / Poprawnych / Wart. procentowa<BR>\n";
                foreach($rep[1] as $phase=>$a) {
                    $string.="$phase ".$a[0]." ".$a[1]." ".$formatter->format($a[2])."<BR>\n";
                }
                $string .= "Faza / Plik / Notek / Poprawnych / Wart. procentowa<BR>\n";
                foreach($rep[0] as $phase=>$files) {
                    foreach($files as $file=>$a)
                        $string .= "$phase ".$file." ".$a[0]." ".$a[1]." ".$formatter->format($a[2])."<BR>\n";
                }
                $file = $project."premorph/";
                file_put_contents($file."report.html", $string);
                echo "/corporanet/".$file."report.html";
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
                    return "/corporanet/".$file;
        }
        return "";
    }
    
    private function applyPatternToFile($file, $data) {
        // by now, note exists
        $array = [];
        $fields = $data['fields'];
        $ignore = $data['ignore'];
        if(!is_dir($file) && (pathinfo($file, PATHINFO_EXTENSION)=="html" 
                                    || pathinfo($file, PATHINFO_EXTENSION)=="htm")) {
            $tree = self::prepareTreeForLoadFile($file);
            // delete ignored lines
            if(false) { // check liczność
                $branches = [];
                foreach($fields as $key => $value) {
                    if($key!="note")
                        $branches[$key] = self::goToBranch($tree, $value, $key, $file);
                }
                self::deleteIgnores($tree, "root", $ignore);
                $array = self::applyPatternToSection($branches, $file);
            } else {
                $note = self::goToBranch($tree, $fields['note'], "note", $file);
                if($note!=null) {
                    $siblings = $note->getParent()->getChildren();
                    $newfields = [];
                    foreach($fields as $key => $field) {
                        if($key!="note") {
                            $newfields[$key] = str_replace($fields['note'], "root", $field, $file);
                        }
                    }
                    $i = 0;
                    foreach($siblings as $child) {
                        // $child to nasz nowy root
                        $papa = $child->getTag()->getAttribute("id");
                        $branches = [];
                        foreach($newfields as $key => $value) {
                            $branches[$key] = self::goToBranch($child, $value, $key, $file);
                        }
                        self::deleteIgnores($papa, "root", $ignore);
                        $array["note-".($i++)] = self::applyPatternToSection($branches, $file);
                    }
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
    
    private $report = [];
    private $phase = 0;
    
    private function applyPatternToFileResearch($file, $data) {
        $array = [];
        $fields = $data['fields'];
        $research = $data['research'];
        $phase = $data['researchphase'];
        self::log( "file: $file<BR>\n");
        self::log( "phase: $phase<BR>\n");
        if(!is_dir($file) && (pathinfo($file, PATHINFO_EXTENSION)=="html" 
                                    || pathinfo($file, PATHINFO_EXTENSION)=="htm")) {
            $tree = self::prepareTreeForLoadFile($file);
            $note = self::goToBranch($tree, $fields['note'], "note", $file);
                $newfields = [];
            foreach($fields as $key => $field) {
                    if($key!="note") {
                        $newfields[$key] = str_replace($fields['note'], "root", $field);
                    }
            }
            $i = 0;
            if($phase==0 || $phase==1) { // daddy is selected from position
                if($note!=null) {
                    $siblings = $note->getParent()->getChildren();
                    $this->report[$this->phase][$file]["note"] = count($siblings);
                
                }
                if($phase==0) { // ojciec pozycyjnie, dziecko pozycyjnie
                    if($note!=null) {
                        foreach($siblings as $child) {
                            $branches = [];
                            foreach($newfields as $key => $value) {
                                $branches[$key] = self::goToBranch($child, $value, $key, $file);
                            }
                            $array["note-".($i++)] = self::applyPatternToSection($branches, $file);
                        }
                    }
                }
                else { // ojeciec pozycyjnie, dziecko selektorem
                    if($note!=null) {
                        foreach($siblings as $child) {
                            $branches = [];
                            foreach($research as $k=>$v) {
                                if($k!=="note") {
                                    $branches[$k] = self::goToBranchResearch($child, $v, $k, $file);
                                }
                            }
                            $array["note-".($i++)] = self::applyPatternToSection($branches, $file);
                        }
                    }
                }
            }
            else { // 2 or 3: daddy is selected from selector
                $siblings = self::goToBranchResearch($tree, $research['note'], "note", $file);
                if($phase==2) { // ojciec selektorem, dziecko pozycyjnie
                    foreach($siblings as $child) {
                        $branches = [];
                        foreach($newfields as $key => $value) {
                            $branches[$key] = self::goToBranch($child, $value, $key, $file);
                        }
                            $array["note-".($i++)] = self::applyPatternToSection($branches, $file);
                    }
                }
                else { // ojciec selektorem, dziecko selektorem
                    foreach($siblings as $child) {
                        $branches = [];
                        foreach($research as $k=>$v) {
                            if($k!=="note") {
                                $branches[$k] = self::goToBranchResearch($child, $v, $k, $file);
                            }
                        }
                        $array["note-".($i++)] = self::applyPatternToSection($branches, $file);
                    }
                }
            }
            unset($tree);
        }
        return $array;
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
    
    private function applyPatternToSection($branches, $file) {
        $vals = array_values($branches);
        for($i = 0; $i<count($vals); $i++) {
            if($vals[$i] instanceof Dom\Collection) {
                $vals[$i] = $vals[$i]->toArray();
                if(count($vals[$i])>0) {
                    $vals[$i] = $vals[$i][0];
                }
            } 
            if (is_array($vals[$i]) && count($vals[$i])>0) {
                $vals[$i] = $vals[$i][0];
            }
            else if(is_array($vals[$i])) {
                $vals[$i] = null;
            }
        }
        for($i = 0; $i<count($vals); $i++) {
            for($j = 0; $j<count($vals); $j++) {
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
            if($value instanceof Dom\Collection)
                $value = $value->toArray();
            if(is_array($value))
                if(count($value)>0)
                    $value = $value[0];
                else
                    $value = null;
            $array = self::getChunksHelper($value, $key);
            if(count($array)>0)
                $result[$key] = $array;
        }
        return $result;
		
    }
    
    private function checkChildren($data) { 
            // allowable tags
        $tags = array("b", "i", "u", "a", "strong", "em", "mark", "ins", 
            "sub", "sup", "img", "text", "span", "small", "del");
        if(($data===null || $data instanceof Dom\TextNode))
            return true;
        else {
            $children = [$data];
            $var = true;
            for($i = 0; $i<count($children) && $var; $i++) {
                $child = $children[$i];
                if(!($child instanceof Dom\TextNode)) {
                    $children = array_merge($children, $child->getChildren());
                    $var2 = false;
                    foreach($tags as $tag) {
                        $t = strtolower($child->getTag()->name());
                        if($t==$tag)
                            $var2 = true;
                    }
                    if(!$var2) {
                        $var = false;
                    }
                }
            }
            return $var;
        }
    }
    
    	
    private function getChunksHelper($data, $type) {
            $array = array();
            if($data!==null && ($data instanceof Dom\InnerNode) && $data->hasChildren()) {
                $var = self::checkChildren($data);
                if($var) {
                    $text = trim($data->text(true));
                    if(strlen($text)>0)
                        $array[] = $text;
                }
                else {
                    $children = $data->getChildren();
                    $concat = "";
                    foreach($children as $child) {
                        if(self::checkChildren($child)) {
                            $concat .= trim($child->text(true))." ";
                        }
                        else {
                            if(strlen($concat)>0) {
                                $array[] = $concat;
                                $concat = "";
                            }
                            $array = array_merge($array, self::getChunksHelper($child, $type));
                        }
                    }
                    if(strlen($concat)>0) {
                        $array[] = $concat;
                        $concat = "";
                    }
                }
            }
            else if ($data!==null) {
                if(is_array($data))
                    $data = $data[0];
                $text = trim($data->text(true));
                if(strlen($text)>0)
                    $array[] = $text;
            }
            return $array;
    }
    
    
    
    /**
     * 
     * @param type $root
     * @param type $string
     * @return HTMLNode|boolean
     */
    private function goToBranch($root, $string, $type, $file) {
        $current = $root;
        $array = split("-", $string);
        for($i = 1; $i<count($array) && !($current instanceof Dom\TextNode); $i++) { // root jest pierwszy
            $pos = split(":", $array[$i]);
            $beforedot = split("\.", $pos[0])[0];
            
            $children = $current->getChildren();
            $j = 1;
            $got = false;
            for($k=0; $k<count($children) && !$got; $k++) {
                $tag = $children[$k]->getTag()->name();
                if($tag===$beforedot) {
                    if($j==$pos[1]) {
                        $current = $children[$k];
                        $got = true;
                    }
                    else
                        $j++;
                }
            }
            if(!$got && $i<=count($array)-1) {
                //if($type==="note")
                //    echo $string." not found, $beforedot<BR>";
                
                $this->report[$this->phase][$file][$type][] = 0;
                return null;
            }
        }
        if($type!=="note")
            $this->report[$this->phase][$file][$type][] = 1;
        return $current;
        
    }
    
    private function goToBranchResearch($root, $string, $type, $file) {
        $result = $root->find($string);
        $this->report[$this->phase][$file][$type][] = $result->count();
        return $result;
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
        $text = file_get_contents($path);
        $text = str_replace("&", "&amp;", $text);
        //$text = Encoding::toUTF8($text);
        $dom = new Dom();
        $dom->load($text, [/*"enforceEncoding"=>"UTF-8", */"whitespaceTextNode"=>false]);
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
        $tag = self::getTextTag($branch);
        if(!($branch instanceof Dom\TextNode))
            $string.= "\n<B>$tag</B><BR>";
        if(!($branch instanceof Dom\TextNode) && $branch->hasChildren()) {
            $children = $branch->getChildren();
            $items = [];
            for($i=0; $i<count($children); $i++) {
                $tag = self::getTag($children[$i]);
                $tag_simple = $children[$i]->getTag()->name();
                if(array_key_exists($tag_simple, $items))
                    $items[$tag_simple]++;
                else
                    $items[$tag_simple] = 1;
                $string.= "\n".self::skeletonBranch($children[$i], 
                        $name."-".$tag.":".$items[$tag_simple]);
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
    
    private function getTextTag($branch) {
        $tag = $branch->getTag();
        if($tag->getAttribute("class")!=false) {
            $tag = $tag->name() . "." .$tag->getAttribute("class")['value'];
        } else {
            $tag = $tag->name();
        }
        return $tag;
    }
        

}
