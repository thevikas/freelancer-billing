<?php

use yii\helpers\Url;

class NowCest
{
    public function _before(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
        $logValue = "today: howdy";
        $url = Url::to(['/api/now', 'log' => $logValue]);
        $I->sendGET($url);
        $I->seeResponseCodeIs(200); // HTTP Status 200
        $I->seeResponseIsJson();
        $response = $I->grabResponse();
        $responseData = json_decode($response, true);

        // Assert that the response JSON includes expected keys or values
        // This part depends on what your API returns. For example:
        $I->assertArrayHasKey('success', $responseData);
        $I->assertEquals(true, $responseData['success']);
    }
}
