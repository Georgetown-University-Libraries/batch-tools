<?php
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=export.xls");
echo $_POST['data'];
?>