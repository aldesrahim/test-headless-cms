<div
    x-data="{
        isShow: false,
        alert: null,
        init() {
            window.addEventListener('flash-alert:show', (event) => {
                const detail = Array.isArray(event.detail)
                    ? event.detail[0]
                    : event.detail

                let content = null
                let duration = null

                if (typeof detail === 'object') {
                    content = detail.content
                    duration = detail.duration ?? null
                } else {
                    content = detail
                }

                if (this.alert) {
                    this.hide()
                }

                this.alert = {
                    content,
                    duration: duration ?? 5000,
                    timeout: null,
                }

                this.show()
            })
        },
        show() {
            if (! this.alert) {
                return
            }

            this.isShow = true

            this.alert.timeout = setTimeout(() => {
                this.hide()
            }, this.alert.duration)
        },
        hide() {
            this.isShow = false

            if (this.alert != null) {
                clearTimeout(this.alert.timeout)
            }

            this.alert = null
        },
    }"
>
    <div
        class="mb-5 flex items-center space-x-2 rounded bg-zinc-800/5 p-5 [print-color-adjust:exact] dark:bg-white/10"
        x-cloak
        x-show="isShow"
    >
        <span class="grow" x-text="alert?.content"></span>
        <flux:button icon="x-mark" variant="subtle" :inset="true" @click="hide()" />
    </div>
</div>
