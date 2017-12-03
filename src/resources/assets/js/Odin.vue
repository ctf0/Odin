<script>
export default {
    name: 'odin-comp',
    props: ['odinTrans', 'revList'],
    data() {
        return {
            selected: null,
            list : this.revList
        }
    },
    methods: {
        // navigate
        navigation(e) {
            let cur = this.selected
            let arr = this.list
            let index = arr.indexOf(cur)
            let newId = null

            // hide
            if (keycode(e) == 'esc' && cur) {
                this.toggleRev()
            }

            if (arr.length > 1) {
                // first
                if (keycode(e) == 'home') {
                    newId = arr[0]
                }

                // last
                if (keycode(e) == 'end') {
                    newId = arr[arr.length - 1]
                }

                // next
                if (keycode(e) == 'right' || keycode(e) == 'down') {
                    newId = arr[index + 1]
                }

                // prev
                if (keycode(e) == 'left' || keycode(e) == 'up') {
                    newId = arr[index - 1]
                }

                if (
                    keycode(e) == 'home' ||
                    keycode(e) == 'end' ||
                    keycode(e) == 'left' ||
                    keycode(e) == 'right' ||
                    keycode(e) == 'down' ||
                    keycode(e) == 'up'
                ) {
                    if (!newId) {
                        return
                    }

                    this.updateRev(newId)
                    this.goTo(`${newId}`)
                }
            }
        },
        goTo(id) {
            document.getElementById(id).scrollIntoView()
            this.$refs.container.scrollTop -= 28
        },

        // rev
        updateRev(id) {
            this.selected = id
        },
        toggleRev(id = null) {
            if (!id) {
                this.selected = null
                document.removeEventListener('keydown', this.navigation)
                return EventHub.fire('odin-hide')
            }

            EventHub.fire('odin-show')
            this.updateRev(id)
            this.$nextTick(() => {
                this.goTo(`${id}`)
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
                    this.$refs[`rev-${id}`].map((e) => {
                        e.remove()
                    })
                    arr.splice(index, 1)

                    if (arr.length) {
                        let newIndex = arr[0]
                        this.updateRev(newIndex)
                        return this.goTo(`${newIndex}`)
                    }

                    this.toggleRev()
                    return this.$refs.revisions.remove()
                }

                this.showNotif(data.message, 'danger')

            }).catch((err) => {
                console.error(err)
                this.showNotif(this.trans('ajax_fail'), 'black')
            })
        },
        trans(key) {
            return this.odinTrans[key]
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
    render () {}
}
</script>
