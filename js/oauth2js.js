/*
 * Copyright (c) 2018. Nepsis Software Solutions SRL
 * version: 1.0.1 20180402
 */


/**
 *
 * @param  {{
 *          authEndPointBaseUrl: authEndPointBaseUrl, dbg: dbg, clientId: clientId
 *          clientSecret: clientSecret, localStorageLabel: localStorageLabel,
 *          authorizationUrl: authorizationUrl, infoTokenUrl: infoTokenUrl, refreshTokenUrl: refreshTokenUrl,
 *          refreshTokenBeforeExpireGuard: refreshTokenBeforeExpireGuard,
 *          resourceBaseUrl: resourceBaseUrl,nonAuthOverlay: nonAuthOverlay,
 *          onAuth: onAuth, onDeauth: onDeauth, onAuthFail: onAuthFail}} options
 * @return {{}}
 * @constructor
 */
function OAuth2(options) {
    if(typeof options!=="object" || options===null)
        options = {};

    /**
     *
     * @type {boolean|null}
     */
    var authState = null;
    /**
     *
     * @type {null}
     */
    var authData = null;

    /**
     * wrapper for localStorage. The idea is to allow in the future to extend this to other storage types
     * @param st
     * @param label
     * @return {{save: save, retrieve: (function(*=): string), delete: delete}}
     */
    function storage(st,label) {
        var myLabel = label;
        var m = {
            save: function(value) {
                console.log("saving",myLabel);
                window.localStorage.setItem(myLabel,value);
            },
            retrieve: function() {
                return window.localStorage.getItem(myLabel);
            },
            remove: function() {
                console.log("removing",myLabel)
                window.localStorage.removeItem(myLabel);
            }
        };
        return m;
    }

    var opts = {
        myStorage: null,
        authEndPointBaseUrl: "",
        clientId: "demo_client",
        clientSecret: "",
        authorizationUrl: null,
        refreshTokenUrl: null,
        infoTokenUrl: null,
        localStorageLabel: "oauth2js",
        refreshTokenBeforeExpireGuard: 300,
        resourceBaseUrl: "",
        nonAuthOverlay: "#notAuthOverlay",
        /**
         * callback function which is run when user has been deauthorized
         * defaults to showing and overlay over the entire page
         * ideally the overlay should be already in place and to be hidden when user is authorized
         */
        onDeauth: function () {
            var $ol = $(opts.nonAuthOverlay);
            if(!$ol.length) {
                var style = "<style>" +
                    opts.nonAuthOverlay+"{position: fixed; left: 0; top: 0; width: 100%; height: 100%; margin: 0; padding: 0; background-color: white;}"+
                    opts.nonAuthOverlay+" div{width: 200px; height: 50px; background-color: blue; text-align: center; position:absolute; " +
                    "left:0; right:0; top:0; bottom:0; margin:auto; max-width:100%; max-height:100%; overflow: auto;}"+
                    "</style>";
                $('html > head').append(style);
                $("body").append("<div id='notAuthOverlay'><div>Please <a href='"+(options.loginPage?options.loginPage:"#")+"'>login</a></div></div>");
            }
        },
        /**
         * callback function to run when user has been authorized
         * defaults to hide the overlay
         */
        onAuth: function () {
            var $ol = $(opts.nonAuthOverlay);
            if($ol.length)
                $ol.css("display","none");
        },
        /**
         * callback function which to run when user authorization has failed
         * @param xhr
         */
        onAuthFail: function (xhr) {
            dbg && console.log(" auth fail",xhr);
            setAuth(false);
        }
    };

    var dbg = typeof options.dbg!=="undefined"?options.dbg:true;
    if(typeof options.authEndPointBaseUrl!=="undefined")
        opts.authEndPointBaseUrl = options.authEndPointBaseUrl;
    if(typeof options.authorizationUrl!=="undefined")
        opts.authorizationUrl = options.authorizationUrl;
    else
        opts.authorizationUrl = opts.authEndPointBaseUrl + "/password";
    if(typeof options.refreshTokenUrl!=="undefined")
        opts.refreshTokenUrl = options.refreshTokenUrl;
    else
        opts.refreshTokenUrl = opts.authEndPointBaseUrl + "/refresh";
    if(typeof options.infoTokenUrl!=="undefined")
        opts.infoTokenUrl = options.infoTokenUrl;
    else
        opts.infoTokenUrl = opts.authEndPointBaseUrl + "/info";

    if(typeof options.localStorageLabel!=="undefined")
        opts.localStorageLabel = options.localStorageLabel;
    if(typeof options.clientId!=="undefined")
        opts.clientId = options.clientId;
    if(typeof options.clientSecret!=="undefined")
        opts.clientSecret = options.clientSecret;

    if(typeof options.refreshTokenBeforeExpireGuard!=="undefined")
        opts.refreshTokenBeforeExpireGuard = options.refreshTokenBeforeExpireGuard;
    if(typeof options.resourceBaseUrl!=="undefined")
        opts.resourceBaseUrl = options.resourceBaseUrl;
    if(typeof options.nonAuthOverlay!=="undefined")
        opts.nonAuthOverlay =  options.nonAuthOverlay;
    if(typeof options.onDeauth==="function")
        opts.onDeauth = options.onDeauth;
    if(typeof options.onAuth==="function")
        opts.onAuth = options.onAuth;
    if(typeof options.onAuthFail==="function")
        opts.onAuthFail = options.onAuthFail;

    opts.myStorage = storage(null,opts.localStorageLabel);

    {
        var internalOauthObj = {};

        /**
         * @return {boolean}
         */
        internalOauthObj.isAuth = function () {
            return authState;
        };

        /**
         * performs the authentication request and stores
         * @param username
         * @param password
         * @param success
         * @param fail
         * @return {boolean}
         */
        internalOauthObj.userAuthorize = function (username, password, success, fail) {
            console.log("start auth");
            $.ajax({
                url: opts.authorizationUrl,
                data: {
                    username: username,
                    password: password,
                    grant_type: "password",
                    client_id: opts.clientId,
                    client_secret: opts.clientSecret
                },
                type: "POST",
                dataType: "json"
            })
                .done(function (data, status, xhr) {
                    dbg && console.log(data);
                    setAuth(true,data);
                    if (success && success.constructor === Function)
                        success(data, status, xhr);
                })
                .fail(function (xhr) {
                    opts.onAuthFail(xhr);
                    if (typeof fail==="function")
                        fail(xhr);
                });
            return this;
        };

        /**
         * Revoke the authorisation
         */
        internalOauthObj.revoke = function () {
            dbg && console.log("Revoke triggered");
            setAuth(false);
            // TODO: make revocation call
            $.ajax()
            return;
        };

        /**
         * attaches callbacks
         * @param eventName []
         * @param callback
         * @return {internalOauthObj}
         */
        internalOauthObj.on = function (eventName, callback) {
            if (!callback || callback.constructor !== Function)
                return this;

            switch (eventName) {
                case "auth":
                    opts.onAuth = callback;
                    break;
                case "deauth":
                    opts.onDeauth = callback;
                    break;
                case "authFail":
                    opts.onAuthFail = callback;
                    break;
            }
            return this;
        };

        /**
         * retrieves access token
         * @return {*}
         */
        internalOauthObj.getAccessToken = function () {
            if (!authState || !authData)
                return null;

            return authData.access_token;
        };

        /**
         * append access_token to an URL only when protocol is HTTPS
         * @param lnk
         * @return {*}
         */
        internalOauthObj.addToken2Lnk = function (lnk) {
            var res = parseUrl(lnk);
            if (!res)
                return lnk;
            var lnkFullMatch, lnkBaseUrl, lnkProtocol, lnkHost, lnkPath, lnkQm, lnkQuery;
            [lnkFullMatch, lnkBaseUrl, lnkProtocol, lnkHost, lnkPath, lnkQm, lnkQuery] = res;

            // check for https and when not secure do not add token
            if (lnkProtocol && lnkProtocol !== "https://")
                return lnk;

            // check for current page protocol when lnk is relative and when not secure do not add token
            if (!lnkProtocol && rgx.exec(window.location)[2] !== "https://")
                return lnk;
            lnkQuery = lnkQuery ? lnkQuery.split("&") : [];
            var found = false;
            for (var i = 0; i < lnkQuery.length; i++) {
                if (lnkQuery[i].substr(0, 13) === "access_token=") {
                    found = true;
                    lnkQuery[i] = "access_token=" + internalOauthObj.getAccessToken();
                }
            }
            if (!found)
                lnkQuery.push("access_token=" + internalOauthObj.getAccessToken());

            return lnkBaseUrl + lnkPath + "?" + lnkQuery.join("&");
        };

        internalOauthObj.getOpts = function () {
            return opts;
        }
    }

    function parseUrl(str) {
        return /((.*:\/\/)(.*?)\/)?(.*?)(\?|$)(.*)/gi.exec(str);
    }

    function extractTokenFromUrl() {
        var lnkFullMatch, lnkBaseUrl, lnkProtocol, lnkHost, lnkPath, lnkQm, lnkQuery;
        var p = parseUrl(window.location);
        [lnkFullMatch, lnkBaseUrl, lnkProtocol, lnkHost, lnkPath, lnkQm, lnkQuery] = p;
        if (!lnkProtocol && rgx.exec(window.location)[2] !== "https://")
            return null;
        lnkQuery = lnkQuery ? lnkQuery.split("&") : [];
        for (var i = 0; i < lnkQuery.length; i++) {
            if (lnkQuery[i].substr(0, 13) === "access_token=") {
                return lnkQuery[i].substr(14);
            }
        }
        return null;
    }

    /**
     * performs the token refresh request and refreshes the local stored auth data
     */
    function refreshToken () {
        if(!authData) {
            dbg && console.log("Refresh token triggered wrongly");
            return;
        }
        var ajxReq = {
            url: opts.refreshTokenUrl,
            type:"POST",
            dataType:"json",
            data:{
                refresh_token: authData.refresh_token,
                grant_type: "refresh_token",
                client_id: opts.clientId,
                client_secret: opts.clientSecret
            }
        };
        dbg && console.log("Refresh token",ajxReq);

        $.ajax(ajxReq)
            .done(function (data, state, xhr) {
                dbg && console.log("Refresh performed and new data received => setAuth true");
                setAuth(true,data);
            })
            .fail(function (xhr) {
                internalOauthObj.revoke();
            })
    }

    /**
     *
     * @param data
     */
    function save_auth_data(data) {
        authData = data;
        dbg && console.log("saving data",data);
        // set expire
        if(!data.hasOwnProperty("expires")) {
            data.expires = Math.round(Date.now() / 1000) + data.expires_in;
            delete data.expires_in;
        }

        // save in storage
        opts.myStorage.save(JSON.stringify(data));
    }

    /**
     *
     */
    function remove_auth_data() {
        opts.myStorage.remove();
    }

    /**
     *
     * @param urls
     * @param token
     * @return {boolean}
     */
    function addAuthorizationHeader(urls,token) {
        $(document).ajaxSend(function(event, xhr, settings){
            if(urls.constructor!==Array)
                urls = [urls];

            urls.forEach(function(item) {
                if(settings.url.indexOf(item)!==0)
                    return;

                //dbg && console.log("url match");
                xhr.setRequestHeader("Authorization","Bearer "+token);
            });
        });
        return true;
    }

    function checkTokenFromUrl() {
        urlTokenChecked = true;
        var accessToken = extractTokenFromUrl();
        if(!accessToken)
            return;
        $.get(opts.infoTokenUrl+"?access_token="+accessToken)
            .done(function (data,state,xhr) {
                dbg && console.log("Token info received => set auth true")
                setAuth(true,data);
            });
    }
    var urlTokenChecked = false;

    /**
     *
     */
    function init() {
        var tmp = opts.myStorage.retrieve();
        dbg && console.log(tmp);
        if(!tmp) {
            dbg && console.log("No data saved => set auth false");
            setAuth(false);
            if(!urlTokenChecked) checkTokenFromUrl();
            return internalOauthObj;
        }

        authData = JSON.parse(tmp);
        if(!authData || typeof authData!=="object" || typeof authData.access_token==="undefined") {
            dbg && console.log("Invalid data => set auth false");
            setAuth(false);
            return internalOauthObj;
        }

        if(authData.expires<(Date.now()/1000-opts.refreshTokenBeforeExpireGuard)) {
            refreshToken();
            dbg && console.log("Token expired => trigger token refresh && set auth false")
            setAuth(false);
        }
        else {
            dbg && console.log("Auth ok");
            setAuth(true,authData);
            addAuthorizationHeader(opts.resourceBaseUrl,authData.access_token);
        }
        return internalOauthObj;
    }

    /**
     *
     * @param state boolean
     * @return {{}}
     */
    function setAuth(state,data) {
        // validate state against data, when state is true
        if(state && (!data || typeof data!=="object" || typeof data.access_token==="undefined"))
            state = false;

        // save old state
        var oldAuthState = authState;
        authState = state;

        // save or cleanup data
        if(state) {
            save_auth_data(data);
        }
        else {
            remove_auth_data();
        }

        if(oldAuthState!==authState) {
            authState ? opts.onAuth(authData) : opts.onDeauth();
            init();
        }



        return internalOauthObj;
    }

    return init();
}

/**
 * quick function to perform login
 * override it for more function
 * @param username
 * @param password
 * @return {*}
 */
function login(username,password) {
    console.log("login");
    if(!username)
        return console.log("not allowed empty username");
    if(!password)
        return console.log("not allowed empty password");
    if(typeof oauth2!=="undefined" && username && password)
        oauth2.userAuthorize(username,password);
    return false;
}

/**
 * quick function to perform logout
 */
function logout() {
    if(typeof oauth2!=="undefined")
        oauth2.revoke();
}


