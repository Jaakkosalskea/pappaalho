/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/js/src/custom.js":
/*!*********************************!*\
  !*** ./assets/js/src/custom.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("(function ($) {\n  $(document).ready(function () {\n    $('h1').click(function (event) {\n      alert(event.target.id);\n    });\n  });\n})(jQuery);\n\n//# sourceURL=webpack:///./assets/js/src/custom.js?");

/***/ }),

/***/ "./assets/js/src/front-end.js":
/*!************************************!*\
  !*** ./assets/js/src/front-end.js ***!
  \************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _custom_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./custom.js */ \"./assets/js/src/custom.js\");\n/* harmony import */ var _custom_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_custom_js__WEBPACK_IMPORTED_MODULE_0__);\n/**\n * Tiiliskivi theme JavaScript.\n */\n//import Vue from '../../../node_modules/vue/dist/vue.js'\n//import jQuery from 'jquery';\n// Import modules (comment to disable)\n//import Vue from '@vue'; \n//import jQuery from '@jquery';\n//import MoveTo from 'moveto';\n//import LazyLoad from 'vanilla-lazyload';\n//import reframe from 'reframe.js';\n//import './modules/navigation.js';\n//import slick from './modules/slick.min';\n//import fancybox from './modules/jquery.fancybox.min.js';\n// Custom\n // Define Javascript is active by changing the body class\n//document.body.classList.remove('no-js');\n//document.body.classList.add('js');\n// Fit video embeds to container\n//reframe('.wp-has-aspect-ratio iframe');\n\n//# sourceURL=webpack:///./assets/js/src/front-end.js?");

/***/ }),

/***/ 1:
/*!******************************************!*\
  !*** multi ./assets/js/src/front-end.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = __webpack_require__(/*! /Users/tuomaslaine/Projektit/tiiliskiviteema/content/themes/tiiliskivi/assets/js/src/front-end.js */\"./assets/js/src/front-end.js\");\n\n\n//# sourceURL=webpack:///multi_./assets/js/src/front-end.js?");

/***/ })

/******/ });