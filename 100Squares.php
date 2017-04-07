<?php
// Implement the 100squares game

    require 'board.php';

    class nSquaresGame
    {
        private $_board;
        private $_lastPlay;
        
        // Create board from scratch or from session data
        function nSquaresGame($xRange, $yRange, $defaultValue, $forceRenew = false)
        {
            if( !$forceRenew && isset($_SESSION['board']))
            {
                $this->_board = unserialize($_SESSION['board']);
            }
            else
            {
                $this->_board = new board($xRange, $yRange, $defaultValue);
                $this->save();
            }
        }

        // Session storage
        private function save()
        {
            $_SESSION['board'] = serialize($this->_board);
        }
        
        // Clear last move data
        function clearLastPlay()
        {
            $this->_lastPlay["squares"] = null;
            $this->_lastPlay["code"] = null;
        }

        // Set last move informations (squares and/or code)
        private function addLastPlay($value, $type = "squares")
        {
            $this->_lastPlay[$type][] = $value;
        }

        // Return last move informations to front in given @format
        function getLastPlay($format = "json")
        {
            if(sizeof($this->_lastPlay["squares"]) == 0 || $this->_lastPlay["code"] == -1)
                return '';
            switch($format)
            {
                case "json" :
                    $res = '{"squares":{';
                    // Could be 1 to n moves
                    for($i = 0; $i < sizeof($this->_lastPlay["squares"]); $i++)
                    {
                        if($i > 0) $res .= ',';
                        $res .= '"'.$i.'":'.$this->_lastPlay["squares"][$i]->toJson();
                    }
                    
                    // Code let front know what action to do (play, go back, ...)
                    $res .= '},"code":"'.$this->_lastPlay["code"][0].'"';

                    // Data for available squares to highlight
                    $nextSquares = $this->getValidSquaresAround($this->_lastPlay["squares"][sizeof($this->_lastPlay["squares"])-1]->getX(), $this->_lastPlay["squares"][sizeof($this->_lastPlay["squares"])-1]->getY());
                    if(sizeof($nextSquares) > 0)
                    {
                        $res .= ',"available":{';
                        for($i = 0; $i < sizeof($nextSquares); $i++)
                        {
                            if($i > 0) $res .= ',';
                            $res .= '"'.$i.'":'.$nextSquares[$i]->toJson();
                        }
                        $res .= '}';
                    }

                    $res .= '}';
                    break;
            }
            return $res;
        }

        // Play 1 move
        function play($x, $y)
        {
            if($this->isValidSquare($x, $y))
            {
                // The square is free and reachable
                // Valid the move
                $this->_board->setSquareValue($x, $y, $this->_board->getLastElement()->getValue()+1);
                
                // Store new position and value
                $this->addLastPlay($this->_board->getLastElement());

                // Is the game finished ?
                if($this->_board->getLastElement()->getValue() == $this->_board->getSize()->getValue())
                    $this->addLastPlay(0, "code");
            }
            else
            {
                // The square is not free
                $s = $this->_board->getSquare($x, $y);
                if($s->getValue() > 0 && $s->getValue() < $this->_board->getLastElement()->getValue())
                {
                    // Store position and code to ask for back to user.
                    $this->addLastPlay($s);
                    $this->addLastPlay(1, "code");
                }
            }
            $this->save();
        }

        // Go back to a previous square
        function back($x, $y)
        {
            // Clear all squares with value > chosen square
            $value = $this->_board->getSquareValue($x, $y);
            if($value != 0)
            {
                for($i = 0; $i < $this->_board->getSize()->getX(); $i++)
                {
                    for($j = 0; $j < $this->_board->getSize()->getY(); $j++)
                    {
                        if($this->_board->getSquareValue($i, $j) > $value)
                            $this->_board->clearSquare($i, $j);
                    }
                }

                // Update last square/value
                $this->_board->setSquareValue($x, $y, $value);

                // Store position and code to update the board
                $this->addLastPlay($this->_board->getLastElement());
                $this->addLastPlay(2, "code");
            }
            $this->save();
        }
        
        // Return square at position @pos reachable from position (@x, @y) (see below)
        /*
            -----1-----
            ---8---2---
            -----------
            --7--X--3--
            -----------
            ---6---4---
            -----5-----
        */
        private function getNextSquare($x, $y, $pos)
        {
            switch ($pos)
            {
                case 1 : return $this->_board->getSquare($x, $y-3);
                case 2 : return $this->_board->getSquare($x+2, $y-2);
                case 3 : return $this->_board->getSquare($x+3, $y);
                case 4 : return $this->_board->getSquare($x+2, $y+2);
                case 5 : return $this->_board->getSquare($x, $y+3);
                case 6 : return $this->_board->getSquare($x-2, $y+2);
                case 7 : return $this->_board->getSquare($x-3, $y);
                case 8 : return $this->_board->getSquare($x-2, $y-2);
            }
        }

        // Return true if square at position (@x, @y) is free and reachable from current square
        function isValidSquare($x, $y)
        {
            if($this->_board->isEmpty($x, $y))
            {
                if($x >= 0 && $x < $this->_board->getSize()->getX() && $y >= 0 && $y < $this->_board->getSize()->getY())
                {
                    if($this->_board->isEmpty($this->_board->getLastElement()->getX(), $this->_board->getLastElement()->getY()))
                        return true;
                    for($pos = 1; $pos < 9; $pos++)
                    {
                        $s = $this->getNextSquare($x, $y, $pos);
                        if($s != null && $s->getValue() == $this->_board->getLastElement()->getValue())
                            return true;
                    }
                }
            }
            return false;
        }

        // Return an array of squares free and reachable from square at position (@x, @y)
        function getValidSquaresAround($x, $y)
        {
            $validSquares = array();

            for($pos = 1; $pos < 9; $pos++)
            {
                $s = $this->getNextSquare($x, $y, $pos);
                if($s && $this->_board->isEmpty($s->getX(), $s->getY()))
                    $validSquares[] = $s;
            }
            return $validSquares;
        }

        // Return the best choice according to the AI algorithm
        // Best choice is an available square with the less available next squares (but more than zero)
        function findBestSquare()
        {
            $s = $this->_board->getLastElement();
            $nextSquares = $this->getValidSquaresAround($s->getX(), $s->getY());
            $minValue = 9;
            $bestSquare = null;
            foreach($nextSquares as $s2)
            {
                $nextSquares2 = $this->getValidSquaresAround($s2->getX(), $s2->getY());
                if(sizeof($nextSquares2) < $minValue && sizeof($nextSquares2) > 0
                    || $bestSquare == null
                    || sizeof($nextSquares2) == $minValue && rand(0, 9) > 4)
                {
                    $minValue = sizeof($nextSquares2);
                    $bestSquare = $s2;
                }
            }
            return $bestSquare;
        }
    }
?>
