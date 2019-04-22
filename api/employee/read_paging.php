<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/core.php';
include_once '../shared/utilities.php';
include_once '../config/database.php';
include_once '../objects/employee.php';
include_once '../shared/token.php';

// json web token
$token = new Token();

// authorize user with jwt
if (isset(apache_request_headers()['Authorization']) and $token->validate((apache_request_headers()['Authorization']))) {

    // utilities
    $utilities = new Utilities();

    // instantiate database and employee object
    $database = new Database();
    $db = $database->getConnection();
    $employee = new Employee($db);

    // query employees
    $stmt = $employee->readPaging($from_record_num, $records_per_page);
    $num = $stmt->rowCount();

    // check if more than 0 record found
    if ($num > 0) {

        // employees array
        $employees_arr = array();
        $employees_arr["records"] = array();
        $employees_arr["paging"] = array();

        // retrieve our table contents
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // extract row
            // this will make $row['name'] to
            // just $name only
            extract($row);

            $employee_item = array(
                "empid" => $empid,
                "name" => $name,
                "title" => $title,
                "phone" => $phone,
                "manager" => $manager
            );

            array_push($employees_arr["records"], $employee_item);
        }


        // include paging
        $total_rows = $employee->count();
        $page_url = "{$home_url}employee/read_paging.php?";
        $paging = $utilities->getPaging($page, $total_rows, $records_per_page, $page_url);
        $employees_arr["paging"] = $paging;

        // set response code - 200 OK
        http_response_code(200);

        // make it json format
        echo json_encode($employees_arr);
    } else {

        // set response code - 404 Not found
        http_response_code(404);

        // tell the user employees does not exist
        echo json_encode(
            array("message" => "No employees found.")
        );
    }
} else {

    // set response code
    http_response_code(401);

    // tell the user access denied
    echo json_encode(array("message" => "Access denied."));
}