<?php
$fp = fopen("moip.txt", "a");
$escreve = fwrite($fp, json_encode($_POST));
fclose($fp);