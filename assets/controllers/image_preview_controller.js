import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['group', 'input', 'preview', 'image', 'filename', 'removeBtn', 'placeholder', 'loading']

    connect() {
        this.element.closest('form').addEventListener('submit', this.cleanEmptyFields.bind(this))
    }

    handleFileChange(event) {
        const input = event.target
        const group = input.closest('[data-image-preview-target="group"]')
        const preview = group.querySelector('[data-image-preview-target="preview"]')
        const image = group.querySelector('[data-image-preview-target="image"]')
        const filename = group.querySelector('[data-image-preview-target="filename"]')
        const placeholder = group.querySelector('[data-image-preview-target="placeholder"]')
        const loading = group.querySelector('[data-image-preview-target="loading"]')

        if (input.files && input.files[0]) {
            const file = input.files[0]
        
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file')
                input.value = ''
                return
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB')
                input.value = ''
                return
            }

            placeholder.classList.add('hidden')
            loading.classList.remove('hidden')

            const reader = new FileReader()
            reader.onload = (e) => {
                loading.classList.add('hidden')
                image.src = e.target.result
                filename.textContent = file.name
                preview.classList.remove('hidden')
                
                group.classList.remove('border-dashed')
                group.classList.add('border-solid', 'border-green-400', 'dark:border-green-500')
            }
            
            reader.onerror = () => {
                loading.classList.add('hidden')
                placeholder.classList.remove('hidden')
                alert('Error reading file')
                input.value = ''
            }
            
            reader.readAsDataURL(file)
        } else {
            this.resetToPlaceholder(group)
        }
    }

    removeImage(event) {
        event.stopPropagation()
        const group = event.target.closest('[data-image-preview-target="group"]')
        const input = group.querySelector('[data-image-preview-target="input"]')
        
        input.value = ''
        this.resetToPlaceholder(group)
    }

    resetToPlaceholder(group) {
        const preview = group.querySelector('[data-image-preview-target="preview"]')
        const placeholder = group.querySelector('[data-image-preview-target="placeholder"]')
        const loading = group.querySelector('[data-image-preview-target="loading"]')

        preview.classList.add('hidden')
        loading.classList.add('hidden')
        placeholder.classList.remove('hidden')
        
        group.classList.remove('border-solid', 'border-green-400', 'dark:border-green-500')
        group.classList.add('border-dashed')
    }

    cleanEmptyFields(event) {
        this.inputTargets.forEach((input) => {
            if (!input.value) {
                input.disabled = true
            }
        })
    }
}