/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')

// directive
require('vue-multi-ref')

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}

// vue-awesome
import 'vue-awesome/icons/flag'
Vue.component('icon', require('vue-awesome/components/Icon'))

/*                Components                */
Vue.component('Odin', require('./Odin.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
EventHub.listen('odin-show', () => {})
EventHub.listen('odin-hide', () => {})
