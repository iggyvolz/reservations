$(function(){
    // Get places
    $.getJSON("data.json").done(function(data)
    {
        for(let i=0;i<data.places.length;i++)
        {
            $("select[name=place]").append("<option value=\""+i+"\">"+data.places[i]+"</option>");
        }
    });
    $("button").click(function(e){
        e.preventDefault();
        var postvars={};
        var thing="";
        $("form input,textarea,select").each(function(_,elem){
            thing+='$';
            thing+=elem.name;
            thing+=', ';
            // Add value to post vars
            postvars[elem.name]=elem.value;
        });
        $.post("api.php?method=submitEvent",postvars).done(function(result){
            result=JSON.parse(result);
            switch(result.status)
            {
                case 0:
                    alert("Success!");
                    // Reset form
                    $("form")[0].reset();
                    break;
                case 1:
                    alert("Conflict with event "+result.data);
                    break;
                case 2:
                    alert("Malformed variable "+result.data);
                    break;
            }
        }).fail(function(result,result2){
            alert("Error, check console for details");
            console.log(result);
            console.log(result2);
        });
    });
});