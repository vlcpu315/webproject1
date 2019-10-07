<?php
require 'includes/functions.php';

if(count($_POST) > 0 && $_GET['from'] == 'login')
if(count($_POST) > 0 && $_GET['from'] == 'login')
{
    // assume not found
    $found   = false;
    $foundAdmin = false;
    $user = trim($_POST['user']);
    $pass = trim($_POST['password']);
    //function to find admin
    $foundAdmin = findAdmin($user,$pass,'username');
    echo $foundAdmin;
    if(checkUsername($user))
    {
        $found = findUser($user, $pass, 'username');
    }
    elseif(checkPhoneNumber($user))
    {
        $found = findUser($user, $pass, 'phone');
    }

    if($found)
    {
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user;
        header('Location: thankyou.php?type=login&username='.$user);
    }elseif($foundAdmin){
        //check admin login or not
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['admin'] = true;
        header('Location: thankyou.php?type=login&username='.$user);
    }
    else
    {
        setcookie('error_message', 'Login not found! Try again.');
        header('Location: login.php');
    }

    exit();
}
elseif(count($_POST) > 0 && $_GET['from'] == 'signup')
{
    $check = checkSignUp($_POST);

    if($check !== true)
    {
        setcookie('error_message', $check);
        header('Location: signup.php');
    }
    else
    {
        if(saveUser($_POST))
        {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = filterUserName(trim($_POST['username']));
            header('Location: thankyou.php?type=signup&username='.trim($_POST['username']));
        }
        else
        {
            setcookie('error_message', 'Unable to sign up at this time.');
            header('Location: signup.php');
        }
    }

    exit();
}

// should never reach here but if we do, back to index they go
header('Location: index.php');
exit();