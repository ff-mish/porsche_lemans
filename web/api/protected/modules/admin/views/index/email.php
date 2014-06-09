<div class="email-con" ng-controller="EmailController">
  <table class="table table-bordered table-hover" id='email-table' ng-init="initDataTablePlugin()">
    <thead>
      <th><?php echo Yii::t("lemans", "Email")?></th>
      <th><?php echo Yii::t("lemans", "lemans", "Nick Name")?></th>
      <th><?php echo Yii::t("lemans", "Platform")?></th>
      <th><?php echo Yii::t("lemans", "Time")?></th>
    </thead>
    <tbody>
      <?php foreach ($emails as $email):?>
        <tr>
          <td><?php echo $email["mail"]?></td>
          <td><?php echo $email["name"] ? $email["name"]: "None"?></td>
          <td><?php echo $email["from"] ? $email["from"] : "None"?></td>
          <td><?php echo $email["cdate"]?></td>
        </tr>
      <?php endforeach;?>
    </tbody>
  </table>
</div>
