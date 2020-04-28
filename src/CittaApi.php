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
 * 
 *
 * @author Rodrigo Dornelles <rodrigo@dornelles.me> <rodrigo@dynamika.com.br>
 * @version 0.1  (28/04/2020)
 * 
 */
class CittaApi extends \yii\base\Model
{   
    private static $url_reference;
    private static $instance;

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


    public static function request($uri, $dataProviderParams = [])
    {
        $curl = new \Curl\Curl();

        // base URI reference
        if (!self::$url_reference){
            throw new CittaException('URI Reference Error');
        }
        
        // construct URI
        if (is_array($uri)){
            $uri[0] = self::$url_reference.$uri[0];
            $uri = \yii\Helpers\Url::to($uri);

        } else if (is_string($uri)){
            $uri = self::$url_reference.$uri;

        } else {
            throw new CittaException('URI Type Error');

        }

        // return consult
        return \yii\data\ArrayDataProvider(
            \yii\Helpers\ArrayHelper::merge(
                ['allModels' =>  yii\helpers\Json::decode($curl->get($uri), true)],
                $dataProviderParams
            )
        );
    }
}