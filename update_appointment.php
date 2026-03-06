<?php

$conn = new mysqli("localhost","root","","car_workshop");

$id = $_POST['id'];
$mechanic = $_POST['mechanic'];
$slot = $_POST['time_slot'];

$stmt = $conn->prepare("
UPDATE appointments 
SET mechanic_id=?, time_slot=? 
WHERE id=?
");

$stmt->bind_param("isi",$mechanic,$slot,$id);
$stmt->execute();

echo "
<script>
alert('Appointment Updated!');
window.location='admin_dashboard.php';
</script>
";

?>