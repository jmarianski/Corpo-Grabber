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
		$file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $url);
		// Remove any runs of periods (thanks falstro!)
		$file = mb_ereg_replace("([\.]{2,})", '', $file);
		$file = "tmp/".$file.".html";
		return $file;
	}
	
	public function preview() {
		echo self::getPage($_POST['url'], $_POST['num_words'], $_POST['depth']);
	}
	
	public function download() {			
		$page = PageDownloader::download1($_POST['url']);
                if(strlen($page)==3) {
                    echo $page;
                    return;
                }
		$file = self::getFileName($_POST['url']);
		file_put_contents($file, $page);
                $dom = new Dom();
                $dom->loadStr($page, []);
                $a = $dom->getElementsByTag("a");
                foreach($a as $url) {
                    echo $url->getTag()->getAttribute("href")['value']."\n";
                }
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
        $data['title'] = "Pobieranie zaawansowane";
        View::renderTemplate('header', $data);
        View::render('main/load', $data);
        View::renderTemplate('footer', $data);
	}

}
