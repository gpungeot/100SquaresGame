var hSquares = {};
var width = 20;

hSquares.Board = function(w, h)
{
    var that = this;
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
        if(that.manageReturnCode(data))
        {
            that.fillSquares(data);
            that.showAvailableSquares(data);
        }
    }

    this.manageReturnCode = function(data)
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
                            {"action": "back", "x": data.squares[0].x, "y": data.squares[0].y},
                            function(data)
                            {
                                that.updateBoard(data)
                            }
                        );
                    }
                    else
                    {
                        return false;
                    }
                    break;
                case '2':
                    // Back done server-side
                    // Update front board
                    that.clearSquares(data.squares[0].value);
                    break;
            }
        }
        return true;
    }

    this.fillSquares = function(data)
    {
        if(data.squares)
        {
            // Remove highlighted cells
            $('#animate').stop(true, true).remove();
            $('.availableSquare').removeClass('availableSquare');

            var i = 0;
            while(data.squares[i])
            {
                $('.lastSquare').removeClass('lastSquare');

                // Put value in square + highlight new current square
                $('#board td#'+((data.squares[i].y) * width + data.squares[i].x)).html(data.squares[i].value).addClass('lastSquare');
                i++;
            }
        }
    }

    this.showAvailableSquares = function(data)
    {
        if(data.available)
        {
            var l = $('.lastSquare');
            l.append('<div id="animate" class="availableSquare"></div>');
            var offsetDeparture = l.offset();
            $('#animate').css({ 
                position: "absolute",
                top: "-500px", left: "-500px",
                width: l.width()+"px",
                height: l.height()+"px",
            });
            // Highlight available squares
            var i = 0;
            while(data.available[i])
            {
                let d = $('#animate');
                d.offset({top:offsetDeparture.top,left:offsetDeparture.left});
                var offsetArrival = $('#board td#'+((data.available[i].y) * width + data.available[i].x)).offset();
                let item = i;
                d.animate({top: offsetArrival.top+"px", left: offsetArrival.left+"px"}, 100, function(){
                    $('#board td#'+((data.available[item].y) * width + data.available[item].x)).addClass('availableSquare');
                    if(d.queue().length == 1)
                        d.offset({top:-500,left:-500});
                });
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