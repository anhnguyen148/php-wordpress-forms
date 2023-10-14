<?php 
//////////////////////////////////////////
//     SPONSORSHIP FORM VALIDATION      //
//              2023-08-01              //
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
    $event_name = sanitize_text_field($_POST["event_name"]);
    $event_date = sanitize_text_field($_POST["event_date"]);
    $location = sanitize_text_field($_POST["location"]);
    $desc = sanitize_text_field($_POST["desc"]);
    $amount = sanitize_text_field($_POST["amount"]);
    $supporting_doc = $_FILES["supporting_doc"];
    $timestamp = submit_time();

    if (empty($org_name)) {
        $message = "Sponsorship form: Lack of Organization Name";
        error_log($message);
        exit($message);
    }
    if (empty($contact_name)) {
        $message = "Sponsorship form: Lack of Contact Name";
        error_log($message);
        exit($message);
    }
    if (empty($email)) {
        $message = "Sponsorship form: Lack of Contact Email";
        error_log($message);
        exit($message);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Sponsorship form: Invalid Email";
        error_log($message);
        exit($message);
    }
    if (empty($phone)) {
        $message = "Sponsorship form: Lack of Phone Number";
        error_log($message);
        exit($message);
    }
    if (check_phone_number($phone) != 1) {
        $message = "Sponsorship form: Invalid Phone Number";
        error_log($message);
        exit($message);
    }
    if (empty($website)) {
        $message = "Sponsorship form: Lack of Website";
        error_log($message);
        exit($message);
    }
    if (empty($event_name)) {
        $message = "Sponsorship form: Lack of Event Name";
        error_log($message);
        exit($message);
    }
    if (empty($event_date)) {
        $message = "Sponsorship form: Lack of Event Date";
        error_log($message);
        exit($message);
    } else {
        if (check_date_pattern($event_date) != 1) {
            $message = "Sponsorship form: Wrong Date type yyyy/mm/dd";
            error_log($message);
            exit($message);
        }
    }
    if (empty($location)) {
        $message = "Sponsorship form: Lack of Location";
        error_log($message);
        exit($message);
    }
    if (empty($desc)) {     
        $message = "Sponsorship form: Lack of Description";
        error_log($message);
        exit($message);
    }
    if (empty($amount)) {
        $message = "Sponsorship form: Lack of Amount Request";
        error_log($message);
        exit($message);
    }
    if ($supporting_doc["name"][0] === "") {
        $message = "Sponsorship form: Lack of Supporting Documents";
        error_log($message);
        exit($message);
    }
    $supporting_doc_array = path_multi_files($supporting_doc, "sponsorship-request-form", "supporting_doc");
    $info1 = array(
        "org_name" => $org_name,
        "contact_name" => $contact_name,
        "email" => $email,
        "phone" => $phone,
        "web" => $website,
        "event_name" => $event_name,
        "event_date" => $event_date,
        "location" => $location,
        "desc" => $desc,
        "amount" => $amount,
        "timestamp" => $timestamp
    );

    global $wpdb;
    $sponsorship = $wpdb->prefix . "sponsorship";
    $supporting_documents = $wpdb->prefix . "supporting_docs";
    
    // start the transaction
    $wpdb->query('START TRANSACTION');  
    try {
        $result = $wpdb->insert($sponsorship, $info1);
        if ($result === false) {
            error_log($wpdb->last_error);
            echo "Error occurred while inserting data into the database.<br>";
            echo $wpdb->last_error;
        } else {
            $last_inserted_id = $wpdb->insert_id;
        foreach ($supporting_doc_array as $el) {
            $info2 = array(
                "sponsorship_id" => $last_inserted_id,
                "path" => $el
            );
            $wpdb->insert($supporting_documents, $info2);
        }
        // Commit the transaction
        $wpdb->query('COMMIT');
        header("location: https://msnha.una.edu/auto-draft");
        }
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK'); // Rollback the transaction on error
        error_log("Error occurred: " . $e->getMessage());
    }
}