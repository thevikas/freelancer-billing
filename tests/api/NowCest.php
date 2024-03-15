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
        file_put_contents($tempLogfile,date('Y-m-01 H:i')  . ": init: dummy1\n" .
        date('Y-m-d H:i')  . ": init: dummy2\n");
        $url = Url::to(['/api/now', 'log' => $logValue]);
        $I->sendGET($url);
        $I->seeResponseCodeIs(200); // HTTP Status 200
        $I->seeResponseIsJson();
        $response = $I->grabResponse();
        $responseData = json_decode($response, true);

        $validator = new Validator();
        $validator->validate($response, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        // Assert that the response JSON includes expected keys or values
        // This part depends on what your API returns. For example:
        $I->assertArrayHasKey('success', $responseData);
        $I->assertEquals(true, $responseData['success']);
        
        //$res_schema = $this->findSchemaForApi(self::URL, $http, null, 'get');
        //echo "$res_schema = $res_schema\n";
        $main1 = $this->swagger['components']['schemas'][$res_schema];
        $schemas = $this->swagger['components'];
        $main1['components'] = $schemas;
        $validResponseJsonSchema = json_encode($main1);
        $response->assertJsonSchema($validResponseJsonSchema);

    }
}
