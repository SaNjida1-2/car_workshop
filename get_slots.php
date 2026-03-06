<?php
$conn = new mysqli("localhost","root","","car_workshop");

header('Content-Type: application/json');

//Validate inputs
if (!isset($_GET['mechanic_id']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$mechanic_id = (int)$_GET['mechanic_id'];
$date = $_GET['date'];

// Check date-specific slots
$stmt = $conn->prepare("
    SELECT slots 
    FROM mechanic_slots 
    WHERE mechanic_id = ? 
    AND slot_date = ?
");
$stmt->bind_param("is", $mechanic_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $slots = (int)$row['slots'];
} else {

    //Fallback to default max_slots
    $stmt2 = $conn->prepare("
        SELECT max_slots 
        FROM mechanics 
        WHERE id = ?
    ");
    $stmt2->bind_param("i", $mechanic_id);
    $stmt2->execute();
    $default = $stmt2->get_result();

    if($default->num_rows > 0){
        $row = $default->fetch_assoc();
        $slots = (int)$row['max_slots'];
    } else {
        echo json_encode([]);
        exit;
    }
}

//Generate time slots
$startHour = 9;
$timeSlots = [];

for($i = 0; $i < $slots; $i++){
    $start = $startHour + $i;
    $end = $start + 1;
    $timeSlots[] = sprintf("%02d:00-%02d:00", $start, $end);
}

//Remove booked slots
$stmt3 = $conn->prepare("
    SELECT time_slot 
    FROM appointments 
    WHERE mechanic_id = ? 
    AND appointment_date = ?
");
$stmt3->bind_param("is", $mechanic_id, $date);
$stmt3->execute();
$bookedResult = $stmt3->get_result();

$booked = [];
while($b = $bookedResult->fetch_assoc()){
    $booked[] = $b['time_slot'];
}

//Calculate available slots
$available = array_values(array_diff($timeSlots, $booked));

echo json_encode($available);
?>