# Template
[![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
![Downloads](https://img.shields.io/packagist/dt/wpbp/template.svg) 

Generate a fake page on the fly

## Install

`composer require wpbp/language:dev-master`

[composer-php52](https://github.com/composer-php52/composer-php52) supported.

## Example

```php
// This is like the woocommerce function
function load_content_demo( $original_template ) {
        if ( is_singular( 'demo' ) && in_the_loop() ) {
            return wpbp_get_template_part( 'content', 'demo', false );
        } else {
            return $original_template;
        }
}
add_filter( 'template_include', 'load_content_demo' );

// This is an extended version that search for folder with names based on locales like it_IT
$get_template_email = wpbp_get_template_part( 'header', 'prefix' );

// Look the code of the library
```

