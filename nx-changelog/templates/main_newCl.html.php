<h2>Project: <?= $t['proj']->title;?></h2>
	
<form method="POST" action="<?=cgn_appurl('nx-changelog','main','saveCl');?>">
<h2>Prepare Changelog</h2>
Changelog Title:
<input type="text" size="40" name="title" id="title" value="New Changelog" />
<br/>
<br/>

Trunk URL:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" size="60" name="url1" id="url1" value="<?=$t['proj']->get('trunk_url');;?>" />
<br/>
<br/>

Last Tag URL:&nbsp;&nbsp;&nbsp;&nbsp;
<input type="text" size="60" name="url2" id="url2" />
<br/>
<br/>

<input type="hidden" name="proj_id"  value="<?=$t['proj']->get('nxc_project_id');?>" />
<input type="submit" name="sbmt-button" />
</form>
