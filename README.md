# InstaCapture
Get photos from Instagram (only public profiles) using Curl with PHP.

# How to use
```
<?php
require_once 'InstaCapture.php';

$ic = new InstaCapture();
$ic->setProfileUrl("https://www.instagram.com/tiago.henrique.92/");
$photos = $ic->getPhotos();
```
