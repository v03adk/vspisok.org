<h1>Смена пароля</h1>

{errors}

<form method="post" action="">
<p>
    Старый пароль:<br />
    <input type="password" name="password" class="full" value="" />
</p>
<p>
    Новый Пароль (минимальная длина 8 символов):<br />
    <input type="password" name="new_password" class="full" value="" />
</p>
<p>
    Подтверждение пароля:<br />
    <input type="password" name="re_password" class="full" value="" />
</p>
<p> 
    <input type="submit" name="change" value="Изменить" />
</p>
</form>