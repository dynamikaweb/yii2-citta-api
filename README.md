dynamikaweb/yii2-citta-api 
====================================
[![Latest Stable Version](https://img.shields.io/github/v/release/dynamikaweb/yii2-citta-api)](https://github.com/dynamikaweb/yii2-citta-api/releases) [![Total Downloads](https://poser.pugx.org/dynamikaweb/yii2-citta-api/downloads)](https://packagist.org/packages/dynamikaweb/yii2-citta-api) [![License](https://poser.pugx.org/dynamikaweb/yii2-citta-api/license)](https://github.com/dynamikaweb/yii2-citta-api/blob/master/LICENSE) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/bf1d2317b7cb41de87bea7d7bd927cd7)](https://www.codacy.com/gh/dynamikaweb/yii2-citta-api?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=dynamikaweb/yii2-citta-api&amp;utm_campaign=Badge_Grade) [![Build Test](https://scrutinizer-ci.com/g/dynamikaweb/yii2-citta-api/badges/build.png?b=master)](https://scrutinizer-ci.com/g/dynamikaweb/yii2-citta-api/) [![Quality Score](https://scrutinizer-ci.com/g/dynamikaweb/yii2-citta-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dynamikaweb/yii2-citta-api/) [![Latest Unstable Version](https://poser.pugx.org/dynamikaweb/yii2-citta-api/v/unstable)](https://github.com/dynamikaweb/yii2-citta-api/find/master)


Instalação
------------
ultilize [composer](http://getcomposer.org/download/) para instalar esta extensão.

execute

```bash
$ composer require dynamikaweb/yii2-1doc-api 
```
ou adicione

```json
"dynamikaweb/yii2-citta-api" : "*"
```
em seu arquivo `composer.json` da aplicação.

Como usar
-----
```PHP
use dynamikaweb\api\CittaApi;

class DemoController extends MyBaseController
{
    public function actionIndex($unidade, $exercicio)
    {   
        // set url api
        CittaApi::url("https://example.com");

        // make requests
        $categorias = CittaApi::request(['orgaos', 
            'unidadeGestora' => $unidade,
            'exercicio' => $exercicio
        ]);

        $anos = CittaApi::request(['ano/filtros']);
        
        // return response data
        return $this->render('index', [
            'categorias' => $categoria->models,
            'anos' => $anos->models
        ]);
    }
}
```
