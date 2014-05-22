# (Prototype) Laravel Hyper Response

This package includes a Laravel "Hyper Response" Service Provider for HAL and ApiProblem responses.
[Hypertext Application Language](http://stateless.co/hal_specification.html) (HAL) is a simple format that gives a consistent and easy way to hyperlink between resources in your API. The second, [ApiProblem](http://tools.ietf.org/html/draft-nottingham-http-problem-06), is a simple specification for formatting error responses in your API.

*Work in progress: I will try to deploy this developed proprietary workbench package (from a frozen project) on packagist.org.*

Anyway you can add the **HyperResponseServiceProvider** in `app/config/app.php`:

    'Joergpatz\HyperResponse\HyperResponseServiceProvider',

## Usage

Just like the Laravel Response::json() method, give the inherited Response Facade also a data type.

For the HAL response:

```php
$hal_data = array(
            	'message'	=> 'Welcome to the App API!',
            	'_links'	=> array(
                	'self'	=> array(
                    	'href'	=> URL::to('/', array(), false),
                    	'title'	=> 'You have arrived.'
                	),
                	'content'	=> array(
                    	'href'	=> 'http://stateless.co/hal_specification.html',
                    	'title'	=> 'content type: application/hal+json'
                	),
                	'api-problem'	=> array(
                   		'href'	=> 'http://tools.ietf.org/html/draft-nottingham-http-problem-04',
                    	'title'	=> 'api-problem type: application/api-problem+json'
                	),
            	)
        	);

Response::hal($hal_data);
```

The Response::hal() method accept not only an array. You can give them also a Eloquent Model, Collection or Paginator object.


For the ApiProblem response:

```php
$apiproblem_data = array(
                    'detail'    => 'Your requested resource was not found.',
                    'instance'  => Request::url(),
                    'code'      => 404
                   );

Response::apiProblem($apiproblem_data, 404);
```

The Response::apiProblem() method accept only an array.


## HAL Links

*I still have to write about that*