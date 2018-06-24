<script>
import animateScrollTo from './animated-scroll-to'

export default {
    name: 'odin',
    props: ['translations', 'revList'],
    data() {
        return {
            selected: null,
            list: this.revList
        }
    },
    methods: {
        // navigate
        navigation(e) {
            let key = keycode(e)

            let cur = this.selected
            let arr = this.list
            let index = arr.indexOf(cur)
            let newId = null

            // hide
            if (key == 'esc' && cur) {
                this.toggleRev()
            }

            if (arr.length > 1) {
                // first
                if (key == 'home') {
                    newId = arr[0]
                }

                // last
                if (key == 'end') {
                    newId = arr[arr.length - 1]
                }

                // next
                if (key == 'right' || key == 'down') {
                    newId = arr[index + 1]
                }

                // prev
                if (key == 'left' || key == 'up') {
                    newId = arr[index - 1]
                }

                if (
                    key == 'home' ||
                    key == 'end' ||
                    key == 'left' ||
                    key == 'right' ||
                    key == 'down' ||
                    key == 'up'
                ) {
                    if (!newId) {
                        return
                    }

                    e.preventDefault()
                    this.goTo(newId)
                }
            }
        },
        goTo(id) {
            this.updateRev(id)

            animateScrollTo(document.getElementById(id), {
                maxDuration: 1000,
                offset: -28,
                element: this.$refs.container,
                useKeys: true
            })
        },

        // rev
        updateRev(id) {
            this.selected = id
        },
        toggleRev(id = null) {
            if (!id) {
                this.selected = null
                return document.removeEventListener('keydown', this.navigation)
            }

            this.$nextTick(() => {
                this.goTo(id)
                document.addEventListener('keydown', this.navigation)
            })
        },

        // form
        removeRev(e) {
            let arr = this.list
            let id = parseInt(e.target.dataset.id)
            let index = arr.indexOf(id)

            axios({
                method: 'DELETE',
                url: e.target.action
            }).then(({data}) => {
                if (data.success) {
                    this.showNotif(data.message)
                    Array.from(document.querySelectorAll(`[data-index="${id}"]`)).forEach((e) => {
                        e.remove()
                    })
                    arr.splice(index, 1)

                    if (arr.length) {
                        let newIndex = arr[index] || arr[0]
                        return this.goTo(newIndex)
                    }

                    this.toggleRev()
                    return this.$refs.revisions.remove()
                }

                this.showNotif(data.message, 'danger')

            }).catch((err) => {
                console.error(err)
                this.showNotif(this.trans('ajax_error'), 'black')
            })
        },
        trans(key) {
            return this.translations[key]
        },
        showNotif(msg, s = 'success') {

            let title
            let duration = 2

            switch (s) {
                case 'black':
                case 'danger':
                    title = 'Error'
                    duration = null
                    break
                default:
                    title = 'Success'
            }

            EventHub.fire('showNotif', {
                title: title,
                body: msg,
                type: s,
                duration: duration
            })
        }
    },
    watch: {
        selected(val) {
            const html = document.getElementsByTagName('html')[0]

            if (val) {
                html.classList.add('no-scroll')
                return EventHub.fire('odin-show')
            }

            html.classList.remove('no-scroll')
            EventHub.fire('odin-hide')
        }
    },
    render() {}
}
</script>
