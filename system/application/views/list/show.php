<script type="text/javascript" src="<?php echo base_url().'js/jquery-1.4.2.min.js'?>"></script>
<script type="text/javascript" src="<?php echo base_url().'js/jquery.tablednd_0_5.js'?>"></script>

<script type="text/javascript">

$(document).ready(function(){   
    $("#spisok").tableDnD({
        onDrop: function(table, row){
            var rows = table.tBodies[0].rows;
            var debugStr = '';
            for (var i=0; i<rows.length; i++) {
                debugStr += rows[i].id+',';
            }
            $('#order').val(debugStr);    
        },
        dragHandle: "dragHandle"
    });
    
    
    $("#spisok tr").hover(function() {
          $(this.cells[0]).addClass('showDragHandle');
    }, function() {
          $(this.cells[0]).removeClass('showDragHandle');
    });

});

function save_layout()
{
    var url = '{url}';
    $.post('<?php echo base_url().'sp/saveLayout'; ?>', {order : $('#order').val(), url : url });
}

</script>

<h1>Список {name}</h1>
<a href="<?php echo base_url().'sp/addElem/'; ?>{url}">Добавить пункт</a>&nbsp;&nbsp;
<a onclick="save_layout(); return false;" href="save_layout">Сохранить расположение</a>
<form action="" method="post">
<input type="hidden" value="1" name="l" />
<input type="hidden" value="" id="order" />
<table cellpadding="5" cellspacing="5" id="spisok">
<colgroup> 
<col style="width: 1em" /> 
<col style="width: 1em" /> 
<col style="width: auto" />  
<col style="width: 5em" />  
</colgroup> 
{elems}
<tr id="{id}">
    <td class="dragHandle">&nbsp;</td>
    <td><input type="checkbox" name="elems[]" value="{id}" /></td>
    <td {style}>
        <a href="<?php echo base_url().'sp/showelem/'; ?>{url}/{id}">{e_name}</a> <br />
        <span class="note">{description}</span>
    </td>
    <td>{action}</td>    
</tr>
{/elems}
</table>
<select name="action"><option value="done">пометить сделанными</option><option value="undone">вернуть</option><option value="del">удалить из списка</option></select>
<input type="submit" value="Выполнить" />
</form>

<p>Ссылка на список : <a href="<?php echo base_url().'sp/show/'; ?>{url}"><?php echo base_url().'sp/show/'; ?>{url}</a></p>