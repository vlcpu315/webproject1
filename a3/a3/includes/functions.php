<?php

define('SALT', 'a_very_random_salt_for_this_app');

/**
 * Look up the user & password pair from the text file.
 *
 * User can be the username or the phone number.
 * Passwords are simple md5 hashed.
 *
 * Remember, md5() is just for demonstration purposes.
 * Do not do this in production for passwords.
 *
 * @param $user string The username or phone number to look up
 * @param $password string The password to look up
 * @param $field string user|phone
 * @return bool true if found, false if not
 */
function findUser($user, $password, $field)
{
    $found = false;

    $lines = file('users.txt');

    foreach($lines as $line)
    {
        $pieces = preg_split("/\|/", $line); // | is a special character, so escape it

        if($field == 'username' && $pieces[0] == $user && trim($pieces[2]) == md5($password . SALT))
        {
            $found = true;
        }
        elseif($field == 'phone' && $pieces[1] == $user && trim($pieces[2]) == md5($password . SALT))
        {
            $found = true;
        }
    }

    return $found;
}



/**
 * Remember, md5() is just for demonstration purposes.
 * Do not do this in production for passwords.
 *
 * @param $data
 * @return bool returns false if fopen() or fwrite() fails
 */
function saveUser($data)
{
    $success = false;

    $fp = fopen('users.txt', 'a+');

    if($fp != false)
    {
        $username       = trim($data['username']);
        $phoneNumber    = trim(preg_replace("/[^0-9]/", '', $data['phoneNumber']));
        $password       = trim($data['password']);
        $passwordHash   = md5($password . SALT);

        $results = fwrite($fp, $username.'|'.$phoneNumber.'|'.$passwordHash. PHP_EOL);

        fclose($fp);

        if($results)
        {
            $success = true;
        }
    }

    return $success;
}

function checkUsername($username)
{
    return preg_match('/^[a-z]([a-z]|[0-9]){6}([a-z]|[0-9])*[0-9]+$/i', $username);
}

function checkPhoneNumber($phoneNumber)
{
    // assuming phone numbers can start with a 0
    return preg_match("/^[0-9]{7}$|^[0-9]{10}$/", $phoneNumber);
}

/**
 * @param $data
 * @return bool|string
 */
function checkSignUp($data)
{
    $valid = true;

    // if any of the fields are missing, return an error
    if( trim($data['username'])        == '' ||
        trim($data['phoneNumber'])     == '' ||
        trim($data['password'])        == '' ||
        trim($data['verify_password']) == '')
    {
        $valid = "All inputs are required.";
    }
    elseif(!preg_match('/^[a-z]([a-z]|[0-9]){6}([a-z]|[0-9])*[0-9]+$/i', trim($data['username'])))
    {
        $valid = "Invalid username!";
    }
    elseif(!preg_match("/^((\([0-9]{3}\))|([0-9]{3}))?( |-)?[0-9]{3}( |-)?[0-9]{4}$/", trim($data['phoneNumber'])))
    {
        $valid = "Invalid phone number!";
    }
    else if(!preg_match('/((?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[,.\/\?\*!])){8}/', trim($data['password'])))
    {
        $valid = "Invalid password!";
    }
    elseif($data['password'] != $data['verify_password'])
    {
        $valid = 'Passwords do not match!';
    }

    return $valid;
}

/**
 * @param $data
 * @return bool
 */
function checkPost($data, $username)
{
    $valid = true;
    // if any of the fields are missing, return an error
    if( trim($data['username']) == '' ||
        trim($data['title'])    == '' ||
        trim($data['comment'])  == '' ||
        trim($data['priority']) == '')
    {
        $valid = "All inputs are required.";
    }
    elseif($username != trim($data['username'])&&$_SESSION['admin']!=true)
    {
        $valid = "Invalid username!";
    }
    elseif(!preg_match('/^[a-z]([a-z]|[0-9]){6}([a-z]|[0-9])*[0-9]+$/i', trim($data['username']))&&$_SESSION['admin']!=true)
    {
        $valid = "Invalid username!";
    }
    elseif(!preg_match("/^[a-z ]+$/i", trim($data['title'])))
    {
        $valid = "Invalid title!";
    }
    elseif(!preg_match('/^[a-z0-9 ,\.\?!]+$/i', trim($data['comment'])))
    {
        $valid = "Invalid comment!";
    }
    elseif(!preg_match('/^[1-3]$/i', trim($data['priority'])))
    {
        $valid = "Invalid priority!";
    }

    return $valid;
}

