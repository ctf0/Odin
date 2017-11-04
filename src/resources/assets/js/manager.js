$.ajaxSetup({
    cache: false,
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
})

/*                Libs                */
window.Vue = require('vue')
window.EventHub = require('vuemit')
window.keycode = require('keycode')
Vue.use(require('vue-scrollto'), {
    container: '.compare-page',
    duration: 180,
    easing: 'ease',
    offset: -28,
    cancelable: true,
    onDone: false,
    onCancel: false,
    x: false,
    y: true
})

/*                Components                */
Vue.component('OdinComp', require('./Odin.vue'))
Vue.component('MyNotification', require('vue-notif'))

/*                Events                */
EventHub.listen('odin-show', () => {})
EventHub.listen('odin-hide', () => {})
