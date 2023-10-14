<?php
/////////////////////////////////////////
//       FUNCTIONS FOR VALIDATION      //
//             2023-06-18              //
//        Scripted by Anh Nguyen       //
/////////////////////////////////////////

// check date pattern
function check_date_pattern($date) {
  $pattern = "/^\d{4}\-\d{2}\-\d{2}$/";
  return preg_match($pattern, $date);
}

// check if their position is on allowed list
function check_position($user) {
  $positions_list = array("volunteer", "consultant", "employee");
  return in_array($user, $positions_list);
}

// check upload file type
function check_file_type($file) {
  $allowed_types = array ( "application/pdf", "image/jpeg", "image/png", "image/jpg" );
  $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
  $detected_type = finfo_file($fileInfo, $file);
  return in_array($detected_type, $allowed_types);
  finfo_close( $fileInfo );
}

// check phone number 
function check_phone_number($phone) {
  $allowed_pattern = "/^[0-9]{10}+$/";
  return preg_match($allowed_pattern, $phone);
}

// check if the organization type is on allowed list
function check_organization_type($org) {
  $organization_types_list = array("none profit", "local business", "school", "gov", "tourism office", "other");
  return in_array($org, $organization_types_list);
}

// check if the project type is on allowed list
function check_project_type($pj) {
  $project_types_list = array("museum", "historic", "interpretation", "workshop", "history", "event", "art", "recreation", "environmental");
  return in_array($pj, $project_types_list);
}

// check if the project theme is on allowed list
function check_project_theme($theme) {
  $project_themes_list = array("tennessee", "music", "heritage", "other");
  return in_array($theme, $project_themes_list);
}

function submit_time() {
  date_default_timezone_set("America/Chicago");
  return date("m/d/Y H:i:s");
}

// move and get path of upload file (SINGLE FILE)
function path_single_upload($file, $form_name, $name) {
  // if file uploaded
  if ($file["name"] != "") {
    // case no error
    if ($file["error"] === UPLOAD_ERR_OK) {
      if (!check_file_type($file["tmp_name"])) {
        $message = "{$form_name}: {$name} - PDF or Image Only";
        error_log($message);
        exit($message);
      }
      if ($file["size"] > 2097152) {
        $message = "{$form_name}: {$name} - File too big, 2MB max";
        error_log($message);
        exit($message);
      }
      // get location path for agenda
      $uploaded_dir = wp_upload_dir();
      $current_month = date("m");
      $current_year = date("Y");
      $target_dir = $uploaded_dir["basedir"] . '/' . $form_name . "/" . $current_year . "/" . $current_month;

      // Create the target directory if it doesn't exist
      wp_mkdir_p($target_dir);
      $base_name = basename($file["name"]);
      $file_path = $target_dir . '/' . $base_name;
      $file_info = pathinfo($file_path);
      $index = 1;  
      while (file_exists($file_path)) {
        $base_name = $file_info['filename'] . '-' . $index . '.' . $file_info['extension'];
        $file_path = $target_dir . "/" . $base_name;
        $index++;
      }
      // check if move file to wp upload location successfully
      if (!move_uploaded_file($file["tmp_name"], $file_path)) {
        $message = "{$form_name}: {$name} - Upload Unsuccessfully.";
        error_log($message);
        exit($message);
      } else {
        $file_path = str_replace("/home3/unalions/public_html/msnha", "https://msnha.una.edu", $file_path);
        return $file_path;
      }
    }
  } else {
    return NULL;
  }
}

function path_multi_files($raw_files_array, $form_name, $name) {
  $files_array = array();
  if ($raw_files_array["name"][0] === "") {
    return $files_array;
  }
  for ($i = 0; $i < count($raw_files_array["name"]); $i++) {
    if ($raw_files_array["error"][$i] !== UPLOAD_ERR_OK) {
      $message = "{$form_name}: {$name} - ERROR {$raw_files_array["error"][$i]}";
      error_log($message);
      exit($message);
    }
    if ($raw_files_array["error"][$i] === UPLOAD_ERR_OK) {
      if (!check_file_type($raw_files_array["tmp_name"][$i])) {
        $message = "{$form_name}: {$raw_files_array["name"][$i]} - PDF or Image Only";
        error_log($message);
        exit($message);
      }
      if ($raw_files_array["size"][$i] > 2097152) {
        $message = "{$form_name}: {$raw_files_array["name"][$i]} - file too large (Max 2MB).";
        exit();
      }

      $uploaded_dir = wp_upload_dir();                
      $current_month = date("m");
      $current_year = date("Y");
      $target_dir = $uploaded_dir["basedir"] . "/" . $form_name . "/" . $current_year . "/" . $current_month;

      // Create the target directory if it doesn't exist
      wp_mkdir_p($target_dir);
      $base_name = basename($raw_files_array["name"][$i]);
      $file_path = $target_dir . "/" . $base_name;
      $index = 1;
      $file_info = pathinfo($file_path);
      // check if file is existing
      while (file_exists($file_path)) {
        $base_name = $file_info['filename'] . '-' . $index . '.' . $file_info['extension'];
        $file_path = $target_dir . "/" . $base_name;
        $index++;
      }

      // check if move file to wp upload location successfully
      if (!move_uploaded_file($raw_files_array["tmp_name"][$i], $file_path)) {
        $message = "{$form_name}: {$raw_files_array["tmp_name"][$i]} - Upload Unsuccessfully.";
        error_log($message);
        exit($message);
      } else { // move successfully => add key-value pair to empty array to get path of every file
        $files_array[$i] = str_replace("/home3/unalions/public_html/msnha", "https://msnha.una.edu", $file_path);
      }
    }
  }
  return $files_array;
}

