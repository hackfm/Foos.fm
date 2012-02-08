<?php
    /**
     * A way to test a IRCcat script without IRCcat
     */

    // Emulate $toks
    $toks = explode('_', $_SERVER['QUERY_STRING']);
    if ($toks[0] == '') {
        $toks = array();
    }

    // Beginning
    require_once('foos.class.php');


    $workingPath = '/userhome/marek/foos/';
    $showHelp = true;
    
    // Usecase 1: ?foos
    if (count($toks) == 0) {
        $table = new FoosTable($workingPath);
        $table->loadCurrentStatus();
        $table->calculateScore();
        printFoosTable($table->getPlayers());

        $showHelp = false;
    } 

    // Usecase 2: ?foos player1 player2
    if (count($toks) == 2) {
        $table = new FoosTable($workingPath);
        $table->loadCurrentStatus();
        $table->calculateScore();
        $player1 = $table->getPlayerByName($toks[0]);
        $player2 = $table->getPlayerByName($toks[1]);
        echo $player1->getName().' => Chance: '.number_format($player1->getChancesToWinAgainst($player2)*100, 2).'%,'.
            ' Win: '.round($player1->getStrengthDeltaAfterGame($player2, 1))."\n";
        echo $player2->getName().' => Chance: '.number_format($player2->getChancesToWinAgainst($player1)*100, 2).'%,'.
            ' Win: '.round($player2->getStrengthDeltaAfterGame($player1, 1))."";
        
        $showHelp = false;
    }

    
    if (count($toks) == 4) {

        // Usecase 3: ?foos player1 score player2 score
        if (is_numeric($toks[1]) && is_numeric($toks[3])) {
            $tableOld = new FoosTable($workingPath);
            $tableOld->loadCurrentStatus();
            $tableOld->calculateScore();

            $table = new FoosTable($workingPath);
            $table->loadCurrentStatus();

            $player1 = $table->getPlayerByName($toks[0]);
            $player2 = $table->getPlayerByName($toks[2]);
            $player1Old = $tableOld->getPlayerByName($toks[0]);
            $player2Old = $tableOld->getPlayerByName($toks[2]);
            $tableOld->sortPlayers();

            $match = new FoosMatch($player1, $toks[1], $player2, $toks[3]);
            $table->addMatch($match);

            echo "Game ".$table->getNumberOfMatches().": ".$match->getPlayer1()->getName()." beat ".$match->getPlayer2()->getName()."\n";

            $table->calculateScore();

            echo $player1->getName().' => '.
                'from #'.$tableOld->getPositionOfPlayer($toks[0])." (".$player1Old->getRoundedStrength().") ".
                "to #".$table->getPositionOfPlayer($toks[0])." (".$player1->getRoundedStrength().")\n";
            echo $player2->getName().' => '.
                'from #'.$tableOld->getPositionOfPlayer($toks[2])." (".$player2Old->getRoundedStrength().") ".
                "to #".$table->getPositionOfPlayer($toks[2])." (".$player2->getRoundedStrength().")\n";

            $table->saveToFile();    
        } else {  // Usecase 4: ?foos player1 player2 player3 player4
            $table = new FoosTable($workingPath);
            $table->loadCurrentStatus();
            $table->calculateScore();
            $player1 = $table->getPlayerByName($toks[0]);
            $player2 = $table->getPlayerByName($toks[1]);
            $team1   = new FoosTeam($player1, $player2);
            $player3 = $table->getPlayerByName($toks[2]);
            $player4 = $table->getPlayerByName($toks[3]);
            $team2   = new FoosTeam($player3, $player4);
            echo $team1->getName().' => Chance: '.number_format($team1->getChancesToWinAgainst($team2)*100, 2).'%,'.
                ' Win: '.round($team1->getStrengthDeltaAfterGame($team2, 1))."\n";
            echo $team2->getName().' => Chance: '.number_format($team2->getChancesToWinAgainst($team1)*100, 2).'%,'.
                ' Win: '.round($team2->getStrengthDeltaAfterGame($team1, 1))."";
        }

        $showHelp = false;
    }

    // Usecase 5: ?foos player1 player2 score1 player3 player4 score2
    if ((count($toks)) == 6 && is_numeric($toks[2]) && is_numeric($toks[5])) {
        $tableOld = new FoosTable($workingPath);
        $tableOld->loadCurrentStatus();
        $tableOld->calculateScore();

        $table = new FoosTable($workingPath);
        $table->loadCurrentStatus();

        $player1 = $table->getPlayerByName($toks[0]);
        $player2 = $table->getPlayerByName($toks[1]);
        $team1   = new FoosTeam($player1, $player2);
        $player3 = $table->getPlayerByName($toks[3]);
        $player4 = $table->getPlayerByName($toks[4]);
        $team2   = new FoosTeam($player3, $player4);
        $player1Old = $tableOld->getPlayerByName($toks[0]);
        $player2Old = $tableOld->getPlayerByName($toks[1]);
        $player3Old = $tableOld->getPlayerByName($toks[3]);
        $player4Old = $tableOld->getPlayerByName($toks[4]);
        $tableOld->sortPlayers();

        $match = new FoosMatch($team1, $toks[2], $team2, $toks[5]);
        $table->addMatch($match);

        echo "Game ".$table->getNumberOfMatches().": ".$match->getPlayer1()->getName()." beat ".$match->getPlayer2()->getName()."\n";

        $table->calculateScore();

        echo $player1->getName().' => '.
            'from #'.$tableOld->getPositionOfPlayer($toks[0])." (".$player1Old->getRoundedStrength().") ".
            "to #".$table->getPositionOfPlayer($toks[0])." (".$player1->getRoundedStrength().")\n";
        echo $player2->getName().' => '.
            'from #'.$tableOld->getPositionOfPlayer($toks[1])." (".$player2Old->getRoundedStrength().") ".
            "to #".$table->getPositionOfPlayer($toks[1])." (".$player2->getRoundedStrength().")\n";
        echo $player3->getName().' => '.
            'from #'.$tableOld->getPositionOfPlayer($toks[3])." (".$player3Old->getRoundedStrength().") ".
            "to #".$table->getPositionOfPlayer($toks[3])." (".$player3->getRoundedStrength().")\n";
        echo $player4->getName().' => '.
            'from #'.$tableOld->getPositionOfPlayer($toks[4])." (".$player4Old->getRoundedStrength().") ".
            "to #".$table->getPositionOfPlayer($toks[4])." (".$player4->getRoundedStrength().")\n";        

        $table->saveToFile();    
        $showHelp = false;
    }

    // Display help message
    if ($showHelp) {
        echo "How it works:\n". 
            "Current table: foos\n".
            "Chances of winning: foos Samuel Coffey\n".
            "Log a game: foos Samuel 2 Coffey 0\n";
    }

function printFoosTable($players)
{
    $maxNameLength = 8;
    $numPlayers = count( $players );        
    $numRows = ceil( sqrt( 2 * $numPlayers + 0.25 ) - 0.5 );;
    $numSlotsLastRow = $numRows;
    $lastRowWidth = $numSlotsLastRow * ( $maxNameLength + 6 + 1 ) - 1;
    $numPerLine = 1;
    while( count( $players ) > 0 )
    {
        $xpos = ( $lastRowWidth / 2 ) - ( $numPerLine * ( $maxNameLength + 6 + 1  ) / 2 );
        $space = "";
        $space = str_pad( $space, $xpos, " ", STR_PAD_LEFT );
        print( $space );
        for ( $i = 0; $i < $numPerLine; $i++ )
        {
            $player = array_shift( $players );
            if ($player instanceof FoosPlayer)  {
                $name = ucfirst($player->getName());
                $name = substr( $name, 0, $maxNameLength );
                $name = str_pad( $name, $maxNameLength, " ", STR_PAD_BOTH );
                print( $name ) . "(".$player->getRoundedStrength().") ";
            }
        }
        print( "\n" );
        $numPerLine++;    
    }
}