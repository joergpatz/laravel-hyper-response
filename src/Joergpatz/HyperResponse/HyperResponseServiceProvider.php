<?php
namespace Joergpatz\HyperResponse;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class HyperResponseServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('joergpatz/hyper-response');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $app = $this->app;

        // set new custom extended core class aliases
        // we don't have to register it in app/config/app.php
        $app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Response', 'Joergpatz\HyperResponse\Support\Facades\Response');
            $loader->alias('HalResponse', 'Joergpatz\HyperResponse\Http\HalResponse');
            $loader->alias('ApiProblemResponse', 'Joergpatz\HyperResponse\Http\ApiProblemResponse');
        });

        // Handle HttpException errors (Not others)
        $app->error(function(HttpExceptionInterface $exception, $code) use ($app)
        {
            //TODO-Improve: define Accept Headers in a central way and check it here
            if ( $app['request']->header('accept') === 'application/json' )
            {
                // I thought you must create 'Request' object manually
                // as $app['response'] doesn't exit when this is ran...
                // However we can grab its Facade (apparently)
                // via $app['Response'] - Note the capitalization

                //$response = new Response; # Don't need

                return $app['Response']::apiProblem(array(
                        'detail' => $exception->getMessage(),
                        'instance' => $app['request']->url(),
                        'code' => $code),
                    $code
                );
            }
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
        return array();
	}

}