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

</div>

<!-- JS -->
<?php

//hook for plugging in code into the footer
$hooks->run('footer');
?>

</body>
</html>
