
<div class="qa-container" ng-controller="QAController">
  <div class="form-box-bg hideme {{form_class}}" ng-click="closeQaFormPopup('')"></div>
  <table class="table table-hover table-bordered" id='qa-table'>
    <thead>
      <th>选择</th>
      <th>序号</th>
      <th>题目</th>
      <th>编辑</th>
      <th>回答次数</th>
      <th>回答正确次数</th>
    </thead>
    <tbody>
      <?php foreach ($list as $item):?>
        <tr>
          <td><input type="checkbox" name="select" value="1"/></td>
          <td><?php echo $item->qaid?></td>
          <td><?php echo $item->question?></td>
          <td>
            <a href="#/edit" data-id="1">编辑</a>
            <a href="#/delete" data-id="1">删除</a>
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
      <a href="javascript:void(0)" ng-click="openQaFormPopup()">添加Q&A</a>
    </div>
  </div>
  
  <div class="row form-row-con">
  <form action="/admin/index/qa" enctype="multipart/form-data" name="addqa" method="post" class="form-horizontal hideme span10 {{form_class}}">
    <div class="close" ng-click="closeQaFormPopup()"></div>
    <div class="field-item">
      <label for="question">问题</label>
      <input type="text" name="question" />
    </div>
    <div class="field-item">
      <label for="answer">答案</label>
      <div class="group-item">
        <div class="group-item-field">
          <label for="a1">A</label>
          <input type="input" name="q[]" />
        </div>
        <div class="group-item-field">
          <label for="a2">B</label>
          <input type="input" name="q[]" />
        </div>
        <div class="group-item-field">
          <label for="a3">C</label>
          <input type="input" name="q[]" />
        </div>
        <div class="group-item-field">
          <label for="a4">D</label>
          <input type="input" name="q[]" />
        </div>
      </div>
    </div>
    <div class="field-item">
      <label for="right">正确答案</label>
      <input type="" name="right" />
    </div>
    <div class="field-item">
      <input type="submit" value="保存"/>
    </div>
    <input type="hidden" name="qaid" value="" />
  </form>
  </div>

</div>


