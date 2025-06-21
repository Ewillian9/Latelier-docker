import { Controller } from "@hotwired/stimulus";
/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    artworkId: Number,
    liked: Boolean,
    urlPrefix: String,
  }

  static targets = ["icon"]

  async toggle(event) {
    event.preventDefault();

    const action = this.likedValue ? 'unlike' : 'like';
    const url = `${this.urlPrefixValue}/${action}`;

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
        },
      });

      const data = await response.json();

      if (data.status === 'success') {
        this.likedValue = !this.likedValue;

        // Toggle button color
        this.element.classList.toggle('text-amber-500', this.likedValue);
        this.element.classList.toggle('text-zinc-100', !this.likedValue);

        
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Like toggle error:', error);
    }
  }
}
