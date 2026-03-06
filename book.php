<?php
$conn = new mysqli("localhost","root","","car_workshop");

/* Validate data */
if(
    empty($_POST['name']) ||
    empty($_POST['phone']) ||
    empty($_POST['date']) ||
    empty($_POST['mechanic']) ||
    empty($_POST['time_slot'])
){
    die("Invalid form submission!");
}

$name = $_POST['name'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$license = $_POST['license'];
$engine = $_POST['engine'];
$date = $_POST['date'];
$mechanic = (int)$_POST['mechanic'];
$time_slot = $_POST['time_slot'];

/*Check if same car already booked*/
$stmt = $conn->prepare("SELECT 1 FROM appointments WHERE car_license=? OR engine_number=?");
$stmt->bind_param("ss", $license, $engine);
$stmt->execute();
if($stmt->get_result()->num_rows > 0){
    die("This car already has an existing appointment in the system! (Same license plate or engine number)");
}
$stmt->close();

/*insert appointment*/
$stmt = $conn->prepare("
INSERT INTO appointments 
(client_name,address,phone,car_license,engine_number,appointment_date,mechanic_id,time_slot)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssssis", $name, $address, $phone, $license, $engine, $date, $mechanic, $time_slot);
$stmt->execute();
$stmt->close();

echo "
<script>
alert('Appointment Successful!');
window.location='appointment.php';
</script>
";
?>