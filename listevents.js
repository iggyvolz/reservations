$(function(){
    $.getJSON("data.json").done(function(data)
    {
        for(let i=0;i<data.places.length;i++)
        {
            $("body").append("<span id=\"places"+i+"\"></span>");
            $("#places"+i).append("<h1>"+data.places[i]+"</h1>");
            $("#places"+i).append("<ul></ul>");
        }
        $.getJSON("api.php?method=listUpcomingEvents").done(function(result){
            for(let i=0;i<result.length;i++)
            {
                let event=result[i];
                $("#places"+event.place).find("ul").append("<li>"+event.name+"</li>");
            }
        });
    });
});