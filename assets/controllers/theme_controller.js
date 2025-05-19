import{Controller}from'@hotwired/stimulus'/*stimulusFetch:'lazy'*/
export default class extends Controller{static targets=['button']
connect(){document.documentElement.classList.toggle("dark",localStorage.theme==="dark"||(!("theme"in localStorage)&&window.matchMedia("(prefers-color-scheme: dark)").matches),)
this.updateIcon()}
toggle(){localStorage.theme=document.documentElement.classList.toggle('dark')?'dark':'light'
this.updateIcon()}
updateIcon(){this.buttonTarget.textContent=document.documentElement.classList.contains('dark')?'🌙':'☀️'}}