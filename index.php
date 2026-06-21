<!DOCTYPE html>
<html>
<head>
    <title>SMART MOBILITY CORRIDOR</title>
   <style>
   
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(rgba(12, 15, 28, 0.35), rgba(12, 15, 28, 0.35)), url('wap1.jpg') no-repeat center center fixed;
        background-size: cover;
        margin: 0;
        padding: 10px;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-container{
        background: rgba(255, 255, 255, 0.16);
        width: 400px;
        margin: 20px auto;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        color: #f7f9fb;
    }

    h2 {
        text-align: center;
        color: #211bd9;
        margin-top: 0;
        margin-bottom: 20px;
    }

    label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
    }

    input:focus,
    select:focus {
        border-color: #3498db;
        outline: none;
    }

    input[type="submit"] {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #2980b9;
    }

    .required {
        color: red;
    }
</style>
</head>
<body>
<div class="form-container">
<h2>SMART MOBILITY CORRIDOR</h2>


<form action="register.php" method="POST" onsubmit="return validateForm()">

   <input type="text" id="fname" name="fname" placeholder="Enter First Name" required>
<br><br>
    
    <input type="text" id="mname" name="mname" placeholder="Enter Middle Name" required><br><br>

    <input type="text" id="sname" name="sname" placeholder="Enter Last Name" required><br><br>

    <input type="email" id="email" name="email" placeholder="Enter Email Address" required><br><br>

    <input type="text" id="phone" name="phone" placeholder="Enter Phone Number" required><br><br>

     

    <div class="form-group">
            <label>Select Category</label>
            <select name="role" id="role" required>
                <option value="">-- Choose Role --</option>
                <option value="farmer">Farmer</option>
                <option value="driver">Driver</option>
                <option value="buyer">Buyer</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password" required>
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <div>
    <input type="submit" value="Register">

    <a href="welcome.php"></a>
        </div>
        <div class="login-link">
        Already have an account?
        <a href="login.php">Login</a>
    </div>
</form>
</div> 
<script>
 

    function validateForm() {

    let fname = document.getElementById("fname").value;
    let mname = document.getElementById("mname").value;
    let sname = document.getElementById("sname").value;
    let email = document.getElementById("email").value;
    let phone = document.getElementById("phone").value;
    let role = document.getElementById("role").value;
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirm_password").value;

    if(fname == "") {
        alert("Please enter First Name");
        return false;
    }

    if(mname == "") {
        alert("Please enter Middle Name");
        return false;
    }

    if(sname == "") {
        alert("Please enter Last Name");
        return false;
    }

    if(email == "") {
        alert("Please enter Email Address");
        return false;
    }

    if(phone == "") {
        alert("Please enter Phone Number");
        return false;
    }

    if(role == "") {
        alert("Please select Role");
        return false;
    }

    if(password == "") {
        alert("Please enter Password");
        return false;
    }

    if(password !== confirmPassword) {
        alert("Passwords do not match");
        return false;
    }

    return true;

}
</script>

</body>
</html>