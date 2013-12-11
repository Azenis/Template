Below is a list of default functions included, which should not be removed. Located in <strong>includes/functions.php</strong><br>
<small>(Well I can't prevent you from doing that, but you'll regret doing that.)</small><br>

<div class="methods">
    <div>
        <strong>__autoload($class)</strong> <br />
        Attempts to autoload a class when instantiated<br />
        <span class="accept">Accepts</span><br />
        <ul>
            <li>A class name</li>
        </ul>
        <span class="return">Returns</span><em>NONE</em><br />
        <span class="fail">Error</span><em>NONE</em><br />
    </div>

    <div>
        <strong>debug($array, $stacktrace = false)</strong> <br />
        Prints out a human readable array,<br />
        <span class="accept">Accepts</span><br />
        <ul>
            <li>array or object for which you want the information extracted</li>
            <li>Boolean, whether or not to provide a function stacktrace</li>
        </ul>
        <span class="return">Returns</span><em>NONE</em><br />
        <span class="fail">Error</span><em>NONE</em><br />
    </div>
    <div>
        <strong>preprocess_page()</strong> <br />
        Logic to be exeuted before the page is generated.<br />
        <span class="accept">Accepts</span><em>NONE</em><br>
        <span class="return">Returns</span>User defined<br />
        <span class="fail">Error</span>User defined<br />
    </div>
    <div>
        <strong>hook_menu()</strong> <br />
        Contains an array which will be constructed intoa listable menu<br />
        Keys - Will become the destination on the hyperlink<br>
        Values - Will become the value on the hyperlink<br>
        <span class="accept">Accepts</span><em>NONE</em><br>
        <span class="return">Returns</span>Array<br />
        <span class="fail">Error</span><em>NONE</em><br />
    </div>
    <div>
        <strong>hook_pages($class)</strong> <br />
        List of pages which are allowed to be shown.<br />
        <span class="accept">Accepts</span><em>NONE</em><br />
        <span class="return">Returns</span>Array<br />
        <span class="fail">Error</span><em>NONE</em><br />
    </div>
</div>