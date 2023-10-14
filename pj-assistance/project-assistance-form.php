<?php 
//////////////////////////////////////////
//    PJ ASSISTANCE FORM VALIDATION     //
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
    $website = sanitize_text_field($_POST["website"]);
    $location = sanitize_text_field($_POST["location"]);
    $desc = sanitize_text_field($_POST["desc"]);
    $assistance = sanitize_text_field($_POST["assistance"]);
    $timeline = sanitize_text_field($_POST["timeline"]);
    $work = sanitize_text_field($_POST["work"]);
    $resources = sanitize_text_field($_POST["resources"]);
    $staff = sanitize_text_field($_POST["staff"]);
    $timestamp = submit_time();

    if (empty($org_name)) {
        $message = "PJ Assistance form: Lack of Organization Name";
        error_log($message);
        exit($message);
    }
    if (empty($contact_name)) {
        $message = "PJ Assistance form: Lack of Contact Name";
        error_log($message);
        exit($message);
    }
    if (empty($email)) {
        $message = "PJ Assistance form: Lack of Contact Email";
        error_log($message);
        exit($message);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "PJ Assistance form: Invalid Email";
        error_log($message);
        exit($message);
    }
    if (empty($phone)) {
        $message = "PJ Assistance form: Lack of Phone Number";
        error_log($message);
        exit($message);
    }
    if (check_phone_number($phone) != 1) {
        $message = "PJ Assistance form: Invalid Phone Number";
        error_log($message);
        exit($message);
    }
    if (empty($website)) {
        $message = "PJ Assistance form: Lack of Website";
        error_log($message);
        exit($message);
    }
    if (empty($location)) {
        $message = "PJ Assistance form: Lack of Location";
        error_log($message);
        exit($message);
    }
    if (empty($desc)) {
        $message = "PJ Assistance form: Lack of Description";
        error_log($message);
        exit($message);
    }
    if (empty($assistance)) {
        error_log("PJ Assistance form: Lack of assistance needs");
        exit("PJ Assistance form: Lack of infomation: What specifically do you need assistance with?");
    }
    if (empty($timeline)) {
        error_log("PJ Assistance form: Lack of timeline");
        exit("PJ Assistance form: Lack of infomation: What is your timeline for completion of the project?");
    }
    if (empty($work)) {
        error_log("PJ Assistance form: Lack of work done");
        exit("PJ Assistance form: Lack of infomation: Has any work already been completed on the project? If so, please describe.");
    }
    if (empty($resources)) {
        error_log("PJ Assistance form: Lack of financial resources");
        exit("PJ Assistance form: Lack of infomation: Does the organization have financial resources to contribute to the project?");
    }
    if (empty($staff)) {
        error_log("PJ Assistance form: Lack of staff capacity");
        exit("PJ Assistance form: Lack of infomation: Does the organization have volunteer and/or staff capacity to contribute to the project? If so, are staff and/or volunteers willing to document their time spent on the project?");
    }

    $info = array(
        "org_name" => $org_name,
        "contact_name" => $contact_name,
        "email" => $email,
        "phone" => $phone,
        "web" => $website,
        "location" => $location,
        "desc" => $desc,
        "assistance" => $assistance,
        "timeline" => $timeline,
        "work" => $work,
        "resources" => $resources,
        "staff" => $staff,
        "timestamp" => $timestamp
    );

    global $wpdb;
    $pj_assistance = $wpdb->prefix . "pj_assistance";
    $result = $wpdb->insert($pj_assistance, $info);
    if ($result === false) {
        exit("Error occurred while inserting data into the database.");
        error_log("PJ Assistance form: " . $wpdb->last_error);
    } else {
        header("location: https://msnha.una.edu/auto-draft");
    }
}