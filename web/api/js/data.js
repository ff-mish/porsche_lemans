var dataApi=(function(){
    var urlBase='/api/web';

    return {
        getRaceData:function(callback) {
            $.ajax({ type:"GET", url:urlBase+"/racedata", async:true, cache:false, dataType:"json", success:function(data) {
                if (callback) callback(data);
            }});
        },
        getTeamData:function(type, callback) {
            $.ajax({ type:"GET", url:urlBase+"/teammobiledata?type="+type, async:true, cache:false, dataType:"json", success:function(data) {
                if (callback) callback(data);
            }});
        }
    };
})();