<?php

namespace AlibabaCloud\RpcClient;

use Exception;
use AlibabaCloud\Tea\Request;
use AlibabaCloud\Tea\Response;
use InvalidArgumentException;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Support\Sign;
use AlibabaCloud\Client\Clients\Client;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Signature\ShaHmac1Signature;
use AlibabaCloud\Client\Credentials\Providers\CredentialsProvider;

/**
 * Class RpcClient
 *
 * @package AlibabaCloud\RpcClient
 */
class RpcClient
{
    /**
     * @var null/string
     */
    public $body;

    /**
     * @var string|null
     */
    protected $clientName;

    /**
     * @var mixed|string
     */
    protected $endpoint_host = '';

    /**
     * @param string|null $clientName
     * @param array       $config
     */
    public function __construct($clientName = null, array $config = [])
    {
        $this->clientName = $clientName;

        if (!isset($config['endpoint'])) {
            throw new InvalidArgumentException('endpoint can not be empty.');
        }

        $this->endpoint_host = $config['endpoint'];
    }

    /**
     * @param array $array
     *
     * @return TeaError
     */
    public function teaError(array $array)
    {
        return new TeaError($array['message']);
    }

    /**
     * @param array   $query
     * @param Request $request
     *
     * @return array
     * @throws Exception
     * @throws ClientException
     */
    public function _getQuery(array $query, Request $request)
    {
        $sign                      = $this->getClient()->getSignature();
        $query['Format']           = 'json';
        $query['SignatureMethod']  = $sign->getMethod();
        $query['SignatureVersion'] = $sign->getVersion();
        $query['SignatureNonce']   = $this->_getNonce();
        $query['Timestamp']        = $this->_getTimestamp();
        $query['AccessKeyId']      = $this->_getAccessKeyId();
        $query['SignatureType']    = $sign->getType();
        $query['Signature']        = $sign->rpc(
            $this->_getAccessKeySecret(),
            $request->getMethod(),
            $query
        );

        return $query;
    }

    /**
     * Get the client based on the request's settings.
     *
     * @return Client
     * @throws ClientException
     */
    public function getClient()
    {
        if (!$this->clientName) {
            $this->clientName = CredentialsProvider::getDefaultName();
        }

        if (!AlibabaCloud::all()) {
            if (CredentialsProvider::hasCustomChain()) {
                CredentialsProvider::customProvider($this->clientName);
            } else {
                CredentialsProvider::defaultProvider($this->clientName);
            }
        }

        return AlibabaCloud::get($this->clientName);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function _getNonce()
    {
        return Sign::uuid(time());
    }

    /**
     * @return string
     */
    public function _getTimestamp()
    {
        return gmdate('Y-m-d\TH:i:s\Z');
    }

    /**
     * @return mixed
     * @throws ClientException
     */
    public function _getAccessKeyId()
    {
        return $this->getClient()->getCredential()->getAccessKeyId();
    }

    /**
     * @return mixed
     * @throws ClientException
     */
    public function _getAccessKeySecret()
    {
        return $this->getClient()->getCredential()->getAccessKeySecret();
    }

    /**
     * @param Response $response
     *
     * @return Response|array
     */
    public function _json(Response $response)
    {
        return $response;
    }

    /**
     * @param string $product
     * @param string $regionId
     *
     * @return mixed|string
     */
    public function _getEndpoint($product, $regionId)
    {
        return $this->endpoint_host;
    }

    /**
     * @param Request $request
     *
     * @param string  $accessKeySecret
     *
     * @return string
     */
    public function _getSignature(Request $request, $accessKeySecret)
    {
        return (new ShaHmac1Signature())->rpc(
            $accessKeySecret,
            $request->getMethod(),
            $request->query
        );
    }

    /**
     * @param array $query
     *
     * @return array
     */
    public function _query(array $query)
    {
        return $query;
    }

    /**
     * @param Response $response
     *
     * @return bool
     */
    public function _hasError(Response $response)
    {
        return $response->getStatusCode() >= 400;
    }
}
