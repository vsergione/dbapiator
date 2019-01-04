<?php $adminBasePath = $basePath."/admin" ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    <h1 class="h2">Summary <?=$apiName?> </h1>
</div>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page"><a href="">Home</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href="">Applications</a></li>
        <li class="breadcrumb-item" aria-current="page"><a href=""><?=$apiName?></a></li>
    </ol>
</nav>

<ul class="nav nav-tabs" id="navtabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="summary-tab"  href="#summary" aria-controls="summary" aria-selected="true">Summary</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="connection-tab"  href="#connection" aria-controls="connection" aria-selected="false">DB Connection</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="configuration-tab"  href="#configuration" aria-controls="configuration" aria-selected="false">Configuration</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="settings-tab"  href="#settings" aria-controls="settings" aria-selected="false">API Settings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="clients-tab"  href="#clients" aria-controls="clients" aria-selected="false">Clients</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="documentation-tab"  href="#documentation" aria-controls="documentation" aria-selected="false">Documentation</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="querybuilder-tab"  href="#querybuilder" aria-controls="querybuilder" aria-selected="false">Query Builder</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="restclient-tab"  href="#restclient" aria-controls="restclient" aria-selected="false">REST Client</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" role="tab"  id="codegenerator-tab"  href="#codegenerator"  aria-controls="codegenerator" aria-selected="false">3B.JS</a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane" id="summary" role="tabpanel" aria-labelledby="summary-tab">
        <div class="container">
            <table class="table mt-5">
                <tbody>
                <tr>
                    <td class="font-weight-bold">API Url</td>
                    <td>https://<?= $apiName?></td>
                </tr>
                <tr>
                    <td class="font-weight-bold">API Status</td>
                    <td>Active</td>
                </tr>
                <tr>
                    <td class="font-weight-bold">DB Type</td>
                    <td>MySQL</td>
                </tr>
                <tr>
                    <td class="font-weight-bold">DB Connection status</td>
                    <td>Available</td>
                </tr>
                <tr>
                    <td class="font-weight-bold">API Status</td>
                    <td>Active</td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Security</td>
                    <td>Public</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane" id="connection" role="tabpanel" aria-labelledby="connection-tab" style="padding: 10px;">
        <div class="container">
            <form id="connectionDetailsForm">
                <div class="form-group">
                    <label for="type">Database type:</label>
                    <select class="form-control" id="type" name="type">
                        <option>Select DB</option>
                        <option value="oracle">Oracle DB</option>
                        <option value="mysqli">MySQL/MariaDB</option>
                        <option value="mssql">Microsoft SQL Server</option>
                        <option value="pgsql">PostgreSQL</option>
                        <option value="sqlite">SQLite</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="host">Host</label>
                    <input type="text" class="form-control" id="host" name="host" placeholder="Server hostname or IP">
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                </div>
                <div class="form-group">
                    <label for="database">Schema Name</label>
                    <input type="text" class="form-control" id="database" name="database" placeholder="Schema/database name">
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="updateConnection();return false;">Update connection</button>
            </form>
        </div>
    </div>
    <div class="tab-pane" id="configuration" role="tabpanel" aria-labelledby="configuration-tab" style="padding: 10px;">
        <div style="padding: 10px;">
            <button type="button" id="cfgRegenerateButton" class="btn btn-sm" onclick="regenerateConfiguration();return false;"><span data-feather="git-pull-request"></span> Regenerate from DB</button>
            <button type="button" id="cfgReloadButton" class="btn btn-sm" onclick="loadConfiguration();return false;"><span data-feather="refresh-cw"></span> Reload</button>
            <button type="button" id="cfgSaveButton" class="btn btn-sm" onclick="saveConfiguration($('#configarea').val());return false;" disabled><span data-feather="save"></span> Save</button>
        </div>
        <div style="width: 100%; min-height: 400px" id="configarea">... Loading</div>
    </div>
    <div class="tab-pane container-fluid" id="settings" role="tabpanel" aria-labelledby="settings-tab">
        <div class="row">
            <div class="col-md-12">
                <form>
                    <ul>
                        <li>Access <select><option>Public</option><option>Private</option></select></li>
                        <li>Requests Rate: <input type=""></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
    <!-- //START CLIENT TAB  -->
    <div class="tab-pane" id="clients" role="tabpanel" aria-labelledby="clients-tab">Clients</div>
    <div class="tab-pane" id="querybuilder" role="tabpanel" aria-labelledby="querybuilder-tab">
        Query builder
        <form>
            http://api/name
        </form>
    </div>
    <!-- END// -->
    <!-- //START DOCUMENTATION TAB  -->
    <div class="tab-pane" id="documentation" role="tabpanel" aria-labelledby="documentation-tab">
        <div>
            Tables
        </div>
    </div>
    <div class="tab-pane" id="restclient" role="tabpanel" aria-labelledby="restclient-tab">REST Cient</div>
    <div class="tab-pane" id="codegenerator" role="tabpanel" aria-labelledby="codegenerator-tab">3B</div>
