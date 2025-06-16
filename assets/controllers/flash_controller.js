import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['message']

  connect() {
    this.messageTargets.forEach((el) => {
      setTimeout(() => {
        el.classList.add('opacity-0', 'transition-opacity', 'duration-1000')
        setTimeout(() => el.remove(), 1000)
      }, 5000)
    })
  }
}
