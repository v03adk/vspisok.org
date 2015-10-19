<script type="text/javascript" src="<?php echo base_url().'js/jquery-1.4.2.min.js'?>"></script>
<script type="text/javascript" src="<?php echo base_url().'js/jquery-dynamic-form.js'?>"></script>

<script type="text/javascript">

$(document).ready(function(){   
    $("#double").dynamicForm("#plus", "#minus", {});
});

</script>

<h1>Создание списка</h1>

<form action="" method="post"> 

<p>
    Название списка:<br />
    <input type="text" name="title" value="<?php echo set_value('title')?>" class="full" />
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
    <input type="submit" value="Создать список" />
</p>
</form>