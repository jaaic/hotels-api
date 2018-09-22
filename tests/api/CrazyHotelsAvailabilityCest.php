<?php

use Codeception\Util\HttpCode;

class CrazyHotelsAvailabilityCest
{
    /** @var string */
    protected $path;

    /**
     * Setup before each test
     *
     */
    public function _before()
    {
        $this->path = '/providers/crazyhotels';
    }

    /**
     * Test successful call
     *
     * @param \ApiTester $I
     */
    public function testGetCrazyHotels(ApiTester $I)
    {
        $requestParams = [
            'city'        => 'DXB',
            'from'        => '2018-12-06 12:00:00',
            'to'          => '2018-12-07 12:00:00',
            'adultsCount' => 2,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();
        $searchRequestResponse = json_decode($I->grabResponse(), true);


        if (!empty($searchRequestResponse)) {
            $isValid = $this->checkResponseAttributes($searchRequestResponse);
        } else {
            $isValid = true;
        }

        \PHPUnit_Framework_Assert::assertTrue($isValid);
    }

    /**
     * Test elements in response
     *
     * @param array $searchResponse
     *
     * @return bool
     */
    private function checkResponseAttributes(array $searchResponse)
    {
        foreach ($searchResponse as $index => $hotel) {

            $hotelKeys = array_keys($hotel);
            if (!in_array('hotelName', $hotelKeys) ||
                !in_array('rate', $hotelKeys) ||
                !in_array('price', $hotelKeys) ||
                !in_array('amenities', $hotelKeys)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Test validation error for 0 adults
     *
     * @param \ApiTester $I
     */
    public function testGetCrazyHotelsThrowsValidationErrorForGuests(ApiTester $I)
    {
        $requestParams = [
            'city'        => 'DXB',
            'from'        => '2018-12-06 12:00:00',
            'to'          => '2018-12-07 12:00:00',
            'adultsCount' => 0,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();

        $I->seeResponseContains('{"status":"400","title":"Bad Request","detail":{"adultsCount":["validation.min.numeric"]},"type":"Client Error"}');
    }

    /**
     * Test validation error for checkout date before checkin date
     *
     * @param \ApiTester $I
     */
    public function testGetCrazyHotelsThrowsValidationErrorForDates(ApiTester $I)
    {
        $requestParams = [
            'city'        => 'DXB',
            'from'        => '2018-12-06 12:00:00',
            'to'          => '2018-12-04 12:00:00',
            'adultsCount' => 1,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();

        $I->seeResponseContains('{"status":"400","title":"Bad Request","detail":{"to":["validation.after"]},"type":"Client Error"}');
    }

    /**
     * Test validation error for date format
     *
     * @param \ApiTester $I
     */
    public function testGetCrazyHotelsThrowsValidationErrorForDateFormat(ApiTester $I)
    {
        $requestParams = [
            'city'        => 'DXB',
            'from'        => '2018-12-06',
            'to'          => '2018-12-04',
            'adultsCount' => 1,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();

        $I->seeResponseContains('{"status":"400","title":"Bad Request","detail":{"from":["validation.date_format"],"to":["validation.date_format","validation.after"]},"type":"Client Error"}');
    }

    /**
     * Test validation error for city to be IATA code with 3 chars
     *
     * @param \ApiTester $I
     */
    public function testGetBestHotelsThrowsValidationErrorForIATA(ApiTester $I)
    {
        $requestParams = [
            'city'        => 'Dubai',
            'from'        => '2018-12-06 12:00:00',
            'to'          => '2018-12-10 12:00:00',
            'adultsCount' => 1,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();

        $I->seeResponseContains('{"status":"400","title":"Bad Request","detail":{"city":["validation.size.string"]},"type":"Client Error"}');
    }
}
