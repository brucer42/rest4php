<?php

//********************************
// This is main application file. It's the onlyone that is aware of context (that is,
// if we are running the script from server environemnt or CLI environment).
// All other classes are free from context. They can be run from server, CLi, unittestst.
// We map all requests to index.php, so this is central point (Front Controller)
//********************************

// Auto_load implementation, include commons
require('Bootstrap.php');

//if CLI context, pass required paramaters as CMD arguments
php_sapi_name() == 'cli' ? $handler = strtolower($argv[1]) : $handler = strtolower($_REQUEST['handler']);

//predefined REST API ROUTES
$routes = array(
    '/(get|set|delete|create)\/folder\/([0-9]*)/' => array('controller'=>'Categorie'),
    '/(get|set|delete|create)\/ad\/([0-9]*)/' => array('controller'=>'Add'),
    '/(q)\/folder\/([0-9]*)\/ad/' => array('controller'=>'Queries', 'query'=>'from adds where folder_id = '),
);

//dispatcher
foreach (array_keys($routes) as $key){
    if (preg_match($key, $handler, $matches, PREG_OFFSET_CAPTURE)){
        $request_method = $matches[1][0];
        $specifier = $matches[2][0];
        $controller = $routes[$key]['controller'];
        switch ($request_method){
            case 'get':
            case 'delete':
                $params =  empty($specifier) ? array() : array('id'=>$specifier);
                break;
            case 'set':
            case 'create':
                $raw = file_get_contents('php://input');
                $params = json_decode($raw,true);
                break;
            case 'q':
                $params = array('query'=> $routes[$key]['query'] . $specifier );
                break;
        }
        
        //ime rezrede
        $controllerName = '\\controllers\\' . ucfirst($controller);
        
        //naredim instanco
        $controller = new $controllerName();

        $response = Call_User_Func(Array($controller, $request_method), $params);
        break;
    }
}

//if we are running this in server environment, render HTTP headers
if (php_sapi_name() != 'cli')
    header("Content-Type: text/json; charset=utf-8");

//TODO (če ni route || responsa) >> default switch oz. errMessage

echo $response;
