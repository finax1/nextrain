<?php
header("Status: 200 OK");
header("content-type: image/gif");
echo file_get_contents("default.gif");
die();