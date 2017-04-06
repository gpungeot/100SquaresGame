var hSquares = {};

hSquares.Board = function(w, h)
{
    var that = this;
    var lastSquare = null;
    var width = w;
    var height = h;

    // Bind all cells of the board
    this.bindBoard = function(id)
    {
        $(id).click(function()
        {
            var x, y;
            x = y = $(this).attr('id');
            x = x % width;
            y = (y - x) / width;
            that.playSquare(x, y);
    	});
    }

    // Manages clicks on board
    this.playSquare = function(x, y)
    {
        $.getJSON("actionManager.php",
            {"action": "play", "x": x, "y": y},
            function(data)
            {
                that.updateBoard(data);
            }
        );
    }

    // Clears all squares containing value > @value
    this.clearSquares = function(value)
    {
        $('#board td').each(function()
        {
            if($(this).html() > value)
                $(this).html("");
        });
    }

    // Update board state
    this.updateBoard = function(data)
    {
        if(data.code)
        {
            switch(data.code)
            {
                case '0':
                    // Won
                    alert("Bravo, c'est gagné !")
                    break;
                case '1':
                    // Confirm back to this square
                    if (confirm("Voulez-vous revenir à cette case ?"))
                    {
                        $.getJSON("actionManager.php",
                            {"action": "back", "i": data.squares[0].x, "j": data.squares[0].y},
                            function(data)
                            {
                                that.updateBoard(data)
                            }
                        );
                    }
                    else
                    {
                        return;
                    }
                    break;
                case '2':
                    // Back done server-side
                    // Update front board
                    that.clearSquares(data.squares[0].value);
                    break;
            }
        }
        if(data.squares)
        {
            // Remove highlighted cells
            $('.availableSquare').removeClass('availableSquare');

            var i = 0;
            while(data.squares[i])
            {
                if(lastSquare)
                    lastSquare.removeClass('lastSquare');

                // Put value in square + highlight new current square
                lastSquare = $('#board td#'+((data.squares[i].y) * width + data.squares[i].x));

                lastSquare.html(data.squares[i].value);
                lastSquare.addClass('lastSquare');
                i++;
            }
        }
        if(data.available)
        {
            // Highlight available squares
            var i = 0;
            while(data.available[i])
            {
                $('#board td#'+((data.available[i].y) * width + data.available[i].x)).addClass('availableSquare');
                i++;
            }
        }
    }

    // AI game - 1 step
    this.findNext = function()
    {
        $.getJSON("actionManager.php",
            {"action": "findNext"},
            function(data)
            {
                that.updateBoard(data);
            }
        );
    }

    // AI game - try to finish
    this.findAll = function()
    {
        $.getJSON("actionManager.php",
            {"action": "findAll"},
            function(data)
            {
                that.updateBoard(data);
            }
        );
    }
}