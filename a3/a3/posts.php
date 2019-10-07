<?php
require 'includes/functions.php';

$message = '';
session_start();

if(!isset($_SESSION['loggedin']))
{
    header('Location: index.php');
    exit();
}

if(count($_POST) > 0)
{
    $check = checkPost($_POST, $_SESSION['username']);
    if($check !== true)
    {
        $message = '<div class="alert alert-danger text-center">'
                    . $check .
                    '</div>';
    }elseif(isset($_POST['id'])){
        //check admin editing or not
        editPost($_POST);
    }
    else
    {
        savePost($_POST);
    }
}

$posts = getAllPosts();

?>
<!DOCTYPE html>
<html>
<head>
    <title>COMP 3015</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div id="wrapper">

    <div class="container">

        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <h1 class="login-panel text-center text-muted">
                    COMP 3015 Assignment 2
                </h1>
                <hr/>
                <?php echo $message; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <button class="btn btn-default" data-toggle="modal" data-target="#newPost"><i class="fa fa-comment"></i> New Post</button>
                <a href="logout.php" class="btn btn-default pull-right"><i class="fa fa-sign-out"> </i> Logout</a>
                <hr/>
            </div>
        </div>

        <?php
            foreach($posts as $post)
            {
                echo '
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <div class="panel '.getPriorityTag(trim($post[4])).'">
                                    <div class="panel-heading">
                                        <span>
                                            '.$post['2'].'
                                        </span>
                                        <span class="pull-right text-muted">'
                ;
                //if the the user is same or it is the admin show delete
                if($_SESSION['username'] == $post[1]||$_SESSION['admin']==true)
                {
                    echo '
                                        <a class="" href="delete.php?id='.$post['0'].'">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>'
                    ;
                }

                if($_SESSION['admin']==true)
                {   /*
                      create the modals with giving each modal a unique div id(by using id of the posts) so the default value of each post will be different 
                    */
                    echo '
                        <a href="javascript:void(0);" class="small-box-footer" data-toggle="modal" data-target="#EditPost'.$post['0'].'">
                        <i class="fa fa-check"></i>Edit</a>
                                        <div id="EditPost'.$post['0'].'" class="modal fade" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <form role="form" method="post" action="posts.php">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title">Edit</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                            <div class="form-group">
                                                                <input class="form-control disabled" type="hidden" placeholder="id" name="id" value="'.$post['0'].'" readonly="readonly">
                                                            </div>
                                                            <div class="form-group">
                                                                <input class="form-control disabled" type="text" placeholder="Username" name="username" value="'.$post['1'].'" readonly="readonly">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Title</label>
                                                                <input class="form-control" type="text" placeholder="" name="title" value="'.$post['2'].'">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Comment</label>
                                                                <textarea class="form-control" rows="3" name="comment">'.$post['3'].'</textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Priority</label>
                                                                <select class="form-control" name="priority" value="'.$post['4'].'">
                                                                    <option value="1">Important</option>
                                                                    <option value="2">High</option>
                                                                    <option value="3">Normal</option>
                                                                </select>
                                                            </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        <input type="submit" class="btn btn-primary" value="Post!"/>
                                                    </div>
                                                </div><!-- /.modal-content -->
                                                </form>
                                            </div><!-- /.modal-dialog -->
                                            </div><!-- /.modal -->                

                        '
                                        
                    ;
                }

                echo'
                                        </span>
                                    </div>
                                    <div class="panel-body">
                                        <p class="text-muted">
                                        </p>
                                        <p>
                                            '.$post['3'].'
                                        </p>
                                    </div>
                                    <div class="panel-footer">
                                        <p>
                                            '.$post['1'].'
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>'
                ;
            }
        ?>

    </div>
</div>

<div id="newPost" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="posts.php">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">New Post</h4>
        </div>
        <div class="modal-body">
                <div class="form-group">
                    <input class="form-control disabled" type="text" placeholder="Username" name="username" value="<?php echo $_SESSION['username']; ?>">
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input class="form-control" type="text" placeholder="" name="title">
                </div>
                <div class="form-group">
                    <label>Comment</label>
                    <textarea class="form-control" rows="3" name="comment"></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select class="form-control" name="priority">
                        <option value="1">Important</option>
                        <option value="2">High</option>
                        <option value="3">Normal</option>
                    </select>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <input type="submit" class="btn btn-primary" value="Post!"/>
        </div>
    </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</body>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</html>
