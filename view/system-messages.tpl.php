<?php echo $template->message(); ?>
As of V1.3 methods <strong>getMessage()</strong> and <strong>setMessage()</strong> have been merged.<br>
            and you can achieve user messages (errors, notice, succes) through the <strong>->message($message, $type)</strong> method.<br>
            <strong>$type</strong> is flexible and does not need to be of a specific type. Defaults to 'notice'<br><br>
            If this method does not recieve any parameters it will return a string containing all messages previously set in the application <strong>$type</strong> will be the class of the tag returned
        