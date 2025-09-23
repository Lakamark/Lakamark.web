export class Carousel {

    /**
     * This callback type is called `moveCallback` and is displayed as a global symbol.
     *
     * @callback moveCallack
     * @param {number} index
     */

    /**
     * @param {HTMLElement} element
     * @param {object} options
     * @param {object} [options.slidesToScroll=1] Number elements to scroll
     * @param {object} [options.slidesVisible=1] Number visibles elements in one slide
     * @param {boolean} [options.loop=false] Enable the looping behavior when you attempt the end slide
     * @param {boolean} [options.pagination=false] Enable the pagination behavior
     * @param {boolean} [options.pagination=true] Enable the navigation behavior
     * @param {boolean} [options.infinite=false] Enable the infinite scrolling behavior
     */
    constructor (element, options = {}) {
        this.element = element
        this.options = Object.assign({}, {
            slidesToScroll: 1,
            slidesVisible: 1,
            loop: false,
            pagination: false,
            navigation: true,
            infinite: false,
        }, options)

        // We return a fatal error if you activate the loop and the infinite options' carousel.
        if (this.options.loop && this.options.infinite) {
            throw new Error("You can't enable the loop option and the infinite option in the carousel.");
        }

        let children = [].slice.call(element.children)
        this.isMobile = false
        this.curentItem = 0
        this.moveCallbacks = []
        this.offset = 0

        // Create the carousel components in the DOM
        this.root = this.createDivWithClass('carousel')
        this.root.setAttribute('tabindex', '0')
        this.container = this.createDivWithClass('carousel__container')
        this.root.appendChild(this.container)
        this.element.appendChild(this.root)
        this.items = children.map((child) => {
            let item = this.createDivWithClass('carousel__item')
            item.appendChild(child)
            return item
        })

        // Check if the infinite option is enabled
        if (this.options.infinite) {
            this.offset = this.options.slidesVisible + this.options.slidesToScroll

            // If the carousel has not enough element. The offset is bigger than children elements
            if (this.offset > children.length) {
                console.error("Your are not enough children in the carousel", element)
            }

            // Rebuild the items array
            this.items = [
                ...this.items.slice(this.items.length - this.offset).map(item => item.cloneNode(true)),
                ...this.items,
                ...this.items.slice(0, this.offset).map(item => item.cloneNode(true))
            ]
            this.goToItem(this.offset, false)
        }
        this.items.forEach(item => this.container.appendChild(item))
        this.setStyles()

        // If the navigation option is enabled
        if (this.options.navigation === true) {
            this.createNavigation()
        }

        // If the pagination option is enabled
        if (this.options.pagination === true) {
            this.createPagination()
        }
        this.moveCallbacks.forEach(cb => cb(this.curentItem))

        // Events
        this.onWindowResize()
        window.addEventListener('resize', this.onWindowResize.bind(this))
        this.root.addEventListener('keyup', e => {
            if (e.key === 'ArrowRight' || e.key === 'Right') {
                this.next()
            } else if (e.key === 'ArrowLeft' || e.key === 'Left') {
                this.prev()
            }
        })

        if (this.options.infinite) {
            this.container.addEventListener('transitionend', this.resetInfinite.bind(this))
        }
    }

    /**
     * Apply the right ratio to the carousel elements
     */
    setStyles() {
        let ratio = this.items.length / this.slidesVisible
        this.container.style.width = (ratio * 100) + '%'
        this.items.forEach(item => item.style.width = ((100 / this.slidesVisible) / ratio) + '%')

        // If you switch the device, we should update the UI
        // Because, the slidesVisible change depend on the device and options configuration
        this.updateUI(this.items)
    }

    /**
     * Create the controller carousel
     */
    createNavigation() {
        let nextButton = this.createDivWithClass('carousel__next')
        let prevButton = this.createDivWithClass('carousel__prev')
        this.root.appendChild(nextButton)
        this.root.appendChild(prevButton)

        nextButton.addEventListener('click', this.next.bind(this))
        prevButton.addEventListener('click', this.prev.bind(this))

        // Check the looping behavior is enabled.
        // If the loop is disabled won't execute the rest of this function
        if (this.options.loop === true) {
            return
        }
        this.onMove(index => {

            // Hide the prev button
            if (index === 0) {
                prevButton.classList.add('carousel__prev--hidden')
            } else {
                prevButton.classList.remove('carousel__prev--hidden')
            }

            // Hide the next button
            if (this.items[this.curentItem + this.slidesVisible] === undefined) {
                nextButton.classList.add('carousel__next--hidden')
            } else {
                nextButton.classList.remove('carousel__next--hidden')
            }
        })
    }

    /**
     * Create the pagination behavior in the DOM
     */
    createPagination() {
        let pagination = this.createDivWithClass('carousel__pagination')
        let buttons = []
        this.root.appendChild(pagination)

        // Create a button for each item
        for (let i = 0; i < (this.items.length - 2 * this.offset); i = i + this.options.slidesToScroll) {
            let button = this.createDivWithClass('carousel__pagination__button')
            button.addEventListener('click', () => this.goToItem(i + this.offset))
            pagination.appendChild(button)
            buttons.push(button)
        }
        this.onMove(index => {
            let count = this.items.length - 2 * this.offset;
            let activeButton = buttons[Math.floor(((index - this.offset) % count) / this.options.slidesToScroll)];
            if (activeButton) {
                buttons.forEach((button) => button.classList.remove('carousel__pagination__button--active'))
                activeButton.classList.add('carousel__pagination__button--active')
            }
        })
    }

    /**
     * Move forward the carousel container
     */
    next() {
        this.goToItem(this.curentItem + this.slidesToScroll)
    }

    /**
     * Move backward the carousel container
     */
    prev() {
        this.goToItem(this.curentItem - this.slidesToScroll)
    }

    /**
     * To move the carousel to the target index item
     *
     * @param {Number} index
     * @param {boolean} [animation=true]
     */
    goToItem(index, animation = true) {
        if (index < 0 ) {
            if (this.options.loop) {
                index = this.items.length - this.slidesVisible
            } else {
                return
            }
        } else if (
            index >= this.items.length
            || (this.items[this.curentItem + this.slidesVisible] === undefined && index > this.curentItem)
        ) {
            if (this.options.loop) {
                index = 0;
            } else {
                return
            }
        }
        let translateX = index * -100 / this.items.length

        // Disabled transform animations
        if (animation === false) {
            this.container.style.transition = 'none'
        }

        this.container.style.transform = `translate3d(${translateX}%, 0, 0)`
        this.container.offsetHeight // Force repaint

        if (animation === false) {
            this.container.style.transition = ''
        }

        this.curentItem = index
        this.updateUI(this.items);
        this.moveCallbacks.forEach(cb => cb(index))
    }

    /**
     * Update the UI when the carousel is moving
     * You can write your own CSS properties
     * when the class carousel__slide--active was added in the DOM
     * This is a toggle class when the current items and the index match
     *
     * example :
     * unactive element state:
     * <div class="carousel__item">
     *     Your content
     * </div>
     *
     * active element state:
     * <div class="carousel__item carousel__slide--active">
     *     Your content
     * </div>
     *
     * @param {Array<HTMLElement>} items
     */
    updateUI(items) {
        items.forEach((slide, id) => {
            if (
                id >= this.curentItem
                && id < this.curentItem + this.slidesVisible
            ) {
                slide.classList.add('carousel__slide--active')
            } else {
                slide.classList.remove('carousel__slide--active')
            }
        })
    }

    /**
     * Move the container to stimulate an infinite slice loop animation
     */
    resetInfinite() {
        if (this.curentItem <=this.options.slidesToScroll) {
            // Move left
            this.goToItem(this.curentItem + (this.items.length - 2 * this.offset), false)
        } else if(this.curentItem >= this.items.length - this.offset) {
            // Move right
            this.goToItem(this.curentItem - (this.items.length - 2 * this.offset), false)
        }
    }

    /**
     *
     * @param {moveCallack} cb
     */
    onMove(cb) {
        this.moveCallbacks.push(cb)
    }

    /**
     * To listen the resizing window
     * It is a responsive system to adapt the carousel depend on the device
     */
    onWindowResize () {
        let mobile = window.innerWidth < 800;
        if (mobile !== this.isMobile) {
            this.isMobile = mobile
            this.setStyles()
            this.moveCallbacks.forEach(cb => cb(this.curentItem))
        }
    }

    /**
     * To create a div with the class attribute
     *
     * @param {string} className
     * @returns {HTMLElement}
     */
    createDivWithClass(className) {
        let div = document.createElement('div')
        div.setAttribute('class', className)
        return div
    }

    /**
     * To get the number of the slide to scroll if you are on mobile device or desktop device
     *
     * @returns {number}
     */
    get slidesToScroll() {
        return this.isMobile ? 1 : this.options.slidesToScroll
    }

    /**
     * To get the number of the visible element to scroll if you are on mobile device or desktop device
     *
     * @returns {number}
     */
    get slidesVisible() {
        return this.isMobile ? 1 : this.options.slidesVisible
    }
}
