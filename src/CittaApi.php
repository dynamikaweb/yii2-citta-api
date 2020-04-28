<?php
/**
 * @copyright Copyright (c) 2019 Dynamika Web
 * @link https://github.com/dynamikaweb/yii2-citta-api
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
namespace dynamikaweb\api;

use Yii;

/** 
 *
 * @author Rodrigo Dornelles <rodrigo@dornelles.me> <rodrigo@dynamika.com.br>
 * @version 0.1  (28/04/2020)
 * 
 */
class CittaApi extends \yii\base\Model
{   
    /**
     * @param string $url_reference
     * @param object $instance 
     */
    private static $url_reference;
    private static $instance;

    /** 
     * __construct
     * 
     * @see Sigleton Pattern
     *   
     */ 
    private function  __construct ( $config = [] )
    {
        parent::__construct( $config );
    }

    /**
     * url
     * 
     * @see Sigleton Pattern
     *
     * @param array|void $url_reference
     * @return object self
     * 
     */
    public static function url($uri = null)
    {   
        // change API URI
        if($uri !== null){
            self::$url_reference = $uri;
        }

        if(self::$instance === null){
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * request
     * 
     * dataProvide
     *
     * @param array|string $uri 
     * @return array|void $dataProviderParams
     * 
     * @throws CittaException
     * 
     */
    public static function request($uri, $dataProviderParams = [])
    {
        $curl = new \Curl\Curl();
        $redirects_count = 0;

        // base URI reference
        if (!self::$url_reference){
            throw new CittaException('URI Reference Error');
        }
        
        // consult URI
        if (is_array($uri)){
            $curl->get(self::$url_reference.\yii\helpers\Url::to($uri));

        } else if (is_string($uri)){
            $curl->get(self::$url_reference."/{$uri}");

        } else {
            throw new CittaException('URI Type Error');
        }


        // redirect URI
        while (300 <= $curl->http_status_code && $curl->http_status_code < 400){
            // probably loop
            if (++$redirects_count > 10){
                throw new CittaException('Too many redirects');
            }

            foreach ($curl->response_headers as $header) {
                // ignore header
                if (strpos($header, 'Location:') === false){
                    continue;
                }

                // try new uri
                $curl->get(strtr($header,[
                        'Location: ' => '',
                        'location: ' => '',
                        'Location:' => '',
                        'location:' => ''
                    ])
                );
            }
        }

        // resquest error
        if ($curl->error && $curl->http_status_code){
            throw new CittaException("HTTP Status {$curl->http_status_code} Error");
        }

        // curl error
        if ($curl->error){
            throw new CittaException($curl->error_message);
        }

        // return response data
        return new \yii\data\ArrayDataProvider(
            \yii\Helpers\ArrayHelper::merge(
                ['allModels' =>  \yii\helpers\Json::decode($curl->response, true)],
                $dataProviderParams
            )
        );
    }
}