To start of you are granted the <strong>$template</strong> variable, this will contain all methods and properties of the <strong>template.class.php</strong> use it to make your code more readable. later referenced to as 'the object'<br><br>

The system works by first constructing the stylesheet and javascript links to be placed in the DOM, using the methods in the <strong>->javascript</strong> and <strong>->stylesheet()</strong><strong>template.class.php</strong> file<br />
It then finds a <strong>controller</strong> file this file is the where you extract from databases, create variables to be used later on, if such controller file is found. PHP will execute it.<br />
Now after that, it will require the template file this file contains the ovarall structure of the website, such as menu, footer and a content box.<br /><br>
This template file WILL require a <strong>view</strong> file must be the same name as the controller file. the view file contains the HTML for page specific content variables created in the according controller file should be used here.<br><br />
If you do not have the view file a 404 page will be issued
<br />
A <strong>controller</strong> is being executed BEFORE any HTML is output.<br />
a <strong>view</strong> file is being exevuted AFTER HTML output.<br />
<pre>
        $this->allowed["page-name"] = "default page title";
        $this->allowed["new-page"] = "Another page title";
</pre>
The main menu (assuming you have one) is to be constructed in the <strong>menu()</strong> method of <strong>template.class.php</strong> as follows:
<pre>
        $menu["href"] = "value";
</pre>
A <strong>controller</strong> file must be named after the page being visited, E.G if the page is contact the <strong>controller</strong> file must be named contact.php<br />
<strong>View</strong> files have the sub-extension .tpl to distinguish between controller filers and view files.. for example: contact.tpl.php
<br>
The template also has a default <strong>__autoload</strong> method implemented which means you do not need to require the class files everytime i want to use a new class.<br />
<br />
There is also an <strong>upload.class.php</strong> file attached in the system, together with some handy PHP functions in <strong>utils.class.php </strong>
<br />
Having all this stuff in one place makes it very easy for one to get a head start and code without having to worry about every little detail.<br />
It will take time to use the system, and it will cause you a headache or two, but when you learn to use one and know how to code OOP, life's going to be much easier for you.<br />
<br />
<em>I'd recommend once you learned to code in this template, go ahead onto a real framework like code igniter, which have way more documentation than mine will ever get.</em>
