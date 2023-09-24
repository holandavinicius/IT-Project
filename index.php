<?php
session_start();

$users = file('src/users.txt',FILE_IGNORE_NEW_LINES);

/*
Users:
1 - login: admin - password: 123 - privilege: 0(admin)
2 - login: security - password: tecnologiasdeinternet - privilege: 1(security)
3 - login: user1 - password: arduinov - privilege:  2(employee)
4 - login: employee - password: learningiot - privilege: 2(employee)

*/

// Method Post/Redirect/Get to remove form resubmission

$request_method = strtoupper($_SERVER['REQUEST_METHOD']);

if ($request_method === 'POST') {
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['password'] = $_POST['password'];

    header('Location: index.php', true, 303);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/loginpage.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="src/favicon.svg">
    <title>Login</title>
</head>

<body>
    <!-- Background Image -->
    <div class="bg-img" alt="Blue and Purple Waves"></div>

    <!-- Logo Image -->
    <div class="container">
        <div class="divLogoImage">
        <img class="logo fade-in" src="src/favicon.svg" alt="asd">
    </div>

    <!-- Login Form -->
    <form name="loginForm" onsubmit="return validation()" method="post">
        <h1>Log In</h1>
        <label id="usernameLabel" for="username">
            <input type="text" id="username" placeholder="username" name="username">
            <span>Username</span>
        </label>
        <div id="warningUser" class="warningUser">
            <div class="warningIcon"></div>
            <em id="emptyUser" class="fail">Username can't be empty!</em>
        </div>

        <label id="passwordLabel" for="password">
            <input type="password" id="password" placeholder="Password" name="password">
            <span>Password</span>
        </label>
        <div id="warningPass" class="warningPass">
                <div class="warningIcon"></div>
                <em name="emptyPass" class="fail">Password cant be empty!</em>
            </div>

        <!-- Login Validation-->
        <?php
            //Code to verify if any combination of user and password are in users.txt file

            for($i=0;$i<count($users);$i+=3)
            {
                if(strcmp($_SESSION['username'],$users[$i]) == 0 &&  password_verify($_SESSION['password'],$users[$i+1]))
                {
                    $_SESSION['logged'] = true;
                    $_SESSION['userindex'] = $i;
                    $_SESSION['privilege'] = $users[$i+2];
                    header('location: dashboard.php'); 
                }
            }
            
            //Display warning if username and password mismatch
            if(isset($_SESSION['username']))
            {
                echo '<div class="faildiv"><div class="warningIcon"></div><em class="fail">Username or Password Incorrect!</em></div>';
            } 
        ?>
        <button type="submit">LOGIN</button>
    </form>
    

    <!-- Scripts Load -->
    <script src="js/formvalidation.js"></script>
    <script src="js/fadein.js"></script>
</body>

</html>