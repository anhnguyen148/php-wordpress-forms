<?php 
//////////////////////////////////////////
//   GRANT SUBMISSION FORM VALIDATION   //
//              2023-06-22              //
//        Scripted by Anh Nguyen        //
//////////////////////////////////////////

require_once("../wp-load.php");
require_once("function.inc.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  exit("POST request method is required!");

} else {
  $organization = sanitize_text_field($_POST["organization"]);
  $contact = sanitize_text_field($_POST["contact"]);
  $email = sanitize_text_field($_POST["email"]);
  $phone = sanitize_text_field($_POST["phone"]);
  $address = sanitize_text_field($_POST["address"]);
  $organization_type = sanitize_text_field($_POST["organizationType"]);
  $organization_type_note = sanitize_text_field($_POST["other-organization"]);
  $project_type = sanitize_text_field($_POST["projectType"]);
  $project_theme = sanitize_text_field($_POST["theme"]);
  $project_theme_note = sanitize_text_field($_POST["other-theme"]);
  $project_desc = sanitize_text_field($_POST["desc"]);
  $start_date = sanitize_text_field($_POST["startDate"]);
  $end_date = sanitize_text_field($_POST["endDate"]);
  $budget_file = $_FILES["budget"];
  $vendor_quote = $_FILES["vendorQuote"];
  $IRS = $_FILES["IRS"];
  $disclosure = $_FILES["disclosure"];
  $tax_exempt = $_FILES["tax-exempt"];
  $verified_letter = $_FILES["verifiedLetter"];
  $timestamp = submit_time();

  // check if organization name is empty
  if (empty($organization)) {
    $message = "Grant form: Lack of Organization Name";
    error_log($message);
    exit($message);
  }
  
  // check if contact is empty
  if (empty($contact)) {
    $message = "Grant form: Lack of Contact Name";
    error_log($message);
    exit($message);
  }
  
  // check if email is empty
  if (empty($email)) {
    $message = "Grant form: Lack of Contact Email";
    error_log($message);
    exit($message);
  }
    // check if email is in right format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "Grant form: Invalid Email";
    error_log($message);
    exit($message);
  }
  
  // check if phone is empty
  if (empty($phone)) {
    $message = "Grant form: Lack of Phone Number";
    error_log($message);
    exit($message);
  }
  if (check_phone_number($phone) != 1) {
    $message = "GRant form: Invalid Phone Number";
    error_log($message);
    exit($message);
  }
  
  // check if address is empty
  if (empty($address)) {
    $message = "Grant form: Lack of Address";
    error_log($message);
    exit($message);
  }

  // check if organization type is none profit or local business or school or gov or tourism or other.
  if (check_organization_type($organization_type != true)) {
    $message = "Grant form: Wrong Organization Type";
    error_log($message);
    exit($message);
  }
  // if user choose other options than OTHER => note = NULL
  if ($organization_type !== "other") {
    $organization_type_note = NULL;
  // else if user choose Other, then they must enter more info on input
  } else {
    // check if more info is empty
    if (empty($organization_type_note)) {
      $message = "Grant form: Please give more details about your organization type.";
      error_log($message);
      exit($message);
    }
  }

  // check if project type is not match
  if (check_project_type($project_type) != true) {
    $message = "Grant form: Wrong Project Type";
    error_log($message);
    exit($message);
  }

  // check if project theme is not match
  if (check_project_theme($project_theme) != true) {
    $message = "Grant form: Wrong Project Theme";
    error_log($message);
    exit($message);
  }
  // if user choose other options than OTHER => note = NULL
  if ($project_theme !== "other") {
    $project_theme_note = NULL;
    // else if user choose Other, then they must explain
  } else {
    // check if explanation is empty
    if (empty($project_theme_note)) {
      $message = "Grant form: Please explain more details about your project theme";
      error_log($message);
      exit($message);
    }
  }

  // check if project desc is empty
  if (empty($project_desc)) {
    $message = "Grant form: Lack of Project Description";
    error_log($message);
    exit($message);
  }
  
  // check if start date is empty
  if (empty($start_date)) {
    $message = "Grant form: Lack of Start Date";
    error_log($message);
    exit($message);
  // else if not empty, check pattern
  } else {
    if (check_date_pattern($start_date) != 1) {
      $message = "Grant form: Wrong Date type yyyy/mm/dd";
      error_log($message);
      exit($message);
    }
  }

  // check if end date is empty
  if (empty($end_date)) {
    $message = "Grant form: Lack of End Date";
    error_log($message);
    exit($message);
  // else if not empty, check pattern
  } else {
    if (check_date_pattern($end_date) != 1) {
      $message = "Grant form: Wrong Date type yyyy/mm/dd";
      error_log($message);
      exit($message);
    }
  }

  $budget_file = path_single_upload($budget_file, "grant-form", "budget");
  if ($budget_file == "") {
    $message = "Grant form: Lack of Budget file.";
    error_log($message);
    exit($message);
  }
  $IRS = path_single_upload($IRS, "grant-form", "IRS");
  if ($IRS == NULL) {
    $message = "Grant form: Lack of IRS W9 file.";
    error_log($message);
    exit($message);
  }
  $disclosure = path_single_upload($disclosure, "grant-form", "disclosure");
  $tax_exempt = path_single_upload($tax_exempt, "grant-form", "tax");
  $verified_letter = path_single_upload($verified_letter, "grant-form", "letter");
  if ($verified_letter == NULL) {
    $message = "Grant form: Lack of Verifired Letter.";
    error_log($message);
    exit($message);
  }
  $vendor_quotes_array = path_multi_files($vendor_quote, "grant-form", "vendor-quotes");
  if (empty($vendor_quotes_array)) {
    $message = "Grant form: Lack of Vendor quotes";
    error_log($message);
    exit($message);
  }

  $info1 = array(
    "organization_name" => $organization,
    "contact_name" => $contact,
    "contact_email" => $email,
    "contact_phone" => $phone,
    "contact_address" => $address,
    "org_type" => $organization_type,
    "org_type_note" => $organization_type_note,
    "project_type" => $project_type,
    "project_theme" => $project_theme,
    "project_theme_note" => $project_theme_note,
    "project_des" => $project_desc,
    "start_date" => $start_date,
    "end_date" => $end_date,
    "budget" => $budget_file,
    "irs" => $IRS,
    "disclosure" => $disclosure,
    "tax_exempt" => $tax_exempt,
    "verified_letter" => $verified_letter,
    "timestamp" => $timestamp
  );

  global $wpdb;
  $grant = $wpdb->prefix . "grant";
  $vendor_quotes = $wpdb->prefix . "vendor_quotes";
  
  // start the transaction
  $wpdb->query('START TRANSACTION');  
  try {
    $result = $wpdb->insert($grant, $info1);
    if ($result === false) {
      error_log($wpdb->last_error);
      echo "Error occurred while inserting data into the database.";
    } else {
      $last_inserted_id = $wpdb->insert_id;
      foreach ($vendor_quotes_array as $vendor_quote) {
        $info2 = array(
          "grant_id" => $last_inserted_id,
          "path" => $vendor_quote
        );
        $wpdb->insert($vendor_quotes, $info2);
      }
      // Commit the transaction
      $wpdb->query('COMMIT');
      header("location: https://msnha.una.edu/auto-draft");
    }
  } catch (Exception $e) {
    $wpdb->query('ROLLBACK'); // Rollback the transaction on error
    error_log('Error occurred: ' . $e->getMessage());
    echo "Error occurred while inserting data into the database.";
  }
}