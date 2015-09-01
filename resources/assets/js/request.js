/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://github.com/nooku/nooku-activities for the canonical source repository
 */

(function() {
    var url = getParameterByName('url');

    if (url) {
        request(url);
    }

    function getParameterByName(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    function ajax(url, callback, data, x) {
        try {
            x = new(this.XMLHttpRequest || ActiveXObject)('MSXML2.XMLHTTP.3.0');
            x.open(data ? 'POST' : 'GET', url, 1);
            x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            x.onreadystatechange = function () {
                x.readyState > 3 && callback && callback(x.responseText, x);
            };
            x.send(data);
        } catch (e) {}
    }

    function request(url) {
        ajax(url, function (responseText, xhr) {
            try {
                if (xhr.status == 200) {
                    var response = JSON.parse(responseText);
                    if (typeof response === 'object' && response.continue) {
                        setTimeout(function() { request(url) }, 1000);
                    }
                }
            } catch (e) {}
        });
    }
})();