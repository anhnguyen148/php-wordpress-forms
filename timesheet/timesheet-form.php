<?php 
/////////////////////////////////////////
//      TIMESHEET FORM VALIDATION      //
//             2023-06-18              //
//       Scripted by Anh Nguyen        //
/////////////////////////////////////////

require_once("../wp-load.php");
require_once("function.inc.php");

// check if POST method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  exit("POST request method is required!");

} else {
  $employee_name = sanitize_text_field($_POST["employee_name"]);
  $email = sanitize_text_field($_POST["email"]);
  $position = sanitize_text_field($_POST["position"]);
  $rate = sanitize_text_field($_POST["rate"]);
  $date = sanitize_text_field($_POST["date"]);
  $project = sanitize_text_field($_POST["project"]);  
  $time_length = sanitize_text_field($_POST["time"]);
  $desc = sanitize_text_field($_POST["description"]);
  $agenda = $_FILES["agendaImg"];
  $miles = sanitize_text_field($_POST["miles"]);
  $mile_file = $_FILES["mileageImg"];
  $timestamp = submit_time();

  // check if name is empty
  if (empty($employee_name)) {  
    $message = "Timesheet form: Lack of Name";
    error_log($message);  
    exit($message);
  }  
  // check if email is empty
  if (empty($email)) {  
    $message = "Timesheet form: Lack of Email";
    error_log($message);  
    exit($message);
  }
  // check if email is in right format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {  
    $message = "Timesheet form: Invalid Email";
    error_log($message);  
    exit($message);
  }
  // check if position is not consultant or volunteer or employee
  if (!check_position($position)) {  
    $message = "Timesheet form: Invalid Position";
    error_log($message);  
    exit($message);
  }
  // if position is consultant or employee, user must submit rate
  if ($position === "consultant" || $position === "employee") {
    if (empty($rate)) {  
      $message = "Timesheet form: Lack of Hourly Rate";
      error_log($message);  
      exit($message);
    } else {
      // check if rate is not a number
        if (!is_numeric($rate)) {  
          $message = "Timesheet form: Rate must be a valid number";
          error_log($message);  
          exit($message);     
      }
    }
  } else if ($position === "volunteer") {
    $rate = 25.25;
  }
  
  $full_position = "";
  if ($position === "consultant") {
    $full_position = "MSNHA consultant";
  } else if ($position === "employee") {
    $full_position = "non-federal employee";
  } else if ($position === "volunteer") {
    $full_position = "MSNHA volunteer";
  }
  // check if date is empty
  if (empty($date)) {  
    $message = "Timesheet form: Lack of Date";
    error_log($message);  
    exit($message);
  } else {
    // check if date meet the pattern
    if (check_date_pattern($date) != 1) {  
      $message = "Timesheet form: Wrong Date type yyyy/mm/dd";
      error_log($message);  
      exit($message);
    }
  }
  // check if pj name is empty
  if (empty($project)) {  
    $message = "Timesheet form: Lack of Project Name";
    error_log($message);  
    exit($message);
  }
  // check if length of time is empty
  if (empty($time_length)) {  
    $message = "Timesheet form: Time length is required";
    error_log($message);  
    exit($message);
  } else { // check if time length meets pattern: numeric
    if (!is_numeric($time_length)) {  
      $message = "Timesheet form: Time must be a valid number";
      error_log($message);  
      exit($message);
    }
  } 
  // check if desc is empty
  if(empty($desc)) {  
    $message = "Timesheet form: Lack of Description";
    error_log($message);  
    exit($message);
  }
  
  $mile_file_path = path_single_upload($mile_file, "timesheet-form", "mile"); 
   
  if ((!empty($miles)) && ($miles !== 0)) {
    if ($mile_file["name"] === "") {
      $message = "Timesheet form: Lack of Mile Image";
      error_log($message);  
      exit($message);
    }
  }
  $agenda_path = path_single_upload($agenda, "timesheet-form", "agenda");

  // insert to table
  $info = array(
    "employee_name" => $employee_name,
    "email" => $email,
    "position" => $full_position,
    "rate" => $rate,
    "date" => $date,
    "project_name" => $project,
    "time_length" => $time_length,
    "desc" => $desc,
    "agenda_img" => $agenda_path,
    "miles" => $miles,
    "mile_img" => $mile_file_path,
    "timestamp" => $timestamp
  );

  global $wpdb;
  $timesheet = $wpdb->prefix . "timesheet";
  $result = $wpdb->insert($timesheet, $info);
  if ($result === false) {
    echo "Error occurred while inserting data into the database.";
    error_log($wpdb->last_error);
  } else {
    header("location: https://msnha.una.edu/auto-draft");
  }
}


