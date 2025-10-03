<?php
session_start();
$_SESSION['provjera'] = 'Session radi!';
echo 'Session ID: ' . session_id() . "<br>";
echo 'Cookie: ';
print_r($_COOKIE);
echo '<hr>';
echo 'Session podaci: ';
print_r($_SESSION);
