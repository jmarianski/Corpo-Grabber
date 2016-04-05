<?php
/**
 * Welcome controller.
 *
 * @author David Carr - dave@daveismyname.com
 *
 * @version 2.2
 * @date June 27, 2014
 * @date updated Sept 19, 2015
 */
namespace Controllers;

use Core\Controller;
use Core\View;
use Helpers\PageDownloader;
use Modules;

/**
 * Sample controller showing a construct and 2 methods and their typical usage.
 */
class Index extends Controller
{
    /**
     * Call the parent construct.
     */
    public function __construct()
    {
        parent::__construct();
        $this->language->load('Welcome');
    }

    /**
     * Define Index page title and load template files.
     */
    public function index()
    {
        $data['title'] = $this->language->get('welcome_text');
        $data['welcome_message'] = $this->language->get('welcome_message');
		if(strlen($_GET['page'])>0)
			$data['test'] = PageDownloader::getText(PageDownloader::download1($_GET['page']));
        View::renderTemplate('header', $data);
        View::render('main/index', $data);
        View::renderTemplate('footer', $data);
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
	
	public function page() {
		echo self::getPage($_POST['url'], $_POST['num_words'], $_POST['depth']);
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

    /**
     * Define Subpage page title and load template files.
     */
    public function subPage()
    {
        $data['title'] = $this->language->get('subpage_text');
        $data['welcome_message'] = $this->language->get('subpage_message');

        View::renderTemplate('header', $data);
        View::render('welcome/subpage', $data);
        View::renderTemplate('footer', $data);
    }
}
