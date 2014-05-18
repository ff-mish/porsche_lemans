<form action="/api/media/add" enctype="multipart/form-data" method="POST">
  <div class="item">
    <label for="">Type</label>
    <select name="type" id="type">
      <option value="video">Video</option>
      <option value="image">Image</option>
    </select>
  </div>
  <div class="item">
    <label for="">Media</label>
    <input type="file" name="uri"/>
  </div>
  <div class="item">
    <label for="">Video Teaser</label>
    <input type="file" name="teaser_image"/>
  </div>
  <div class="item">
    <input type="submit" value="add"/>
  </div>
</form>

