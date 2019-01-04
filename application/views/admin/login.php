<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>Apiator Login</title>

    <!-- Bootstrap core CSS -->
    <link href="/proteus/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="/proteus/css/login.css" rel="stylesheet">
    <!-- Bootstrap core JavaScript -->
    <script src="/proteus/js/jquery-3.3.1.js"></script>
    <script src="/proteus/js/jquery-ui.js"></script>
    <script src="/proteus/js/popper.min.js"></script>
    <script src="/proteus/js/bootstrap.min.js"></script>
    <!-- Icons -->
    <script src="/proteus/js/feather.min.js"></script>
</head>

<body>
<div class="form-signin text-center">
    <form onsubmit="console.log('submit');login(this.username.value,this.password.value);return false;" action="#">
        <img class="mb-4" src="" alt="" width="72" height="72">
        <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
        <label for="inputUsername" class="sr-only">Username</label>
        <input type="text" name="username"  id="inputUsername" class="form-control" placeholder="Username" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <!--<div class="checkbox mb-3">
            <label>
                <input type="checkbox" value="remember-me"> Remember me
            </label>
        </div>-->
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
        <hr>
        You don't have yet an account?
        <button class="btn btn-lg btn-primary btn-block" type="button" data-toggle="modal" data-target="#registerModal">Register now</button>
        <p class="mt-5 mb-3 text-muted">&copy; 2017-2018</p>
    </form>
</div>
<div class="modal" tabindex="-1" role="dialog" id="registerModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="#" onsubmit="return register(this)">
            <div class="modal-header">
                <h5 class="modal-title">Registration form</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="username" class="form-control" id="username" aria-describedby="usernameHelp" placeholder="Enter username" name="username" required>
                        <small id="usernameHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Enter email" name="email" required>
                        <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="passwordConf">Confirm Password</label>
                        <input type="password" class="form-control" id="passwordConf" placeholder="Confirm Password" name="passwordConf" required>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
            </form>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="registerOk">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Registration successful</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p>Registration was succesfull and your account is ready to use.</p>
                <p>Please confirm your email to avoid getting the account deleted</p>
                <div class="text-center">
                    <a href="/proteus/launchpad/" class="btn btn-success">Continue to launchpad</a>
                </div>
            </div>
            <!--
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Continue</button>
            </div>
            -->
        </div>
    </div>
</div>
<script src="<?=$basePath?>/js/oauth2js.js"></script>
<script>

    var baseUrl = "<?=$basePath;?>";
    /**
     * @var OAuth2
     */
    var oauth2;

    feather.replace();
    function displayAuthState() {
        $("#overlay").css("display","none");
    }

    function displayNonAuthState() {
        $("#overlay").css("display","block");
    }

    $(document).ready(function () {
        oauth2 = OAuth2({
            onAuth: function(tokenData){
                console.log(tokenData);
                window.location = baseUrl+"/admin/dashboard?access_token="+tokenData.access_token;
            },
            onDeauth: function () {},
            authEndPointBaseUrl: baseUrl+"/oauth/token"
        });
        $("a").each(function (item) {

        });
    });


    function register(f) {
        if(f.password.value!==f.passwordConf.value)
            return;
        $.post(baseUrl+"/register",
                {
                    username:f.username.value,
                    email:f.email.value,
                    password:f.password.value
                }
            )
            .done(function (data) {
                /**
                localStorage.setItem("authData",data);
                $("#registerModal").modal("hide");
                var lnk = $("#registerOk").modal("show").find("a");
                var newPage = lnk.attr("href")+"?_token="+data.access_token;
                lnk.attr("href",newPage);
                window.setTimeout(function () {
                    window.location.href=newPage;
                },5000);
                 */
                $("#registerModal").modal("hide");
                login(f.username.value,f.password.value);
            })
            .fail(function (f) {
                console.log(f);
            })
        return false;
    }
</script>
</body>
</html>
