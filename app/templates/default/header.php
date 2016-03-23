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
<html lang="<?php echo LANGUAGE_CODE; ?>">
<head>

	<!-- Site meta -->
	<meta charset="utf-8">
	<?php
    //hook for plugging in meta tags
    $hooks->run('meta');
    ?>
	<title><?=$data['title']?></title>

	<!-- CSS -->
	<?php
    Assets::css([
        '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',
        Url::templatePath().'css/style.css',
    ]);

    //hook for plugging in css
    $hooks->run('css');
    ?>

</head>
<body>
<?php
//hook for running code after body tag
$hooks->run('afterBody');

?>
<div align="center" class="header">
<form method=get action="/corpo-grabber/">
<a class="btn btn-md btn-success" href="/corpo-grabber/">Index</a>
<input name=page value="http://google.com">
<a class="btn btn-md btn-success" href="/corpo-grabber/subpage">Subpage</a>
</form>
</div><div class="container">
