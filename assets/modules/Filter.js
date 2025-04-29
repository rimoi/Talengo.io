//import {Flipper} from 'flip-toolkit'
import { Flipper, Flipped } from 'react-flip-toolkit'
/**
 * @Property {HTMLElement} pagination
 * @Property {HTMLElement} content
 * @Property {HTMLElement} sorting
 * @Property {HTMLFormElement} form
 * 
 */

export default class Filter {

	/**
	 * @param {HTMLElement|null} element
	*/
	constructor (element) {

		if (element === null) {

			return

		}

		this.pagination = element.querySelector('.js-filter-pagination')
		this.content = element.querySelector('.js-filter-content')
		this.sorting = element.querySelector('.js-filter-sorting')
		this.form = element.querySelector('.js-filter-form')
		this.bindEvents()
	}

	/**
	 * Ajoute le comportement au différents éléments
	 */
	bindEvents () {

		const Clicklistener = e => {
			if (e.target.tagName === 'A'){
				e.preventDefault()
				this.loadUrl(e.target.getAttribute('href'))
			}
		}

		this.sorting.addEventListener('click', Clicklistener)
		this.pagination.addEventListener('click', Clicklistener)
		this.form.querySelectorAll('input').forEach(input => {
			input.addEventListener('change', this.loadForm.bind(this))
		})
		this.form.querySelectorAll('input').forEach(input => {
			input.addEventListener('keyup', this.loadForm.bind(this))
		})
	}

	async loadForm () {
		const data = new FormData(this.form)
		const url = new URL(this.form.getAttribute('action') || window.location.href)
		const params = new URLSearchParams()
		data.forEach((value, key) => {
			params.append(key, value)
		})

		return this.loadUrl(url.pathname + '?' + params.toString())
	}

	async loadUrl (url) {

		const ajaxUrl = url + '&ajax=1'

		const response = await fetch(ajaxUrl, {
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		})

		if (response.status >= 200 && response.status < 300) {

			// Recupération des données
			const data = await response.json()

			// Injection du contenu
			this.content.innerHTML = data.content
			//this.flipContent(data.content)
			this.sorting.innerHTML = data.sorting
			this.pagination.innerHTML = data.pagination
			//this.form.innerHTML = data.form

			history.replaceState({}, '', url)

		} else {
			console.error(response)
		}
	}

	/**
	 * Remplace les éléments de la grille avec un éffet d'annimation flip
	 * @param {string} content
	 */
	flipContent (content) {
		const flipper = new Flipper({
			element: this.content
		})
		this.content.children.forEach(element => {
			flipper.addFlipped({
				element,
				flipId: element.id,
				shouldFlip: false,
				onExit: (element, index, removeElement) => {
					window.setTimeout(() => {
						removeElement()
					}, 2000)
				}
			})
		})
		flipper.recordBeforeUpdate()
		this.content.innerHTML = content
		this.content.children.forEach(element => {
			flipper.addFlipped({
				element,
				flipId: element.id
			})
		})
		flipper.update()
	}

}