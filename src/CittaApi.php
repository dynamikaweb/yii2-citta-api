<?php
/**
 *
 * @author Rodrigo Dornelles <rodrigo@dornelles.me> <rodrigo@dynamika.com.br>
 * 
 * @copyright Copyright (c) 2019 Dynamika Web
 * @link https://github.com/dynamikaweb/yii2-citta-api
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace dynamikaweb\api;

use Yii;

/** 
 * dynamikaweb\api\CittaApi 
 */
class CittaApi
{   
    /**
     * @param string $url_base
     * @param integer $max_redirect 
     */
    private static $url_base;
    private static $max_redirect = 10;

    /** 
     * __construct
     * 
     * @deprecated
     */ 
    private function  __construct()
    {

    }

    /**
     * setUrlBase
     * 
     * @param string $uri
     */
    public static function setUrlBase($uri)
    {
        self::$url_base = $uri;
    }

    /**
     * getUrlBase
     * 
     * @throws dynamikaweb\api\CittaException
     * 
     * @return string
     */
    public static function getUrlBase()
    {
        if (!self::$url_base) {
            throw new CittaException('URL Base Not Set');
        }

        if (!is_string(self::$url_base)){
            throw new CittaException('URL Base Type Error');
        }

        return self::$url_base;
    }

    /**
     * getUrl
     * 
     * @param string|array $uri 
     * 
     * @see yii\helpers\Url
     * @throws dynamikaweb\api\CittaException
     * 
     * @return string
     */
    public static function getUrlTo($uri)
    {
        if (is_array($uri)) {
            return strtr("{base_url}{uri}",[
                "{base_url}" => self::getUrlBase(),
                "{uri}" => \yii\helpers\Url::to($uri)
            ]);
        } else if (is_string($uri)) {
            return strtr("{base_url}/{uri}",[
                "{base_url}" => self::getUrlBase(),
                "{uri}" => $uri
            ]);
        }
        
        throw new CittaException('URI Request Type Error');
    }

    /**
     * makeRequest
     * 
     * @param string $uri
     * 
     * @return object
     */
    private static function makeRequest($uri)
    {
        $curl = new \Curl\Curl();
        $attemps = 0;

        // get link
        $request = self::getUrlTo($uri);

        // try
        do {
            $result = $curl->get($request);
        }
        // redirect
        while (self::remakeRequest($request, $attemps, $result));

        return $result;
    }  

    /**
     * remakeRequest
     * 
     * @property 
     * 
     * @param string $uri
     * @param integer $attemps
     * @param object $result
     * 
     * @throws dynamikaweb\api\CittaException
     * 
     * @return boolean
     */
    private static function remakeRequest(&$uri, &$attemps, $result)
    {
        // success
        if (300 > $result->http_status_code || $result->http_status_code >= 400) {
            return false;
        }

        // probably loop
        if ($attemps++ >= self::$max_redirect) {
            throw new CittaException('Too many redirects');
        }

        foreach ($result->response_headers as $header) {
            if (strpos($header, 'Location:') === false) {
                continue;
            }

            // new url
            $uri = strtr($header, [
                'Location: ' => '',
                'location: ' => '',
                'Location:' => '',
                'location:' => ''
            ]);
        }

        return true;
    }

    /**
     * cacheFindAll
     * 
     * @see yii\helpers\Json
     * @see yii\helpers\ArrayHelper
     * @see yii\data\ArrayDataProvider
     *
     * @param array|string $uri 
     * @param array $dataProviderParams
     * @param integer $duration
     * 
     * @throws dynamikaweb\api\CittaException
     * 
     * @return object
     */
    public static function cacheFindAll($uri, $dataProviderParams = [], $duration = null)
    {
        $key = base64_encode(self::getUrlTo($uri));

        if (!Yii::$app->cache->exists($key)){
            Yii::$app->cache->set($key, self::findAll($uri, $dataProviderParams), $duration);
        }

        return Yii::$app->cache->get($key);
    }

    /**
     * findAll
     * 
     * @see yii\helpers\Json
     * @see yii\helpers\ArrayHelper
     * @see yii\data\ArrayDataProvider
     *
     * @param array|string $uri 
     * @param array $dataProviderParams
     * 
     * @throws dynamikaweb\api\CittaException
     * 
     * @return object
     */
    public static function findAll($uri, $dataProviderParams = [])
    {
        // call api
        $result = self::makeRequest($uri);

        // http return a error
        if ($result->error && $result->http_status_code) {
            throw new CittaException(strtr('HTTP Status {code} Error: {error}', [ 
                    '{error}' => \yii\helpers\ArrayHelper::getValue(\yii\helpers\Json::decode($result->response, true), 'message', null),
                    '{code}' => $result->http_status_code
                 ])
            );
        }

        // curl return a error
        if ($result->error) {
            throw new CittaException($result->error_message);
        }

        // decode json
        $data = \yii\helpers\Json::decode($result->response, true);
        
        // return response data
        return new \yii\data\ArrayDataProvider(
            \yii\Helpers\ArrayHelper::merge(
                [
                    'allModels' =>  \yii\helpers\ArrayHelper::getValue($data, 'data', $data), // adapt data|root for unique patern
                    'totalCount' => \yii\helpers\ArrayHelper::getValue($data, 'count', count($data)), // total count api            
                    'pagination' => [
                        'pageSize' => \yii\helpers\ArrayHelper::getValue($uri, 'size', false),
                        'page' => false
                    ]
                ],
                $dataProviderParams
            )
        );
    }
}
