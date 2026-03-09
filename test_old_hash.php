<?php
$hash = '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa';
echo password_verify('123456', $hash) ? 'SIM' : 'NAO';
?>
