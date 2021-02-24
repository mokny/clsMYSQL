# clsMYSQL
A PHP MySQL wrapper class that can handle multiple database connections (sync)

## Example
```php
include("clsMYSQL.php");

//Initiate the class with login credentials for your primary database
$sql = new MYSQL('username','password','server','database');

//Add multiple connections. Last bool parameter makes this database the primary database for read requests if set to true
$sql->AddConnection("username", "password", "server", "database", false);

//Query the database and receive an associative array with all the data from myTable
$data = $sql->Get('SELECT * FROM myTable');

//Review the received data:
var_dump($data);

//Insert data into the table ´people´
$newrow = array();
$newrow['name'] = "John Doe";
$newrow['age'] = 55;

$sql->Insert('people', $newrow);

//Update data
$changedata = array();
$changedata['name'] = "John Foo";

$sql->Update('people', $changedata, "age > 50");

//Execute Statement(s) without return (accepts multiple statements, seperated by semicolon ";"
$sql->Execute('DELETE FROM people WHERE age > 50');

//Get Row-Count of a table
$peoplecount = $sql->Count("people");

//Escape a string for safety
$str = $sql->Escape("This could be an injection");

//Close all connections
$sql->CloseAll()
```
