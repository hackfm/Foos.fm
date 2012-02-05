<?php 

class FoosPlayer {
    private $name;
    private $normalizedName;
    private $strength;
    private $games = 0;
    private $lastTimestamp;

    private $lostAgainst = array();
    private $wonAgainst = array();
    private $nemesisList = array();

    public function FoosPlayer ($name, $strength) {
        $this->name = $name;
        $this->normalizedName = FoosPlayer::normalizeName($name);
        $this->strength = $strength;
    }

    public function getName() {
        return $this->name;
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

    public function getRoundedStrength() {
        return round($this->strength);
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

    private function addOpponentToList(FoosPlayer $opponent, &$list, $inc = 1) {
        $oppNormalizedName = $opponent->getNormalizedName();
        if (isset($list[$oppNormalizedName])) {
            $list[$oppNormalizedName]['count'] += $inc;
        }
        else
        {
            $list[$oppNormalizedName]['count']  = $inc;
            $list[$oppNormalizedName]['player'] = $opponent;
        }

    }

    public function loseAgainst(FoosPlayer $opponent, $strengthDelta, $timestamp) {
        $this->addOpponentToList($opponent, $this->lostAgainst);
        $this->addOpponentToList($opponent, $this->nemesisList);
        $this->addStrength($strengthDelta);
        $this->incGames($timestamp);
    } 

    public function winAgainst(FoosPlayer $opponent, $strengthDelta, $timestamp) {
        $this->addOpponentToList($opponent, $this->wonAgainst);
        $this->addOpponentToList($opponent, $this->nemesisList, -1);
        $this->addStrength($strengthDelta);
        $this->incGames($timestamp);     
    } 

    public function drawAgainst(FoosPlayer $opponent, $strengthDelta, $timestamp) {
        $this->addStrength($strengthDelta);
        $this->incGames($timestamp);
    } 

    public function getLostAgainstList() {
        uasort($this->lostAgainst, array("FoosPlayer", "sortPlayerInListWithCounter"));
        return $this->lostAgainst;
    }

    public function getWonAgainstList() {
        uasort($this->wonAgainst, array("FoosPlayer", "sortPlayerInListWithCounter"));
        return $this->wonAgainst;
    }

    public function getNemesisList() {
        uasort($this->nemesisList, array("FoosPlayer", "sortPlayerInListWithCounter"));
        return $this->nemesisList;
    }

    private static function sortPlayerInListWithCounter($a, $b) {
        if ($a['count'] == $b['count']) {
            return 0;
        }
        return ($a['count'] < $b['count']) ? 1 : -1;
    }

    /**
     * Determines a players nemesis
     * @return Array('count' => delta won games against lost, 'player' => Player) or false if no nemesis found
     */
    public function getNemesis() {
        $list = $this->getNemesisList();

        $values = array_values($list);  
           
        if (count($values) == 0) {
            return false;
        }
        if ($values[0]['count'] < 1) {
            return false;
        }

        return $values[0];   
    }

    public function getNormalizedStrength() {
        return pow(10, $this->getStrength() / FoosTable::RELATIVE_STRENGTH_NORMALISATION);
    }

    public function getChancesToWinAgainst(FoosPlayer $opponent) {
        $q1 = $this->getNormalizedStrength();
        $q2 = $opponent->getNormalizedStrength();
        return $q1 / ($q1 + $q2);
    }

    /**
     * Calculates strength delta for defined outcome. Does not change strength.
     * @param $opponent
     * @param $outcome float 0 = lost, 0.5 = draw, 1 = win
     * @return Strength delta
     */
    public function getStrengthDeltaAfterGame(FoosPlayer $opponent, $outcome) {
        return FoosTable::K * ($outcome - $this->getChancesToWinAgainst($opponent));
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

    public function FoosMatch(FoosPlayer $player1, $score1, FoosPlayer $player2, $score2) {
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

    public function calculateScore() {
        if ($this->isDraw()) {
            $deltaStrength1 = $this->player1->getStrengthDeltaAfterGame($this->player2, 0.5);
            $deltaStrength2 = $this->player2->getStrengthDeltaAfterGame($this->player1, 0.5);

            $this->player1->drawAgainst($this->player2, $deltaStrength1, $this->timestamp);
            $this->player2->drawAgainst($this->player1, $deltaStrength2, $this->timestamp);
        }
        else
        {
            $deltaStrength1 = $this->player1->getStrengthDeltaAfterGame($this->player2, 1);
            $deltaStrength2 = $this->player2->getStrengthDeltaAfterGame($this->player1, 0);

            $this->player1->winAgainst ($this->player2, $deltaStrength1, $this->timestamp);
            $this->player2->loseAgainst($this->player1, $deltaStrength2, $this->timestamp);
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
    const GAMES_FILE    = 'games.json';

    private $gameFolder = '/userhome/marek/foos/'; // Has to end with a trailing slash!
    
    private $players = array();
    private $matches = array();

    private $logMaxSize = 0;
    private $log = array();

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

    public function setLogMaxSize($logMaxSize) {
        $this->logMaxSize = $logMaxSize;
    }

    public function getLogMaxSize() {
        return $this->logMaxSize;
    }

    public function getLog() {
        return $this->log;
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

    /** 
     * Save table to file
     * @param $gamesFile [optional, defaults to GAMES_FILE] File name in $gameFolder
     */
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

    /**
     * Add match to this table and sorts it if necessary.
     */
    public function addMatch(FoosMatch $match) {
        if (count($this->matches) == 0) {
            $this->matches[] = $match;
        }
        else
        {
            $lastMatch = $this->matches[count($this->matches)-1];
            $this->matches[] = $match;
            if($lastMatch->getTimestamp() > $match->getTimestamp()) {
                $this->sortMatches();
            }
        }
    }

    /**
     * Sorts matches. Is called by addMatch().
     * @see FoosTable.addMatch()
     */
    private function sortMatches() {
        uasort($this->matches, array("FoosTable", "sortMatchesByTimestamp"));
    }

    /** 
     * Sort function for sorting arrays of Matches by Timestamp
     */
    private static function sortMatchesByTimestamp(FoosMatch $a, FoosMatch $b) {
        if ($a->getTimestamp() == $b->getTimestamp()) {
            return 0;
        }
        return ($a->getTimestamp() < $b->getTimestamp()) ? 1 : -1;
    }

    /**
     * Delete a match by timestamp
     * @param timestamp Unix timestamp 
     * @return true if successful, otherwise false
     */
    public function deleteMatch($timestamp) {
        for($i=0; $i<count($this->matches); $i++) {
            if ($this->matches[$i]->getTimestamp() == $timestamp) {
                unset($this->matches[$i]);
                $this->matches = array_values($this->matches);
                return true;
            }
        }
        return false;
    }

    /** 
     * Return player by name, create a new object if necessary.
     * This function is case-insensitiv.
     * @param $name Player name, case insensitiv
     * @return FoosPlayer object
     */
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

    /**
     * Should never be called more than once on the same function
     */
    public function calculateScore() {
        foreach($this->matches as $i=>$match) {
            $match->calculateScore();
            if ($this->logMaxSize + $i >= $this->getNumberOfMatches()) {
                $logEntry = array();
                foreach($this->players as $normalizedName=>$player) {
                    $logEntry[$normalizedName] = $player->getStrength();
                }
                $this->log[] = $logEntry;
            }
        }

        $this->sortPlayers();
    }

    public function sortPlayers() {
        uasort($this->players, array("FoosTable", "sortPlayerByStrength"));
    }

    private static function sortPlayerByStrength(FoosPlayer $a, FoosPlayer $b) {
        if ($a->getStrength() == $b->getStrength()) {
            return 0;
        }
        return ($a->getStrength() < $b->getStrength()) ? 1 : -1;
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

    public function getNumberOfMatches() {
        return count($this->matches);
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

}



