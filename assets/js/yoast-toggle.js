var Yoast_Plugin_Toggler = {
	toggle_plugin: function( plugin, nonce ) {
		"use strict";

		jQuery.getJSON(
			ajaxurl,
			{
				action: "toggle_version",
				ajax_nonce: nonce,
				plugin: plugin
			},
			function( response ) {
				if ( response.activated_version !== undefined ) {
					window.location.reload(true);
				}
			}
		);

		return true;
	}
};
