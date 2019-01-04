<html>
<head>
    <title>Demo</title>
    <style>
        fieldset{
            display: inline-block;
            clear: right;
        }
        label{
            display: inline-block;
            width: 100px;
        }
    </style>
    <base href="<?= $baseUrl ?>">
</head>
<body>

<h1>OAuth</h1>
<form onsubmit="return false" action="#" name="login_frm">
    <fieldset>
        <legend>Login</legend>
        <label for="username">Username</label><input type="text" name="username" placeholder="username" value="vsergiu"><br>
        <label for="password">Password</label><input type="password" name="password" placeholder="password" value="parola123"><br>
        <label for="url">URL</label><input type="text" name="url" value="index.php/oauth/token/password"><br>
        <label></label><input type="button" value="Authenticate" onclick="auth(event);"><br>
        <hr>
        <label for="response">Response</label><input type="text" name="response"><br>
        <label for="access_token">Access Token</label><input type="text" name="access_token" id="access_token" value="afb5992f6cd10003255eb88ab390657c292a7106"><br>
        <label for="refresh_token">Refresh Token</label><input type="text" name="refresh_token" id="refresh_token" value="">
    </fieldset>
</form>
<form onsubmit="return false" action="#" name="res_req_frm">
    <fieldset>
        <legend>Access resources</legend>
        <label>URL</label><br><input type="text" name="url" placeholder="url" value="index.php/Resource" size="50"><br>
        <input type="button" value="GET resource" onclick="request_resource(this.form,'GET');">
        <input type="button" value="POST resource" onclick="request_resource(this.form,'POST');"><br>
        <textarea name="response" cols="50" rows="10"></textarea>
    </fieldset>
</form>
<form onsubmit="return refresh_token(this);" action="#">
    <fieldset>
        <legend>Refresh token</legend>
        <label for="url">URL</label><input type="text" name="url" value="index.php/oauth/token/refresh"><br>
        <input type="submit" value="Refresh"><br>
    </fieldset>
</form>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
    crossorigin="anonymous"></script>

<script>
    var clientId = "demo_client";
    var clientSecret = "";

    /**
     *
     * @param frm
     * @return {boolean}
     */
    function auth(e)
    {
        e.preventDefault();
        var frm = e.target.form;
        console.log(frm.url.value);
        $.ajax(
            {
                url:frm.url.value,
                data: {
                    username: frm.username.value,
                    password: frm.password.value,
                    grant_type: "password",
                    client_id: clientId,
                    client_secret: clientSecret
                },
                type: "POST",
                dataType: "json"
            }
        )
        .done(function (data,status,xhr) {
            console.log(data);
            frm.response.value=xhr.status + " " + xhr.statusText;
            if(data.hasOwnProperty("access_token")) {
                frm.access_token.value = data.access_token;
                frm.refresh_token.value = data.refresh_token;
            }
        })
        .fail(function (xhr) {
            console.log(xhr);
            frm.response.value=xhr.status + " " + xhr.statusText;
        });
        return false;
    }

    function refresh_token(frm)
    {
        var ajxReq = {
            url:frm.url.value,
            type:"POST",
            dataType:"json",
            data:{
                refresh_token:$("#refresh_token")[0].value,
                grant_type: "refresh_token",
                client_id: clientId,
                client_secret: clientSecret
            }
        };
        console.log(ajxReq);
        $.ajax(ajxReq).done(function (data, state, xhr) {
            console.log(data);
            document.forms["login_frm"].access_token.value = data.access_token;
            document.forms["login_frm"].refresh_token.value = data.refresh_token;
        })
        .fail(function (xhr) {
            console.log(xhr);
        })
        return false;
    }

    /**
     *
     * @param frm
     * @return {boolean}
     */
    function request_resource(frm,method)
    {
        var ajxReq = {
            url:frm.url.value,
            type:method,
            beforeSend: function(xhr) {
                console.log($("#access_token")[0].value);
                xhr.setRequestHeader("Authorization","Bearer "+$("#access_token")[0].value)
            }
        }
        /*
        if(method=="POST")
            ajxReq.data = {access_token:$("#access_token")[0].value};
        else
            ajxReq.url += (ajxReq.url.indexOf("?")==-1?"?":"")+"&access_token="+$("#access_token")[0].value;
        */

        $.ajax(ajxReq)
            .done(function(msg){
                console.log(msg);
                frm.response.value=JSON.stringify(msg);
            })
            .fail(function (xhr) {
                frm.response.value=xhr.statusText;
            });
        return false;
    }

</script>
</body>
</html>