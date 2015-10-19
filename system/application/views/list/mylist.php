<h1>Мои списки</h1>

<p>
<a href="<?php echo base_url().'sp/create/';?>">Создать список</a>
</p>

<table cellpadding="5" cellspacing="5">
<colgroup>  
<col style="width: auto" />  
<col style="width: 10em" />  
</colgroup> 
{list}
<tr>
    <td><a href="<?php echo base_url().'sp/show/';?>{url}">{name}</a></td>
    <td>{action}&nbsp;&nbsp;<a href="<?php echo base_url().'sp/del/';?>{url}">удалить</a></td>    
</tr>
{/list}
</table>
{paginator}