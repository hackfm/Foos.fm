<?php 

class FoosPlayer {
    private $name;
    private $normalizedName;
    private $strength;
    private $games = 0;
    private $lastTimestamp;
    private $table;

    private $lostAgainst = array();
    private $wonAgainst = array();

    public function FoosPlayer ($name, $strength, FoosTable $table) {
        $this->name = $name;
        $this->normalizedName = FoosPlayer::normalizeName($name);
        $this->strength = $strength;
        $this->table = $table;
    }

    public function getName() {
        return $this->name;
    }

    public function getUppercaseName() {
        return $this->uppercaseName;
    }

    public static function normalizeName($name) {
        return strtoupper($name);
    }

    public function getNormalizedName() {
        return $this->normalizedName;
    }

    public function getGames() {
        return $this->games;
    }

    public function incGames($timestamp) {
        $this->games++;
        $this->lastTimestamp = $timestamp;
    }

    public function addStrength($addStrength) {
        $this->strength += $addStrength;
    }

    public function getStrength() {
        return $this->strength;
    }

    public function setStrength($strength) {
        $this->strength = $strength;
    }
    
    public function matchesName($name) {
    	return $this->normalizedName == FoosPlayer::normalizeName($name);
    }

    public function getLastTimestamp() {
        return $this->lastTimestamp;
    }

    public function loseAgainst(FoosPlayer $opponent) {
        $OppNormalizedName = $opponent->getNormalizedName();
        if (isset($this->lostAgainst[$OppNormalizedName])) {
            $this->lostAgainst[$OppNormalizedName]++;
        }
        else
        {
            $this->lostAgainst[$OppNormalizedName] = 1;
        }
    } 

    public function winAgainst(FoosPlayer $opponent) {
        $OppNormalizedName = $opponent->getNormalizedName();
        if (isset($this->wonAgainst[$OppNormalizedName])) {
            $this->wonAgainst[$OppNormalizedName]++;
        }
        else
        {
            $this->wonAgainst[$OppNormalizedName] = 1;
        }
    } 

    public function getLostAgainstList() {
        arsort($this->lostAgainst);
        return $this->lostAgainst;
    }

    public function getWonAgainstList() {
        arsort($this->wonAgainst);
        return $this->wonAgainst;
    }

    public function getNemesis() {
        $list = $this->getLostAgainstList();
        $values = array_keys($list);
        if (count($values) == 0) {
            return false;
        }
        return $values[0];
    }

    private function 

    public function getChancesToWinAgains(FoosPlayer $player) {
      
    }
}

/** 
 * If match is not drawn, player1 is always the winner
 */
class FoosMatch {
    private $player1;
    private $score1;
    private $player2;
    private $score2;
    private $timestamp;
    private $table;

    public function FoosMatch(FoosPlayer $player1, $score1, FoosPlayer $player2, $score2, FoosTable $table) {
        if ($score1>=$score2) {
            $this->player1 = $player1;  
            $this->player2 = $player2;  
        }
        else
        {
            $this->player1 = $player2;  
            $this->player2 = $player1; 
        }

    	$this->score1   = max($score1, $score2);
    	$this->score2   = min($score1, $score2);

        $this->timestamp = time();
        $this->table = $table;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    public function getPlayer1() {
        return $this->player1;
    }

    public function getPlayer2() {
        return $this->player2;
    }

    public function getScore1() {
        return $this->score1;
    }	

    public function getScore2() {
        return $this->score2;
    }
    
    public function isDraw() {
        return $this->score1 == $this->score2;
    }

    public function calculateScore($additionalInfo = true) {
        $q1 = pow(10, $this->player1->getStrength() / FoosTable::RELATIVE_STRENGTH_NORMALISATION);
        $q2 = pow(10, $this->player2->getStrength() / FoosTable::RELATIVE_STRENGTH_NORMALISATION);

        $expected1 = $q1 / ($q1 + $q2);
        $expected2 = $q2 / ($q1 + $q2);

        $this->player1->addStrength(
            FoosTable::K * (($this->isDraw()?0.5:1) - $expected1)
        );
        $this->player2->addStrength(
            FoosTable::K * (($this->isDraw()?0.5:0) - $expected2)
        );

        $this->player1->incGames($this->timestamp);
        $this->player2->incGames($this->timestamp);

        if (!$this->isDraw() && $additionalInfo) {
            $this->player1->winAgainst($this->player2);
            $this->player2->loseAgainst($this->player1);
        }
    }

    public function asArray() {
        return array(
            'timestamp' => $this->timestamp,
            'result'    => array(
                $this->player1->getName() => $this->score1,
                $this->player2->getName() => $this->score2,
            ),
        );
    }
}



class FoosTable {
    const K = 200;   
    const RELATIVE_STRENGTH_NORMALISATION = 500;   
    const DEFAULT_STRENGTH = 1000;  

