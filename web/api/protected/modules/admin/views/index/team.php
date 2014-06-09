<div class="qa-container" ng-controller="TeamController">
  <div class="form-box-bg hideme {{form_class}}" ng-click="closeQaFormPopup('')"></div>
    <table class="table table-bordered table-hover " id="team-table">
      <thead>
        <th><?php echo Yii::t("lemans","Ranking")?></th>
        <th><?php echo Yii::t("lemans","Team")?></th>
        <th><?php echo Yii::t("lemans","Members")?></th>
        <th><?php echo Yii::t("lemans","Create Time")?></th>
        <th><?php echo Yii::t("lemans","Speed")?></th>
        <th><?php echo Yii::t("lemans","Imspact")?></th>
        <th><?php echo Yii::t("lemans","Quality")?></th>
        <th>Q&A</th>
        <th><?php echo Yii::t("lemans","Score")?></th>
        <th><?php echo Yii::t("lemans","Distinct")?></th>
        <th><?php echo Yii::t("lemans","Platform")?></th>
      </thead>
      <tbody>
        <?php foreach ($teams as $key => $team): ?>
        <tr>
          <?php $team_member = $this->getUserTeamInfo($team["tid"])?>
          <td><?php echo $key?></td>
          <td><?php echo $team["name"]?></td>
          <td><?php echo $team_member["names"]?></td>
          <td><?php echo $team["cdate"]?></td>
          <td><?php echo $team["speed"]?></td>
          <td><?php echo $team["impact"]?></td>
          <td><?php echo $team["quality"]?></td>
          <td><?php echo $team["assiduity"]?></td>
          <td><?php echo $team["average"]?></td>
          <td><?php echo $team_member["location"]?></td>
          <td><?php echo $team_member["from"]?></td>
        <?php endforeach;?>
        </tr>
      </tbody>
    </table>
</div>