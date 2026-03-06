<?php
$conn = new mysqli("localhost","root","","car_workshop");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

if(!isset($_GET['id'])){
    die("No appointment ID provided");
}

$id = $_GET['id'];

// get appointment info
$result = $conn->query("
    SELECT * 
    FROM appointments 
    WHERE id='$id'
");

if($result->num_rows == 0){
    die("Appointment not found");
}

$row = $result->fetch_assoc();

$date = $row['appointment_date'];
$current_mechanic = $row['mechanic_id'];
$current_slot = $row['time_slot'];

// get mechanics
$mechanics = $conn->query("SELECT * FROM mechanics");
?>
<?php
// Update appointment
if(isset($_POST['update'])){

    $mechanic = $_POST['mechanic'];
    $slot = $_POST['time_slot'];

    // Prevent double booking
    $check = $conn->query("
        SELECT * FROM appointments
        WHERE mechanic_id='$mechanic'
        AND appointment_date='$date'
        AND time_slot='$slot'
        AND id!='$id'
    ");

    if($check->num_rows > 0){
        echo "<script>alert('This slot is already booked for this mechanic');</script>";
    }else{

        $conn->query("
            UPDATE appointments
            SET mechanic_id='$mechanic',
                time_slot='$slot'
            WHERE id='$id'
        ");

        echo "<script>
        alert('Appointment Updated Successfully');
        window.location='admin.php';
        </script>";
        exit();
    }
}
?>
<style>

body{
    margin:0;
    font-family: Arial, sans-serif;

    background: url("edit.jpg") no-repeat center center/cover;
    height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;
}

/* Card container */
.container{
    background: rgba(255,255,255,0.9);
    padding:40px;
    border-radius:10px;
    width:350px;

    box-shadow:0 10px 25px rgba(0,0,0,0.3);
    text-align:center;
}

/* Heading */
h2{
    margin-bottom:20px;
}

/* Labels */
label{
    display:block;
    margin-top:10px;
    font-weight:bold;
}

/* Inputs */
select{
    width:100%;
    padding:8px;
    margin-top:5px;
    border-radius:5px;
    border:1px solid #ccc;
}

/* Button */
button{
    margin-top:20px;
    padding:10px;
    width:100%;

    background:#28a745;
    border:none;
    color:white;

    font-size:16px;
    border-radius:5px;
    cursor:pointer;
}

button:hover{
    background:#218838;
}

</style>
<div class="container">

<h2>Edit Appointment</h2>

<form method="POST">

<p><b>Date:</b> <?php echo $date; ?></p>

<input type="hidden" id="date" name="date" value="<?php echo $date; ?>">

<br>

<label>Mechanic</label>

<select name="mechanic" id="mechanic" required>

<?php
while($m = $mechanics->fetch_assoc()){

$selected = ($m['id'] == $current_mechanic) ? "selected" : "";

echo "<option value='{$m['id']}' $selected>{$m['name']}</option>";
}
?>

</select>

<br><br>

<label>Available Time Slots</label>

<select name="time_slot" id="slot" required>

<option value="<?php echo $current_slot; ?>">
<?php echo $current_slot; ?> (current)
</option>

</select>

<br><br>

<button type="submit" name="update">
Update Appointment
</button>

</form>
</div>
<script>

function loadSlots(){

const mechanic = document.getElementById("mechanic").value;
const date = document.getElementById("date").value;
const currentSlot = "<?php echo $current_slot; ?>";

fetch(`get_slots.php?mechanic_id=${mechanic}&date=${date}`)
.then(res => res.json())
.then(data => {

const slotSelect = document.getElementById("slot");

slotSelect.innerHTML = "";

/* keep current slot */
const currentOption = document.createElement("option");
currentOption.value = currentSlot;
currentOption.text = currentSlot + " (current)";
slotSelect.appendChild(currentOption);

/* add available slots */
data.forEach(slot => {

if(slot == currentSlot) return;

const opt = document.createElement("option");
opt.value = slot;
opt.text = slot;

slotSelect.appendChild(opt);

});

})
.catch(err => console.error(err));

}

document.getElementById("mechanic").addEventListener("change", loadSlots);

window.onload = loadSlots;

</script>