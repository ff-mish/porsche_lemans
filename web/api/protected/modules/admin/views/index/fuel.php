<div class="fuel-container" ng-controller="FuelController">
  <div class="form-box-bg hideme {{form_class}}" ng-click="closeFuelFormPopup($event)"></div>
  <table class="table table-bordered table-hover" id="fuel-table">
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
          <a href="#/edit" data-id="<?php echo $media["mid"]?>" ng-click="openFuelFormPopup($event)">编辑</a>
          <a href="#/view" data-id="<?php echo $media["mid"]?>">查看</a>
          <a href="#/delete" data-id="<?php echo $media["mid"]?>">删除</a>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
  
  <div class="row">
    <div class="offset8 span2">
      <a href="javascript:void(0)" ng-click="openFuelFormPopup($event)">添加Fuel</a>
    </div>
  </div>

  <div class="row form-row-con">
    <form action="/admin/index/fuel" enctype="multipart/form-data" name="addfuel" class="form-horizontal hideme span10 {{form_class}}" method="post">
      <div class="close" ng-click="closeFuelFormPopup()"></div>
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
        <input type="input" name="title" ng-model="fuel.title"/>
      </div>
      <div class="field-item">
        <label for="description">描述</label>
        <textarea name="description" cols="30" rows="10" placeholder="说明" ng-model="fuel.description"></textarea>
      </div>
      <div class="field-item">
        <input type="button" value="保存" ng-click="addFuelFormSubmit()"/>
      </div>
      <input type="hidden" name="mid" value="" ng-model="fuel.mid"/>
    </form>
  </div>

</div>