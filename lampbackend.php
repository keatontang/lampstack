<?php

$dbhost = 'localhost';
$dbuser = ‘<username>’;
$dbpass = ‘<password>’;
$dbname = ‘<database>’;

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
mysqli_select_db($conn, $dbname);
date_default_timezone_set('NZ');


function query($query) {
        global $conn;
        $result = mysqli_query($conn, $query);
        return $result;
}

function getSingle($query) {
        global $conn;
        $result = query($query);
        $row = mysqli_fetch_row($result);
        return $row[0];
}

function getUid() {
        global $conn;
        $ip = mysqli_real_escape_string($conn,$_SERVER['REMOTE_ADDR']);
        $uid = getSingle("select uid from users where ip = '".$ip."'");
        if (!$uid) {
                query("insert into users(ip) values ('$ip')");
        }
        $uid = getSingle("select uid from users where ip = '".$ip."'");
        return $uid;
}

if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}

$date = Date("Y-m-d H:i:s");
$user = getUid();

if(($_REQUEST['follow'])) {
        $follow = mysqli_real_escape_string($conn, $_REQUEST['follow']);
        query("insert ignore into follows(uid, follower) values ($user, '$follow')");
}

if(($_REQUEST['post'])) {
        $post = mysqli_real_escape_string($conn, $_REQUEST['post']);
        $date = Date("Y-m-d H:i:s");
        query("insert into posts(uid, post, date) values ($user, '$post', '$date')");
}

if(($_REQUEST['unfollow'])) {
        $unfollow = mysqli_real_escape_string($conn, $_REQUEST['unfollow']);
        query("delete from follows where uid=$user and follower='$unfollow'");
}


print <<<EOF
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="ano$
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<head>
<body>
<link rel="shortcut icon" href="favicon.ico" />
<title>PigeonPost</title>
<style>
body{
  padding:20px;
}
</style>
<h3>
  PigeonPost
</h3>
<h5><small class="text-muted">Full-stack web development project on LAMP stack (Linux, Apache, MariaDB, PHP)</small></h5>
</body>
</head>
<BR>
<form action=index.php method=post>
<table><TR><TD width=400>
<textarea placeholder="What's up?" name=post class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
</td><TD>
<button type="submit" class="btn btn-primary btn-lg">Post</button>
</td></tr></table>
</form>

EOF;

function renderPosts($posts) {
        global $user;
        print "<table  bordercolor=#ddd cellpadding=2 border=1>";
        foreach($posts as $row) {
                 $uid = $row['uid'];
                 $post = htmlspecialchars($row['post']);
                 $date = $row['date'];
        
        if (!getSingle("select follower from follows where uid=$user and follower=$uid"))
                $follow = <<<EOF
                <a class="btn btn-outline-primary" href=index.php?follow=$uid role="button">Follow</a>
EOF;
        else {
                $follow = <<<EOF
                 <a class="btn btn-outline-secondary" href=index.php?unfollow=$uid role="button">Unfollow</a>
EOF;
        }
        print <<<EOF
        <tr><TD>$uid</td><td>$post</td><td>$date</td><td>$follow</td></tr>
EOF;
        }
print "</table>";
}


print "<h3>Latest</h3>";
$result = query("select * from posts order by date desc limit 100");
while($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
}
renderPosts($posts);

print "<HR>";

print "<h3>Followed Users</h3>";
$posts = array();
$result = query("select * from posts where uid in (select follower from follows where uid=$user) order by date desc limit 100");
while($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
}
renderPosts($posts);
