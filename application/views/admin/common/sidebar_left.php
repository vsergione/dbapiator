<?php $adminBasePath = $basePath."/admin" ?>
<nav class="col-md-2 d-none d-md-block bg-light sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link<?= isset($currentPage)&&$currentPage==="dashboard"?" active":"" ?>" href="<?=$adminBasePath?>/dashboard?access_token=<?=$accessToken?>">
                    <span data-feather="home"></span>
                    Dashboard<?= isset($currentPage)&&$currentPage==="dashboard"?" <span class=\"sr-only\">(current)</span>":"" ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= isset($currentPage)&&$currentPage==="dbapiator_apis_list"?" active":"" ?>" href="<?=$adminBasePath?>/dbapiator?access_token=<?=$accessToken?>">
                    <span data-feather="file"></span>
                    DB Apiator<?= isset($currentPage)&&$currentPage==="dbapiator_apis_list"?" <span class=\"sr-only\">(current)</span>":"" ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= isset($currentPage)&&$currentPage==="gwapiator"?" active":"" ?>" href="<?=$adminBasePath?>/gwapiator?access_token=<?=$accessToken?>">
                    <span data-feather="file"></span>
                    GW Apiator<?= isset($currentPage)&&$currentPage==="gwapiator"?" <span class=\"sr-only\">(current)</span>":"" ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= isset($currentPage)&&$currentPage==="fileapiator"?" active":"" ?>" href="<?=$adminBasePath?>/fileapiator?access_token=<?=$accessToken?>">
                    <span data-feather="file"></span>
                    File Apiator<?= isset($currentPage)&&$currentPage==="fileapiator"?" <span class=\"sr-only\">(current)</span>":"" ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= isset($currentPage)&&$currentPage==="mailapiator"?" active":"" ?>" href="<?=$adminBasePath?>/mailapiator?access_token=<?=$accessToken?>">
                    <span data-feather="file"></span>
                    M@ilApiator<?= isset($currentPage)&&$currentPage==="mailapiator"?" <span class=\"sr-only\">(current)</span>":"" ?>
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span data-toggle="collapse" data-target="#reportsLinks">Saved reports</span>
            <a class="d-flex align-items-center text-muted">
                <button data-toggle="collapse" data-target="#reportsLinks" data-feather="plus-circle"></button>
            </a>
        </h6>
        <ul class="nav mb-2 collapse" id="reportsLinks">
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <span data-feather="file-text"></span>
                    Current month
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <span data-feather="file-text"></span>
                    Last quarter
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <span data-feather="file-text"></span>
                    Social engagement
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <span data-feather="file-text"></span>
                    Year-end sale
                </a>
            </li>
        </ul>
    </div>
</nav>