    private $gameFolder = '/userhome/marek/foos/'; // Has to end with a trailing slash!
    //const TABLE_FILE    = 'table.json';
    const GAMES_FILE    = 'games.json';
    
    private $players = array();
    private $matches = array();

    public function FoosTable($gameFolder = null) {
        if ($gameFolder != null) {
            $this->gameFolder = $gameFolder;
        }
    }

    public function loadCurrentStatus() {
        $this->loadGamesFromFile(self::GAMES_FILE);
    }  

    public function loadStatusForTime($timestamp) {
        $this->loadGamesFromFile(self::GAMES_FILE, $timestamp);
    }  

    public function loadGamesFromFile($gamesFile, $timestamp = null) {
        if(!$timestamp) {
            $timestamp = time();
        }

        $this->matches = array();
        $gamesRaw    = file_get_contents($this->gameFolder.$gamesFile);
        if ($gamesRaw) { 
            $gamesJson = json_decode($gamesRaw, true);
            foreach ($gamesJson as $gameJson) {   
                if ($timestamp > $gameJson['timestamp']) {
                    $result = $gameJson['result'];

                    $players = array_keys($result);
                    $scores  = array_values($result);

                    $player1 = $this->getPlayerByName($players[0]);
                    $player2 = $this->getPlayerByName($players[1]);

                    $match = new FoosMatch($player1, $scores[0], $player2, $scores[1]);
                    $match->setTimestamp($gameJson['timestamp']);

                    $this->addMatch($match);

                }
            }

            return true;
        }
        else 
        {
            return false;
        }   
    }

    public function saveToFile($gamesFile = null) {
        if(!$gamesFile) {
            $gamesFile = self::GAMES_FILE; 
        }

        $fileName = $this->gameFolder.$gamesFile;
        $json = array();

        foreach ($this->matches as $match) {
            $json[] = $match->asArray();
        }

        file_put_contents($fileName, json_encode($json));
    }

    public function addMatch(FoosMatch $match) {
        $this->matches[] = $match;
    }

    public function getPlayerByName($name) {
        // Do we know this player?
        $normalizedName = FoosPlayer::normalizeName($name);
        if (isset($this->players[$normalizedName])) {
            return $this->players[$normalizedName];
        }

        // No? Create a new one
        $player = new FoosPlayer($name, FoosTable::DEFAULT_STRENGTH);
        $this->players[$normalizedName] = $player;
        return $player;
    }

    public function calculateScore() {
        foreach($this->matches as $match) {
            $match->calculateScore();
        }
    }

    public function sortPlayers() {
        uasort($this->players, 'sortPlayer');
    }

    public function getPlayers() {
        return $this->players;
    }

    /**
     * @param pos integer Starts with 1!
     */
    public function getPlayerAtPosition($pos) {
        $playersValues = array_values($this->players);
        return $playersValues[$pos-1];
    }

