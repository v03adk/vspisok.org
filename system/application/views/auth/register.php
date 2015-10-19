<h1>Регистрация</h1>

{errors}

<form method="post" action="">
<p>
    Адрес электронной почты (будет использоваться в качесте логина):<br />
    <input type="text" name="email" class="full" value="<?php echo set_value('email'); ?>" />
</p>
<p>
    Пароль (минимальная длина 8 символов):<br />
    <input type="password" name="password" class="full" value="" />
</p>
<p>
    Подтверждение пароля:<br />
    <input type="password" name="re_password" class="full" value="" />
</p>
<p> 
    <input type="submit" name="register" value="Зарегестрироваться" />
</p>
</form>