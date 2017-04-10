<?php
    require 'actionManager.php';

    $nbRows = isset($_GET['height']) ? $_GET['height'] : 10;
    $nbCols = isset($_GET['width']) ? $_GET['width'] : 10;;

    // Create back board
    new nSquaresGame($nbCols, $nbRows, null, true);

    // Iphone CSS - Ugly stuff I know (but I don't care)
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') == false && strpos($_SERVER['HTTP_USER_AGENT'], 'Android') == false)
        $cssSquare = 'square';
    else
        $cssSquare = 'squareIphone';

    // Create front board
    $board = "<table id='board'>";
    for($y = 0; $y < $nbRows; $y++)
    {
        $board .= "<tr>";
        for($x = 0; $x < $nbCols; $x++)
        {
            $id = $y*$nbCols + $x;
            $board .= "<td id='$id' class=\"".$cssSquare."\" ></td>";
        }
        $board .= "</tr>";
    }
    $board .= "</table>";

    // Show computer game buttons
    $AI = false;
    if(isset($_GET['AI_button']) && $_GET['AI_button'] == '1')
    {
        $AI = true;
    }

    require 'index.html';