/**
 * @param $data
 * @return bool true on successful write
 */
function savePost($data)
{
    $success = false;

    $fp = fopen('posts.txt', 'a+');

    if($fp != false)
    {
        $id         = uniqid();
        $username   = trim($data['username']);
        $title      = trim($data['title']);
        $comment    = trim($data['comment']);
        $priority   = trim($data['priority']);

        $results = fwrite(
            $fp,
            $id.'|'.$username.'|'.$title.'|'.$comment.'|'.$priority.PHP_EOL
        );

        fclose($fp);

        if($results)
        {
            $success = true;
        }
    }

    return $success;
}

function getAllPosts()
{
    $lines = file('posts.txt');

    if($lines != false)
    {
        $importantPosts = [];
        $highPosts      = [];
        $normalPosts    = [];

        foreach($lines as $line)
        {
            $pieces = preg_split("/\|/", $line);
            if(trim($pieces[4]) == 1)
            {
                $importantPosts[] = $pieces;
            }
            elseif(trim($pieces[4]) == 2)
            {
                $highPosts[] = $pieces;
            }
            elseif(trim($pieces[4]) == 3)
            {
                $normalPosts[] = $pieces;
            }
        }

        return array_merge($importantPosts, $highPosts, $normalPosts);
    }

    return [];
}

function getPriorityTag($id)
{
    $tags = [
        1 => 'panel-danger',
        2 => 'panel-warning',
        3 => 'panel-info'
    ];

    return $tags[$id];
}

function deletePost($id, $username)
{
    $lines = file('posts.txt');

    if($lines != false)
    {
        // w truncates the file
        $fp = fopen('posts.txt', 'w');

        // comb through all existing lines
        foreach($lines as $line)
        {
            $pieces = preg_split("/\|/", $line);

            if($pieces[0] == $id && $pieces[1] == $username)
            {
                continue;       // skip this line if this is the post to delete
            }

            fwrite($fp, $line); // include this line
        }

        fclose($fp);
    }
}

/**
 * admin delete the post no username check
 * @param $id the id passed by get function
 */
function admindeletePost($id)
{
    $lines = file('posts.txt');

    if($lines != false)
    {
        // w truncates the file
        $fp = fopen('posts.txt', 'w');

        // comb through all existing lines
        foreach($lines as $line)
        {
            $pieces = preg_split("/\|/", $line);

            if($pieces[0] == $id)
            {
                continue;       // skip this line if this is the post to delete
            }

            fwrite($fp, $line); // include this line
        }

        fclose($fp);
    }
}

function filterUserName($name)
{
    // if it's not alphanumeric, replace it with an empty string
    return preg_replace("/[^a-z0-9]/i", '', $name);
}

/**
 * if admin editing, delete the old one and write the new one
 * @param $data the post
 */
function editPost($data){
    
    $id         = trim($data['id']);
    $username   = trim($data['username']);
    $title      = trim($data['title']);
    $comment    = trim($data['comment']);
    $priority   = trim($data['priority']);
    $lines = file('posts.txt');

    if($lines != false)
    {
        // w truncates the file
        $fp = fopen('posts.txt', 'w');

        // comb through all existing lines
        foreach($lines as $line)
        {
            $pieces = preg_split("/\|/", $line);

            if($pieces[0] == $id)
            {
                continue;       // skip this line if this is the post to delete
            }

            fwrite($fp, $line); // include this line
        }

        fwrite(
            $fp,
            $id.'|'.$username.'|'.$title.'|'.$comment.'|'.$priority.PHP_EOL
        );
        fclose($fp);
    }
}

/**
 * Look up the user & password pair from the admin.ini.
 *
 * User can only be admin.
 *
 * Remember, md5() is just for demonstration purposes.
 * Do not do this in production for passwords.
 *
 * @param $user string The username to look up
 * @param $password string The password to look up
 * @param $field string user
 * @return bool true if found, false if not
 */
function findAdmin($user, $password, $field)
{
    $found = false;

    $lines = file('admin.ini');
   
    foreach($lines as $line)
    {
        $pieces = preg_split("/\,/", $line); // | is a special character, so escape it

        if($field == 'username' && $pieces[0] == $user && trim($pieces[1]) == $password)
        {
            $found = true;
        }
    }
    return $found;
}