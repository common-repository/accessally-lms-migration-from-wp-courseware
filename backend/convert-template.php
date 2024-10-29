<tr class="accessally-wp-courseware-list-existing-row">
	<td class="accessally-wp-courseware-id-col">{{id}}</td>
	<td class="accessally-wp-courseware-title-col">
		<a target="_blank" href="{{edit-link}}">{{name}}</a>
	</td>
	<td class="accessally-wp-courseware-detail-col">{{details}}</td>
	<td class="accessally-wp-courseware-convert-col">
		<select id="accessally-wp-courseware-operation-{{id}}" data-dependency-source="accessally-wp-courseware-operation-{{id}}">
			<option value="no">Do not convert</option>
			<option value="stage">Convert to a Stage-release course</option>
			<option value="alone">Convert to a Standalone course</option>
			<option value="wp">Convert to regular WordPress pages (Advanced)</option>
		</select>
		<div style="display:none" hide-toggle data-dependency="accessally-wp-courseware-operation-{{id}}" data-dependency-value-not="no"
			 accessally-convert-course="{{id}}"
			 class="accessally-setting-convert-button">
			Convert
		</div>
	</td>
</tr>