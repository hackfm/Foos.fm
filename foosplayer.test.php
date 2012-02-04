<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('foos.class.php');

class TestFoosPlayer extends UnitTestCase {

    function testInit() {
        $player = new FoosPlayer('Name', 1000);
    	$this->assertEqual($player->getName(), 'Name');
    	$this->assertEqual($player->getStrength(), 1000);
    }

    function testNormalize() {
        $player1 = new FoosPlayer('name',1);
	    $player2 = new FoosPlayer('NaMe',1);
	    $this->assertEqual($player1->getNormalizedName(), $player2->getNormalizedName());
    }

    function testMatchName() {
        $player = new FoosPlayer('Name', 1);
	    $this->assertTrue($player->matchesName('name'));
	    $this->assertFalse($player->matchesName('hamster'));
    }
}
