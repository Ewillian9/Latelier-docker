import { Controller } from '@hotwired/stimulus'
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['mainImage', 'thumb']
    current = 0
    interval = null
    connect() {
        this.showImage(this.current)
        this.startAutoSlide()
        this.thumbTargets.forEach(thumb => {
            thumb.addEventListener('click', (e) => {
                clearInterval(this.interval)
                const index = parseInt(thumb.dataset.index)
                this.showImage(index)
                this.startAutoSlide()
            })
        })
    }
    showImage(index) {
        this.mainImageTargets.forEach((img, i) => {
            img.classList.toggle('opacity-100', i === index)
            img.classList.toggle('z-10', i === index)
            img.classList.toggle('opacity-0', i !== index)
            img.classList.toggle('z-0', i !== index)
        })
        this.thumbTargets.forEach((thumb, i) => {
            thumb.classList.toggle('border-blue-500', i === index)
        })
        this.current = index
    }
    startAutoSlide() {
        this.interval = setInterval(() => {
            const next = (this.current + 1) % this.mainImageTargets.length
            this.showImage(next)
        }, 5000)
    }
}