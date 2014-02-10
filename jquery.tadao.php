<?php
// Contains additional javascript that will be inserted in jquery document.ready cycle 
ob_start();
?>
// console.log("test");


<?php 
$content = ob_get_clean();
echo $content;
?>
