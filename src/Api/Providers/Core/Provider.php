<?php

namespace seregazhuk\PinterestBot\Api\Providers\Core;

use seregazhuk\PinterestBot\Api\Request;
use seregazhuk\PinterestBot\Api\Response;
use seregazhuk\PinterestBot\Helpers\Pagination;

/**
 * Class Provider.
 */
abstract class Provider
{
    /**
     * List of methods that require logged status.
     *
     * @var array
     */
    protected $loginRequiredFor = [];

    /**
     * Instance of the API Request.
     *
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Executes a POST request to Pinterest API.
     *
     * @param array $requestOptions
     * @param string $resourceUrl
     * @param bool $returnResponse
     *
     * @return Response|bool
     */
    protected function post($requestOptions, $resourceUrl, $returnResponse = false)
    {
        $postString = Request::createQuery($requestOptions);

        $this->execute($resourceUrl, $postString);

        return $returnResponse ? $this->response : $this->response->isOk();

    }

    /**
     * Executes a GET request to Pinterest API.
     *
     * @param array $requestOptions
     * @param string $resourceUrl
     * @return array|bool|Response
     */
    protected function get(array $requestOptions = [], $resourceUrl = '')
    {
        $query = Request::createQuery(
            $requestOptions,
            $this->response->getBookmarks()
        );

        $this->execute($resourceUrl . '?' . $query);

        return $this->response->hasBookmarks() ?
            $this->response :
            $this->response->getResponseData();

    }

    /**
     * @param $url
     * @param string $postString
     * @return $this
     */
    protected function execute($url, $postString = "")
    {
        $result = $this->request->exec($url, $postString);

        $this->response->fillFromJson($result);

        return $this;
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public function checkMethodRequiresLogin($method)
    {
        return in_array($method, $this->loginRequiredFor);
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->request->isLoggedIn();
    }

    /**
     * @param mixed $data
     * @param string $resourceUrl
     * @param int $limit
     *
     * @return Pagination
     */
    protected function paginate($data, $resourceUrl, $limit = Pagination::DEFAULT_LIMIT)
    {
        return (new Pagination($limit))
            ->paginateOver(function() use ($data, $resourceUrl) {
                return $this->get($data, $resourceUrl);
            });
    }

    /**
     * Simply makes GET request to some url.
     * @param string $url
     * @return array|bool
     */
    public function visitPage($url = '')
    {
        return $this->get([], $url);
    }
}
