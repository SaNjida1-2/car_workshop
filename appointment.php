<?php
$conn = new mysqli("localhost","root","","car_workshop");

// date
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

?>

<!DOCTYPE html>
<html>
<head>
    <title>Car Workshop Appointment</title>
    <link rel="stylesheet" href="style_user.css">
</head>
<body>

<div class="container">

<h2 style="text-align:center;">Book Appointment</h2>

<form method="POST" action="book.php">

    <input type="text" name="name" placeholder="Full Name" required>
    <input type="text" name="address" placeholder="Address" required>
    <input type="text" name="phone" placeholder="Phone Number" pattern="[0-9]+" required>
    <input type="text" name="license" placeholder="Car License Number" required>
    <input type="text" name="engine" placeholder="Engine Number" pattern="[0-9]+" required>

    <!-- Date Picker -->
    <input type="date" name="date" id="datePicker"
       value="<?php echo $selected_date; ?>"
       min="<?php echo date('Y-m-d'); ?>"
       required>

    <!-- Mechanic Selection -->
    <select name="mechanic" id="mechanic_select" required>
        <option value="">Select Mechanic</option>

        <?php
        $mechs = $conn->query("
            SELECT 
                m.id,
                m.name,
                COALESCE(ms.slots, m.max_slots) AS total_slots,
                COUNT(a.id) AS booked
            FROM mechanics m
            LEFT JOIN mechanic_slots ms 
                ON m.id = ms.mechanic_id 
                AND ms.slot_date = '$selected_date'
            LEFT JOIN appointments a 
                ON m.id = a.mechanic_id 
                AND a.appointment_date = '$selected_date'
            GROUP BY m.id
            ORDER BY m.name
        ");

        while($mech = $mechs->fetch_assoc()) {

            $available = $mech['total_slots'] - $mech['booked'];

            if($available <= 0) {
                echo "<option disabled>".$mech['name']." (Fully booked)</option>";
            } else {
                echo "<option value='".$mech['id']."'>"
                    .$mech['name']." (".$available." slot"
                    .($available>1?'s':'')." left)</option>";
            }
        }
        ?>
    </select>

    <!-- Slot Dropdown -->
    <select name="time_slot" id="time_slot" required>
        <option value="">Select Time</option>
    </select>

    <button type="submit">Book Appointment</button>
</form>

<p style="text-align:center; margin-top:10px;">
    <a href="admin_login.php" style="color:#00e676; text-decoration:none;">Admin Login</a>
</p>

</div>

<script>
const mechanicSelect = document.getElementById('mechanic_select');
const datePicker = document.getElementById('datePicker');
const timeSlotSelect = document.getElementById('time_slot');

// Reload page when date changes
datePicker.addEventListener('change', function() {
    window.location.href = "appointment.php?date=" + this.value;
});

mechanicSelect.addEventListener('change', updateTimeSlots);

function updateTimeSlots() {

    const mechanicId = mechanicSelect.value;
    const date = datePicker.value;

    if(!mechanicId || !date){
        timeSlotSelect.innerHTML = '<option value="">Select Time</option>';
        return;
    }

    fetch(`get_slots.php?mechanic_id=${mechanicId}&date=${date}`)
    .then(response => response.json())
    .then(slots => {

        timeSlotSelect.innerHTML = '<option value="">Select Time</option>';

        slots.forEach(slot => {
            const opt = document.createElement('option');
            opt.value = slot;
            opt.text = slot;
            timeSlotSelect.appendChild(opt);
        });

    })
    .catch(err => console.error(err));
}
</script>

</body>
</html>

<?php $conn->close(); ?>