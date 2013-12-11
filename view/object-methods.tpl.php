<div class="methods">
    This section will briefly cover the methods you can take advantage of in your controller files<br />
    For more check out the <strong>utils.class.php</strong> file.<br />
    <br />

    <div>
        <strong>javascript($jscript)</strong> <br />
        Add destination files javascript to the DOM<br />
        <span class="accept">Accepts</span><br />
        <ul>
            <li>array containing the destinations for .js files</li>
        </ul>
        <span class="return">Returns</span><em>NONE</em><br />
        <span class="fail">Error</span><em>NONE</em><br />
        <span class="note">Notice</span>file must contain javascript
    </div>

    <div>
        <strong>stylesheet($styles)</strong> <br />
        Add destination files stylesheet to the DOM<br />
        <span class="accept">Accepts</span><br />
        <ul>
            <li>array containing the destinations for .css files</li>
        </ul>
        <span class="return">Returns</span><em>NONE</em><br />
        <span class="fail">Error</span><em>NONE</em><br />
        <span class="note">Notice</span>File must contain a valid type of css
    </div>
    <div>
        <strong>set($property, $value)</strong><br />
        Set a value for the specfied property in the object<br />
        <span class="accept">Accepts</span><br />
        <ul>
            <li>$property - the property name to set value for
            <li>$value - The value to be set on the property
        </ul>
        <span class="return">Returns</span><em>NONE</em><br />
        <span class="fail">Error</span><em>NONE</em>
    </div>
    <div>
        <strong>get($property)</strong><br />
        Gets the value for the specfied property in the object<br />
        <span class="accept">Accepts</span><br />
        <ul>
            <li>$property - the property of which the value is wanted
        </ul>
        <span class="return">Returns</span>A value from the objects property<br />
        <span class="fail">Error</span><em>NONE</em>
    </div>
</div>