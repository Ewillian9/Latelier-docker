import { Controller } from '@hotwired/stimulus'
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['container']
    connect() {
        this.index = this.containerTarget.children.length
        this.prototype = this.containerTarget.dataset.prototype
        this.maxFields = parseInt(this.containerTarget.dataset.max)
        this.containerTarget.querySelectorAll('input[type="file"]').forEach((input) => this.attachChangeEvent(input))
        this.element.closest('form').addEventListener('submit', this.cleanEmptyFields.bind(this))
        if (this.index === 0) this.addField()
    }
    cleanEmptyFields(event) {
        this.containerTarget.querySelectorAll('.image-input').forEach((div) => {
            const input = div.querySelector('input[type="file"]')
            if (input && !input.value) div.remove()
        })
    }
    addField() {
        if (this.index >= this.maxFields) return
        const newForm = this.prototype.replace(/__name__/g, this.index)
        const div = document.createElement('div')
        div.classList.add('image-input')
        div.innerHTML = newForm
        this.containerTarget.appendChild(div)
        const input = div.querySelector('input[type="file"]')
        this.attachChangeEvent(input)
        this.index++
    }
    attachChangeEvent(input) {
        if (!input) return
        input.addEventListener('change', () => {
            if (this.index < this.maxFields) this.addField()
        })
    }
}