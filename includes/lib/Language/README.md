# Widgets Helper Class
[![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
![Downloads](https://img.shields.io/packagist/dt/wpbp/language.svg) 

A class that extends the built-in WP_Widget class to provide an easier/faster way to create Widgets for WordPress.   
This is a fork of the original version with updates from the pull request on the official projects and few little improvements.

## Install

`composer require wpbp/language:dev-master`

[composer-php52](https://github.com/composer-php52/composer-php52) supported.

## Example

```php
echo get_language();

register_string( 'Test Wrapper', 'Test in progress', 'You are testing this wrapper' );

echo get_string( 'Test Wrapper', 'Test in progress', 'You are testing this wrapper' );

//Uncomment this after checked that in the plugin settings the string exist
deregister_string( 'Test Wrapper', 'Test in progress' );
```
