## Getting started

######*NOTE: The `hyperPHP` framework uses Twig templating engine*

#### 1. Run `composer install`
*Why?*

#### 2. Run `composer dump-autoload -o`
*Why?*

#### 3. `composer.json`

Your `composer.json` basically looks like this:
```json
{
  "require": {
    "twig/twig": "^2.0", //Twig templating engine
    "funcphp/twig-compress": "dev-master", //HTML compression
    "ext-pdo": "*",
    "ext-json": "*",
    "ext-mysqli": "*"
  },
  "autoload": {
    "psr-4": {
      "Hyper\\": "vendor/hyper/", //OR replace with path to hyper
      "Controllers\\": "controllers/", //OR replace with path to your controllers
      "Models\\": "models/"//OR replace with path to your models
    }
  }
}
```

###### That's it, you're good to go, happy coding!!!

