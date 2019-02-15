/*                Libs                */
window.EventHub = require('vuemit')
window.keycode = require('keycode')

// axios
window.axios = require('axios')
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'X-Requested-With': 'XMLHttpRequest'
}
axios.interceptors.response.use(
    (response) => response,
    (error) => Promise.reject(error.response)
)

// vue-awesome
import 'vue-awesome/icons/flag'
Vue.component('icon', require('vue-awesome/components/Icon').default)

/*                Components                */
Vue.component('Odin', require('./Odin.vue').default)
Vue.component('MyNotification', require('vue-notif').default)

/*                Events                */
EventHub.listen('odin-show', () => {})
EventHub.listen('odin-hide', () => {})
