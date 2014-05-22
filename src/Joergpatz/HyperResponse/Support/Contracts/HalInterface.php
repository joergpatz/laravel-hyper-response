<?php
namespace Joergpatz\HyperResponse\Support\Contracts;

use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface HalInterface {

    /**
     * Gets the data to be sent as collection.
     *
     * @param Paginator $data
     *
     * @return Hal+Json
     */
    public function processPagination(Paginator $data);

    /**
     * Gets the data to be sent as collection.
     *
     * @param Collection $data
     *
     * @return Hal+Json
     */
    public function processCollection(Collection $data);

    /**
     * Gets the data to be sent as resource.
     *
     * @param Model $data
     *
     * @return Hal+Json
     */
    public function processResource(Model $data);

    /**
     * Gets the data to be sent as resource.
     *
     * @param array $data
     *
     * @return Hal+Json
     */
    public function processArray(array $data);
}