</div>

<div class="modal fade" id="modalDialog" tabindex="-1" role="dialog" aria-labelledby="modalDialogLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary button-yes" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-secondary button-no" data-dismiss="modal">No</button>
                <button type="button" class="btn button-close" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script src="<?=$basePath?>/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=$basePath?>/js/ace/mode-javascript.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=$basePath?>/js/ace/mode-json.js" type="text/javascript" charset="utf-8"></script>
<script src="<?=$basePath?>/js/ace/theme-monokai.js" type="text/javascript" charset="utf-8"></script>
<script>
    /**
     * generates a modal dialog with yes/no buttons
     * @param modal JQuery selector or JQuery object
     * @param {title:title,content:content,buttons:{label:{class,onclick,attributes}}} options
     */
    function modalDialog(modal,options) {
        var $modal = $(modal);

        if(options.title)
            $modal.find(".modal-title").html(options.title);
        if(options.content)
            $modal.find(".modal-body").html(options.content);
        if(options.buttons) {
            var $footer = $modal.find(".modal-footer").empty();
            for(var label in options.buttons) {
                var $button = $("<button>").text(label).addClass("btn "+options.buttons[label].class).appendTo($footer);
                if(options.buttons[label].onclick)
                    $button.on("click",options.buttons[label].onclick)
                if(options.buttons[label].attributes) {
                    for(var attr in options.buttons[label].attributes)
                        $button.attr(attr,options.buttons[label].attributes[attr])
                }
            }
        }
        $(modal).modal("show");
    }

    /**
     *
     * @param modal
     * @param title
     * @param content
     * @param action
     */
    function quickConfirmDialog(modal,title,content,action) {
        modalDialog(modal,{
            title:title,
            content:content,
            buttons:{
                Yes:{
                    onclick: action,
                    class: "btn-primary button-yes",
                    attributes:{
                        "data-dismiss":"modal"
                    }
                },
                No:{
                    class: "btn-secondary button-no",
                    attributes:{
                        "data-dismiss":"modal"
                    }
                }
            }
        });
    }
</script>
<script>
    var appName = '<?=$apiName?>';
    var baseUrl = "<?=$basePath?>/admin";

    [b,a] = window.location.href.split("#");
    if(a===undefined) window.location+="#summary";

    $(document).ready(function () {
        $("#navtabs").find("a").each(function(){

            $(this).on('click', function () {
                $(this).tab('show');
            });

            $(this).on("shown.bs.tab",function () {
                var funcName = "load_"+$(this).attr("aria-controls");
                switch($(this).attr("aria-controls")) {
                    case "summary":
                        break;
                    case "connection":
                        loadConnection();
                        break;
                    case "configuration":
                        loadConfiguration();
                        break;
                    case "settings":
                        break;
                    case "clients":
                        break;
                    case "documentation":
                        loadDocumentation();
                        break;
                    case "restclient":
                        break;
                    case "codegenerator":
                        break;
                }
                if(eval("typeof "+funcName)!=="function") return;
                var func=null;
                eval ("func = "+funcName);
                func();
            });

            if(this.href===window.location.href)
                $(this).click();
        })
    });


