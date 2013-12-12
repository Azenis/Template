
The following steps is how the object processes a request<br><br>
1)<br>
<strong>preprocess_page()</strong> will be invoked if it exists, this is for executing data across all pages and is run before the controller<br><br>
2)<br>
<strong>controller</strong> if a matching controller file to the current requested page is found it will be executed, use to generate variables for the view file.<br>
controller files is being executed before any ouput is sent to the browser<br>
These files must also be named after the page being requested (?page=mypage in the url)<br><br>
3)<br>
<strong>View</strong> file, is the file doing the magic, use this file to construct your html output, you can use your variables from the controller here.<br>
Those files must also have the extension .tpl.php, this is to distinguish view files form controller files<br><br>


