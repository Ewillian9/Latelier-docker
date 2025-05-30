import { Controller } from '@hotwired/stimulus'
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['mainImage', 'thumb', "legend"]
    current = 0
    interval = null
    connect() {
        this.showImage(this.current)
        this.startAutoSlide()
    }

    thumbClicked(event) {
        this.clearAutoSlide()
        const index = parseInt(event.currentTarget.dataset.index)
        this.showImage(index)
        this.startAutoSlide()
    }
    showImage(index) {
        this.mainImageTargets.forEach((img, i) => {
            const isActive = i === index
            img.classList.toggle('opacity-100', isActive)
            img.classList.toggle('z-10', isActive)
            img.classList.toggle('opacity-0', !isActive)
            img.classList.toggle('z-0', !isActive)
        })
        this.thumbTargets.forEach((thumb, i) => {
            thumb.classList.toggle('border-blue-500', i === index)
            thumb.classList.toggle('border-transparent', i !== index)
        })
        if (this.hasLegendTarget) {
            this.legendTargets.forEach((legend, i) => {
                const isActive = i === index
                legend.classList.toggle('opacity-100', isActive)
                legend.classList.toggle('opacity-0', !isActive)
                legend.classList.toggle('absolute', !isActive)
            })
        }
        this.current = index
    }
    startAutoSlide() {
        if (this.mainImageTargets.length <= 1) return
        this.interval = setInterval(() => {
            const next = (this.current + 1) % this.mainImageTargets.length
            this.showImage(next)
        }, 5000)
    }

    clearAutoSlide() {
        if (this.interval) {
            clearInterval(this.interval)
            this.interval = null
        }
    }

    pauseAutoSlide() {
        this.clearAutoSlide()
    }
    
    resumeAutoSlide() {
        this.startAutoSlide()
    }
    
    keyPressed(event) {
        if (event.key === 'ArrowLeft') {
            this.previousImage()
        } else if (event.key === 'ArrowRight') {
            this.nextImage()
        }
    }
    
    previousImage() {
        this.clearAutoSlide()
        const prev = this.current > 0 ? this.current - 1 : this.mainImageTargets.length - 1
        this.showImage(prev)
        this.startAutoSlide()
    }
    
    nextImage() {
        this.clearAutoSlide()
        const next = (this.current + 1) % this.mainImageTargets.length
        this.showImage(next)
        this.startAutoSlide()
    }
    
    disconnect() {
        this.clearAutoSlide()
    }
}