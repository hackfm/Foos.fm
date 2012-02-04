<?php
   require_once('foos.class.php');

   $table = new FoosTable('/userhome/samuel/');
   $table->loadCurrentStatus();
   $table->calculateScore();
   $table->sortPlayers();

   $tableOld = new FoosTable('/userhome/samuel/');
   $tableOld->loadStatusForTime(time() - 48 * 60 * 60);
   $tableOld->calculateScore();
   $tableOld->sortPlayers();

   echo "<h1>Foosball</h1><ol>";

   $i = 1;
   foreach ($table->getPlayers() as $player) {
       if ($player) {
           echo "<li><strong>".$player->getName()."</strong> ".round($player->getStrength())." in ".$player->getGames()." games ";
           $nemesis = $player->getNemesis();
           if ($nemesis) {
               $nemesis = $table->getPlayerByName($nemesis);
	       echo " (Nemesis: ".$nemesis->getName().") ";
	   }

           $changePos = $tableOld->getPositionOfPlayer($player->getName());
           if (!$changePos) {
		echo "<small>NEW!</small></li>\n";
           }
           else
           {
                $changePos = $changePos - $i;
		if ($changePos > 0) {
	  	    echo "<small>$changePos positions improved in the last 48h</small></li>\n";
		}
                elseif ($changePos < 0) {
		    $changePos *= -1;
                    echo "<small>$changePos positions lost in the last 48h</small></li>\n";
		}
                else 
                {
		    echo "<small>No position change in the last 48h</small></li>\n";
                }
           }
           $i++;
       }
   }

   echo "</ol><h2>Log</h2><ol>";

   foreach ($table->getMatches() as $match) {
       echo "<li>";
       echo $match->getPlayer1()->getName()." (".$match->getScore1(). ") ";
       echo $match->getPlayer2()->getName()." (".$match->getScore2().") ";
       echo " <small>".date('d F Y g:i A', $match->getTimestamp())."</small></li>\n";
   }

