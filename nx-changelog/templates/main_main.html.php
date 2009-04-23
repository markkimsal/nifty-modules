<h1>No eXcuse for Changelogs</h1>

<p>
NX Changelog helps you and/or your team stay on top of creating changelogs and release notes 
for your project.  
</p>

<p>With NXC you can <b>categroize</b> or <b>hide</b> any Subversion commit log messages 
between a <b>release tag</b> and the <b>trunk</b>.  After categorizing your commit entries you can add your 
own summary of the changes as release notes.  Each changelog can be downloaded in <b>text, 
wiki, and XML</b> formats.
</p>

<h2>Your Projects</h2>

	<?php
	echo cgn_applink('New Project', 'nx-changelog', 'main', 'newProj');
	?>


<?php
foreach ($t['projectList'] as $project) { ?>

	<h3><?= $project->title; ?></h3>
	<ul>
	<li>
	<a href="<?= cgn_appurl('nx-changelog','main','editProj',array('id'=>$project->getPrimaryKey()));?>">Edit Project</a>
	</li>
	<li>
<?=
				cgn_applink('New Changelog','nx-changelog','main','newCl',array('id'=>$project->getPrimaryKey()));
?>
	</li>
	</ul>

	<h4>List of Changelogs</h4>
	<?php
	if ( isset($t['logTableList'][$project->nxc_project_id])) { ?>

		<?php
		$table = $t['logTableList'][$project->nxc_project_id];
		echo $table->toHtml();
		?>

	<?php
	} ?>


<?php 
} ?>