    public function getNumberOfPlayers() {
        return count($this->players);
    }

    public function getMatches() {
        return $this->matches;
    }

    /**
     * Make sure to call sortPlayers first!
     */
    public function getPositionOfPlayer($name) {
        $normalizedName = FoosPlayer::normalizeName($name);
        $i = 1;
        foreach($this->players as $key => $player) {
            if($key == $normalizedName) {
                return $i;
            }
            $i++;
        }
        return false;
    }

    /*** HELPER ***/

    public function getDayTextForTimestamp($timestamp) {
        return date("Y-m-d", $timestamp);
    }

    public function timestampIsToday($timestamp) {
        return getDayTextForTimestamp(time()) == getDayTextForTimestamp(time());
    }
/*

  
          
  
          // Printing table?
          if ( ! $toks) {
              $i = 1;
  
              foreach ($table as $player => $strength) {
                  echo "$i: $player (" . intval($strength) . ")\n";
  
                  ++$i;
              }
  
              break;
          }
  
          // Scoring?
          if (count($toks) != 4) {
              echo "Example usage: foos Samuel 11 Coffey 15";
  
              break;
          }
  
          list($player1, $score1, $player2, $score2) = $toks;
  
          if ( ! is_numeric($score1) || ! is_numeric($score2)) {
              echo "Example usage: foos Samuel 11 Coffey 15";
  
              break;
          }
  
         $strength1 = isset($table[$player1])
              ? $table[$player1]
              : DEFAULT_STRENGTH;
  
          $strength2 = isset($table[$player2])
              ? $table[$player2]
              : DEFAULT_STRENGTH;
  
          $q1 = pow(10, $strength1 / RELATIVE_STRENGTH_NORMALISATION);
          $q2 = pow(10, $strength2 / RELATIVE_STRENGTH_NORMALISATION);
  
          $expected1 = $q1 / ($q1 + $q2);
          $expected2 = $q2 / ($q1 + $q2);
  
          $internalScore1 = 0;
          $internalScore2 = 0;
  
          if ($score1 > $score2) {
              $internalScore1 = 1;
          } elseif ($score1 < $score2) {
              $internalScore2 = 1;
          } else {
  
              // TODO Should players be able to draw;   
          $internalScore1 = $internalScore2 = 0.5;
          }
  
          $strength1 += K * ($internalScore1 - $expected1);
          $strength2 += K * ($internalScore2 - $expected2);
  
          $table[$player1] = $strength1;
          $table[$player2] = $strength2;
  
          asort($table, SORT_NUMERIC);
          $table = array_reverse($table, true);
  
          file_put_contents(TABLE_FILE, json_encode($table));
  
          // TODO Expose this data!
         $games[] = array(
              'timestamp' => time(),
              'result'    => array(
                  $player1 => $score1,
                  $player2 => $score2,
              ),
          );
  
          file_put_contents(GAMES_FILE, json_encode($games));
  
          // FOOS-1 Give better feedback about the game
          $result = 'Game #' . count($games) . ': ';
  
          $prettyPlayer1 = "{$player1} (" . intval($strength1) . ')';
          $prettyPlayer2 = "{$player2} (" . intval($strength2) . ')';
  
          if ($score1 > $score2) {
              $result .= "{$prettyPlayer1} beat {$prettyPlayer2}!";
          } elseif ($score1 < $score2) {
              $result .= "{$prettyPlayer2} beat {$prettyPlayer1}!";
          } else {
              $result .= "{$prettyPlayer1} drew with {$prettyPlayer2}...";
          }
  
          echo $result;
  
          break;
  
      default:
          // print "Don't know how to '$input'\n";        
          break;
*/
}


function sortPlayer(FoosPlayer $a, FoosPlayer $b) {
    if ($a->getStrength() == $b->getStrength()) {
        return 0;
    }
    return ($a->getStrength() < $b->getStrength()) ? 1 : -1;
}
