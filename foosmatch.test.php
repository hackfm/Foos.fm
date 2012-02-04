<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('foos.class.php');

class TestFoosMatch extends UnitTestCase {

    function testSort1() {
        $player1 = new FoosPlayer('player1', 1000);
        $player2 = new FoosPlayer('player2', 1000);
        $match = new FoosMatch($player1, 2, $player2, 1);
        $this->assertEqual($match->getPlayer1(), $player1);
        $this->assertEqual($match->getPlayer2(), $player2);
        $this->assertEqual($match->getScore1(), 2);
        $this->assertEqual($match->getScore2(), 1);
    }

    function testSort2() {
        $player1 = new FoosPlayer('player1', 1000);
        $player2 = new FoosPlayer('player2', 1000);
        $match = new FoosMatch($player1, 1, $player2, 2);
        $this->assertEqual($match->getPlayer1(), $player2);
        $this->assertEqual($match->getPlayer2(), $player1);
        $this->assertEqual($match->getScore1(), 2);
        $this->assertEqual($match->getScore2(), 1);
    }

    function testSortDraw() {
        $player1 = new FoosPlayer('player1', 1000);
        $player2 = new FoosPlayer('player2', 1000);
        $match = new FoosMatch($player1, 2, $player2, 2);
        $this->assertEqual($match->getPlayer1(), $player1);
        $this->assertEqual($match->getPlayer2(), $player2);
        $this->assertEqual($match->getScore1(), 2);
        $this->assertEqual($match->getScore2(), 2);
    }

    function testPassingByReference() {
        $player1 = new FoosPlayer('player1', 1000);
        $player2 = new FoosPlayer('player2', 1000);
        $match = new FoosMatch($player1, 2, $player2, 1);
        $player1->setStrength(1);
        $this->assertEqual($match->getPlayer1()->getStrength(), 1);
    }
    
    function testCalculateScoreDraw() {
        $player1 = new FoosPlayer('player1', 1000);
        $player2 = new FoosPlayer('player2', 1000);
        $match = new FoosMatch($player1, 2, $player2, 2);
        $match->calculateScore();
        $this->assertEqual($player1->getStrength(), 1000);
        $this->assertEqual($player2->getStrength(), 1000);
    }

    function testCalculateScore1() {
        $player1 = new FoosPlayer('player1', 1000);
        $player2 = new FoosPlayer('player2', 1000);
        $match = new FoosMatch($player1, 2, $player2, 1);
        $match->calculateScore();
        $this->assertEqual($player1->getStrength(), 1100);
        $this->assertEqual($player2->getStrength(), 900);
    }

    function testCalculateScore2() {
        $player1 = new FoosPlayer('player1', 1000);
        $player2 = new FoosPlayer('player2', 1000);
        $match = new FoosMatch($player1, 1, $player2, 2);
        $match->calculateScore();
        $this->assertEqual($player2->getStrength(), 1100);
        $this->assertEqual($player1->getStrength(), 900);
    }



    
}