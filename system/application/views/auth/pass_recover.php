<h1>Востановление пароля</h1>

<p>
Так как мы храним ваш пароль в зашифрованном виде, мы не сможем вам выслать именно его. Новый пароль будет сгенерирован и послан вам на почту. Потом вы сможете сменить его.
</p>

{errors}

<form method="post" action="">
<p>
    Электронный адрес:<br />
    <input type="text" name="email" class="full" value="<?php echo set_value('email'); ?>" />
</p>
<p> 
    <input type="submit" name="recover" value="Востановить" />
</p>
</form>