</script>
<script>
    /**
     *
     */
    function loadDocumentation() {
        return;
        var map = {
            table: "Tables",
            view: "Views",
            procedure: "Procedures"
        };
        $.ajax({
            url: baseUrl+"/apis/"+appName+"/structure",
            type: 'GET'
        }).done(function (data) {

            var buttonsTables = "";

            var $c = $("<ul>").appendTo($("#documentation").empty());
            var types = {};

            for(var item in data) {
                var cType = map[data[item].type];
                if(!types.hasOwnProperty(cType)) {
                    var newRow = $("<li>").append("<strong>"+cType+"</strong>").appendTo($c);
                    types[cType] = $("<ul>").appendTo(newRow);
                }

                $("<li style='padding:10px; margin 10px; vertical-align: middle' class='border border-dark; '>").html("<a href=''>"+item+"</a>"+buttonsTables).appendTo(types[cType]);
            }
            console.log(types);
            $c.find("a").on("click",function (a) {
                console.log(a);
                return false;
            })
        }).fail(function () {

        })
    }
</script>
<script>
    /**
     *
     */
    function loadConnection() {
        $.ajax({
            url: baseUrl+"/apis/"+appName+"/connection",
            type: 'GET'
        })
            .done(function(data){
                var frm = $("#connectionDetailsForm")[0];
                if(!data.hasOwnProperty("data") || data.data===null)
                    return console.log("Missing data attr");
                if(!data.data.hasOwnProperty("attributes") || data.data.attributes===null)
                    return console.log("Missing attr attr");
                if(!data.data.hasOwnProperty("type") || data.data.type!=="connection")
                    return console.log("Missing type attr");
                console.log(data,frm);

                data = data.data.attributes;
                for(var key in data){
                    if(!data.hasOwnProperty(key))
                        continue;
                    $(frm[key]).val(data[key]);
                }
            })
            .fail(function (xhr) {
                console.log(xhr);
            })
    }

    /**
     * Perform the AJAX req to update the configuration
     */
    function performConnectionUpdate() {
        console.log("update");
        var frm = $("#connectionDetailsForm")[0];
        var data = {
            type: $(frm["type"]).val(),
            host: $(frm["host"]).val(),
            username: $(frm["username"]).val(),
            password: $(frm["password"]).val(),
            database: $(frm["database"]).val()
        };
        $.ajax({
            url: "/proteus/manager/apps/"+appName+"/connection",
            type: 'PUT',
            data: data
        })
            .done(function(data){
                console.log(data);
                for(var key in data){
                    if(!data.hasOwnProperty(key))
                        continue;
                    $(frm[key]).val(data[key]);
                }

            })
            .fail(function (xhr) {
                console.log(xhr);
            })
    }

    /**
     * trigger the confirmation dialog for updating the connection details
     */
    function updateConnection() {
        quickConfirmDialog("#modalDialog",
            "Confirmation dialog",
            "Please confirm that you wish to update the configuration",
            performConnectionUpdate);
    }

</script>

<script>
    var editor = ace.edit("configarea", {
        mode: "ace/mode/json",
        theme:"ace/theme/monokai",
        selectionStyle: "text",
        readOnly: false,
        displayIndentGuides: true
    });
    editor.on("change",function(){
        console.log("change occured");
        enableSave();
    });
    function enableSave() {
        $("#cfgSaveButton").attr("disabled",false);
    }
    function disableSave() {
        $("#cfgSaveButton").attr("disabled",true);

    }

    function loadConfiguration() {
        $.ajax({
            url: baseUrl+"/apis/"+appName+"/structure",
            type: 'GET'
        })
            .done(function(data){
                editor.setValue(JSON.stringify(data,null,4));
                console.log("Configuration loaded succesfully");
                disableSave();
            })
            .fail(function (xhr) {
                console.log(xhr);
            })
    }

    function saveConfiguration() {
        var data = editor.getValue();
        $.ajax({
            url: baseUrl+"/apis/"+appName+"/structure",
            data: data,
            type: 'PUT'
        })
            .done(function (data,status,xhr) {
                console.log(xhr);
                disableSave();
            })
            .fail(function (xhr) {
                console.log(xhr);
            })
    }

    /**
     * Retrieves from server a new regenerated configuration
     */
    function regenerateConfiguration() {
        $.ajax({
            url: baseUrl+"/apis/"+appName+"/structure/regenerate",
            type: 'GET'
        })
            .done(function(data){
                editor.setValue(JSON.stringify(data,null,4));
                console.log("Configuration succesfully regenerated");
                enableSave();
            })
            .fail(function (xhr) {
                console.log(xhr);
            })
    }
</script>
