<?php
session_start();
session_unset();
session_destroy();
header("Location: https://ifvirginvirtual.vip");
http_response_code(302);