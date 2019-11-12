# Hyper Documentation v1.0.12
-- 
## Getting started

## TODOs
- Secure `web.json`

#### Create new Hyper app: index.php
###### Import ``HyperApp`` class:
```php
use Hyper\Application\HyperApp;
```
###### Import hyper file to enable most functions using ``require``
```php
require 'hyper/index.php';
```
###### Create and run the application
```php
new HyperApp("<Name of your app>", "auto");
```
###### Run app with custom sections
```php
$customSections = ["hello-section"];
new HyperApp("<Name of your app>", "auto", $customSections);
```

###### Run app with manual routing
```php
//TODO: work on manual routing
```

###### Complete index.php
```php
use Hyper\Application\HyperApp;

require 'Hyper/Index.php';

new HyperApp("<Name of your app>", "auto");
```
###### Usage:
```html
//Put section where you want it to be rendered. Usually layout.php
<h-section type="hello-section"></h-section>

//Put section content from other views
<h-section hello-section></h-section>
```

##### Config
Hyper configurations are stored in ``web.json`` or your own ``JSON`` file

###### Database connection
To configure a database, add a ``db`` object in the config file ``web.json``

For example:
```json
{
  "db": {
    "host": "localhost",
    "port": 80,
    "database": "<dbName>",
    "username": "root",
    "password": ""
  }
}
```
###### Debug
To turn on/off debug mode set ``debug`` property to ``json``
For example:
```json
{
  "debug": true
}
```
###### Custom error pages