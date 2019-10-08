# InstaCapture
Get photos from Instagram (only public profiles) using Curl with PHP.

# How to use

```php
<?php
use App\InstaCapture;

$ic = new InstaCapture("https://www.instagram.com/tiago.henrique.92/");
$photos = $ic->getPhotos();
```
