AmplifyConnect_position = ""; //vertical-right , vertical-left , footer-right , footer-left
AmplifyConnect_theme = ""; // dark light
AmplifyConnect_text = "";
AmplifyConnect_hide = "";
var shareTracking = false;
var urlTracking = false;
function _amplify(u) {
    setTimeout(function() {
        var d = document, f = d.getElementsByTagName('head')[0],
                _sc = d.createElement('script');
        _sc.type = 'text/javascript';
        _sc.async = true;
        _sc.src = u;
        f.appendChild(_sc, f);
        __d = false;
        _sc.onload = _sc.onreadystatechange = function() {
            if (!__d && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                __d = true;
                Amplify = new AmplifyEngine();
            }
        };
    }, 1);
}
_amplify('http://' + Amplify_ProjectID + '.getamplify.com/magic?akey=' + Amplify_APIKey);
