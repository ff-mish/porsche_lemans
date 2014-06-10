var dataApi=(function(){
    var urlBase='/testrace/api/web';

    return {
        getRaceData:function(callback) {
            $.ajax({ type:"GET", url:urlBase+"/racedata", async:true, cache:false, dataType:"json", success:function(data) {
                if (callback) callback(data);
            }});
        },
        getTeamData:function(callback) {
            $.ajax({ type:"GET", url:urlBase+"/teamdata", async:true, cache:false, dataType:"json", success:function(data) {
                if (callback) callback(data);
            }});
        }
    };
})();