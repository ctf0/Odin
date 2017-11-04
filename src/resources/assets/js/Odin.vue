<script>
export default {
    name: 'odin-comp',
    data() {
        return {
            selected: null,
            list : []
        }
    },
    methods: {
        // navigate
        navigation() {
            $('html').keydown((e) => {
                let f = $('.timeline-header').first()
                let l = $('.timeline-header').last()

                // first
                if (keycode(e) == 'home' && f.length) {
                    this.updateRev(f[0].id)
                    this.goTo(f[0])
                }

                // last
                if (keycode(e) == 'end' && l.length) {
                    this.updateRev(l[0].id)
                    this.goTo(l[0])
                }

                // hide
                if (keycode(e) == 'esc' && this.selected) {
                    this.toggleRev()
                }

                // next / prev
                let arr = this.list
                let cur = this.selected
                let index = arr.indexOf(cur)
                let item = null

                if (arr.length > 1) {
                    if (keycode(e) == 'right' || keycode(e) == 'down') {
                        item = arr[index + 1]
                    }

                    if (keycode(e) == 'left' || keycode(e) == 'up') {
                        item = arr[index - 1]
                    }

                    if ((keycode(e) == 'left' || keycode(e) == 'right' || keycode(e) == 'down' || keycode(e) == 'up') && item) {
                        this.updateRev(item)
                        this.goTo($(`#${item}`)[0])
                    }
                }
            })
        },
        goTo(item) {
            this.$scrollTo(item)
        },

        // rev
        updateRev(item) {
            this.selected = item
        },
        toggleRev(item = null) {
            if (!item) {
                this.list = []
                this.selected = null
                $('html').unbind()
                return EventHub.fire('odin-hide')
            }

            EventHub.fire('odin-show')
            this.updateRev(item)
            this.$nextTick(() => {
                this.goTo(`#${item}`)
                this.navigation()
            })

            // so we can use left/right
            let that = this
            $('.timeline-header').each(function() {
                that.list.push($(this)[0].id)
            })
        },

        // form
        removeRev(e) {
            let arr = this.list
            let id = e.target.dataset.id
            let index = arr.indexOf(id)

            $.ajax({
                url: event.target.action,
                type: 'DELETE'
            }).done((res) => {
                if (res.success) {
                    this.showNotif(res.message)
                    $(`[rev-id="${id}"]`).remove()
                    arr.splice(index, 1)

                    if (arr.length) {
                        let newIndex = arr[0]

                        this.selected = newIndex
                        return this.goTo(`#${newIndex}`)
                    }

                    this.toggleRev()
                    return $('.revisions').remove()
                }

                this.showNotif(res.message)

            }).fail(() => {
                this.showNotif('Ajax Call Failed', 'black')
            })
        },
        showNotif(msg, s = 'success') {

            let title = ''
            let duration = null

            switch (s) {
            case 'danger':
                title = 'Error'
                break
            case 'warning':
                title = 'Warning'
                duration = 3
                break
            default:
                title = 'Success'
                duration = 3
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
