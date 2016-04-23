<?php
/**
 * Sample layout.
 */
use Helpers\Assets;
use Helpers\Hooks;
use Helpers\Url;

//initialise hooks
$hooks = Hooks::get();
?>
<!DOCTYPE html>
<html lang="pl_PL">
<head>

	<!-- Site meta -->
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<?php
    //hook for plugging in meta tags
    $hooks->run('meta');
    ?>
	<title><?=$data['title']?></title>

	<!-- CSS -->
	<?php
    Assets::css([
        Url::templatePath().'css/style.css'
    ]);
	
	Assets::js([
		'https://code.jquery.com/jquery-1.12.3.min.js',
		'//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js',
	]);



    //hook for plugging in css
    $hooks->run('css');
	
	//hook for plugging in javascript
	$hooks->run('js');
    ?>
</head>
<body>
<?php
//hook for running code after body tag
$hooks->run('afterBody');

?>
<div style="background:#ccccdd" align="center" class="header">
<table class="header">
<tr>
<td width="33%">
<a href="/corpo-grabber/">Index</a></td>
<td width="33%">
<form method=get action="/corpo-grabber/download/single">
<input style="display:table-cell; width:100%" name=page value="<?=strlen($_GET['page'])>0?$_GET['page']:"http://google.com"?>">
</form>
</td><td>
<a href="/corpo-grabber/download/multiple">Advanced download</a>
</td>
<td>
    <a href="/corpo-grabber/download/load">Review previous download</a>
</td>
</tr>
</table>
</div><div class="container">
