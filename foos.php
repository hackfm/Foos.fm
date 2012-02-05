<?php
   //** Model **//
   require_once('foos.class.php');

   //** Controller **//
   $table = new FoosTable('/home/marek/foosdata/');
   $table->loadCurrentStatus();
   $table->calculateScore();

   $tableOld = new FoosTable('/home/marek/foosdata/');
   $tableOld->loadStatusForTime(time() - 48 * 60 * 60);
   $tableOld->calculateScore();


   //** View **//
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Foos.fm</title>
  <meta name="description" content="Foos.fm">
  <link rel="stylesheet" href="style.css">
</head>
<body>

    <table> 
        <tr>
            <th>48h</th>
            <th>Position</th>
            <th>Name</th>
            <th>Strength</th>
            <th>Games</th>
            <th>Nemesis</th>
        </tr>
<?php

    $i = 1;

    /** 
     * Columns:
     * - Change in last 48h
     * - Position
     * - Name
     * - Strength
     * - Number of Games played
     * - Nemesis
     */

    foreach ($table->getPlayers() as $player) {

    
        echo "<tr>";

        // Change in the last 48h
        echo "<td>";
        $changePos = $tableOld->getPositionOfPlayer($player->getName());
        if (!$changePos) {
            echo "NEW!";
        } else {
            $changePos = $changePos - $i;
            if ($changePos > 0) {
                echo "+$changePos";
            }
            elseif ($changePos < 0) {
                echo "$changePos";
            } else {
                echo "<=>";
            }
        }
        echo "</td>";
        
        // Position   
        echo "<td>$i.</td>";  
        
        // Name
        echo "<td>".$player->getName()."</td>";

        // Strength
        echo "<td>".$player->getRoundedStrength()."</td>";

        // Number of Games played
        echo "<td>".$player->getGames()."</td>";

        // Nemesis
        echo "<td>";
        $nemesis = $player->getNemesis();
        if ($nemesis) {
            echo $nemesis['player']->getName()." (+".$nemesis['count']." wins)";
        } else {
            echo "";
        }
        echo "</td>";

        // Increase counter
        $i++;
       
   }


?>
    </table>

<!-- LOG -->
<?php
   $i = 1;
   
   echo "</ol><h2>Log</h2><ol>";

   foreach (array_reverse($table->getMatches()) as $match) {
       echo "<li>";
       echo $match->getPlayer1()->getName()." (".$match->getScore1(). ") ";
       echo $match->getPlayer2()->getName()." (".$match->getScore2().") ";
       echo " <small>".date('d F Y g:i A', $match->getTimestamp())."</small></li>\n";
   }
?>
    
    <!--<script src="js/scripts.js"></script>-->
</body>
</html>


   

