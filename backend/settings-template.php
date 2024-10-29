<div id="accessally-wp-courseware-convert-container">
	<div class="accessally-setting-section">
		<div class="accessally-setting-header">Existing WP Courseware Courses</div>
		<div class="accessally-wp-courseware-list-existing-container">
			<table class="accessally-wp-courseware-list-existing">
				<tr>
					<th class="accessally-wp-courseware-id-col">ID</th>
					<th class="accessally-wp-courseware-title-col">Course name</th>
					<th class="accessally-wp-courseware-detail-col">Details</th>
					<th class="accessally-wp-courseware-convert-col">Conversion option</th>
				</tr>
				{{wp-courseware-courses}}
			</table>
		</div>
	</div>
	<div class="accessally-setting-section" {{show-existing}}>
		<div class="accessally-setting-header">Converted courses</div>
		<div class="accessally-wp-courseware-list-existing-container">
			<table class="accessally-wp-courseware-list-existing">
				<tbody>
					<tr>
						<th class="accessally-wp-courseware-list-existing-name-col">Name</th>
						<th class="accessally-wp-courseware-list-existing-edit-col">Edit</th>
						<th class="accessally-wp-courseware-list-existing-revert-col">Revert</th>
					</tr>
					{{existing-courses}}
				</tbody>
			</table>
		</div>
	</div>
</div>