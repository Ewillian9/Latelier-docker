import { Controller } from "@hotwired/stimulus";
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["form"];

    submit() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            this.formTarget.requestSubmit();
        }, 400);
    }
}
