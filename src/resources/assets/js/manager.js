/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')
require('vue-multi-ref')

window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}

/*                Components                */
Vue.component('OdinComp', require('./Odin.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
EventHub.listen('odin-show', () => {})
EventHub.listen('odin-hide', () => {})
