<?php 
//////////////////////////////////////////
//     GRANT REPORT FORM VALIDATION     //
//              2023-08-02              //
//        Scripted by Anh Nguyen        //
//////////////////////////////////////////

require_once("../wp-load.php");
require_once("function.inc.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  exit("POST request method is required!");

} else {
  $org_name = sanitize_text_field($_POST["org_name"]);
  $contact_name = sanitize_text_field($_POST["contact_name"]);
  $email = sanitize_text_field($_POST["email"]);
  $phone = sanitize_text_field($_POST["phone"]);
  $address = sanitize_text_field($_POST["address"]);
  $addendum_num = sanitize_text_field($_POST["addendum_num"]);
  $desc = sanitize_text_field($_POST["desc"]);
  $video = sanitize_text_field($_POST["videos"]);
  $pictures = $_FILES["pictures"];
  $docs = $_FILES["docs"];
  $budget = $_FILES["budget"];
  $backup_docs = $_FILES["backup_docs"];
  $invoice = $_FILES["invoice"];
  $type1 = sanitize_text_field($_POST["type1"]);
  $grant_received = $_FILES["letter1"];
  $type2 = sanitize_text_field($_POST["type2"]);
  $partnership = $_FILES["letter2"];
  $timestamp = submit_time();

  if (empty($org_name)) {
    $message = "Grant Report: Lack of Organization Name";
    error_log($message);
    exit($message);
  }
  if (empty($contact_name)) {
    $message = "Grant Report: Lack of Contact Name";
    error_log($message);
    exit($message);
  }
  if (empty($email)) {
    $message = "Grant Report: Lack of Contact Email";
    error_log($message);
    exit($message);
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "Grant Report: Invalid Email";
    error_log($message);
    exit($message);
  }
  if (empty($phone)) {
    $message = "Grant Report: Lack of Phone Number";
    error_log($message);
    exit($message);
  }
  if (check_phone_number($phone) != 1) {
    $message = "Grant Report: Invalid Phone Number";
    error_log($message);
    exit($message);
  }
  if (empty($address)) {
    $message = "Grant Report: Lack of Address";
    error_log($message);
    exit($message);
  }
  if (empty($addendum_num)) {
    $message = "Grant Report: Lack of Addendum Number";
    error_log($message);
    exit($message);
  }
  if (empty($desc)) {
    $message = "Grant Report: Lack of Description";
    error_log($message);
    exit($message);
  }
  $pictures_path_array = path_multi_files($pictures, "grant-report", "pictures");
  if (empty($pictures_path_array)) {
    $message = "Grant Report: Lack of Pictures";
    error_log($message);
    exit($message);
  }
  $docs_path_array = path_multi_files($docs, "grant-report", "docs");
  $budget_path = path_single_upload($budget, "grant-report", "docs");
  if ($budget_path === NULL) {
    $message = "Grant Report: Lack of Budget file";
    error_log($message);
    exit($message);
  }
  $backup_docs_array = path_multi_files($backup_docs, "grant-report", "backup-docs");
  if (empty($backup_docs_array)) {
    $message = "Grant Report: Lack of Back up documentation (invoices, contracts, gig sheets, cancelled checks, in kind forms, timesheets, etc.)";
    error_log($message);
    exit($message);
  }
  $invoice_path = path_single_upload($invoice, "grant-report", "invoice");
  if ($invoice_path === NULL) {
    $message = "Grant Report: Lack of Invoice";
    error_log($message);
    exit($message);
  }
  $grant_received_letter = path_single_upload($grant_received, "grant-report", "grant-received-letter");
  if ($type1 !== "") {
    if ($grant_received_letter === NULL) {
      $message = "Grant Report: If you receive grant from the MSNHA, you must include supporting documentation";
      error_log($message);
      exit($message);
    }
  }
  $partnership_path = path_single_upload($partnership, "grant-report", "partnership-letter");

  if ($type1 === "grant-received") {
    $type1 = "yes";
  } else {
    $type1 = "no";
  }
  if ($type2 === "partnership") {
    $type2 = "yes";
  } else {
    $type2 = "no";
  }

  $info1 = array(
    "org_name" => $org_name,
    "contact_name" => $contact_name,
    "email" => $email,
    "phone" => $phone,
    "address" => $address,
    "addendum" => $addendum_num,
    "desc" => $desc,
    "video" => $video,
    "budget" => $budget_path,
    "invoice" => $invoice_path,
    "grant_received" => $type1,
    "partnership" => $type2,
    "letter1" => $grant_received_letter,
    "letter2" => $partnership_path,
    "timestamp" => $timestamp
  );

  global $wpdb;
  $grant_report = $wpdb->prefix . "grant_report";
  $pictures = $wpdb->prefix . "pictures";
  $docs = $wpdb->prefix . "docs";
  $backup_docs = $wpdb->prefix . "backup_docs";
  
  //start the transaction
  $wpdb->query('START TRANSACTION');  
  try {
    $result = $wpdb->insert($grant_report, $info1);
    if ($result === false) {
      error_log($wpdb->last_error);
      echo "Error occurred while inserting data into the database.";
    } else {
      $last_inserted_id = $wpdb->insert_id;
      foreach ($pictures_path_array as $picture) {
        $info2 = array(
          "report_id" => $last_inserted_id,
          "path" => $picture
        );
        $wpdb->insert($pictures, $info2);
      }
      foreach ($docs_path_array as $doc) {
        $info3 = array(
          "report_id" => $last_inserted_id,
          "path" => $doc
        );
        $wpdb->insert($docs, $info3);
      }
      foreach ($backup_docs_array as $backup_doc) {
        $info4 = array(
          "report_id" => $last_inserted_id,
          "path" => $backup_doc
        );
        $wpdb->insert($backup_docs, $info4);
      }
      // Commit the transaction
      $wpdb->query('COMMIT');
      header("location: https://msnha.una.edu/auto-draft");
    }
  } catch (Exception $e) {
    $wpdb->query('ROLLBACK'); // Rollback the transaction on error
    error_log('Error occurred: ' . $e->getMessage());
  }
}