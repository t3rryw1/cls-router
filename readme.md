# Serializable Router

a simple router to allow user to register classes to a certain http request


##Usage

```php

// routes.php

//regsiter with IRequestHandler class name, omit validator
register("POST","/account/register",AccountRegisterHandler::class);

//regsiter with IRequestHandler (class name / object), omit validator, with version
register("POST","/account/register", new AccountRegisterHandler(),"1.2.3" );

//regsiter with IRequestHandler (class name / object), with validator (class name / object) 
register("POST","/account/register", new AccountRegisterHandler(),new AccountRegisterValidator());

//regsiter with IRequestProcessor (class name / object) , with version
register("POST","/account/register", new AccountRegisterProcessor(), "1.2");

```

```php

// index.php

Router::init('/ajax');
include __DIR__ . "/routes.php";
Router::getInstance()->run(_g('$ip_block_list', []));       

```


