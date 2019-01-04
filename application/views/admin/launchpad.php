<?php include "common/header.php";?>

<body>

<?php include "common/navbar.php";?>
<div class="container-fluid">
    <div class="row">
        <?php include("common/sidebar_left.php");?>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <?php include "$currentPage.php"?>
        </main>
    </div>
</div>
<div id="overlayLoading">
    <div class="display">Loading.....</div>
</div>
<div id="overlayNotAuth">
    <div class="display">Sorry, you're not authorized to access this page.<br>Please login <a href="<?=$basePath?>/admin/login">here</a></div>
</div>
<script>
    feather.replace();

    function showPage() {
        $("#overlayLoading" ).hide( "fade", {}, 1000 );
        $("#overlayNotAuth").hide();
    }

    function hidePage() {
        $("#overlayNotAuth").show("fade", {}, 1000 );
        $("#overlayLoading" ).hide( "fade", {}, 1000);
    }

    $(document).ready(function () {
        oauth2 = OAuth2({
            onAuth: showPage,
            onDeauth: hidePage,
            authEndPointBaseUrl: "/proteus/oauth/token"
        });
        $("a").each(function (item) {

        });
    });

</script>
</body>
</html>
