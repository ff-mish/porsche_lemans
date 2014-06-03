<div class="email-con" ng-controller="EmailController">
  <table class="table table-bordered table-hover" id='email-table' ng-init="initDataTablePlugin()">
    <thead>
      <th>Email</th>
      <th>昵称</th>
      <th>平台</th>
      <th>时间</th>
    </thead>
    <tbody>
      <?php foreach ($emails as $email):?>
        <tr>
          <td><?php echo $email["mail"]?></td>
          <td><?php echo $email["name"] ? $email["name"]: "匿名"?></td>
          <td><?php echo $email["from"] ? $email["from"] : "匿名"?></td>
          <td><?php echo $email["cdate"]?></td>
        </tr>
      <?php endforeach;?>
    </tbody>
  </table>
</div>
