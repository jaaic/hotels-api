<?php

use Codeception\Util\HttpCode;

class BestHotelsAvailabilityCest
{
    /** @var string */
    protected $path;

    /**
     * Setup before each test
     *
     */
    public function _before()
    {
        $this->path = '/providers/besthotels';
    }

    /**
     * Test successful call
     *
     * @param \ApiTester $I
     */
    public function testGetBestHotels(ApiTester $I)
    {
        $requestParams = [
            'city'           => 'DXB',
            'fromDate'       => '2018-12-06',
            'toDate'         => '2018-12-07',
            'numberOfAdults' => 1,
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
            if (!in_array('hotel', $hotelKeys) ||
                !in_array('hotelRate', $hotelKeys) ||
                !in_array('hotelFare', $hotelKeys) ||
                !in_array('roomAmenities', $hotelKeys)) {
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
    public function testGetBestHotelsThrowsValidationErrorForGuests(ApiTester $I)
    {
        $requestParams = [
            'city'           => 'DXB',
            'fromDate'       => '2018-12-06',
            'toDate'         => '2018-12-07',
            'numberOfAdults' => 0,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();

        $I->seeResponseContains('{"status":"400","title":"Bad Request","detail":{"numberOfAdults":["validation.min.numeric"]},"type":"Client Error"}');
    }

    /**
     * Test validation error for checkout date before checkin date
     *
     * @param \ApiTester $I
     */
    public function testGetBestHotelsThrowsValidationErrorForDates(ApiTester $I)
    {
        $requestParams = [
            'city'           => 'DXB',
            'fromDate'       => '2018-12-06',
            'toDate'         => '2018-12-04',
            'numberOfAdults' => 2,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();

        $I->seeResponseContains('{"status":"400","title":"Bad Request","detail":{"toDate":["validation.after"]},"type":"Client Error"}');
    }

    /**
     * Test validation error for city to be IATA code with 3 chars
     *
     * @param \ApiTester $I
     */
    public function testGetBestHotelsThrowsValidationErrorForIATA(ApiTester $I)
    {
        $requestParams = [
            'city'           => 'Dubai',
            'fromDate'       => '2018-12-06',
            'toDate'         => '2018-12-07',
            'numberOfAdults' => 1,
        ];

        /** @var \ApiTester $I api tester */
        $I->sendPOST($this->path, $requestParams);
        $I->seeResponseCodeIs(HttpCode::OK); // 200
        $I->seeResponseIsJson();

        $I->seeResponseContains('{"status":"400","title":"Bad Request","detail":{"city":["validation.size.string"]},"type":"Client Error"}');
    }
}
