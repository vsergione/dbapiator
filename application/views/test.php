<html>
<head>
    <title></title>
</head>
<body>
<form>
    <input type="text" name="count">
    <button type="button" onclick="srvcount(this)">Fetch</button>
</form>
<script src="js/jquery-3.3.1.js"></script>
<script>
    function srvcount(b) {
        $.get("https://develhost/proteus/mytest/ajx_resp")
            .done(function (data,xhr) {
                b.form.count.value=data
            })
    }
</script>
</body>
</html>