<?php

switch () {
    case 'home':
        include 'pages/home';
        break;
    case 'about':
        include 'pages/about.php';
        break;
    case 'contact':
        include 'pages/contact.php';
        break;
    case 'profile':
        include 'pages/profile.php';
        break;
    case 'edit_profile':
        include 'pages/edit_profile.php';
        break;
    default:
        include 'pages/404.php';
        break;
}
