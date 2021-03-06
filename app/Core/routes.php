<?php
/**
 * Routes - all standard routes are defined here.
 *
 * @author David Carr - dave@daveismyname.com
 *
 * @version 2.2
 * @date updated Sept 19, 2015
 */

/** Create alias for Router. */
use Core\Router;
use Helpers\Hooks;

/* Define routes. */
Router::any('', 'Controllers\Index@index');
Router::any('subpage', 'Controllers\Index@subpage');
Router::any('download/single', 'Controllers\Downloader@show');
Router::any('download/preview', 'Controllers\Downloader@preview');
Router::any('download/load', 'Controllers\Downloader@load_project');
Router::any('download/download', 'Controllers\Downloader@download');
Router::any('download/download_httrack', 'Controllers\Downloader@download_httrack');
Router::any('download/download_wget', 'Controllers\Downloader@download_wget');
Router::any('download/download_wget_post', 'Controllers\Downloader@post_process_wget');
Router::any('download/download_wget_status', 'Controllers\Downloader@get_wget_download_status');
Router::any('download/multiple', 'Controllers\Downloader@download_multiple');
Router::any('download/project', 'Controllers\Downloader@project');

/* Module routes. */
$hooks = Hooks::get();
$hooks->run('routes');

/* If no route found. */
Router::error('Core\Error@index');

/* Turn on old style routing. */
Router::$fallback = false;

/* Execute matched routes. */
Router::dispatch();
