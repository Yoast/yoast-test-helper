var Yoast_Plugin_Toggler = {
	toggle_plugin: function( group, plugin, nonce ) {
		"use strict";

		jQuery.getJSON(
			ajaxurl,
			{
				action: "toggle_plugin",
				ajax_nonce: nonce,
				group: group,
				plugin: plugin
			},
			function( response ) {
				if ( response.activated_plugin !== undefined ) {
					window.location.reload(true);
				}
			}
		);

		return true;
	}
};
