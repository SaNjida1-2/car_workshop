<?php
$conn = new mysqli("localhost","root","","car_workshop");

if(isset($_POST['mechanic_id']) && isset($_POST['slot_date']) && isset($_POST['slots'])){

    $mechanic_id = $_POST['mechanic_id'];
    $date = $_POST['slot_date'];
    $slots = $_POST['slots'];

    // Check if row already exists
    $check = $conn->query("
        SELECT id FROM mechanic_slots 
        WHERE mechanic_id='$mechanic_id' 
        AND slot_date='$date'
    ");

    if($check->num_rows > 0){
        // Update existing row
        $conn->query("
            UPDATE mechanic_slots 
            SET slots='$slots'
            WHERE mechanic_id='$mechanic_id' 
            AND slot_date='$date'
        ");
    } else {
        // Insert new row
        $conn->query("
            INSERT INTO mechanic_slots (mechanic_id, slot_date, slots)
            VALUES ('$mechanic_id', '$date', '$slots')
        ");
    }

    echo "success";
}
?>