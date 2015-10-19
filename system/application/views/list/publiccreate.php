<script type="text/javascript" src="<?php echo base_url().'js/jquery-1.4.2.min.js'?>"></script>
<script type="text/javascript" src="<?php echo base_url().'js/jquery-dynamic-form.js'?>"></script>

<script type="text/javascript">

$(document).ready(function(){   
    $("#double").dynamicForm("#plus", "#minus", {});
});

</script>

<h1>Создание списка</h1>

<?php echo $errors; ?>

<form action="" method="post"> 

<p>
    Название списка:<br />
    <input type="text" name="title" value="<?php echo set_value('title')?>" class="full" />
</p>

<p>
    Пароль:<br />
    <input type="password" name="password" value="" class="full" /><br />
    <span class="note">Если вы заполните это поле, доступ к вашему списку будет ограничен. Его можно будет увидеть только после ввода пароля.</span>
</p>

<p id="double">
    Элемент:<br />
    <input type="text" name="task" value="" class="full" /><br />
    Описание(опционально):<br />
    <textarea name="description" rows="8" cols="40" class="full"></textarea>    
</p>
<p>
<a id="minus" href="minus">Удалить элемент</a>&nbsp;&nbsp;<a id="plus" href="plus">Добавить элемент</a>
</p>

<p>
    Поделиться списком с друзьями:<br />
    <textarea name="emails" rows="5" cols="40" class="full"><?php echo set_value('emails')?></textarea><br />
    <span class="note">Здесь вы можете указать электронные адреса ваших друзей, с которыми вы бы хотели поделиться списком сразу после сохранения. Вводите каждый адрес на отдельной строке.</span>
</p>

<p>
    Срок годности списка:<br />
    <select name="expire"><option value="1" <?php echo set_select('expire', '1'); ?>>1 день</option><option value="3" <?php echo set_select('expire', '3'); ?>>3 дня</option><option value="7" <?php echo set_select('expire', '7'); ?>>7 дней</option></select><br />
    <span class="note">Через сколько времени ваш список будет удалён с сервера.</span>
</p>

<p>
    <input type="submit" value="Создать список" />
</p>
</form>