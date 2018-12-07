# Lightwork API [v3.1]
Light MVC framework for API to create API RESTful most easier

## Patch note v3 -> v3.1 :
- Status in the render() change:
    - The code error or success is define in the code and not in property "success" (ex: E_A001 for Error A001 or S_G001 for Success G001)
    - "Detail" becomes "message"
- The key is no longer a property but it in the "message"
- Fix issue on class Mail
- Add class Captcha with public and private keys in class Config

## Require :
- Edit config in kernel/Config-sample.php by your own information
- Rename kernel/Config-sample.php by kernel/Config.php
- Execute : composer install
- Enjoy :)
- Optional (to use token system) : create a table Auth with 4 fields (pk int id, fk int user_id, string token, string date)
to use the secure system route by token

## More info :
- Many examples are available in files of the framework like the routes (in index.php and documentation)

## HTTPs requests :
- GET : Retrieve data
- POST : Insert data
- PUT : Update data
- DELETE : Remove data

## The documentation of your API :
- The documentation is auto generate
- For the documentation generation to be perfect, fill in the information on each of the routes in the index.php

## Routing examples
- Groupe :
```php
$router->group('/users', function (Group $group) {

    $group->add('GET', '/:slug-:id', "User#index", "Just index", true, [], [
        'slug' => 'String'
    ]);

}, null, [], [ // Params of routes in this group
    'id' => 'Int',
]);
```
- GET :
```php
$router->add('GET', '/users', "User#getAll", "Get all User");
```
- POST :
```php
$router->add('POST', '/users', "User#add", "Add a new User", true, [
    '>' => 2,
    '<=' => 5
], [
    '*email' => '',
    '*password' => 'String',
    'name' => 'String',
    'age' => 'Int',
]);
```
- PUT :
```php
$router->add('PUT', '/users/:id', "User#edit", "Edit a User", true, [], [
    'id' => 'Int'
]);
```
- DELETE :
```php
$router->add('DELETE', '/users/:id', "User#delete", "Delete a User", true, [], [
    'id' => 'Int'
]);
```

## Utils Features (Utils is a class with many tools) :
- ***public static* createToken($withIp = true)** : realize a random token, with or without ip hashed (sha1)
```php
$token = Utils::createToken();
```
- ***public static* between($needle, $min = null, $max = null)** : check if the needle is between the min and the max
```php
$num = 24
if (Utils::between($num, 10, 30)) {
    echo 'success';
}
```
- ***public static* dateUs($date, $key = 'date')** : convert the french date to us date (if is not a french date, generate an error)
```php
$dateUs = Utils::dateUs($date);
or
$dateUs = Utils::dateUs($dateLogin, 'date_login');
```
- ***public static* toTimestamp($date)** : convert us date to timestamp
```php
$dateUs = Utils::dateUs($date);
$timestamp = Utils::toTimestamp($dateUs);
```
- ***public static* setInterval($callback, $seconds)** : it is the equivalent to the setInterval in JS. To stop the interval, return true. Example : 
```php
Utils::setInterval(function($time) {
    echo $time;
    return ($time == 30); // Stop the interval when the time is 30 seconds
}, 10);
```
- ***public static* getHeader($header = null)** : get the content of an header with its key
```php
$token = Utils::getHeader('token');
```
- ***private static* _getallheaders()** : get all headers
- ***public static* match($pattern, $subject)** : check if subject match with pattern previously enter in the kernel/Config.php file
```php
if (
    Utils::match(Config::getReg()['email'], $email)
) {
    echo 'success';
}
```
- ***public static* removeAttrs($array = [], $attrs = [], $type = 'obj')** : remove all attributes of an array.
The attributes is pass in an array like ['email', 'password']...
```php
$users = User::getAll();
$users = Utils::removeAttrs($users, ['email', 'password'];
```
- ***public static* in_multi_array($array, $needle)** : the same function that in_array but for multidimensional array
```php
$key = Utils::in_multi_array($users, 'my@email.com');
if ($key) {
    var_dump($users[$key];
}
```
- ***public static* setValuesInObject(&$haystack, $needle, $escape = [])** : set the values of existing properties in object ($haystack) with the other object $needle which contain the same keys
```php
public static function addUser($post) {
    $user = new User();

    // Set all values sent in post in the user object
    Utils::setValuesInObject($user, $post, ['id'];
    
    $user->insert();
}
```
- ***public static* checkPropsInObject($haystack, $needle, $type = [])** : check the existing properties in object ($haystack) with the $needle array which contain the keys to check. Check also the type if necessary (if the $type contain the key)
```php
public static function editUserBio($put) {
    // Render an error A005 if the property biography is not in the put object
    Utils::checkPropsInObject($put, ['biography'], ['String']);
}
```
- ***public static* setRegex($type)** : set the regex with the type (like : String => '\w+')
```php
$regex = Utils::setRegex('String');

echo $regex;
// Output : \w+
```
- ***public static* parse_http_put()** : parse and get all data sent in put

