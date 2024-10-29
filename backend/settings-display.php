<div class="wrap">
<h2 style="display:none;"><?php _e('AccessAlly WP Courseware Conversion'); ?></h2>

<div id="accessally-wp-courseware-convert-wait-overlay">
	<div class="accessally-wp-courseware-convert-wait-content">
		<img src="<?php echo AccessAlly_WpCoursewareConversion::$PLUGIN_URI; ?>backend/wait.gif" alt="wait" width="128" height="128" />
	</div>
</div>
<div class="accessally-setting-container">
	<div class="accessally-setting-title">AccessAlly - WP Courseware Custom Post Conversion</div>
	<div class="accessally-setting-section">
		<div class="accessally-setting-message-container">
			<p>Use this tool to convert Courses, Units and Lessons created in WP Courseware Courses to regular WordPress pages, so they can be re-used after WP Courseware has been deactivated.</p>
			<ol>
				<li>Conversion does not modify the content of the units.</li>
				<li>The conversion process can be reverted, so WP Courseware courses / modules / units can be restored.</li>
				<li>Courses can be automatically converted to AccessAlly Course Wizard courses. <strong>Important:</strong> These are created as <strong>Drafts</strong> and they need to be further customized before they are published.</li>
			</ol>
		</div>
	</div>
	<?php echo $operation_code; ?>
</div>
</div>