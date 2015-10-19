<h1>Вход</h1>

{errors}

<form method="post" action="">
<p>
    Логин:<br />
    <input type="text" name="email" class="full" value="<?php echo set_value('email'); ?>" />
</p>
<p>
    Пароль:<br />
    <input type="password" name="password" class="full" value="" />
</p>
<p> 
    <input type="submit" name="enter" value="Вход" />
</p>
</form>

<span class="note"><a href="<?php echo base_url().'user/recover/'; ?>">Забыли пароль?</a></span>