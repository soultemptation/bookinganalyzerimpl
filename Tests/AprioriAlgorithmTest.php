<?php
require_once dirname(__DIR__) . '/Interfaces/AprioriProgress.php';
require_once dirname(__DIR__) . '/Interfaces/Field.php';
require_once dirname(__DIR__) . '/Interfaces/DataIterator.php';
require_once dirname(__DIR__) . '/Business/Algorithms/AprioriAlgorithm.php';
require_once dirname(__DIR__) . '/Business/BookingDataIterator.php';
require_once dirname(__DIR__) . '/Models/Booking.php';
require_once dirname(__DIR__) . '/Models/Histograms.php';
require_once dirname(__DIR__) . '/Models/Histogram.php';
require_once dirname(__DIR__) . '/Models/HistogramBin.php';
require_once dirname(__DIR__) . '/Models/Price.php';
require_once dirname(__DIR__) . '/Models/Distance.php';
require_once dirname(__DIR__) . '/Models/DataTypeCluster.php';
require_once dirname(__DIR__) . '/Models/IntegerField.php';
require_once dirname(__DIR__) . '/Models/BooleanField.php';
require_once dirname(__DIR__) . '/Models/FloatField.php';
require_once dirname(__DIR__) . '/Models/StringField.php';
require_once dirname(__DIR__) . '/Models/PriceField.php';
require_once dirname(__DIR__) . '/Models/DistanceField.php';
require_once dirname(__DIR__) . '/Models/DayOfWeekField.php';
require_once dirname(__DIR__) . '/Models/MonthOfYearField.php';
require_once dirname(__DIR__) . '/Models/DayOfWeek.php';
require_once dirname(__DIR__) . '/Models/MonthOfYear.php';
require_once dirname(__DIR__) . '/Utilities/ConfigProvider.php';
require_once __DIR__ . '/BookingDataIteratorMock.php';

use PHPUnit\Framework\TestCase;

class AprioriAlgorithmTest extends TestCase
{
    private $configMock;
    private $aprioriProgressMock;
    private $factoryMock;
    private $filtersMock;

    private function GetBooking($intRooms, $intBedrooms, $intStars, $boolTv, $boolBbq, $boolPets,
                                $boolBalcony, $boolSauna, $floatLong, $floatLat, $price, $diSea, $diLake, $diSki)
    {
        return new Booking(1, new DataTypeCluster(
            ['ROOMS' => new IntegerField('ROOMS', $intRooms),
                'BEDROOMS' => new IntegerField('BEDROOMS', $intBedrooms),
                 'STARS' => new IntegerField('STARS', $intStars)],
            ['TV' => new BooleanField('TV', $boolTv),
                'BBQ' => new BooleanField('BBQ', $boolBbq),
                'PETS' => new BooleanField('PETS', $boolPets),
                'BALCONY' => new BooleanField('BALCONY', $boolBalcony),
                'SAUNA' => new BooleanField('SAUNA', $boolSauna)],
            ['long' => new FloatField('long', $floatLong, $floatLong),
                'lat' => new FloatField('lat', $floatLat, $floatLat)],
            [],
            ['PRICE' => new PriceField('PRICE', $price)],
            ['SEA' => new DistanceField('SEA', $diSea),
                'LAKE' => new DistanceField('LAKE', $diLake),
                'SKI' => new DistanceField('SKI', $diSki)],[],[]));
    }

    protected function setUp()
    {
        $map = [
            ['apriori', [
                'minSup' => 0.6,
                'serviceStopFile' => '',
                'outputInterval' => '',
                'serviceOutput' => '',
            ]],
            ['ignoreFields', []],
        ];
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')
            ->will($this->returnValueMap($map));

        $this->aprioriProgressMock = $this->createMock(AprioriProgress::class);
        $this->factoryMock = $this->createMock(\DI\FactoryInterface::class);
        $this->filtersMock = $this->createMock(Filters::class);
    }

