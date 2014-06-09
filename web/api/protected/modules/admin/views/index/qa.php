
<div class="qa-container" ng-controller="QAController">
  <div class="form-box-bg hideme {{form_class}}" ng-click="closeQaFormPopup('')"></div>
  <table class="table table-hover table-bordered" id='qa-table'>
    <thead>
      <th><?php echo Yii::t("lemans","Select")?></th>
      <th><?php echo Yii::t("lemans","No")?></th>
      <th><?php echo Yii::t("lemans","Question")?></th>
      <th><?php echo Yii::t("lemans","Edit")?></th>
      <th><?php echo Yii::t("lemans","Times Of Answered")?></th>
      <th><?php echo Yii::t("lemans","Times Of Right Answered")?></th>
    </thead>
    <tbody>
      <?php foreach ($list as $item):?>
        <tr>
          <td><input type="checkbox" name="select" value="1"/></td>
          <td><?php echo $item->qaid?></td>
          <td><?php echo $item->question?></td>
          <td>
            <a href="#/edit" data-id="<?php echo $item->qaid?>" ng-click="openQaFormPopup($event)"><?php echo Yii::t("lemans","Edit")?></a>
            <a href="#/delete" data-id="<?php echo $item->qaid?>" ng-click="deleteQa($event)"><?php echo Yii::t("lemans","Delete")?></a>
          </td>
          <td>
            <?php echo $item->answered ? $item->answered: 0?>
          </td>
          <td>
            <?php echo $item->right_answered ? $item->right_answered: 0?>
          </td>
        </tr>
      <?php endforeach;?>
    </tbody>
  </table>
  <div class="row">
    <div class="offset8 span2">
      <a href="javascript:void(0)" ng-click="openQaFormPopup($event)"><?php echo Yii::t("lemans","Add Q&A")?></a>
    </div>
  </div>
  
  <div class="row form-row-con">
  <form action="/admin/index/qa" enctype="multipart/form-data" name="qaform" method="post" class="form-horizontal hideme span10 {{form_class}}">
    <div class="close" ng-click="closeQaFormPopup()"></div>
    <div class="field-item">
      <label for="question"><?php echo Yii::t("lemans","Question")?></label>
      <input type="text" name="question" ng-model="question.question" ng-required/>
    </div>
    <div class="field-item">
      <label for="answer"><?php echo Yii::t("lemans","Answer")?></label>
      <div class="group-item">
        <div class="group-item-field">
          <label for="a1">A</label>
          <input type="input" name="q[]" ng-model="question.answer1" required/>
        </div>
        <div class="group-item-field">
          <label for="a2">B</label>
          <input type="input" name="q[]" ng-model="question.answer2" required/>
        </div>
        <div class="group-item-field">
          <label for="a3">C</label>
          <input type="input" name="q[]" ng-model="question.answer3" required/>
        </div>
        <div class="group-item-field">
          <label for="a4">D</label>
          <input type="input" name="q[]" ng-model="question.answer4" required/>
        </div>
      </div>
    </div>
    <div class="field-item">
      <label for="right"><?php echo Yii::t("lemans","Right Answer Number")?></label>
      <input type="" name="right" ng-model="question.right" required ng-pattern="/^\d$/"/>
      <div class="desc"><?php echo Yii::t("lemans","Number is : ")?>: A - 1 | B - 2 | C - 3 | D - 4</div>
    </div>
    <div class="field-item">
      <input type="button" value="<?php echo Yii::t("lemans","Save")?>" ng-click="qaFormSubmit()" />
    </div>
    <input type="hidden" name="qaid" value="" ng-model="question.qaid"/>
  </form>
  </div>

</div>


