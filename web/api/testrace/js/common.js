var TYPES=[{name:'Weibo', bgOffsetV:0, color:'red'}, {name:'Twitter', bgOffsetV:0.5, color:'#3366cc'}];
var RANKINGS = ['th', 'st', 'nd', 'rd'];

function rankingsTitle(rankings) {
    if (rankings < 1) return '';
    else if (rankings < RANKINGS.length) return RANKINGS[rankings];
    else return RANKINGS[0];
}