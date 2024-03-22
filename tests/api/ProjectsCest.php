<?php

use yii\helpers\Url;
use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;
use Swaggest\JsonSchema\Schema;

class ProjectsCest
{
    var $swagger;

    public function _before(ApiTester $I)
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(Yii::getAlias('@app'),Yii::$app->params['envFile']);
        $dotenv->load();       
        $this->swagger = json_decode(file_get_contents(Yii::getAlias('@app/swagger.json')), true);
    }

    // tests
    public function tryToTestBasicProjectsResponse(ApiTester $I)
    {
        $url = Url::to(['/api/projects']);
        $I->sendGET($url);
        $response = $I->grabResponse();
        $I->seeResponseCodeIs(200); // HTTP Status 200
        $I->seeResponseIsJson();
        $responseData = json_decode($response);
        $schema = $this->swagger['components']['schemas']['ProjectsRes'];
        $schema['components'] = $this->swagger['components'];
        $validator = new Validator();
        $rt = $validator->validate($responseData, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);
        //print_r($responseData);
        $I->assertTrue($validator->isValid(), "Response did not validate against schema: " . print_r($validator->getErrors(),true));        
    }

    public function tryToTestProjectsResponseWithRecentTasks(ApiTester $I)
    {
        $_ENV['TIMELOG_FILEPATH'] = 'tests/_data/now3.log';
        $url = Url::to(['/api/projects']);
        $I->sendGET($url);
        $response = $I->grabResponse();
        $I->seeResponseCodeIs(200); // HTTP Status 200
        $I->seeResponseIsJson();
        $responseData = json_decode($response);
        $schema = $this->swagger['components']['schemas']['ProjectsRes'];
        $schema['components'] = $this->swagger['components'];
        $validator = new Validator();

        //$schema = Schema::import($schema);
        //$schema->in($response);
        $rt = $validator->validate($responseData, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);
        print_r($responseData);
        $I->assertTrue($validator->isValid(), "Response did not validate against schema: " . print_r($validator->getErrors(),true));        

        //now windlass project has a recent item
        $I->assertEquals($responseData->windlass->recent[0]->task,"meeting lastone");
        $I->assertEquals($responseData->windlass->recent[1]->task,"mrl: uploading mrl#14 secondlast");
        $I->assertEquals($responseData->windlass->recent[2]->task,"mrl: uploading mrl#14 thirdlast");
    }
}
