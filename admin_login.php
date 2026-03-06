<?php
session_start();
?>

<link rel="stylesheet" href="style_admin.css">

<div class="container">
<h2>Admin Login</h2>

<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
<br>

<a href="index.html">
<button type="button">Back to Homepage</button>
</a>
</div>

<?php
$conn = new mysqli("localhost","root","","car_workshop");

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $u=$_POST['username'];
    $p=$_POST['password'];

    $res=$conn->query("SELECT * FROM admin WHERE username='$u' AND password='$p'");
    if($res->num_rows>0){
        $_SESSION['admin']=true;
        header("Location: admin.php");
    } else {
        echo "<script>alert('Incorrect Username or Password!');</script>";
    }
}
?>