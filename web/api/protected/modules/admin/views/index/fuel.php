<div class="fuel-container" ng-controller="FuelController">
  <div class="row bar-link">
    <div class="offset8 span2">
      <a href="javascript:void(0)" ng-click="openFuelFormPopup($event)"><?php echo Yii::t("lemans","Add Fuel")?></a>
    </div>
  </div>
  <div class="form-box-bg hideme {{form_class}}" ng-click="closeFuelFormPopup($event)"></div>
  <table class="table table-bordered table-hover" id="fuel-table">
    <thead>
      <th><?php echo Yii::t("lemans","Title")?></th>
      <th><?php echo Yii::t("lemans","Description")?></th>
      <th><?php echo Yii::t("lemans","Create Date")?></th>
      <th><?php echo Yii::t("lemans","Operator")?></th>
    </thead>
    <tbody>
      <?php foreach ($medias as $media):?>
      <tr>
        <td><?php echo $media["title"]?></td>
        <td><?php echo $media["description"]?></td>
        <td><?php echo $media["cdate"]?></td>
        <td>
          <a href="#/edit" data-id="<?php echo $media["mid"]?>" ng-click="openFuelFormPopup($event)"><?php echo Yii::t("lemans","Edit")?></a>
          <a href="#/view" data-id="<?php echo $media["mid"]?>" ><?php echo Yii::t("lemans","View")?></a>
          <a href="#/delete" data-id="<?php echo $media["mid"]?>" ng-click="deleteFuelConfirm($event)"><?php echo Yii::t("lemans","Delete")?></a>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
  


  <div class="row form-row-con">
    <form action="/admin/index/fuel" enctype="multipart/form-data" name="addfuel" class="form-horizontal hideme span10 {{form_class}}" method="post">
      <div class="close" ng-click="closeFuelFormPopup()"></div>
      <div class="field-item">
        <label for="media">File</label>
        <input type="file" name="media" />
        <img src="{{fuel.uri}}" alt="" style="width:30%"/>
      </div>
      <div class="field-item">
        <label for="teaser_image"><?php echo Yii::t("lemans","Thumbnail")?></label>
        <input type="file" name="teaser_image" />
      </div>
      <div class="field-item">
        <label for="title"><?php echo Yii::t("lemans","Title")?></label>
        <input type="input" name="title" ng-model="fuel.title"/>
      </div>
      <div class="field-item">
        <label for="description"><?php echo Yii::t("lemans","Description")?></label>
        <textarea name="description" cols="30" rows="5" placeholder="" ng-model="fuel.description"></textarea>
      </div>
      <div class="field-item">
        <label for="description"><?php echo Yii::t("lemans","Language")?></label>
        <input type="select" name="lang" ng-model="fuel.lang"></input>
        <div class="desc">en | zh</div>
      </div>
      <div class="field-item">
        <input type="button" value="<?php echo Yii::t("lemans","Save")?>" ng-click="addFuelFormSubmit()"/>
      </div>
      
      <input type="hidden" name="mid" value="" ng-model="fuel.mid"/>
    </form>
  </div>
  
</div>