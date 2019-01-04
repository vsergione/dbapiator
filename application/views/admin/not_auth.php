<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>APIATOR ADMIN - DBApiator - APIs list</title>

    <!-- Bootstrap core CSS -->
    <link href="/proteus/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/proteus/css/dashboard.css" rel="stylesheet">
</head>
<body>

<div id="overlayNotAuth">
    <div class="display">You're not authorized to access this page.<br><br>If you are not redirected automatically to login page, please click <a href="<?=$basePath?>/admin/login">here</a></div>
</div>
<script type="application/javascript" language="JavaScript">
    setTimeout(function(){window.location="<?=$basePath?>/admin/login"},2000);
</script>
</body>
</html>