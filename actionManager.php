
<?php
// Manage actions on front board

    session_start();
    require '100Squares.php';

    if(! (isset($_GET['action'])))
        return  ;

    $action = $_GET['action'];
    $x = isset($_GET['x']) ? $_GET['x'] : -1;
    $y = isset($_GET['y']) ? $_GET['y'] : -1;
    $format = isset($_GET['format']) ? $_GET['format'] : 'json';

    // Create an empty board or restore it from the session
    $b = new nSquaresGame(0, 0, null);
    
    $b->clearLastPlay();

    switch ($action)
    {
        case "play":
            // Play normally
            $b->play($x, $y);
            break;
        case "back":
            // Go back
            $b->back($x, $y);
            break;
        case "findNext":
            // Let the computer find the next move
            $s = $b->findBestSquare();
            if($s)
                $b->play($s->getX(), $s->getY());
            break;
        case "findAll":
            // Let the computer finish the game
            while(true)
            {
                $s = $b->findBestSquare();
                if($s)
                    $b->play($s->getX(), $s->getY());
                else
                    break;
            }
            break;
    }

    // Return information to display
    echo $b->getLastPlay($format);
?>
