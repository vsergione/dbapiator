<?php $adminBasePath = $basePath."/admin" ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    <h1 class="h2">Applications</h1>
</div>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item" aria-current="page">Home</li>
        <li class="breadcrumb-item active" aria-current="page">Applications</li>
    </ol>
</nav>
<div data-3bentry="apicollection" data-3bapp="apilist" class="container-fluid">
    <table class="table">
        <thead>
        <tr>
            <th><input type="checkbox"></th>
            <th>App name</th>
            <th>Connection</th>
            <th>API Root</th>
            <th>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#newAppDialog">
                    New app
                </button>
            </th>
        </tr>
        </thead>
        <tbody data-3bcontainer="apicollection" data-3btype="collection">
        <tr data-3btemplate>
            <td><input type="checkbox"></td>
            <td><a href="<?=$adminBasePath?>/dbapiator/api/<%= name %>?access_token=<?=$accessToken?>"><%= name %></a></td>
            <td><%= type+'://'+host +'/'+database%></td>
            <td><%= "<?= $apiRoot ?>"+name %></td>
            <td>
                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#renameDialog" data-apiname="<%= name %>">
                    Rename
                </button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDeleteDialog" data-apiname="<%= name %>">
                    Delete
                </button>
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td data-3bpagging="userstable" colspan="5" class="pagging">
                <span data-3bpagging-first></span>
                <span data-3bpagging-prev></span>
                <span data-3bpagging-pages></span>
                <span data-3bpagging-next></span>
                <span data-3bpagging-last></span>
                <span data-3bpagging-pagesize></span>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
<style>
    #new_app_form label{
        display: inline-block;
        width: 200px;
    }
    .error-messages{
        color: red;
    }
</style>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="newAppDialog" aria-labelledby="newAppDialogLabel">
    <form id="new_app_form">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-fields">
                        <label>App name</label><input type="text" name="label" placeholder="some_name" value="bcanvas"><br>

                        <label>Database Type</label><select name="type">
                            <option value="mysqli">MySQL/MariaDB</option>
                            <option value="oracle">OracleDB</option>
                            <option value="msql" disabled>Microsoft SQL</option>
                            <option value="pgsql" disabled>PostgreSQL</option>
                            <option value="sqlite" disabled>SQLite</option>
                        </select><br>
                        <label>Host</label><input type="text" name="host" placeholder="hostname or IP" value="localhost"><br>
                        <label>Username</label><input type="text" name="username" placeholder="username" value="root"><br>
                        <label>Password</label><input type="text" name="password" placeholder="password" value="parola123"><br>
                        <label>Database name</label><input type="text" name="database" placeholder="your_db_name" value="bcanvas"><br>
                        <hr>
                        <input type="checkbox" name="database" placeholder="your_db_name" value="bcanvas"> Take me directly to API dashboard<br>
                    </div>
                    <div class="error-messages"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="newApi(this);return false;" id="newAppButton">Create application</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="errorDialog" aria-labelledby="errorDialogLabel">
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="confirmDeleteDialog" aria-labelledby="confirmDeleteDialogLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteDialogLabel">Confirmation dialog</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete API <strong data-label="apiname"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger" onclick="deleteApi(this)">Yes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="renameDialog" aria-labelledby="renameDialogLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renameDialogLabel">Rename API</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                New API name: <input type="text" name="apiname">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger">Yes</button>
            </div>
        </div>
    </div>
</div>


<script src="/proteus/js/bootstrap.min.js"></script>
<script>window.bootstrap || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.js"><\/script>')</script>
<script src="/proteus/js/underscore-min.js"></script>
<script>window._ || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.9.1/underscore-min.js"><\/script>')</script>
<script src="/proteus/js/backbone.js"></script>
<script>window.Backbone || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.3.3/backbone.js"><\/script>')</script>
<script src="/3b.js/3b.js"></script>
<script src="/3b.js/util.js"></script>
<script src="/3b.js/url.js"></script>

<script>
    var apiBaseUrl = "/proteus/admin";
    var logLevel = 0;


    /*
    function confirmDelete(src) {
        $("")
    }
    */
    function deleteApi() {
        $("#confirmDeleteDialog").data().model.destroy();
        $("#confirmDeleteDialog").modal("hide");
    }

    $("#confirmDeleteDialog").on('show.bs.modal',function (e) {
        $(this).data($(e.relatedTarget).parents("tr").data())
        $(this).find("[data-label=apiname]").html($(e.relatedTarget).attr("data-apiname"));
    });
    $("#renameDialog").on('show.bs.modal',function (e) {
        $(this).find("input[name=apiname]").val($(e.relatedTarget).attr("data-apiname"));
    });

    $("#newAppDialog").on('show.bs.modal',function () {
        $(this).find("form")[0].reset();
        $(this).find(".error-messages").empty();
    });

    function displayErrors(xhr) {
        var errors = xhr.hasOwnProperty("responseJSON") && xhr.responseJSON.hasOwnProperty("errors")?
            xhr.responseJSON.errors:
            [{status:null,title:xhr.responseText}];
        console.log(errors);
        var $errors = $('#newAppDialog').find(".error-messages").empty().append("<hr>");
        errors.forEach(function (i) {
            console.log(i);
            $("<div>").html(i.title).appendTo($errors);
        })
    }

    function newApi(b) {
        console.log("new api");
        $.ajax( {
            url: apiBaseUrl+"/apis",
            data: {
                host:b.form.host.value,
                label:b.form.label.value,
                type:$(b.form.type).val(),
                username:b.form.username.value,
                password:b.form.password.value,
                database:b.form.database.value
            },
            type: "POST"
        })
            .done(function(data){
                window.location.pathname = window.location.pathname+"/api/"+data.data.id
            })
            .fail(function(xhr){
                displayErrors(xhr);
            })
    }

    function loadProteus(queryParas) {
        var query = {limit:6};
        if(queryParas && queryParas.constructor==Object)
            _.extendOwn(query,queryParas);

        $("[data-3bapp=apilist]").proteus({
            url: new URLClass(apiBaseUrl+"/apis",query),
            onReadFail: readFail
        });
    }

    function readFail(d1,d2,d3) {
        console.log(d1,d2,d3)
    }


    $(document).ready(function () {
        loadProteus();
    });



    function loadConfigs() {
        $.get(apiBaseUrl+"/apis")
            .done(function (data) {
                console.log(data);
            })
            .fail(function (xhr) {
                console.log(xhr);
                if(!xhr.hasOwnProperty("responseJSON")) {
                    errorDialog("Server error",xhr.responseText);
                    return;
                }
                errorDialog("Server error",xhr.responseJSON.errors.title,xhr.responseJSON.errors.detail);

            });
    }

    function errorDialog(title,message) {
        var $modal = $("#dialogModal");
        $modal.find(".modal-title").text(title);
        $modal.find(".modal-body").html(message);
        $("#dialogModal").modal();
    }
</script>