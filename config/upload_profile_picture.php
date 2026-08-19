```php
<?php

session_start();

require_once "database.php";


/* =========================================================
   ERROR REPORTING
========================================================= */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/* =========================================================
   LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role_id'])
) {
    header("Location: ../authentication/login.php");
    exit();
}


/* =========================================================
   MANAGER / EMPLOYEE ONLY
========================================================= */

if (
    $_SESSION['role_id'] != 2 &&
    $_SESSION['role_id'] != 3
) {
    die("Unauthorized access.");
}


/* =========================================================
   CHECK FILE
========================================================= */

if (!isset($_FILES['profile_picture'])) {
    die("No profile picture was received.");
}

if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    die(
        "Upload failed. Error code: " .
        $_FILES['profile_picture']['error']
    );
}

$file = $_FILES['profile_picture'];


/* =========================================================
   FILE SIZE LIMIT
   2 MB
========================================================= */

$max_size = 2 * 1024 * 1024;

if ($file['size'] > $max_size) {
    die("Profile picture must be 2 MB or smaller.");
}


/* =========================================================
   CHECK REAL IMAGE
========================================================= */

$image_info = getimagesize($file['tmp_name']);

if ($image_info === false) {
    die("The uploaded file is not a valid image.");
}


/* =========================================================
   ALLOWED TYPES
========================================================= */

$allowed_types = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$mime_type = $image_info['mime'];

if (!isset($allowed_types[$mime_type])) {
    die("Only JPG, PNG and WebP images are allowed.");
}

$extension = $allowed_types[$mime_type];


/* =========================================================
   GET EMPLOYEE
========================================================= */

$user_id = (int) $_SESSION['user_id'];

$sql = "
    SELECT
        employee_id,
        profile_picture
    FROM employees
    WHERE user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Employee record not found for this logged-in user.");
}

$employee = $result->fetch_assoc();

$employee_id = (int) $employee['employee_id'];
$old_picture = $employee['profile_picture'] ?? '';



/* =========================================================
   FILE NAME
========================================================= */

$file_name =
    "employee_" .
    $employee_id .
    "_" .
    time() .
    "." .
    $extension;


/* =========================================================
   UPLOAD DIRECTORY
========================================================= */

$upload_dir =
    __DIR__ .
    "/../assets/images/profiles/";


if (!is_dir($upload_dir)) {

    if (!mkdir($upload_dir, 0755, true)) {
        die("Could not create profile image folder.");
    }

}


$destination =
    $upload_dir .
    $file_name;


/* =========================================================
   MOVE FILE
========================================================= */

if (!move_uploaded_file(
    $file['tmp_name'],
    $destination
)) {
    die("The image could not be saved to the profiles folder.");
}


/* =========================================================
   UPDATE DATABASE
========================================================= */

$sql = "
    UPDATE employees
    SET profile_picture = ?
    WHERE employee_id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "si",
    $file_name,
    $employee_id
);

$stmt->execute();


/* =========================================================
   VERIFY DATABASE UPDATE
========================================================= */

$check_sql = "
    SELECT profile_picture
    FROM employees
    WHERE employee_id = ?
    LIMIT 1
";

$check_stmt = $conn->prepare($check_sql);

$check_stmt->bind_param(
    "i",
    $employee_id
);

$check_stmt->execute();

$check_result = $check_stmt->get_result();

$check_row = $check_result->fetch_assoc();

if (
    !$check_row ||
    $check_row['profile_picture'] !== $file_name
) {

    if (file_exists($destination)) {
        unlink($destination);
    }

    die("Profile picture was uploaded but database update failed.");
}


/* =========================================================
   DELETE OLD IMAGE
========================================================= */

if (
    !empty($old_picture) &&
    $old_picture !== $file_name
) {

    $old_file =
        $upload_dir .
        basename($old_picture);

    if (
        file_exists($old_file) &&
        is_file($old_file)
    ) {
        unlink($old_file);
    }

}


/* =========================================================
   REDIRECT
========================================================= */

if ($_SESSION['role_id'] == 2) {

    header(
        "Location: ../manager/profile.php?success=picture"
    );

} else {

    header(
        "Location: ../employee/profile.php?success=picture"
    );

}

exit();

?>
```
