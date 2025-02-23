<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "mad_mobile_app");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt password
$department = $_POST['department'];
$year = $_POST['year'];
$batch = $_POST['batch'];

// Generate Unique Student ID (Format: DEPT-BATCH-YEAR-XXX)
$dept_code = [
    "B.SC CS" => "CS",
    "B.SC CA" => "CA",
    "OTHER"   => "OT"
];

$dept_abbr = isset($dept_code[$department]) ? $dept_code[$department] : "OT";
$batch_year = str_replace("-", "", $batch); // Remove hyphen
$year_abbr = ($year == "I") ? "1" : (($year == "II") ? "2" : "3");

// Find the last student ID with the same pattern
$sql = "SELECT student_id FROM stu_det WHERE student_id LIKE '$dept_abbr-$batch_year-$year_abbr%' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    preg_match('/\d+$/', $row['student_id'], $matches);
    $new_number = str_pad($matches[0] + 1, 3, "0", STR_PAD_LEFT);
} else {
    $new_number = "001";
}

$student_id = "$dept_abbr-$batch_year-$year_abbr$new_number";

// Insert into database
$sql = "INSERT INTO stu_det (student_id, name, email, phone, password, department, year, batch) 
        VALUES ('$student_id', '$name', '$email', '$phone', '$password', '$department', '$year', '$batch')";

if ($conn->query($sql) === TRUE) {
    // Redirect to WhatsApp group
    header("Location: https://chat.whatsapp.com/YOUR_GROUP_LINK");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
