<?php
session_start();

// logout
if(isset($_POST['logout'])){
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$conn = new mysqli("localhost","root","","car_workshop");

// Add new mechanic
if(isset($_POST['add_mechanic'])){
    $name = $_POST['mechanic_name'];
    $max = $_POST['max_slots'];

    $stmt = $conn->prepare("INSERT INTO mechanics (name, max_slots) VALUES (?,?)");
    $stmt->bind_param("si",$name,$max);
    $stmt->execute();
}

// delete mechanic
if(isset($_POST['delete_id'])){
    $id = $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM mechanics WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
}

// delete appointment
if(isset($_POST['delete_appointment'])){
    $id = $_POST['appointment_id'];

    $stmt = $conn->prepare("DELETE FROM appointments WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
}

// fetch appointments
$appointments = $conn->query("
SELECT appointments.*, mechanics.name AS mechanic_name
FROM appointments
JOIN mechanics ON appointments.mechanic_id = mechanics.id
ORDER BY appointment_date, time_slot
");

// fetch mechanic
$mechanics = $conn->query("SELECT * FROM mechanics");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Panel</title>
<link rel="stylesheet" href="style_admin.css">
</head>

<body>

<div class="container">

<h2>Appointment List</h2>

<table border="1" width="100%">
<tr>
<th>Name</th>
<th>Phone</th>
<th>License</th>
<th>Date</th>
<th>Time Slot</th>
<th>Mechanic</th>
<th>Action</th>
</tr>

<?php while($row = $appointments->fetch_assoc()): ?>
<tr>

<td><?= $row['client_name'] ?></td>
<td><?= $row['phone'] ?></td>
<td><?= $row['car_license'] ?></td>
<td><?= $row['appointment_date'] ?></td>
<td><?= $row['time_slot'] ?></td>
<td><?= $row['mechanic_name'] ?></td>

<td>

<a href="edit_appointment.php?id=<?= $row['id'] ?>">
<button type="button">Edit</button>
</a>

<form method="POST" style="display:inline;">
<input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
<button type="submit" name="delete_appointment">Delete</button>
</form>

</td>

</tr>
<?php endwhile; ?>
</table>

<br><br>

<h2>Manage Mechanics</h2>

<!-- add mechanic -->
<form method="POST" style="margin-bottom:15px;">
<input type="text" name="mechanic_name" placeholder="Mechanic Name" required>
<input type="number" name="max_slots" placeholder="Max slots per day" required>
<button type="submit" name="add_mechanic">Add Mechanic</button>
</form>

<table border="1" width="100%">

<tr>
<th>Mechanic Name</th>
<th>Current Max Slots</th>
<th>Update Slots for Date</th>
<th>Delete Mechanic</th>
</tr>

<?php while($row = $mechanics->fetch_assoc()): ?>

<tr>

<td><?= $row['name'] ?></td>
<td><?= $row['max_slots'] ?></td>

<td>

<input type="date" class="slot_date" data-mechanic="<?= $row['id'] ?>">

<div class="slot_control" style="display:flex; align-items:center; gap:5px; margin-top:5px;">

<button type="button" class="minus">-</button>

<input type="text" class="slot_number"
value="<?= $row['max_slots'] ?>"
title="Number of slots left"
readonly
style="width:40px; text-align:center;">

<button type="button" class="plus">+</button>

<button type="button"
class="update_btn"
data-mechanic="<?= $row['id'] ?>">
Update
</button>

</div>

</td>

<td>

<form method="POST">
<input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
<button type="submit">Delete</button>
</form>

</td>

</tr>

<?php endwhile; ?>

</table>

<br>

<!-- logout -->
<form method="POST">
<button name="logout" type="submit">Logout</button>
</form>

</div>

<script>

// Fetch slots for selected date
document.querySelectorAll('.slot_date').forEach(dateInput => {

dateInput.addEventListener('change', () => {

const mechanicId = dateInput.dataset.mechanic;
const date = dateInput.value;

const slotNumberInput =
dateInput.parentElement.querySelector('.slot_number');

if(!date) return;

fetch(`get_slots.php?mechanic_id=${mechanicId}&date=${date}`)
.then(res => res.json())
.then(data => {

if(Array.isArray(data))
slotNumberInput.value = data.length;
else
slotNumberInput.value = 0;

})
.catch(err => console.error(err));

});

});

// Plus button
document.querySelectorAll('.plus').forEach(btn => {

btn.addEventListener('click', () => {

const input = btn.parentElement.querySelector('.slot_number');

let value = parseInt(input.value);
if(isNaN(value)) value = 0;

input.value = value + 1;

});

});

// Minus button
document.querySelectorAll('.minus').forEach(btn => {

btn.addEventListener('click', () => {

const input = btn.parentElement.querySelector('.slot_number');

let value = parseInt(input.value);
if(isNaN(value)) value = 0;

input.value = Math.max(value - 1, 0);

});

});

// Update slot button
document.querySelectorAll('.update_btn').forEach(btn => {

btn.addEventListener('click', () => {

const mechanicId = btn.dataset.mechanic;

const dateInput =
btn.parentElement.parentElement.querySelector('.slot_date');

const slots =
btn.parentElement.querySelector('.slot_number').value;

const date = dateInput.value;

if(!date)
return alert("Select a date first!");

fetch('update_slots.php', {

method: 'POST',

headers:{
'Content-Type':'application/x-www-form-urlencoded'
},

body:
`mechanic_id=${mechanicId}&slot_date=${date}&slots=${slots}`

})

.then(res => res.text())
.then(resp => alert("Slots updated successfully!"))
.catch(err => console.error(err));

});

});

</script>

</body>
</html>