import{Controller}from'@hotwired/stimulus'/*stimulusFetch:'lazy'*/
export default class extends Controller{static targets=[]
goBack(){window.history.back()}}