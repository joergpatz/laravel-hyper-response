<?php
namespace Joergpatz\HyperResponse\Http;

use Crell\ApiProblem\ApiProblem;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Error Response represents an HTTP response in API-PROBLEM+JSON format.
 *
 * Note that this class does returned JSON content to be an
 * object. It is however recommended that you do return an object as it
 * protects yourself against XSSI and JSON-JavaScript Hijacking.
 *
 * @see https://www.owasp.org/index.php/OWASP_AJAX_Security_Guidelines#Always_return_JSON_with_an_Object_on_the_outside
 *
 */
class ApiProblemResponse extends JsonResponse {

    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  string  $value
     * @param  bool    $replace
     * @return \Illuminate\Http\Response
     */
    public function header($key, $value, $replace = true)
    {
        $this->headers->set($key, $value, $replace);

        return $this;
    }

    /**
     * Sets the data to be sent as api-problem+json.
     *
     * @param mixed $data
     *
     * @return ApiProblemResponse
     */
    public function setData($data = array())
    {
        //fill mandatory title
        $data['title'] = self::$statusTexts[$this->getStatusCode()];

        //check optional fields
        if (empty($data['detail'])) $data['detail'] = '';
        if (empty($data['instance'])) $data['instance'] = '';

        //create api-problem object
        $problem = new ApiProblem($data['title'], "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html");
        $problem->setDetail($data['detail']);
        $problem->setProblemInstance($data['instance']);
        $problem->setHttpStatus($this->getStatusCode());

        $this->data = $problem->asJson();

        return $this->update();
    }
}