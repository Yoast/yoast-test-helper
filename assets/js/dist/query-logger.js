/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/src/query-logger/store.js":
/*!*********************************************!*\
  !*** ./assets/js/src/query-logger/store.js ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);

const DEFAULT_STATE = {
  showDrawer: true
};
const TOGGLE_DRAWER = "TOGGLE_DRAWER";
const CLOSE_DRAWER = "CLOSE_DRAWER";
const actions = {
  toggleDrawer() {
    return {
      type: TOGGLE_DRAWER
    };
  },

  closeDrawer() {
    return {
      type: CLOSE_DRAWER
    };
  }

};
const store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.createReduxStore)("yoast/query-logger", {
  reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
      case TOGGLE_DRAWER:
        return { ...state,
          showDrawer: !state.showDrawer
        };

      case CLOSE_DRAWER:
        return { ...state,
          showDrawer: false
        };
    }

    return state;
  },

  actions,
  selectors: {
    isDrawerOpen(state) {
      return state.showDrawer;
    }

  }
});
(0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.register)(store);

/***/ }),

/***/ "styled-components":
/*!*****************************************!*\
  !*** external "yoast.styledComponents" ***!
  \*****************************************/
/***/ (function(module) {

module.exports = yoast.styledComponents;

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ (function(module) {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!*********************************************!*\
  !*** ./assets/js/src/query-logger/index.js ***!
  \*********************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var styled_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! styled-components */ "styled-components");
/* harmony import */ var styled_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(styled_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./store */ "./assets/js/src/query-logger/store.js");





const Drawer = (styled_components__WEBPACK_IMPORTED_MODULE_2___default().div)`
    position: fixed;
    bottom: 0px;
    width: 100%;
    height: 200px;
    z-index: 10000 !important;
    display: flex;
    flex-direction: column;
    background-color: white;
`;
const Bar = (styled_components__WEBPACK_IMPORTED_MODULE_2___default().div)`
    border-top: 1px solid black;
    border-bottom: 1px solid black;
    width: 100%;
    height: 24px;
    display: flex;
    align-items: center;
    background-color: #ccd0d4;

    button {
        padding: 0;
    }

    span.title {
        margin-left: 4px;
        flex-grow: 1;
    }
`;
const adminBar = document.getElementById("wp-admin-bar-yoast-query-logger");

if (adminBar) {
  adminBar.querySelector(".ab-item").onclick = e => {
    e.preventDefault();
    (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.dispatch)("yoast/query-logger").toggleDrawer();
  };
}

const Menu = () => {
  const {
    closeDrawer
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useDispatch)("yoast/query-logger");
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Bar, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "title"
  }, "Yoast query monitor"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: closeDrawer
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "dashicons dashicons-no-alt"
  })));
};

const QueryLogger = () => {
  const open = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useSelect)(select => {
    return select("yoast/query-logger").isDrawerOpen();
  }, []);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, open ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Drawer, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Menu, null)) : null);
};

jQuery(() => {
  const box = document.createElement("div");
  box.className = "yoast-query-logger";
  document.body.appendChild(box);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.AsyncModeProvider, {
    value: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(QueryLogger, null)), box);
});
}();
/******/ })()
;
//# sourceMappingURL=query-logger.js.map