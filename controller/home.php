<?php
if(!strpos($_SERVER["HTTP_HOST"], "rehhoff.me")) {
    $template->message("Congratulations! you're now using a template system", "success");
    $template->message("Don't forget to add database credentiels in <em>index.php</em>", "attention");
}
?>  