## Controller Features :
- ***protected static* _view($view, $data = [])** : render a view with twig. Use for the documentation
```php
public static function getUserById($params) {
    // Render the view 'user.html.twig' with the data user which contain the user with its id
    self::_view('user', [
        'user' => User::getById($params->id)
    ]);
}
```
- ***protected static* _toJson($var)** : parse php content to json
```php
public static function getUserById($params) {
    self::_toJson(User::getById($params->id));
}
```
- ***protected static* _addEventLog($code, $key = null, $status, $method, $endpoint)** : add the code, $key (if needed) status, method and endpoint in the log. It's 
use with error codes in the render()
```php
public static function getUserById($params) {
    if (null == User::getById($params->id)) {
        // Info : this method is already used by the render method
        self::_addEventLog('A006', 'id', '422', 'GET', '/users/'.$params->id);
    }
}
```
- ***public static* render($code, $data = false, $key = null)** : render the status, with code previously enter in the kernel/status.json file, the data, and the key (if needed)
```php
public static function getUserById($params) {
    // Render in json, the code G001 (success code) and the user
    self::render('G001', User::getById($params->id));
    // Output : 
    //{
    //  "success": true,
    //    "code": "G001",
    //    "status": 200,
    //    "title": "OK",
    //    "method": "GET",
    //    "endpoint": "/users/2"
    //  },
    //  data: {
    //    "id": 2,
    //    "email": "my@email.com",
    //    ...
    //  }
    //}
}
```

## Database Features :
- ***public static* getTable()** : get table which called
- ***public static* getColumns($table = null)** : get fields by table name
- ***public static* where($where, $params = [], $order = null, $limit = null)** : get values with SELECT query and clause WHERE
```php
$users = User::where('role_id > ?', [3]);
```
- ***public static* whereFirst($where, $params)** : get first row with SELECT query and clause WHERE like ('name = ?', ['foo'])
```php
$user = User::whereFirst('id = ?', [3]);
```
- ***public static* whereLast($where, $params)** : get last row with SELECT query and clause WHERE ('name = ?', ['foo'])
```php
$user = User::whereLast('role_id <= ?', [3]);
```
- ***public static* find($params, $order = null, $limit = null)** : get values by params array like (['name' => 'foo'], 'date_register DESC', 100)
```php
$users = User::find(['role_id' => 3, 'id DESC', 50]);
```
- ***public static* findFirst($params)** : get first row by params array like (['id' => 1])
```php
$user = User::findFirst(['email' => 'my@email.com']);
```
- ***public static* findLast($params)** : get last row by params array like (['id' => 1])
```php
$user = User::findLast(['name' => 'Foo']);
```
- ***public static* getById($id)** : get row by id
```php
$user = User::getById(10);
```
- ***public static* getAll($order = null, $limit = null)** : get all values in table
```php
$users = User::getAll();
```
- ***public static* count($where = null, $params = [])** : get number of rows with or without params
```php
$nbUsersWithRoleLessThan4 = User::count('role_id < ?', [4]);
```
- ***public* insert()** : insert new row like $user = new User(); $user->foo = 'bar'; $user->insert(); and return the id inserted
```php
$user = new User();
$user->firstname = 'Foo';
$user->lastname = 'Bar';
$user->email = 'my@email.com';
$user->insert();
```
- ***public* update()** : update a row like $user = new User(1); $user->foo = 'foobar'; $user->update(); 1 is an id
```php
$user = new User(3);
$user->email = 'my@new-email.com'; // It's the new value
$user->update();
```
- ***public* delete()** : delete a row like $user = new User(1); $user->delete(); 1 is an id
```php
$user = new User(3);
$user->delete();
```
- ***public* infoFk($targetModel = [])** : get all information of foreign key with the object which contain a fk. Example : $user->infoFk()
```php
$users = User::getAll();
foreach($users as $user) {
    $user->infoFk();
    or
    $user->infoFk('educ_id' => 'Education'); // to call the Education model
}
```
- ***public static* getLastId** : get the last id insert
```php
$userId = User::getLastId();
```
- ***public static* query($statement, $params = null)** : get a values with SQL statement and params
```php
$users = User::query('SELECT * FROM user');
```
- ***public static* exec($statement, $params)** : execute SQL statement (insert, update...) with params
```php
User::exec('DELETE FROM user WHERE id = ?', [2]);
```

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
