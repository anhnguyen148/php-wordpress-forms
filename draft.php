<?php 
require_once("../wp-load.php");
require_once("function.inc.php");

// check if POST method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  exit("POST request method is required!");

} else {
  $employee_name = sanitize_text_field($_POST["employee_name"]);
  $position = sanitize_text_field($_POST["position"]);
  $rate = sanitize_text_field($_POST["rate"]);
  $invoice = $_FILES["invoiceImg"];
  $date = sanitize_text_field($_POST["date"]);
  $project = sanitize_text_field($_POST["project"]);  
  $time_length = sanitize_text_field($_POST["time"]);
  $desc = sanitize_text_field($_POST["description"]);
  $agenda = $_FILES["agendaImg"];
  $miles = sanitize_text_field($_POST["miles"]);
  $mile_file = $_FILES["mileageImg"];

  // check if name is empty
  if (empty($employee_name)) {
    header("location: https://msnha.una.edu/timesheet-form?error=emptyname");
    exit();
  }
  // check if position is not consultant or volunteer or employee
  if (!check_position($position)) {
    header("location: https://msnha.una.edu/timesheet-form?error=wrongposition");
    exit();
  }
  // if position is consultant or employee, user must submit rate
  if ($position === "consultant" || $position === "employee") {
    if (empty($rate)) {
      header("location: https://msnha.una.edu/timesheet-form?error=emptyrate");
      exit();
    } else {
      // check if rate is not a number
        if (!is_numeric($rate)) {
        header("location: https://msnha.una.edu/timesheet-form?error=rateisnotnumeric");
        exit();          
      }
    }
  } 
  // if position is consultant, user must upload an invoice
  if ($position === "consultant") {
    if ($invoice["name"] === "") { // they didn't upload invoice
      header("location: https://msnha.una.edu/timesheet-form?error=MSNHAlackofinvoice");
      exit();
    } else { // yes, they did
      // check file error
      if ($invoice["error"] !== UPLOAD_ERR_OK) { // there's error
        print_r($invoice["error"]);
      // no error
      } else { 
        if (!check_file_type($invoice["tmp_name"])) {
          header("location: https://msnha.una.edu/timesheet-form?error=invoice=wrongtype");
          exit();
        }
        if ($invoice["size"] > 2097152) {
          header("location: https://msnha.una.edu/timesheet-form?error=invoice=filetoobig?2mbmax");
          exit();        
        }
        // get location path for invoice
        $invoice_uploaded_dir = wp_upload_dir();
        $current_month = date('m');
        $current_year = date('Y');
        $target_dir = $invoice_uploaded_dir['basedir'] . "/timesheet-files-upload" . "/" . $current_year . "/" . $current_month;

        // Create the target directory if it doesn't exist
        wp_mkdir_p($target_dir);

        $invoice_base_name = basename($invoice["name"]);
        $invoice_file_path = $target_dir . "/" . rawurlencode($invoice_base_name);
  
        // check if move file to wp upload location successfully
        if (!move_uploaded_file($invoice["tmp_name"], $invoice_file_path)) {
          header("location: https://msnha.una.edu/timesheet-form?error=invoice=uploadunsuccessfully");
          exit();
        } else { // move successfully
          $invoice_path = $invoice_file_path;
        }
      }
    }
  // if they are volunteer or non-federal employee, they don't need upload the invoice
  } else {
    $invoice_path = NULL;
  }
  // check if date is empty
  if (empty($date)) {
    header("location: https://msnha.una.edu/timesheet-form?error=emptydate");
    exit();
  } else {
    // check if date meet the pattern
    if (check_date_pattern($date) != 1) {
      header("location: https://msnha.una.edu/timesheet-form?error=wrongdatepattern");
      exit();
    }
  }
  // check if pj name is empty
  if (empty($project)) {
    header("location: https://msnha.una.edu/timesheet-form?error=emptypjname");
    exit();
  }
  // check if length of time is empty
  if (empty($time_length)) {
    header("location: https://msnha.una.edu/timesheet-form?error=emptytime");
    exit("Time length is required.");
  } else { // check if time length meets pattern: numeric
    if (!is_numeric($time_length)) {
      header("location: https://msnha.una.edu/timesheet-form?error=timeisnotnumber");
      exit();  
    }
  } 
  // check if desc is empty
  if(empty($desc)) {
    header("location: https://msnha.una.edu/timesheet-form?error=emptydesc");
    exit();
  }
  // check if agenda file is uploaded
  if ($agenda["name"] !== "") {
    // case no error
    if ($agenda["error"] === UPLOAD_ERR_OK) {
      if (!check_file_type($agenda["tmp_name"])) {
        header("location: https://msnha.una.edu/timesheet-form?error=agenda=wrongtype");
        exit();
      }
      if ($agenda["size"] > 2097152) {
        header("location: https://msnha.una.edu/timesheet-form?error=agenda=filetoobig?2mbmax");
        exit();
      }  
      // get location path for agenda
      $agenda_uploaded_dir = wp_upload_dir();
      $current_month = date('m');
      $current_year = date('Y');
      $target_dir = $genda_uploaded_dir['basedir'] . "/timesheet-files-upload" . "/" . $current_year . "/" . $current_month;

      // Create the target directory if it doesn't exist
      wp_mkdir_p($target_dir);

      $agenda_base_name = basename($agenda["name"]);
      $agenda_file_path = $target_dir . "/" . rawurlencode($agenda_base_name);

      // check if move file to wp upload location successfully
      if (!move_uploaded_file($agenda["tmp_name"], $agenda_file_path)) {
        header("location: https://msnha.una.edu/timesheet-form?error=agenda=uploadunsuccessfully");
        exit();
      } else {
        $agenda_path = $agenda_file_path;
      }
    // case there's error 
    } else { 
      print_r($agenda["error"]);
    }
  // if they didn't upload agenda file
  } else {
    $agenda_path = NULL;
  }
  // check if user enter miles traveled
  if (!empty($miles) && ($miles !== 0)) { // yes, they entered it
    // check is miles is numeric
    if (!is_numeric($miles)) {      
      header("location: https://msnha.una.edu/timesheet-form?error=mileisnotanumber");
      exit();
    }
    // check if they uploaded file or not
    if ($mile_file["name"] === "") { // no, they didn't
      header("location: https://msnha.una.edu/timesheet-form?error=lackofmileagefile");
      exit();
    } else { // yes, they did
      // check error
      if ($mile_file["error"] === UPLOAD_ERR_OK) { // no error
        if (!check_file_type($mile_file["tmp_name"])) {
          header("location: https://msnha.una.edu/timesheet-form?error=mile=wrongtype");
          exit();
        }
        if ($mile_file["size"] > 2097152) {
          header("location: https://msnha.una.edu/timesheet-form?error=mile=filetoobig?2mbmax");
          exit();
        }  
        // get location path for mile
        $mile_uploaded_dir = wp_upload_dir();
        $current_month = date('m');
        $current_year = date('Y');
        $target_dir = $mile_uploaded_dir['basedir'] . "/timesheet-files-upload" . "/" . $current_year . "/" . $current_month;

        // Create the target directory if it doesn't exist
        wp_mkdir_p($target_dir);

        $mile_base_name = basename($mile_file["name"]);
        $mile_file_path = $target_dir . "/" . rawurlencode($mile_base_name);
  
        // check if move file to wp upload location successfully
        if (!move_uploaded_file($mile_file["tmp_name"], $mile_file_path)) {
          header("location: https://msnha.una.edu/timesheet-form?error=mile=uploadunsuccessfully");
          exit();
        } else {
          $mile_file_path = $mile_file_path;
        }
      // case there's error 
      } else { 
        print_r($mile_file["error"]);
      }
    }
  // miles traveled is not entered or equal to 0
  } else {
    $mile_file_path = NULL;
  }

  // insert to table
  $info = array(
    "employee_name" => $employee_name,
    "position" => $position,
    "rate" => $rate,
    "date" => $date,
    "project_name" => $project,
    "time_length" => $time_length,
    "desc" => $desc,
    "agenda_img" => $agenda_path,
    "miles" => $miles,
    "mile_img" => $mile_file_path,
    "invoice_img" => $invoice_path
  );

  global $wpdb;
  $timesheet = $wpdb->prefix . 'timesheet';
  $result = $wpdb->insert($timesheet, $info);
  if ($result === false) {
    echo "Error occurred while inserting data into the database.";
    echo $wpdb->last_error;
  } else {
    // echo "Submit successfully.";
    header("location: https://msnha.una.edu/auto-draft");
  }
}


