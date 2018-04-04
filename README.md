# Lightwork API [v2]
Light MVC framework for API

## Require :
- Edit config in kernel/Config-sample.php by your own account database
- Rename kernel/Config-sample.php by kernel/Config.php
- Execute : composer install
- Enjoy :)
- Optional : create a table Auth with 4 fields (pk int id, fk int user_id, string token, string date)
to use the secure system route by token

## Utils :
- Many examples are available in files of the framework like the routes (in index.php)
or the controller methods (in the folder app/controllers/)

## Routing methods :
- GET : Retrieve data
- POST : Insert data
- PUT : Update data
- DELETE : Remove data

## The documentation of your API :
- The documentation is almost entirely realized by the framework. It's available with the route /docs
- To add information on home page of documentation, go to the Doc.php file
- To add a reference with many routes, duplicate a json file in public/doc/ and enter all information that you can.
If some information can't be filled, enter null value
- With this information, the documentation is auto generate

## All methods of Controller can be used :
- ***static protected* _view($view, $data = [])** : render a view with twig. Use for the documentation
- ***static protected* _removeAttrs($array = [], $attrs = [], $type = 'obj')** : remove all attributes of an array.
The attributes is pass in an array like ['email', 'password']...
- ***static protected* _in_multi_array($array, $field, $needle)** : the same function that in_array but for multidimensional array
- getHeader($header = null)** : get the content of an header with its key
- ***static protected* _createToken($withIp = true)** : realize a random token, with or without ip hash
- ***static protected* _between($needle, $min = null, $max = null)** : check if the needle is between the min and the max
- ***static protected* _dateUs($date)** : convert the date french date to us date
- ***static protected* _toTimestamp($date)** : convert date to timestamp
- ***static protected* _match($pattern, $subject)** : check if subject match with pattern previously enter in the kernel/Config.php file
- ***static protected* _toJson($var)** : parse php content to json
- ***static protected* _addEventLog($code, $status, $method, $endpoint)** : add the code, status, method and endpoint in the log. It's 
use with error codes in the _render()
- ***static protected* _render($code, $data = null)** : render the status, with code previously enter in the kernel/status.json file , and the data

## All methods of Database can be used :
- ***static public* getTable()** : get table which called
- ***static public* getColumns($table = null)** : get fields by table name
- ***static public* where($where, $params = [], $order = null, $limit = null)** : get values with SELECT query and clause WHERE
- ***static public* whereFirst($where, $params)** : get first value with SELECT query and clause WHERE like ('name = ?', ['foo'])
- ***static public* whereLast($where, $params)** : get last value with SELECT query and clause WHERE ('name = ?', ['foo'])
- ***static public* find($params, $order = null, $limit = null)** : get values by params array like (['name' => 'foo'], 'date_register DESC', 100)
- ***static public* findOne($params)** : get first value by params array like (['id' => 1])
- ***static public* getById($id)** : get value by id
- ***static public* getAll($order = null, $limit = null)** : get all values in table
- ***static public* count($where = null, $params = [])** : get number of rows with or without params
- ***public* insert()** : insert new row like $user = new User(); $user->foo = 'bar'; $user->insert();
- ***public* update()** : update a row like $user = new User(1); $user->foo = 'foobar'; $user->update(); 1 is an id
- ***public* delete()** : delete a row like $user = new User(1); $user->delete(); 1 is an id
- ***static public* infoFk(&$handle, $replacesFk = [])** : get all information of foreign key with handle param which contain a fk
- ***static public* infoFk(&$handle, $replacesFk = [])** : get all information of foreign key with handle param which contain a fk
- ***static public* getLastId** : get the last id insert
- ***static public* query($statement, $params = null)** : get a values with SQL statement and params
- ***static public* exec($statement, $params)** : execute SQL statement (insert, update...) with params

### App :
    Your MVC Files : Controllers / Models / Views(for doc)
    
### Kernel :
    The kernel of the website : Config / Router / Database / Logs...
    
### Public :
    Your public files
    
### Main index.php :
    The main file of the site with the autoload, routing...
    
## Contact :
You can contact me :
- With my personal website -> https://adrien-martineau.fr/me-contacter/
- By open an issue in this repository
