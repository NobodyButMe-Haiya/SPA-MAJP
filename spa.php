<?php
$serverName = "localhost";
$userName = "youtuber";
$password = "123456";
$database = "youtuber";

$connection = new mysqli($serverName, $userName, $password, $database);
?>
<!doctype html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <!-- icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.6.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.9/dist/sweetalert2.min.css">
    <title>Single Page Application - Php Ways</title>
</head>
<body class="container-fluid">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="spa.php">SPA</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="spa_with_search.php">SPA With Search</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="spa_search_without_axios.php"">SPA With Search(No Axios)</a>
                </li>
        </div>
    </div>
</nav>
<br/>
<h1> Single Page Application - PHP </h1>
<table class="table table-striped">
    <thead class="table-light">
    <tr>
        <th>    <button type="button" onclick="refreshMe()" class="btn btn-primary">
                <i class="bi bi-tropical-storm"></i>
            </button></th>
        <td>
            <input class="form-control" id="name" type="text"/>
        </td>
        <td>
            <input style="text-align: right" class="form-control" id="age" type="text"/>
        </td>
        <td>
            <button type="button" class="btn btn-success" onclick="createRecord()">
                <i class="bi bi-plus-circle"></i>
                NEW
            </button>
        </td>
    </tr>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">NAME</th>
        <th scope="col">AGE</th>
        <th scope="col">ACTION</th>
    </tr>
    </thead>
    <tbody id="tbody">

    <?php $SQL = "SELECT * FROM person ORDER BY personId DESC";
    $result = $connection->query($SQL);
    while (($row = $result->fetch_assoc()) == TRUE) { ?>
        <tr id="<?php echo $row["personId"]; ?>_personId">
            <th scope="row">
                <?php echo $row["personId"]; ?>
            </th>
            <td>
                <input class="form-control" id="<?php echo $row["personId"]; ?>_name" type="text"
                       value="<?php echo $row["name"]; ?>"/>
            </td>
            <td>
                <input style="text-align: right" class="form-control" id="<?php echo $row["personId"]; ?>_age"
                       type="text" value="<?php echo $row["age"]; ?>"/>
            </td>
            <td>
                <div class="btn-group" role="group" aria-label="Form Button">
                    <button type="button" class="btn btn-warning"
                            onclick="updateRecord(<?php echo $row["personId"]; ?>)">
                        <i class="bi bi-file-earmark-text"></i>
                        UPDATE
                    </button>
                    <button type="button" class="btn btn-danger"
                            onclick="deleteRecord(<?php echo $row["personId"]; ?>)">
                        <i class="bi bi-trash"></i>
                        DELETE
                    </button>
                </div
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<script>
    // at here we try to be native as possible and you can use url to ease change the which one you prefer
    let url = "api.php";
    let tbody = $("#tbody")

    function refreshMe() {
        readRecord();
    }

    function emptyTemplate() {
        return "" +
            "<tr>" +
            "<td colspan='4'>It's lonely her</td>" +
            "</tr>";
    }

    function template(personId, name, age) {
        return "" +
            "  <tr id=\"" + personId + "_personId\">" +
            "        <td>" + personId + "</td>" +
            "        <td><input type=\"text\" class=\"form-control\" id=\"" + personId + "_name\" value=\"" + name + "\">" +
            "        </td>" +
            "        <td><input type=\"text\" class=\"form-control\" id=\"" + personId + "_age\" value=\"" + age + "\" style=\"text-align: right\">" +
            "        </td>" +
            "" +
            "        <td>" +
            "          <div class=\"btn-group\">" +
            "            <button type=\"button\" onclick=\"updateRecord('" + personId + "')\" class=\"btn btn-warning\">" +
            "              <i class=\"bi bi-file-diff\"></i>" +
            "              UPDATE" +
            "            </button>" +
            "            <button type=\"button\" onclick=\"deleteRecord('" + personId + "')\" class=\"btn btn-danger\">" +
            "              <i class=\"bi bi-trash\"></i>" +
            "              DELETE" +
            "            </button>" +
            "          </div>" +
            "        </td>" +
            "      </tr>" +
            "";
    }

    function createRecord() {

        const name = $("#name");
        const age = $("#age");

        const formData = new FormData();
        formData.append("mode","create");
        formData.append("name",name.val());
        formData.append("age",age.val());

        axios.post(url , formData)
            .then(function (response) {
                const data = response.data;
                console.log(data);
                if (data.status) {
                    const tbodyTemplate = template(data.lastInsertId, name.val(), age.val());
                    tbody.prepend(tbodyTemplate);
                    Swal.fire({
                        title: 'Success!',
                        text: 'You create a record',
                        icon: 'success',
                        confirmButtonText: 'Cool'
                    })
                    name.val("");
                    age.val("");
                } else {
                    console.log("something wrong");
                }
            })
            .catch(function (error) {
                console.log(error);
            });
    }

    function readRecord() {
        const formData = new FormData();
        formData.append("mode","read");

        axios.post(url, formData)
            .then(function (response) {
                const data = response.data;
                if (data.status) {
                    let templateStringBuilder = "";
                    for (let i = 0; i < data.data.length; i++) {
                        templateStringBuilder += template(data.data[i].personId, data.data[i].name, data.data[i].age);
                    }
                    tbody.html("").html(templateStringBuilder);
                } else {
                    console.log("something wrong");
                }
            })
            .catch(function (error) {
                console.log(error);
            });
    }

    function updateRecord(personId) {

        const formData = new FormData();
        formData.append("mode","update");
        formData.append("name",$("#"+personId+"_name").val());
        formData.append("age",$("#"+personId+"_age").val());
        formData.append("personId",personId);

        axios.post(url, formData)
            .then(function (response) {
                const data = response.data;
                if (data.status) {
                    Swal.fire({
                        title: 'System!',
                        text: 'You updated the record',
                        icon: 'info'
                    })
                } else {
                    console.log("something wrong");
                }
            })
            .catch(function (error) {
                console.log(error);
            });
    }

    function deleteRecord(personId) {

        const formData = new FormData();
        formData.append("mode","delete");
        formData.append("personId",personId);

        Swal.fire({
            title: 'System!',
            text: 'Want to delete the record?',
            icon: 'warning',
            confirmButtonText: 'Yes, I am sure!',
            showCancelButton: true,
            cancelButtonText: "No, cancel it!",
        }).then(function (result) {
            if (result.value) {

                axios.post(url, formData)
                    .then(function (response) {
                        const data = response.data;
                        if (data.status) {
                            $("#" + personId + "_personId").remove();
                            Swal.fire(
                                "Deleted!",
                                "Your file has been deleted.",
                                "success"
                            )
                        } else {
                            console.log("something wrong");
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            } else if (result.dismiss === "cancel") {
                Swal.fire(
                    "Cancelled",
                    "Haiya , be safe la .. sui ",
                    "error"
                )
            }
        });
    }
</script>
</body>
</html>