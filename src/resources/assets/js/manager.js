/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')

// directive
require('vue-multi-ref')

// polyfill
// window.__forceSmoothScrollPolyfill__ = true
require('smoothscroll-polyfill').polyfill()

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}

/*                Components                */
Vue.component('Odin', require('./Odin.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
EventHub.listen('odin-show', () => {})
EventHub.listen('odin-hide', () => {})
