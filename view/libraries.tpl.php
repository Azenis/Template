<?php echo $template->message(); ?>
<br>
The template comes with a variety of third party libraries already. simply use the <strong>-add($library)</strong> method and the application will automatically append the neccessary javascript and CSS to the DOM<br>
You can add third party libraries yourself, and then use the <strong>->stylesheet()</strong> and <strong>->javascript()</strong> to add the neccessary scripts

<br />
<div class="methods">
    <div>
        <span class="accept">Accepts</span><br />
        <ul>
            <li>lightbox
            <li>colorbox
            <li>jqueryui
        </ul>
        <span class="return">Returns</span>
        Bolean<br />
        <span class="error">Error</span>
        Will throw exception if not a valid argument
    </div>
</div>