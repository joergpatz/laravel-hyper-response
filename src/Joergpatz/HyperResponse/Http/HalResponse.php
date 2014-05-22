<?php
namespace Joergpatz\HyperResponse\Http;

use Request;
use Nocarrier\Hal;
use Joergpatz\HyperResponse\Support\Contracts\HalInterface;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Response represents an HTTP response in HAL+JSON format.
 *
 * Hypertext Application Language (HAL)
 * is a simple format that gives a consistent and easy way to hyperlink between resources in your API.
 * @see http://stateless.co/hal_specification.html
 *
 */
class HalResponse extends JsonResponse implements HalInterface {

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
     * Sets the data to be sent as hal.
     *
     * @param mixed $data
     *
     * @return HalResponse
     */
    public function setData($data = array())
    {

        //is data a models PaginatOr?
        if ($data instanceof Paginator)
        {
            $this->data = $this->processPagination($data);
        }
        //is data a models collection?
        elseif ($data instanceof Collection)
        {
            $this->data = $this->processCollection($data);
        }
        //or a model resource?
        elseif ($data instanceof Model)
        {
            $this->data = $this->processResource($data);
        }
        //or array data?
        elseif (is_array($data) && !empty($data))
        {
            $this->data = $this->processArray($data);
        }
        //if wrong resource type -> abort the process with a api-problem
        else
        {
            App::abort(404, 'Wrong resource type for hal response');
        }

        return $this->update();
    }

    /**
     * Gets the data to be sent as pagination.
     *
     * @param Paginator $data
     *
     * @return Hal+Json
     */
    public function processPagination(Paginator $data)
    {
        // Prepend a string with a single instance of a given value.
        $self = '/'.ltrim(Request::path(), '/');

        $currentPage = $data->getCurrentPage();
        $lastPage    = $data->getLastPage();

        //if the client do need the pagination uri's
        $root = Request::root();
        $first = str_replace($root, '', $data->getUrl(1));
        $prev = ($currentPage > 1) ? str_replace($root, '', $data->getUrl($currentPage-1)) : null;
        $next = ($currentPage < $lastPage) ? str_replace($root, '', $data->getUrl($currentPage+1)) : null;
        $last = str_replace($root, '', $data->getUrl($lastPage));
        $page = str_replace($root, '', $data->getUrl($currentPage));

        //create hal object
        $hal = new Hal($self, array(
            'first'     => $first,
            'prev'      => $prev,
            'next'      => $next,
            'last'      => $last,
            'page'      => $page,
            'perPage'   => $data->getPerPage(),
            'from'      => $data->getFrom(),
            'to'        => $data->getTo(),
            'total'     => $data->getTotal()
        ));

        //embed data as resources, because the collection is also a resource
        $this->embedResource($hal, $data);

        return $hal->asJson();
    }

    /**
     * Gets the data to be sent as collection.
     *
     * @param Collection $data
     *
     * @return Hal+Json
     */
    public function processCollection(Collection $data)
    {
        // Prepend a string with a single instance of a given value.
        $self = '/'.ltrim(Request::path(), '/');

        //create hal object
        //NOTICE: second parameter of the collection can have data too!
        $hal = new Hal($self, array());

        //embed data as resources, because the collection is also a resource
        $this->embedResource($hal, $data);

        return $hal->asJson();
    }

    /**
     * Gets the data to be sent as resource.
     *
     * @param Model $model
     *
     * @return Hal+Json
     */
    public function processResource(Model $model)
    {
        //create hal object
        $hal = new Hal(null, $model->toArray());

        //fire event for getting links using their observers
        $linkStack = $this->getLinkStack($model);


        //get and set the required self link (every resource have a self link)
        $self = array_pull($linkStack, 'self');
        if (!isset($self['href'])) App::abort(500, 'Missing resource self link');
        $hal->setUri($self['href']);

        //check for optional links
        foreach($linkStack as $rel => $link) {
            $this->addLink($link, $hal, $rel);
        }

        //TODO: recognize the model embedded attribute


        return $hal->asJson();
    }

    /**
     * Gets the data to be sent as resource.
     *
     * @param array $data
     *
     * @return Hal+Json
     */
    public function processArray(array $data)
    {
        // Prepend a string with a single instance of a given value.
        $requestUri = '/'.ltrim(Request::path(), '/');

        //create hal object
        $hal = new Hal($requestUri, $data);

        //check for array _links
        if (!empty($data['_links']) && is_array($data['_links'])) {
            foreach($data['_links'] as $rel => $link)
                $this->addLink($link, $hal, $rel);
        }

        return $hal->asJson();
    }

    /**
     * embed resources, recognize the model embedded attribute
     *
     * @param Hal $hal
     * @param mixed $data
     *
     * @return bool
     */
    protected function embedResource(Hal &$hal, $data)
    {
        if (!$data) return false;

        //process collection items
        foreach($data as $item) {
            $classname = class_basename(get_class($item));

            //instantiate a new hal embedded resource
            $resource = new Hal(null, $item->toArray());

            // fire event for getting links using their observers
            $linkStack = $this->getLinkStack($item);

            //get and set the required self link (every resource have a self link)
            $self = array_pull($linkStack, 'self');
            if (!isset($self['href'])) App::abort(500, 'Missing resource self link');
            $resource->setUri($self['href']);

            //check for optional links
            foreach($linkStack as $rel => $link) {
                $this->addLink($link, $resource, $rel);
            }

            //TODO: recognize the model embedded attribute

            //for the embedded resource name, strip the namespace part and get the real pluralized class name
            $hal->addResource(str_plural(lcfirst($classname)), $resource);
        }

        return true;
    }

    /**
     * Fires the Modelevent "links" to retrieve the links-array
     * Events are implemented in the observers
     *
     * @param Model $model
     * @return array
     */
    protected function getLinkStack(Model $model)
    {
        return current(Event::fire('eloquent.links: ' . get_class($model), $model));
    }

    /**
     * sets the link format from the linkstack
     *
     * @param $link
     * @param $hal
     * @param $rel
     */
    protected function addLink($link, &$hal, $rel)
    {
        $title = isset($link['title']) ? $link['title'] : '';
        $hal->addLink($rel, $link['href'], array('title' => $title));
    }
}