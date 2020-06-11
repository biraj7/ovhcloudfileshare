<div class="section">
	<p>
		<?php print_unescaped(str_replace(
			[
				'{communityopen}',
				'{githubopen}',
				'{licenseopen}',
				'{linkclose}',
			],
			[
				'<a href="https://nextcloud.com/contribute" target="_blank" rel="noreferrer noopener">',
				'<a href="https://github.com/nextcloud" target="_blank" rel="noreferrer noopener">',
				'<a href="https://www.gnu.org/licenses/agpl-3.0.html" target="_blank" rel="noreferrer noopener">',
				'</a>',
			],
			$l->t('OVH File Drop')
		)); ?>
	</p>
</div>
