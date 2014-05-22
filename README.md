# (Prototype) Laravel Hyper Response

This package includes a Laravel "Hyper Response" Service Provider for HAL and ApiProblem responses.
[Hypertext Application Language](http://stateless.co/hal_specification.html) (HAL) is a simple format that gives a consistent and easy way to hyperlink between resources in your API. The second, [ApiProblem](http://tools.ietf.org/html/draft-nottingham-http-problem-06), is a simple specification for formatting error responses in your API.

*I will try to get this developed proprietary workbench package (from a frozen project) to deploy on packagist.org.*

Anyway you can add the **HyperResponseServiceProvider** in `app/config/app.php`:

    'Joergpatz\HyperResponse\HyperResponseServiceProvider',

## Usage

```php
$hal_data = array(
            	'message'         => 'Welcome to the App API!',
            	'_links'        => array(
                	'self' => array(
                    	'href'  => URL::to('/', array(), false),
                    	'title'  => 'You have arrived.'
                	),
                	'content' => array(
                    	'href'  => 'http://stateless.co/hal_specification.html',
                    	'title'  => 'content type: application/hal+json'
                	),
                	'api-problem' => array(
                   		'href'  => 'http://tools.ietf.org/html/draft-nottingham-http-problem-04',
                    	'title'  => 'api-problem type: application/api-problem+json'
                	),
            	)
        	);

Response::hal($hal_data);
```