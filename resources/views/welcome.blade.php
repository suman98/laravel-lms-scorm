<html lang="en">


    <form action="/api/admin/scorm/upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" id="myFile" name="zip">
        <input type="submit">
      </form>

</html>