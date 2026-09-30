<?php
$conn = mysqli_connect("sql213.infinityfree.com","if0_42788093","hstr2111","if0_42788093_homestore");

if(!$conn){
    die("DB ERROR: " . mysqli_connect_error());
}
?>