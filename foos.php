<?php
   //** Model **//
   require_once('foos.class.php');

   //** Controller **//
   $table = new FoosTable('/home/marek/foosdata/');
   $table->setLogMaxSize(30);
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

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Game #');
<?php
    foreach ($table->getPlayers() as $player) {
        echo "data.addColumn('number', '".$player->getName()."');";
    }

    echo "data.addRows([";
    $i = 1;
    foreach ($table->getLog() as $logEntry) {
        echo "['".$i++."'";
            foreach ($table->getPlayers() as $player) {
                echo ",".round($logEntry[$player->getNormalizedName()]);
            }
        echo "],";
    }
    echo "]);";
?>

        var options = {
          width: "100%", height: 400,
          theme: 'maximized'
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>

    <div id="chart_div"></div>

    <h1>History</h1>

    <table>
        <tr>
            <th>#</th>
            <th>Winner</th>
            <th></th>
            <th>Looser</th>
            <th></th>
            <th>Time</th>
            <th>Action</th>
        </tr>    


<!-- LOG -->
<?php
    $i = 1;
    foreach (array_reverse($table->getMatches()) as $match) {
        echo "<tr>";
        echo "<td>".$i++."</td>";
        echo "<td>".$match->getPlayer1()->getName()."</td>";
        echo "<td>".$match->getScore1(). "</td>";
        echo "<td>".$match->getPlayer2()->getName()."</td>";
        echo "<td>".$match->getScore2()."</td>";
        echo "<td>".date('d F Y g:i A', $match->getTimestamp())."</td>";
        echo "<td><a href=\"confirm.php?delete=".$match->getTimestamp()."\">Delete</a></td>";

        echo "</tr>";
    }
?>
    
    <!--<script src="js/scripts.js"></script>-->

    </table>
</body>
</html>


   

