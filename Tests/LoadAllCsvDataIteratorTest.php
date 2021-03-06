<?php
require_once dirname(__DIR__) . "/Interfaces/DataIterator.php";
require_once dirname(__DIR__) . "/Utilities/Iterators/LoadAllCsvDataIterator.php";

use \PHPUnit\Framework\TestCase,
    org\bovigo\vfs\vfsStream;

class LoadAllCsvDataIteratorTest extends TestCase
{
    private $testfile;
    private $configMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(ConfigProvider::class);


        // Creating mock data file with vfs (virtual file system).
        vfsStream::setup('home');
        $this->testfile = vfsStream::url('home/test.csv');
        file_put_contents($this->testfile, 'first;second;third
1;2;3
2;3;4');
    }

    /**
     * @test
     */
    public function validFileShouldBeOpened() {
        new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $this->assertTrue(true);
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function invalidFileShouldThrowException() {
        new LoadAllCsvDataIterator($this->configMock, "invalidFile");
    }

    /**
     * @test
     */
    public function gettingFirstLineShouldReturnCorrectData() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function gettingSecondLineShouldReturnCorrectData() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function gettingThirdLineShouldReturnFalse() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $sut->next();
        $line = $sut->current();
        $this->assertFalse($line);
    }

    /**
     * @test
     */
    public function validFieldOnFirstLineShouldReturnTrue() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForSecondLineShouldReturnTrue() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $valid = $sut->valid();
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function validFieldForThirdLineShouldReturnFalse() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $sut->next();
        $valid = $sut->valid();
        $this->assertFalse($valid);
    }

    /**
     * @test
     */
    public function afterARewindItShouldReturnFirstLine() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $sut->rewind();
        $line = $sut->current();
        $this->assertEquals(array('first' => '1','second' => '2','third' => '3'), $line);
    }

    /**
     * @test
     */
    public function afterARewindAndANextItShouldReturnTheSecondElement() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);
        $sut->next();
        $sut->next();
        $sut->rewind();
        $sut->next();
        $line = $sut->current();
        $this->assertEquals(array('first' => '2','second' => '3','third' => '4'), $line);
    }

    /**
     * @test
     */
    public function providingDiffernentDeliminiterShouldReturnSingleString() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile, ',');
        $line = $sut->current();
        $this->assertEquals(array('first;second;third' => '1;2;3'), $line);
    }

    /**
     * @test
     */
    public function loopingThroughShouldReturnAllElements() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);

        $result = [];
        foreach($sut as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals([
            1 => ['first' => '1','second' => '2','third' => '3'],
            2 => ['first' => '2','second' => '3','third' => '4'],
        ], $result);
    }

    /**
     * @test
     */
    public function gettingCountShouldReturn2() {
        $sut = new LoadAllCsvDataIterator($this->configMock, $this->testfile);

        $bookingsCount = $sut->count();

        $this->assertEquals(2, $bookingsCount);
    }
}
