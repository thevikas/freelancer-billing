<?php

use yii\helpers\Url;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

class NowCest
{
    var $swagger;

    public function _before(ApiTester $I)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'),Yii::$app->params['envFile']);
        $dotenv->load();       
        $this->swagger = json_decode(file_get_contents(Yii::getAlias('@app/swagger.json')), true);
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
        $logValue = "today: howdy";
        $tempLogfile = Yii::getAlias($_ENV['TIMELOG_FILEPATH']);
        //file_put_contents($tempLogfile,date('Y-m-01 H:i')  . ": init: dummy1\n" .
        //    date('Y-m-d H:i')  . ": init: dummy2\n");
        $url = Url::to(['/api/now', 'log' => $logValue]);
        $I->sendGET($url);
        $response = $I->grabResponse();
        $I->seeResponseCodeIs(200); // HTTP Status 200
        $I->seeResponseIsJson();
        $responseData = json_decode($response);
        $schema = $this->swagger['components']['schemas']['TaskTimeStamp'];
        $schema['components'] = $this->swagger['components'];
        $validator = new Validator();
        $rt = $validator->validate($responseData, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);
        $I->assertTrue($validator->isValid(), "Response did not validate against schema: " . print_r($validator->getErrors(),true));        

        //$res_schema = $this->findSchemaForApi(self::URL, $http, null, 'get');
        //echo "$res_schema = $res_schema\n";
        /*
        $main1 = $this->swagger['components']['schemas'][$res_schema];
        $schemas = $this->swagger['components'];
        $main1['components'] = $schemas;
        $validResponseJsonSchema = json_encode($main1);
        $response->assertJsonSchema($validResponseJsonSchema);
        */
    }
}
