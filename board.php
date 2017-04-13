<?php

    // Basic square
    class square
    {
        private $_x;
        private $_y;
        private $_value;

        public function __construct($x, $y, $value = null)
        {
            $this->_x = $x;
            $this->_y = $y;
            $this->_value = $value;
        }

        public function setValue($value)
        {
            $this->_value = $value;
        }

        public function getValue()
        {
            return $this->_value;
        }

        public function getX()
        {
            return $this->_x;
        }

        public function getY()
        {
            return $this->_y;
        }

        private function clear($value = null)
        {
            $this->_value = $value;
        }
        
        public function toJson()
        {
            if($this->_value != null)
                return '{"x":'.$this->_x.',"y":'.$this->_y.',"value":'.$this->_value.'}';
            else
                return '{"x":'.$this->_x.',"y":'.$this->_y.',"value":""}';
        }
    }

    // Basic board
    class board
    {
        private $_xRange;
        private $_yRange;
        private $_squares = array();
        private $_lastElement;
        private $_defaultValue;

        public function __construct($x, $y, $value = null)
        {
            $this->_xRange = $x;
            $this->_yRange = $y;
            $this->_defaultValue = $value;

            // Creates all squares filled with default value
            for($i = 0; $i < $this->_xRange; $i++)
            {
                for($j = 0; $j < $this->_yRange; $j++)
                {
                    $this->_squares[$i][$j] = new square($i, $j, $value);
                }
            }

            $this->_squares[-1][-1] = new square(-1, -1, $value);
            $this->_lastElement = $this->_squares[-1][-1];
        }

        // Clear all squares
        private function clear()
        {
            for($x = 0; $x < $this->_xRange; $x++)
            {
                for($y = 0; $y < $this->_yRange; $y++)
                {
                    $this->getSquare($x, $y)->clear($this->_defaultValue);
                }
            }
            $this->_lastElement = $this->getSquare(-1, -1);
        }

        // Return true if square at position (@x, @y) is free
        public function isEmpty($x, $y)
        {
            return $this->getSquareValue($x, $y) == $this->_defaultValue;
        }

        // Return the size of the bord as a square object
        public function getSize()
        {
            return new square($this->_xRange, $this->_yRange, $this->_xRange*$this->_yRange);
        }

        // Return square object at position (@x, @y)
        public function getSquare($x, $y)
        {
            if($x < 0 || $y < 0 || $x > $this->_xRange-1 || $y > $this->_yRange-1 )
                return null;
            return $this->_squares[$x][$y] ? $this->_squares[$x][$y] : null;
        }

        // Return value stored in square at position (@x, @y)
        public function getSquareValue($x, $y)
        {
            if($this->getSquare($x, $y) != null)
                return $this->getSquare($x, $y)->getValue();
            else
                return $this->_defaultValue;
        }

        // Set @value in square at position (@x, @y)
        // Store the last square played
        public function setSquareValue($x, $y, $value)
        {
            $this->getSquare($x, $y)->setValue($value);
            $this->_lastElement = $this->getSquare($x, $y);
        }

        // Reset value stored in square at position (@x, @y)
        public function clearSquare($x, $y)
        {
            $this->getSquare($x, $y)->clear($this->_defaultValue);
        }

        // Return the last played square
        public function getLastElement()
        {
            return $this->_lastElement;
        }

    }
?>
