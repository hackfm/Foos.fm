<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('foos.class.php');

class TestFoosTable extends UnitTestCase {

    function testSort1() {
        $table = new FoosTable(dirname(__FILE__) . '/testData/');
        $table->loadCurrentStatus();
        $table->calculateScore();
        $table->sortPlayers();
        //print_r($table->getPlayers());
        $this->assertEqual($table->getPlayerAtPosition(1), $table->getPlayerByName('coffey'));
        $this->assertEqual($table->getPositionOfPlayer('samuel'), 3);
        $this->assertEqual($table->getPlayerByName('marek')->getGames(), 11);
    }

    function testSave() {
        $table = new FoosTable(dirname(__FILE__) . '/testData/');
        $table->loadCurrentStatus();
        $table->calculateScore();
        $table->sortPlayers();
        $table->saveToFile('games2.json');

        $file = file_get_contents(dirname(__FILE__) . '/testData/games.json');
        $file2 = file_get_contents(dirname(__FILE__) . '/testData/games2.json');
        $this->assertEqual($file, $file2);
    }

    function testLostAgainst() {
        $table = new FoosTable(dirname(__FILE__) . '/testData/');
        $table->loadCurrentStatus();
        $table->calculateScore();
        $table->sortPlayers();
        $lostAgainst = $table->getPlayerByName('marek')->getLostAgainstList();
        $this->assertEqual($lostAgainst['COFFEY']['count'], 2);
        $this->assertEqual($lostAgainst['COFFEY']['player'], $table->getPlayerByName('coffey'));
        $this->assertEqual(count($lostAgainst), 6);
        
        $nemesis = $table->getPlayerByName('michaelhoran')->getNemesis();
        $this->assertEqual($nemesis['count'], 2);
        $this->assertEqual($nemesis['player'], $table->getPlayerByName('jing'));
    }

    function testLastTimestamp() {
        $table = new FoosTable(dirname(__FILE__) . '/testData/');
        $table->loadCurrentStatus();
        $table->calculateScore();
        $table->sortPlayers();
        $this->assertEqual($table->getPlayerByName('marek')->getLastTimestamp(), 1328033408);
    }

    function testDeleteMatch() {
        $table = new FoosTable(dirname(__FILE__) . '/testData/');
        $table->loadCurrentStatus();
        $matches = $table->getMatches();
        $match = $matches[3];
        $this->assertEqual($table->getNumberOfMatches(), 37);
        $this->assertTrue($table->deleteMatch($match->getTimestamp()));
        $this->assertEqual($table->getNumberOfMatches(), 36);
        $this->assertFalse($table->deleteMatch($match->getTimestamp()));
        $this->assertEqual($table->getNumberOfMatches(), 36);
    }
    
}