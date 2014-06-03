<table class="table table-bordered table-hover ">
  <thead>
    <th>排名</th>
    <th>组名</th>
    <th>成员</th>
    <th>创建时间</th>
    <th>速度</th>
    <th>影响力</th>
    <th>质量</th>
    <th>Q&A</th>
    <th>总成绩</th>
    <th>地区</th>
    <th>平台</th>
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