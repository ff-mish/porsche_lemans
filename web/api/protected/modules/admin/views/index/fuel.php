<table class="table table-bordered table-hover">
  <thead>
    <th>标题</th>
    <th>说明</th>
    <th>创建时间</th>
    <th>操作</th>
  </thead>
  <tbody>
    <?php foreach ($medias as $media):?>
    <tr>
      <td><?php echo $media["title"]?></td>
      <td><?php echo $media["description"]?></td>
      <td><?php echo $media["cdate"]?></td>
      <td>
        <a href="#/edit" data-id="<?php echo $media["mid"]?>">编辑</a>
        <a href="#/view" data-id="<?php echo $media["mid"]?>">查看</a>
      </td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>

<form action="/admin/index/fuel" enctype="multipart/form-data" name="addfuel" method="post">
  <div class="field-item">
    <label for="media">File</label>
    <input type="file" name="media" />
  </div>
  <div class="field-item">
    <label for="teaser_image">预览图片</label>
    <input type="file" name="teaser_image" />
  </div>
  <div class="field-item">
    <label for="title">标题</label>
    <input type="input" name="title" />
  </div>
  <div class="field-item">
    <label for="description">描述</label>
    <textarea name="description" cols="30" rows="10" placeholder="说明"></textarea>
  </div>
  <div class="field-item">
    <input type="submit" value="保存"/>
  </div>
  <input type="hidden" name="mid" value="" />
</form>

