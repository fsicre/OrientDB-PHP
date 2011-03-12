<?php

require_once 'OrientDB/OrientDB.php';
require_once 'OrientDBBaseTest.php';

class OrientDBDataclusterCountTest extends OrientDBBaseTesting
{

    protected function setUp() {
        $this->db = new OrientDB('localhost', 2424);
    }

    protected function tearDown() {
        $this->db = null;
    }

    public function testDataclusteCountOnNotConnectedDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterCount(array());
    }

    public function testDataclusteCountOnConnectedDB() {
        $this->db->connect('root', $this->root_password);
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterCount(array());
    }

    public function testDataclusteCountOnNotOpenDB() {
        $this->setExpectedException('OrientDBWrongCommandException');
        $result = $this->db->dataclusterCount(array());
    }

    public function testDataclusteCountOnOpenDB() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result = $this->db->dataclusterCount(array());
        $this->assertInternalType('integer', $result);
        $this->assertEquals(0, $result);
    }

    public function testDataclusteCountWithWrongParamCount() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterCount();
    }

    public function testDataclusteCountWithWrongParamType() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBWrongParamsException');
        $result = $this->db->dataclusterCount('string');
    }

    public function testDataclusteCountOnClusterNotExist() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $this->setExpectedException('OrientDBException');
        $result = $this->db->dataclusterCount(array(10000));
    }

    public function testDataclusteCountOnManyClusters() {
        $this->db->DBOpen('demo', 'writer', 'writer');
        $result1 = $this->db->dataclusterCount(array(1));
        $result2 = $this->db->dataclusterCount(array(2));
        $result = $this->db->dataclusterCount(array(1, 2));
        $this->assertEquals($result1 + $result2, $result);
    }
}