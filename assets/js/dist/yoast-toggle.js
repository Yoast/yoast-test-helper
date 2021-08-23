/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
/*!***************************************!*\
  !*** ./assets/js/src/yoast-toggle.js ***!
  \***************************************/
var Yoast_Plugin_Toggler = {
  toggle_plugin: function (group, plugin, nonce) {
    "use strict";

    jQuery.getJSON(ajaxurl, {
      action: "toggle_plugin",
      ajax_nonce: nonce,
      group: group,
      plugin: plugin
    }, function (response) {
      if (response.activated_plugin !== undefined) {
        window.location.reload(true);
      }
    });
    return true;
  }
};
/******/ })()
;
//# sourceMappingURL=yoast-toggle.js.map