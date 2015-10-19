<h1>Обратная связь</h1>
{errors}

<form action="" method="post">
<p>
    Сообщение : <br />
    <textarea name="mes" rows="8" cols="40" class="full"><?php echo set_value('mes'); ?></textarea>
</p>
        
<p>
    Электронный адрес (мы его никому не покажем) : <br />
    <input type="text" name="email" value="<?php echo set_value('email'); ?>" class="full" />
</p>
        
<p>
    Код подтверждения (только цифры и заглавные буквы): <br />
    {image}&nbsp;&nbsp;<input type="text" name="captcha" value="" />
</p>
        
<p>
    <input type="submit" value="Отправить" />
</p>

</form>