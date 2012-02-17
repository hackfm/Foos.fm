<?php
    //** Model **//
    require_once('foos.class.php');

    //** Constants **//
    $workingPath = '/userhome/marek/foos/';

    //** Controller **//

    // Delete
    if (isset($_GET['delete'])) {
        $timestamp = $_GET['delete'] * 1;
        $tmpTable = new FoosTable($workingPath);
        $tmpTable->loadCurrentStatus();
        $tmpTable->deleteMatch($timestamp);
        $tmpTable->saveToFile();

        // Remove ?delete=xxx part from URL
        header('Location: foos.php');
        die();
    }

    // Current data
    $table = new FoosTable($workingPath);
    $table->setLogMaxSize(30);
    $table->loadCurrentStatus();
    $table->calculateScore();

    // Table with data 48h old (to display changes)
    $tableOld = new FoosTable($workingPath);
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
    <link rel="icon" href="favicon.ico" type="image/x-icon">  
</head>
<body>

    <div id="player_chart"></div>
    <div id="log_chart"></div>

    <h1>History</h1>

    <table>
        <tr>
            <th>#</th>
            <th>Winner</th>
            <th></th>
            <th>Loser</th>
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


    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        google.load("visualization", "1", {packages:['corechart','table']});
        google.setOnLoadCallback(drawChart);

        function drawChart() {
            // Player table
            var playerTable = new google.visualization.DataTable();
            playerTable.addColumn('number', '48h');
            playerTable.addColumn('number', 'Position');
            playerTable.addColumn('string', 'Name');
            playerTable.addColumn('number', 'Strength');
            playerTable.addColumn('number', 'Games');
            playerTable.addColumn('string', 'Nemesis');
            <?php
                $minStrength = null;
                $i = 0;
                foreach ($table->getPlayers() as $player) {
                    echo "playerTable.addRows(1);\n";

                    $changePos = $tableOld->getPositionOfPlayer($player->getName());
                    if (!$changePos) {
                        echo "playerTable.setCell($i, 0, 0);\n";
                    } else {
                        $changePos = $changePos - $i - 1;
                        echo "playerTable.setCell($i, 0, ".$changePos.");\n";
                    }
                    echo "playerTable.setCell($i, 1, ".($i + 1).");\n";
                    echo "playerTable.setCell($i, 2, '".$player->getName()."');\n";
                    echo "playerTable.setCell($i, 3, ".$player->getRoundedStrength().");\n";
                    echo "playerTable.setCell($i, 4, ".$player->getGames().");\n";
                    $nemesis = $player->getNemesis();
                    if ($nemesis) {
                        echo "playerTable.setCell($i, 5, '".$nemesis['player']->getName()." (+".$nemesis['count']." wins)');\n";
                    } else {
                        echo "playerTable.setCell($i, 5, '');\n";
                    }

                    if ($minStrength === null) {
                        $minStrength = $player->getRoundedStrength();
                    }
                    else
                    {
                        $minStrength = min($minStrength, $player->getRoundedStrength());
                    }
                    
                    
                    $i++;
                }
            ?>

            // Arrow formater for first column
            var formatter = new google.visualization.ArrowFormat();
            formatter.format(playerTable, 0); 

            // Bar formater for third column
            var formatter = new google.visualization.BarFormat({
                width: 400,
                base: <?php echo FoosTable::DEFAULT_STRENGTH; ?>,
                min: <?php echo $minStrength; ?>
            });
            formatter.format(playerTable, 3);

            var playerOptions = {
                allowHtml: true,
                showRowNumber: false
            };

            var playerChart = new google.visualization.Table(document.getElementById('player_chart'));
            playerChart.draw(playerTable, playerOptions);

            // Log data
            var logTable = new google.visualization.DataTable();
            logTable.addColumn('string', 'Game #');

            <?php
                foreach ($table->getPlayers() as $player) {
                    echo "logTable.addColumn('number', '".$player->getName()."');\n";
                }

                echo "logTable.addRows([";
                $i = 1;
                $matches = $table->getMatches();
                    
                foreach ($table->getLog() as $logEntry) {
                    $match = $matches[count($matches) - 31 + $i];
                    echo "['".
                            $match->getPlayer1()->getName().
                            " vs. ".
                            $match->getPlayer2()->getName().
                            " (".
                            date('d F Y g:i A', $match->getTimestamp()).
                            ")".
                          "'";
                        foreach ($table->getPlayers() as $player) {
                            echo ",".round($logEntry[$player->getNormalizedName()]);
                        }
                    echo "],\n";
                    $i++;
                }
                echo "]);";
            ?>

            var logOptions = {
                width: "100%", height: 400,
                theme: 'maximized',
                hAxis: {
                    textPosition: 'none',
                }
            };

            var logChart = new google.visualization.LineChart(document.getElementById('log_chart'));
            logChart.draw(logTable, logOptions);
        }

    </script>  
</body>
</html>


   

