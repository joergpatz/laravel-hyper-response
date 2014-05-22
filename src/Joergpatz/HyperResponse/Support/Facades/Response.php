<?php
namespace Joergpatz\HyperResponse\Support\Facades;

use Joergpatz\HyperResponse\Http\ApiProblemResponse;
use Joergpatz\HyperResponse\Http\HalResponse;
use Illuminate\Support\Contracts\ArrayableInterface;

class Response extends \Illuminate\Support\Facades\Response {

    /**
     * Return a new hal+json response from the application.
     *
     * @param  mixed  $data
     * @param  int    $status
     * @param  array  $headers
     * @return \Nocarrier\Hal
     */
    public static function hal($data = array(), $status = 200, array $headers = array())
    {
        $headers['Content-Type'] = 'application/hal+json';

        return new HalResponse($data, $status, $headers);
    }

    /**
     * Return a new application/api-problem+json response from the application.
     *
     * @param  mixed  $data
     * @param  int    $status
     * @param  array  $headers
     * @return \Crell\ApiProblem
     */
    public static function apiProblem($data = array(), $status = 200, array $headers = array())
    {
        if ($data instanceof ArrayableInterface)
        {
            $data = $data->toArray();
        }

        $headers['Content-Type'] = 'application/api-problem+json';

        return new ApiProblemResponse($data, $status, $headers);
    }
}