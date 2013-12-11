This application uses the simpleDB(); class for database handling.<br>
Connection goes through the <strong>index.php</strong> file and is as follows:
<pre>
    $db = new SimpleDB(SimpleDB::MYSQL, 'localhost', 'username', 'password', 'databasename');
</pre>
See <strong>template/SimpleDB.class.php</strong>