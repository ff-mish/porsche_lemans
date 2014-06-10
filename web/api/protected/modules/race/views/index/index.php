<div class="row">
  <div class="span4 offset4 login-form-con">
    <form action="/admin/index/index" enctype="form-data/multipart" method="post" name="login">
      <fieldset class="field-item">
        <label for="name"><?php echo Yii::t("lemans", "User")?></label>
        <input type="" name="user" />
      </fieldset>

      <fieldset class="field-item">
        <label for="password"><?php echo Yii::t("lemans", "Password")?></label>
        <input type="password" name="password" />
      </fieldset>

      <fieldset class="field-item">
        <input type="submit" value="<?php echo Yii::t("lemans", "Login")?>" />
      </fieldset>
    </form>
  </div>
</div>
