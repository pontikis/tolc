<?php
// prevent direct access
if(!$tolc_include) {
	echo CONST_ACCESS_DENIED . ' (' . __FILE__ . ')';
	exit;
}
?>

<?php if(isset($_SESSION['username'])) { ?>

<a href="javascript:void(0);" id="trigger" class="trigger">tolc</a>

<div id="panel" class="panel">

	<div id="tolc_user">
		<h1><?php print gettext('Welcome') ?>, <span
			id="login_user"><?php print $_SESSION['username']?></span>!</h1>

		<p><a id="gravatar_profile"
			  href="<?php print get_gravatar_profile($user_email)?>"
			  target="_blank"><img id="gravatar"
								   src="<?php print get_gravatar($user_email) ?>"
								   alt="Gravatar"
								   title="Gravatar (<?php print $user_email ?>)"></a>
			<?php print gettext('You can') ?> <a id="tp_login_user"
												 href="javascript:void(0);"><?php print gettext('change your profile')?></a>
			<?php print gettext('or') ?>
			<a id="regional"
			   href="<?php print $tolc_conf['pref_reserved_urls']['regional'] ?>"><?php print gettext('regional settings')?></a>.
		</p>

		<p>
			<?php print gettext('Disconnect') ?> <a id="tp_logout"
													href="javascript:void(0);"><?php print gettext('here') ?></a>.
		</p>

	</div>

	<div id="tolc_cms">
		<h1><?php print gettext('Content management') ?></h1>
		<ul>
			<li><a id="tp_edit_page"
				   href="javascript:void(0);"><?php print gettext('Edit page properties') ?></a>
				<?php print ' (' . gettext('URL') . ', ' . gettext('title') . ' ' . gettext('etc') . ')' ?>
			</li>

			<li><a id="tp_pages"
				   href="javascript:void(0);"><?php print gettext('Web site management') ?></a>
			</li>

			<li><a id="tp_sitemap"
				   href="javascript:void(0);"><?php print gettext('Sitemap') ?></a>
				(<?php print gettext('web site structure') ?>)
			</li>

			<li><a id="tp_templates"
				   href="javascript:void(0);"><?php print gettext('Manage web site templates') ?></a>
			</li>

			<li><a id="tp_filemanager"
				   href="javascript:void(0);"><?php print gettext('Upload files') ?></a>
				<?php print '(' . gettext('images') . ', ' . gettext('media files') . ' ' . gettext('etc') . ')' ?>
			</li>
		</ul>
	</div>

	<?php if($lk_roles_id == CONST_ROLE_ADMIN_KEY) { ?>
	<div id="tolc_users">
		<h1><?php print gettext('Users management') ?></h1>

		<p><a id="tp_new_user"
			  href="javascript:void(0);"><?php print gettext('Add a new user') ?></a> <?php print gettext('or') ?> <?php print gettext('manage') ?>
			<a id="tp_users"
			   href="javascript:void(0);"><?php print gettext('existing users') ?></a>.
		</p>
	</div>
	<?php } ?>

	<div id="tolc_about">
		<h1><?php print gettext('About Tolc') ?></h1>

		<p><?php print gettext('Find out') ?> <a id="tp_about"
												 href="javascript:void(0);"><?php print gettext('here') ?></a>.
		</p>

	</div>

</div>

<?php } ?>