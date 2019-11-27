<html>
<head>
    <title>Test</title>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
</head>
<body>
<button onclick="send()">asdasdas</button>
<script>
    function send() {
        $.ajax({
            url: "https://5d77725c97176.dbapi.apiator/test/a",
            method: "POST",
            data: {
                a:1,
                b: {
                    c:3,
                    d:{
                        a: [1,2],
                        b: "asd"
                    }
                }
            }
        }).done(function (data) {
            console.log(data);

        })
    }
</script>
</body>
</html>