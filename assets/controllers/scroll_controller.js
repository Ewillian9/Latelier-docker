import { Controller } from "@hotwired/stimulus"
/*stimulusFetch:'lazy'*/
export default class extends Controller {
    static targets = ["container"]

    connect() {
        this.scrollToBottom()
    }

    scrollToBottom() {
        this.containerTarget.scrollTo({
            top: this.containerTarget.scrollHeight,
            behavior: 'smooth'
        })
    }

    scrollToMessage(event) {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                this.scrollToBottom()
            })
        })
    }
}
