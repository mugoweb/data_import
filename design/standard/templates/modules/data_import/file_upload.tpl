<h1>Upload file here</h1>
<form action={'data_import/file_upload'|ezurl()} method="post" enctype="multipart/form-data">
    <label for="file">Select File:</label>
    <input type="file" name="file" id="file"><br>
    <input type="submit" name="submit" value="Submit">
</form>
