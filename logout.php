<?php
session_start();
session_destroy();
header("Location: /library_qr/index.php");
exit;