    /**
     * @test
     */
    public function checkIntegerInSetSize1() {
        $rooms = 7;

        $bookingDataIteratorMock = BookingDataIteratorMock::get($this, [
            $this->GetBooking(
                $rooms, 1, 1,
                false, false, false, false, false,
                40.45538, -3.79278,
                Price::Empty,
                Distance::Empty, Distance::Empty, Distance::Empty),
            $this->GetBooking(
                $rooms, 2, 2,
                false, false, false, false, false,
                41.45538, -3.79278,
                Price::Empty,
                Distance::Close, Distance::Empty, Distance::Empty),
            $this->GetBooking(
                $rooms, 3, 3,
                false, false, false, false, false,
                42.45538, -3.79278,
                Price::Empty,
                Distance::Empty, Distance::Empty, Distance::Empty)]);

        $sut = new AprioriAlgorithm($bookingDataIteratorMock, $this->configMock, $this->aprioriProgressMock, $this->factoryMock, $this->filtersMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['ROOMS'=>$rooms], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
        $this->assertEquals(3, $histogramBins[0]->getTotal());
    }

    /**
     * @test
     */
    public function checkBooleansInSetSize1() {
        $tv = true;

        $bookingDataIteratorMock = BookingDataIteratorMock::get($this, [
                $this->GetBooking(
                    1, 1, 1,
                    $tv, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    2, 2, 2,
                    $tv, false, false, false, false,
                    41.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    3, 3, 3,
                    $tv, false, false, false, false,
                    42.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty)]);

        $sut = new AprioriAlgorithm($bookingDataIteratorMock, $this->configMock, $this->aprioriProgressMock, $this->factoryMock, $this->filtersMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['TV'=>$tv], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }

    /**
     * @test
     */
    public function checkPriceInSetSize1() {
        $price = Price::Budget;

        $bookingDataIteratorMock = BookingDataIteratorMock::get($this, [
                $this->GetBooking(
                    1, 1, 1,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    $price,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    2, 2, 2,
                    false, false, false, false, false,
                    41.45538, -3.79278,
                    $price,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    3, 3, 3,
                    false, false, false, false, false,
                    42.45538, -3.79278,
                    $price,
                    Distance::Empty, Distance::Empty, Distance::Empty)]);

        $sut = new AprioriAlgorithm($bookingDataIteratorMock, $this->configMock, $this->aprioriProgressMock, $this->factoryMock, $this->filtersMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['PRICE'=>$price], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }

    /**
     * @test
     */
    public function checkDistancesInSetSize1() {
        $diSea = Distance::Close;

        $bookingDataIteratorMock = BookingDataIteratorMock::get($this, [
                $this->GetBooking(
                    1, 1, 1,
                    false, false, false, false, false,
                    41.45538, -3.79278,
                    Price::Empty,
                    $diSea, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    2, 2, 2,
                    false, false, false, false, false,
                    42.45538, -3.79278,
                    Price::Empty,
                    $diSea, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    3, 3, 3,
                    false, false, false, false, false,
                    43.45538, -3.79278,
                    Price::Empty,
                    $diSea, Distance::Empty, Distance::Empty)]);

        $sut = new AprioriAlgorithm($bookingDataIteratorMock, $this->configMock, $this->aprioriProgressMock, $this->factoryMock, $this->filtersMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['SEA'=>$diSea], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
    }

    /**
     * @test
     */
    public function checkIntegerInSetSize2() {
        $bookingDataIteratorMock = BookingDataIteratorMock::get($this, [
                $this->GetBooking(
                    7, 8, 1,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    7, 8, 2,
                    false, false, false, false, false,
                    41.45538, -3.79278,
                    Price::Empty,
                    Distance::Close, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    7, 8, 3,
                    false, false, false, false, false,
                    42.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty)]);

        $sut = new AprioriAlgorithm($bookingDataIteratorMock, $this->configMock, $this->aprioriProgressMock, $this->factoryMock, $this->filtersMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(2);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['ROOMS' => 7,'BEDROOMS' => 8], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
        $this->assertEquals(3, $histogramBins[0]->getTotal());
    }

    /**
     * @test
     */
    public function checkIntegerInSetSize3() {
        $bookingDataIteratorMock = BookingDataIteratorMock::get($this, [
                $this->GetBooking(
                    7, 8, 9,
                    false, false, false, false, false,
                    40.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    7, 8, 9,
                    false, false, false, false, false,
                    41.45538, -3.79278,
                    Price::Empty,
                    Distance::Close, Distance::Empty, Distance::Empty),
                $this->GetBooking(
                    7, 8, 9,
                    false, false, false, false, false,
                    42.45538, -3.79278,
                    Price::Empty,
                    Distance::Empty, Distance::Empty, Distance::Empty)]);

        $sut = new AprioriAlgorithm($bookingDataIteratorMock, $this->configMock, $this->aprioriProgressMock, $this->factoryMock, $this->filtersMock);
        $histograms = $sut->run();

        $this->assertEquals(3, count($histograms->getAll()));

        $histogram2 = $histograms->getHistogram(2);
        $histogramBins2 = $histogram2->getHistogramBins();
        $this->assertEquals(3, count($histogramBins2));
        $this->assertEquals(['ROOMS' => 7,'BEDROOMS' => 8], $histogramBins2[0]->getFields());
        $this->assertEquals(3, $histogramBins2[0]->getCount());
        $this->assertEquals(3, $histogramBins2[0]->getTotal());
        $this->assertEquals(['ROOMS' => 7, 'STARS' => 9], $histogramBins2[1]->getFields());
        $this->assertEquals(3, $histogramBins2[1]->getCount());
        $this->assertEquals(['BEDROOMS' => 8, 'STARS' => 9], $histogramBins2[2]->getFields());
        $this->assertEquals(3, $histogramBins2[2]->getCount());

        $histogram3 = $histograms->getHistogram(3);
        $histogramBins3 = $histogram3->getHistogramBins();
        $this->assertEquals(1, count($histogramBins3));
        $this->assertEquals(['ROOMS' => 7,'BEDROOMS' => 8, 'STARS' => 9], $histogramBins3[0]->getFields());
        $this->assertEquals(3, $histogramBins3[0]->getCount());
        $this->assertEquals(3, $histogramBins3[0]->getTotal());
    }

    /**
     * @test
     */
    public function ignoreFieldConfigShouldIgnoreFields() {
        $rooms = 7;
        $price = Price::Budget;
        $ignoreFields = ['PRICE'];

        $map = [
            ['ignoreFields', $ignoreFields],
            ['apriori', [
                'minSup' => 0.6,
                'serviceStopFile' => '',
                'outputInterval' => '',
                'serviceOutput' => '',
            ]],
        ];
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->configMock->method('get')
            ->will($this->returnValueMap($map));

        $bookingDataIteratorMock = BookingDataIteratorMock::get($this, [
            $this->GetBooking(
                $rooms, 1, 1,
                false, false, false, false, false,
                40.45538, -3.79278,
                $price,
                Distance::Empty, Distance::Empty, Distance::Empty),
            $this->GetBooking(
                $rooms, 2, 2,
                false, false, false, false, false,
                41.45538, -3.79278,
                $price,
                Distance::Close, Distance::Empty, Distance::Empty),
            $this->GetBooking(
                $rooms, 3, 3,
                false, false, false, false, false,
                42.45538, -3.79278,
                $price,
                Distance::Empty, Distance::Empty, Distance::Empty)]);

        $sut = new AprioriAlgorithm($bookingDataIteratorMock, $this->configMock, $this->aprioriProgressMock, $this->factoryMock, $this->filtersMock);
        $histograms = $sut->run();
        $histogram = $histograms->getHistogram(1);
        $histogramBins = $histogram->getHistogramBins();

        $this->assertEquals(1, count($histogramBins));
        $this->assertEquals(['ROOMS'=>$rooms], $histogramBins[0]->getFields());
        $this->assertEquals(3, $histogramBins[0]->getCount());
        $this->assertEquals(3, $histogramBins[0]->getTotal());
    }
}
