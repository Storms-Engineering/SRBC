<?php
$date = new DateTime("now", new DateTimeZone('America/Anchorage'));
echo $date->format("m/j/Y G:i");
?>