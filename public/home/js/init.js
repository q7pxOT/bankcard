////////////////////////////
(function(d, c) {
    var e = d.documentElement,
        b = "orientationchange" in window ? "orientationchange" : "resize",
        a = function() {
            var f = e.clientWidth;
            if (!f) {
                return
            }
            e.style.fontSize = 11 * (f / 320) + "px"
        };
    if (!d.addEventListener) {
        return
    }
    c.addEventListener(b, a, false);
    d.addEventListener("DOMContentLoaded", a, false);
    a()
})(document, window);

////////////////////////////

var Databus={
	pauseMusic:localStorage.pauseMusic==undefined?0:localStorage.pauseMusic,
	pauseSound:localStorage.pauseSound==undefined?0:localStorage.pauseSound
};
